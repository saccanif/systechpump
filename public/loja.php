<?php
session_start();

// Verificação simples de sessão - MESMO PADRÃO DO adm.php
if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];

// Verificar se o usuário é lojista
if ($usuario['tipoUsuario'] !== 'lojista') {
    header("Location: login.php?erro=Acesso não autorizado para lojista");
    exit();
}

$nomeUsuario = $usuario['nomeUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';

// Conexão com banco
require_once "../config/connection.php";
$conexao = conectarBD();

// Buscar produtos mais vendidos (máximo 3)
$sql_mais_vendidos = "SELECT idprodutos, nomeProduto, categoria, imagem_url, descProduto 
                      FROM produtos 
                      WHERE mais_vendidos = 1 AND ativoProduto = 1 
                      ORDER BY idprodutos 
                      LIMIT 3";
$result_mais_vendidos = mysqli_query($conexao, $sql_mais_vendidos);
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
                <li><a href="./loja.php" class="active"><i class="fas fa-home"></i> Home</a></li>
                <li><a href="./gerenciadorPedidosLojista.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                <li><a href="./gerenciadorInsights.php"><i class="fas fa-chart-line"></i> Insights</a></li>
                <li><a href="./gerenciadorConfig.php"><i class="fas fa-cog"></i> Configurações</a></li>
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
                        <?php if ($result_mais_vendidos && mysqli_num_rows($result_mais_vendidos) > 0): ?>
                            <?php while ($produto = mysqli_fetch_assoc($result_mais_vendidos)): ?>
                            <div class="product-card">
                                <div class="product-image" style="position: relative;">
                                    <?php if (!empty($produto['imagem_url'])): ?>
                                        <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                                             alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>"
                                             style="width: 100%; height: 150px; object-fit: cover; border-radius: 8px 8px 0 0;">
                                    <?php else: ?>
                                        <div style="width: 100%; height: 150px; background: linear-gradient(135deg, #0e47a1 0%, #3b81ee 100%); display: flex; align-items: center; justify-content: center; border-radius: 8px 8px 0 0;">
                                            <i class="fas fa-box" style="font-size: 3rem; color: white;"></i>
                                        </div>
                                    <?php endif; ?>
                                    <div style="position: absolute; bottom: 10px; right: 10px; background: rgba(255, 152, 0, 0.9); color: white; padding: 5px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: bold;">
                                        <i class="fas fa-fire"></i> Mais Vendido
                                    </div>
                                </div>
                                <div class="product-info">
                                    <h4><?php echo htmlspecialchars($produto['nomeProduto']); ?></h4>
                                    <?php if (!empty($produto['categoria'])): ?>
                                        <p style="color: #666; font-size: 0.9rem; margin: 5px 0;">
                                            <i class="fas fa-tag"></i> <?php echo htmlspecialchars($produto['categoria']); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="product-actions">
                                        <a href="./gerenciadorPedidosLojista.php?adicionar=<?php echo $produto['idprodutos']; ?>" 
                                           class="btn btn-outline" style="flex: 1;">
                                            <i class="fas fa-shopping-cart"></i> Adicionar ao Pedido
                                        </a>
                                    </div>
                                </div>
                            </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="col-12 text-center py-5">
                                <i class="fas fa-box-open fa-3x text-muted mb-3"></i>
                                <p class="text-muted">Nenhum produto mais vendido configurado ainda.</p>
                                <p class="text-muted"><small>O administrador precisa selecionar os produtos mais vendidos no menu de produtos.</small></p>
                            </div>
                        <?php endif; ?>
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