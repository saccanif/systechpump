<?php
function startSecureSession() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
    
    // Criar um fingerprint único para esta sessão
    $fingerprint = md5($_SERVER['HTTP_USER_AGENT'] . $_SERVER['REMOTE_ADDR'] . time() . rand());
    
    // Se não existe fingerprint ou é diferente, regenerar sessão
    if (!isset($_SESSION['fingerprint'])) {
        $_SESSION['fingerprint'] = $fingerprint;
    } elseif ($_SESSION['fingerprint'] !== $fingerprint) {
        // Sessão inválida - destruir e recriar
        session_destroy();
        session_start();
        session_regenerate_id(true);
        $_SESSION['fingerprint'] = $fingerprint;
    }
}
?>