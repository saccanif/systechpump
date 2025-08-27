<?php

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

?>



