<?php
    function iniciarSessaoSegura() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
        
        // Se já existe um usuário logado nesta sessão, destruir
        if (isset($_SESSION['usuario']) && !isset($_SESSION['nova_sessao'])) {
            session_destroy();
            session_start();
            session_regenerate_id(true);
        }
        
        $_SESSION['nova_sessao'] = true;
    }
?>