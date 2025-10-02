<?php
session_start();
require_once "../../config/connection.php";

$usuario = $_POST['usuario'] ?? '';
$senha = $_POST['senha'] ?? '';

$conexao = conectarBD();

// Busca usuário pelo e-mail ou nome
$sql = "SELECT * FROM usuario WHERE emailUsuario = ? OR nomeUsuario = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    // Verifica senha usando SHA256 (conforme está no banco)
    $senhaHash = hash('sha256', $senha);
    
    if ($senhaHash === $row['senhaHASH_Usuario']) {
        // Cria a sessão (remove a senha por segurança)
        unset($row['senhaHASH_Usuario']);
        $_SESSION['usuario'] = $row;

        // Redireciona de acordo com o tipo de usuário
        switch ($row['tipoUsuario']) {
            case 'admin':
                header("Location: ../../public/adm.php");
                exit();
            case 'representante':
                header("Location: ../../public/representante.php");
                exit();
            case 'logista':
                header("Location: ../../public/logista.php");
                exit();
            default:
                header("Location: ../../public/login.php?erro=Tipo de usuário inválido");
                exit();
        }
    } else {
        // Senha incorreta
        header("Location: ../../public/login.php?erro=Senha incorreta");
        exit();
    }
} else {
    // Usuário não encontrado
    header("Location: ../../public/login.php?erro=Usuário não encontrado");
    exit();
}