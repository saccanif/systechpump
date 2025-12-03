<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$idUsuario = $usuario['idUsuario'];

// Apenas admin e representante podem acessar
if ($tipoUsuario !== 'admin' && $tipoUsuario !== 'representante') {
    header("Location: login.php?erro=Acesso não autorizado");
    exit();
}

require_once "../config/connection.php";
$conexao = conectarBD();

// Verificar se a tabela existe
$sql_check = "SHOW TABLES LIKE 'solicitacoes'";
$result_check = mysqli_query($conexao, $sql_check);
if (mysqli_num_rows($result_check) == 0) {
    // Criar tabela se não existir
    $sql_create = "CREATE TABLE IF NOT EXISTS solicitacoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        tipo ENUM('lojista', 'representante') NOT NULL,
        nome VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        cnpj VARCHAR(20) NOT NULL,
        mensagem TEXT NOT NULL,
        cidade_idCidade INT NOT NULL,
        estado_idEstado INT NOT NULL,
        destinatario_id INT NULL,
        status ENUM('pendente', 'aceita', 'negada') DEFAULT 'pendente',
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade) ON DELETE CASCADE,
        FOREIGN KEY (estado_idEstado) REFERENCES estado(idEstado) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    mysqli_query($conexao, $sql_create);
}

// Processar mudança de status
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['atualizar_status'])) {
    $solicitacao_id = intval($_POST['solicitacao_id']);
    $novo_status = $_POST['novo_status'];
    
    // Validar status
    if (!in_array($novo_status, ['aceita', 'negada'])) {
        $_SESSION['erro'] = "Status inválido";
    } else {
        // Verificar se a solicitação pertence ao usuário
        if ($tipoUsuario === 'representante') {
            // Buscar dados da solicitação antes de atualizar
            $sql_busca = "SELECT * FROM solicitacoes WHERE id = ? AND destinatario_id = ?";
            $stmt_busca = mysqli_prepare($conexao, $sql_busca);
            mysqli_stmt_bind_param($stmt_busca, "ii", $solicitacao_id, $idUsuario);
            mysqli_stmt_execute($stmt_busca);
            $result_busca = mysqli_stmt_get_result($stmt_busca);
            $solicitacao = mysqli_fetch_assoc($result_busca);
            
            if (!$solicitacao) {
                $_SESSION['erro'] = "Solicitação não encontrada ou você não tem permissão";
            } else {
                $sql_update = "UPDATE solicitacoes SET status = ? WHERE id = ?";
                $stmt_update = mysqli_prepare($conexao, $sql_update);
                mysqli_stmt_bind_param($stmt_update, "si", $novo_status, $solicitacao_id);
                mysqli_stmt_execute($stmt_update);
                
                // Se foi aceita e é representante, redirecionar para criar loja com dados pré-preenchidos
                if ($novo_status === 'aceita' && $tipoUsuario === 'representante') {
                    $_SESSION['solicitacao_aceita'] = $solicitacao;
                    header("Location: gerenciadorLojas.php?criar_loja=1&solicitacao_id=" . $solicitacao_id);
                    exit();
                }
                
                $_SESSION['sucesso'] = "Status atualizado com sucesso!";
            }
        } else {
            // Admin pode atualizar qualquer solicitação
            $sql_update = "UPDATE solicitacoes SET status = ? WHERE id = ?";
            $stmt_update = mysqli_prepare($conexao, $sql_update);
            mysqli_stmt_bind_param($stmt_update, "si", $novo_status, $solicitacao_id);
            mysqli_stmt_execute($stmt_update);
            $_SESSION['sucesso'] = "Status atualizado com sucesso!";
        }
    }
    
    header("Location: gerenciadorSolicitacoes.php");
    exit();
}

// Limpar histórico antigo
$sql_cleanup = "DELETE FROM solicitacoes WHERE status IN ('aceita', 'negada') AND data_atualizacao < DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($conexao, $sql_cleanup);

