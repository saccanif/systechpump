<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Cidade - Systech Pump</title>
  <link rel="stylesheet" href="./css/representanteHome.css" />
  <link rel="stylesheet" href="./css/cadastroStores.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>

<body>
  <div class="sidebar">
    <div class="user">
      <img src="./imgs/others/representante.jpg" alt="Usuário" />
      <span>representante</span>
    </div>
    <ul>
      <li>
        <a href="./representanteHome.php">
          <i class="fa fa-home"></i> Home
        </a>
      </li>
      <li>
        <a href="#">
          <i class="fa fa-store"></i> 
          Pedidos
        </a>
      </li>
      <li>
        <a href="./cadastroStores.php">
          <i class="fa fa-cart-plus"></i> Cadastrar lojas
        </a>
      </li>
      <li>
        <a href="./cadastroCidades.php">
          <i class="fa fa-city"></i> Cadastrar Cidades
        </a>
      </li>
    </ul>
    <div class="bottom-menu">
      <ul>
            <li>
                <a href="./config.php">
                    <i class="fa fa-home"></i> Configurações
                </a>
            </li>
            <li>
                <a href="./index.php">
                    <i class="fa fa-store"></i>
                    <span>Sair</span>
                </a>    
            </li>
        </ul>
    </div>
  </div>
  </div>

  <div class="main">
    <div class="topbar">
      <h2>Cadastrar Cidade</h2>
      <div class="logo">
        <img src="./imgs/others/logo-branco.png" alt="logo loja" />
      </div>
    </div>

    <div class="content-container">
      <div class="form-box">
        <form action="../src/controllers/CitysController.php" method="post" enctype="multipart/form-data">
          <label for="nomeCidade">Nome da Cidade</label>
          <input type="text" id="nomeCidade" name="nomeCidade" />

          <label for="idEstado">Estado</label>
          <select name="idEstado" id="idEstado" required>
                <option value="" disabled selected>Escolha um estado</option>
                <?php
                  require_once '../config/connection.php';
                  $sqlEstado = "SELECT idEstado, nomeEstado FROM estado ORDER BY nomeEstado";
                  $resEstado = mysqli_query(conectarBD(), $sqlEstado);
                  while ($estado = mysqli_fetch_assoc($resEstado)) {
                    echo "<option value='{$estado['idEstado']}'>{$estado['nomeEstado']}</option>";
                  }
                ?>
          </select>

          <button type="submit">Cadastrar Cidade</button>
        </form>

        <?php
          // Exibir a mensagem de ERRO caso OCORRA
          if (isset($_GET["msg"])) {  // Verifica se tem mensagem de ERRO
            $mensagem = $_GET["msg"];
            echo "<FONT color=red>$mensagem</FONT>";
          }
        ?>
      </div>
    </div>
  </div>

  
  
</body>
</html>

