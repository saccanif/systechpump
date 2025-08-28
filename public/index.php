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
          <li><a href="../src/view/vender1.html">Quero vender</a></li>
          <li><a href="#fale-conosco">Fale conosco</a></li>
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

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-t16.png" />
                  <figcaption>S-T16</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-t8x.png" />
                  <figcaption>S-T8X</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r1e.png" />
                  <figcaption>S-R1E</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r1l.png" />
                  <figcaption>S-R1L</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/s-r8xl.png" />
                  <figcaption>S-R8XL</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-s.html" class="btn btn-border">Saiba Mais</a>
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

                <a href="../src/view/linhas/linha-b.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/b-r1.png" />
                  <figcaption>B-R1</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-b.html" class="btn btn-border">Saiba Mais</a>
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

                <a href="../src/view/linhas/linha-i.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/i-rv16.png" />
                  <figcaption>I-RV16</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-i.html" class="btn btn-border">Saiba Mais</a>
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

                <a href="../src/view/linhas/linha-p.html" class="btn btn-border">Saiba Mais</a>
              </li>
              <li class="produto animate-on-view">
                <figure>
                  <img src="./imgs/produtos/p-r1.png" />
                  <figcaption>P-R1</figcaption>
                </figure>

                <a href="../src/view/linhas/linha-p.html" class="btn btn-border">Saiba Mais</a>
              </li>
            </ul>
          </section>
        </div>
      </div>
    </section>

    <section id="numeros">
      <div class="container section-content">
        <div class="numeros-content">
          <ul class="numeros-lista">
            <li class="numero">
              <div class="icon">
                <i class="fa-solid fa-boxes-stacked fa-3x"></i>
              </div>
              <div class="texto">
                <p>12</p>
                <span>produtos</span>
              </div>
            </li>
            <li class="numero">
              <div class="icon">
                <i class="fa-solid fa-shop fa-3x"></i>
              </div>
              <div class="texto">
                <p>+125</p>
                <span>lojas</span>
              </div>
            </li>
            <li class="numero">
              <div class="icon">
                <i class="fa-solid fa-city fa-3x"></i>
              </div>
              <div class="texto">
                <p>+50</p>
                <span>cidades</span>
              </div>
            </li>
          </ul>

          <div class="mapa">
            <img src="./imgs/others/brasil.png" />
          </div>
        </div>
      </div>
    </section>


    <section id="fim-dos-fios">
      <div class="container section-content">
        <header>
          <div class="fdf-titulo">
            <i class="fa-solid fa-wifi fa-rotate-90 fa-3x"></i>
            <h2 class="titulo">Tecnologia</h2>
            <i class="fa-solid fa-wifi fa-rotate-270 fa-3x"></i>
          </div>
          <div class="fdf-subtitulo">
            <h3 class="subtitulo">O fim dos fios</h3>
            <img src="./imgs/others/cabo-cortado.png" alt="cabo cortado" />
          </div>
        </header>

        <div class="fdf-content">
          <ul class="fdf-beneficios">
            <li class="animate-on-view">
              <p>Systech Pump</p>
              <ul>
                <li>
                  <i class="fa-solid fa-circle-check fa-xl"></i>Acionamento de válvulas solenoides
                </li>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Baixo custo</li>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Sustentável</li>
              </ul>
            </li>
            <li class="animate-on-view">
              <p>Systech Pump</p>
              <ul>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Acionamento de bombas</li>
                <li>
                  <i class="fa-solid fa-circle-check fa-xl"></i>Inversão de frequência de bombas
                </li>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Tecnologia</li>
              </ul>
            </li>
            <li class="animate-on-view">
              <p>Systech Pump</p>
              <ul>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Plantação de café</li>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Acionamento de bombas</li>
                <li><i class="fa-solid fa-circle-check fa-xl"></i>Acionamento de bombas</li>
              </ul>
            </li>
          </ul>
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
          <form id="encontrar-loja-form" method="POST" action="./index.php">
            <!-- <label>
              Estado: <br />
              <select name="estado">

                <option value="" disabled selected>Escolha um estado</option> -->
                <!-- conectando o banco e montando o combobox -->
                <?php
                // require_once '../config/connection.php';

                // $sql = "select * from estado";
                // $res = mysqli_query(conectarBD(), $sql);

                // while ($registro = mysqli_fetch_assoc($res)) {
                //   $estadoLoja = $registro["nomeEstado"];
                //   $siglaLoja = $registro["siglaEstado"];

                //   echo "<OPTION value = '$siglaLoja'>$estadoLoja</OPTION>";
                // }
                ?>

              <!-- </select>
            </label> -->

            <!-- <label>
              Cidade: <br />
              <select name="cidade">
                <option value="" disabled selected>Escolha uma cidade</option> -->
                <!-- conectando o banco e montando o combobox -->
                <?php
                // require_once '../config/connection.php';

                // $sql = "select * from cidade";
                // $res = mysqli_query(conectarBD(), $sql);

                // while ($registro = mysqli_fetch_assoc($res)) {
                //   $cidadeLoja = $registro["nomeCidade"];
                //   $idEstado = $registro["idCidade"];

                //   echo "<OPTION value = '$idEstado'>$cidadeLoja</OPTION>";
                // }
                ?>
              <!-- </select>
            </label> -->
<!-- 
            <button class="btn">Buscar lojas</button> -->
          </form>
          <div class="loja-container">
            <?php
              require_once '../config/connection.php';

              $sql = "SELECT * FROM lojas";
              $res = mysqli_query(conectarBD(), $sql);

              echo "<div class='loja-container'>";

              while ($registro = mysqli_fetch_assoc($res)) {
                $nomeLoja = $registro["nomeLoja"];
                $telefone = $registro["telefoneLoja"];
                $cidade = $registro["cidade_idCidade"];
                $img = base64_encode($registro["fotoLoja"]);

                echo "
                  <div class='loja-item'>
                      <img src='data:image/jpeg;base64,$img' alt='Foto da loja' />
                      <p><b>$nomeLoja</b></p>
                      <p><b>Telefone:</b> $telefone</p>
                      <p><b>Endereço:</b> $cidade</p>
                    </div>
                  ";
                }

              echo "</div>";
            ?>

          </div>
            

        </div>
      </div>
      </div>
    </section>
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
            <li><a href="../src/view/vender1.html">Quero vender</a></li>
            <li><a href="#fale-conosco">Fale conosco</a></li>
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
