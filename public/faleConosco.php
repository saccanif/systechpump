<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./css/index.css" />
  <link rel="stylesheet" href="./css/faleConosco.css" />
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />

  <!-- font awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.7.2/css/all.min.css"
    integrity="sha512-Evv84Mr4kqVGRNSgIGL/F/aIDqQb7xQ2vcrdIwxfjThSH8CSR7PBEakCr51Ck+w+/U6swU2Im1vVX0SVk9ABhg=="
    crossorigin="anonymous" referrerpolicy="no-referrer" />

  <!-- fontes -->
  <link rel="preconnect" href="https://fonts.googleapis.com" />
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin />
  <link
    href="https://fonts.googleapis.com/css2?family=Chewy&family=Poppins:ital,wght@0,100;0,200;0,300;0,400;0,500;0,600;0,700;0,800;0,900;1,100;1,200;1,300;1,400;1,500;1,600;1,700;1,800;1,900&display=swap"
    rel="stylesheet" />
  <title>Fale Conosco - Systech Pump</title>
</head>

<body>
  <header class="navbar">
    <div class="container navbar-content">
      <a href="./index.php" class="logo-link">
        <img src="./imgs/others/logo.png" alt="Systech Pump" />
      </a>
      <nav class="navbar-navigation">
        <ul class="navbar-list">
          <li><a href="./index.php#quem-somos">Quem somos</a></li>
          <li><a href="./index.php#produtos">Produtos</a></li>
          <li><a href="./queroVender.php">Quero vender</a></li>
          <li><a href="./faleConosco.php">Fale conosco</a></li>
          <li><a href="./login.php">Entrar</a></li>
        </ul>
      </nav>
    </div>
  </header>

  <main>
    <!-- Mensagens de Sucesso/Erro -->
    <?php if (isset($_GET['sucesso'])): ?>
      <div class="alert alert-success" style="position: fixed; top: 5rem; left: 50%; transform: translateX(-50%); z-index: 10000; padding: 1rem 2rem; background: #28a745; color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fas fa-check-circle"></i> <?= htmlspecialchars($_GET['sucesso']) ?>
      </div>
      <script>
        setTimeout(() => {
          document.querySelector('.alert-success').style.display = 'none';
        }, 5000);
      </script>
    <?php endif; ?>
    
    <?php if (isset($_GET['erro'])): ?>
      <div class="alert alert-error" style="position: fixed; top: 5rem; left: 50%; transform: translateX(-50%); z-index: 10000; padding: 1rem 2rem; background: #dc3545; color: white; border-radius: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.1);">
        <i class="fas fa-exclamation-circle"></i> <?= htmlspecialchars($_GET['erro']) ?>
      </div>
      <script>
        setTimeout(() => {
          document.querySelector('.alert-error').style.display = 'none';
        }, 5000);
      </script>
    <?php endif; ?>

    <!-- Capa -->
    <section class="fale-conosco-hero">
      <div class="fale-conosco-hero-content">
        <h1 class="fale-conosco-titulo">Fale com o Systech Pump</h1>
        <p class="fale-conosco-subtitulo">Entre em contato com o nosso Representante de vendas da sua região ou nos envie uma mensagem via formulário</p>
      </div>
    </section>

    <!-- Seção Representantes -->
    <section id="representantes" class="section-content">
      <div class="container">
        <header>
          <h2 class="titulo">Representantes</h2>
          <h3 class="subtitulo">Encontre o representante da sua região</h3>
        </header>

        <!-- Filtro por Estado -->
        <div class="filtro-estado-container">
          <label for="filtro-estado-representantes">
            <i class="fas fa-filter"></i> Filtrar por Estado:
          </label>
          <select id="filtro-estado-representantes" class="filtro-estado-select">
            <option value="">Todos os estados</option>
            <?php
            if (!isset($conexao)) {
              require_once '../config/connection.php';
              $conexao = conectarBD();
            }
            
            $sql_estados = "SELECT idEstado, nomeEstado, siglaEstado FROM estado ORDER BY nomeEstado";
            $res_estados = mysqli_query($conexao, $sql_estados);
            
            while ($estado = mysqli_fetch_assoc($res_estados)) {
              echo "<option value='{$estado['idEstado']}'>{$estado['nomeEstado']} ({$estado['siglaEstado']})</option>";
            }
            ?>
          </select>
        </div>

        <!-- Lista de Representantes -->
        <div id="representantes-container" class="representantes-grid">
          <?php
          if (!isset($conexao)) {
            require_once '../config/connection.php';
            $conexao = conectarBD();
          }
          
          // Verificar se a coluna whatsapp existe
          $sql_check_whatsapp = "SHOW COLUMNS FROM usuario LIKE 'whatsapp'";
          $result_check_whatsapp = mysqli_query($conexao, $sql_check_whatsapp);
          if (mysqli_num_rows($result_check_whatsapp) == 0) {
              $sql_add_whatsapp = "ALTER TABLE usuario ADD COLUMN whatsapp VARCHAR(20) NULL AFTER avatar_url";
              mysqli_query($conexao, $sql_add_whatsapp);
          }
          
          // Verificar se a coluna estados_fale_conosco existe
          $sql_check_col = "SHOW COLUMNS FROM usuario LIKE 'estados_fale_conosco'";
          $result_check_col = mysqli_query($conexao, $sql_check_col);
          if (mysqli_num_rows($result_check_col) == 0) {
              $sql_add_col = "ALTER TABLE usuario ADD COLUMN estados_fale_conosco TEXT NULL AFTER whatsapp";
              mysqli_query($conexao, $sql_add_col);
          }
          
          // Buscar representantes com seus estados (limite de 12 aleatórios)
          // Usar campo de texto estados_fale_conosco ao invés de buscar pelas cidades
          $sql_representantes = "
            SELECT u.idUsuario, u.nomeUsuario, u.emailUsuario, 
                   COALESCE(u.whatsapp, '') as whatsapp,
                   COALESCE(u.estados_fale_conosco, '') as estados
            FROM usuario u
            WHERE u.tipoUsuario = 'representante'
            ORDER BY RAND()
            LIMIT 12
          ";
          
          $result_representantes = mysqli_query($conexao, $sql_representantes);
          
          if (mysqli_num_rows($result_representantes) > 0) {
            while ($rep = mysqli_fetch_assoc($result_representantes)) {
              $whatsapp = $rep['whatsapp'] ?? '';
              $whatsapp_link = '';
              if (!empty($whatsapp)) {
                // Remover caracteres não numéricos
                $whatsapp_clean = preg_replace('/[^0-9]/', '', $whatsapp);
                $whatsapp_link = "https://wa.me/55{$whatsapp_clean}";
              }
              
              $estados_display = !empty($rep['estados']) ? $rep['estados'] : 'Não informado';
              echo "<div class='representante-card' data-estados='" . htmlspecialchars($estados_display) . "'>";
              echo "<div class='representante-header'>";
              echo "<h3>" . htmlspecialchars($rep['nomeUsuario']) . "</h3>";
              echo "</div>";
              echo "<div class='representante-body'>";
              echo "<p class='representante-estados'><i class='fas fa-map-marker-alt'></i> " . htmlspecialchars($estados_display) . "</p>";
              echo "<p class='representante-email'><i class='fas fa-envelope'></i> " . htmlspecialchars($rep['emailUsuario']) . "</p>";
              if (!empty($whatsapp_link)) {
                echo "<a href='{$whatsapp_link}' target='_blank' class='btn-whatsapp'><i class='fab fa-whatsapp'></i> WhatsApp</a>";
              }
              echo "</div>";
              echo "</div>";
            }
          } else {
            echo "<p class='text-center'>Nenhum representante cadastrado ainda.</p>";
          }
          
          mysqli_close($conexao);
          ?>
        </div>
      </div>
    </section>

    <!-- Seção Formulário -->
    <section id="formulario-contato" class="section-content formulario-section">
      <div class="container">
        <header>
          <h2 class="titulo">Fale Conosco</h2>
        </header>

        <form id="form-fale-conosco" class="form-fale-conosco" method="POST" action="./processarMensagem.php">
          <div class="form-row">
            <div class="form-group">
              <label for="nome"><i class="fas fa-user"></i> Nome *</label>
              <input type="text" id="nome" name="nome" required placeholder="Digite seu nome completo">
            </div>
            <div class="form-group">
              <label for="telefone"><i class="fas fa-phone"></i> Telefone *</label>
              <input type="tel" id="telefone" name="telefone" required placeholder="(00) 00000-0000">
            </div>
          </div>

          <div class="form-row">
            <div class="form-group">
              <label for="email"><i class="fas fa-envelope"></i> E-mail *</label>
              <input type="email" id="email" name="email" required placeholder="seu@email.com">
            </div>
            <div class="form-group">
              <label for="cidade_uf"><i class="fas fa-map-marker-alt"></i> Cidade/UF</label>
              <input type="text" id="cidade_uf" name="cidade_uf" placeholder="Cidade - UF">
            </div>
          </div>

          <div class="form-group">
            <label for="motivo"><i class="fas fa-tag"></i> Indique o motivo do contato: *</label>
            <select id="motivo" name="motivo" required>
              <option value="">Selecione um motivo</option>
              <option value="Solicitação de orçamento">Solicitação de orçamento</option>
              <option value="Dúvidas ou assistência técnica">Dúvidas ou assistência técnica</option>
              <option value="Revenda ou parceria comercial">Revenda ou parceria comercial</option>
              <option value="Trabalhe conosco">Trabalhe conosco</option>
            </select>
          </div>

          <div class="form-group">
            <label for="mensagem"><i class="fas fa-comment"></i> Mensagem *</label>
            <textarea id="mensagem" name="mensagem" rows="5" required maxlength="180" placeholder="Digite sua mensagem (máximo 180 caracteres)"></textarea>
            <small class="contador-caracteres"><span id="contador">0</span> / 180</small>
          </div>

          <button type="submit" class="btn btn-enviar">
            <i class="fas fa-paper-plane"></i> ENVIAR
          </button>
        </form>
      </div>
    </section>
  </main>

  <footer>
    <div class="container footer-content">
      <section class="footer-section">
        <div class="footer-main">
          <a href="./index.php" class="logo-link">
            <img src="./imgs/others/logo.png" alt="Systech Pump" />
          </a>
          <p>CISA INDUSTRIA COMERCIO E REPRESENTAÇÃO DE EQUIPAMENTOS AGRICOLAS LTDA</p>
          <p>CNPJ: 05.920.305/0001-35</p>
        </div>
      </section>
      <section class="footer-section">
        <nav>
          <h4>Systech Pump</h4>
          <ul>
            <li><a href="./index.php#quem-somos">Quem somos</a></li>
            <li><a href="./index.php#produtos">Produtos</a></li>
            <li><a href="./queroVender.php">Quero vender</a></li>
            <li><a href="./faleConosco.php">Fale conosco</a></li>
          </ul>
        </nav>
      </section>

      <section class="footer-section">
        <div>
          <h4>Redes Sociais</h4>
          <ul>
            <li>
              <a target="_blank" href="tel:+552799999999">+55 27 99999999</a>
            </li>
            <li>
              <a target="_blank" href="https://www.instagram.com/systechpump/">Instagram</a>
            </li>
            <li>
              <a target="_blank"
                href="https://www.google.com/maps/place/Bar+e+Restaurante+do+Neg%C3%A3o/@-19.5012677,-40.6605974,17.5z/data=!4m6!3m5!1s0xb707392e3d999f:0xbbbe51be57fd6059!8m2!3d-19.5006671!4d-40.660048!16s%2Fg%2F11f3cbtv7m?entry=ttu&g_ep=EgoyMDI1MDQxNC4xIKXMDSoASAFQAw%3D%3D">Localização
              </a>
            </li>
          </ul>
        </div>
      </section>
      
      <section class="footer-section">
        <nav>
          <h4>Produtos</h4>
          <ul>
            <li><a href="../src/view/linhas/linha-b.html">Linha B</a></li>
            <li><a href="../src/view/linhas/linha-i.html">Linha I</a></li>
            <li><a href="../src/view/linhas/linha-p.html">Linha P</a></li>
            <li><a href="../src/view/linhas/linha-s.html">Linha S</a></li>
          </ul>
        </nav>
      </section>
    </div>
  </footer>

  <script src="./js/index.js"></script>
  <script>
    // Filtro de representantes por estado
    document.getElementById('filtro-estado-representantes').addEventListener('change', function() {
      const estadoSelecionado = this.value;
      const estadoNome = this.options[this.selectedIndex].text.toLowerCase();
      
      if (!estadoSelecionado) {
        // Mostrar apenas os 12 primeiros (padrão)
        location.reload();
        return;
      }
      
      // Buscar todos os representantes do estado selecionado
      fetch(`./buscarRepresentantes.php?estado_id=${estadoSelecionado}`)
        .then(resp => resp.json())
        .then(data => {
          const container = document.getElementById('representantes-container');
          
          if (data.error || !data.representantes || data.representantes.length === 0) {
            container.innerHTML = '<p class="text-center">Nenhum representante encontrado para este estado.</p>';
            return;
          }
          
          let html = '';
          data.representantes.forEach(rep => {
            const whatsapp_link = rep.whatsapp ? `https://wa.me/55${rep.whatsapp.replace(/\D/g, '')}` : '';
            
            html += `<div class="representante-card" data-estados="${rep.estados}">`;
            html += `<div class="representante-header">`;
            html += `<h3>${rep.nomeUsuario}</h3>`;
            html += `</div>`;
            html += `<div class="representante-body">`;
            html += `<p class="representante-estados"><i class="fas fa-map-marker-alt"></i> ${rep.estados}</p>`;
            html += `<p class="representante-email"><i class="fas fa-envelope"></i> ${rep.emailUsuario}</p>`;
            if (whatsapp_link) {
              html += `<a href="${whatsapp_link}" target="_blank" class="btn-whatsapp"><i class="fab fa-whatsapp"></i> WhatsApp</a>`;
            }
            html += `</div>`;
            html += `</div>`;
          });
          
          container.innerHTML = html;
        })
        .catch(error => {
          console.error('Erro ao buscar representantes:', error);
        });
    });

    // Contador de caracteres
    const textarea = document.getElementById('mensagem');
    const contador = document.getElementById('contador');
    
    textarea.addEventListener('input', function() {
      contador.textContent = this.value.length;
    });

    // Máscara de telefone
    document.getElementById('telefone').addEventListener('input', function(e) {
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
  </script>
</body>

</html>

