<?php
require_once '../config/connection.php';
header('Content-Type: application/json');

$conexao = conectarBD();

if (!isset($_GET['estado_id']) || empty($_GET['estado_id'])) {
    echo json_encode(['error' => 'Estado não informado']);
    exit;
}

$estado_id = intval($_GET['estado_id']);

// Verificar se a coluna whatsapp existe
$sql_check_whatsapp = "SHOW COLUMNS FROM usuario LIKE 'whatsapp'";
$result_check_whatsapp = mysqli_query($conexao, $sql_check_whatsapp);
if (mysqli_num_rows($result_check_whatsapp) == 0) {
    $sql_add_whatsapp = "ALTER TABLE usuario ADD COLUMN whatsapp VARCHAR(20) NULL AFTER avatar_url";
    mysqli_query($conexao, $sql_add_whatsapp);
}

// Verificar se a coluna estados_fale_conosco existe
$sql_check_col = "SHOW COLUMNS FROM usuario LIKE 'estados_fale_conosco'";
$result_check_col = mysqli_query($conexao, $sql_check_col);
if (mysqli_num_rows($result_check_col) == 0) {
    $sql_add_col = "ALTER TABLE usuario ADD COLUMN estados_fale_conosco TEXT NULL AFTER whatsapp";
    mysqli_query($conexao, $sql_add_col);
}

// Buscar nome do estado para filtrar
$sql_estado = "SELECT nomeEstado FROM estado WHERE idEstado = ?";
$stmt_estado = mysqli_prepare($conexao, $sql_estado);
mysqli_stmt_bind_param($stmt_estado, "i", $estado_id);
mysqli_stmt_execute($stmt_estado);
$result_estado = mysqli_stmt_get_result($stmt_estado);
$estado_info = mysqli_fetch_assoc($result_estado);
$nome_estado = $estado_info['nomeEstado'] ?? '';

// Buscar representantes - filtrar por texto que contenha o nome do estado
$sql_representantes = "
    SELECT u.idUsuario, u.nomeUsuario, u.emailUsuario, 
           COALESCE(u.whatsapp, '') as whatsapp,
           COALESCE(u.estados_fale_conosco, '') as estados
    FROM usuario u
    WHERE u.tipoUsuario = 'representante' 
    AND (u.estados_fale_conosco LIKE ? OR u.estados_fale_conosco = '' OR u.estados_fale_conosco IS NULL)
    ORDER BY u.nomeUsuario
";

$stmt = mysqli_prepare($conexao, $sql_representantes);
$estado_like = "%{$nome_estado}%";
mysqli_stmt_bind_param($stmt, "s", $estado_like);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

$representantes = [];
while ($rep = mysqli_fetch_assoc($result)) {
    $estados_display = !empty($rep['estados']) ? $rep['estados'] : 'Não informado';
    $representantes[] = [
        'idUsuario' => $rep['idUsuario'],
        'nomeUsuario' => $rep['nomeUsuario'],
        'emailUsuario' => $rep['emailUsuario'],
        'whatsapp' => $rep['whatsapp'],
        'estados' => $estados_display
    ];
}

mysqli_stmt_close($stmt);
mysqli_close($conexao);

echo json_encode(['representantes' => $representantes]);
?>

