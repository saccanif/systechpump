<?php
    session_start();

    // Verificação simples de sessão
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php?erro=Faça login para acessar");
        exit();
    }

    // Verificar se é admin OU representante
    $tipoUsuario = $_SESSION['usuario']['tipoUsuario'];
    if ($tipoUsuario !== 'admin' && $tipoUsuario !== 'representante') {
        header("Location: login.php?erro=Acesso não autorizado");
        exit();
    }

    $usuario = $_SESSION['usuario'];
    $nomeUsuario = $usuario['nomeUsuario'];
    $avatarUrl = $usuario['avatar_url'] ?? '';
    
    // Definir página de voltar baseada no tipo de usuário
    if ($tipoUsuario === 'admin') {
        $voltar = "adm.php";
    } elseif ($tipoUsuario === 'representante') {
        $voltar = "representante.php";
    } else {
        $voltar = "login.php"; 
    }

    // Conexão com banco
    require_once "../config/connection.php";
    $conexao = conectarBD();

    // Variáveis para filtros
    $filtro_nome = $_GET['filtro_nome'] ?? '';
    $filtro_estado = $_GET['filtro_estado'] ?? '';

    // Processar formulário se for submetido
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // CADASTRAR NOVA CIDADE
        if (isset($_POST['cadastrar_cidade'])) {
            $nomeCidade = $_POST['nomeCidade'] ?? '';
            $estado_idEstado = $_POST['estado_idEstado'] ?? '';
            $qtdaAcesso = $_POST['qtdaAcesso'] ?? 0;

            // Validações
            if (empty($nomeCidade) || empty($estado_idEstado)) {
                $erro = "Nome da cidade e estado são obrigatórios";
            } else {
                $conexao = conectarBD();
                
                // Verificar se cidade já existe
                $sql_verifica = "SELECT idCidade FROM cidade WHERE nomeCidade = ? AND estado_idEstado = ?";
                $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
                mysqli_stmt_bind_param($stmt_verifica, "si", $nomeCidade, $estado_idEstado);
                mysqli_stmt_execute($stmt_verifica);
                $result_verifica = mysqli_stmt_get_result($stmt_verifica);

                if (mysqli_num_rows($result_verifica) > 0) {
                    $erro = "Cidade já cadastrada neste estado";
                } else {
                    // Inserir nova cidade
                    $sql_insere = "INSERT INTO cidade (nomeCidade, estado_idEstado, qtdaAcesso) 
                                VALUES (?, ?, ?)";
                    $stmt_insere = mysqli_prepare($conexao, $sql_insere);
                    mysqli_stmt_bind_param($stmt_insere, "sii", $nomeCidade, $estado_idEstado, $qtdaAcesso);

                    if (mysqli_stmt_execute($stmt_insere)) {
                        $sucesso = "Cidade cadastrada com sucesso!";
                        
                        // Limpar POST para não manter dados no form
                        unset($_POST);
                    } else {
                        $erro = "Erro ao cadastrar cidade: " . mysqli_error($conexao);
                    }
                }
            }
        }
        
        // EDITAR CIDADE
        if (isset($_POST['editar_cidade'])) {
            $idCidade = $_POST['idCidade'] ?? '';
            $nomeCidade = $_POST['nomeCidade'] ?? '';
            $estado_idEstado = $_POST['estado_idEstado'] ?? '';
            $qtdaAcesso = $_POST['qtdaAcesso'] ?? 0;

            if (empty($nomeCidade) || empty($estado_idEstado) || empty($idCidade)) {
                $erro = "Todos os campos são obrigatórios";
            } else {
                $conexao = conectarBD();
                
                // Verificar se cidade já existe (excluindo a atual)
                $sql_verifica = "SELECT idCidade FROM cidade WHERE nomeCidade = ? AND estado_idEstado = ? AND idCidade != ?";
                $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
                mysqli_stmt_bind_param($stmt_verifica, "sii", $nomeCidade, $estado_idEstado, $idCidade);
                mysqli_stmt_execute($stmt_verifica);
                $result_verifica = mysqli_stmt_get_result($stmt_verifica);

                if (mysqli_num_rows($result_verifica) > 0) {
                    $erro = "Cidade já cadastrada neste estado";
                } else {
                    // Atualizar cidade
                    $sql_atualiza = "UPDATE cidade SET nomeCidade = ?, estado_idEstado = ?, qtdaAcesso = ? 
                                    WHERE idCidade = ?";
                    $stmt_atualiza = mysqli_prepare($conexao, $sql_atualiza);
                    mysqli_stmt_bind_param($stmt_atualiza, "siii", $nomeCidade, $estado_idEstado, $qtdaAcesso, $idCidade);

                    if (mysqli_stmt_execute($stmt_atualiza)) {
                        $sucesso = "Cidade atualizada com sucesso!";
                    } else {
                        $erro = "Erro ao atualizar cidade: " . mysqli_error($conexao);
                    }
                }
            }
        }
    }

    // EXCLUIR CIDADE (via GET) - APENAS ADMIN pode excluir
    if (isset($_GET['excluir']) && $tipoUsuario === 'admin') {
        $idCidade = $_GET['excluir'];
        
        if (!empty($idCidade) && is_numeric($idCidade)) {
            $conexao = conectarBD();
            
            // Primeiro, buscar o nome da cidade para verificar se é Colatina
            $sql_busca_cidade = "SELECT nomeCidade FROM cidade WHERE idCidade = ?";
            $stmt_busca = mysqli_prepare($conexao, $sql_busca_cidade);
            mysqli_stmt_bind_param($stmt_busca, "i", $idCidade);
            mysqli_stmt_execute($stmt_busca);
            $result_busca = mysqli_stmt_get_result($stmt_busca);
            
            if ($cidade = mysqli_fetch_assoc($result_busca)) {
                $nomeCidade = $cidade['nomeCidade'];
                
                // Verificar se é Colatina (não pode ser excluída)
                if (strtolower($nomeCidade) === 'colatina') {
                    $erro = "Não é possível excluir a cidade de Colatina, pois é a cidade do administrador do sistema.";
                } else {
                    // Excluir cidade (não é Colatina)
                    $sql_exclui = "DELETE FROM cidade WHERE idCidade = ?";
                    $stmt_exclui = mysqli_prepare($conexao, $sql_exclui);
                    mysqli_stmt_bind_param($stmt_exclui, "i", $idCidade);
                    
                    if (mysqli_stmt_execute($stmt_exclui)) {
                        if (mysqli_affected_rows($conexao) > 0) {
                            $sucesso = "Cidade excluída com sucesso!";
                        } else {
                            $erro = "Cidade não encontrada ou já foi excluída.";
                        }
                    } else {
                        $erro = "Erro ao excluir cidade. Pode haver registros vinculados a esta cidade.";
                    }
                }
            } else {
                $erro = "Cidade não encontrada.";
            }
        }
    }

    // Buscar cidades para a tabela com filtros
    $conexao = conectarBD();
    $where_conditions = [];
    $params = [];
    $types = "";

    if (!empty($filtro_nome)) {
        $where_conditions[] = "c.nomeCidade LIKE ?";
        $params[] = "%$filtro_nome%";
        $types .= "s";
    }

    if (!empty($filtro_estado)) {
        $where_conditions[] = "c.estado_idEstado = ?";
        $params[] = $filtro_estado;
        $types .= "i";
    }

    $where_sql = "";
    if (!empty($where_conditions)) {
        $where_sql = "WHERE " . implode(" AND ", $where_conditions);
    }

    $sql_cidades = "SELECT c.*, e.nomeEstado, e.siglaEstado 
                    FROM cidade c 
                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                    $where_sql
                    ORDER BY c.idCidade DESC 
                    LIMIT 50";

    $stmt_cidades = mysqli_prepare($conexao, $sql_cidades);
    if (!empty($params)) {
        mysqli_stmt_bind_param($stmt_cidades, $types, ...$params);
    }
    mysqli_stmt_execute($stmt_cidades);
    $result_cidades = mysqli_stmt_get_result($stmt_cidades);

    // Buscar estados para os selects
    $sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
    $result_estados = mysqli_query($conexao, $sql_estados);

    // Buscar cidade específica para edição
    $cidade_edicao = null;
    if (isset($_GET['editar'])) {
        $idCidade = $_GET['editar'];
        $sql_cidade = "SELECT c.*, e.nomeEstado, e.siglaEstado 
                    FROM cidade c 
                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                    WHERE c.idCidade = ?";
        $stmt_cidade = mysqli_prepare($conexao, $sql_cidade);
        mysqli_stmt_bind_param($stmt_cidade, "i", $idCidade);
        mysqli_stmt_execute($stmt_cidade);
        $result_cidade = mysqli_stmt_get_result($stmt_cidade);
        $cidade_edicao = mysqli_fetch_assoc($result_cidade);
    }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Cidades - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link rel="stylesheet" href="./css/gerenciadorCidades.css">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .modal-custom {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
        }
        .modal-content-custom {
            background: white;
            margin: 5% auto;
            padding: 0;
            border-radius: 15px;
            max-width: 700px;
            box-shadow: 0 20px 40px rgba(0,0,0,0.1);
        }
        .modal-header-custom {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 20px;
            border-radius: 15px 15px 0 0;
        }
        .modal-body-custom {
            padding: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #2c3e50;
        }
        .alert-custom {
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .alert-success {
            background: #d4edda;
            color: #155724;
            border: 1px solid #c3e6cb;
        }
        .alert-error {
            background: #f8d7da;
            color: #721c24;
            border: 1px solid #f5c6cb;
        }
        .filtros-container {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 20px;
            display: none;
        }
        .filtros-container.show {
            display: block;
        }
        .btn-excluir-desabilitado {
            opacity: 0.5;
            cursor: not-allowed !important;
        }
        .badge-protegido {
            background-color: #6c757d;
            color: white;
            font-size: 0.7em;
            margin-left: 5px;
        }
    </style>
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
                    <h1 class="mb-0">Gerenciar Cidades</h1> 
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
                <?php if (isset($sucesso)): ?>
                    <div class="alert-custom alert-success">
                         <?php echo $sucesso; ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($erro)): ?>
                    <div class="alert-custom alert-error">
                         <?php echo $erro; ?>
                    </div>
                <?php endif; ?>

                <div class="cities-container">
                    <div class="cities-header">
                        <h2>Cidades Atendidas</h2>
                        <div class="cities-actions">
                            <button class="btn btn-primary" onclick="toggleFiltros()">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                            <button class="btn btn-success" onclick="abrirModal('addCityModal')">
                                <i class="fas fa-plus"></i> Nova Cidade
                            </button>
                        </div>
                    </div>

                    <!-- Filtros -->
                    <div class="filtros-container" id="filtrosContainer">
                        <form method="GET" action="" class="row g-3">
                            <div class="col-md-5">
                                <label for="filtro_nome" class="form-label">Nome da Cidade</label>
                                <input type="text" class="form-control" id="filtro_nome" name="filtro_nome" 
                                       value="<?php echo htmlspecialchars($filtro_nome); ?>" placeholder="Filtrar por nome...">
                            </div>
                            <div class="col-md-5">
                                <label for="filtro_estado" class="form-label">Estado</label>
                                <select class="form-control" id="filtro_estado" name="filtro_estado">
                                    <option value="">Todos os estados</option>
                                    <?php 
                                    mysqli_data_seek($result_estados, 0); // Reset do ponteiro do resultado
                                    if ($result_estados): ?>
                                        <?php while ($estado = mysqli_fetch_assoc($result_estados)): ?>
                                            <option value="<?php echo $estado['idEstado']; ?>" 
                                                <?php echo ($filtro_estado == $estado['idEstado']) ? 'selected' : ''; ?>>
                                                <?php echo $estado['nomeEstado']; ?>
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

                    <!-- Tabela de Cidades -->
                    <div class="cities-table-container">
                        <table class="cities-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Cidade</th>
                                    <th>Estado</th>
                                    <th>Qtda Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($result_cidades && mysqli_num_rows($result_cidades) > 0): ?>
                                    <?php while ($cidade = mysqli_fetch_assoc($result_cidades)): ?>
                                        <?php $isColatina = (strtolower($cidade['nomeCidade']) === 'colatina'); ?>
                                        <tr>
                                            <td><?php echo $cidade['idCidade']; ?></td>
                                            <td>
                                                <?php echo htmlspecialchars($cidade['nomeCidade']); ?>
                                                <?php if ($isColatina): ?>
                                                    <span class="badge-protegido" title="Cidade protegida - não pode ser excluída"></span>
                                                <?php endif; ?>
                                            </td>
                                            <td><?php echo $cidade['siglaEstado']; ?></td>
                                            <td><?php echo number_format($cidade['qtdaAcesso'], 0, ',', '.'); ?></td>
                                            <td>
                                                <button class="btn btn-sm btn-outline-primary me-1" 
                                                        onclick="editarCidade(<?php echo $cidade['idCidade']; ?>)" 
                                                        title="Editar">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <?php if ($tipoUsuario === 'admin'): ?>
                                                    <?php if ($isColatina): ?>
                                                        <button class="btn btn-sm btn-outline-danger btn-excluir-desabilitado" 
                                                                title="Esta cidade não pode ser excluída" disabled>
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php else: ?>
                                                        <button class="btn btn-sm btn-outline-danger" 
                                                                onclick="confirmarExclusao(<?php echo $cidade['idCidade']; ?>, '<?php echo htmlspecialchars($cidade['nomeCidade']); ?>')" 
                                                                title="Excluir">
                                                            <i class="fas fa-trash"></i>
                                                        </button>
                                                    <?php endif; ?>
                                                <?php else: ?>
                                                    <!-- Representante não vê botão de excluir -->
                                                    <button class="btn btn-sm btn-outline-secondary" disabled title="Apenas administradores podem excluir cidades">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted">
                                            Nenhuma cidade encontrada
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

    <!-- Modal para Nova Cidade -->
    <div class="modal-custom" id="addCityModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Nova Cidade
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('addCityModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="" id="formNovaCidade">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomeCidade" class="form-label">
                                    <i class="fas fa-city me-2"></i>Nome da Cidade *
                                </label>
                                <input type="text" class="form-control" id="nomeCidade" name="nomeCidade" required 
                                    placeholder="Digite o nome da cidade" value="">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="estado_idEstado" class="form-label">
                                    <i class="fas fa-map me-2"></i>Estado *
                                </label>
                                <select class="form-control" id="estado_idEstado" name="estado_idEstado" required>
                                    <option value="">Selecione o estado</option>
                                    <?php 
                                    mysqli_data_seek($result_estados, 0);
                                    if ($result_estados): ?>
                                        <?php while ($estado = mysqli_fetch_assoc($result_estados)): ?>
                                            <option value="<?php echo $estado['idEstado']; ?>">
                                                <?php echo $estado['nomeEstado'] . ' - ' . $estado['siglaEstado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="qtdaAcesso" class="form-label">
                                    <i class="fas fa-users me-2"></i>Quantidade de Acesso
                                </label>
                                <input type="number" class="form-control" id="qtdaAcesso" name="qtdaAcesso" 
                                    value="0" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('addCityModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="cadastrar_cidade">
                            <i class="fas fa-save me-2"></i>Salvar Cidade
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Cidade -->
    <div class="modal-custom" id="editCityModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Cidade
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('editCityModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <?php if ($cidade_edicao): ?>
                <form method="POST" action="">
                    <input type="hidden" name="idCidade" value="<?php echo $cidade_edicao['idCidade']; ?>">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_nomeCidade" class="form-label">
                                    <i class="fas fa-city me-2"></i>Nome da Cidade *
                                </label>
                                <input type="text" class="form-control" id="edit_nomeCidade" name="nomeCidade" required 
                                       value="<?php echo htmlspecialchars($cidade_edicao['nomeCidade']); ?>">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_estado_idEstado" class="form-label">
                                    <i class="fas fa-map me-2"></i>Estado *
                                </label>
                                <select class="form-control" id="edit_estado_idEstado" name="estado_idEstado" required>
                                    <option value="">Selecione o estado</option>
                                    <?php 
                                    mysqli_data_seek($result_estados, 0); // Reset do ponteiro do resultado
                                    if ($result_estados): ?>
                                        <?php while ($estado = mysqli_fetch_assoc($result_estados)): ?>
                                            <option value="<?php echo $estado['idEstado']; ?>" 
                                                <?php echo ($cidade_edicao['estado_idEstado'] == $estado['idEstado']) ? 'selected' : ''; ?>>
                                                <?php echo $estado['nomeEstado'] . ' - ' . $estado['siglaEstado']; ?>
                                            </option>
                                        <?php endwhile; ?>
                                    <?php endif; ?>
                                </select>
                            </div>
                        </div>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit_qtdaAcesso" class="form-label">
                                    <i class="fas fa-users me-2"></i>Quantidade de Acesso
                                </label>
                                <input type="number" class="form-control" id="edit_qtdaAcesso" name="qtdaAcesso" 
                                       value="<?php echo $cidade_edicao['qtdaAcesso']; ?>" min="0">
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('editCityModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="editar_cidade">
                            <i class="fas fa-save me-2"></i>Atualizar Cidade
                        </button>
                    </div>
                </form>
                <?php else: ?>
                    <div class="text-center text-muted py-4">
                        <i class="fas fa-exclamation-triangle fa-2x mb-3"></i>
                        <p>Cidade não encontrada para edição.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            
            // Se for o modal de NOVA cidade, garante que está limpo
            if (modalId === 'addCityModal') {
                document.getElementById('formNovaCidade').reset();
                // Define valor padrão para quantidade de acesso
                document.getElementById('qtdaAcesso').value = '0';
            }
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function toggleFiltros() {
            const container = document.getElementById('filtrosContainer');
            container.classList.toggle('show');
        }

        function editarCidade(idCidade) {
            window.location.href = '?editar=' + idCidade;
        }

        function confirmarExclusao(idCidade, nomeCidade) {
            if (confirm('Tem certeza que deseja excluir a cidade "' + nomeCidade + '"?\n\nEsta ação não pode ser desfeita.')) {
                window.location.href = '?excluir=' + idCidade;
            }
        }

        // Abrir modal de edição se houver parâmetro na URL
        window.onload = function() {
            const urlParams = new URLSearchParams(window.location.search);
            if (urlParams.has('editar')) {
                abrirModal('editCityModal');
            }
            
            // Mostrar filtros se algum filtro estiver ativo
            const filtros = ['filtro_nome', 'filtro_estado'];
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