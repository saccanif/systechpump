<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

// Verificar se o usuário é admin
if ($_SESSION['usuario']['tipoUsuario'] !== 'admin') {
    header("Location: login.php?erro=Acesso não autorizado para administrador");
    exit();
}

$usuario = $_SESSION['usuario'];
$nomeUsuario = $usuario['nomeUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';

include_once "../config/connection.php";
$conexao = conectarBD();

// Verificar se a tabela existe
$sql_check = "SHOW TABLES LIKE 'mensagens'";
$result_check = mysqli_query($conexao, $sql_check);
if (mysqli_num_rows($result_check) == 0) {
    // Criar tabela se não existir
    $sql_create = "CREATE TABLE IF NOT EXISTS mensagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        cidade_uf VARCHAR(100),
        motivo VARCHAR(100) NOT NULL,
        mensagem TEXT NOT NULL,
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        lida TINYINT(1) DEFAULT 0,
        INDEX idx_lida (lida),
        INDEX idx_data (data_envio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($conexao, $sql_create);
}

// Processar marcação como lida
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['marcar_lida'])) {
    $id = intval($_POST['id']);
    $sql_update = "UPDATE mensagens SET lida = 1 WHERE id = ?";
    $stmt = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    mysqli_stmt_close($stmt);
    header("Location: ./gerenciadorMensagens.php");
    exit;
}

// Buscar mensagens
$filtro_lida = $_GET['filtro'] ?? 'todas';
$where = "1=1";
if ($filtro_lida === 'lidas') {
    $where .= " AND lida = 1";
} elseif ($filtro_lida === 'nao_lidas') {
    $where .= " AND lida = 0";
}

$sql_mensagens = "SELECT * FROM mensagens WHERE $where ORDER BY data_envio DESC";
$result_mensagens = mysqli_query($conexao, $sql_mensagens);

