<?php
session_start();

if (!isset($_SESSION['usuario'])) {
    header("Location: login.php?erro=Faça login para acessar");
    exit();
}

$usuario = $_SESSION['usuario'];
$tipoUsuario = $usuario['tipoUsuario'];
$idUsuario = $usuario['idUsuario'];
$nomeUsuario = $usuario['nomeUsuario'];
$emailUsuario = $usuario['emailUsuario'];
$avatarUrl = $usuario['avatar_url'] ?? '';

include_once "../config/connection.php";
$conexao = conectarBD();

// Verificar se a coluna whatsapp existe
$sql_check_whatsapp = "SHOW COLUMNS FROM usuario LIKE 'whatsapp'";
$result_check_whatsapp = mysqli_query($conexao, $sql_check_whatsapp);
if (mysqli_num_rows($result_check_whatsapp) == 0) {
    $sql_add_whatsapp = "ALTER TABLE usuario ADD COLUMN whatsapp VARCHAR(20) NULL AFTER avatar_url";
    mysqli_query($conexao, $sql_add_whatsapp);
}

// Buscar dados adicionais conforme o tipo
$dadosAdicionais = [];
if ($tipoUsuario === 'lojista') {
    // Buscar dados da loja
    $sql_loja = "SELECT l.*, c.nomeCidade, e.siglaEstado 
                 FROM lojas l
                 LEFT JOIN cidade c ON l.cidade_idCidade = c.idCidade
                 LEFT JOIN estado e ON c.estado_idEstado = e.idEstado
                 WHERE l.idlojas = (SELECT loja_id FROM usuario WHERE idUsuario = ?)";
    $stmt = mysqli_prepare($conexao, $sql_loja);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dadosAdicionais = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
} elseif ($tipoUsuario === 'representante') {
    // Buscar WhatsApp
    $sql_whatsapp = "SELECT whatsapp FROM usuario WHERE idUsuario = ?";
    $stmt = mysqli_prepare($conexao, $sql_whatsapp);
    mysqli_stmt_bind_param($stmt, "i", $idUsuario);
    mysqli_stmt_execute($stmt);
    $result = mysqli_stmt_get_result($stmt);
    $dadosAdicionais = mysqli_fetch_assoc($result);
    mysqli_stmt_close($stmt);
}

// Determinar página de retorno
$voltar = "adm.php";
if ($tipoUsuario === 'representante') {
    $voltar = "representante.php";
} elseif ($tipoUsuario === 'lojista') {
    $voltar = "loja.php";
}
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações - System Pump</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="./css/adm.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
    <style>
        .config-container {
            max-width: 800px;
            margin: 0 auto;
        }
        .config-section {
            background: white;
            border-radius: 10px;
            padding: 2rem;
            margin-bottom: 2rem;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .config-section h3 {
            color: var(--primary);
            margin-bottom: 1.5rem;
            padding-bottom: 1rem;
            border-bottom: 2px solid var(--light-gray);
        }
        .avatar-preview {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--primary);
            margin: 1rem auto;
            display: block;
        }
        .avatar-placeholder {
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: var(--light-gray);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 3rem;
            color: var(--gray);
            margin: 1rem auto;
            border: 4px solid var(--primary);
        }
        .form-group {
            margin-bottom: 1.5rem;
        }
        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 600;
            color: var(--dark);
        }
        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid var(--light-gray);
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }
        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(26, 111, 176, 0.1);
        }
        .btn-upload {
            background: var(--primary);
            color: white;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
            display: inline-block;
        }
        .btn-upload:hover {
            background: var(--secondary);
        }
        .file-input-wrapper {
            position: relative;
            display: inline-block;
        }
        .file-input-wrapper input[type="file"] {
            position: absolute;
            opacity: 0;
            width: 100%;
            height: 100%;
            cursor: pointer;
        }
    </style>
