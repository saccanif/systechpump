<!DOCTYPE html>
<html lang="pt-BR">

<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <link rel="stylesheet" href="./css/index.css" />
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />

  <!-- leaflet -->
  <link rel="stylesheet" href="https://unpkg.com/leaflet@1.9.4/dist/leaflet.css" />
  <script src="https://unpkg.com/leaflet@1.9.4/dist/leaflet.js"></script>

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
  <title>Systech Pump</title>
</head>

<body>
  <header class="navbar">
    <div class="container navbar-content">
      <a href="/" class="logo-link">
        <img src="./imgs/others/logo.png" alt="Systech Pump" />
      </a>
      <nav class="navbar-navigation">
        <ul class="navbar-list">
          <li><a href="#quem-somos">Quem somos</a></li>
          <li><a href="#produtos">Produtos</a></li>
          <li><a href="./queroVender.php">Quero vender</a></li>
          <li><a href="./faleConosco.php">Fale conosco</a></li>
          <li><a href="./login.php">Entrar</a></li>
        </ul>
      </nav>
    </div>
  </header>
  <main>
    <section class="hero">
      <div class="container hero-content">
        <div class="hero-text">
          <span class="animate-on-view">💦 Fazendo diferença desde 2024</span>
          <h1 class="animate-on-view systech-pump">Systech Pump</h1>
          <p class="animate-on-view">
            Controle seus setores de irrigação sem fios, sem microtubos e com tecnologia blindada
            contra raios e falhas. A solução definitiva para o seu agro!
          </p>
        </div>
        <a href="#quem-somos" class="btn animate-on-view">Saiba mais</a>
      </div>
    </section>

    <section id="quem-somos">
      <div class="container section-content">
        <header>
          <h2 class="titulo">Somos a Systech Pump</h2>
        </header>

        <div class="video">
          <div class="fluid-video-wrapper">
            <iframe width="560" height="315" src="https://www.youtube.com/embed/jUuqBZwwkQw?si=49ii6wYRh0BTwuHq"
              title="YouTube video player" frameborder="0"
              allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share"
              referrerpolicy="strict-origin-when-cross-origin" allowfullscreen></iframe>
          </div>
        </div>
      </div>
    </section>

    <section id="tecnologia">
      <div class="container section-content">
        <header>
          <h2 class="titulo">Revolucione sua plantação com tecnologia de ponta</h2>
        </header>

        <div class="tecnologia-content">
          <div class="tecnologia-diagrama animate-on-view">
            <img src="./imgs/others/tecnologia-diagrama.png" alt="diagrama tecnologia sem fio" />
          </div>

          <div class="tecnologia-texto">
            <p class="animate-on-view">
              <strong>Tecnologia via rádio</strong> para acionamento remoto de válvulas
              solenoides, sem uso de cabos ou instalações complicadas.
            </p>

            <p class="animate-on-view">
              Pensado para <strong>plantações de café</strong> e outras culturas, o sistema
              oferece controle total da irrigação à distância, com praticidade mesmo nos pontos
              mais distantes da fazenda.
            </p>

            <ul class="tecnologia-beneficios animate-on-view">
              <li id="distancia">
                <i class="fa-solid fa-satellite-dish fa-3x"></i>
                <p>Controle à distância</p>
              </li>
              <li id="irrigacao">
                <i class="fa-solid fa-droplet fa-3x"></i>
                <p>Irrigação sem fios</p>
              </li>
              <li id="autonomia">
                <i class="fa-solid fa-screwdriver-wrench fa-3x"></i>
                <p><span>+ autonomia</span> <span>- manutenção</span></p>
              </li>
            </ul>

            <p class="texto-destaque animate-on-view">
              <em>
                Modernize sua lavoura com inteligência. Controle sua irrigação de forma simples,
                segura e eficiente.
              </em>
            </p>
          </div>
        </div>
      </div>
    </section>

    <section id="produtos">
      <div class="container section-content">
        <header>
          <h2 class="titulo">Nossos Produtos</h2>
        </header>

        <div class="linhas">
          <!-- LINHA S -->
          <section class="linha" style="--col-span: 3">
            <div class="linha-titulo">
              <h3 class="titulo-2">Linha S</h3>
              <span>Acionamento de válvula solenoide</span>
            </div>

            <ul class="produtos-lista">
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-t8.png" />
                  <figcaption>S-T8</figcaption>
                </figure>

                <a href="./produto.php?produto=s-t8" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-t16.png" />
                  <figcaption>S-T16</figcaption>
                </figure>

                <a href="./produto.php?produto=s-t16" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-t8x.png" />
                  <figcaption>S-T8X</figcaption>
                </figure>

                <a href="./produto.php?produto=s-t8x" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r1e.png" />
                  <figcaption>S-R1E</figcaption>
                </figure>

                <a href="./produto.php?produto=s-r1e" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r1l.png" />
                  <figcaption>S-R1L</figcaption>
                </figure>

                <a href="./produto.php?produto=s-r1l" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r8xl.png" />
                  <figcaption>S-R8XL</figcaption>
                </figure>

                <a href="./produto.php?produto=s-r8xl" class="btn btn-border">Saiba Mais</a>
              </li>
            </ul>
          </section>

          <!-- LINHA B -->
          <section class="linha" style="--col-span: 1">
            <div class="linha-titulo">
              <h3 class="titulo-2">Linha B</h3>
              <span>Acionamento de bombas de irrigação </span>
            </div>

            <ul class="produtos-lista">
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/b-t1.png" />
                  <figcaption>B-T1</figcaption>
                </figure>

                <a href="./produto.php?produto=b-t1" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/b-r1.png" />
                  <figcaption>B-R1</figcaption>
                </figure>

                <a href="./produto.php?produto=b-r1" class="btn btn-border">Saiba Mais</a>
              </li>
            </ul>
          </section>

          <!-- LINHA I -->
          <section class="linha" style="--col-span: 1">
            <div class="linha-titulo">
              <h3 class="titulo-2">Linha I</h3>
              <span>Acionamento e controle de inversores de frequência de bombas de irrigação</span>
            </div>

            <ul class="produtos-lista">
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/i-tv16.png" />
                  <figcaption>I-TV16</figcaption>
                </figure>

                <a href="./produto.php?produto=i-tv16" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/i-rv16.png" />
                  <figcaption>I-RV16</figcaption>
                </figure>

                <a href="./produto.php?produto=i-rv16" class="btn btn-border">Saiba Mais</a>
              </li>
            </ul>
          </section>

          <!-- LINHA P -->
          <section class="linha" style="--col-span: 1">
            <div class="linha-titulo">
              <h3 class="titulo-2">Linha P</h3>
              <span>Produtos para o acionamento de bombas de irrigação </span>
            </div>

            <ul class="produtos-lista">
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/p-t1.png" />
                  <figcaption>P-T1</figcaption>
                </figure>

                <a href="./produto.php?produto=p-t1" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/p-r1.png" />
                  <figcaption>P-R1</figcaption>
                </figure>

                <a href="./produto.php?produto=p-r1" class="btn btn-border">Saiba Mais</a>
              </li>
            </ul>
          </section>
        </div>
      </div>
    </section>

    <section id="numeros">
      <div class="container section-content">
        <header style="text-align: center; margin-bottom: 3rem;">
          <h2 class="titulo">Nossos Números</h2>
          <h3 class="subtitulo" style="margin-top: 1rem;">Crescendo junto com você</h3>
        </header>
        <div class="numeros-content">
          <ul class="numeros-lista">
            <li class="numero animate-on-view">
              <div class="numero-icon">
                <i class="fa-solid fa-boxes-stacked"></i>
              </div>
              <div class="numero-texto">
                <p class="numero-valor">12</p>
                <span class="numero-label">Produtos</span>
              </div>
            </li>
            <li class="numero animate-on-view">
              <div class="numero-icon">
                <i class="fa-solid fa-shop"></i>
              </div>
              <div class="numero-texto">
                <p class="numero-valor">+125</p>
                <span class="numero-label">Lojas</span>
              </div>
            </li>
            <li class="numero animate-on-view">
              <div class="numero-icon">
                <i class="fa-solid fa-city"></i>
              </div>
              <div class="numero-texto">
                <p class="numero-valor">+50</p>
                <span class="numero-label">Cidades</span>
              </div>
            </li>
            <li class="numero animate-on-view">
              <div class="numero-icon">
                <i class="fa-solid fa-seedling"></i>
              </div>
              <div class="numero-texto">
                <p class="numero-valor">100%</p>
                <span class="numero-label">Satisfação</span>
              </div>
            </li>
          </ul>
        </div>
      </div>
    </section>


    <section id="fim-dos-fios">
      <div class="container section-content">
        <header>
          <div class="fdf-titulo">
            <i class="fa-solid fa-wifi fa-rotate-90"></i>
            <h2 class="titulo" style="color: white;">Tecnologia</h2>
            <i class="fa-solid fa-wifi fa-rotate-270"></i>
          </div>
          <div class="fdf-subtitulo">
            <h3 class="subtitulo" style="color: white; font-size: 2rem;">O fim dos fios</h3>
            <p style="color: white; font-size: 1.2rem; margin-top: 1rem; opacity: 0.9;">
              Revolucionando a irrigação com tecnologia sem fio
            </p>
          </div>
        </header>

        <div class="fdf-content">
          <div class="fdf-beneficios-grid">
            <div class="fdf-card animate-on-view">
              <div class="fdf-card-icon">
                <i class="fa-solid fa-satellite-dish"></i>
              </div>
              <h3>Acionamento Remoto</h3>
              <p>Controle suas válvulas solenoides e bombas de irrigação via rádio, sem necessidade de fios ou microtubos.</p>
              <ul>
                <li><i class="fa-solid fa-check"></i> Comunicação sem fio</li>
                <li><i class="fa-solid fa-check"></i> Alcance ampliado</li>
                <li><i class="fa-solid fa-check"></i> Sinal estável</li>
              </ul>
            </div>
            <div class="fdf-card animate-on-view">
              <div class="fdf-card-icon">
                <i class="fa-solid fa-shield-halved"></i>
              </div>
              <h3>Proteção Avançada</h3>
              <p>Tecnologia blindada contra raios e falhas, garantindo máxima confiabilidade em qualquer condição climática.</p>
              <ul>
                <li><i class="fa-solid fa-check"></i> Proteção contra raios</li>
                <li><i class="fa-solid fa-check"></i> Resistente a intempéries</li>
                <li><i class="fa-solid fa-check"></i> Alta durabilidade</li>
              </ul>
            </div>
            <div class="fdf-card animate-on-view">
              <div class="fdf-card-icon">
                <i class="fa-solid fa-coffee"></i>
              </div>
              <h3>Ideal para Café</h3>
              <p>Solução perfeita para culturas de café, melão e outras culturas que exigem irrigação precisa e eficiente.</p>
              <ul>
                <li><i class="fa-solid fa-check"></i> Otimizado para café</li>
                <li><i class="fa-solid fa-check"></i> Economia de água</li>
                <li><i class="fa-solid fa-check"></i> Sustentável</li>
              </ul>
            </div>
          </div>
        </div>
      </div>
    </section>
    <section id="encontrar-loja">
      <div class="container section-content">
        <header>
          <h2 class="titulo">Nossas lojas</h2>
          <h3>Encontre a loja mais próxima com produtos Pump</h3>
        </header>

        <div class="encontrar-loja-content">
          <!-- LADO DIREITO: Mapa Estático do Brasil -->
          <div class="loja-right-panel">
            <img src="./imgs/others/brasil.png" alt="Brasil">
          </div>

          <!-- LADO ESQUERDO: Formulário e Resultados -->
          <div class="loja-left-panel">
            <!-- FORMULÁRIO DE BUSCA -->
            <form id="encontrar-loja-form">
              <div class="form-group">
                <label for="estadoSelect">
                  <i class="fas fa-map"></i> Estado:
                </label>
                <select name="estado" id="estadoSelect" required>
                  <option value="">Escolha um estado</option>
                  <?php
                  require_once '../config/connection.php';
                  $con = conectarBD();
                  
                  $sql = "SELECT * FROM estado ORDER BY nomeEstado";
                  $res = mysqli_query($con, $sql);

                  while ($reg = mysqli_fetch_assoc($res)) {
                    echo "<option value='{$reg['idEstado']}'>{$reg['nomeEstado']}</option>";
                  }
                  mysqli_close($con);
                  ?>
                </select>
              </div>

              <div class="form-group">
                <label for="cidadeSelect">
                  <i class="fas fa-city"></i> Cidade:
                </label>
                <select name="cidade" id="cidadeSelect" required disabled>
                  <option value="">Escolha uma cidade</option>
                </select>
                <input type="text" id="cidadeManual" name="cidadeManual" 
                       placeholder="Digite o nome da cidade" 
                       style="display: none; margin-top: 0.5rem; width: 100%; padding: 0.75rem; border: 2px solid #e0e0e0; border-radius: 8px;">
              </div>

              <button type="submit" class="btn btn-buscar">
                <i class="fas fa-search"></i> Buscar lojas
              </button>
            </form>

            <!-- LISTA DE LOJAS -->
            <div class="loja-container" id="lojaContainer">
              <div class="loja-placeholder">
                <i class="fas fa-store fa-3x"></i>
                <p>Selecione seu estado e cidade para encontrar lojas próximas</p>
              </div>
            </div>
          </div>
        </div>
      </div>
    </section>

    <script>
      // Sistema de contagem de acessos removido - será feito via PHP

      // Carregar cidades automaticamente quando o usuário escolhe o estado
      document.getElementById('estadoSelect').addEventListener('change', function() {
        const idEstado = this.value;
        const cidadeSelect = document.getElementById('cidadeSelect');
        const cidadeManual = document.getElementById('cidadeManual');
        
        if (!idEstado) {
          cidadeSelect.innerHTML = '<option value="">Escolha uma cidade</option>';
          cidadeSelect.disabled = true;
          cidadeManual.style.display = 'none';
          cidadeManual.value = '';
          return;
        }

        cidadeSelect.disabled = true;
        cidadeSelect.innerHTML = '<option value="">Carregando...</option>';
        cidadeManual.style.display = 'none';
        cidadeManual.value = '';

        fetch(`./get_cidades.php?estado=${idEstado}`)
          .then(resp => resp.text())
          .then(html => {
            cidadeSelect.innerHTML = html;
            cidadeSelect.disabled = false;
          })
          .catch(error => {
            console.error('Erro ao carregar cidades:', error);
            cidadeSelect.innerHTML = '<option value="">Erro ao carregar cidades</option>';
          });
      });

      // Mostrar/ocultar campo manual quando selecionar "Outra cidade"
      document.getElementById('cidadeSelect').addEventListener('change', function() {
        const cidadeManual = document.getElementById('cidadeManual');
        if (this.value === 'outra') {
          cidadeManual.style.display = 'block';
          cidadeManual.required = true;
        } else {
          cidadeManual.style.display = 'none';
          cidadeManual.required = false;
          cidadeManual.value = '';
        }
      });

      // Buscar lojas quando o formulário for enviado
      document.getElementById('encontrar-loja-form').addEventListener('submit', async function(e) {
        e.preventDefault();
        
        const cidadeId = document.getElementById('cidadeSelect').value;
        const cidadeManual = document.getElementById('cidadeManual').value;
        const estadoId = document.getElementById('estadoSelect').value;
        
        if (!cidadeId || (cidadeId === 'outra' && !cidadeManual.trim())) {
          alert('Por favor, selecione ou digite uma cidade');
          return;
        }

        const container = document.getElementById('lojaContainer');
        container.innerHTML = '<div class="loading"><i class="fas fa-spinner fa-spin"></i> Buscando lojas...</div>';

        try {
          let response;
          let data;
          
          // Se for cidade manual (não está no banco)
          if (cidadeId === 'outra') {
            // Buscar com nome da cidade manual - retornará vazio e mostrará sede
            response = await fetch(`./buscarLojas.php?cidade_nome=${encodeURIComponent(cidadeManual)}&estado_id=${estadoId}`);
            data = await response.json();
          } else {
            // Buscar lojas na cidade do banco
            response = await fetch(`./buscarLojas.php?cidade_id=${cidadeId}`);
            data = await response.json();
          }

          if (data.error) {
            container.innerHTML = `<div class="loja-error"><i class="fas fa-exclamation-triangle"></i> ${data.error}</div>`;
            return;
          }

          // Registrar acesso à cidade (apenas se cidade estiver no banco)
          if (cidadeId && cidadeId !== 'outra') {
            fetch(`./registrarAcesso.php?cidade_id=${cidadeId}`)
              .catch(err => console.error('Erro ao registrar acesso:', err));
          }

          // Se não encontrou lojas e cidade está no banco, buscar em raio de 40km
          if (!data.tem_lojas && cidadeId && cidadeId !== 'outra') {
            const responseRaio = await fetch(`./buscarLojas.php?cidade_id=${cidadeId}&raio=1`);
            data = await responseRaio.json();
          }

          // Exibir resultados
          if (data.tem_lojas && data.lojas.length > 0) {
            let html = '';
            
            // Adicionar mensagem se for busca em raio
            if (data.busca_raio) {
              html += `<div class="loja-aviso"><i class="fas fa-info-circle"></i> Não encontramos lojas na sua cidade. Mostrando lojas próximas em um raio de 40km:</div>`;
            }
            
            // Processar lojas
            data.lojas.forEach((loja) => {
              const enderecoCompleto = `${loja.logradouro || ''} ${loja.numero || ''}, ${loja.bairro || ''} - ${loja.nomeCidade}/${loja.siglaEstado}`.trim();
              const distancia = loja.distancia ? ` <span class="loja-distancia">(${loja.distancia} km)</span>` : '';
              
              html += `
                <div class="loja-item">
                  ${loja.fotoLoja ? `<img src="${loja.fotoLoja}" alt="${loja.nomeLoja}" onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">` : ''}
                  ${!loja.fotoLoja ? '<div class="loja-icon"><i class="fas fa-store"></i></div>' : '<div class="loja-icon" style="display:none;"><i class="fas fa-store"></i></div>'}
                  <div class="loja-info">
                    <h4>${loja.nomeLoja}${distancia}</h4>
                    ${enderecoCompleto ? `<p><i class="fas fa-map-marker-alt"></i> ${enderecoCompleto}</p>` : `<p><i class="fas fa-map-marker-alt"></i> ${loja.nomeCidade}/${loja.siglaEstado}</p>`}
                    ${loja.telefoneLoja ? `<p><i class="fas fa-phone"></i> ${loja.telefoneLoja}</p>` : ''}
                    ${loja.emailLoja ? `<p><i class="fas fa-envelope"></i> ${loja.emailLoja}</p>` : ''}
                  </div>
                </div>
              `;
            });
            
            container.innerHTML = html;
          } else {
            // Não encontrou lojas - mostrar card da sede
            const sede = data.sede;
            container.innerHTML = `
              <div class="loja-sede">
                <div class="sede-header">
                  <i class="fas fa-building"></i>
                  <h3>Nenhuma loja próxima encontrada</h3>
                </div>
                <div class="sede-info">
                  <p>Entre em contato direto com a sede:</p>
                  <div class="sede-details">
                    <h4>${sede.nome}</h4>
                    <p><i class="fas fa-map-marker-alt"></i> ${sede.endereco}</p>
                    <p><i class="fas fa-city"></i> ${sede.cidade} - ${sede.estado}</p>
                    <p><i class="fas fa-phone"></i> ${sede.telefone}</p>
                    <p><i class="fas fa-envelope"></i> ${sede.email}</p>
                  </div>
                </div>
              </div>
            `;

          }
        } catch (error) {
          console.error('Erro ao buscar lojas:', error);
          container.innerHTML = '<div class="loja-error"><i class="fas fa-exclamation-triangle"></i> Erro ao buscar lojas. Tente novamente.</div>';
        }
      });
    </script>



  </main>

  <footer>
    <div class="container footer-content">
      <section class="footer-section">
        <div class="footer-main">
          <a href="/" class="logo-link">
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
            <li><a href="#quem-somos">Quem somos</a></li>
            <li><a href="#produtos">Produtos</a></li>
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
</body>

</html>
