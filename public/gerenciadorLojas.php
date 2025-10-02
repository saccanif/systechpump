<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    $tipo = $_SESSION['usuario']['tipoUsuario'];

    if ($tipo === 'admin') {
        $voltar = "adm.php";
    } elseif ($tipo === 'representante') {
        $voltar = "representante.php";
    } else {
        $voltar = "login.php";
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
    <link rel="stylesheet" href="./css/gerenciadorLojas.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="admin-container">

        <!-- Conteúdo Principal -->
        <main class="main-content">
            <!-- Header -->
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <!-- Botão Voltar -->
                    <a href="<?php echo $voltar; ?>" class="btn-voltar">Voltar</a>
                    <h1 class="mb-0">Gerenciar Lojas</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <div class="user-avatar">AD</div>
                        <span>Admin Geral</span>
                    </div>
                </div>
            </header>

            <!-- Conteúdo da Página - Gerenciamento de Lojas -->
            <div class="page-content">
                <div class="stores-container">
                    <div class="stores-header">
                        <h2>Lojas do Sistema</h2>
                        <div class="stores-actions">
                            <button class="btn btn-primary" id="filter-toggle">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                            <button class="btn btn-success" id="add-store-btn">
                                <i class="fas fa-plus"></i> Nova Loja
                            </button>
                        </div>
                    </div>

                    <!-- Tabela de Lojas -->
                    <div class="stores-table-container">
                        <table class="stores-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Loja</th>
                                    <th>Localização</th>
                                    <th>Tipo</th>
                                    <th>Produtos</th>
                                    <th>Status</th>
                                    <th>Data de Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody id="stores-table-body">
                                <!-- Os dados das lojas serão carregados aqui -->
                            </tbody>
                        </table>
                        
                        <!-- Paginação -->
                        <div class="pagination d-flex justify-content-between align-items-center mt-3">
                            <div class="pagination-info">
                                Mostrando 1-10 de 25 lojas
                            </div>
                            <div class="pagination-controls d-flex gap-2">
                                <button class="btn btn-secondary btn-sm">
                                    <i class="fas fa-chevron-left"></i>
                                </button>
                                <button class="btn btn-primary btn-sm">1</button>
                                <button class="btn btn-secondary btn-sm">2</button>
                                <button class="btn btn-secondary btn-sm">3</button>
                                <button class="btn btn-secondary btn-sm">
                                    <i class="fas fa-chevron-right"></i>
                                </button>
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

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
