<?php
    include_once "../../config/connection.php";

    // Permitir CORS para requisições AJAX
    header('Access-Control-Allow-Origin: *');
    header('Content-Type: application/json');

    $conexao = conectarBD();

    if (isset($_GET['estado_id']) && !empty($_GET['estado_id'])) {
        $estado_id = intval($_GET['estado_id']);
        
        $sql = "SELECT c.idCidade, c.nomeCidade, e.siglaEstado 
                FROM cidade c 
                INNER JOIN estado e ON c.estado_idEstado = e.idEstado 
                WHERE c.estado_idEstado = ? 
                ORDER BY c.nomeCidade";
        $stmt = mysqli_prepare($conexao, $sql);
        
        if ($stmt) {
            mysqli_stmt_bind_param($stmt, "i", $estado_id);
            mysqli_stmt_execute($stmt);
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
    } else {
        echo json_encode(['error' => 'Estado não especificado']);
    }

    mysqli_close($conexao);
?>