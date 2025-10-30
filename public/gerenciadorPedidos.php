<?php
session_start();

// Verificação de sessão
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];
$nomeUsuario = $usuario['nomeUsuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';
$idUsuario = $usuario['idUsuario'];

// Definir página de voltar baseada no tipo de usuário
if ($tipoUsuario === 'admin') {
    $voltar = "adm.php";
} elseif ($tipoUsuario === 'representante') {
    $voltar = "representante.php";
} elseif ($tipoUsuario === 'lojista') {
    $voltar = "lojista.php";
} else {
    $voltar = "login.php";
}

// Conexão com banco
require_once "../config/connection.php";
$conexao = conectarBD();

// Buscar produtos ativos
$sql_produtos = "SELECT idprodutos, nomeProduto, categoria, imagem_url FROM produtos WHERE ativoProduto = 1 ORDER BY categoria, nomeProduto";
$result_produtos = mysqli_query($conexao, $sql_produtos);

// Processar novo pedido (APENAS LOJISTA)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['criar_pedido']) && $tipoUsuario === 'lojista') {
    $produtos = $_POST['produtos'] ?? [];
    
    // Buscar a loja vinculada ao lojista
    $sql_loja = "SELECT idlojas, representante_id FROM lojas WHERE idlojas = (SELECT loja_id FROM usuario WHERE idUsuario = ?)";
    $stmt_loja = mysqli_prepare($conexao, $sql_loja);
    mysqli_stmt_bind_param($stmt_loja, "i", $idUsuario);
    mysqli_stmt_execute($stmt_loja);
    $result_loja = mysqli_stmt_get_result($stmt_loja);
    $loja = mysqli_fetch_assoc($result_loja);
    
    if (!$loja) {
        $erro = "Loja não encontrada para este usuário";
    } else {
        $lojas_idlojas = $loja['idlojas'];
        $representante_id = $loja['representante_id'];

        if (empty($produtos)) {
            $erro = "Selecione pelo menos um produto";
        } else {
            // Verificar se há produtos selecionados
            $produtos_selecionados = false;
            foreach ($produtos as $quantidade) {
                if ($quantidade > 0) {
                    $produtos_selecionados = true;
                    break;
                }
            }

            if (!$produtos_selecionados) {
                $erro = "Selecione pelo menos um produto com quantidade maior que zero";
            } else {
                // Inserir pedido vinculado ao representante
                $sql_pedido = "INSERT INTO pedidos (lojas_idlojas, status, representante_id) VALUES (?, 'pendente', ?)";
                $stmt_pedido = mysqli_prepare($conexao, $sql_pedido);
                mysqli_stmt_bind_param($stmt_pedido, "ii", $lojas_idlojas, $representante_id);
                
                if (mysqli_stmt_execute($stmt_pedido)) {
                    $idPedidos = mysqli_insert_id($conexao);
                    
                    // Inserir itens do pedido
                    foreach ($produtos as $produto_id => $quantidade) {
                        if ($quantidade > 0) {
                            $sql_item = "INSERT INTO pedido_itens (quantPedItens, pedidos_idPedidos, produtos_idProdutos) 
                                        VALUES (?, ?, ?)";
                            $stmt_item = mysqli_prepare($conexao, $sql_item);
                            mysqli_stmt_bind_param($stmt_item, "iii", $quantidade, $idPedidos, $produto_id);
                            mysqli_stmt_execute($stmt_item);
                        }
                    }
                    
                    $sucesso = "Pedido #$idPedidos enviado para seu representante com sucesso!";
                } else {
                    $erro = "Erro ao criar pedido: " . mysqli_error($conexao);
                }
            }
        }
    }
}

// Processar atualização de status (apenas representante)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $idPedidos = $_POST['idPedidos'] ?? '';
    $novoStatus = $_POST['novoStatus'] ?? '';

    if ($tipoUsuario === 'representante' && !empty($idPedidos) && !empty($novoStatus)) {
        // Validar se o status é válido
        $status_validos = ['pendente', 'enviado', 'entregue', 'cancelado'];
        if (in_array($novoStatus, $status_validos)) {
            // Atualizar apenas pedidos das lojas deste representante
            $sql = "UPDATE pedidos SET status = ? WHERE idPedidos = ? AND representante_id = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "sii", $novoStatus, $idPedidos, $idUsuario);
            
            if (mysqli_stmt_execute($stmt)) {
                $sucesso = "Status do pedido #$idPedidos atualizado para " . ucfirst($novoStatus) . "!";
            } else {
                $erro = "Erro ao atualizar status: " . mysqli_error($conexao);
            }
        } else {
            $erro = "Status inválido!";
        }
    }
}