// Buscar solicitações
if ($tipoUsuario === 'representante') {
    // Representante vê apenas solicitações destinadas a ele
    $sql_solicitacoes = "SELECT s.*, c.nomeCidade, e.siglaEstado, e.nomeEstado
                        FROM solicitacoes s
                        INNER JOIN cidade c ON s.cidade_idCidade = c.idCidade
                        INNER JOIN estado e ON s.estado_idEstado = e.idEstado
                        WHERE s.destinatario_id = ?
                        ORDER BY s.data_solicitacao DESC";
    $stmt = mysqli_prepare($conexao, $sql_solicitacoes);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result_solicitacoes = mysqli_stmt_get_result($stmt);
} else {
    // Admin vê solicitações destinadas a ele (destinatario_id IS NULL)
    $sql_solicitacoes = "SELECT s.*, c.nomeCidade, e.siglaEstado, e.nomeEstado
                        FROM solicitacoes s
                        INNER JOIN cidade c ON s.cidade_idCidade = c.idCidade
                        INNER JOIN estado e ON s.estado_idEstado = e.idEstado
                        WHERE s.destinatario_id IS NULL
                        ORDER BY s.data_solicitacao DESC";
    $result_solicitacoes = mysqli_query($conexao, $sql_solicitacoes);
}

// Buscar histórico (últimas 30 dias)
$sql_historico = "SELECT s.*, c.nomeCidade, e.siglaEstado
                  FROM solicitacoes s
                  INNER JOIN cidade c ON s.cidade_idCidade = c.idCidade
                  INNER JOIN estado e ON s.estado_idEstado = e.idEstado
                  WHERE s.status IN ('aceita', 'negada') 
                  AND s.data_atualizacao >= DATE_SUB(NOW(), INTERVAL 30 DAY)";
if ($tipoUsuario === 'representante') {
    $sql_historico .= " AND s.destinatario_id = ?";
    $stmt_hist = mysqli_prepare($conexao, $sql_historico);
    mysqli_stmt_bind_param($stmt_hist, "i", $idUsuario);
    mysqli_stmt_execute($stmt_hist);
    $result_historico = mysqli_stmt_get_result($stmt_hist);
} else {
    $sql_historico .= " AND s.destinatario_id IS NULL";
    $result_historico = mysqli_query($conexao, $sql_historico);
}

$voltar = $tipoUsuario === 'admin' ? 'adm.php' : 'representante.php';

