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
    $senhaLoja = $_POST['senhaLoja'] ?? ''; // Nova: senha para login da loja
    $fotoLoja = $_POST['fotoLoja'] ?? '';
    $cep = $_POST['cep'] ?? '';
    $logradouro = $_POST['logradouro'] ?? '';
    $numero = $_POST['numero'] ?? '';
    $bairro = $_POST['bairro'] ?? '';
    $cidade_idCidade = $_POST['cidade_idCidade'] ?? '';
    $nomeCidade = $_POST['nomeCidade'] ?? ''; // Novo: nome da cidade para criar se não existir
    $estado_idEstado = $_POST['estado_idEstado'] ?? ''; // Novo: estado para criar cidade
    $criarNovaCidade = isset($_POST['criarNovaCidade']) && $_POST['criarNovaCidade'] === '1'; // Flag para criar nova cidade
    
    $usuario = $_SESSION['usuario'];
    $tipoUsuario = $usuario['tipoUsuario'];
    $idUsuario = $usuario['idUsuario'];

    // Validações
    if (empty($nomeLoja)) {
        $_SESSION['erro'] = "Nome da loja é obrigatório";
        header("Location: ../../public/gerenciadorLojas.php");
        exit();
    }

    if (empty($emailLoja)) {
        $_SESSION['erro'] = "E-mail da loja é obrigatório";
        header("Location: ../../public/gerenciadorLojas.php");
        exit();
    }

    if (empty($senhaLoja)) {
        $_SESSION['erro'] = "Senha para login da loja é obrigatória";
        header("Location: ../../public/gerenciadorLojas.php");
        exit();
    }

    // Se está criando nova cidade, validar campos da cidade
    if ($criarNovaCidade) {
        if (empty($nomeCidade) || empty($estado_idEstado)) {
            $_SESSION['erro'] = "Para criar uma nova cidade, é necessário informar o nome da cidade e o estado";
            header("Location: ../../public/gerenciadorLojas.php");
            exit();
        }
    } else {
        // Se não está criando nova cidade, precisa selecionar uma existente
        if (empty($cidade_idCidade)) {
            $_SESSION['erro'] = "Selecione uma cidade existente ou crie uma nova";
            header("Location: ../../public/gerenciadorLojas.php");
            exit();
        }
    }

    $conexao = conectarBD();

    try {
        // Iniciar transação
        mysqli_begin_transaction($conexao);
        
        // Determinar representante_id
        $representante_id = null;
        if ($tipoUsuario === 'representante') {
            $representante_id = $idUsuario;
        } else if ($tipoUsuario === 'admin') {
            // Admin pode ter selecionado um representante ou usar o da cidade
            $representante_id = $_POST['representante_id'] ?? null;
            if (empty($representante_id)) {
                throw new Exception("É necessário selecionar um representante");
            }
        }

        // Se está criando nova cidade
        if ($criarNovaCidade) {
            // Verificar se a cidade já existe no estado
            $sql_verifica_cidade = "SELECT idCidade, representante_id FROM cidade WHERE nomeCidade = ? AND estado_idEstado = ?";
            $stmt_verifica = mysqli_prepare($conexao, $sql_verifica_cidade);
            mysqli_stmt_bind_param($stmt_verifica, "si", $nomeCidade, $estado_idEstado);
            mysqli_stmt_execute($stmt_verifica);
            $result_verifica = mysqli_stmt_get_result($stmt_verifica);
            $cidade_existente = mysqli_fetch_assoc($result_verifica);
            
            if ($cidade_existente) {
                // Cidade já existe
                if (!empty($cidade_existente['representante_id'])) {
                    // Verificar se já tem representante diferente
                    if ($cidade_existente['representante_id'] != $representante_id) {
                        // Buscar nome do representante atual
                        $sql_rep_atual = "SELECT nomeUsuario FROM usuario WHERE idUsuario = ?";
                        $stmt_rep = mysqli_prepare($conexao, $sql_rep_atual);
                        mysqli_stmt_bind_param($stmt_rep, "i", $cidade_existente['representante_id']);
                        mysqli_stmt_execute($stmt_rep);
                        $result_rep = mysqli_stmt_get_result($stmt_rep);
                        $rep_atual = mysqli_fetch_assoc($result_rep);
                        $nome_rep_atual = $rep_atual['nomeUsuario'] ?? 'outro representante';
                        
                        throw new Exception("Esta cidade já existe e está vinculada ao representante: $nome_rep_atual. Uma cidade só pode ter um representante.");
                    }
                    // Mesmo representante, usar cidade existente
                    $cidade_idCidade = $cidade_existente['idCidade'];
                } else {
                    // Cidade existe mas não tem representante, vincular ao representante atual
                    $cidade_idCidade = $cidade_existente['idCidade'];
                    $sql_atualiza_rep = "UPDATE cidade SET representante_id = ? WHERE idCidade = ?";
                    $stmt_atualiza = mysqli_prepare($conexao, $sql_atualiza_rep);
                    mysqli_stmt_bind_param($stmt_atualiza, "ii", $representante_id, $cidade_idCidade);
                    mysqli_stmt_execute($stmt_atualiza);
                }
            } else {
                // Criar nova cidade
                $sql_criar_cidade = "INSERT INTO cidade (nomeCidade, estado_idEstado, representante_id, qtdaAcesso) VALUES (?, ?, ?, 0)";
                $stmt_criar = mysqli_prepare($conexao, $sql_criar_cidade);
                mysqli_stmt_bind_param($stmt_criar, "sii", $nomeCidade, $estado_idEstado, $representante_id);
                
                if (!mysqli_stmt_execute($stmt_criar)) {
                    throw new Exception("Erro ao criar cidade: " . mysqli_error($conexao));
                }
                
                $cidade_idCidade = mysqli_insert_id($conexao);
                error_log("✅ Cidade criada automaticamente - ID: $cidade_idCidade, Nome: $nomeCidade");
            }
        } else {
            // Usando cidade existente - verificar se tem representante e se é o correto
            $sql_dono = "SELECT representante_id, estado_idEstado FROM cidade WHERE idCidade = ?";
            $stmt_dono = mysqli_prepare($conexao, $sql_dono);
            mysqli_stmt_bind_param($stmt_dono, "i", $cidade_idCidade);
            mysqli_stmt_execute($stmt_dono);
            $res_dono = mysqli_stmt_get_result($stmt_dono);
            $dados_cidade = mysqli_fetch_assoc($res_dono);

            if (!$dados_cidade) {
                throw new Exception("Cidade não encontrada");
            }

            // Se a cidade já tem representante
            if (!empty($dados_cidade['representante_id'])) {
                // Verificar se é o mesmo representante (ou admin pode usar qualquer cidade)
                if ($tipoUsuario === 'representante' && $dados_cidade['representante_id'] != $representante_id) {
                    // Buscar nome do representante da cidade
                    $sql_rep_cidade = "SELECT nomeUsuario FROM usuario WHERE idUsuario = ?";
                    $stmt_rep_cid = mysqli_prepare($conexao, $sql_rep_cidade);
                    mysqli_stmt_bind_param($stmt_rep_cid, "i", $dados_cidade['representante_id']);
                    mysqli_stmt_execute($stmt_rep_cid);
                    $result_rep_cid = mysqli_stmt_get_result($stmt_rep_cid);
                    $rep_cidade = mysqli_fetch_assoc($result_rep_cid);
                    $nome_rep_cidade = $rep_cidade['nomeUsuario'] ?? 'outro representante';
                    
                    throw new Exception("Esta cidade já está vinculada ao representante: $nome_rep_cidade. Uma cidade só pode ter um representante.");
                }
                $representante_id = $dados_cidade['representante_id'];
            } else {
                // Cidade sem representante, vincular ao representante atual
                $sql_atualiza_rep = "UPDATE cidade SET representante_id = ? WHERE idCidade = ?";
                $stmt_atualiza = mysqli_prepare($conexao, $sql_atualiza_rep);
                mysqli_stmt_bind_param($stmt_atualiza, "ii", $representante_id, $cidade_idCidade);
                mysqli_stmt_execute($stmt_atualiza);
            }
        }

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
        $email_lojista = $emailLoja;
        $senha_hash = hash('sha256', $senhaLoja); // Usar a senha fornecida pelo representante
        
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
        error_log("✅ Usuário lojista criado - ID: $idUsuarioLojista, Email: $email_lojista");
        
        // Limpar sessão de solicitação aceita após criar loja
        if (isset($_SESSION['solicitacao_aceita'])) {
            unset($_SESSION['solicitacao_aceita']);
        }

        // Commit da transação
        mysqli_commit($conexao);

        $_SESSION['sucesso'] = "Loja cadastrada com sucesso! Usuário lojista criado automaticamente (Email: $email_lojista)";

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

        // 2. Excluir pedidos relacionados à loja (primeiro os itens, depois os pedidos)
        // Buscar IDs dos pedidos
        $sql_pedidos = "SELECT idPedidos FROM pedidos WHERE lojas_idlojas = ?";
        $stmt_pedidos = mysqli_prepare($conexao, $sql_pedidos);
        mysqli_stmt_bind_param($stmt_pedidos, "i", $idlojas);
        mysqli_stmt_execute($stmt_pedidos);
        $result_pedidos = mysqli_stmt_get_result($stmt_pedidos);
        
        while ($pedido = mysqli_fetch_assoc($result_pedidos)) {
            $idPedido = $pedido['idPedidos'];
            
            // Excluir itens do pedido
            $sql_delete_itens = "DELETE FROM Pedido_Itens WHERE pedidos_idPedidos = ?";
            $stmt_itens = mysqli_prepare($conexao, $sql_delete_itens);
            mysqli_stmt_bind_param($stmt_itens, "i", $idPedido);
            mysqli_stmt_execute($stmt_itens);
            mysqli_stmt_close($stmt_itens);
        }
        mysqli_stmt_close($stmt_pedidos);
        
        // Excluir pedidos
        $sql_delete_pedidos = "DELETE FROM pedidos WHERE lojas_idlojas = ?";
        $stmt_delete_pedidos = mysqli_prepare($conexao, $sql_delete_pedidos);
        mysqli_stmt_bind_param($stmt_delete_pedidos, "i", $idlojas);
        mysqli_stmt_execute($stmt_delete_pedidos);
        mysqli_stmt_close($stmt_delete_pedidos);

        // 3. Excluir a loja
        $sql_excluir_loja = "DELETE FROM lojas WHERE idlojas = ?";
        $stmt_excluir_loja = mysqli_prepare($conexao, $sql_excluir_loja);
        mysqli_stmt_bind_param($stmt_excluir_loja, "i", $idlojas);

        if (!mysqli_stmt_execute($stmt_excluir_loja)) {
            throw new Exception("Erro ao excluir loja: " . mysqli_error($conexao));
        }

        // 4. Excluir o usuário lojista se existir
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