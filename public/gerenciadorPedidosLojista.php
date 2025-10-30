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

// Verificar se é lojista
if ($tipoUsuario !== 'lojista') {
    header("Location: login.php?erro=Acesso não autorizado para lojista");
    exit();
}

$voltar = "loja.php";

// Conexão com banco
require_once "../config/connection.php";
$conexao = conectarBD();

// Buscar a loja do usuário logado e o representante vinculado
$sql_loja = "SELECT l.idlojas, l.nomeLoja, l.representante_id, r.nomeUsuario as nome_representante, r.emailUsuario as email_representante
             FROM lojas l 
             LEFT JOIN usuario r ON l.representante_id = r.idUsuario
             WHERE l.idlojas = (SELECT loja_id FROM usuario WHERE idUsuario = ?)";
$stmt_loja = mysqli_prepare($conexao, $sql_loja);
mysqli_stmt_bind_param($stmt_loja, "i", $idUsuario);
mysqli_stmt_execute($stmt_loja);
$result_loja = mysqli_stmt_get_result($stmt_loja);
$loja = mysqli_fetch_assoc($result_loja);

// Se não encontrou loja, mostrar mensagem amigável
if (!$loja) {
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Loja não Configurada - System Pump</title>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="./css/loja.css">
        <link rel="stylesheet" href="./css/gerenciadorPedidosLojista.css">
        <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    </head>
    <body>
        <div class="admin-container">
            <main class="main-content">
                <header class="header d-flex justify-content-between align-items-center">
                    <div class="d-flex align-items-center gap-3">
                        <a href="<?php echo $voltar; ?>" class="btn btn-secondary">
                            <i class="fas fa-arrow-left"></i> Voltar
                        </a>
                        <h1 class="mb-0">Fazer Pedido</h1> 
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

                <div class="page-content">
                    <div class="alert alert-warning text-center py-5">
                        <i class="fas fa-store fa-3x mb-3 text-warning"></i>
                        <h3>Loja não configurada</h3>
                        <p class="mb-3">Sua conta de lojista ainda não foi vinculada a uma loja no sistema.</p>
                        <p class="text-muted">Entre em contato com seu representante comercial para configurar sua loja.</p>
                        <div class="mt-4">
                            <a href="loja.php" class="btn btn-primary">
                                <i class="fas fa-home me-2"></i>Voltar para Home
                            </a>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </body>
    </html>
    <?php
    exit();
}

$idLoja = $loja['idlojas'];
$nomeLoja = $loja['nomeLoja'];
$idRepresentante = $loja['representante_id'];

// Inicializar carrinho se não existir - CORREÇÃO CRÍTICA
if (!isset($_SESSION['carrinho'])) {
    $_SESSION['carrinho'] = [];
}

// Buscar produtos ativos
$sql_produtos = "SELECT idprodutos, nomeProduto, categoria, imagem_url, descProduto 
                 FROM produtos WHERE ativoProduto = 1 ORDER BY categoria, nomeProduto";
$result_produtos = mysqli_query($conexao, $sql_produtos);

// DEBUG: Verificar estado do carrinho
error_log("Carrinho antes de processar: " . print_r($_SESSION['carrinho'], true));