// CONSTRUIR QUERY COM FILTROS - CORRIGIDO
$where_conditions = [];
$params = [];
$param_types = "";

// Buscar pedidos baseado no tipo de usuário - QUERIES SIMPLIFICADAS
if ($tipoUsuario === 'lojista') {
    // Lojista vê apenas os pedidos da sua loja
    $sql_pedidos = "SELECT p.*, l.nomeLoja 
                    FROM pedidos p 
                    INNER JOIN lojas l ON p.lojas_idlojas = l.idlojas 
                    WHERE l.idlojas = (SELECT loja_id FROM usuario WHERE idUsuario = ?)";
    
    $params[] = $idUsuario;
    $param_types .= "i";
    
} elseif ($tipoUsuario === 'representante') {
    // Representante vê apenas os pedidos das lojas que ele cadastrou - QUERY SIMPLIFICADA
    $sql_pedidos = "SELECT p.*, l.nomeLoja, u.nomeUsuario as nome_lojista
                    FROM pedidos p 
                    INNER JOIN lojas l ON p.lojas_idlojas = l.idlojas 
                    LEFT JOIN usuario u ON l.idlojas = u.loja_id
                    WHERE p.representante_id = ?";
    
    $params[] = $idUsuario;
    $param_types .= "i";
    
} elseif ($tipoUsuario === 'admin') {
    // Admin vê todos os pedidos
    $sql_pedidos = "SELECT p.*, l.nomeLoja, u.nomeUsuario as nome_lojista, 
                           rep.nomeUsuario as nome_representante
                    FROM pedidos p 
                    INNER JOIN lojas l ON p.lojas_idlojas = l.idlojas 
                    LEFT JOIN usuario u ON l.idlojas = u.loja_id AND u.tipoUsuario = 'lojista'
                    INNER JOIN usuario rep ON p.representante_id = rep.idUsuario
                    WHERE 1=1";
}

// Adicionar filtros adicionais apenas se não forem lojista (que já tem filtro fixo)
if ($tipoUsuario !== 'lojista') {
    // Filtro por status
    if (isset($_GET['status']) && !empty($_GET['status'])) {
        $where_conditions[] = "p.status = ?";
        $params[] = $_GET['status'];
        $param_types .= "s";
    }

    // Filtro por representante (apenas admin)
    if ($tipoUsuario === 'admin' && isset($_GET['representante']) && !empty($_GET['representante'])) {
        $where_conditions[] = "p.representante_id = ?";
        $params[] = $_GET['representante'];
        $param_types .= "i";
    }

    // Filtro por data
    if (isset($_GET['data_inicio']) && !empty($_GET['data_inicio'])) {
        $where_conditions[] = "DATE(p.dataPedido) >= ?";
        $params[] = $_GET['data_inicio'];
        $param_types .= "s";
    }

    if (isset($_GET['data_fim']) && !empty($_GET['data_fim'])) {
        $where_conditions[] = "DATE(p.dataPedido) <= ?";
        $params[] = $_GET['data_fim'];
        $param_types .= "s";
    }
}

// Adicionar condições WHERE se houver
if (!empty($where_conditions)) {
    // Se for admin, já tem WHERE 1=1, então usa AND
    if ($tipoUsuario === 'admin') {
        $sql_pedidos .= " AND " . implode(" AND ", $where_conditions);
    } 
    // Se for representante ou lojista, precisa adicionar WHERE
    elseif ($tipoUsuario === 'representante' || $tipoUsuario === 'lojista') {
        $sql_pedidos .= " AND " . implode(" AND ", $where_conditions);
    }
}

$sql_pedidos .= " ORDER BY p.dataPedido DESC";

// DEBUG: Verificar a query e parâmetros
error_log("SQL: " . $sql_pedidos);
error_log("Params: " . print_r($params, true));
error_log("Param types: " . $param_types);

