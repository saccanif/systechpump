<?php
require_once "../config/connection.php";

// Verifica se o estado foi enviado
if (!isset($_GET["estado"]) || empty($_GET["estado"])) {
    echo "<option value=''>Selecione um estado primeiro</option>";
    exit;
}

$con = conectarBD();

// Segurança: Garante que é um número inteiro
$idEstado = intval($_GET["estado"]);

// Busca cidades apenas desse estado
$sql = "SELECT idCidade, nomeCidade FROM cidade WHERE estado_idEstado = ? ORDER BY nomeCidade";
$stmt = mysqli_prepare($con, $sql);
mysqli_stmt_bind_param($stmt, "i", $idEstado);
mysqli_stmt_execute($stmt);
$res = mysqli_stmt_get_result($stmt);

if (mysqli_num_rows($res) > 0) {
    echo "<option value=''>Escolha uma cidade</option>";
    while ($c = mysqli_fetch_assoc($res)) {
        echo "<option value='{$c['idCidade']}'>{$c['nomeCidade']}</option>";
    }
    // Adicionar opção para outras cidades
    echo "<option value='outra'>Outra cidade (não listada)</option>";
} else {
    echo "<option value=''>Escolha uma cidade</option>";
    echo "<option value='outra'>Outra cidade (não listada)</option>";
}

mysqli_close($con);
?>