<!DOCTYPE html>
<html lang="pt-br">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="telalogin.css" rel="stylesheet">
</head>
<body>
  <div class="container">
    <div class="left">
      <div class="tabs">
        <button class="tab active">Login</button>
        <button class="tab"><a href="../tela-de-cadastro/telacadastro.html">Cadastro</a></button>
      </div>
      <h1 class="welcome">Bem-vindo(a)<br>de volta!</h1>

      <?php if (isset($_GET['erro'])): ?>
        <span class="erro-msg">Usuário ou senha incorreto!</span>
      <?php endif; ?>

      <form method="post" action="../backend/verifica_login.php">
        <div class="form-group">
          <input type="email" name="email" placeholder="Digite seu Email" required>
        </div>
        <div class="form-group">
          <input type="password" name="senha" placeholder="Digite sua Senha" required>
        </div>
        <button type="submit" class="login-button">Fazer login</button>
      </form>

      <div class="register-link">
        Ainda não possui uma conta? <a href="../tela-de-cadastro/telacadastro.html">Cadastre-se</a>
      </div>
    </div>
    <div class="right">
      <!-- Imagem de fundo aqui -->
    </div>
  </div>
</body>
</html>