// Processar adição ao carrinho - CORREÇÃO CRÍTICA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['adicionar_carrinho'])) {
    $produto_id = intval($_POST['produto_id'] ?? '');
    $quantidade = intval($_POST['quantidade'] ?? 1);
    
    error_log("Tentando adicionar produto: $produto_id, quantidade: $quantidade");
    
    if (!empty($produto_id) && $quantidade > 0) {
        // Buscar informações completas do produto
        $sql_produto_info = "SELECT nomeProduto, categoria, imagem_url FROM produtos WHERE idprodutos = ?";
        $stmt_produto = mysqli_prepare($conexao, $sql_produto_info);
        mysqli_stmt_bind_param($stmt_produto, "i", $produto_id);
        mysqli_stmt_execute($stmt_produto);
        $produto_info = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt_produto));
        
        if ($produto_info) {
            // Verificar se produto já está no carrinho - CORREÇÃO CRÍTICA
            $produto_encontrado = false;
            $novo_carrinho = [];
            
            foreach ($_SESSION['carrinho'] as $item) {
                if ($item['produto_id'] == $produto_id) {
                    // Se já existe, atualiza a quantidade
                    $item['quantidade'] += $quantidade;
                    $produto_encontrado = true;
                    error_log("Produto encontrado, nova quantidade: " . $item['quantidade']);
                }
                $novo_carrinho[] = $item;
            }
            
            if (!$produto_encontrado) {
                // Se não encontrou, adiciona novo item
                $novo_carrinho[] = [
                    'produto_id' => $produto_id,
                    'nome' => $produto_info['nomeProduto'],
                    'categoria' => $produto_info['categoria'],
                    'imagem' => $produto_info['imagem_url'],
                    'quantidade' => $quantidade
                ];
                error_log("Novo produto adicionado ao carrinho");
            }
            
            $_SESSION['carrinho'] = $novo_carrinho;
            $sucesso = "Produto adicionado ao carrinho!";
            
            error_log("Carrinho após adição: " . print_r($_SESSION['carrinho'], true));
        }
    }
}

// Processar remoção do carrinho
if (isset($_GET['remover_carrinho'])) {
    $produto_id = intval($_GET['remover_carrinho']);
    $novo_carrinho = [];
    
    foreach ($_SESSION['carrinho'] as $item) {
        if ($item['produto_id'] != $produto_id) {
            $novo_carrinho[] = $item;
        }
    }
    
    $_SESSION['carrinho'] = $novo_carrinho;
    $sucesso = "Produto removido do carrinho!";
    
    // Redirecionar para evitar reenvio
    header("Location: gerenciadorPedidosLojista.php");
    exit();
}

// Processar atualização de quantidade - CORREÇÃO CRÍTICA
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_quantidade'])) {
    $produto_id = intval($_POST['produto_id'] ?? '');
    $nova_quantidade = intval($_POST['quantidade'] ?? 0);
    
    error_log("Atualizando quantidade - Produto: $produto_id, Nova quantidade: $nova_quantidade");
    
    if (!empty($produto_id)) {
        $novo_carrinho = [];
        $item_atualizado = false;
        
        foreach ($_SESSION['carrinho'] as $item) {
            if ($item['produto_id'] == $produto_id) {
                if ($nova_quantidade > 0) {
                    $item['quantidade'] = $nova_quantidade;
                    $sucesso = "Quantidade atualizada!";
                    error_log("Quantidade atualizada para: " . $nova_quantidade);
                } else {
                    // Não adiciona o item (remove)
                    $sucesso = "Produto removido do carrinho!";
                    error_log("Produto removido do carrinho");
                    continue; // Pula este item (não adiciona ao novo carrinho)
                }
                $item_atualizado = true;
            }
            $novo_carrinho[] = $item;
        }
        
        $_SESSION['carrinho'] = $novo_carrinho;
        
        if (!$item_atualizado) {
            error_log("Produto não encontrado para atualização");
        }
        
        error_log("Carrinho após atualização: " . print_r($_SESSION['carrinho'], true));
        
        // Redirecionar para evitar reenvio
        header("Location: gerenciadorPedidosLojista.php");
        exit();
    }
}

// Processar limpeza do carrinho
if (isset($_GET['limpar_carrinho'])) {
    $_SESSION['carrinho'] = [];
    $sucesso = "Carrinho limpo com sucesso!";
    
    // Redirecionar para evitar reenvio do formulário
    header("Location: gerenciadorPedidosLojista.php");
    exit();
}

