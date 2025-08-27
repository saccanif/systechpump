<?php
    // recebendo dados
    $nomeCidade = $_POST["nomeCidade"];
    $estadoCidade = $_POST["idEstado"];

    include_once "../../config/connection.php";

    // conectar ao banco
    $conexao = conectarBD();

    // inserindo dados na tabela cidade
    $sql = "INSERT INTO cidade (
                nomeCidade,
                estado_idEstado,
                qtdaAcesso
            )
            VALUES (
                '$nomeCidade',
                $estadoCidade,
                0
            )";

    mysqli_query($conexao, $sql);

    $cod = mysqli_insert_id($conexao);

    header("Location:../../public/cadastroCidades.php?msg=Cidade $cod inserida com sucesso!");
?>