</head>
<body>
    <div class="admin-container">
        <!-- Sidebar -->
        <aside class="sidebar">
            <div class="sidebar-header">
                <h2><i class="fas fa-cog"></i> System Pump</h2>
                <p>Configurações</p>
            </div>
            
            <ul class="sidebar-menu">
                <?php if ($tipoUsuario === 'admin'): ?>
                    <li><a href="./adm.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="./gerenciadorInsights.php"><i class="fas fa-chart-line"></i> Insights</a></li>
                    <li><a href="./gerenciadorSolicitacoes.php"><i class="fas fa-inbox"></i> Solicitações</a></li>
                    <li><a href="./gerenciadorMensagens.php"><i class="fas fa-envelope"></i> Mensagens</a></li>
                    <li><a href="./gerenciadorObservacoes.php"><i class="fas fa-eye"></i> Observação</a></li>
                    <li><a href="./gerenciadorPedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                    <li><a href="./gerenciadorProdutos.php"><i class="fas fa-box"></i> Produtos</a></li>
                    <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                    <li><a href="./gerenciadorCidades.php"><i class="fas fa-map-marker-alt"></i> Cidades</a></li>
                    <li><a href="./gerenciadorUsers.php"><i class="fas fa-users"></i> Usuários</a></li>
                <?php elseif ($tipoUsuario === 'representante'): ?>
                    <li><a href="./representante.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="./gerenciadorLojas.php"><i class="fas fa-store"></i> Lojas</a></li>
                    <li><a href="./gerenciadorPedidos.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                    <li><a href="./gerenciadorSolicitacoes.php"><i class="fas fa-inbox"></i> Solicitações</a></li>
                <?php elseif ($tipoUsuario === 'lojista'): ?>
                    <li><a href="./loja.php"><i class="fas fa-home"></i> Home</a></li>
                    <li><a href="./gerenciadorPedidosLojista.php"><i class="fas fa-shopping-cart"></i> Pedidos</a></li>
                    <li><a href="./gerenciadorInsights.php"><i class="fas fa-chart-line"></i> Insights</a></li>
                <?php endif; ?>
                <li><a href="./gerenciadorConfig.php" class="active"><i class="fas fa-cog"></i> Configurações</a></li>
                <li><a href="./logout.php"><i class="fas fa-sign-out-alt"></i> Sair</a></li>
            </ul>
        </aside>

        <main class="main-content">
            <header class="header">
                <div class="header-left">
                    <a href="<?= $voltar ?>" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Voltar
                    </a>
                    <h1 style="margin-left: 1rem;">Configurações</h1>
                </div>
                <div class="header-right">
                    <div class="user-info">
                        <?php if (!empty($avatarUrl)): ?>
                            <img src="<?= htmlspecialchars($avatarUrl) ?>" 
                                alt="Avatar" 
                                class="user-avatar-img" 
                                style="width: 40px; height: 40px; border-radius: 50%; object-fit: cover;"
                                onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                            <div class="user-avatar fallback" style="display: none;"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                        <?php else: ?>
                            <div class="user-avatar"><?= strtoupper(substr($nomeUsuario, 0, 2)) ?></div>
                        <?php endif; ?>
                        <span><?= htmlspecialchars($nomeUsuario) ?></span>
                    </div>
                </div>
            </header>

            <div class="page-content">
                <!-- Mensagens de Sucesso/Erro -->
                <?php if (isset($_GET['sucesso'])): ?>
                    <div class="alert alert-success" style="position: fixed; top: 5rem; right: 2rem; z-index: 10000; padding: 1rem 2rem; background: #28a745; color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['sucesso']) ?>
                    </div>
                    <script>
                        setTimeout(() => {
                            document.querySelector('.alert-success').style.display = 'none';
                        }, 5000);
                    </script>
                <?php endif; ?>
                
                <?php if (isset($_GET['erro'])): ?>
                    <div class="alert alert-error" style="position: fixed; top: 5rem; right: 2rem; z-index: 10000; padding: 1rem 2rem; background: #dc3545; color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
                        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['erro']) ?>
                    </div>
                    <script>
                        setTimeout(() => {
                            document.querySelector('.alert-error').style.display = 'none';
                        }, 5000);
                    </script>
                <?php endif; ?>

                <div class="config-container">
                    <!-- Seção Foto/Avatar -->
                    <div class="config-section">
                        <h3><i class="fas fa-image"></i> Foto de Perfil</h3>
                        <div id="avatar-preview-container">
                            <?php if (!empty($avatarUrl)): ?>
                                <img src="<?= htmlspecialchars($avatarUrl) ?>" alt="Avatar" class="avatar-preview" id="avatar-preview" onerror="this.style.display='none'; document.getElementById('avatar-placeholder').style.display='flex';">
                                <div class="avatar-placeholder" id="avatar-placeholder" style="display: none;">
                                    <?= strtoupper(substr($nomeUsuario, 0, 2)) ?>
                                </div>
                            <?php else: ?>
                                <div class="avatar-placeholder" id="avatar-placeholder">
                                    <?= strtoupper(substr($nomeUsuario, 0, 2)) ?>
                                </div>
                            <?php endif; ?>
                        </div>
                        <form method="POST" action="../src/controllers/configController.php" enctype="multipart/form-data">
                            <input type="hidden" name="acao" value="upload_avatar">
                            <div class="file-input-wrapper">
                                <button type="button" class="btn-upload" onclick="document.getElementById('avatar-file').click()">
                                    <i class="fas fa-upload"></i> Escolher Foto
                                </button>
                                <input type="file" id="avatar-file" name="avatar" accept="image/*" onchange="previewAvatar(this)" style="display: none;">
                            </div>
                            <button type="submit" class="btn-upload" style="margin-top: 1rem;">
                                <i class="fas fa-save"></i> Salvar Foto
                            </button>
                        </form>
                    </div>

                    <!-- Seção Dados Pessoais -->
                    <div class="config-section">
                        <h3><i class="fas fa-user"></i> Dados Pessoais</h3>
                        <form method="POST" action="../src/controllers/configController.php">
                            <input type="hidden" name="acao" value="atualizar_dados">
                            
                            <div class="form-group">
                                <label for="nomeUsuario"><i class="fas fa-user"></i> Nome *</label>
                                <input type="text" id="nomeUsuario" name="nomeUsuario" value="<?= htmlspecialchars($nomeUsuario) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="emailUsuario"><i class="fas fa-envelope"></i> E-mail *</label>
                                <input type="email" id="emailUsuario" name="emailUsuario" value="<?= htmlspecialchars($emailUsuario) ?>" required>
                            </div>

                            <?php if ($tipoUsuario === 'representante'): ?>
                                <div class="form-group">
                                    <label for="whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</label>
                                    <input type="text" id="whatsapp" name="whatsapp" value="<?= htmlspecialchars($dadosAdicionais['whatsapp'] ?? '') ?>" placeholder="(00) 00000-0000">
                                </div>
                            <?php endif; ?>

                            <?php if ($tipoUsuario === 'lojista' && $dadosAdicionais): ?>
                                <div class="form-group">
                                    <label for="cep"><i class="fas fa-map-marker-alt"></i> CEP</label>
                                    <input type="text" id="cep" name="cep" value="<?= htmlspecialchars($dadosAdicionais['cep'] ?? '') ?>" placeholder="00000-000">
                                </div>

                                <div class="form-group">
                                    <label for="logradouro"><i class="fas fa-road"></i> Logradouro</label>
                                    <input type="text" id="logradouro" name="logradouro" value="<?= htmlspecialchars($dadosAdicionais['logradouro'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="numero"><i class="fas fa-hashtag"></i> Número</label>
                                    <input type="text" id="numero" name="numero" value="<?= htmlspecialchars($dadosAdicionais['numero'] ?? '') ?>">
                                </div>

                                <div class="form-group">
                                    <label for="bairro"><i class="fas fa-map"></i> Bairro</label>
                                    <input type="text" id="bairro" name="bairro" value="<?= htmlspecialchars($dadosAdicionais['bairro'] ?? '') ?>">
                                </div>
                            <?php endif; ?>

                            <button type="submit" class="btn-upload">
                                <i class="fas fa-save"></i> Salvar Alterações
                            </button>
                        </form>
                    </div>

                    <!-- Seção Alterar Senha -->
                    <div class="config-section">
                        <h3><i class="fas fa-lock"></i> Alterar Senha</h3>
                        <form method="POST" action="../src/controllers/configController.php">
                            <input type="hidden" name="acao" value="alterar_senha">
                            
                            <div class="form-group">
                                <label for="senha_atual"><i class="fas fa-key"></i> Senha Atual *</label>
                                <input type="password" id="senha_atual" name="senha_atual" required>
                            </div>

                            <div class="form-group">
                                <label for="nova_senha"><i class="fas fa-lock"></i> Nova Senha *</label>
                                <input type="password" id="nova_senha" name="nova_senha" required minlength="6">
                                <small class="text-muted">Mínimo 6 caracteres</small>
                            </div>

                            <div class="form-group">
                                <label for="confirmar_senha"><i class="fas fa-lock"></i> Confirmar Nova Senha *</label>
                                <input type="password" id="confirmar_senha" name="confirmar_senha" required minlength="6">
                            </div>

                            <button type="submit" class="btn-upload">
                                <i class="fas fa-save"></i> Alterar Senha
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            <footer class="footer">
                <p>&copy; 2025 System Pump • Conectando água e tecnologia</p>
            </footer>
        </main>
    </div>

    <script>
        function previewAvatar(input) {
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const preview = document.getElementById('avatar-preview');
                    const placeholder = document.getElementById('avatar-placeholder');
                    
                    if (!preview) {
                        // Criar elemento img se não existir
                        const img = document.createElement('img');
                        img.id = 'avatar-preview';
                        img.className = 'avatar-preview';
                        img.src = e.target.result;
                        img.alt = 'Avatar';
                        img.onerror = function() {
                            this.style.display = 'none';
                            placeholder.style.display = 'flex';
                        };
                        document.getElementById('avatar-preview-container').insertBefore(img, placeholder);
                    } else {
                        preview.src = e.target.result;
                        preview.style.display = 'block';
                    }
                    if (placeholder) placeholder.style.display = 'none';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Máscara de telefone/WhatsApp
        document.getElementById('whatsapp')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 11) {
                if (value.length <= 10) {
                    value = value.replace(/^(\d{2})(\d{4})(\d{0,4}).*/, '($1) $2-$3');
                } else {
                    value = value.replace(/^(\d{2})(\d{5})(\d{0,4}).*/, '($1) $2-$3');
                }
                e.target.value = value;
            }
        });

        // Máscara de CEP
        document.getElementById('cep')?.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            if (value.length <= 8) {
                value = value.replace(/^(\d{5})(\d{0,3}).*/, '$1-$2');
                e.target.value = value;
            }
        });
    </script>
</body>
</html>

<?php
mysqli_close($conexao);
?>

