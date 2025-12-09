<?php
session_start();

require_once "../../config/connection.php";

$usuario = $_POST['usuario'];
$senha = $_POST['senha'];

$conexao = conectarBD();

$sql = "SELECT * FROM usuario WHERE emailUsuario = ? OR nomeUsuario = ?";
$stmt = mysqli_prepare($conexao, $sql);
mysqli_stmt_bind_param($stmt, "ss", $usuario, $usuario);
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);

if ($row = mysqli_fetch_assoc($result)) {
    $senhaHash = hash('sha256', $senha);
    
    if ($senhaHash === $row['senhaHASH_Usuario']) {
        unset($row['senhaHASH_Usuario']);
        
        // Sessão simples - MESMO PADRÃO PARA TODOS
        $_SESSION['usuario'] = $row;

        switch ($row['tipoUsuario']) {
            case 'admin':
                header("Location: ../../public/adm.php");
                exit();
            case 'representante':
                header("Location: ../../public/representante.php");
                exit();
            case 'lojista':
                header("Location: ../../public/loja.php");
                exit();
        }
    } else {
        header("Location: ../../public/login.php?erro=Senha incorreta");
        exit();
    }
} else {
    header("Location: ../../public/login.php?erro=Usuário não encontrado");
    exit();
}
?>
