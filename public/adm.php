<?php
session_start();

// Verificação simples de sessão
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
$tipoUsuario = $usuario['tipoUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';

include_once "../config/connection.php";
$conexao = conectarBD();

// Contagens do banco
$qtdLojas = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total FROM lojas"))['total'];
$qtdSolicitacoes = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total FROM pedidos WHERE status = 'pendente'"))['total'];
$qtdProdutos = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total FROM produtos"))['total'];
$qtdRepresentantes = mysqli_fetch_assoc(mysqli_query($conexao, "SELECT COUNT(*) AS total FROM usuario WHERE tipoUsuario = 'representante'"))['total'];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-tachometer-alt"></i> System Pump</h2>
                <p>Painel Administrativo</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="./adm.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./gerenciadorRelatorios.php"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <li><a href="./gerenciadorSolicitacoes.php"><i class="fas fa-inbox"></i> Solicitações</a></li>
                <li><a href="./gerenciadorPedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="./gerenciadorProdutos.php"><i class="fas fa-box"></i> Produtos</a></li>
                <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                <li><a href="./gerenciadorCidades.php"><i class="fas fa-map-marker-alt"></i> Cidades</a></li>
                <li><a href="./gerenciadorUsers.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="./gerenciadorConfig.php"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard Geral</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <?php 
                            // Verifica se existe uma URL de avatar (não verifica file_exists para URLs web)
                            if (!empty($avatarUrl)): 
                            ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                    alt="Avatar do <?= htmlspecialchars($nomeUsuario) ?>" 
                                    class="user-avatar-img" 
                                    style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                    onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                <div class="user-avatar fallback" style="display: none;"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                            <?php else: ?>
                                <div class="user-avatar"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                            <?php endif; ?>
                        <span><?= htmlspecialchars($nomeUsuario) ?> </span>
                    </div>
                </div>
            </header>

            <!-- Resto do conteúdo permanece igual -->
            <div class="page-content">
                <div class="page-header">
                    <h2>Visão Geral do Sistema</h2>
                </div>

                <!-- Cards de Resumo -->
                <div class="summary-cards">
                    <div class="summary-card fade-in">
                        <div class="card-icon blue">
                            <i class="fa-solid fa-shop"></i>
                        </div>
                        <div class="card-info">
                            <h3><?= $qtdLojas ?></h3>
                            <p>Lojas</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon green">
                            <i class="fas fa-valve-open"></i>
                        </div>
                        <div class="card-info">
                            <h3><?= $qtdSolicitacoes ?></h3>
                            <p>Solicitações</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon orange">
                            <i class="fa-brands fa-product-hunt"></i>
                        </div>
                        <div class="card-info">
                            <h3><?= $qtdProdutos ?></h3>
                            <p>Produtos</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon red">
                            <i class="fa-solid fa-person"></i>
                        </div>
                        <div class="card-info">
                            <h3><?= $qtdRepresentantes ?></h3>
                            <p>Representantes</p>
                        </div>
                    </div>
                </div>

                <!-- Relatórios Rápidos -->
                <div class="dashboard-row">
                    <div class="chart-container">
                        <div class="chart-header">
                            <h3>Relatório Rápido</h3>
                        </div>
                        <div class="chart-placeholder">
                            <i class="fas fa-chart-pie" style="font-size: 2rem; margin-right: 10px;"></i>
                            Gráfico
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <script>
        // Fallback adicional em caso de erro no carregamento da imagem
        document.addEventListener('DOMContentLoaded', function() {
            const avatarImgs = document.querySelectorAll('.user-avatar-img');
            avatarImgs.forEach(img => {
                img.addEventListener('error', function() {
                    this.style.display = 'none';
                    const fallback = this.nextElementSibling;
                    if (fallback && fallback.classList.contains('fallback')) {
                        fallback.style.display = 'flex';
                    }
                });
            });
        });
    </script>
</body>
</html>