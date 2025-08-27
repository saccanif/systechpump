<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>
    <link rel="stylesheet" href="./css/login.css">
    <link rel="shortcut icon" href="./imgs/others/logo.png" type="image/png" />
</head>
<body>
  <div class="background">
    <div class="logo">
      <img src="./imgs/others/logo-branco.png" alt="Logo Systech Pump">
    </div>
    <div class="center">
      <div class="login-card">
        <h2>Bem-vindo ao Systech Pump</h2>
        <form method="post" name="formLogin" action="../src/controllers/ClientController.php">
          <input type="text" class="input-field" name="email" placeholder="Email" required>
          <input type="password" class="input-field" name="senha" placeholder="Senha" required>
          <button type="submit" class="login-btn">Entrar</button>
          <a href="#" class="forgot">Esqueceu sua senha?</a>
        </form>
      </div>
    </div>
  </div>
</body>
</html>