// Preparar e executar query
$stmt_pedidos = mysqli_prepare($conexao, $sql_pedidos);
if ($stmt_pedidos) {
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt_pedidos, $param_types, ...$params);
    }
    mysqli_stmt_execute($stmt_pedidos);
    $result_pedidos = mysqli_stmt_get_result($stmt_pedidos);
} else {
    $erro = "Erro ao preparar query: " . mysqli_error($conexao);
    $result_pedidos = false;
}

// Buscar representantes para filtro (apenas admin)
$representantes = [];
if ($tipoUsuario === 'admin') {
    $sql_rep = "SELECT idUsuario, nomeUsuario FROM usuario WHERE tipoUsuario = 'representante' ORDER BY nomeUsuario";
    $result_rep = mysqli_query($conexao, $sql_rep);
    $representantes = mysqli_fetch_all($result_rep, MYSQLI_ASSOC);
}

// Função para buscar itens do pedido
function buscarItensPedido($conexao, $idPedidos) {
    $sql = "SELECT pi.*, p.nomeProduto, p.categoria, p.imagem_url
            FROM pedido_itens pi 
            INNER JOIN produtos p ON pi.produtos_idProdutos = p.idprodutos 
            WHERE pi.pedidos_idPedidos = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idPedidos);
    mysqli_stmt_execute($stmt);
    return mysqli_stmt_get_result($stmt);
}

