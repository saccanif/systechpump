<?php
session_start();

// Verificação simples de sessão
if (!isset($_SESSION['usuario'])) {
    header("Location: ../../public/login.php?msg=Faça login para continuar");
    exit;
}

// Verificar se o usuário logado é admin
if ($_SESSION['usuario']['tipoUsuario'] !== 'admin') {
    header("Location: ../../public/login.php?msg=Acesso não autorizado");
    exit;
}

$usuario_logado = $_SESSION['usuario'];

include_once "../../config/connection.php";
$conexao = conectarBD();

// Debug - verificar dados recebidos
error_log("Dados POST recebidos: " . print_r($_POST, true));

// --- CADASTRAR USUÁRIO (TODOS OS TIPOS) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && !isset($_POST['update'])) {
    // Verificar campos obrigatórios
    $camposObrigatorios = ['nomeUsuario', 'emailUsuario', 'senha', 'tipoUsuario', 'idEstado', 'idCidade'];
    $camposFaltantes = [];
    
    foreach ($camposObrigatorios as $campo) {
        if (empty($_POST[$campo])) {
            $camposFaltantes[] = $campo;
        }
    }
    
    if (!empty($camposFaltantes)) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro: Campos obrigatórios faltando: " . implode(', ', $camposFaltantes));
        exit;
    }

    $nomeUsuario  = $_POST["nomeUsuario"];
    $emailUsuario = $_POST["emailUsuario"];
    $senha        = $_POST["senha"];
    $tipoUsuario  = $_POST["tipoUsuario"];
    $avatar_url   = $_POST["avatar_url"] ?? '';
    $idCidade     = intval($_POST["idCidade"]);
    $idEstado     = intval($_POST["idEstado"]);

    $senhaHASH = hash('sha256', $senha);

    // Verificar se email já existe
    $sql_verifica = "SELECT idUsuario FROM usuario WHERE emailUsuario = ?";
    $stmt_verifica = mysqli_prepare($conexao, $sql_verifica);
    mysqli_stmt_bind_param($stmt_verifica, "s", $emailUsuario);
    mysqli_stmt_execute($stmt_verifica);
    $result_verifica = mysqli_stmt_get_result($stmt_verifica);

    if (mysqli_num_rows($result_verifica) > 0) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro: Este email já está cadastrado");
        exit;
    }

    // Inserir usuário (todos os tipos)
    if ($tipoUsuario === 'lojista') {
        // Para lojista, adicionar loja_id (pode ser NULL inicialmente)
        $loja_id = null;
        $sql = "INSERT INTO usuario (
                    nomeUsuario,
                    emailUsuario,
                    senhaHASH_Usuario,
                    tipoUsuario,
                    avatar_url,
                    UsuCriadoEm,
                    cidade_idCidade,
                    idEstado,
                    loja_id
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?, ?)";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssssiii", $nomeUsuario, $emailUsuario, $senhaHASH, $tipoUsuario, $avatar_url, $idCidade, $idEstado, $loja_id);
    } else {
        // Para outros tipos (admin, representante)
        $sql = "INSERT INTO usuario (
                    nomeUsuario,
                    emailUsuario,
                    senhaHASH_Usuario,
                    tipoUsuario,
                    avatar_url,
                    UsuCriadoEm,
                    cidade_idCidade,
                    idEstado
                ) VALUES (?, ?, ?, ?, ?, NOW(), ?, ?)";
        
        $stmt = mysqli_prepare($conexao, $sql);
        mysqli_stmt_bind_param($stmt, "sssssii", $nomeUsuario, $emailUsuario, $senhaHASH, $tipoUsuario, $avatar_url, $idCidade, $idEstado);
    }

    if (mysqli_stmt_execute($stmt)) {
        $cod = mysqli_insert_id($conexao);
        header("Location: ../../public/gerenciadorUsers.php?msg=Usuário $nomeUsuario cadastrado com sucesso! ID: $cod");
    } else {
        $erro = mysqli_error($conexao);
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro ao cadastrar usuário: " . $erro);
    }
    mysqli_stmt_close($stmt);
    exit;
}

// --- EDITAR (UPDATE) ---
if ($_SERVER["REQUEST_METHOD"] === "POST" && isset($_POST['update'])) {
    $idUsuario    = intval($_POST["idUsuario"]);
    $nomeUsuario  = $_POST["nomeUsuario"];
    $emailUsuario = $_POST["emailUsuario"];
    $tipoUsuario  = $_POST["tipoUsuario"];
    $avatar_url   = $_POST["avatar_url"] ?? '';
    $idCidade     = intval($_POST["idCidade"]);

    // Buscar dados atuais do usuário
    $sql_atuais = "SELECT tipoUsuario FROM usuario WHERE idUsuario = ?";
    $stmt_atuais = mysqli_prepare($conexao, $sql_atuais);
    mysqli_stmt_bind_param($stmt_atuais, "i", $idUsuario);
    mysqli_stmt_execute($stmt_atuais);
    $result_atuais = mysqli_stmt_get_result($stmt_atuais);
    
    if (mysqli_num_rows($result_atuais) === 0) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro: Usuário não encontrado");
        exit;
    }

    // Atualizar usuário
    $sql = "UPDATE usuario 
            SET nomeUsuario = ?,
                emailUsuario = ?,
                tipoUsuario = ?,
                avatar_url = ?,
                cidade_idCidade = ?
            WHERE idUsuario = ?";
    
    $stmt = mysqli_prepare($conexao, $sql);
    mysqli_stmt_bind_param($stmt, "ssssii", $nomeUsuario, $emailUsuario, $tipoUsuario, $avatar_url, $idCidade, $idUsuario);

    if (mysqli_stmt_execute($stmt)) {
        // Se o usuário editado for o usuário logado, atualizar os dados na sessão
        if ($usuario_logado['idUsuario'] == $idUsuario) {
            $queryRefresh = "SELECT * FROM usuario WHERE idUsuario = ?";
            $stmtRefresh = mysqli_prepare($conexao, $queryRefresh);
            mysqli_stmt_bind_param($stmtRefresh, "i", $idUsuario);
            mysqli_stmt_execute($stmtRefresh);
            $resultRefresh = mysqli_stmt_get_result($stmtRefresh);
            if ($resultRefresh && $rowRefresh = mysqli_fetch_assoc($resultRefresh)) {
                unset($rowRefresh['senhaHASH_Usuario']);
                $_SESSION['usuario'] = $rowRefresh;
            }
            if (isset($stmtRefresh)) mysqli_stmt_close($stmtRefresh);
        }

        $msg = "Usuário atualizado com sucesso!";
        header("Location: ../../public/gerenciadorUsers.php?msg=" . urlencode($msg));
    } else {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro ao atualizar usuário: " . mysqli_error($conexao));
    }
    
    mysqli_stmt_close($stmt);
    mysqli_stmt_close($stmt_atuais);
    exit;
}

