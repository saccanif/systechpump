<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Loja - Systech Pump</title>
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
      <h2>Cadastrar Loja</h2>
      <div class="logo">
        <img src="./imgs/others/logo-branco.png" alt="logo loja" />
      </div>
    </div>

    <div class="content-container">
      <div class="form-box">
        <form action="../src/controllers/StoresController.php" method="post" enctype="multipart/form-data">
  
          <label for="nomeLoja">Nome da Loja</label>
          <input type="text" id="nomeLoja" name="nomeLoja" />

          <label for="telefoneLoja">Telefone da Loja</label>
          <input type="text" id="telefoneLoja" name="telefoneLoja" />

          <label for="emailLoja">E-mail da Loja</label>
          <input type="email" id="emailLoja" name="emailLoja" />

          <label for="idcidade">Cidade ID</label>
          <input type="text" id="idcidade" name="idcidade" />

          <label for="idRepresentante">Id do Representante</label>
          <input type="text" id="idRepresentante" name="idRepresentante" />

          <label for="fotoLoja">Foto da Loja</label>
          <input type="file" id="fotoLoja" name="fotoLoja" />

          <button type="submit">Cadastrar Loja</button>
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

