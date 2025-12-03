<?php
header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

require_once '../config/connection.php';

if (!isset($_GET['cidade_id']) || empty($_GET['cidade_id'])) {
    echo json_encode(['success' => false, 'error' => 'Cidade não especificada']);
    exit;
}

$cidade_id = intval($_GET['cidade_id']);
$conexao = conectarBD();

// Verificar se a tabela de acessos existe, se não, criar
$sql_check = "SHOW TABLES LIKE 'acessos_cidade'";
$result_check = mysqli_query($conexao, $sql_check);

if (mysqli_num_rows($result_check) == 0) {
    // Criar tabela de acessos
    $sql_create = "CREATE TABLE IF NOT EXISTS acessos_cidade (
        id INT AUTO_INCREMENT PRIMARY KEY,
        cidade_idCidade INT NOT NULL,
        data_acesso TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        FOREIGN KEY (cidade_idCidade) REFERENCES cidade(idCidade) ON DELETE CASCADE,
        INDEX idx_cidade_data (cidade_idCidade, data_acesso)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4";
    
    mysqli_query($conexao, $sql_create);
}

// Registrar acesso
$sql_insert = "INSERT INTO acessos_cidade (cidade_idCidade) VALUES (?)";
$stmt = mysqli_prepare($conexao, $sql_insert);
mysqli_stmt_bind_param($stmt, "i", $cidade_id);
mysqli_stmt_execute($stmt);

// Limpar acessos com mais de 30 dias
$sql_cleanup = "DELETE FROM acessos_cidade WHERE data_acesso < DATE_SUB(NOW(), INTERVAL 30 DAY)";
mysqli_query($conexao, $sql_cleanup);

echo json_encode(['success' => true]);
mysqli_close($conexao);
?>




