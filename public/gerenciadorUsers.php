<?php
    session_start();
    if (!isset($_SESSION['usuario'])) {
        header("Location: login.php");
        exit();
    }

    $tipo = $_SESSION['usuario']['tipoUsuario'];

    if ($tipo === 'admin') {
        $voltar = "adm.php";
    } else {
        $voltar = "login.php";
    }

    // Conexão com o banco para os comboboxes
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
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
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

                <div class="user-info d-flex align-items-center gap-2">
                    <div class="user-avatar">US</div>
                    <span><?php echo $_SESSION['usuario']['nomeUsuario'] ?? 'Visitante'; ?></span>
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
                            <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#novoUsuarioModal">
                                <i class="fas fa-plus"></i> Novo Usuário
                            </button>
                        </div>
                    </div>

                    <!-- FILTRO -->
                    <div id="filter-box" class="card p-3 mb-3 d-none">
                        <form method="GET" class="row g-2">
                            <div class="col-md-3">
                                <input type="text" name="nome" value="<?php echo $_GET['nome'] ?? ''; ?>" class="form-control" placeholder="Nome">
                            </div>
                            <div class="col-md-3">
                                <input type="email" name="email" value="<?php echo $_GET['email'] ?? ''; ?>" class="form-control" placeholder="E-mail">
                            </div>
                            <div class="col-md-3">
                                <select name="tipo" class="form-select">
                                    <option value="">Todos os Tipos</option>
                                    <option value="admin" <?php if(($_GET['tipo'] ?? '')==='admin') echo 'selected'; ?>>Admin</option>
                                    <option value="representante" <?php if(($_GET['tipo'] ?? '')==='representante') echo 'selected'; ?>>Representante</option>
                                    <option value="lojista" <?php if(($_GET['tipo'] ?? '')==='lojista') echo 'selected'; ?>>Lojista</option>
                                </select>
                            </div>
                            <div class="col-md-3 d-flex gap-2">
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
                                        $where[] = "nomeUsuario LIKE '%$nome%'";
                                    }
                                    if (!empty($_GET['email'])) {
                                        $email = mysqli_real_escape_string($conexao, $_GET['email']);
                                        $where[] = "emailUsuario LIKE '%$email%'";
                                    }
                                    if (!empty($_GET['tipo'])) {
                                        $tipo = mysqli_real_escape_string($conexao, $_GET['tipo']);
                                        $where[] = "tipoUsuario = '$tipo'";
                                    }

                                    $sql = "SELECT idUsuario, nomeUsuario, emailUsuario, tipoUsuario, avatar_url, UsuCriadoEm 
                                            FROM usuario";
                                    if (count($where) > 0) {
                                        $sql .= " WHERE " . implode(" AND ", $where);
                                    }
                                    $sql .= " ORDER BY UsuCriadoEm DESC";

                                    $result = mysqli_query($conexao, $sql);

                                    if (mysqli_num_rows($result) > 0) {
                                        while ($row = mysqli_fetch_assoc($result)) {
                                            echo "<tr>";
                                            echo "<td>".$row['idUsuario']."</td>";
                                            echo "<td>".$row['nomeUsuario']."</td>";
                                            echo "<td>".$row['emailUsuario']."</td>";
                                            echo "<td>".$row['tipoUsuario']."</td>";
                                            echo "<td><img src='".$row['avatar_url']."' alt='avatar' style='width:40px;height:40px;border-radius:50%;'></td>";
                                            echo "<td>".$row['UsuCriadoEm']."</td>";
                                            echo "<td>
                                                    <button class='btn btn-sm btn-warning' 
                                                            data-bs-toggle='modal' 
                                                            data-bs-target='#editarUsuarioModal'
                                                            data-id='".$row['idUsuario']."'
                                                            data-nome='".$row['nomeUsuario']."'
                                                            data-email='".$row['emailUsuario']."'
                                                            data-tipo='".$row['tipoUsuario']."'
                                                            data-avatar='".$row['avatar_url']."'>
                                                        Editar
                                                    </button>
                                                    <a href='../src/controllers/usersController.php?delete=".$row['idUsuario']."' 
                                                       class='btn btn-sm btn-danger'
                                                       onclick=\"return confirm('Tem certeza que deseja excluir este usuário?')\">
                                                       Excluir
                                                    </a>
                                                </td>";
                                            echo "</tr>";
                                        }
                                    } else {
                                        echo "<tr><td colspan='7'>Nenhum usuário encontrado</td></tr>";
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

    <!-- Modal Cadastro -->
    <div class="modal fade" id="novoUsuarioModal" tabindex="-1" aria-labelledby="novoUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="../src/controllers/usersController.php" method="POST">
            <div class="modal-header">
                <h5 class="modal-title">Cadastrar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label>Nome</label><input type="text" class="form-control" name="nomeUsuario" required></div>
                <div class="mb-3"><label>Email</label><input type="email" class="form-control" name="emailUsuario" required></div>
                <div class="mb-3"><label>Senha</label><input type="password" class="form-control" name="senha" required></div>
                <div class="mb-3"><label>Tipo</label>
                    <select class="form-select" name="tipoUsuario" required>
                        <option value="admin">Admin</option>
                        <option value="representante">Representante</option>
                        <option value="lojista">Lojista</option>
                    </select>
                </div>
                <div class="mb-3"><label>Avatar (URL)</label><input type="text" class="form-control" name="avatar_url"></div>
                
                <!-- COMBOBOX CIDADE -->
                <div class="mb-3">
                    <label>Cidade</label>
                    <select class="form-select" name="idCidade" required>
                        <option value="">Selecione uma cidade</option>
                        <?php
                            $sql_cidades = "SELECT idCidade, nomeCidade FROM cidade ORDER BY nomeCidade";
                            $res_cidades = mysqli_query($conexao, $sql_cidades);
                            
                            while ($registro = mysqli_fetch_assoc($res_cidades)) {
                                $idCidade = $registro["idCidade"];
                                $nomeCidade = $registro["nomeCidade"];
                                
                                echo "<option value='$idCidade'>$nomeCidade</option>";
                            }
                        ?>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn btn-success">Salvar</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Modal Editar -->
    <div class="modal fade" id="editarUsuarioModal" tabindex="-1" aria-labelledby="editarUsuarioLabel" aria-hidden="true">
      <div class="modal-dialog">
        <div class="modal-content">
          <form action="../src/controllers/usersController.php" method="POST">
            <input type="hidden" name="idUsuario" id="edit-id">
            <div class="modal-header">
                <h5 class="modal-title">Editar Usuário</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3"><label>Nome</label><input type="text" class="form-control" id="edit-nome" name="nomeUsuario" required></div>
                <div class="mb-3"><label>Email</label><input type="email" class="form-control" id="edit-email" name="emailUsuario" required></div>
                <div class="mb-3"><label>Tipo</label>
                    <select class="form-select" id="edit-tipo" name="tipoUsuario" required>
                        <option value="admin">Admin</option>
                        <option value="representante">Representante</option>
                        <option value="lojista">Lojista</option>
                    </select>
                </div>
                <div class="mb-3"><label>Avatar (URL)</label><input type="text" class="form-control" id="edit-avatar" name="avatar_url"></div>
            </div>
            <div class="modal-footer">
                <button type="submit" name="update" class="btn btn-warning">Salvar Alterações</button>
            </div>
          </form>
        </div>
      </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

    <script>
        // Toggle filtro
        document.getElementById("filter-toggle").addEventListener("click", () => {
            document.getElementById("filter-box").classList.toggle("d-none");
        });

        // Preenche modal editar
        const editarUsuarioModal = document.getElementById('editarUsuarioModal');
        editarUsuarioModal.addEventListener('show.bs.modal', event => {
            const button = event.relatedTarget;
            document.getElementById('edit-id').value = button.getAttribute('data-id');
            document.getElementById('edit-nome').value = button.getAttribute('data-nome');
            document.getElementById('edit-email').value = button.getAttribute('data-email');
            document.getElementById('edit-tipo').value = button.getAttribute('data-tipo');
            document.getElementById('edit-avatar').value = button.getAttribute('data-avatar');
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