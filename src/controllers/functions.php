<?php

    // Conexão com o BD
    function conectarBD() {

    $conexao = mysqli_connect("127.0.0.1:3306", "root", "", "tipi" );
    
    // PARA RESOLVER PROBLEMAS DE ACENTUAÇÃO 
    // Converte CODIFICAÇÃO para UTF-8
     mysqli_query($conexao, "SET NAMES 'utf8'");
     mysqli_query($conexao, "SET character_set_connection=utf8");
     mysqli_query($conexao, "SET character_set_client=utf8");
     mysqli_query($conexao, "SET character_set_results=utf8");
    
    
    return $conexao;

}

    // funções de validação

    function ValidarCadastro ($email,$senha) {

        $msgErro = "";

        $emailRes = validarEmail($email);
        $senhaRes = validarSenha($senha);
       

        $msgErro .= $senhaRes;
        $msgErro .= $emailRes;

        return $msgErro;
    }

    function validarEmail ($email){
        $msgErro = "";
        if (empty($email)){
            $msgErro = $msgErro . "Campo de email vazio, informe seu email!<BR>";
        } else if (filter_var($email,FILTER_VALIDATE_EMAIL)){
            $msgErro = $msgErro . "";
        } else {
            $msgErro = $msgErro . "Email Inválido, informe um correto!<BR>";
        }

        return $msgErro;
    }

    function validarSenha($senha) {
        $msgErro = "";

        if (empty($senha)){
            $msgErro .= "Campo de Senha vazio, informe sua senha!<br>";
        } else if (strlen($senha) < 8){
            $msgErro .= "A senha deve ter pelo menos 8 caracteres!<br>";
        } else if (!preg_match('/[A-Z]/', $senha)) {
            $msgErro .= "A senha deve conter pelo menos uma letra maiúscula!<br>";
        } else  if (!preg_match('/[a-z]/', $senha)) {
            $msgErro .= "A senha deve conter pelo menos uma letra minúscula!<br>";
        } else   if (!preg_match('/[0-9]/', $senha)) {
            $msgErro .= "A senha deve conter pelo menos um número!<br>";
        } else if (!preg_match('/[\W_]/', $senha)) {
            $msgErro .= "A senha deve conter pelo menos um caractere especial!<br>";
        }
            
        
        return $msgErro;         
    }

    function transformarBytes($arquivo){
        $tamanhoimg = $arquivo["size"];

        $arqAberto = fopen($arquivo["tmp_name"],"r");

        $foto = addslashes(fread($arqAberto,$tamanhoimg));

        return $foto;
    }

    function excluirLoja($idLoja, $conexao) {

        // Excluir a loja
        $sqlExcluirLoja = "DELETE FROM lojas WHERE id = ?";
        $stmtLoja = $conexao->prepare($sqlExcluirLoja);
        $stmtLoja->bind_param("i", $idLoja);
        $stmtLoja->execute();
        $stmtLoja->close();
    }

    function alterarLoja($id, $nome, $telefone, $email, $arquivo) {

    $conexao = conectarBD();   
    
    // Converter a imagem
    $tamanhoImg = $arquivo["size"]; 
    $arqAberto = fopen ( $arquivo["tmp_name"], "r" );
    $foto = addslashes( fread ( $arqAberto , $tamanhoImg ) );

    // Montar SQL
    $sql = "UPDATE lojas SET "

    . "nomeLoja = '$nome', "
    . "telefoneLoja = '$telefone', "
    . "emailLoja = '$email', "
    . "fotoLoja = '$foto' "
    . "WHERE idLoja = $id";

    mysqli_query($conexao, $sql) or die ( mysqli_error($conexao) . $sql );  
    
    return $id;
}
?>



