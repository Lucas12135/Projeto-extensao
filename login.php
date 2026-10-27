<?php 
  require_once __DIR__ .  "/BD/conexao.php";
  session_start();
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $emailDoador = isset($_POST['email-doador']) ? $_POST['email-doador'] : null;
    $senhaDoador = isset($_POST['senha-doador']) ? $_POST['senha-doador'] : null;

    $emailOng = isset($_POST['email-ong']) ? $_POST['email-ong'] : null;
    $senhaOng = isset($_POST['senha-ong']) ? $_POST['senha-ong'] : null;
    
    if($emailOng == null) {
      $smnt=$conexao->prepare("SELECT pw_doador FROM DOADOR WHERE nm_email_doador = :email");
      $smnt->bindParam(":email", $emailDoador);
      $smnt->execute();
      $usuario = $smnt->fetch(PDO::FETCH_ASSOC);

      if (password_verify($senhaDoador, $usuario['pw_doador'])) {
        $_SESSION['usuario_email'] = $usuario['email-doador'];
        header("Location: index.php");
      } else {
        echo "E-mail ou senha incorretos.";
      }
      
    } else {
      $smnt=$conexao->prepare("SELECT pw_ong FROM ONG WHERE nm_email_ong = :email");
      $smnt->bindParam(":email", $emailOng);
      $smnt->execute();
      $usuario = $smnt->fetch(PDO::FETCH_ASSOC);

      if (password_verify($senhaOng, $usuario['pw_ong'])) {
        $_SESSION['usuario_email'] = $usuario['email-ong'];
        header("Location: index.php");
      } else {
        echo "E-mail ou senha incorretos.";
      }
    }
    $conexao = null;
  }
  
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login - REUNE+</title>
  <link rel="icon" href="resource/logo.jpeg" type="image/x-icon">
  <link rel="stylesheet" href="style.css">
</head>
<body class="login-page">
  <main>
    <a href="index.php" class="btn-back">Voltar</a>
    <div class="login-container">
      <div class="login-content">

        <div class="login-signup">
          <h2>Não possui uma conta?</h2>
          <p>Cadastre-se e comece a fazer parte da comunidade REUNE+</p>
          <a href="cadastro.php">
            <button class="btn-signup">Cadastre-se</button>
          </a>
        </div>

        <div class="login-form-section">
          <h1 class="login-title">Bem-vindo</h1>
          <p class="login-subtitle">Faça login na sua conta</p>

          <div class="login-tabs">
            <button class="tab-button active" onclick="trocarAba('doador')">Doador</button>
            <button class="tab-button" onclick="trocarAba('ong')">ONG</button>
          </div>

          <form class="login-form active" id="form-doador" action="#" method="POST">
            <div class="form-group">
              <label for="email-doador">Email</label>
              <input type="email" id="email-doador" name="email-doador" placeholder="seu@email.com" required>
            </div>

            <div class="form-group">
              <label for="password-doador">Senha</label>
              <input type="password" id="password-doador" name="senha-doador" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
              <label class="remember-me">
                <input type="checkbox" name="remember">
                Lembrar-me
              </label>
              <a href="#" class="forgot-password">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-login">Entrar como Doador</button>
          </form>

          <form class="login-form" id="form-ong" action="#" method="POST">
            <div class="form-group">
              <label for="email-ong">Email</label>
              <input type="email" id="email-ong" name="email-ong" placeholder="contato@ong.com.br" required>
            </div>

            <div class="form-group">
              <label for="password-ong">Senha</label>
              <input type="password" id="password-ong" name="senha-ong" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
              <label class="remember-me">
                <input type="checkbox" name="remember">
                Lembrar-me
              </label>
              <a href="#" class="forgot-password">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-login">Entrar como ONG</button>
          </form>
        </div>
      </div>
    </div>
  </main>

  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>

  <script src="script.js"></script>
</body>
</html>
