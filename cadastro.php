<?php require_once __DIR__ . "/PHP/FUNCOES/processarCadastro.php"; $conexao = null;?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cadastro - REUNE+</title>
  <link rel="icon" href="resource/logo.jpeg" type="image/x-icon">
  <link rel="stylesheet" href="style.css">
</head>
<body class="signup-page">
  <main>
    <a href="index.php" class="btn-back">Voltar</a>
    <div class="signup-container">
      <div class="signup-content">

        <div class="signup-info">
          <h2>Já possui uma conta?</h2>
          <p>Acesse sua conta e continue ajudando a transformar comunidades</p>
          <button class="btn-login-alt" onclick="window.location.href='login.php'">Fazer Login</button>
        </div>

        <div class="signup-form-section">
          <h1 class="signup-title">Crie sua conta</h1>
          <p class="signup-subtitle">Cadastre-se como Doador ou ONG</p>

          <?php if ($resultado !== null): ?>
            <p class="<?= $resultado['sucesso'] ? 'sucesso' : 'erro' ?>">
              <?= htmlspecialchars($resultado['mensagem'], ENT_QUOTES, 'UTF-8') ?>
              <?= $resultado['sucesso'] ? '(ID: ' . (int) $resultado['id'] . ')' : '' ?>
            </p>
          <?php endif; ?>

          <div class="signup-tabs">
            <button class="tab-button active" onclick="trocarAbaCadastro('doador', this)">Doador</button>
            <button class="tab-button" onclick="trocarAbaCadastro('ong', this)">ONG</button>
          </div>

          <?php include __DIR__ . "/PHP/FORMS/formDoador.php"; ?>
          <?php include __DIR__ . "/PHP/FORMS/formOng.php"; ?>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . "/PHP/acessibilidade.php"; ?>

  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>

  <script src="JS/formatar.js"></script>
  <script src="JS/forms.js"></script>
</body>
</html>