$total_mensagens = mysqli_num_rows($result_mensagens);
$nao_lidas = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) as total FROM mensagens WHERE lida = 0"))['total'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mensagens - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <style>
        .mensagem-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid var(--primary);
            transition: all 0.3s;
        }
        .mensagem-card.nao-lida {
            border-left-color: var(--accent);
            background: #f8f9fa;
        }
        .mensagem-card:hover {
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        .mensagem-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #e0e0e0;
        }
        .mensagem-info h4 {
            margin: 0 0 0.5rem 0;
            color: var(--primary);
        }
        .mensagem-meta {
            display: flex;
            gap: 1rem;
            flex-wrap: wrap;
            font-size: 0.9rem;
            color: var(--gray);
        }
        .mensagem-meta span {
            display: flex;
            align-items: center;
            gap: 0.25rem;
        }
        .mensagem-body {
            margin-top: 1rem;
        }
        .mensagem-body p {
            margin: 0.5rem 0;
            line-height: 1.6;
        }
        .badge-nao-lida {
            background: var(--accent);
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.8rem;
        }
        .filtros-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 2rem;
            flex-wrap: wrap;
        }
        .filtro-btn {
            padding: 0.5rem 1.5rem;
            border: 2px solid var(--primary);
            background: white;
            color: var(--primary);
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s;
            text-decoration: none;
            display: inline-block;
        }
        .filtro-btn:hover, .filtro-btn.active {
            background: var(--primary);
            color: white;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-tachometer-alt"></i> System Pump</h2>
                <p>Painel Administrativo</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="./adm.php"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./gerenciadorInsights.php"><i class="fas fa-chart-line"></i> Insights</a></li>
                <li><a href="./gerenciadorSolicitacoes.php"><i class="fas fa-inbox"></i> Solicitações</a></li>
                <li><a href="./gerenciadorMensagens.php" class="active"><i class="fas fa-envelope"></i> Mensagens</a></li>
                <li><a href="./gerenciadorObservacoes.php"><i class="fas fa-eye"></i> Observação</a></li>
                <li><a href="./gerenciadorPedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="./gerenciadorProdutos.php"><i class="fas fa-box"></i> Produtos</a></li>
                <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                <li><a href="./gerenciadorCidades.php"><i class="fas fa-map-marker-alt"></i> Cidades</a></li>
                <li><a href="./gerenciadorUsers.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="./gerenciadorConfig.php"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <h1>Mensagens</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                alt="Avatar" 
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

            <div class="page-content">
                <div class="page-header d-flex justify-content-between align-items-center">
                    <h2>Mensagens Recebidas</h2>
                    <div>
                        <span class="badge-nao-lida">
                            <i class="fas fa-envelope"></i> <?= $nao_lidas ?> não lidas
                        </span>
                    </div>
                </div>

                <!-- Filtros -->
                <div class="filtros-container">
                    <a href="?filtro=todas" class="filtro-btn <?= $filtro_lida === 'todas' ? 'active' : '' ?>">
                        Todas (<?= $total_mensagens ?>)
                    </a>
                    <a href="?filtro=nao_lidas" class="filtro-btn <?= $filtro_lida === 'nao_lidas' ? 'active' : '' ?>">
                        Não Lidas (<?= $nao_lidas ?>)
                    </a>
                    <a href="?filtro=lidas" class="filtro-btn <?= $filtro_lida === 'lidas' ? 'active' : '' ?>">
                        Lidas (<?= $total_mensagens - $nao_lidas ?>)
                    </a>
                </div>

                <!-- Lista de Mensagens -->
                <div class="mensagens-container">
                    <?php if ($total_mensagens > 0): ?>
                        <?php while ($mensagem = mysqli_fetch_assoc($result_mensagens)): ?>
                            <div class="mensagem-card <?= $mensagem['lida'] == 0 ? 'nao-lida' : '' ?>">
                                <div class="mensagem-header">
                                    <div class="mensagem-info">
                                        <h4>
                                            <?= htmlspecialchars($mensagem['nome']) ?>
                                            <?php if ($mensagem['lida'] == 0): ?>
                                                <span class="badge-nao-lida">Nova</span>
                                            <?php endif; ?>
                                        </h4>
                                        <div class="mensagem-meta">
                                            <span><i class="fas fa-envelope"></i> <?= htmlspecialchars($mensagem['email']) ?></span>
                                            <span><i class="fas fa-phone"></i> <?= htmlspecialchars($mensagem['telefone']) ?></span>
                                            <?php if (!empty($mensagem['cidade_uf'])): ?>
                                                <span><i class="fas fa-map-marker-alt"></i> <?= htmlspecialchars($mensagem['cidade_uf']) ?></span>
                                            <?php endif; ?>
                                            <span><i class="fas fa-tag"></i> <?= htmlspecialchars($mensagem['motivo']) ?></span>
                                            <span><i class="fas fa-clock"></i> <?= date('d/m/Y H:i', strtotime($mensagem['data_envio'])) ?></span>
                                        </div>
                                    </div>
                                    <?php if ($mensagem['lida'] == 0): ?>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="id" value="<?= $mensagem['id'] ?>">
                                            <button type="submit" name="marcar_lida" class="btn btn-sm btn-success">
                                                <i class="fas fa-check"></i> Marcar como lida
                                            </button>
                                        </form>
                                    <?php endif; ?>
                                </div>
                                <div class="mensagem-body">
                                    <p><strong>Mensagem:</strong></p>
                                    <p><?= nl2br(htmlspecialchars($mensagem['mensagem'])) ?></p>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="mensagem-card">
                            <div class="text-center" style="padding: 3rem;">
                                <i class="fas fa-inbox fa-3x" style="color: #ccc; margin-bottom: 1rem;"></i>
                                <p style="color: #999;">Nenhuma mensagem encontrada</p>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>
</body>
</html>

<?php
mysqli_close($conexao);
?>

