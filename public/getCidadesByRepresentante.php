<?php
session_start();
include_once "../config/connection.php";

// Permitir CORS para requisições AJAX
header('Access-Control-Allow-Origin: *');
header('Content-Type: application/json');

// Verificar se há sessão
if (!isset($_SESSION['usuario'])) {
    echo json_encode(['error' => 'Usuário não autenticado']);
    exit;
}

$usuario = $_SESSION['usuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$idUsuario = $usuario['idUsuario'];

$conexao = conectarBD();

// Se for representante, buscar apenas suas cidades
// Se for admin, buscar todas as cidades (ou filtrar por representante se especificado)
if ($tipoUsuario === 'representante') {
    $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, e.nomeEstado, e.idEstado
            FROM cidade c 
            INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
            WHERE c.representante_id = ?
            ORDER BY e.nomeEstado, c.nomeCidade";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
} else if ($tipoUsuario === 'admin') {
    // Admin pode ver todas as cidades, mas pode filtrar por representante se especificado
    if (isset($_GET['representante_id']) && !empty($_GET['representante_id'])) {
        $representante_id = intval($_GET['representante_id']);
        $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, e.nomeEstado, e.idEstado
                FROM cidade c 
                INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                WHERE c.representante_id = ?
                ORDER BY e.nomeEstado, c.nomeCidade";
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "i", $representante_id);
    } else {
        // Todas as cidades
        $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado, e.nomeEstado, e.idEstado
                FROM cidade c 
                INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                ORDER BY e.nomeEstado, c.nomeCidade";
        $stmt = mysqli_prepare($conexao, $sql);
    }
} else {
    echo json_encode(['error' => 'Acesso não autorizado']);
    mysqli_close($conexao);
    exit;
}

if ($stmt) {
    if (isset($representante_id) || $tipoUsuario === 'representante') {
        mysqli_stmt_execute($stmt);
    } else {
        mysqli_stmt_execute($stmt);
    }
    $result = mysqli_stmt_get_result($stmt);
    
    $cidades = [];
    while ($row = mysqli_fetch_assoc($result)) {
        $cidades[] = $row;
    }
    
    echo json_encode($cidades);
    mysqli_stmt_close($stmt);
} else {
    echo json_encode(['error' => 'Erro na preparação da query']);
}

mysqli_close($conexao);
?>




