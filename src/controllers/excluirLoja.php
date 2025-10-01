<?php

    require_once("../controllers/functions.php");
    excluirLoja( $_GET["id"] );

    header("Location:../../deletarLojaRepre.php");

?>