// Processar finalização do pedido
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['finalizar_pedido'])) {
    if (!empty($_SESSION['carrinho'])) {
        // Inserir pedido vinculado ao representante
        $sql_pedido = "INSERT INTO pedidos (lojas_idlojas, status, representante_id) VALUES (?, 'pendente', ?)";
        $stmt_pedido = mysqli_prepare($conexao, $sql_pedido);
        mysqli_stmt_bind_param($stmt_pedido, "ii", $idLoja, $idRepresentante);
        
        if (mysqli_stmt_execute($stmt_pedido)) {
            $idPedidos = mysqli_insert_id($conexao);
            
            // Inserir itens do pedido
            foreach ($_SESSION['carrinho'] as $item) {
                $sql_item = "INSERT INTO pedido_itens (quantPedItens, pedidos_idPedidos, produtos_idProdutos) 
                            VALUES (?, ?, ?)";
                $stmt_item = mysqli_prepare($conexao, $sql_item);
                mysqli_stmt_bind_param($stmt_item, "iii", $item['quantidade'], $idPedidos, $item['produto_id']);
                mysqli_stmt_execute($stmt_item);
            }
            
            // Limpar carrinho
            $_SESSION['carrinho'] = [];
            
            $sucesso = "Pedido #$idPedidos enviado para seu representante com sucesso!";
            
            // Redirecionar para evitar reenvio do formulário
            header("Location: gerenciadorPedidosLojista.php?sucesso=" . urlencode("Pedido #$idPedidos enviado para seu representante com sucesso!"));
            exit();
        } else {
            $erro = "Erro ao criar pedido: " . mysqli_error($conexao);
        }
    } else {
        $erro = "Carrinho vazio! Adicione produtos antes de finalizar o pedido.";
    }
}

// Buscar pedidos anteriores da loja
$sql_pedidos = "SELECT p.*, COUNT(pi.idPedItens) as total_itens 
                FROM pedidos p 
                LEFT JOIN pedido_itens pi ON p.idPedidos = pi.pedidos_idPedidos 
                WHERE p.lojas_idlojas = ? 
                GROUP BY p.idPedidos 
                ORDER BY p.dataPedido DESC 
                LIMIT 10";
$stmt_pedidos = mysqli_prepare($conexao, $sql_pedidos);
mysqli_stmt_bind_param($stmt_pedidos, "i", $idLoja);
mysqli_stmt_execute($stmt_pedidos);
$result_pedidos = mysqli_stmt_get_result($stmt_pedidos);

// Calcular total do carrinho
$total_carrinho = 0;
$total_itens_carrinho = 0;

if (isset($_SESSION['carrinho']) && is_array($_SESSION['carrinho'])) {
    $total_itens_carrinho = count($_SESSION['carrinho']);
    foreach ($_SESSION['carrinho'] as $item) {
        $total_carrinho += intval($item['quantidade']);
    }
}

// Processar mensagem de sucesso via GET
if (isset($_GET['sucesso'])) {
    $sucesso = $_GET['sucesso'];
}

