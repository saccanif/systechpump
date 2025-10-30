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

    $sucesso = mysqli_query($conexao, $sql);

    $cod = mysqli_insert_id($conexao);
    
    if ($sucesso) {
        // Verificar se há sessão para redirecionar corretamente
        session_start();
        if (isset($_SESSION['usuario'])) {
            $user = $_SESSION['usuario'];
            switch ($user['tipoUsuario']) {
                case 'admin':
                    header('Location:../../public/cadastroCidadesAdm.php?msg=Cidade $nomeCidade inserida com sucesso!');
                    exit();
                case 'representante':
                    header('Location:../../public/gerenciadorCidades.php?msg=Cidade $nomeCidade inserida com sucesso!');
                    exit();
            }
        } else {
            // Fallback caso não haja sessão
            header('Location:../../public/cadastroCidadesAdm.php?msg=Cidade $nomeCidade inserida com sucesso!');
            exit();
        }
    } else {
        header('Location:../../public/cadastroCidadesRepre.php?msg=Cidade $nomeCidade não inserida!');
        exit();
    }
?>