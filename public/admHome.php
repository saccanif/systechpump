<?php
  session_start();
  require_once 'functions.php';

  if (!isset($_SESSION['usuario']) || $_SESSION['usuario']['tipoUsuario'] !== 'administrador') {
      header('Location: ./login.php');
      exit();
  }
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <title>Painel Administrativo - Systech Pump</title>
  <link rel="stylesheet" href="./css/representanteHome.css" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" />
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>
<body>

  <div class="sidebar">
    <div class="user">
      <img src="./imgs/others/representante.jpg" alt="Usuário">
      <span><?php echo $_SESSION['usuario']['nomeUsuario']; ?></span>
      <small>Administrador</small>
    </div>
    <ul>
      <li>
        <a href="<?php echo getHomePage(); ?>">
          <i class="fa fa-home"></i> Home
        </a>
      </li>
      <li>
        <a href="./pedidos.php">
            <i class="fa fa-chart-bar"></i>
            <span>Relatórios</span>
        </a>    
      </li>
      <li>
        <a href="./cadastroStores.php">
          <i class="fa fa-store"></i> Cadastrar Lojas
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
            <i class="fa fa-cog"></i> Configurações
          </a>
        </li>
        <li>
          <a href="./logout.php">
            <i class="fa fa-sign-out-alt"></i>
            <span>Sair</span>
          </a>    
        </li>
      </ul>
    </div>
  </div>

  <div class="main">
    <div class="topbar">
      <h2>Painel Administrativo</h2>
      <div class="logo">
        <img src="./imgs/others/logo-branco.png" alt="Systech Pump">
      </div>
    </div>
    
    <div class="content">
      <h1>Bem-vindo, <?php echo $_SESSION['usuario']['nomeUsuario']; ?>!</h1>
      <p>Seu cargo: <strong>Administrador</strong></p>
    </div>
  </div>

</body>
</html>