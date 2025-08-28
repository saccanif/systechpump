<?php
require_once '../../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
    $senha = $_POST['senha'];
    $senhaHash = hash('sha256',$senha);
        
    $query = "SELECT * FROM usuario WHERE emailUsuario = '$usuario' OR nomeUsuario = '$usuario'";
    $result = mysqli_query($conexao, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Verifique a senha (texto puro)
        if ($senhaHash === $user['senhaHASH_Usuario']) {
            $_SESSION['usuario'] = $user;
            
            // Redirecionar baseado no tipo de usuário
            switch ($user['tipoUsuario']) {
                case 'admin':
                    header('Location: ../../public/admHome.php');
                    exit();
                case 'representante':
                    header('Location: ../../public/representanteHome.php');
                    exit();
                case 'lojista':
                    header('Location: ../../public/lojaHome.php');
                    exit();
                default:
                    header('Location: ../../public/login.php?erro=Tipo+de+usuário+inválido');
                    exit();
            }
        } else {
            header('Location: ../../public/login.php?erro=Senha+incorreta');
            exit();
        }
    } else {
        header('Location: ../../public/login.php?erro=Usuário+não+encontrado');
        exit();
    }
} else {
    header('Location: ../../public/login.php');
    exit();
}
?>