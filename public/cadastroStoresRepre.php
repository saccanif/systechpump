<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Cadastrar Loja - Systech Pump</title>
  <link rel="stylesheet" href="./css/representanteHome.css" />
  <link rel="stylesheet" href="./css/cadastroStores.css" />
    <link rel="stylesheet" href="./css/cadastroCidades.css" />

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
      <li><a href="./representanteHome.php"><i class="fa fa-home"></i> Home</a></li>
      <li><a href="#"><i class="fa fa-store"></i> Pedidos</a></li>
      <li><a href="./cadastroStoresRepre.php"><i class="fa fa-cart-plus"></i> Cadastrar lojas</a></li>
      <li><a href="./cadastroCidadesRepre.php"><i class="fa fa-city"></i> Cadastrar Cidades</a></li>
      <li><a href="./deletarLojaRepre.php"><i class="fa fa-delete"></i> Deletar Lojas</a></li>
    </ul>
    <div class="bottom-menu">
      <ul>
        <li><a href="./config.php"><i class="fa fa-home"></i> Configurações</a></li>
        <li><a href="./index.php"><i class="fa fa-store"></i> Sair</a></li>
      </ul>
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
          <div class="form-row">
            <div class="form-group">
              <label for="nomeLoja">Nome da Loja</label>
              <input type="text" id="nomeLoja" name="nomeLoja" required />
            </div>
            
            <div class="form-group">
              <label for="telefoneLoja">Telefone da Loja</label>
              <input type="text" id="telefoneLoja" name="telefoneLoja" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="emailLoja">E-mail da Loja</label>
              <input type="email" id="emailLoja" name="emailLoja" required />
            </div>
            
            <div class="form-group">
              <label for="idRepresentante">Id do Representante</label>
              <input type="text" id="idRepresentante" name="idRepresentante" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="cep">CEP</label>
              <input type="text" id="cep" name="cep" placeholder="00000-000" required />
            </div>
            
            <div class="form-group">
              <label for="numero">Número</label>
              <input type="text" id="numero" name="numero" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="bairro">Bairro</label>
              <input type="text" id="bairro" name="bairro" required />
            </div>
            
            <div class="form-group">
              <label for="logradouro">Logradouro</label>
              <input type="text" id="logradouro" name="logradouro" required />
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
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
            </div>
            
            <div class="form-group">
              <label for="idCidade">Cidade</label>
              <select name="idCidade" id="idCidade" required>
                <option value="" disabled selected>Escolha uma cidade</option>
                <?php
                  $sqlCidade = "SELECT idCidade, nomeCidade FROM cidade ORDER BY nomeCidade";
                  $resCidade = mysqli_query(conectarBD(), $sqlCidade);
                  while ($cidade = mysqli_fetch_assoc($resCidade)) {
                      echo "<option value='{$cidade['idCidade']}'>{$cidade['nomeCidade']}</option>";
                  }
                ?>
              </select>
            </div>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="fotoLoja">Foto da Loja</label>
              <input type="file" id="fotoLoja" name="fotoLoja" />
            </div>
          </div>
          
          <button type="submit">Cadastrar Loja</button>
        </form>

        <?php
          if (isset($_GET["msg"])) {
            $mensagem = $_GET["msg"];
            echo "<font color=red>$mensagem</font>";
          }
        ?>
      </div>
    </div>
  </div>
</body>
</html>

