<?php
session_start();

if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] !== 'admin') {
    header("Location: login.php?erro=Acesso não autorizado");
    exit();
}

require_once "../config/connection.php";
$conexao = conectarBD();

// Verificar se a tabela existe
$sql_check = "SHOW TABLES LIKE 'solicitacoes'";
$result_check = mysqli_query($conexao, $sql_check);
if (mysqli_num_rows($result_check) == 0) {
    echo "<!DOCTYPE html><html><head><title>Erro</title></head><body><h1>Tabela de solicitações não encontrada. Crie uma solicitação primeiro.</h1></body></html>";
    exit;
}

// Buscar todas as solicitações com informações do destinatário
$sql_solicitacoes = "SELECT s.*, 
                            c.nomeCidade, 
                            e.siglaEstado, 
                            e.nomeEstado,
                            CASE 
                                WHEN s.destinatario_id IS NULL THEN 'Admin Master'
                                ELSE u.nomeUsuario
                            END as destinatario_nome
                     FROM solicitacoes s
                     INNER JOIN cidade c ON s.cidade_idCidade = c.idCidade
                     INNER JOIN estado e ON s.estado_idEstado = e.idEstado
                     LEFT JOIN usuario u ON s.destinatario_id = u.idUsuario
                     ORDER BY s.data_solicitacao DESC";

$result_solicitacoes = mysqli_query($conexao, $sql_solicitacoes);

// Processar mudança de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $solicitacao_id = intval($_POST['solicitacao_id']);
    $novo_status = $_POST['novo_status'];
    
    if (in_array($novo_status, ['aceita', 'negada'])) {
        $sql_update = "UPDATE solicitacoes SET status = ? WHERE id = ?";
        $stmt_update = mysqli_prepare($conexao, $sql_update);
        mysqli_stmt_bind_param($stmt_update, "si", $novo_status, $solicitacao_id);
        mysqli_stmt_execute($stmt_update);
        $_SESSION['sucesso'] = "Status atualizado com sucesso!";
        header("Location: gerenciadorObservacoes.php");
        exit();
    }
}

$sucesso = $_SESSION['sucesso'] ?? '';
$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['sucesso'], $_SESSION['erro']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Observação - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <style>
        .observacao-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #0e47a1;
        }
        .observacao-card.pendente { border-left-color: #ffc107; }
        .observacao-card.aceita { border-left-color: #28a745; }
        .observacao-card.negada { border-left-color: #dc3545; }
        .observacao-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .observacao-info h4 {
            color: #0e47a1;
            margin: 0 0 0.5rem 0;
        }
        .observacao-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .destinatario-badge {
            background: #0e47a1;
            color: white;
            padding: 0.25rem 0.75rem;
            border-radius: 15px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .observacao-actions {
            display: flex;
            gap: 0.5rem;
        }
        .btn-status {
            padding: 0.5rem 1rem;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }
        .btn-aceitar {
            background: #28a745;
            color: white;
        }
        .btn-aceitar:hover { background: #218838; }
        .btn-negar {
            background: #dc3545;
            color: white;
        }
        .btn-negar:hover { background: #c82333; }
    </style>
</head>
<body>
    <div class="admin-container">
        <main class="main-content">
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="./adm.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0"><i class="fas fa-eye"></i> Observação</h1>
                </div>
            </header>

            <div class="page-content">
                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?php echo $sucesso; ?></div>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <div class="observacoes-container">
                    <h2>Todas as Solicitações</h2>
                    <p style="color: #666; margin-bottom: 2rem;">Visualize todas as solicitações e para quem foram destinadas</p>
                    
                    <?php if ($result_solicitacoes && mysqli_num_rows($result_solicitacoes) > 0): ?>
                        <?php while ($solicitacao = mysqli_fetch_assoc($result_solicitacoes)): ?>
                            <div class="observacao-card <?php echo $solicitacao['status']; ?>">
                                <div class="observacao-header">
                                    <div class="observacao-info" style="flex: 1;">
                                        <h4>
                                            <i class="fas fa-<?php echo $solicitacao['tipo'] === 'lojista' ? 'store' : 'user-tie'; ?>"></i>
                                            <?php echo htmlspecialchars($solicitacao['nome']); ?>
                                            <span class="destinatario-badge" style="margin-left: 1rem;">
                                                <i class="fas fa-user"></i> Para: <?php echo htmlspecialchars($solicitacao['destinatario_nome']); ?>
                                            </span>
                                        </h4>
                                        <div class="observacao-meta">
                                            <span><strong>Tipo:</strong> <?php echo ucfirst($solicitacao['tipo']); ?></span>
                                            <span><strong>CNPJ:</strong> <?php echo htmlspecialchars($solicitacao['cnpj']); ?></span>
                                            <span><strong>Telefone:</strong> <?php echo htmlspecialchars($solicitacao['telefone']); ?></span>
                                            <span><strong>E-mail:</strong> <?php echo htmlspecialchars($solicitacao['email']); ?></span>
                                            <span><strong>Localização:</strong> <?php echo htmlspecialchars($solicitacao['nomeCidade']); ?> - <?php echo $solicitacao['siglaEstado']; ?></span>
                                            <span><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])); ?></span>
                                            <span><strong>Status:</strong> 
                                                <?php if ($solicitacao['status'] === 'pendente'): ?>
                                                    <span style="color: #ffc107;"><i class="fas fa-clock"></i> Pendente</span>
                                                <?php elseif ($solicitacao['status'] === 'aceita'): ?>
                                                    <span style="color: #28a745;"><i class="fas fa-check-circle"></i> Aceita</span>
                                                <?php else: ?>
                                                    <span style="color: #dc3545;"><i class="fas fa-times-circle"></i> Negada</span>
                                                <?php endif; ?>
                                            </span>
                                        </div>
                                        <p style="margin-top: 1rem; color: #333;"><strong>Mensagem:</strong> <?php echo nl2br(htmlspecialchars($solicitacao['mensagem'])); ?></p>
                                    </div>
                                    <?php if ($solicitacao['status'] === 'pendente'): ?>
                                    <div class="observacao-actions">
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="solicitacao_id" value="<?php echo $solicitacao['id']; ?>">
                                            <input type="hidden" name="novo_status" value="aceita">
                                            <button type="submit" name="atualizar_status" class="btn-status btn-aceitar">
                                                <i class="fas fa-check"></i> Aceitar
                                            </button>
                                        </form>
                                        <form method="POST" style="display: inline;">
                                            <input type="hidden" name="solicitacao_id" value="<?php echo $solicitacao['id']; ?>">
                                            <input type="hidden" name="novo_status" value="negada">
                                            <button type="submit" name="atualizar_status" class="btn-status btn-negar">
                                                <i class="fas fa-times"></i> Negar
                                            </button>
                                        </form>
                                    </div>
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhuma solicitação encontrada.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>




