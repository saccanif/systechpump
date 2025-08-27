<?php
    // recebendo dados
    $nomeLoja = $_POST["nomeLoja"];
    $telefoneLoja = $_POST["telefoneLoja"];
    $emailLoja = $_POST["emailLoja"];
    $cidadeLoja = $_POST["idcidade"];
    $usuarioLoja = $_POST["idRepresentante"];
    $fotoLoja = $_FILES["fotoLoja"];

    include_once "../../config/connection.php";
    include_once "./functions.php";

    $conexao = conectarBD();

    $ftConvertida = transformarBytes($fotoLoja);

    // inserindo dados na tabela lojas
    $sql = "INSERT INTO lojas (
                nomeLoja,
                telefoneLoja,
                emailLoja,
                lojaCriadoEm,
                cidade_idCidade,
                Usuario_idUsuario,
                fotoLoja
            )
            VALUES (
                '$nomeLoja',
                '$telefoneLoja',
                '$emailLoja',
                NOW(),
                $cidadeLoja,
                $usuarioLoja,
                '$ftConvertida'
            )";

    mysqli_query($conexao, $sql);

    $cod = mysqli_insert_id($conexao);

    header("Location:../../public/cadastroStores.php?msg=Loja $cod inserida com sucesso!");
?>
