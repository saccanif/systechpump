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

include_once "../config/connection.php";
$conexao = conectarBD();

// Buscar estados (uma vez) para reutilizar no HTML
$sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
$res_estados = mysqli_query($conexao, $sql_estados);
$estados = [];
while ($r = mysqli_fetch_assoc($res_estados)) {
    $estados[] = $r;
}

// ----------------------- AÇÕES (POST) -----------------------
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // CADASTRAR
    if (isset($_POST['cadastrar_cidade'])) {
        $nomeCidade = trim($_POST['nomeCidade'] ?? '');
        $estado_idEstado = intval($_POST['estado_idEstado'] ?? 0);
        $qtdaAcesso = intval($_POST['qtdaAcesso'] ?? 0);

        if ($nomeCidade === '' || $estado_idEstado === 0) {
            // redireciona com mensagem de erro (opcional: poderia usar outra chave)
            header("Location: gerenciadorCidades.php?msg=" . urlencode("Nome da cidade e estado são obrigatórios"));
            exit;
        }

        // Verifica duplicidade
        $sql_check = "SELECT idCidade FROM cidade WHERE nomeCidade = ? AND estado_idEstado = ?";
        $stmtc = mysqli_prepare($conexao, $sql_check);
        mysqli_stmt_bind_param($stmtc, "si", $nomeCidade, $estado_idEstado);
        mysqli_stmt_execute($stmtc);
        $res_check = mysqli_stmt_get_result($stmtc);
        if (mysqli_num_rows($res_check) > 0) {
            header("Location: gerenciadorCidades.php?msg=" . urlencode("Cidade já cadastrada neste estado"));
            exit;
        }

        $sql_ins = "INSERT INTO cidade (nomeCidade, estado_idEstado, qtdaAcesso) VALUES (?, ?, ?)";
        $stmti = mysqli_prepare($conexao, $sql_ins);
        mysqli_stmt_bind_param($stmti, "sii", $nomeCidade, $estado_idEstado, $qtdaAcesso);
        mysqli_stmt_execute($stmti);

        header("Location: gerenciadorCidades.php?msg=" . urlencode("Cidade cadastrada com sucesso!"));
        exit;
    }

    // EDITAR
    if (isset($_POST['editar_cidade'])) {
        $idCidade = intval($_POST['idCidade'] ?? 0);
        $nomeCidade = trim($_POST['nomeCidade'] ?? '');
        $estado_idEstado = intval($_POST['estado_idEstado'] ?? 0);
        $qtdaAcesso = intval($_POST['qtdaAcesso'] ?? 0);

        if ($idCidade > 0 && $nomeCidade !== '' && $estado_idEstado > 0) {
            $sql_up = "UPDATE cidade SET nomeCidade = ?, estado_idEstado = ?, qtdaAcesso = ? WHERE idCidade = ?";
            $stmtu = mysqli_prepare($conexao, $sql_up);
            mysqli_stmt_bind_param($stmtu, "siii", $nomeCidade, $estado_idEstado, $qtdaAcesso, $idCidade);
            mysqli_stmt_execute($stmtu);

            header("Location: gerenciadorCidades.php?msg=" . urlencode("Cidade atualizada com sucesso!"));
            exit;
        } else {
            header("Location: gerenciadorCidades.php?msg=" . urlencode("Preencha todos os campos obrigatórios para editar."));
            exit;
        }
    }

    // EXCLUIR
    if (isset($_POST['excluir_cidade'])) {
        $idCidade = intval($_POST['idCidade'] ?? 0);
        if ($idCidade > 0) {
            $sql_del = "DELETE FROM cidade WHERE idCidade = ?";
            $stmtd = mysqli_prepare($conexao, $sql_del);
            mysqli_stmt_bind_param($stmtd, "i", $idCidade);
            mysqli_stmt_execute($stmtd);

            header("Location: gerenciadorCidades.php?msg=" . urlencode("Cidade excluída com sucesso!"));
            exit;
        }
    }
}

