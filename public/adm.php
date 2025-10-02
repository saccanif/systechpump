<?php
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] !== 'admin') {
        header("Location: login.php");
        exit();
    }
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
                <li><a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-inbox"></i> Solicitações</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="#"><i class="fas fa-box"></i> Produtos</a></li>
                <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                <li><a href="./gerenciadorCidades.php"><i class="fas fa-map-marker-alt"></i> Cidades</a></li>
                <li><a href="./gerenciadorUsers.php"><i class="fas fa-users"></i> Usuários</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
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
                        <div class="user-avatar">AD</div>
                        <span>Admin Geral</span>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página -->
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
                            <h3>12</h3>
                            <p>Lojas</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon green">
                            <i class="fas fa-valve-open"></i>
                        </div>
                        <div class="card-info">
                            <h3>2</h3>
                            <p>Solicitações</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon orange">
                            <i class="fa-brands fa-product-hunt"></i>
                        </div>
                        <div class="card-info">
                            <h3>20</h3>
                            <p>Produtos</p>
                        </div>
                    </div>
                    
                    <div class="summary-card fade-in">
                        <div class="card-icon red">
                            <i class="fa-solid fa-person"></i>
                        </div>
                        <div class="card-info">
                            <h3>3</h3>
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

</body>
</html>