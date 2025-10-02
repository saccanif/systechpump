<?php
    session_start();
    if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] !== 'lojista') {
        header("Location: login.php");
        exit();
    }
?>


<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel da Loja - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/loja.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-store"></i> System Pump</h2>
                <p>Painel da Loja</p>
            </div>
            
            <ul class="sidebar-menu">
                <li><a href="#" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="#"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="#"><i class="fas fa-chart-bar"></i> Relatórios</a></li>
                <li><a href="#"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </aside>

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="header">
                <div class="header-left">
                    <h1>Dashboard da Loja</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">VL</div>
                        <span>Vendedor Loja</span>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página -->
            <div class="page-content">
                <div class="page-header">
                    <h2>Visão Geral da Loja</h2>
                </div>

                <!-- Pedidos Recentes -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Pedidos Recentes</h3>
                        <div class="table-actions">
                            <button class="btn btn-outline"><i class="fas fa-filter"></i> Filtrar</button>
                            <button class="btn btn-primary"><i class="fas fa-plus"></i> Novo Pedido</button>
                        </div>
                    </div>
                    <div class="table-responsive">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nº Pedido</th>
                                    <th>Data</th>
                                    <th>Status</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>#SP-0042</td>
                                    <td>15/03/2025</td>
                                    <td><span class="status active">Entregue</span></td>
                                    <td>
                                        <button class="btn btn-outline"><i class="fas fa-eye"></i></button>
                                        <button class="btn btn-outline"><i class="fas fa-print"></i></button>
                                    </td>
                                </tr>
                                <tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Produtos em Destaque -->
                <div class="table-container">
                    <div class="table-header">
                        <h3>Produtos em Destaque</h3>
                        <div class="table-actions">
                            <button class="btn btn-outline"><i class="fas fa-list"></i> Ver Todos</button>
                        </div>
                    </div>
                    <div class="products-grid">
                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-tint" style="font-size: 3rem;"></i>
                            </div>
                            <div class="product-info">
                                <h4>Válvula Solenoide S-T8</h4>
                                <div class="product-actions">
                                    <button class="btn btn-outline" style="flex: 1;"><i class="fas fa-edit"></i> Adicionar ao Carrinho</button>
                                    <button class="btn btn-primary" style="flex: 1;"><i class="fas fa-eye"></i> Ver</button>
                                </div>
                            </div>
                        </div>

                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-tint" style="font-size: 3rem;"></i>
                            </div>
                            <div class="product-info">
                                <h4>Válvula Solenoide S-T8</h4>
                                <div class="product-actions">
                                    <button class="btn btn-outline" style="flex: 1;"><i class="fas fa-edit"></i> Adicionar ao Carrinho</button>
                                    <button class="btn btn-primary" style="flex: 1;"><i class="fas fa-eye"></i> Ver</button>
                                </div>
                            </div>
                        </div>

                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-tint" style="font-size: 3rem;"></i>
                            </div>
                            <div class="product-info">
                                <h4>Válvula Solenoide S-T8</h4>
                                <div class="product-actions">
                                    <button class="btn btn-outline" style="flex: 1;"><i class="fas fa-edit"></i> Adicionar ao Carrinho</button>
                                    <button class="btn btn-primary" style="flex: 1;"><i class="fas fa-eye"></i> Ver</button>
                                </div>
                            </div>
                        </div>

                        <div class="product-card">
                            <div class="product-image">
                                <i class="fas fa-tint" style="font-size: 3rem;"></i>
                            </div>
                            <div class="product-info">
                                <h4>Válvula Solenoide S-T8</h4>
                                <div class="product-actions">
                                    <button class="btn btn-outline" style="flex: 1;"><i class="fas fa-edit"></i> Adicionar ao Carrinho</button>
                                    <button class="btn btn-primary" style="flex: 1;"><i class="fas fa-eye"></i> Ver</button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
                <p>Loja Online v2.1 | Última atualização: 15/03/2025</p>
            </footer>
        </main>
    </div>

</body>
</html>