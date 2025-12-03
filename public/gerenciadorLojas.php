<?php
session_start();

// Verificação simples de sessão
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];
$nomeUsuario = $usuario['nomeUsuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';
$idUsuario = $usuario['idUsuario'];

// Verificar se o usuário é admin OU representante
if ($tipoUsuario !== 'admin' && $tipoUsuario !== 'representante') {
    header("Location: login.php?erro=Acesso não autorizado");
    exit();
}

// Definir página de voltar baseada no tipo de usuário
if ($tipoUsuario === 'admin') {
    $voltar = "adm.php";
} else {
    $voltar = "representante.php";
}

// Conexão com banco
require_once "../config/connection.php";
$conexao = conectarBD();

// Variáveis para filtros
$filtro_nome = $_GET['filtro_nome'] ?? '';
$filtro_cidade = $_GET['filtro_cidade'] ?? '';

// Verificar se há solicitação aceita para pré-preencher formulário
$solicitacao_aceita = null;
if (isset($_GET['criar_loja']) && isset($_SESSION['solicitacao_aceita'])) {
    $solicitacao_aceita = $_SESSION['solicitacao_aceita'];
}

// Buscar lojas para a tabela com filtros
$where_conditions = [];
$params = [];
$types = "";

// FILTRO POR TIPO DE USUÁRIO
if ($tipoUsuario === 'representante') {
    $where_conditions[] = "l.representante_id = ?";
    $params[] = $idUsuario;
    $types .= "i";
}

if (!empty($filtro_nome)) {
    $where_conditions[] = "l.nomeloja LIKE ?";
    $params[] = "%$filtro_nome%";
    $types .= "s";
}

if (!empty($filtro_cidade)) {
    $where_conditions[] = "l.cidade_idCidade = ?";
    $params[] = $filtro_cidade;
    $types .= "i";
}

$where_sql = "";
if (!empty($where_conditions)) {
    $where_sql = "WHERE " . implode(" AND ", $where_conditions);
}

// CORREÇÃO: Removido l.Usuario_idUsuario da query
$sql_lojas = "SELECT 
            l.idlojas,
            l.nomeLoja,
            l.telefoneloja, 
            l.emailloja,
            l.fotoLoja,
            l.cep,
            l.logradouro,
            l.numero,
            l.bairro,
            l.lojaCriadoEm,
            l.cidade_idCidade,
            l.representante_id,
            c.nomeCidade, 
            e.siglaEstado, 
            u.nomeUsuario as nome_lojista,
            rep.nomeUsuario as nome_representante
        FROM lojas l 
        INNER JOIN cidade c ON l.cidade_idCidade = c.idCidade 
        INNER JOIN estado e ON c.estado_idEstado = e.idEstado
        LEFT JOIN usuario u ON u.loja_id = l.idlojas AND u.tipoUsuario = 'lojista'  -- CORREÇÃO: Buscar lojista pelo loja_id
        LEFT JOIN usuario rep ON l.representante_id = rep.idUsuario
        $where_sql
        ORDER BY l.idlojas DESC 
        LIMIT 50";

$stmt_lojas = mysqli_prepare($conexao, $sql_lojas);
if (!empty($params)) {
    mysqli_stmt_bind_param($stmt_lojas, $types, ...$params);
}
mysqli_stmt_execute($stmt_lojas);
$result_lojas = mysqli_stmt_get_result($stmt_lojas);

// Buscar cidades para os selects - filtrar por representante se for representante
if ($tipoUsuario === 'representante') {
    $sql_cidades = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, e.idEstado 
                    FROM cidade c 
                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                    WHERE c.representante_id = ?
                    ORDER BY e.nomeEstado, c.nomeCidade";
    $stmt_cidades = mysqli_prepare($conexao, $sql_cidades);
    mysqli_stmt_bind_param($stmt_cidades, "i", $idUsuario);
    mysqli_stmt_execute($stmt_cidades);
    $result_cidades = mysqli_stmt_get_result($stmt_cidades);
} else {
    // Admin vê todas as cidades (será filtrado via JavaScript quando selecionar representante)
    $sql_cidades = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, e.idEstado 
                    FROM cidade c 
                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                    ORDER BY e.nomeEstado, c.nomeCidade";
    $result_cidades = mysqli_query($conexao, $sql_cidades);
}

// Buscar estados para criar nova cidade
$sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
$result_estados = mysqli_query($conexao, $sql_estados);

// Buscar loja específica para edição (com verificação de permissão)
$loja_edicao = null;
if (isset($_GET['editar'])) {
    $idlojas = $_GET['editar'];
    
    // Query base para edição
    $sql_loja = "SELECT l.*, c.nomeCidade, e.siglaEstado 
                FROM lojas l 
                INNER JOIN cidade c ON l.cidade_idCidade = c.idCidade 
                INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                WHERE l.idlojas = ?";
    
    // Se for representante, só pode editar suas próprias lojas
    if ($tipoUsuario === 'representante') {
        $sql_loja .= " AND l.representante_id = ?";  // CORREÇÃO: Verificar por representante_id
        $stmt_loja = mysqli_prepare($conexao, $sql_loja);
        mysqli_stmt_bind_param($stmt_loja, "ii", $idlojas, $idUsuario);
    } else {
        $stmt_loja = mysqli_prepare($conexao, $sql_loja);
        mysqli_stmt_bind_param($stmt_loja, "i", $idlojas);
    }
    
    mysqli_stmt_execute($stmt_loja);
    $result_loja = mysqli_stmt_get_result($stmt_loja);
    $loja_edicao = mysqli_fetch_assoc($result_loja);
    
    // Se não encontrou a loja ou não tem permissão, redireciona
    if (!$loja_edicao) {
        $_SESSION['erro'] = "Loja não encontrada ou você não tem permissão para editá-la";
        header("Location: gerenciadorLojas.php");
        exit();
    }
}

// Recuperar mensagens da sessão
$sucesso = $_SESSION['sucesso'] ?? '';
$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['sucesso'], $_SESSION['erro']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Lojas - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link rel="stylesheet" href="./css/gerenciadorLojas.css">
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
                    <h1 class="mb-0">Gerenciar Lojas</h1>
                    <?php if ($tipoUsuario === 'representante'): ?>
                        <span class="badge bg-info">Minhas Lojas</span>
                    <?php endif; ?>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                alt="Avatar do <?= htmlspecialchars($nomeUsuario) ?>" 
                                class="user-avatar-img" 
                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
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
                <?php if (!empty($sucesso)): ?>
                    <div class="alert-custom alert-success"> <?php echo $sucesso; ?></div>
                <?php endif; ?>
                <?php if (!empty($erro)): ?>
                    <div class="alert-custom alert-error"> <?php echo $erro; ?></div>
                <?php endif; ?>

                <div class="stores-container">
                    <div class="stores-header d-flex justify-content-between align-items-center mb-4">
                        <h2>
                            <?php if ($tipoUsuario === 'admin'): ?>
                                Todas as Lojas do Sistema
                            <?php else: ?>
                                Minhas Lojas Cadastradas
                            <?php endif; ?>
                        </h2>
                        <div class="stores-actions d-flex gap-2">
                            <button class="btn btn-primary" onclick="toggleFiltros()">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                            <button class="btn btn-success" onclick="abrirModal('addLojaModal')">
                                <i class="fas fa-plus"></i> Nova Loja
                            </button>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="filtros-container" id="filtrosContainer">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-5">
                                <label for="filtro_nome" class="form-label">Nome da Loja</label>
                                <input type="text" class="form-control" id="filtro_nome" name="filtro_nome" 
                                       value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Filtrar por nome...">
                            </div>
                            <div class="col-md-5">
                                <label for="filtro_cidade" class="form-label">Cidade</label>
                                <select class="form-control" id="filtro_cidade" name="filtro_cidade">
                                    <option value="">Todas as cidades</option>
                                    <?php 
                                    mysqli_data_seek($result_cidades, 0);
                                    if ($result_cidades): ?>
                                        <?php while ($cidade = mysqli_fetch_assoc($result_cidades)): ?>
                                            <option value="<?php echo $cidade['idCidade']; ?>" 
                                                <?php echo ($filtro_cidade == $cidade['idCidade']) ? 'selected' : ''; ?>>
                                                <?php echo $cidade['nomeCidade'] . ' - ' . $cidade['siglaEstado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="?" class="btn btn-secondary">
                                    <i class="fas fa-times"></i> Limpar
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Tabela de Lojas -->
                    <div class="stores-table-container">
                        <table class="lojas-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Logo</th>
                                    <th>Nome da Loja</th>
                                    <th>Contato</th>
                                    <th>Localização</th>
                                    <th>Endereço</th>
                                   <?php if ($tipoUsuario === 'admin'): ?>
                                        <th>Lojista</th>
                                        <th>Representante</th>
                                    <?php endif; ?>
                                    <th>Data Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_lojas && mysqli_num_rows($result_lojas) > 0): ?>
                                    <?php while ($loja = mysqli_fetch_assoc($result_lojas)): ?>
                                        <tr>
                                            <td><?php echo $loja['idlojas']; ?></td>
                                            <td>
                                                <?php if (!empty($loja['fotoLoja'])): ?>
                                                    <img src="<?php echo htmlspecialchars($loja['fotoLoja']); ?>" alt="Logo da Loja" style="width: 50px; height: 50px; object-fit: cover; border-radius: 8px;">
                                                <?php else: ?>
                                                    <div class="text-muted" style="width: 50px; height: 50px; display: flex; align-items: center; justify-content: center; background: #f8f9fa; border-radius: 8px;">
                                                        <i class="fas fa-store"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                           <td>
                                                <strong>
                                                    <?php 
                                                    if (!empty($loja['nomeLoja'])) {
                                                        echo htmlspecialchars($loja['nomeLoja']);
                                                    } else {
                                                        echo '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> Nome não definido</span>';
                                                    }
                                                    ?>
                                                </strong>
                                            </td>
                                            <td class="contact-info">
                                                <?php if (!empty($loja['telefoneloja'])): ?>
                                                    <div><i class="fas fa-phone"></i> <?php echo htmlspecialchars($loja['telefoneloja']); ?></div>
                                                <?php endif; ?>
                                                <?php if (!empty($loja['emailloja'])): ?>
                                                    <div><i class="fas fa-envelope"></i> <?php echo htmlspecialchars($loja['emailloja']); ?></div>
                                                <?php endif; ?>
                                                <?php if (empty($loja['telefoneloja']) && empty($loja['emailloja'])): ?>
                                                    <span class="text-muted">Sem contato</span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <?php echo $loja['nomeCidade'] . ' - ' . $loja['siglaEstado']; ?>
                                            </td>
                                            <td class="address-info">
                                                <?php if (!empty($loja['logradouro'])): ?>
                                                    <div><strong><?php echo htmlspecialchars($loja['logradouro']); ?>, <?php echo $loja['numero']; ?></strong></div>
                                                    <div><?php echo htmlspecialchars($loja['bairro']); ?></div>
                                                    <div>CEP: <?php echo $loja['cep']; ?></div>
                                                <?php else: ?>
                                                    <span class="text-muted">Endereço não informado</span>
                                                <?php endif; ?>
                                            </td>
                                          <?php if ($tipoUsuario === 'admin'): ?>
                                                <td>
                                                    <?php 
                                                    if (!empty($loja['nome_lojista'])) {
                                                        echo htmlspecialchars($loja['nome_lojista']);
                                                    } else {
                                                        echo '<span class="text-muted"><i class="fas fa-user-slash"></i> Sem lojista</span>';
                                                    }
                                                    ?>
                                                </td>
                                                <td>
                                                    <?php 
                                                    if (!empty($loja['nome_representante'])) {
                                                        echo htmlspecialchars($loja['nome_representante']);
                                                    } else {
                                                        echo '<span class="text-muted"><i class="fas fa-user-tie"></i> Sem representante</span>';
                                                    }
                                                    ?>
                                                </td>
                                            <?php endif; ?>
                                            <td>
                                                <?php echo date('d/m/Y', strtotime($loja['lojaCriadoEm'])); ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="editarLoja(<?php echo $loja['idlojas']; ?>)" 
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($tipoUsuario === 'admin' || $loja['representante_id'] == $idUsuario): ?>
                                                    <button class="btn btn-sm btn-outline-danger" 
                                                            onclick="confirmarExclusao(<?php echo $loja['idlojas']; ?>, '<?php echo htmlspecialchars($loja['nomeloja'] ?? 'Loja sem nome'); ?>')" 
                                                            title="Excluir">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Sem permissão">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo $tipoUsuario === 'admin' ? '10' : '8'; ?>" class="text-center text-muted py-4">
                                            <i class="fas fa-store fa-2x mb-2 d-block"></i>
                                            <?php if ($tipoUsuario === 'admin'): ?>
                                                Nenhuma loja encontrada
                                            <?php else: ?>
                                                Você ainda não cadastrou nenhuma loja
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <!-- Modal para Nova Loja -->
    <div class="modal-custom" id="addLojaModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Nova Loja
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('addLojaModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="../src/controllers/StoresController.php">
                    <?php if ($tipoUsuario === 'admin'): ?>
                    <!-- Campo para admin selecionar representante -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="representante_id" class="form-label">
                                    <i class="fas fa-user-tie me-2"></i>Representante *
                                </label>
                                <select class="form-control" id="representante_id" name="representante_id" required onchange="filtrarCidadesPorRepresentante()">
                                    <option value="">Selecione o representante</option>
                                    <?php
                                    $sql_representantes = "SELECT idUsuario, nomeUsuario FROM usuario WHERE tipoUsuario = 'representante'";
                                    $result_representantes = mysqli_query($conexao, $sql_representantes);
                                    if ($result_representantes): ?>
                                        <?php while ($rep = mysqli_fetch_assoc($result_representantes)): ?>
                                            <option value="<?php echo $rep['idUsuario']; ?>">
                                                <?php echo htmlspecialchars($rep['nomeUsuario']); ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Ao selecionar um representante, apenas as cidades vinculadas a ele serão exibidas.
                                </small>
                            </div>
                        </div>
                    </div>
                    <?php else: ?>
                        <input type="hidden" name="representante_id" id="representante_id" value="<?php echo $idUsuario; ?>">
                    <?php endif; ?>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomeLoja" class="form-label">
                                    <i class="fas fa-store me-2"></i>Nome da Loja *
                                </label>
                                <input type="text" class="form-control" id="nomeLoja" name="nomeLoja" required 
                                    value="<?php echo isset($solicitacao_aceita) ? htmlspecialchars($solicitacao_aceita['nome']) : ''; ?>"
                                    placeholder="Digite o nome da loja">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="telefoneLoja" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Telefone
                                </label>
                                <input type="text" class="form-control" id="telefoneLoja" name="telefoneLoja" 
                                    value="<?php echo isset($solicitacao_aceita) ? htmlspecialchars($solicitacao_aceita['telefone']) : ''; ?>"
                                    placeholder="(00) 00000-0000">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emailLoja" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-mail da Loja *
                                </label>
                                <input type="email" class="form-control" id="emailLoja" name="emailLoja" required
                                    value="<?php echo isset($solicitacao_aceita) ? htmlspecialchars($solicitacao_aceita['email']) : ''; ?>"
                                    placeholder="loja@exemplo.com">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="senhaLoja" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Senha para Login da Loja *
                                </label>
                                <input type="password" class="form-control" id="senhaLoja" name="senhaLoja" required
                                    placeholder="Digite a senha para o login">
                                <small class="form-text text-muted">
                                    <i class="fas fa-info-circle"></i> Esta senha será usada para o lojista fazer login no sistema.
                                </small>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="fotoLoja" class="form-label">
                                    <i class="fas fa-image me-2"></i>Logo (URL)
                                </label>
                                <input type="text" class="form-control" id="fotoLoja" name="fotoLoja" 
                                    placeholder="https://exemplo.com/logo.jpg">
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="cep" class="form-label">
                                    <i class="fas fa-map-pin me-2"></i>CEP
                                </label>
                                <input type="text" class="form-control" id="cep" name="cep" 
                                    placeholder="00000-000">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control" id="logradouro" name="logradouro" 
                                    placeholder="Rua, Avenida, etc.">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="numero" name="numero" 
                                    placeholder="123">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="bairro" name="bairro" 
                                    placeholder="Centro">
                            </div>
                        </div>
                    </div>

                    <!-- Seleção/Criação de Cidade -->
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Cidade *
                                </label>
                                <div class="form-check mb-2">
                                    <input class="form-check-input" type="radio" name="tipoCidade" id="cidadeExistente" value="existente" checked onchange="toggleTipoCidade()">
                                    <label class="form-check-label" for="cidadeExistente">
                                        Selecionar cidade existente
                                    </label>
                                </div>
                                <div class="form-check mb-3">
                                    <input class="form-check-input" type="radio" name="tipoCidade" id="cidadeNova" value="nova" onchange="toggleTipoCidade()">
                                    <label class="form-check-label" for="cidadeNova">
                                        Criar nova cidade
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Selecionar Cidade Existente -->
                    <div class="row" id="containerCidadeExistente">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="cidade_idCidade" class="form-label">
                                    <i class="fas fa-list me-2"></i>Selecione a cidade
                                </label>
                                <select class="form-control" id="cidade_idCidade" name="cidade_idCidade">
                                    <option value="">Selecione a cidade</option>
                                    <?php 
                                    if ($result_cidades): 
                                        mysqli_data_seek($result_cidades, 0);
                                        while ($cidade = mysqli_fetch_assoc($result_cidades)): 
                                            $selected = (isset($solicitacao_aceita) && $solicitacao_aceita['cidade_idCidade'] == $cidade['idCidade']) ? 'selected' : '';
                                        ?>
                                            <option value="<?php echo $cidade['idCidade']; ?>" data-estado="<?php echo $cidade['estado_idEstado'] ?? ''; ?>" <?php echo $selected; ?>>
                                                <?php echo $cidade['nomeCidade'] . ' - ' . $cidade['siglaEstado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                                <?php if ($tipoUsuario === 'representante'): ?>
                                    <small class="form-text text-muted">
                                        <i class="fas fa-info-circle"></i> Apenas cidades vinculadas ao seu representante são exibidas.
                                    </small>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Criar Nova Cidade -->
                    <div class="row" id="containerCidadeNova" style="display: none;">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado_idEstado" class="form-label">
                                    <i class="fas fa-map me-2"></i>Estado *
                                </label>
                                <select class="form-control" id="estado_idEstado" name="estado_idEstado">
                                    <option value="">Selecione o estado</option>
                                    <?php 
                                    if ($result_estados): 
                                        while ($estado = mysqli_fetch_assoc($result_estados)): ?>
                                            <option value="<?php echo $estado['idEstado']; ?>">
                                                <?php echo $estado['nomeEstado'] . ' (' . $estado['siglaEstado'] . ')'; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomeCidade" class="form-label">
                                    <i class="fas fa-city me-2"></i>Nome da Cidade *
                                </label>
                                <input type="text" class="form-control" id="nomeCidade" name="nomeCidade" 
                                    placeholder="Digite o nome da cidade">
                                <input type="hidden" name="criarNovaCidade" id="criarNovaCidade" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('addLojaModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="cadastrar_loja">
                            <i class="fas fa-save me-2"></i>Salvar Loja
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Loja -->
    <div class="modal-custom" id="editLojaModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Loja
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('editLojaModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <?php if ($loja_edicao && isset($loja_edicao['idlojas'])): ?>
                <form method="POST" action="../src/controllers/StoresController.php">
                    <input type="hidden" name="idlojas" value="<?php echo $loja_edicao['idlojas']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nomeLoja" class="form-label">
                                    <i class="fas fa-store me-2"></i>Nome da Loja *
                                </label>
                                <input type="text" class="form-control" id="edit_nomeLoja" name="nomeLoja" required 
                                    value="<?php echo isset($loja_edicao['nomeLoja']) ? htmlspecialchars($loja_edicao['nomeLoja']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_telefoneLoja" class="form-label">
                                    <i class="fas fa-phone me-2"></i>Telefone
                                </label>
                                <input type="text" class="form-control" id="edit_telefoneLoja" name="telefoneLoja" 
                                    value="<?php echo isset($loja_edicao['telefoneLoja']) ? htmlspecialchars($loja_edicao['telefoneLoja']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_emailLoja" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-mail
                                </label>
                                <input type="email" class="form-control" id="edit_emailLoja" name="emailLoja" 
                                    value="<?php echo isset($loja_edicao['emailLoja']) ? htmlspecialchars($loja_edicao['emailLoja']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_fotoLoja" class="form-label">
                                    <i class="fas fa-image me-2"></i>Logo (URL)
                                </label>
                                <input type="text" class="form-control" id="edit_fotoLoja" name="fotoLoja" 
                                    value="<?php echo isset($loja_edicao['fotoLoja']) ? htmlspecialchars($loja_edicao['fotoLoja']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <!-- Endereço -->
                    <div class="row">
                        <div class="col-md-3">
                            <div class="mb-3">
                                <label for="edit_cep" class="form-label">
                                    <i class="fas fa-map-pin me-2"></i>CEP
                                </label>
                                <input type="text" class="form-control" id="edit_cep" name="cep" 
                                    value="<?php echo isset($loja_edicao['cep']) ? htmlspecialchars($loja_edicao['cep']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-5">
                            <div class="mb-3">
                                <label for="edit_logradouro" class="form-label">Logradouro</label>
                                <input type="text" class="form-control" id="edit_logradouro" name="logradouro" 
                                    value="<?php echo isset($loja_edicao['logradouro']) ? htmlspecialchars($loja_edicao['logradouro']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="edit_numero" class="form-label">Número</label>
                                <input type="text" class="form-control" id="edit_numero" name="numero" 
                                    value="<?php echo isset($loja_edicao['numero']) ? htmlspecialchars($loja_edicao['numero']) : ''; ?>">
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="mb-3">
                                <label for="edit_bairro" class="form-label">Bairro</label>
                                <input type="text" class="form-control" id="edit_bairro" name="bairro" 
                                    value="<?php echo isset($loja_edicao['bairro']) ? htmlspecialchars($loja_edicao['bairro']) : ''; ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_cidade_idCidade" class="form-label">
                                    <i class="fas fa-map-marker-alt me-2"></i>Cidade *
                                </label>
                                <select class="form-control" id="edit_cidade_idCidade" name="cidade_idCidade" required>
                                    <option value="">Selecione a cidade</option>
                                    <?php 
                                    mysqli_data_seek($result_cidades, 0);
                                    if ($result_cidades): ?>
                                        <?php while ($cidade = mysqli_fetch_assoc($result_cidades)): ?>
                                            <option value="<?php echo $cidade['idCidade']; ?>" 
                                                <?php echo ($loja_edicao['cidade_idCidade'] == $cidade['idCidade']) ? 'selected' : ''; ?>>
                                                <?php echo $cidade['nomeCidade'] . ' - ' . $cidade['siglaEstado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('editLojaModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="editar_loja">
                            <i class="fas fa-save me-2"></i>Atualizar Loja
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <p>Loja não encontrada para edição ou você não tem permissão.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function toggleFiltros() {
            const container = document.getElementById('filtrosContainer');
            container.classList.toggle('show');
        }

        function editarLoja(idlojas) {
            window.location.href = '?editar=' + idlojas;
        }

        function confirmarExclusao(idlojas, nomeLoja) {
            if (confirm('Tem certeza que deseja excluir a loja "' + nomeLoja + '"?\n\nEsta ação não pode ser desfeita.')) {
                window.location.href = '../src/controllers/StoresController.php?excluir=' + idlojas;
            }
        }

        function toggleTipoCidade() {
            const cidadeExistente = document.getElementById('cidadeExistente').checked;
            const containerExistente = document.getElementById('containerCidadeExistente');
            const containerNova = document.getElementById('containerCidadeNova');
            const selectCidade = document.getElementById('cidade_idCidade');
            const inputNomeCidade = document.getElementById('nomeCidade');
            const selectEstado = document.getElementById('estado_idEstado');
            const hiddenCriarNova = document.getElementById('criarNovaCidade');

            if (cidadeExistente) {
                containerExistente.style.display = 'block';
                containerNova.style.display = 'none';
                selectCidade.required = true;
                inputNomeCidade.required = false;
                selectEstado.required = false;
                hiddenCriarNova.value = '0';
            } else {
                containerExistente.style.display = 'none';
                containerNova.style.display = 'block';
                selectCidade.required = false;
                inputNomeCidade.required = true;
                selectEstado.required = true;
                hiddenCriarNova.value = '1';
            }
        }

        // Filtrar cidades por representante (para admin)
        function filtrarCidadesPorRepresentante() {
            const representanteId = document.getElementById('representante_id').value;
            const selectCidade = document.getElementById('cidade_idCidade');
            
            if (!representanteId) {
                // Se não há representante selecionado, mostrar todas as cidades
                location.reload();
                return;
            }

            // Buscar cidades do representante via AJAX
            fetch(`getCidadesByRepresentante.php?representante_id=${representanteId}`)
                .then(response => response.json())
                .then(data => {
                    if (data.error) {
                        console.error('Erro:', data.error);
                        return;
                    }
                    
                    // Limpar opções existentes (exceto a primeira)
                    selectCidade.innerHTML = '<option value="">Selecione a cidade</option>';
                    
                    // Adicionar novas opções
                    data.forEach(cidade => {
                        const option = document.createElement('option');
                        option.value = cidade.idCidade;
                        option.textContent = `${cidade.nomeCidade} - ${cidade.siglaEstado}`;
                        option.setAttribute('data-estado', cidade.idEstado);
                        selectCidade.appendChild(option);
                    });
                })
                .catch(error => {
                    console.error('Erro ao buscar cidades:', error);
                });
        }

        // Validar formulário antes de enviar
        document.addEventListener('DOMContentLoaded', function() {
            const formLoja = document.querySelector('form[action="../src/controllers/StoresController.php"]');
            if (formLoja) {
                formLoja.addEventListener('submit', function(e) {
                    const cidadeExistente = document.getElementById('cidadeExistente').checked;
                    const selectCidade = document.getElementById('cidade_idCidade');
                    const inputNomeCidade = document.getElementById('nomeCidade');
                    const selectEstado = document.getElementById('estado_idEstado');
                    const representanteId = document.getElementById('representante_id');

                    // Validar representante (se admin)
                    <?php if ($tipoUsuario === 'admin'): ?>
                    if (!representanteId || !representanteId.value) {
                        e.preventDefault();
                        alert('Por favor, selecione um representante.');
                        return false;
                    }
                    <?php endif; ?>

                    if (cidadeExistente) {
                        if (!selectCidade.value) {
                            e.preventDefault();
                            alert('Por favor, selecione uma cidade existente.');
                            return false;
                        }
                    } else {
                        if (!inputNomeCidade.value || !selectEstado.value) {
                            e.preventDefault();
                            alert('Para criar uma nova cidade, é necessário informar o nome da cidade e o estado.');
                            return false;
                        }
                    }
                });
            }
        });

        // Abrir modal de edição se houver parâmetro na URL ou se vier de solicitação aceita
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('editar')) {
                abrirModal('editLojaModal');
            }
            
            // Abrir modal automaticamente se vier de solicitação aceita
            <?php if (isset($_GET['criar_loja']) && isset($solicitacao_aceita)): ?>
            if (urlParams.has('criar_loja')) {
                abrirModal('addLojaModal');
            }
            <?php endif; ?>
            
            // Mostrar filtros se algum filtro estiver ativo
            const filtros = ['filtro_nome', 'filtro_cidade'];
            const hasActiveFilter = filtros.some(param => urlParams.has(param) && urlParams.get(param) !== '');
            if (hasActiveFilter) {
                document.getElementById('filtrosContainer').classList.add('show');
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
    </script>
</body>
</html>

<?php
// Fecha a conexão com o banco
if (isset($conexao)) {
    mysqli_close($conexao);
}
?>