<?php
session_start();

// Verificar se é admin OU representante
if (!isset($_SESSION['usuario']) || ($_SESSION['usuario']['tipoUsuario'] !== 'representante' && $_SESSION['usuario']['tipoUsuario'] !== 'admin')) {
    $_SESSION['erro'] = "Acesso não autorizado";
    header("Location: ../../public/gerenciadorLojas.php");
    exit();
}

include_once "../../config/connection.php";

// Cadastrar nova loja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cadastrar_loja'])) {
    $nomeLoja = $_POST['nomeLoja'] ?? '';
    $telefoneLoja = $_POST['telefoneLoja'] ?? '';
    $emailLoja = $_POST['emailLoja'] ?? '';
    $fotoLoja = $_POST['fotoLoja'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade_idCidade = $_POST['cidade_idCidade'] ?? '';
    
    // Definir representante_id baseado no tipo de usuário
    $usuario = $_SESSION['usuario'];
    if ($usuario['tipoUsuario'] === 'admin') {
        // Admin pode definir qualquer representante
        $representante_id = $_POST['representante_id'] ?? '';
    } else {
        // Representante só pode criar lojas para si mesmo
        $representante_id = $usuario['idUsuario'];
    }

    // Validações
    if (empty($nomeLoja) || empty($cidade_idCidade) || empty($representante_id)) {
        $_SESSION['erro'] = "Nome da loja, cidade e representante são obrigatórios";
        header("Location: ../../public/gerenciadorLojas.php");
        exit();
    }

    $conexao = conectarBD();

    try {
        // Iniciar transação
        mysqli_begin_transaction($conexao);

        // 1. Criar a loja
        $sql_loja = "INSERT INTO lojas (nomeLoja, telefoneLoja, emailLoja, fotoLoja, 
                     cep, logradouro, numero, bairro, cidade_idCidade, 
                     representante_id, lojaCriadoEm) 
                     VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, NOW())";
        $stmt_loja = mysqli_prepare($conexao, $sql_loja);
        mysqli_stmt_bind_param($stmt_loja, "sssssssssi", 
            $nomeLoja, $telefoneLoja, $emailLoja, $fotoLoja,
            $cep, $logradouro, $numero, $bairro, $cidade_idCidade,
            $representante_id
        );
        
        if (!mysqli_stmt_execute($stmt_loja)) {
            throw new Exception("Erro ao criar loja: " . mysqli_error($conexao));
        }

        $idLoja = mysqli_insert_id($conexao);
        error_log("✅ Loja criada com ID: $idLoja");

        // 2. Criar usuário lojista automaticamente vinculado à loja
        $email_lojista = !empty($emailLoja) ? $emailLoja : "loja{$idLoja}@systempump.com";
        $senha_temporaria = "loja{$idLoja}"; // Senha padrão baseada no ID da loja
        $senha_hash = hash('sha256', $senha_temporaria);
        
        // Nome do usuário baseado no nome da loja
        $nomeUsuario = substr(preg_replace('/[^a-zA-Z0-9]/', '', $nomeLoja), 0, 20);
        if (empty($nomeUsuario)) {
            $nomeUsuario = "lojista{$idLoja}";
        }

        // Buscar o idEstado baseado na cidade selecionada
        $sql_estado = "SELECT estado_idEstado FROM cidade WHERE idCidade = ?";
        $stmt_estado = mysqli_prepare($conexao, $sql_estado);
        mysqli_stmt_bind_param($stmt_estado, "i", $cidade_idCidade);
        mysqli_stmt_execute($stmt_estado);
        $result_estado = mysqli_stmt_get_result($stmt_estado);
        $cidade_info = mysqli_fetch_assoc($result_estado);
        
        if (!$cidade_info) {
            throw new Exception("Cidade não encontrada");
        }
        
        $idEstado = $cidade_info['estado_idEstado'];

        // Criar usuário lojista vinculado à loja
        $sql_usuario = "INSERT INTO usuario (
                        nomeUsuario, emailUsuario, senhaHASH_Usuario, tipoUsuario, 
                        loja_id, UsuCriadoEm, cidade_idCidade, idEstado
                    ) VALUES (?, ?, ?, 'lojista', ?, NOW(), ?, ?)";
        $stmt_usuario = mysqli_prepare($conexao, $sql_usuario);
        mysqli_stmt_bind_param($stmt_usuario, "sssiii", 
            $nomeUsuario, 
            $email_lojista, 
            $senha_hash, 
            $idLoja,
            $cidade_idCidade,
            $idEstado
        );
        
        if (!mysqli_stmt_execute($stmt_usuario)) {
            throw new Exception("Erro ao criar usuário lojista: " . mysqli_error($conexao));
        }

        $idUsuarioLojista = mysqli_insert_id($conexao);
        error_log("✅ Usuário lojista criado - ID: $idUsuarioLojista, Email: $email_lojista, Senha: $senha_temporaria");

        // Commit da transação
        mysqli_commit($conexao);

        $_SESSION['sucesso'] = "Loja cadastrada com sucesso! Usuário lojista criado automaticamente (Email: $email_lojista, Senha: $senha_temporaria)";

    } catch (Exception $e) {
        // Rollback em caso de erro
        mysqli_rollback($conexao);
        $_SESSION['erro'] = $e->getMessage();
        error_log("❌ Erro ao cadastrar loja: " . $e->getMessage());
    } finally {
        mysqli_close($conexao);
    }

    header("Location: ../../public/gerenciadorLojas.php");
    exit();
}