// --- EXCLUIR USUÁRIO ---
if (isset($_GET['delete'])) {
    $id = intval($_GET['delete']);

    // VERIFICAR SE É ADMIN MASTER (PROTEGIDO)
    if ($id == 38) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro: Não é possível excluir o admin master");
        exit;
    }

    $conexao = conectarBD();

    try {
        // 1. VERIFICAR SE O USUÁRIO TEM LOJAS VINCULADAS (como representante)
        $sql_count_lojas = "SELECT COUNT(*) as total_lojas FROM lojas WHERE representante_id = ?";
        $stmt_count = mysqli_prepare($conexao, $sql_count_lojas);
        mysqli_stmt_bind_param($stmt_count, "i", $id);
        mysqli_stmt_execute($stmt_count);
        $result_count = mysqli_stmt_get_result($stmt_count);
        $count_data = mysqli_fetch_assoc($result_count);
        $total_lojas = $count_data['total_lojas'];

        // 2. VERIFICAR SE O USUÁRIO TEM LOJA VINCULADA (como lojista)
        $sql_count_loja_lojista = "SELECT COUNT(*) as total FROM usuario WHERE idUsuario = ? AND loja_id IS NOT NULL";
        $stmt_lojista = mysqli_prepare($conexao, $sql_count_loja_lojista);
        mysqli_stmt_bind_param($stmt_lojista, "i", $id);
        mysqli_stmt_execute($stmt_lojista);
        $result_lojista = mysqli_stmt_get_result($stmt_lojista);
        $lojista_data = mysqli_fetch_assoc($result_lojista);
        $tem_loja_vinculada = $lojista_data['total'] > 0;

        // 3. PEGAR NOME DO USUÁRIO PARA MENSAGEM
        $sql_nome = "SELECT nomeUsuario, tipoUsuario FROM usuario WHERE idUsuario = ?";
        $stmt_nome = mysqli_prepare($conexao, $sql_nome);
        mysqli_stmt_bind_param($stmt_nome, "i", $id);
        mysqli_stmt_execute($stmt_nome);
        $result_nome = mysqli_stmt_get_result($stmt_nome);
        $usuario_data = mysqli_fetch_assoc($result_nome);
        $nome_usuario = $usuario_data['nomeUsuario'];
        $tipo_usuario = $usuario_data['tipoUsuario'];

        if ($total_lojas > 0) {
            // TEM LOJAS COMO REPRESENTANTE → NÃO PODE EXCLUIR
            $msg = "Usuário '$nome_usuario' não pode ser excluído! (Possui $total_lojas loja(s) como representante)";
        } else if ($tem_loja_vinculada && $tipo_usuario === 'lojista') {
            // É LOJISTA COM LOJA VINCULADA → NÃO PODE EXCLUIR
            $msg = "Usuário '$nome_usuario' não pode ser excluído! (É lojista vinculado a uma loja)";
        } else {
            // PODE EXCLUIR
            $sql_excluir = "DELETE FROM usuario WHERE idUsuario = ?";
            $stmt_excluir = mysqli_prepare($conexao, $sql_excluir);
            mysqli_stmt_bind_param($stmt_excluir, "i", $id);
            
            if (!mysqli_stmt_execute($stmt_excluir)) {
                throw new Exception("Erro ao excluir usuário: " . mysqli_stmt_error($stmt_excluir));
            }
            
            $msg = "Usuário '$nome_usuario' excluído com sucesso!";
        }

        header("Location: ../../public/gerenciadorUsers.php?msg=" . urlencode($msg));

    } catch (Exception $e) {
        header("Location: ../../public/gerenciadorUsers.php?msg=Erro: " . urlencode($e->getMessage()));
    } finally {
        if (isset($stmt_count)) mysqli_stmt_close($stmt_count);
        if (isset($stmt_lojista)) mysqli_stmt_close($stmt_lojista);
        if (isset($stmt_nome)) mysqli_stmt_close($stmt_nome);
        if (isset($stmt_excluir)) mysqli_stmt_close($stmt_excluir);
        mysqli_close($conexao);
    }
    exit;
}

// Redirecionar se acesso direto
header("Location: ../../public/gerenciadorUsers.php");
exit;
?>