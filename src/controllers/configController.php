<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: ../../public/login.php?msg=Faça login para continuar");
    exit;
}

$usuario = $_SESSION['usuario'];
$idUsuario = $usuario['idUsuario'];
$tipoUsuario = $usuario['tipoUsuario'];

include_once "../../config/connection.php";
$conexao = conectarBD();

// Upload de Avatar
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'upload_avatar') {
    if (isset($_FILES['avatar']) && $_FILES['avatar']['error'] === UPLOAD_ERR_OK) {
        $file = $_FILES['avatar'];
        
        // Validar tipo de arquivo
        $allowedTypes = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
        $fileType = mime_content_type($file['tmp_name']);
        
        if (!in_array($fileType, $allowedTypes)) {
            header("Location: ../../public/gerenciadorConfig.php?erro=Tipo de arquivo não permitido. Use JPG, PNG, GIF ou WEBP");
            exit;
        }
        
        // Validar tamanho (máximo 5MB)
        if ($file['size'] > 5 * 1024 * 1024) {
            header("Location: ../../public/gerenciadorConfig.php?erro=Arquivo muito grande. Máximo 5MB");
            exit;
        }
        
        // Criar diretório de uploads se não existir
        $uploadDir = '../../public/uploads/avatars/';
        if (!file_exists($uploadDir)) {
            if (!file_exists('../../public/uploads/')) {
                mkdir('../../public/uploads/', 0777, true);
            }
            mkdir($uploadDir, 0777, true);
        }
        
        // Gerar nome único
        $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
        $fileName = 'avatar_' . $idUsuario . '_' . time() . '.' . $extension;
        $filePath = $uploadDir . $fileName;
        
        // Mover arquivo
        if (move_uploaded_file($file['tmp_name'], $filePath)) {
            // URL relativa para salvar no banco
            $avatarUrl = './uploads/avatars/' . $fileName;
            
            // Deletar avatar antigo se existir
            if (!empty($usuario['avatar_url']) && strpos($usuario['avatar_url'], 'uploads/avatars/') !== false) {
                $oldPath = '../../public/' . $usuario['avatar_url'];
                if (file_exists($oldPath)) {
                    unlink($oldPath);
                }
            }
            
            // Atualizar no banco
            $sql = "UPDATE usuario SET avatar_url = ? WHERE idUsuario = ?";
            $stmt = mysqli_prepare($conexao, $sql);
            mysqli_stmt_bind_param($stmt, "si", $avatarUrl, $idUsuario);
            
            if (mysqli_stmt_execute($stmt)) {
                // Atualizar sessão
                $_SESSION['usuario']['avatar_url'] = $avatarUrl;
                header("Location: ../../public/gerenciadorConfig.php?sucesso=Foto atualizada com sucesso!");
            } else {
                header("Location: ../../public/gerenciadorConfig.php?erro=Erro ao atualizar foto no banco de dados");
            }
            
            mysqli_stmt_close($stmt);
        } else {
            header("Location: ../../public/gerenciadorConfig.php?erro=Erro ao fazer upload do arquivo");
        }
    } else {
        header("Location: ../../public/gerenciadorConfig.php?erro=Nenhum arquivo selecionado ou erro no upload");
    }
    exit;
}

