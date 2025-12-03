<?php
header('Content-Type: application/json');

require_once '../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'Método não permitido']);
    exit;
}

$nome = $_POST['nome'] ?? '';
$email = $_POST['email'] ?? '';
$telefone = $_POST['telefone'] ?? '';
$cnpj = $_POST['cnpj'] ?? '';
$mensagem = $_POST['mensagem'] ?? '';
$tipoSolicitacao = $_POST['tipoSolicitacao'] ?? '';
$estado_id = $_POST['estado'] ?? '';
$cidade_id = $_POST['cidade'] ?? '';

// Validações
if (empty($nome) || empty($email) || empty($telefone) || empty($cnpj) || empty($mensagem) || empty($tipoSolicitacao) || empty($estado_id) || empty($cidade_id)) {
    echo json_encode(['success' => false, 'message' => 'Todos os campos são obrigatórios']);
    exit;
}

$conexao = conectarBD();

// Verificar se a tabela existe, se não, criar
$sql_check = "SHOW TABLES LIKE 'solicitacoes'";
$result_check = mysqli_query($conexao, $sql_check);

if (mysqli_num_rows($result_check) == 0) {
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
        destinatario_id INT NULL COMMENT 'ID do representante se for lojista, NULL se for admin',
        status ENUM('pendente', 'aceita', 'negada') DEFAULT 'pendente',
        data_solicitacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade) ON DELETE CASCADE,
        FOREIGN KEY (estado_idEstado) REFERENCES estado(idEstado) ON DELETE CASCADE,
        INDEX idx_destinatario (destinatario_id),
        INDEX idx_status (status),
        INDEX idx_data (data_solicitacao)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    mysqli_query($conexao, $sql_create);
}

// Determinar destinatário
$destinatario_id = null;

if ($tipoSolicitacao === 'representante') {
    // Representante sempre vai para admin
    $destinatario_id = null; // NULL = admin
} else if ($tipoSolicitacao === 'lojista') {
    // Lojista: verificar se há representante na cidade
    $sql_rep = "SELECT representante_id FROM cidade WHERE idCidade = ? AND representante_id IS NOT NULL";
    $stmt_rep = mysqli_prepare($conexao, $sql_rep);
    mysqli_stmt_bind_param($stmt_rep, "i", $cidade_id);
    mysqli_stmt_execute($stmt_rep);
    $result_rep = mysqli_stmt_get_result($stmt_rep);
    $cidade_rep = mysqli_fetch_assoc($result_rep);
    
    if ($cidade_rep && !empty($cidade_rep['representante_id'])) {
        $destinatario_id = $cidade_rep['representante_id'];
    } else {
        $destinatario_id = null; // NULL = admin
    }
}

// Inserir solicitação
$sql_insert = "INSERT INTO solicitacoes (tipo, nome, email, telefone, cnpj, mensagem, cidade_idCidade, estado_idEstado, destinatario_id) 
               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
$stmt = mysqli_prepare($conexao, $sql_insert);
mysqli_stmt_bind_param($stmt, "ssssssiii", $tipoSolicitacao, $nome, $email, $telefone, $cnpj, $mensagem, $cidade_id, $estado_id, $destinatario_id);

if (mysqli_stmt_execute($stmt)) {
    // Limpar solicitações antigas (mais de 30 dias e com status aceita ou negada)
    $sql_cleanup = "DELETE FROM solicitacoes WHERE status IN ('aceita', 'negada') AND data_atualizacao < DATE_SUB(NOW(), INTERVAL 30 DAY)";
    mysqli_query($conexao, $sql_cleanup);
    
    echo json_encode(['success' => true, 'message' => 'Solicitação enviada com sucesso!']);
} else {
    echo json_encode(['success' => false, 'message' => 'Erro ao enviar solicitação: ' . mysqli_error($conexao)]);
}

mysqli_close($conexao);
?>




