<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once '../../config/connection.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $usuario = mysqli_real_escape_string($conexao, $_POST['usuario']);
    $senha = $_POST['senha'];
    
    echo "Usuário: $usuario<br>"; // DEBUG
    echo "Senha: $senha<br>"; // DEBUG
    
    $query = "SELECT * FROM Usuario WHERE emailUsuario = '$usuario' OR nomeUsuario = '$usuario'";
    $result = mysqli_query($conexao, $query);
    
    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        echo "Usuário encontrado:<br>"; // DEBUG
        print_r($user); // DEBUG
        
        // Verifique a senha (texto puro)
        if ($senha === $user['senhaltASL_Usuario']) {
            $_SESSION['usuario'] = $user;
            
            echo "Sessão após login:<br>"; // DEBUG
            print_r($_SESSION); // DEBUG
            
            // Redirecionar baseado no tipo de usuário
            switch ($user['tipoUsuario']) {
                case 'administrador':
                    header('Location: ../../public/admHome.php');
                    exit();
                case 'representante':
                    header('Location: ../../public/representanteHome.php');
                    exit();
                case 'loja':
                    header('Location: ../../public/lojaHome.php');
                    exit();
                default:
                    header('Location: ../../public/login.php?erro=Tipo+de+usuário+inválido');
                    exit();
            }
        } else {
            echo "Senha não confere!<br>"; // DEBUG
            header('Location: ../../public/login.php?erro=Senha+incorreta');
            exit();
        }
    } else {
        echo "Usuário não encontrado!<br>"; // DEBUG
        header('Location: ../../public/login.php?erro=Usuário+não+encontrado');
        exit();
    }
} else {
    header('Location: ../../public/login.php');
    exit();
}
?>