// ----------------------- FILTROS (GET) -----------------------
$filtro_estado = $_GET['filtro_estado'] ?? '';
$filtro_nome = $_GET['filtro_nome'] ?? '';

$where = [];
if ($filtro_estado !== '') {
    $where[] = "c.estado_idEstado = " . intval($filtro_estado);
}
if ($filtro_nome !== '') {
    $safe_nome = mysqli_real_escape_string($conexao, $filtro_nome);
    $where[] = "c.nomeCidade LIKE '%$safe_nome%'";
}

$sql_cidades = "SELECT c.*, e.nomeEstado, e.siglaEstado FROM cidade c INNER JOIN estado e ON c.estado_idEstado = e.idEstado";
if (count($where) > 0) $sql_cidades .= " WHERE " . implode(" AND ", $where);
$sql_cidades .= " ORDER BY c.idCidade DESC LIMIT 50";
$res_cidades = mysqli_query($conexao, $sql_cidades);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
<meta charset="utf-8">
<meta name="viewport" content="width=device-width,initial-scale=1">
<title>Gerenciar Cidades - System Pump</title>

<!-- Bootstrap & FontAwesome -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Estilo custom "bonito" -->
<style>
:root{
    --primary:#667eea;
    --secondary:#764ba2;
    --card-bg:#ffffff;
    --muted:#6c757d;
}
body{
    background: linear-gradient(180deg, #f4f7fb 0%, #ffffff 100%);
    font-family: Inter, system-ui, -apple-system, "Segoe UI", Roboto, "Helvetica Neue", Arial;
    color:#2c3e50;
    padding:20px;
}
.admin-container { max-width:1200px; margin:0 auto; }
.header{ padding:10px 0; }
.btn-voltar{ text-decoration:none; }

/* Cards / Tables */
.cities-container { background:transparent; }
.cities-header h2 { font-weight:700; color:var(--primary); }
.cities-actions .btn { border-radius:10px; box-shadow:0 6px 18px rgba(102,126,234,0.12); }

/* Custom modal (fancy) */
.modal-custom {
    display:none;
    position: fixed;
    top: 0;
    left: 0;
    width:100%;
    height:100%;
    background: rgba(2,6,23,0.45);
    z-index: 1050;
    align-items: center;
    justify-content: center;
    padding: 20px;
}
.modal-content-custom {
    width:100%;
    max-width:720px;
    background: var(--card-bg);
    border-radius: 14px;
    overflow: hidden;
    box-shadow: 0 30px 60px rgba(15,23,42,0.15);
}
.modal-header-custom{
    padding:20px;
    background: linear-gradient(135deg, var(--primary), var(--secondary));
    color: #fff;
    display:flex;
    align-items:center;
    justify-content:space-between;
}
.modal-header-custom h5{ margin:0; font-weight:700; }
.modal-body-custom { padding:24px; }
.modal-footer { display:flex; gap:10px; padding:15px 24px; justify-content:flex-end; }

/* Alerts custom look (but we'll use bootstrap alert visually) */
.alert-floating {
    min-width: 260px;
    max-width: 420px;
}

/* Table styling */
.cities-table thead th { background: transparent; border-bottom:2px solid rgba(0,0,0,0.05); }
.cities-table tbody tr td { vertical-align: middle; }

/* Tiny avatar */
.user-avatar {
    background: linear-gradient(45deg,var(--primary),var(--secondary));
    color:white;
    width:40px; height:40px;
    display:inline-flex; align-items:center; justify-content:center;
    border-radius:8px; font-weight:700;
}
</style>
</head>
<body>
<div class="admin-container">
    <main class="main-content">
        <!-- Header -->
        <header class="header d-flex justify-content-between align-items-center">
            <div class="d-flex align-items-center gap-3">
                <a href="<?php echo $voltar; ?>" class="btn btn-outline-secondary btn-sm">
                    <i class="fas fa-arrow-left"></i> Voltar
                </a>
                <h1 class="mb-0">Gerenciar Cidades</h1>
            </div>
            <div class="user-info d-flex align-items-center gap-2">
                <div class="user-avatar">AD</div>
                <span><?php echo htmlspecialchars($_SESSION['usuario']['nomeUsuario'] ?? 'Admin'); ?></span>
            </div>
        </header>

        <!-- Mensagem de sucesso (igual ao gerenciador de usuários) -->
        <?php if (isset($_GET['msg'])): ?>
            <div class="alert alert-success alert-dismissible fade show position-fixed top-0 end-0 m-3 alert-floating" role="alert" id="alert-box" style="z-index: 1060;">
                <?php echo htmlspecialchars($_GET['msg']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Fechar"></button>
            </div>
            <script>
                setTimeout(() => {
                    const alertBox = document.getElementById("alert-box");
                    if (alertBox) {
                        const bsAlert = new bootstrap.Alert(alertBox);
                        bsAlert.close();
                    }
                }, 3000);
            </script>
        <?php endif; ?>

        <!-- Conteúdo -->
        <div class="page-content">
            <div class="cities-container">
                <div class="cities-header d-flex justify-content-between align-items-center mb-3">
                    <h2>Cidades Atendidas</h2>
                    <div class="cities-actions d-flex gap-2">
                        <button class="btn btn-primary" onclick="toggleFiltros()"><i class="fas fa-filter me-2"></i> Filtros</button>
                        <button class="btn btn-success" onclick="abrirModal()"><i class="fas fa-plus me-2"></i> Nova Cidade</button>
                    </div>
                </div>

                <!-- Filtros (toggle) — versão sem "Limpar" -->
                <div id="filtros" style="display:none; margin-bottom:18px;">
                    <form method="GET" class="row g-2">
                        <div class="col-md-6">
                            <input type="text" name="filtro_nome" class="form-control" placeholder="Nome da cidade" value="<?php echo htmlspecialchars($filtro_nome); ?>">
                        </div>
                        <div class="col-md-4">
                            <select name="filtro_estado" class="form-control">
                                <option value="">Todos os estados</option>
                                <?php foreach ($estados as $est): 
                                    $sel = ($filtro_estado == $est['idEstado']) ? 'selected' : '';
                                ?>
                                    <option value="<?php echo $est['idEstado']; ?>" <?php echo $sel; ?>>
                                        <?php echo $est['nomeEstado'] . ' - ' . $est['siglaEstado']; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-2 d-grid">
                            <button type="submit" class="btn btn-primary"><i class="fas fa-search me-1"></i> Buscar</button>
                        </div>
                    </form>
                </div>

                <!-- Tabela -->
                <div class="cities-table-container">
                    <table class="table table-striped cities-table">
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
                            <?php if ($res_cidades && mysqli_num_rows($res_cidades) > 0): ?>
                                <?php while ($cidade = mysqli_fetch_assoc($res_cidades)): ?>
                                    <tr>
                                        <td><?php echo $cidade['idCidade']; ?></td>
                                        <td><?php echo htmlspecialchars($cidade['nomeCidade']); ?></td>
                                        <td><?php echo htmlspecialchars($cidade['siglaEstado']); ?></td>
                                        <td><?php echo number_format($cidade['qtdaAcesso'], 0, ',', '.'); ?></td>
                                        <td>
                                            <button class="btn btn-sm btn-outline-primary me-1"
                                                onclick="abrirModalEditar(<?php echo intval($cidade['idCidade']); ?>, <?php echo json_encode($cidade['nomeCidade']); ?>, <?php echo intval($cidade['estado_idEstado']); ?>, <?php echo intval($cidade['qtdaAcesso']); ?>)">
                                                <i class="fas fa-edit"></i>
                                            </button>

                                            <form method="POST" style="display:inline;">
                                                <input type="hidden" name="idCidade" value="<?php echo $cidade['idCidade']; ?>">
                                                <button type="submit" name="excluir_cidade" class="btn btn-sm btn-outline-danger" onclick="return confirm('Tem certeza que deseja excluir esta cidade?');">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr><td colspan="5" class="text-center text-muted">Nenhuma cidade cadastrada</td></tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>

            </div>
        </div>

        <footer class="footer text-center mt-4">
            <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
        </footer>
    </main>
</div>

<!-- Modal Adicionar (custom) -->
<div class="modal-custom" id="addCityModal">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h5 class="modal-title"><i class="fas fa-plus-circle me-2"></i>Nova Cidade</h5>
            <button type="button" class="btn-close btn-close-white" onclick="fecharModal()" aria-label="Fechar"></button>
        </div>
        <div class="modal-body-custom">
            <form method="POST" action="">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome da Cidade *</label>
                        <input type="text" class="form-control" name="nomeCidade" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado *</label>
                        <select name="estado_idEstado" class="form-control" required>
                            <option value="">Selecione o estado</option>
                            <?php foreach ($estados as $est): ?>
                                <option value="<?php echo $est['idEstado']; ?>"><?php echo $est['nomeEstado'] . ' - ' . $est['siglaEstado']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Qtd Acesso</label>
                    <input type="number" class="form-control" name="qtdaAcesso" value="0" min="0">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModal()">Cancelar</button>
                    <button type="submit" name="cadastrar_cidade" class="btn btn-primary">Salvar Cidade</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Editar (custom) -->
<div class="modal-custom" id="editCityModal">
    <div class="modal-content-custom">
        <div class="modal-header-custom">
            <h5 class="modal-title"><i class="fas fa-edit me-2"></i>Editar Cidade</h5>
            <button type="button" class="btn-close btn-close-white" onclick="fecharModalEditar()" aria-label="Fechar"></button>
        </div>
        <div class="modal-body-custom">
            <form method="POST" action="">
                <input type="hidden" name="idCidade" id="edit_idCidade">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label class="form-label">Nome da Cidade *</label>
                        <input type="text" class="form-control" id="edit_nomeCidade" name="nomeCidade" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado *</label>
                        <select class="form-control" id="edit_estado_idEstado" name="estado_idEstado" required>
                            <option value="">Selecione o estado</option>
                            <?php foreach ($estados as $est): ?>
                                <option value="<?php echo $est['idEstado']; ?>"><?php echo $est['nomeEstado'] . ' - ' . $est['siglaEstado']; ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <div class="mt-3">
                    <label class="form-label">Qtd Acesso</label>
                    <input type="number" class="form-control" id="edit_qtdaAcesso" name="qtdaAcesso" min="0">
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="fecharModalEditar()">Cancelar</button>
                    <button type="submit" name="editar_cidade" class="btn btn-warning">Salvar Alterações</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Bootstrap JS (necessário para alert auto-close) -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>

<!-- JS do comportamento dos modais e filtros -->
<script>
function abrirModal() {
    document.getElementById('addCityModal').style.display = 'flex';
}
function fecharModal() {
    document.getElementById('addCityModal').style.display = 'none';
}
function abrirModalEditar(id, nome, estado, qtda) {
    document.getElementById('edit_idCidade').value = id;
    document.getElementById('edit_nomeCidade').value = nome;
    document.getElementById('edit_estado_idEstado').value = estado;
    document.getElementById('edit_qtdaAcesso').value = qtda;
    document.getElementById('editCityModal').style.display = 'flex';
}
function fecharModalEditar() {
    document.getElementById('editCityModal').style.display = 'none';
}
function toggleFiltros() {
    const f = document.getElementById('filtros');
    f.style.display = (f.style.display === 'none' || f.style.display === '') ? 'block' : 'none';
}
window.onclick = function(event) {
    const add = document.getElementById('addCityModal');
    const edit = document.getElementById('editCityModal');
    if (event.target === add) fecharModal();
    if (event.target === edit) fecharModalEditar();
}
</script>
</body>
</html>

<?php
if (isset($conexao)) mysqli_close($conexao);
?>
