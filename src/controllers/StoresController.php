<?php
    // Recebendo dados do formulário
    $nomeLoja = $_POST["nomeLoja"];
    $telefoneLoja = $_POST["telefoneLoja"];
    $emailLoja = $_POST["emailLoja"];
    $idRepresentante = $_POST["idRepresentante"];
    $cep = $_POST["cep"];
    $bairro = $_POST["bairro"];
    $logradouro = $_POST["logradouro"];
    $numero = $_POST["numero"];
    $idCidade = $_POST["idCidade"];
    $fotoLoja = $_FILES["fotoLoja"];

    include_once "../../config/connection.php";
    include_once "./functions.php";

    $conexao = conectarBD();

    // Converter a foto para bytes
    $ftConvertida = transformarBytes($fotoLoja);

    // Inserir dados na tabela lojas (sem estado_idEstado)
    $sql = "INSERT INTO lojas (
                nomeLoja,
                telefoneLoja,
                emailLoja,
                lojaCriadoEm,
                cidade_idCidade,
                Usuario_idUsuario,
                fotoLoja,
                cep,
                bairro,
                logradouro,
                numero
            )
            VALUES (
                '$nomeLoja',
                '$telefoneLoja',
                '$emailLoja',
                NOW(),
                $idCidade,
                $idRepresentante,
                '$ftConvertida',
                '$cep',
                '$bairro',
                '$logradouro',
                '$numero'
            )";

    if (mysqli_query($conexao, $sql)) {
        $cod = mysqli_insert_id($conexao);
        header("Location:../../public/cadastroStores.php?msg=Loja $nomeLoja inserida com sucesso! ID: $cod");
    } else {
        header("Location:../../public/cadastroStores.php?msg=Erro ao cadastrar loja: " . mysqli_error($conexao));
    }

    mysqli_close($conexao);
?>