// Contar pedidos por status
$pedidos_por_status = [];
if ($result_pedidos && mysqli_num_rows($result_pedidos) > 0) {
    $pedidos_temp = mysqli_fetch_all($result_pedidos, MYSQLI_ASSOC);
    mysqli_data_seek($result_pedidos, 0); // Reset pointer
    
    foreach ($pedidos_temp as $pedido) {
        $status = $pedido['status'];
        if (!isset($pedidos_por_status[$status])) {
            $pedidos_por_status[$status] = 0;
        }
        $pedidos_por_status[$status]++;
    }
} else {
    // Inicializar array vazio se não houver pedidos
    $pedidos_por_status = [
        'pendente' => 0,
        'enviado' => 0,
        'entregue' => 0,
        'cancelado' => 0
    ];
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Pedidos - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="./css/gerenciadorPedidos.css">
</head>
<body>
    <div class="admin-container">
        <main class="main-content">
            <!-- Header -->
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo $voltar; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0">Sistema de Pedidos</h1> 
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                alt="Avatar do <?= htmlspecialchars($nomeUsuario) ?>" 
                                class="user-avatar-img" 
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="user-avatar fallback" style="display: none;"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                        <?php else: ?>
                            <div class="user-avatar"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($nomeUsuario) ?></span>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página -->
            <div class="page-content">
                <!-- Mensagens de Sucesso/Erro -->
                <?php if (isset($sucesso)): ?>
                    <div class="alert alert-success alert-dismissible fade show">
                        <i class="fas fa-check-circle me-2"></i><?php echo $sucesso; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($erro)): ?>
                    <div class="alert alert-danger alert-dismissible fade show">
                        <i class="fas fa-exclamation-circle me-2"></i><?php echo $erro; ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Estatísticas Rápidas -->
                <div class="row mb-4">
                    <?php 
                    $status_info = [
                        'pendente' => ['icon' => 'fa-clock', 'color' => 'warning', 'text' => 'Pendente'],
                        'enviado' => ['icon' => 'fa-shipping-fast', 'color' => 'info', 'text' => 'Enviado'],
                        'entregue' => ['icon' => 'fa-flag-checkered', 'color' => 'success', 'text' => 'Entregue'],
                        'cancelado' => ['icon' => 'fa-times', 'color' => 'danger', 'text' => 'Cancelado']
                    ];
                    
                    foreach ($status_info as $status => $info): 
                        $count = $pedidos_por_status[$status] ?? 0;
                    ?>
                    <div class="col-md-3 col-6 mb-3">
                        <div class="stats-card" style="border-left: 4px solid var(--bs-<?php echo $info['color']; ?>);">
                            <div class="d-flex justify-content-between align-items-center">
                                <div>
                                    <h4 class="mb-0"><?php echo $count; ?></h4>
                                    <small class="text-muted"><?php echo $info['text']; ?></small>
                                </div>
                                <i class="fas <?php echo $info['icon']; ?> text-<?php echo $info['color']; ?> fa-2x"></i>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>

                <!-- Filtros Avançados -->
                <?php if ($tipoUsuario === 'admin' || $tipoUsuario === 'representante'): ?>
                <div class="filter-card">
                    <h5><i class="fas fa-filter me-2"></i>Filtros</h5>
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label class="form-label">Status</label>
                            <select name="status" class="form-select">
                                <option value="">Todos os Status</option>
                                <option value="pendente" <?= ($_GET['status'] ?? '') === 'pendente' ? 'selected' : '' ?>>Pendente</option>
                                <option value="enviado" <?= ($_GET['status'] ?? '') === 'enviado' ? 'selected' : '' ?>>Enviado</option>
                                <option value="entregue" <?= ($_GET['status'] ?? '') === 'entregue' ? 'selected' : '' ?>>Entregue</option>
                                <option value="cancelado" <?= ($_GET['status'] ?? '') === 'cancelado' ? 'selected' : '' ?>>Cancelado</option>
                            </select>
                        </div>
                        
                        <?php if ($tipoUsuario === 'admin'): ?>
                        <div class="col-md-3">
                            <label class="form-label">Representante</label>
                            <select name="representante" class="form-select">
                                <option value="">Todos Representantes</option>
                                <?php foreach ($representantes as $rep): ?>
                                    <option value="<?= $rep['idUsuario'] ?>" <?= ($_GET['representante'] ?? '') == $rep['idUsuario'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($rep['nomeUsuario']) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <?php endif; ?>
                        
                        <div class="col-md-3">
                            <label class="form-label">Data Início</label>
                            <input type="date" name="data_inicio" value="<?= $_GET['data_inicio'] ?? '' ?>" class="form-control">
                        </div>
                        <div class="col-md-3">
                            <label class="form-label">Data Fim</label>
                            <input type="date" name="data_fim" value="<?= $_GET['data_fim'] ?? '' ?>" class="form-control">
                        </div>
                        <div class="col-12">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search me-2"></i>Filtrar
                            </button>
                            <a href="gerenciadorPedidos.php" class="btn btn-secondary">
                                <i class="fas fa-times me-2"></i>Limpar
                            </a>
                        </div>
                    </form>
                </div>
                <?php endif; ?>

                <div class="orders-container">
                    <div class="orders-header d-flex justify-content-between align-items-center mb-4">
                        <h2>
                            <?php if ($tipoUsuario === 'lojista'): ?>
                                <i class="fas fa-shopping-cart me-2"></i>Meus Pedidos
                            <?php elseif ($tipoUsuario === 'representante'): ?>
                                <i class="fas fa-store me-2"></i>Pedidos das Minhas Lojas
                            <?php else: ?>
                                <i class="fas fa-list-alt me-2"></i>Todos os Pedidos
                            <?php endif; ?>
                            <span class="badge bg-primary ms-2"><?= $result_pedidos ? mysqli_num_rows($result_pedidos) : 0 ?></span>
                        </h2>
                        <div class="orders-actions">
                            <?php if ($tipoUsuario === 'lojista'): ?>
                                <button class="btn btn-success" onclick="abrirModal('novoPedidoModal')">
                                    <i class="fas fa-cart-plus"></i> Novo Pedido
                                </button>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Tabela de Pedidos -->
                    <div class="orders-table-container">
                        <div class="table-responsive">
                            <table class="orders-table table table-striped table-hover">
                                <thead class="table-dark">
                                    <tr>
                                        <th>ID</th>
                                        <th>Data</th>
                                        <?php if ($tipoUsuario === 'admin' || $tipoUsuario === 'representante'): ?>
                                            <th>Loja</th>
                                            <th>Lojista</th>
                                        <?php endif; ?>
                                        <?php if ($tipoUsuario === 'admin'): ?>
                                            <th>Representante</th>
                                        <?php endif; ?>
                                        <th>Status</th>
                                        <th>Itens</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if ($result_pedidos && mysqli_num_rows($result_pedidos) > 0): ?>
                                        <?php while ($pedido = mysqli_fetch_assoc($result_pedidos)): 
                                            $itens_pedido = buscarItensPedido($conexao, $pedido['idPedidos']);
                                            $total_itens = mysqli_num_rows($itens_pedido);
                                        ?>
                                            <tr>
                                                <td><strong>#<?php echo $pedido['idPedidos']; ?></strong></td>
                                                <td><?php echo date('d/m/Y H:i', strtotime($pedido['dataPedido'])); ?></td>
                                                <?php if ($tipoUsuario === 'admin' || $tipoUsuario === 'representante'): ?>
                                                    <td><?php echo htmlspecialchars($pedido['nomeLoja']); ?></td>
                                                    <td><?php echo htmlspecialchars($pedido['nome_lojista'] ?? '-'); ?></td>
                                                <?php endif; ?>
                                                <?php if ($tipoUsuario === 'admin'): ?>
                                                    <td><?php echo htmlspecialchars($pedido['nome_representante'] ?? '-'); ?></td>
                                                <?php endif; ?>
                                                <td>
                                                    <span class="status-badge status-<?php echo $pedido['status']; ?>">
                                                        <?php 
                                                            $statusText = [
                                                                'pendente' => 'Pendente',
                                                                'enviado' => 'Enviado',
                                                                'entregue' => 'Entregue',
                                                                'cancelado' => 'Cancelado'
                                                            ];
                                                            echo $statusText[$pedido['status']] ?? $pedido['status'];
                                                        ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <span class="badge bg-primary"><?php echo $total_itens; ?> itens</span>
                                                </td>
                                                <td>
                                                    <div class="btn-group btn-group-sm">
                                                        <?php if ($tipoUsuario === 'representante' && $pedido['status'] !== 'entregue' && $pedido['status'] !== 'cancelado'): ?>
                                                            <button class="btn btn-outline-primary" 
                                                                    onclick="abrirModalAtualizarStatus(<?php echo $pedido['idPedidos']; ?>, '<?php echo $pedido['status']; ?>')" 
                                                                    title="Atualizar Status">
                                                                <i class="fas fa-sync-alt"></i>
                                                            </button>
                                                        <?php endif; ?>
                                                        <button class="btn btn-outline-info" 
                                                                onclick="abrirModalDetalhes(<?php echo $pedido['idPedidos']; ?>)" 
                                                                title="Ver Detalhes">
                                                            <i class="fas fa-eye"></i>
                                                        </button>
                                                    </div>
                                                </td>
                                            </tr>
                                        <?php endwhile; ?>
                                    <?php else: ?>
                                        <tr>
                                            <td colspan="<?php 
                                                echo ($tipoUsuario === 'admin') ? '8' : 
                                                     (($tipoUsuario === 'representante') ? '7' : '5'); 
                                            ?>" class="text-center text-muted py-5">
                                                <i class="fas fa-shopping-cart fa-3x mb-3 d-block"></i>
                                                <?php if ($tipoUsuario === 'lojista'): ?>
                                                    Você ainda não fez nenhum pedido
                                                <?php else: ?>
                                                    Nenhum pedido encontrado
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <!-- Modal para Novo Pedido (APENAS LOJISTA) -->
    <?php if ($tipoUsuario === 'lojista'): ?>
    <div class="modal-custom" id="novoPedidoModal">
        <div class="modal-content-custom" style="max-width: 800px;">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-cart-plus me-2"></i>Novo Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('novoPedidoModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="" id="formNovoPedido">
                    <div class="mb-3">
                        <label class="form-label">
                            <i class="fas fa-boxes me-2"></i>Selecione os Produtos *
                        </label>
                        <div class="products-grid" style="max-height: 400px; overflow-y: auto;">
                            <?php if ($result_produtos && mysqli_num_rows($result_produtos) > 0): ?>
                                <?php 
                                $categoria_atual = '';
                                mysqli_data_seek($result_produtos, 0); // Reset pointer
                                while ($produto = mysqli_fetch_assoc($result_produtos)): 
                                    if ($produto['categoria'] !== $categoria_atual): 
                                        $categoria_atual = $produto['categoria'];
                                ?>
                                    <div class="category-header">
                                        <h6 class="mb-2"><?php echo htmlspecialchars($categoria_atual); ?></h6>
                                    </div>
                                <?php endif; ?>
                                <div class="product-item">
                                    <div class="product-info d-flex align-items-center">
                                        <?php if (!empty($produto['imagem_url'])): ?>
                                            <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                                                 alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>" 
                                                 class="product-image-small">
                                        <?php else: ?>
                                            <div class="product-image-small bg-light d-flex align-items-center justify-content-center">
                                                <i class="fas fa-box text-muted"></i>
                                            </div>
                                        <?php endif; ?>
                                        <span class="product-name"><?php echo htmlspecialchars($produto['nomeProduto']); ?></span>
                                    </div>
                                    <div class="product-quantity">
                                        <input type="number" 
                                               name="produtos[<?php echo $produto['idprodutos']; ?>]" 
                                               class="form-control form-control-sm" 
                                               min="0" 
                                               max="100" 
                                               value="0"
                                               onchange="calcularTotal()"
                                               style="width: 80px;">
                                    </div>
                                </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <p class="text-muted text-center py-3">Nenhum produto disponível</p>
                            <?php endif; ?>
                        </div>
                    </div>

                    <div class="total-section alert alert-info">
                        <strong>Total de Itens Selecionados: <span id="totalItens">0</span></strong>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('novoPedidoModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="criar_pedido" id="btnEnviarPedido">
                            <i class="fas fa-paper-plane me-2"></i>Enviar Pedido
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para Atualizar Status (apenas representante) -->
    <?php if ($tipoUsuario === 'representante'): ?>
    <div class="modal-custom" id="atualizarStatusModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-sync-alt me-2"></i>Atualizar Status do Pedido
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('atualizarStatusModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="">
                    <input type="hidden" name="idPedidos" id="edit_idPedidos">
                    
                    <div class="mb-3">
                        <label for="novoStatus" class="form-label">
                            <i class="fas fa-tag me-2"></i>Novo Status *
                        </label>
                        <select class="form-control" id="novoStatus" name="novoStatus" required>
                            <option value="pendente">Pendente</option>
                            <option value="enviado">Enviado</option>
                            <option value="entregue">Entregue</option>
                            <option value="cancelado">Cancelado</option>
                        </select>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('atualizarStatusModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="atualizar_status">
                            <i class="fas fa-save me-2"></i>Atualizar Status
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Modal para Ver Detalhes -->
    <div class="modal-custom" id="detalhesPedidoModal">
        <div class="modal-content-custom" style="max-width: 700px;">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-eye me-2"></i>Detalhes do Pedido #<span id="detalhes_id"></span>
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('detalhesPedidoModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <div id="detalhesConteudo">
                    <!-- Conteúdo carregado via JavaScript -->
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal('detalhesPedidoModal')">
                        <i class="fas fa-times me-2"></i>Fechar
                    </button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            
            if (modalId === 'novoPedidoModal') {
                document.getElementById('formNovoPedido').reset();
                calcularTotal();
            }
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function abrirModalAtualizarStatus(idPedidos, statusAtual) {
            document.getElementById('edit_idPedidos').value = idPedidos;
            document.getElementById('novoStatus').value = statusAtual;
            abrirModal('atualizarStatusModal');
        }

        function abrirModalDetalhes(idPedidos) {
            document.getElementById('detalhes_id').textContent = idPedidos;
            
            fetch('carregar_detalhes_pedido.php?id=' + idPedidos)
                .then(response => response.text())
                .then(html => {
                    document.getElementById('detalhesConteudo').innerHTML = html;
                    abrirModal('detalhesPedidoModal');
                })
                .catch(error => {
                    document.getElementById('detalhesConteudo').innerHTML = 
                        '<div class="alert alert-danger">Erro ao carregar detalhes do pedido</div>';
                    abrirModal('detalhesPedidoModal');
                });
        }

        function calcularTotal() {
            let total = 0;
            const inputs = document.querySelectorAll('input[name^="produtos"]');
            
            inputs.forEach(input => {
                total += parseInt(input.value) || 0;
            });
            
            document.getElementById('totalItens').textContent = total;
            
            // Habilitar/desabilitar botão de enviar
            const btnEnviar = document.getElementById('btnEnviarPedido');
            if (btnEnviar) {
                btnEnviar.disabled = total === 0;
            }
        }

        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal-custom');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        document.addEventListener('DOMContentLoaded', function() {
            calcularTotal();
            
            // Auto-fechar alerts após 5 segundos
            const alerts = document.querySelectorAll('.alert');
            alerts.forEach(alert => {
                setTimeout(() => {
                    if (alert.parentNode) {
                        const bsAlert = new bootstrap.Alert(alert);
                        bsAlert.close();
                    }
                }, 5000);
            });
        });
    </script>
</body>
</html>

<?php
    if (isset($conexao)) {
        mysqli_close($conexao);
    }
?>