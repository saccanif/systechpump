<?php
    session_start();

    // Verificação de sessão
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php?erro=Faça login para acessar");
        exit();
    }

    // Verificar se é admin
    if ($_SESSION['usuario']['tipoUsuario'] !== 'admin') {
        header("Location: login.php?erro=Acesso não autorizado");
        exit();
    }

    $usuario = $_SESSION['usuario'];
    $nomeUsuario = $usuario['nomeUsuario'];
    $tipoUsuario = $usuario['tipoUsuario'];
    $avatarUrl = $usuario['avatar_url'] ?? '';
    $voltar = "adm.php";

    // Conexão com banco
    require_once "../config/connection.php";
    $conexao = conectarBD();

    // Variáveis para filtros
    $filtro_nome = $_GET['filtro_nome'] ?? '';
    $filtro_categoria = $_GET['filtro_categoria'] ?? '';
    $filtro_status = $_GET['filtro_status'] ?? '';

    // Processar formulários
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Cadastrar produto
        if (isset($_POST['cadastrar_produto'])) {
            $nomeProduto = $_POST['nomeProduto'] ?? '';
            $descProduto = $_POST['descProduto'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $imagem_url = $_POST['imagem_url'] ?? '';
            $ativoProduto = isset($_POST['ativoProduto']) ? 1 : 0;

            if (empty($nomeProduto)) {
                $erro = "Nome do produto é obrigatório";
            } else {
                $sql = "INSERT INTO produtos (nomeProduto, descProduto, categoria, imagem_url, ativoProduto) 
                        VALUES (?, ?, ?, ?, ?)";
                $stmt = mysqli_prepare($conexao, $sql);
                mysqli_stmt_bind_param($stmt, "ssssi", $nomeProduto, $descProduto, $categoria, $imagem_url, $ativoProduto);
                
                if (mysqli_stmt_execute($stmt)) {
                    $sucesso = "Produto cadastrado com sucesso!";
                } else {
                    $erro = "Erro ao cadastrar produto: " . mysqli_error($conexao);
                }
            }
        }

        // Editar produto
        if (isset($_POST['editar_produto'])) {
            $idprodutos = $_POST['idprodutos'] ?? '';
            $nomeProduto = $_POST['nomeProduto'] ?? '';
            $descProduto = $_POST['descProduto'] ?? '';
            $categoria = $_POST['categoria'] ?? '';
            $imagem_url = $_POST['imagem_url'] ?? '';
            $ativoProduto = isset($_POST['ativoProduto']) ? 1 : 0;

            if (empty($nomeProduto) || empty($idprodutos)) {
                $erro = "Nome do produto é obrigatório";
            } else {
                $sql = "UPDATE produtos SET nomeProduto = ?, descProduto = ?, categoria = ?, imagem_url = ?, ativoProduto = ? 
                        WHERE idprodutos = ?";
                $stmt = mysqli_prepare($conexao, $sql);
                mysqli_stmt_bind_param($stmt, "ssssii", $nomeProduto, $descProduto, $categoria, $imagem_url, $ativoProduto, $idprodutos);
                
                if (mysqli_stmt_execute($stmt)) {
                    $sucesso = "Produto atualizado com sucesso!";
                } else {
                    $erro = "Erro ao atualizar produto: " . mysqli_error($conexao);
                }
            }
        }
    }

    // Excluir produto
    if (isset($_GET['excluir'])) {
        $idprodutos = $_GET['excluir'];
        
        $sql = "DELETE FROM produtos WHERE idprodutos = ?";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $idprodutos);
        
        if (mysqli_stmt_execute($stmt)) {
            $sucesso = "Produto excluído com sucesso!";
        } else {
            $erro = "Erro ao excluir produto: " . mysqli_error($conexao);
        }
    }

    // Buscar produtos COM FILTROS
    $where_conditions = [];
    $params = [];
    $types = "";

    if (!empty($filtro_nome)) {
        $where_conditions[] = "nomeProduto LIKE ?";
        $params[] = "%$filtro_nome%";
        $types .= "s";
    }

    if (!empty($filtro_categoria)) {
        $where_conditions[] = "categoria = ?";
        $params[] = $filtro_categoria;
        $types .= "s";
    }

    if (!empty($filtro_status) && $filtro_status !== 'todos') {
        $where_conditions[] = "ativoProduto = ?";
        $params[] = ($filtro_status === 'ativo') ? 1 : 0;
        $types .= "i";
    }

    $where_sql = "";
    if (!empty($where_conditions)) {
        $where_sql = "WHERE " . implode(" AND ", $where_conditions);
    }

    $sql_produtos = "SELECT idprodutos, nomeProduto, descProduto, categoria, imagem_url, ativoProduto, data_criacao, data_atualizacao 
                     FROM produtos $where_sql ORDER BY idprodutos DESC LIMIT 50";

    $stmt_produtos = mysqli_prepare($conexao, $sql_produtos);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt_produtos, $types, ...$params);
    }
    mysqli_stmt_execute($stmt_produtos);
    $result_produtos = mysqli_stmt_get_result($stmt_produtos);

    // Buscar produto para edição
    $produto_edicao = null;
    if (isset($_GET['editar'])) {
        $idprodutos = $_GET['editar'];
        $sql_produto = "SELECT idprodutos, nomeProduto, descProduto, categoria, imagem_url, ativoProduto 
                        FROM produtos WHERE idprodutos = ?";
        $stmt_produto = mysqli_prepare($conexao, $sql_produto);
        mysqli_stmt_bind_param($stmt_produto, "i", $idprodutos);
        mysqli_stmt_execute($stmt_produto);
        $result_produto = mysqli_stmt_get_result($stmt_produto);
        $produto_edicao = mysqli_fetch_assoc($result_produto);
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Produtos - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link rel="stylesheet" href="./css/gerenciadorProdutos.css">
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
                    <h1 class="mb-0">Gerenciar Produtos</h1> 
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
                    <div class="alert-custom alert-success">
                         <?php echo $sucesso; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($erro)): ?>
                    <div class="alert-custom alert-error">
                        ❌ <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <div class="products-container">
                    <div class="products-header">
                        <h2>Catálogo de Produtos</h2>
                        <div class="products-actions">
                            <button class="btn btn-primary" onclick="toggleFiltros()">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                            <button class="btn btn-success" onclick="abrirModal('addProductModal')">
                                <i class="fas fa-plus"></i> Novo Produto
                            </button>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="filtros-container" id="filtrosContainer">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-4">
                                <label for="filtro_nome" class="form-label">Nome do Produto</label>
                                <input type="text" class="form-control" id="filtro_nome" name="filtro_nome" 
                                       value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Filtrar por nome...">
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_categoria" class="form-label">Categoria</label>
                                <select class="form-control" id="filtro_categoria" name="filtro_categoria">
                                    <option value="">Todas as categorias</option>
                                    <option value="Bombas" <?php echo ($filtro_categoria == 'Bombas') ? 'selected' : ''; ?>>Bombas</option>
                                    <option value="Filtros" <?php echo ($filtro_categoria == 'Filtros') ? 'selected' : ''; ?>>Filtros</option>
                                    <option value="Acessórios" <?php echo ($filtro_categoria == 'Acessórios') ? 'selected' : ''; ?>>Acessórios</option>
                                    <option value="Peças" <?php echo ($filtro_categoria == 'Peças') ? 'selected' : ''; ?>>Peças</option>
                                    <option value="Manutenção" <?php echo ($filtro_categoria == 'Manutenção') ? 'selected' : ''; ?>>Manutenção</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filtro_status" class="form-label">Status</label>
                                <select class="form-control" id="filtro_status" name="filtro_status">
                                    <option value="todos">Todos os status</option>
                                    <option value="ativo" <?php echo ($filtro_status == 'ativo') ? 'selected' : ''; ?>>Ativo</option>
                                    <option value="inativo" <?php echo ($filtro_status == 'inativo') ? 'selected' : ''; ?>>Inativo</option>
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

                    <!-- Tabela de Produtos -->
                    <div class="products-table-container">
                        <table class="products-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Imagem</th>
                                    <th>Nome</th>
                                    <th>Descrição</th>
                                    <th>Categoria</th>
                                    <th>Status</th>
                                    <th>Data Criação</th>
                                    <th>Última Atualização</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_produtos && mysqli_num_rows($result_produtos) > 0): ?>
                                    <?php while ($produto = mysqli_fetch_assoc($result_produtos)): ?>
                                        <tr>
                                            <td><?php echo $produto['idprodutos']; ?></td>
                                            <td>
                                                <?php if (!empty($produto['imagem_url'])): ?>
                                                    <img src="<?php echo htmlspecialchars($produto['imagem_url']); ?>" 
                                                         alt="<?php echo htmlspecialchars($produto['nomeProduto']); ?>" 
                                                         class="product-image">
                                                <?php else: ?>
                                                    <div class="no-image">
                                                        <i class="fas fa-image"></i>
                                                    </div>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <strong><?php echo htmlspecialchars($produto['nomeProduto']); ?></strong>
                                            </td>
                                            <td class="description">
                                                <?php echo !empty($produto['descProduto']) ? htmlspecialchars(substr($produto['descProduto'], 0, 50)) . '...' : '<span class="text-muted">Sem descrição</span>'; ?>
                                            </td>
                                            <td>
                                                <span class="category"><?php echo !empty($produto['categoria']) ? htmlspecialchars($produto['categoria']) : '<span class="text-muted">-</span>'; ?></span>
                                            </td>
                                            <td>
                                                <span class="status-badge <?php echo $produto['ativoProduto'] ? 'active' : 'inactive'; ?>">
                                                    <?php echo $produto['ativoProduto'] ? 'Ativo' : 'Inativo'; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($produto['data_criacao'])); ?>
                                            </td>
                                            <td>
                                                <?php echo date('d/m/Y H:i', strtotime($produto['data_atualizacao'])); ?>
                                            </td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="editarProduto(<?php echo $produto['idprodutos']; ?>)" 
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" 
                                                        onclick="confirmarExclusao(<?php echo $produto['idprodutos']; ?>, '<?php echo htmlspecialchars($produto['nomeProduto']); ?>')" 
                                                        title="Excluir">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="9" class="text-center text-muted py-4">
                                            <i class="fas fa-box fa-2x mb-2 d-block"></i>
                                            Nenhum produto encontrado
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

    <!-- Modal para Novo Produto -->
    <div class="modal-custom" id="addProductModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Novo Produto
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('addProductModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="" id="formNovoProduto">
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="nomeProduto" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Nome do Produto *
                                </label>
                                <input type="text" class="form-control" id="nomeProduto" name="nomeProduto" required 
                                       placeholder="Digite o nome do produto">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="categoria" class="form-label">
                                    <i class="fas fa-folder me-2"></i>Categoria
                                </label>
                                <select class="form-control" id="categoria" name="categoria">
                                    <option value="">Selecione uma categoria</option>
                                    <option value="Bombas">Bombas</option>
                                    <option value="Filtros">Filtros</option>
                                    <option value="Acessórios">Acessórios</option>
                                    <option value="Peças">Peças</option>
                                    <option value="Manutenção">Manutenção</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="descProduto" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Descrição
                        </label>
                        <textarea class="form-control" id="descProduto" name="descProduto" 
                                  rows="3" placeholder="Descrição detalhada do produto..."></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="imagem_url" class="form-label">
                                    <i class="fas fa-image me-2"></i>URL da Imagem
                                </label>
                                <input type="text" class="form-control" id="imagem_url" name="imagem_url" 
                                       placeholder="https://exemplo.com/imagem.jpg">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="ativoProduto" name="ativoProduto" checked>
                                    <label class="form-check-label" for="ativoProduto">Produto Ativo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('addProductModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="cadastrar_produto">
                            <i class="fas fa-save me-2"></i>Salvar Produto
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Produto -->
    <div class="modal-custom" id="editProductModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Produto
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('editProductModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <?php if ($produto_edicao): ?>
                <form method="POST" action="">
                    <input type="hidden" name="idprodutos" value="<?php echo $produto_edicao['idprodutos']; ?>">
                    
                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_nomeProduto" class="form-label">
                                    <i class="fas fa-tag me-2"></i>Nome do Produto *
                                </label>
                                <input type="text" class="form-control" id="edit_nomeProduto" name="nomeProduto" required 
                                       value="<?php echo htmlspecialchars($produto_edicao['nomeProduto']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="edit_categoria" class="form-label">
                                    <i class="fas fa-folder me-2"></i>Categoria
                                </label>
                                <select class="form-control" id="edit_categoria" name="categoria">
                                    <option value="">Selecione uma categoria</option>
                                    <option value="Bombas" <?php echo ($produto_edicao['categoria'] == 'Bombas') ? 'selected' : ''; ?>>Bombas</option>
                                    <option value="Filtros" <?php echo ($produto_edicao['categoria'] == 'Filtros') ? 'selected' : ''; ?>>Filtros</option>
                                    <option value="Acessórios" <?php echo ($produto_edicao['categoria'] == 'Acessórios') ? 'selected' : ''; ?>>Acessórios</option>
                                    <option value="Peças" <?php echo ($produto_edicao['categoria'] == 'Peças') ? 'selected' : ''; ?>>Peças</option>
                                    <option value="Manutenção" <?php echo ($produto_edicao['categoria'] == 'Manutenção') ? 'selected' : ''; ?>>Manutenção</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="edit_descProduto" class="form-label">
                            <i class="fas fa-align-left me-2"></i>Descrição
                        </label>
                        <textarea class="form-control" id="edit_descProduto" name="descProduto" 
                                  rows="3"><?php echo htmlspecialchars($produto_edicao['descProduto']); ?></textarea>
                    </div>

                    <div class="row">
                        <div class="col-md-8">
                            <div class="mb-3">
                                <label for="edit_imagem_url" class="form-label">
                                    <i class="fas fa-image me-2"></i>URL da Imagem
                                </label>
                                <input type="text" class="form-control" id="edit_imagem_url" name="imagem_url" 
                                       value="<?php echo htmlspecialchars($produto_edicao['imagem_url']); ?>">
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="mb-3">
                                <label class="form-label">Status</label>
                                <div class="form-check form-switch mt-2">
                                    <input class="form-check-input" type="checkbox" id="edit_ativoProduto" name="ativoProduto" 
                                           <?php echo $produto_edicao['ativoProduto'] ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="edit_ativoProduto">Produto Ativo</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('editProductModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="editar_produto">
                            <i class="fas fa-save me-2"></i>Atualizar Produto
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <p>Produto não encontrado para edição.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            
            // Se for o modal de NOVO produto, garante que está limpo
            if (modalId === 'addProductModal') {
                document.getElementById('formNovoProduto').reset();
            }
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function toggleFiltros() {
            const container = document.getElementById('filtrosContainer');
            container.classList.toggle('show');
        }

        function editarProduto(idprodutos) {
            window.location.href = '?editar=' + idprodutos;
        }

        function confirmarExclusao(idprodutos, nomeProduto) {
            if (confirm('Tem certeza que deseja excluir o produto "' + nomeProduto + '"?\n\nEsta ação não pode ser desfeita.')) {
                window.location.href = '?excluir=' + idprodutos;
            }
        }

        // Abrir modal de edição se houver parâmetro na URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('editar')) {
                abrirModal('editProductModal');
            }
            
            // Mostrar filtros se algum filtro estiver ativo
            const filtros = ['filtro_nome', 'filtro_categoria', 'filtro_status'];
            const hasActiveFilter = filtros.some(param => {
                const value = urlParams.get(param);
                return value && value !== '' && value !== 'todos';
            });
            
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