$sucesso = $_SESSION['sucesso'] ?? '';
$erro = $_SESSION['erro'] ?? '';
unset($_SESSION['sucesso'], $_SESSION['erro']);
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Solicitações - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <style>
        .solicitacao-card {
            background: white;
            border-radius: 10px;
            padding: 1.5rem;
            margin-bottom: 1.5rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            border-left: 4px solid #0e47a1;
        }
        .solicitacao-card.pendente {
            border-left-color: #ffc107;
        }
        .solicitacao-card.aceita {
            border-left-color: #28a745;
        }
        .solicitacao-card.negada {
            border-left-color: #dc3545;
        }
        .solicitacao-header {
            display: flex;
            justify-content: space-between;
            align-items: start;
            margin-bottom: 1rem;
            flex-wrap: wrap;
            gap: 1rem;
        }
        .solicitacao-info h4 {
            color: #0e47a1;
            margin: 0 0 0.5rem 0;
        }
        .solicitacao-meta {
            display: flex;
            flex-wrap: wrap;
            gap: 1rem;
            color: #666;
            font-size: 0.9rem;
        }
        .solicitacao-actions {
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
        .btn-aceitar:hover {
            background: #218838;
        }
        .btn-negar {
            background: #dc3545;
            color: white;
        }
        .btn-negar:hover {
            background: #c82333;
        }
        .historico-item {
            padding: 0.75rem;
            margin-bottom: 0.5rem;
            border-radius: 5px;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        .historico-item.aceita {
            background: #d4edda;
            color: #155724;
        }
        .historico-item.negada {
            background: #f8d7da;
            color: #721c24;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <main class="main-content">
            <header class="header d-flex justify-content-between align-items-center">
                <div class="d-flex align-items-center gap-3">
                    <a href="<?php echo $voltar; ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 class="mb-0"><i class="fas fa-inbox"></i> Solicitações</h1>
                </div>
            </header>

            <div class="page-content">
                <?php if ($sucesso): ?>
                    <div class="alert alert-success"><?php echo $sucesso; ?></div>
                <?php endif; ?>
                <?php if ($erro): ?>
                    <div class="alert alert-danger"><?php echo $erro; ?></div>
                <?php endif; ?>

                <div class="solicitacoes-container">
                    <h2>Solicitações Pendentes</h2>
                    
                    <?php if ($result_solicitacoes && mysqli_num_rows($result_solicitacoes) > 0): ?>
                        <?php while ($solicitacao = mysqli_fetch_assoc($result_solicitacoes)): ?>
                            <?php if ($solicitacao['status'] === 'pendente'): ?>
                            <div class="solicitacao-card pendente">
                                <div class="solicitacao-header">
                                    <div class="solicitacao-info">
                                        <h4>
                                            <i class="fas fa-<?php echo $solicitacao['tipo'] === 'lojista' ? 'store' : 'user-tie'; ?>"></i>
                                            <?php echo htmlspecialchars($solicitacao['nome']); ?>
                                        </h4>
                                        <div class="solicitacao-meta">
                                            <span><strong>Tipo:</strong> <?php echo ucfirst($solicitacao['tipo']); ?></span>
                                            <span><strong>CNPJ:</strong> <?php echo htmlspecialchars($solicitacao['cnpj']); ?></span>
                                            <span><strong>Telefone:</strong> <?php echo htmlspecialchars($solicitacao['telefone']); ?></span>
                                            <span><strong>E-mail:</strong> <?php echo htmlspecialchars($solicitacao['email']); ?></span>
                                            <span><strong>Localização:</strong> <?php echo htmlspecialchars($solicitacao['nomeCidade']); ?> - <?php echo $solicitacao['siglaEstado']; ?></span>
                                            <span><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($solicitacao['data_solicitacao'])); ?></span>
                                        </div>
                                        <p style="margin-top: 1rem; color: #333;"><strong>Mensagem:</strong> <?php echo nl2br(htmlspecialchars($solicitacao['mensagem'])); ?></p>
                                    </div>
                                    <div class="solicitacao-actions">
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
                                </div>
                            </div>
                            <?php endif; ?>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhuma solicitação pendente no momento.
                        </div>
                    <?php endif; ?>

                    <h2 style="margin-top: 3rem;">Histórico (Últimos 30 dias)</h2>
                    <?php if ($result_historico && mysqli_num_rows($result_historico) > 0): ?>
                        <?php while ($item = mysqli_fetch_assoc($result_historico)): ?>
                            <div class="solicitacao-card <?php echo $item['status']; ?>" style="margin-bottom: 1.5rem;">
                                <div class="solicitacao-header">
                                    <div class="solicitacao-info">
                                        <h4>
                                            <i class="fas fa-<?php echo $item['tipo'] === 'lojista' ? 'store' : 'user-tie'; ?>"></i>
                                            <?php echo htmlspecialchars($item['nome']); ?>
                                            <span style="margin-left: 1rem; font-size: 0.9rem; font-weight: normal;">
                                                <?php if ($item['status'] === 'aceita'): ?>
                                                    <i class="fas fa-check-circle" style="color: #28a745;"></i> Aceita
                                                <?php else: ?>
                                                    <i class="fas fa-times-circle" style="color: #dc3545;"></i> Negada
                                                <?php endif; ?>
                                            </span>
                                        </h4>
                                        <div class="solicitacao-meta">
                                            <span><strong>Tipo:</strong> <?php echo ucfirst($item['tipo']); ?></span>
                                            <span><strong>CNPJ:</strong> <?php echo htmlspecialchars($item['cnpj']); ?></span>
                                            <span><strong>Telefone:</strong> <?php echo htmlspecialchars($item['telefone']); ?></span>
                                            <span><strong>E-mail:</strong> <?php echo htmlspecialchars($item['email']); ?></span>
                                            <span><strong>Localização:</strong> <?php echo htmlspecialchars($item['nomeCidade']); ?> - <?php echo $item['siglaEstado']; ?></span>
                                            <span><strong>Data:</strong> <?php echo date('d/m/Y H:i', strtotime($item['data_atualizacao'])); ?></span>
                                        </div>
                                        <p style="margin-top: 1rem; color: #333;"><strong>Mensagem:</strong> <?php echo nl2br(htmlspecialchars($item['mensagem'])); ?></p>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i> Nenhum histórico disponível.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </main>
    </div>
</body>
</html>



