<?php 
  require_once __DIR__ .  "/BD/conexao.php";
  session_start();
  $erroLogin = false;
  $tipoLogin = isset($_COOKIE['reune_remember_login_type']) &&
    $_COOKIE['reune_remember_login_type'] === 'ong' ? 'ong' : 'doador';
  $emailDoador = '';
  $emailOng = '';
  $rememberLogin = false;
  $updateRememberedLogin = static function ($email, $type): void {
    $cookieOptions = [
      'expires' => $email === null ? time() - 3600 : time() + 60 * 60 * 24 * 365,
      'path' => '/',
      'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
      'httponly' => true,
      'samesite' => 'Lax',
    ];
    setcookie('reune_remember_login_email', $email ?? '', $cookieOptions);
    setcookie('reune_remember_login_type', $type ?? '', $cookieOptions);
  };

  if (
    isset($_COOKIE['reune_remember_login_email']) &&
    filter_var($_COOKIE['reune_remember_login_email'], FILTER_VALIDATE_EMAIL)
  ) {
    if ($tipoLogin === 'ong') {
      $emailOng = $_COOKIE['reune_remember_login_email'];
    } else {
      $emailDoador = $_COOKIE['reune_remember_login_email'];
    }
    $rememberLogin = true;
  }

  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $tipoLogin = isset($_POST['email-ong']) ? 'ong' : 'doador';
    $emailDoador = isset($_POST['email-doador']) ? trim($_POST['email-doador']) : '';
    $senhaDoador = isset($_POST['senha-doador']) ? $_POST['senha-doador'] : null;

    $emailOng = isset($_POST['email-ong']) ? trim($_POST['email-ong']) : '';
    $senhaOng = isset($_POST['senha-ong']) ? $_POST['senha-ong'] : null;
    $rememberLogin = isset($_POST['remember']);
    if (!$rememberLogin) {
      $updateRememberedLogin(null, null);
    }
    
    if ($tipoLogin === 'doador') {
      $smnt=$conexao->prepare("SELECT nm_email_doador, pw_doador FROM DOADOR WHERE nm_email_doador = :email");
      $smnt->bindParam(":email", $emailDoador);
      $smnt->execute();
      $usuario = $smnt->fetch(PDO::FETCH_ASSOC);

      if ($usuario && password_verify($senhaDoador, $usuario['pw_doador'])) {
        $_SESSION['email-doador'] = $usuario['nm_email_doador'];
        if ($rememberLogin) {
          $updateRememberedLogin($usuario['nm_email_doador'], 'doador');
        }
        header("Location: index.php");
        exit;
      } else {
        $erroLogin = true;
      }
      
    } else {
      $smnt=$conexao->prepare("SELECT nm_email_ong, pw_ong FROM ONG WHERE nm_email_ong = :email");
      $smnt->bindParam(":email", $emailOng);
      $smnt->execute();
      $usuario = $smnt->fetch(PDO::FETCH_ASSOC);

      if ($usuario && password_verify($senhaOng, $usuario['pw_ong'])) {
        $_SESSION['email-ong'] = $usuario['nm_email_ong'];
        if ($rememberLogin) {
          $updateRememberedLogin($usuario['nm_email_ong'], 'ong');
        }
        header("Location: index.php");
        exit;
      } else {
        $erroLogin = true;
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
            <button class="tab-button <?= $tipoLogin === 'doador' ? 'active' : '' ?>" onclick="trocarAba('doador')">Doador</button>
            <button class="tab-button <?= $tipoLogin === 'ong' ? 'active' : '' ?>" onclick="trocarAba('ong')">ONG</button>
          </div>

          <form class="login-form <?= $tipoLogin === 'doador' ? 'active' : '' ?>" id="form-doador" action="#" method="POST">
            <?php if ($erroLogin && $tipoLogin === 'doador'): ?>
              <p class="login-error" role="alert">E-mail ou senha incorretos.</p>
            <?php endif; ?>
            <div class="form-group">
              <label for="email-doador">Email</label>
              <input type="email" id="email-doador" name="email-doador" placeholder="seu@email.com" value="<?= htmlspecialchars($emailDoador, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
              <label for="password-doador">Senha</label>
              <input type="password" id="password-doador" name="senha-doador" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
              <label class="remember-me">
                <input type="checkbox" name="remember" <?= $tipoLogin === 'doador' && $rememberLogin ? 'checked' : '' ?>>
                Lembrar-me
              </label>
              <a href="#" class="forgot-password">Esqueceu a senha?</a>
            </div>

            <button type="submit" class="btn-login">Entrar como Doador</button>
          </form>

          <form class="login-form <?= $tipoLogin === 'ong' ? 'active' : '' ?>" id="form-ong" action="#" method="POST">
            <?php if ($erroLogin && $tipoLogin === 'ong'): ?>
              <p class="login-error" role="alert">E-mail ou senha incorretos.</p>
            <?php endif; ?>
            <div class="form-group">
              <label for="email-ong">Email</label>
              <input type="email" id="email-ong" name="email-ong" placeholder="contato@ong.com.br" value="<?= htmlspecialchars($emailOng, ENT_QUOTES, 'UTF-8') ?>" required>
            </div>

            <div class="form-group">
              <label for="password-ong">Senha</label>
              <input type="password" id="password-ong" name="senha-ong" placeholder="••••••••" required>
            </div>

            <div class="form-actions">
              <label class="remember-me">
                <input type="checkbox" name="remember" <?= $tipoLogin === 'ong' && $rememberLogin ? 'checked' : '' ?>>
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
  
  <?php include __DIR__ . "/PHP/acessibilidade.php"; ?>
  
  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>

  <script src="JS/forms.js"></script>
</body>
</html>
