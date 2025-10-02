<?php
session_start();
include_once "../../config/connection.php";
$conexao = conectarBD();

// Verifica se o usuário está logado
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../public/login.php?msg=Faça login para continuar");
    exit;
}

// --- CADASTRAR ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['update'])) {
    $nomeUsuario  = mysqli_real_escape_string($conexao, $_POST["nomeUsuario"]);
    $emailUsuario = mysqli_real_escape_string($conexao, $_POST["emailUsuario"]);
    $senha        = mysqli_real_escape_string($conexao, $_POST["senha"]);
    $tipoUsuario  = mysqli_real_escape_string($conexao, $_POST["tipoUsuario"]);
    $avatar_url   = mysqli_real_escape_string($conexao, $_POST["avatar_url"]);
    $idCidade     = intval($_POST["idCidade"]); // já garante que é número

    $senhaHASH = hash('sha256', $senha);

    $sql = "INSERT INTO usuario (
                nomeUsuario,
                emailUsuario,
                senhaHASH_Usuario,
                tipoUsuario,
                avatar_url,
                UsuCriadoEm,
                cidade_idCidade
            ) VALUES (
                '$nomeUsuario',
                '$emailUsuario',
                '$senhaHASH',
                '$tipoUsuario',
                '$avatar_url',
                NOW(),
                $idCidade
            )";

    if (mysqli_query($conexao, $sql)) {
        $cod = mysqli_insert_id($conexao);
        header("Location: ../../public/gerenciadorUsers.php?msg=Usuário $nomeUsuario cadastrado com sucesso! ID: $cod");
    } else {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro ao cadastrar usuário: " . mysqli_error($conexao));
    }
    exit;
}

// --- EDITAR (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $idUsuario    = intval($_POST["idUsuario"]);
    $nomeUsuario  = mysqli_real_escape_string($conexao, $_POST["nomeUsuario"]);
    $emailUsuario = mysqli_real_escape_string($conexao, $_POST["emailUsuario"]);
    $tipoUsuario  = mysqli_real_escape_string($conexao, $_POST["tipoUsuario"]);
    $avatar_url   = mysqli_real_escape_string($conexao, $_POST["avatar_url"]);

    $sql = "UPDATE usuario 
            SET nomeUsuario = '$nomeUsuario',
                emailUsuario = '$emailUsuario',
                tipoUsuario = '$tipoUsuario',
                avatar_url = '$avatar_url'
            WHERE idUsuario = $idUsuario";

    if (mysqli_query($conexao, $sql)) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Usuário atualizado com sucesso!");
    } else {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro ao atualizar usuário: " . mysqli_error($conexao));
    }
    exit;
}

// --- EXCLUIR (DELETE) ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    $sql = "DELETE FROM usuario WHERE idUsuario = $id";

    if (mysqli_query($conexao, $sql)) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Usuário excluído com sucesso!");
    } else {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro ao excluir usuário: " . mysqli_error($conexao));
    }
    exit;
}

mysqli_close($conexao);
?>
