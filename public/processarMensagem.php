<?php
require_once '../config/connection.php';
$conexao = conectarBD();

// Verificar se a tabela mensagens existe, se não, criar
$sql_check = "SHOW TABLES LIKE 'mensagens'";
$result_check = mysqli_query($conexao, $sql_check);

if (mysqli_num_rows($result_check) == 0) {
    $sql_create = "CREATE TABLE IF NOT EXISTS mensagens (
        id INT AUTO_INCREMENT PRIMARY KEY,
        nome VARCHAR(255) NOT NULL,
        telefone VARCHAR(20) NOT NULL,
        email VARCHAR(255) NOT NULL,
        cidade_uf VARCHAR(100),
        motivo VARCHAR(100) NOT NULL,
        mensagem TEXT NOT NULL,
        data_envio TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        lida TINYINT(1) DEFAULT 0,
        INDEX idx_lida (lida),
        INDEX idx_data (data_envio)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    mysqli_query($conexao, $sql_create);
}

// Processar formulário
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nome = mysqli_real_escape_string($conexao, $_POST['nome'] ?? '');
    $telefone = mysqli_real_escape_string($conexao, $_POST['telefone'] ?? '');
    $email = mysqli_real_escape_string($conexao, $_POST['email'] ?? '');
    $cidade_uf = mysqli_real_escape_string($conexao, $_POST['cidade_uf'] ?? '');
    $motivo = mysqli_real_escape_string($conexao, $_POST['motivo'] ?? '');
    $mensagem = mysqli_real_escape_string($conexao, $_POST['mensagem'] ?? '');

    // Validações
    if (empty($nome) || empty($telefone) || empty($email) || empty($motivo) || empty($mensagem)) {
        header("Location: ./faleConosco.php?erro=Por favor, preencha todos os campos obrigatórios");
        exit;
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ./faleConosco.php?erro=E-mail inválido");
        exit;
    }

    // Inserir mensagem
    $sql_insert = "INSERT INTO mensagens (nome, telefone, email, cidade_uf, motivo, mensagem) 
                   VALUES (?, ?, ?, ?, ?, ?)";
    
    $stmt = mysqli_prepare($conexao, $sql_insert);
    mysqli_stmt_bind_param($stmt, "ssssss", $nome, $telefone, $email, $cidade_uf, $motivo, $mensagem);
    
    if (mysqli_stmt_execute($stmt)) {
        header("Location: ./faleConosco.php?sucesso=Mensagem enviada com sucesso! Entraremos em contato em breve.");
    } else {
        header("Location: ./faleConosco.php?erro=Erro ao enviar mensagem. Tente novamente.");
    }
    
    mysqli_stmt_close($stmt);
} else {
    header("Location: ./faleConosco.php");
}

mysqli_close($conexao);
exit;
?>

