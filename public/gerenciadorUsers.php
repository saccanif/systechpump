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
    $tipo = $usuario['tipoUsuario'];
    $voltar = "adm.php";
    $nomeUsuario = $usuario['nomeUsuario'];
    $avatarUrl = $usuario['avatar_url'] ?? '';

    include_once "../config/connection.php";
    $conexao = conectarBD();
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Administrativo - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="stylesheet" href="./css/gerenciadorUsers.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .btn-excluir-desabilitado {
            opacity: 0.5;
            cursor: not-allowed;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <main class="main-content">
            <!-- Header -->
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <!-- Botão Voltar -->
                    <a href="adm.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0">Gerenciar Usuários</h1>
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

            <!-- Alert -->
            <?php if (isset($_GET['msg'])): ?>
                <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3" 
                     role="alert" id="alert-box" style="z-index: 1055;">
                    <?php echo htmlspecialchars($_GET['msg']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
                </div>
                <script>
                    setTimeout(() => {
                        const alertBox = document.getElementById("alert-box");
                        if (alertBox) {
                            let bsAlert = new bootstrap.Alert(alertBox);
                            bsAlert.close();
                        }
                    }, 3000);
                </script>
            <?php endif; ?>

            <!-- Conteúdo -->
            <div class="page-content">
                <div class="users-container">
                    <div class="users-header d-flex justify-content-between align-items-center mb-3">
                        <h2>Usuários do Sistema</h2>
                        <div class="users-actions d-flex gap-2">
                            <button class="btn btn-primary" id="filter-toggle">
                                <i class="fas fa-filter"></i> Filtros
                            </button>
                            <button class="btn btn-success" onclick="abrirModal('novoUsuarioModal')">
                                <i class="fas fa-plus"></i> Novo Usuário
                            </button>
                        </div>
                    </div>

                    <!-- FILTRO -->
                    <div id="filter-box" class="card p-3 mb-3 d-none">
                        <form method="GET" class="row g-2">
                            <div class="col-md-4">
                                <input type="text" name="nome" value="<?php echo $_GET['nome'] ?? ''; ?>" class="form-control" placeholder="Nome">
                            </div>
                            <div class="col-md-4">
                                <input type="email" name="email" value="<?php echo $_GET['email'] ?? ''; ?>" class="form-control" placeholder="E-mail">
                            </div>
                            <div class="col-md-2">
                                <select name="tipo" class="form-select">
                                    <option value="">Todos os Tipos</option>
                                    <option value="admin" <?php if(($_GET['tipo'] ?? '')==='admin') echo 'selected'; ?>>Admin</option>
                                    <option value="representante" <?php if(($_GET['tipo'] ?? '')==='representante') echo 'selected'; ?>>Representante</option>
                                    <option value="lojista" <?php if(($_GET['tipo'] ?? '')==='lojista') echo 'selected'; ?>>Lojista</option>
                                </select>
                            </div>
                            <div class="col-md-2 d-flex gap-2">
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="fas fa-search"></i> Filtrar
                                </button>
                                <a href="gerenciadorUsers.php" class="btn btn-secondary w-100">Limpar</a>
                            </div>
                        </form>
                    </div>

                    <!-- TABELA -->
                    <div class="users-table-container">
                        <table class="users-table table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Nome</th>
                                    <th>E-mail</th>
                                    <th>Tipo</th>
                                    <th>Cidade/Estado</th>
                                    <th>Perfil</th>
                                    <th>Data de Cadastro</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                           <tbody id="users-table-body">
                                <?php
                                    // Filtros
                                    $where = [];
                                    if (!empty($_GET['nome'])) {
                                        $nome = mysqli_real_escape_string($conexao, $_GET['nome']);
                                        $where[] = "u.nomeUsuario LIKE '%$nome%'";
                                    }
                                    if (!empty($_GET['email'])) {
                                        $email = mysqli_real_escape_string($conexao, $_GET['email']);
                                        $where[] = "u.emailUsuario LIKE '%$email%'";
                                    }
                                    if (!empty($_GET['tipo'])) {
                                        $tipo = mysqli_real_escape_string($conexao, $_GET['tipo']);
                                        $where[] = "u.tipoUsuario = '$tipo'";
                                    }

                                    // Query corrigida sem campo status
                                    $sql = "SELECT u.idUsuario, u.nomeUsuario, u.emailUsuario, u.tipoUsuario, 
                                                u.avatar_url, u.UsuCriadoEm, c.nomeCidade, e.nomeEstado, e.siglaEstado
                                            FROM usuario u
                                            LEFT JOIN cidade c ON u.cidade_idCidade = c.idCidade
                                            LEFT JOIN estado e ON u.idEstado = e.idEstado";
                                    
                                    if (count($where) > 0) {
                                        $sql .= " WHERE " . implode(" AND ", $where);
                                    }
                                    $sql .= " ORDER BY u.UsuCriadoEm DESC";

                                    $result = mysqli_query($conexao, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            $isAdminMaster = ($row['idUsuario'] == 38);
                                            
                                            echo "<tr>";
                                            echo "<td>".$row['idUsuario']."</td>";
                                            echo "<td>".$row['nomeUsuario'];
                                            // Adiciona badge de protegido para o admin master
                                            if ($isAdminMaster) {
                                                echo " <span class='badge bg-warning' title='Admin master - não pode ser excluído'>Master</span>";
                                            }
                                            echo "</td>";
                                            echo "<td>".$row['emailUsuario']."</td>";
                                            echo "<td>".$row['tipoUsuario']."</td>";
                                            echo "<td>";
                                            if ($row['nomeCidade']) {
                                                echo $row['nomeCidade'] . " - " . $row['siglaEstado'];
                                            } else {
                                                echo "<span class='text-muted'>Não informado</span>";
                                            }
                                            echo "</td>";
                                            echo "<td><img src='".($row['avatar_url'] ?: './imgs/others/default-avatar.png')."' alt='avatar' style='width:40px;height:40px;border-radius:50%;'></td>";
                                            echo "<td>".date('d/m/Y H:i', strtotime($row['UsuCriadoEm']))."</td>";
                                            echo "<td>";
                                            
                                            // Botão Editar (sempre visível)
                                            echo "<button class='btn btn-sm btn-outline-primary me-1' 
                                                    onclick=\"abrirModalEditar('".$row['idUsuario']."', '".$row['nomeUsuario']."', '".$row['emailUsuario']."', '".$row['tipoUsuario']."', '".$row['avatar_url']."')\" 
                                                    title='Editar'>
                                                <i class='fas fa-edit'></i>
                                            </button>";

                                            // Botão Excluir
                                            if ($isAdminMaster) {
                                                echo "<button class='btn btn-sm btn-outline-danger btn-excluir-desabilitado' 
                                                        title='Admin master não pode ser excluído' 
                                                        disabled>
                                                    <i class='fas fa-trash'></i>
                                                </button>";
                                            } else {
                                                // Botão excluir para outros usuários
                                                echo "<a href='../src/controllers/usersController.php?delete=".$row['idUsuario']."' 
                                                    class='btn btn-sm btn-outline-danger'
                                                    onclick=\"return confirm('Tem certeza que deseja excluir este usuário?')\"
                                                    title='Excluir Usuário'>
                                                    <i class='fas fa-trash'></i>
                                                </a>";
                                            }
                                            echo "</td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='8' class='text-center py-4'>
                                                <i class='fas fa-users fa-2x text-muted mb-3'></i><br>
                                                Nenhum usuário encontrado
                                              </td></tr>";
                                    }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>

            <!-- Footer -->
            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <!-- Modal para Novo Usuário -->
    <div class="modal-custom" id="novoUsuarioModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-plus-circle me-2"></i>Novo Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('novoUsuarioModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="../src/controllers/usersController.php" id="formNovoUsuario">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="nomeUsuario" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nome *
                                </label>
                                <input type="text" class="form-control" id="nomeUsuario" name="nomeUsuario" required 
                                       placeholder="Digite o nome completo">
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="emailUsuario" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-mail *
                                </label>
                                <input type="email" class="form-control" id="emailUsuario" name="emailUsuario" required 
                                       placeholder="usuario@exemplo.com">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="tipoUsuario" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Tipo de Usuário *
                                </label>
                                <select class="form-control" id="tipoUsuario" name="tipoUsuario" required>
                                    <option value="">Selecione o tipo</option>
                                    <option value="admin">Administrador</option>
                                    <option value="representante">Representante</option>
                                    <option value="lojista">Lojista</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <!-- Espaço para alinhamento -->
                        </div>
                    </div>

                    <!-- CAMPOS DE SENHA PARA TODOS OS TIPOS -->
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="senha" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Senha *
                                </label>
                                <input type="password" class="form-control" id="senha" name="senha" required
                                       placeholder="Digite a senha" minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="confirmar_senha" class="form-label">
                                    <i class="fas fa-lock me-2"></i>Confirmar Senha *
                                </label>
                                <input type="password" class="form-control" id="confirmar_senha" name="confirmar_senha" required
                                       placeholder="Confirme a senha" minlength="6">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-12">
                            <div class="mb-3">
                                <label for="avatar_url" class="form-label">
                                    <i class="fas fa-image me-2"></i>Avatar (URL)
                                </label>
                                <input type="text" class="form-control" id="avatar_url" name="avatar_url" 
                                       placeholder="https://exemplo.com/avatar.jpg">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="idEstado" class="form-label">
                                    <i class="fas fa-map me-2"></i>Estado *
                                </label>
                                <select class="form-control" id="idEstado" name="idEstado" required>
                                    <option value="">Selecione um estado</option>
                                    <?php
                                        $sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
                                        $res_estados = mysqli_query($conexao, $sql_estados);
                                        
                                        while ($estado = mysqli_fetch_assoc($res_estados)) {
                                            $idEstado = $estado["idEstado"];
                                            $nomeEstado = $estado["nomeEstado"];
                                            $siglaEstado = $estado["siglaEstado"];
                                            
                                            echo "<option value='$idEstado'>$nomeEstado ($siglaEstado)</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="idCidade" class="form-label">
                                    <i class="fas fa-city me-2"></i>Cidade *
                                </label>
                                <select class="form-control" id="idCidade" name="idCidade" required>
                                    <option value="">Selecione uma cidade</option>
                                    <?php
                                        $sql_cidades = "SELECT c.idCidade, c.nomeCidade, e.nomeEstado, e.siglaEstado 
                                                    FROM cidade c 
                                                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                                                    ORDER BY e.nomeEstado, c.nomeCidade";
                                        $res_cidades = mysqli_query($conexao, $sql_cidades);
                                        
                                        while ($cidade = mysqli_fetch_assoc($res_cidades)) {
                                            $idCidade = $cidade["idCidade"];
                                            $nomeCidade = $cidade["nomeCidade"];
                                            $nomeEstado = $cidade["nomeEstado"];
                                            $siglaEstado = $cidade["siglaEstado"];
                                            
                                            echo "<option value='$idCidade'>$nomeCidade - $siglaEstado</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('novoUsuarioModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="cadastrar_usuario">
                            <i class="fas fa-save me-2"></i>Salvar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal para Editar Usuário -->
    <div class="modal-custom" id="editarUsuarioModal">
        <div class="modal-content-custom">
            <div class="modal-header-custom">
                <h5 class="modal-title">
                    <i class="fas fa-edit me-2"></i>Editar Usuário
                </h5>
                <button type="button" class="btn-close btn-close-white" onclick="fecharModal('editarUsuarioModal')" aria-label="Close"></button>
            </div>
            <div class="modal-body-custom">
                <form method="POST" action="../src/controllers/usersController.php" id="formEditarUsuario">
                    <input type="hidden" name="idUsuario" id="edit-id">
                    <input type="hidden" name="update" value="1">
                    
                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-nomeUsuario" class="form-label">
                                    <i class="fas fa-user me-2"></i>Nome *
                                </label>
                                <input type="text" class="form-control" id="edit-nomeUsuario" name="nomeUsuario" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-emailUsuario" class="form-label">
                                    <i class="fas fa-envelope me-2"></i>E-mail *
                                </label>
                                <input type="email" class="form-control" id="edit-emailUsuario" name="emailUsuario" required>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-tipoUsuario" class="form-label">
                                    <i class="fas fa-user-tag me-2"></i>Tipo *
                                </label>
                                <select class="form-control" id="edit-tipoUsuario" name="tipoUsuario" required>
                                    <option value="admin">Admin</option>
                                    <option value="representante">Representante</option>
                                    <option value="lojista">Lojista</option>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-avatar_url" class="form-label">
                                    <i class="fas fa-image me-2"></i>Avatar (URL)
                                </label>
                                <input type="text" class="form-control" id="edit-avatar_url" name="avatar_url" 
                                       placeholder="https://exemplo.com/avatar.jpg">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-idEstado" class="form-label">
                                    <i class="fas fa-map me-2"></i>Estado *
                                </label>
                                <select class="form-control" id="edit-idEstado" name="idEstado" required>
                                    <option value="">Selecione um estado</option>
                                    <?php
                                        $sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
                                        $res_estados = mysqli_query($conexao, $sql_estados);
                                        
                                        while ($estado = mysqli_fetch_assoc($res_estados)) {
                                            $idEstado = $estado["idEstado"];
                                            $nomeEstado = $estado["nomeEstado"];
                                            $siglaEstado = $estado["siglaEstado"];
                                            
                                            echo "<option value='$idEstado'>$nomeEstado ($siglaEstado)</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="mb-3">
                                <label for="edit-idCidade" class="form-label">
                                    <i class="fas fa-city me-2"></i>Cidade *
                                </label>
                                <select class="form-control" id="edit-idCidade" name="idCidade" required>
                                    <option value="">Selecione uma cidade</option>
                                    <?php
                                        $sql_cidades = "SELECT c.idCidade, c.nomeCidade, e.nomeEstado, e.siglaEstado 
                                                    FROM cidade c 
                                                    INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                                                    ORDER BY e.nomeEstado, c.nomeCidade";
                                        $res_cidades = mysqli_query($conexao, $sql_cidades);
                                        
                                        while ($cidade = mysqli_fetch_assoc($res_cidades)) {
                                            $idCidade = $cidade["idCidade"];
                                            $nomeCidade = $cidade["nomeCidade"];
                                            $nomeEstado = $cidade["nomeEstado"];
                                            $siglaEstado = $cidade["siglaEstado"];
                                            
                                            echo "<option value='$idCidade'>$nomeCidade - $siglaEstado</option>";
                                        }
                                    ?>
                                </select>
                            </div>
                        </div>
                    </div>

                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" onclick="fecharModal('editarUsuarioModal')">
                            <i class="fas fa-times me-2"></i>Cancelar
                        </button>
                        <button type="submit" class="btn btn-primary" name="editar_usuario">
                            <i class="fas fa-save me-2"></i>Atualizar Usuário
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        function abrirModal(modalId) {
            document.getElementById(modalId).style.display = 'block';
            
            // Se for o modal de NOVO usuário, garante que está limpo
            if (modalId === 'novoUsuarioModal') {
                document.getElementById('formNovoUsuario').reset();
            }
        }

        function fecharModal(modalId) {
            document.getElementById(modalId).style.display = 'none';
        }

        function abrirModalEditar(id, nome, email, tipo, avatar) {
            document.getElementById('edit-id').value = id;
            document.getElementById('edit-nomeUsuario').value = nome;
            document.getElementById('edit-emailUsuario').value = email;
            document.getElementById('edit-tipoUsuario').value = tipo;
            document.getElementById('edit-avatar_url').value = avatar || '';
            
            abrirModal('editarUsuarioModal');
        }

        // Toggle filtro
        document.getElementById("filter-toggle").addEventListener("click", () => {
            document.getElementById("filter-box").classList.toggle("d-none");
        });

        // Fechar modal clicando fora
        window.onclick = function(event) {
            const modals = document.querySelectorAll('.modal-custom');
            modals.forEach(modal => {
                if (event.target === modal) {
                    modal.style.display = 'none';
                }
            });
        }

        // Validar formulário antes de enviar
        document.getElementById('formNovoUsuario')?.addEventListener('submit', function(e) {
            const senha = document.getElementById('senha').value;
            const confirmarSenha = document.getElementById('confirmar_senha').value;
            
            // Validar senha
            if (senha !== confirmarSenha) {
                e.preventDefault();
                alert('As senhas não coincidem!');
                return false;
            }
            
            if (senha.length < 6) {
                e.preventDefault();
                alert('A senha deve ter pelo menos 6 caracteres!');
                return false;
            }
            
            return true;
        });

        // Fallback para avatares com erro de carregamento
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

<?php
    // Fecha a conexão com o banco
    if (isset($conexao)) {
        mysqli_close($conexao);
    }
?>