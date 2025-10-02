<?php
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] !== 'representante') {
        header("Location: login.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel do Representante - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/representante.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />

</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-user-tie"></i> System Pump</h2>
                <p>Painel do Representante</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="./representante.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-inbox"></i> Solicitações</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                <li><a href="./gerenciadorCidades.php"><i class="fas fa-map-marker-alt"></i> Cidades</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard do Representante</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">RP</div>
                        <span>Representante</span>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página -->
            <div class="page-content">
                <div class="page-header">
                    <h2>Visão Geral</h2>
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
                            <i class="fas fa-map-marker-alt"></i>
                        </div>
                        <div class="card-info">
                            <h3>3</h3>
                            <p>Cidades</p>
                        </div>
                    </div>
                </div>

                <!-- Solicitações de Lojas -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Solicitações de Lojistas</h3>
                        <div class="table-actions">
                            <button class="btn btn-outline"><i class="fas fa-filter"></i> Filtrar</button>
                            <button class="btn btn-primary"><i class="fas fa-sync"></i> Atualizar</button>
                        </div>
                    </div>

                    <div class="solicitations-list">
                        <div class="solicitation-card fade-in">
                            <div class="solicitation-header">
                                <div class="solicitation-info">
                                    <h4>Agropecuária Nova Era</h4>
                                    <div class="solicitation-meta">
                                        <span><strong>CNPJ:</strong> 12.345.678/0001-90</span> | 
                                        <span><strong>Telefone:</strong> (11) 99999-9999</span> | 
                                        <span><strong>Data:</strong> 15/03/2025</span>
                                    </div>
                                </div>
                                <div class="solicitation-actions">
                                    <button class="btn btn-success" onclick="openModal('approve')"><i class="fas fa-check"></i> Aprovar</button>
                                    <button class="btn btn-danger" onclick="openModal('reject')"><i class="fas fa-times"></i> Recusar</button>
                                    <button class="btn btn-outline"><i class="fas fa-eye"></i> Detalhes</button>
                                </div>
                            </div>
                            <div class="solicitation-body">
                                <p><strong>E-mail:</strong> contato@agronovaera.com.br</p>
                                <div class="solicitation-message">
                                    "Somos uma agropecuária estabelecida há 15 anos na região de Ribeirão Preto. Temos clientes em toda a região e acreditamos que os produtos System Pump terão boa aceitação devido à qualidade e tecnologia."
                                </div>
                            </div>
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