// Atualizar Dados
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'atualizar_dados') {
    $nomeUsuario = mysqli_real_escape_string($conexao, $_POST['nomeUsuario'] ?? '');
    $emailUsuario = mysqli_real_escape_string($conexao, $_POST['emailUsuario'] ?? '');
    
    if (empty($nomeUsuario) || empty($emailUsuario)) {
        header("Location: ../../public/gerenciadorConfig.php?erro=Nome e e-mail são obrigatórios");
        exit;
    }
    
    // Verificar se email já existe (exceto para o próprio usuário)
    $sql_check = "SELECT idUsuario FROM usuario WHERE emailUsuario = ? AND idUsuario != ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "si", $emailUsuario, $idUsuario);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) > 0) {
        header("Location: ../../public/gerenciadorConfig.php?erro=Este e-mail já está em uso por outro usuário");
        exit;
    }
    
    // Atualizar dados do usuário
    $sql = "UPDATE usuario SET nomeUsuario = ?, emailUsuario = ?";
    $params = [$nomeUsuario, $emailUsuario];
    $types = "ss";
    
    // Atualizar WhatsApp se for representante
    if ($tipoUsuario === 'representante' && isset($_POST['whatsapp'])) {
        $whatsapp = mysqli_real_escape_string($conexao, $_POST['whatsapp']);
        $sql .= ", whatsapp = ?";
        $params[] = $whatsapp;
        $types .= "s";
    }
    
    $sql .= " WHERE idUsuario = ?";
    $params[] = $idUsuario;
    $types .= "i";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, $types, ...$params);
    
    if (mysqli_stmt_execute($stmt)) {
        // Atualizar dados da loja se for lojista
        if ($tipoUsuario === 'lojista' && isset($_POST['cep'])) {
            $cep = mysqli_real_escape_string($conexao, $_POST['cep'] ?? '');
            $logradouro = mysqli_real_escape_string($conexao, $_POST['logradouro'] ?? '');
            $numero = mysqli_real_escape_string($conexao, $_POST['numero'] ?? '');
            $bairro = mysqli_real_escape_string($conexao, $_POST['bairro'] ?? '');
            
            // Buscar ID da loja
            $sql_loja = "SELECT loja_id FROM usuario WHERE idUsuario = ?";
            $stmt_loja = mysqli_prepare($conexao, $sql_loja);
            mysqli_stmt_bind_param($stmt_loja, "i", $idUsuario);
            mysqli_stmt_execute($stmt_loja);
            $result_loja = mysqli_stmt_get_result($stmt_loja);
            $loja_data = mysqli_fetch_assoc($result_loja);
            
            if ($loja_data && !empty($loja_data['loja_id'])) {
                $idLoja = $loja_data['loja_id'];
                
                $sql_update_loja = "UPDATE lojas SET cep = ?, logradouro = ?, numero = ?, bairro = ? WHERE idlojas = ?";
                $stmt_update_loja = mysqli_prepare($conexao, $sql_update_loja);
                mysqli_stmt_bind_param($stmt_update_loja, "ssssi", $cep, $logradouro, $numero, $bairro, $idLoja);
                mysqli_stmt_execute($stmt_update_loja);
                mysqli_stmt_close($stmt_update_loja);
            }
            mysqli_stmt_close($stmt_loja);
        }
        
        // Atualizar sessão
        $_SESSION['usuario']['nomeUsuario'] = $nomeUsuario;
        $_SESSION['usuario']['emailUsuario'] = $emailUsuario;
        if ($tipoUsuario === 'representante' && isset($_POST['whatsapp'])) {
            $_SESSION['usuario']['whatsapp'] = $_POST['whatsapp'];
        }
        
        header("Location: ../../public/gerenciadorConfig.php?sucesso=Dados atualizados com sucesso!");
    } else {
        header("Location: ../../public/gerenciadorConfig.php?erro=Erro ao atualizar dados: " . mysqli_error($conexao));
    }
    
    mysqli_stmt_close($stmt);
    exit;
}

// Alterar Senha
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['acao']) && $_POST['acao'] === 'alterar_senha') {
    $senha_atual = $_POST['senha_atual'] ?? '';
    $nova_senha = $_POST['nova_senha'] ?? '';
    $confirmar_senha = $_POST['confirmar_senha'] ?? '';
    
    if (empty($senha_atual) || empty($nova_senha) || empty($confirmar_senha)) {
        header("Location: ../../public/gerenciadorConfig.php?erro=Preencha todos os campos");
        exit;
    }
    
    if ($nova_senha !== $confirmar_senha) {
        header("Location: ../../public/gerenciadorConfig.php?erro=As novas senhas não coincidem");
        exit;
    }
    
    if (strlen($nova_senha) < 6) {
        header("Location: ../../public/gerenciadorConfig.php?erro=A nova senha deve ter pelo menos 6 caracteres");
        exit;
    }
    
    // Verificar senha atual
    $senha_atual_hash = hash('sha256', $senha_atual);
    $sql_check = "SELECT idUsuario FROM usuario WHERE idUsuario = ? AND senhaHASH_Usuario = ?";
    $stmt_check = mysqli_prepare($conexao, $sql_check);
    mysqli_stmt_bind_param($stmt_check, "is", $idUsuario, $senha_atual_hash);
    mysqli_stmt_execute($stmt_check);
    $result_check = mysqli_stmt_get_result($stmt_check);
    
    if (mysqli_num_rows($result_check) === 0) {
        header("Location: ../../public/gerenciadorConfig.php?erro=Senha atual incorreta");
        exit;
    }
    
    // Atualizar senha
    $nova_senha_hash = hash('sha256', $nova_senha);
    $sql_update = "UPDATE usuario SET senhaHASH_Usuario = ? WHERE idUsuario = ?";
    $stmt_update = mysqli_prepare($conexao, $sql_update);
    mysqli_stmt_bind_param($stmt_update, "si", $nova_senha_hash, $idUsuario);
    
    if (mysqli_stmt_execute($stmt_update)) {
        header("Location: ../../public/gerenciadorConfig.php?sucesso=Senha alterada com sucesso!");
    } else {
        header("Location: ../../public/gerenciadorConfig.php?erro=Erro ao alterar senha");
    }
    
    mysqli_stmt_close($stmt_update);
    mysqli_stmt_close($stmt_check);
    exit;
}

header("Location: ../../public/gerenciadorConfig.php");
exit;
?>

