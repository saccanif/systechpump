<?php
   function conectarBD(){
        $conexao = mysqli_connect("127.0.0.1:3306","root","","systechpump") or die ("Erro ao conectar com o Banco!");
        
        mysqli_query($conexao, "SET NAMES 'utf8'");
        mysqli_query($conexao, "SET character_set_connection=utf8");
        mysqli_query($conexao, "SET character_set_client=utf8");
        mysqli_query($conexao, "SET character_set_results=utf8");

        return $conexao;
    }

    $conexao = conectarBD();
?>