// DEBUG: Verificar estado final do carrinho
error_log("Carrinho final: " . print_r($_SESSION['carrinho'], true));
error_log("Total itens carrinho: $total_itens_carrinho, Total unidades: $total_carrinho");
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fazer Pedido - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/lojista.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link rel="stylesheet" href="./css/gerenciadorPedidosLojista.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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
                    <h1 class="mb-0">Fazer Pedido - <?php echo htmlspecialchars($nomeLoja); ?></h1> 
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
                <!-- Informações do Representante -->
                <?php if ($loja['nome_representante']): ?>
                <div class="representante-info">
                    <h5><i class="fas fa-user-tie me-2"></i>Seu Representante</h5>
                    <p class="mb-1"><strong>Nome:</strong> <?php echo htmlspecialchars($loja['nome_representante']); ?></p>
                    <p class="mb-0"><strong>Email:</strong> <?php echo htmlspecialchars($loja['email_representante']); ?></p>
                    <p class="mb-0 small">Seus pedidos serão enviados diretamente para este representante</p>
                </div>
                <?php endif; ?>

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

                <div class="row">
                    <!-- Lista de Produtos -->
                    <div class="col-lg-8">
                        <div class="produtos-main">
                            <h3 class="section-title">
                                <i class="fas fa-boxes me-2"></i>Catálogo de Produtos
                            </h3>
                            
                            <?php if ($result_produtos && mysqli_num_rows($result_produtos) > 0): ?>
                                <div class="categorias-container">
                                    <?php 
                                    $produtos_por_categoria = [];
                                    
                                    // Agrupar produtos por categoria
                                    mysqli_data_seek($result_produtos, 0);
                                    while ($produto = mysqli_fetch_assoc($result_produtos)) {
                                        $produtos_por_categoria[$produto['categoria']][] = $produto;
                                    }
                                    
                                    foreach ($produtos_por_categoria as $categoria => $produtos): 
                                    ?>
                                        <div class="categoria-section">
                                            <h5 class="categoria-titulo"><?php echo htmlspecialchars($categoria); ?></h5>
                                            <div class="produtos-grid">
                                                <?php foreach ($produtos as $produto): ?>
                                                    <div class="produto-card">
                                                        <div class="produto-info">
                                                            <?php if (!empty($produto['imagem_url'])): ?>
                                                                <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                                                                     alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>" 
                                                                     class="produto-imagem">
                                                            <?php else: ?>
                                                                <div class="produto-sem-imagem">
                                                                    <i class="fas fa-box"></i>
                                                                </div>
                                                            <?php endif; ?>
                                                            
                                                            <div class="produto-detalhes">
                                                                <h6 class="produto-nome"><?php echo htmlspecialchars($produto['nomeProduto']); ?></h6>
                                                                
                                                                <?php if (!empty($produto['descProduto'])): ?>
                                                                    <p class="produto-descricao">
                                                                        <?php echo htmlspecialchars(substr($produto['descProduto'], 0, 100)); ?>
                                                                        <?php if (strlen($produto['descProduto']) > 100): ?>...<?php endif; ?>
                                                                    </p>
                                                                <?php endif; ?>
                                                                
                                                                <form method="POST" action="" class="produto-form">
                                                                    <input type="hidden" name="produto_id" value="<?php echo $produto['idprodutos']; ?>">
                                                                    <div class="quantidade-controles">
                                                                        <button type="button" class="btn-quantidade btn-diminuir" onclick="diminuirQuantidade(this)">
                                                                            <i class="fas fa-minus"></i>
                                                                        </button>
                                                                        <input type="number" name="quantidade" value="1" min="1" max="100" 
                                                                               class="quantidade-input" required>
                                                                        <button type="button" class="btn-quantidade btn-aumentar" onclick="aumentarQuantidade(this)">
                                                                            <i class="fas fa-plus"></i>
                                                                        </button>
                                                                        <button type="submit" name="adicionar_carrinho" class="btn-adicionar">
                                                                            <i class="fas fa-cart-plus me-1"></i>
                                                                            Adicionar
                                                                        </button>
                                                                    </div>
                                                                </form>
                                                            </div>
                                                        </div>
                                                    </div>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php else: ?>
                                <div class="text-center text-muted py-5">
                                    <i class="fas fa-box fa-3x mb-3"></i>
                                    <p>Nenhum produto disponível no momento.</p>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Carrinho de Compras -->
                    <div class="col-lg-4">
                        <div class="carrinho-sidebar">
                            <div class="carrinho-header">
                                <h4 class="carrinho-titulo">
                                    <i class="fas fa-shopping-cart me-2"></i>Meu Carrinho
                                    <?php if ($total_itens_carrinho > 0): ?>
                                        <span class="badge bg-primary ms-1"><?php echo $total_itens_carrinho; ?></span>
                                    <?php endif; ?>
                                </h4>
                                <?php if ($total_itens_carrinho > 0): ?>
                                    <div class="carrinho-total">
                                        Total: <strong><?php echo $total_carrinho; ?> unidades</strong>
                                    </div>
                                <?php endif; ?>
                            </div>
                            
                            <?php if ($total_itens_carrinho > 0): ?>
                                <div class="carrinho-itens">
                                    <?php foreach ($_SESSION['carrinho'] as $item): ?>
                                        <div class="carrinho-item">
                                            <div class="carrinho-item-info">
                                                <?php if (!empty($item['imagem'])): ?>
                                                    <img src="<?php echo htmlspecialchars($item['imagem']); ?>" 
                                                         alt="<?php echo htmlspecialchars($item['nome']); ?>" 
                                                         class="carrinho-item-imagem">
                                                <?php else: ?>
                                                    <div class="carrinho-item-sem-imagem">
                                                        <i class="fas fa-box"></i>
                                                    </div>
                                                <?php endif; ?>
                                                <div class="carrinho-item-detalhes">
                                                    <strong class="carrinho-item-nome"><?php echo htmlspecialchars($item['nome']); ?></strong>
                                                    <span class="carrinho-item-categoria"><?php echo htmlspecialchars($item['categoria']); ?></span>
                                                    <div class="carrinho-item-id small text-muted">
                                                        ID: <?php echo $item['produto_id']; ?>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="carrinho-item-controles">
                                                <form method="POST" action="" class="carrinho-quantity-form">
                                                    <input type="hidden" name="produto_id" value="<?php echo $item['produto_id']; ?>">
                                                    <div class="quantidade-carrinho">
                                                        <button type="button" class="btn-quantidade-carrinho" 
                                                                onclick="diminuirQuantidadeCarrinho('<?php echo $item['produto_id']; ?>')">
                                                            <i class="fas fa-minus"></i>
                                                        </button>
                                                        <span class="quantidade-display" id="quantidade-<?php echo $item['produto_id']; ?>">
                                                            <?php echo $item['quantidade']; ?>
                                                        </span>
                                                        <button type="button" class="btn-quantidade-carrinho" 
                                                                onclick="aumentarQuantidadeCarrinho('<?php echo $item['produto_id']; ?>')">
                                                            <i class="fas fa-plus"></i>
                                                        </button>
                                                    </div>
                                                    <input type="hidden" name="atualizar_quantidade" value="1">
                                                </form>
                                                <a href="?remover_carrinho=<?php echo $item['produto_id']; ?>" class="btn-remover" 
                                                   title="Remover" onclick="return confirm('Tem certeza que deseja remover este produto?')">
                                                    <i class="fas fa-trash"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                                
                                <div class="carrinho-acoes">
                                    <form method="POST" action="">
                                        <button type="submit" name="finalizar_pedido" class="btn-finalizar-pedido">
                                            <i class="fas fa-paper-plane me-2"></i>Enviar Pedido
                                        </button>
                                    </form>
                                    <a href="?limpar_carrinho=1" class="btn-limpar-carrinho" 
                                       onclick="return confirm('Tem certeza que deseja limpar todo o carrinho?')">
                                        <i class="fas fa-trash me-2"></i>Limpar Carrinho
                                    </a>
                                </div>
                            <?php else: ?>
                                <div class="carrinho-vazio">
                                    <i class="fas fa-shopping-cart fa-2x mb-3"></i>
                                    <p>Seu carrinho está vazio</p>
                                    <p class="small">Adicione produtos do catálogo</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Pedidos Recentes -->
                        <div class="pedidos-recentes">
                            <h5 class="section-title">
                                <i class="fas fa-history me-2"></i>Meus Pedidos Recentes
                            </h5>
                            
                            <?php if ($result_pedidos && mysqli_num_rows($result_pedidos) > 0): ?>
                                <div class="lista-pedidos">
                                    <?php while ($pedido = mysqli_fetch_assoc($result_pedidos)): ?>
                                        <div class="pedido-item">
                                            <div class="pedido-info">
                                                <strong>Pedido #<?php echo $pedido['idPedidos']; ?></strong>
                                                <div class="pedido-data">
                                                    <?php echo date('d/m/Y H:i', strtotime($pedido['dataPedido'])); ?>
                                                </div>
                                            </div>
                                            <div class="pedido-status">
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
                                                <div class="pedido-itens">
                                                    <?php echo $pedido['total_itens']; ?> itens
                                                </div>
                                            </div>
                                            <div class="pedido-actions mt-2">
                                                <button class="btn btn-sm btn-outline-info" onclick="abrirModalDetalhesPedido(<?php echo $pedido['idPedidos']; ?>)">
                                                    <i class="fas fa-eye me-1"></i>Detalhes
                                                </button>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                </div>
                            <?php else: ?>
                                <p class="text-muted small">Nenhum pedido realizado ainda.</p>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <!-- Modal para Detalhes do Pedido -->
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
        function aumentarQuantidade(btn) {
            const input = btn.parentElement.querySelector('.quantidade-input');
            input.value = parseInt(input.value) + 1;
        }

        function diminuirQuantidade(btn) {
            const input = btn.parentElement.querySelector('.quantidade-input');
            if (parseInt(input.value) > 1) {
                input.value = parseInt(input.value) - 1;
            }
        }

        function aumentarQuantidadeCarrinho(produtoId) {
            const display = document.getElementById('quantidade-' + produtoId);
            let quantidade = parseInt(display.textContent);
            quantidade += 1;
            atualizarQuantidadeNoCarrinho(produtoId, quantidade);
        }

        function diminuirQuantidadeCarrinho(produtoId) {
            const display = document.getElementById('quantidade-' + produtoId);
            let quantidade = parseInt(display.textContent);
            if (quantidade > 1) {
                quantidade -= 1;
                atualizarQuantidadeNoCarrinho(produtoId, quantidade);
            } else {
                if (confirm('Deseja remover este produto do carrinho?')) {
                    window.location.href = '?remover_carrinho=' + produtoId;
                }
            }
        }

        function atualizarQuantidadeNoCarrinho(produtoId, quantidade) {
            // Criar um formulário temporário para enviar os dados
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '';
            form.style.display = 'none';
            
            // Adicionar campo produto_id
            const produtoIdInput = document.createElement('input');
            produtoIdInput.type = 'hidden';
            produtoIdInput.name = 'produto_id';
            produtoIdInput.value = produtoId;
            form.appendChild(produtoIdInput);
            
            // Adicionar campo quantidade
            const quantidadeInput = document.createElement('input');
            quantidadeInput.type = 'hidden';
            quantidadeInput.name = 'quantidade';
            quantidadeInput.value = quantidade;
            form.appendChild(quantidadeInput);
            
            // Adicionar campo de ação
            const acaoInput = document.createElement('input');
            acaoInput.type = 'hidden';
            acaoInput.name = 'atualizar_quantidade';
            acaoInput.value = '1';
            form.appendChild(acaoInput);
            
            // Adicionar formulário ao body e submeter
            document.body.appendChild(form);
            form.submit();
        }

        function abrirModalDetalhesPedido(idPedidos) {
            document.getElementById('detalhes_id').textContent = idPedidos;
            
            fetch('carregar_detalhes_pedido_lojista.php?id=' + idPedidos)
                .then(response => {
                    if (!response.ok) {
                        throw new Error('Erro na requisição: ' + response.status);
                    }
                    return response.text();
                })
                .then(html => {
                    document.getElementById('detalhesConteudo').innerHTML = html;
                })
                .catch(error => {
                    console.error('Erro:', error);
                    document.getElementById('detalhesConteudo').innerHTML = 
                        '<div class="alert alert-danger">' +
                        '<i class="fas fa-exclamation-triangle me-2"></i>' +
                        'Erro ao carregar detalhes do pedido.' +
                        '</div>';
                });
        }

        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            document.body.style.overflow = 'hidden';
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
            document.body.style.overflow = 'auto';
        }

        document.addEventListener('DOMContentLoaded', function() {
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

        // Fechar modal ao clicar fora ou pressionar ESC
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal-custom');
            modals.forEach(modal => {
                if (event.target === modal) {
                    fecharModal(modal.id);
                }
            });
        }

        // Fechar modal com tecla ESC
        document.addEventListener('keydown', function(event) {
            if (event.key === 'Escape') {
                const modals = document.querySelectorAll('.modal-custom');
                modals.forEach(modal => {
                    if (modal.style.display === 'block') {
                        fecharModal(modal.id);
                    }
                });
            }
        });
    </script>
</body>
</html>

<?php
    if (isset($conexao)) {
        mysqli_close($conexao);
    }
?>