<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Login</title>
  <link rel="stylesheet" href="./css/login.css" />
  <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>
<body>

  <!-- Navbar -->
  <header class="navbar">
    <div class="container navbar-content">
      <nav class="navbar-navigation">
        <ul class="navbar-list">
          <li><a href="./index.php">Início</a></li>
        </ul>
      </nav>
      <a href="/" class="logo-link">
        <img src="./imgs/others/logo.png" alt="Systech Pump" />
      </a>
    </div>
  </header>


  <!-- Conteúdo principal -->
  <main>
    <div class="login-card">
      <div class="title-wrap">
        <h1 class="title">Login</h1>
      </div>
      <p class="subtitle">Acesse seu painel</p>

      <form id="loginForm" action="../src/controllers/loginController.php" method="POST">
        <div class="field">
          <label for="usuario">E-mail ou usuário</label>
          <input type="text" id="usuario" name="usuario" class="input" placeholder="Digite seu e-mail ou usuário" required>
        </div>

        <div class="field">
          <label for="senha">Senha</label>
          <input type="password" id="senha" name="senha" class="input" placeholder="Digite sua senha" required>
        </div>

        <div class="row">
          <a href="#" class="link">Esqueci minha senha</a>
        </div>

        <div class="actions">
          <button type="submit" class="btn btn-primary">Entrar</button>
        </div>
        <?php
          if (isset($_GET['erro'])) {
              echo '<div style="background-color: #fee2e2; color: #dc2626; padding: 12px; border-radius: 8px; margin: 20px 0; text-align: center;">';
              echo htmlspecialchars($_GET['erro']);
              echo '</div>';
          }
        ?>
      </form>
    </div>
  </main>

 

  <!-- Footer -->
  <footer class="site">
    <div class="container">
      © 2025 Systech Pump • Conectando água e tecnologia
    </div>
  </footer>

<script defer src="./js/login.js"></script>
</body>
</html>