// Editar loja
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['editar_loja'])) {
    $idlojas = $_POST['idlojas'] ?? '';
    $nomeLoja = $_POST['nomeLoja'] ?? '';
    $telefoneLoja = $_POST['telefoneLoja'] ?? '';
    $emailLoja = $_POST['emailLoja'] ?? '';
    $fotoLoja = $_POST['fotoLoja'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade_idCidade = $_POST['cidade_idCidade'] ?? '';

    if (empty($nomeLoja) || empty($cidade_idCidade) || empty($idlojas)) {
        $_SESSION['erro'] = "Nome da loja, cidade e ID são obrigatórios";
        header("Location: ../../public/gerenciadorLojas.php");
        exit();
    }

    $conexao = conectarBD();

    // Verificar permissão (admin pode editar qualquer loja, representante só suas lojas)
    $usuario = $_SESSION['usuario'];
    if ($usuario['tipoUsuario'] === 'representante') {
        $sql_verifica = "SELECT idlojas FROM lojas WHERE idlojas = ? AND representante_id = ?";
        $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
        mysqli_stmt_bind_param($stmt_verifica, "ii", $idlojas, $usuario['idUsuario']);
        mysqli_stmt_execute($stmt_verifica);
        $result_verifica = mysqli_stmt_get_result($stmt_verifica);
        
        if (mysqli_num_rows($result_verifica) === 0) {
            $_SESSION['erro'] = "Você não tem permissão para editar esta loja";
            header("Location: ../../public/gerenciadorLojas.php");
            exit();
        }
    }

    $sql = "UPDATE lojas SET nomeLoja = ?, telefoneLoja = ?, emailLoja = ?, fotoLoja = ?, 
            cep = ?, logradouro = ?, numero = ?, bairro = ?, cidade_idCidade = ? 
            WHERE idlojas = ?";
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "sssssssssi", 
        $nomeLoja, $telefoneLoja, $emailLoja, $fotoLoja,
        $cep, $logradouro, $numero, $bairro, $cidade_idCidade, $idlojas
    );

    if (mysqli_stmt_execute($stmt)) {
        $_SESSION['sucesso'] = "Loja atualizada com sucesso!";
    } else {
        $_SESSION['erro'] = "Erro ao atualizar loja: " . mysqli_error($conexao);
    }

    mysqli_close($conexao);
    header("Location: ../../public/gerenciadorLojas.php");
    exit();
}

// Excluir loja
if (isset($_GET['excluir'])) {
    $idlojas = $_GET['excluir'];
    
    $conexao = conectarBD();

    // Verificar permissão (admin pode excluir qualquer loja, representante só suas lojas)
    $usuario = $_SESSION['usuario'];
    if ($usuario['tipoUsuario'] === 'representante') {
        $sql_verifica = "SELECT idlojas FROM lojas WHERE idlojas = ? AND representante_id = ?";
        $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
        mysqli_stmt_bind_param($stmt_verifica, "ii", $idlojas, $usuario['idUsuario']);
        mysqli_stmt_execute($stmt_verifica);
        $result_verifica = mysqli_stmt_get_result($stmt_verifica);
        
        if (mysqli_num_rows($result_verifica) === 0) {
            $_SESSION['erro'] = "Você não tem permissão para excluir esta loja";
            header("Location: ../../public/gerenciadorLojas.php");
            exit();
        }
    }

    // Iniciar transação para exclusão
    mysqli_begin_transaction($conexao);
    
    try {
        // 1. Buscar o ID do usuário lojista vinculado à loja (pelo campo loja_id na tabela usuario)
        $sql_buscar_usuario = "SELECT idUsuario FROM usuario WHERE loja_id = ? AND tipoUsuario = 'lojista'";
        $stmt_buscar = mysqli_prepare($conexao, $sql_buscar_usuario);
        mysqli_stmt_bind_param($stmt_buscar, "i", $idlojas);
        mysqli_stmt_execute($stmt_buscar);
        $result_buscar = mysqli_stmt_get_result($stmt_buscar);
        $usuario_info = mysqli_fetch_assoc($result_buscar);
        
        $idUsuarioLojista = $usuario_info['idUsuario'] ?? null;

        // 2. Excluir a loja
        $sql_excluir_loja = "DELETE FROM lojas WHERE idlojas = ?";
        $stmt_excluir_loja = mysqli_prepare($conexao, $sql_excluir_loja);
        mysqli_stmt_bind_param($stmt_excluir_loja, "i", $idlojas);

        if (!mysqli_stmt_execute($stmt_excluir_loja)) {
            throw new Exception("Erro ao excluir loja: " . mysqli_error($conexao));
        }

        // 3. Excluir o usuário lojista se existir
        if ($idUsuarioLojista) {
            $sql_excluir_usuario = "DELETE FROM usuario WHERE idUsuario = ?";
            $stmt_excluir_usuario = mysqli_prepare($conexao, $sql_excluir_usuario);
            mysqli_stmt_bind_param($stmt_excluir_usuario, "i", $idUsuarioLojista);
            mysqli_stmt_execute($stmt_excluir_usuario);
        }

        // Commit da transação
        mysqli_commit($conexao);
        $_SESSION['sucesso'] = "Loja e usuário lojista excluídos com sucesso!";

    } catch (Exception $e) {
        // Rollback em caso de erro
        mysqli_rollback($conexao);
        $_SESSION['erro'] = $e->getMessage();
    } finally {
        mysqli_close($conexao);
    }

    header("Location: ../../public/gerenciadorLojas.php");
    exit();
}

// Redirecionar se acesso direto
header("Location: ../../public/gerenciadorLojas.php");
exit();
?>