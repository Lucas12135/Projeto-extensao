<?php 
  require_once __DIR__ .  "/BD/conexao.php";
  if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nomeDoador = isset($_POST['nome-doador']) ? $_POST['nome-doador'] : null;
    $emailDoador = isset($_POST['email-doador']) ? $_POST['email-doador'] : null;
    $telefoneDoador = isset($_POST['telefone-doador']) ? $_POST['telefone-doador'] : null;
    $enderecoDoador = isset($_POST['endereco-doador']) ? $_POST['endereco-doador'] : null;
    $cpfDoador = isset($_POST['cpf-doador']) ? $_POST['cpf-doador'] : null;
    $senhaDoador = isset($_POST['senha-doador']) ? password_hash($_POST['senha-doador'], PASSWORD_DEFAULT) : null;

    $nomeOng = isset($_POST['nome-ong']) ? $_POST['nome-ong'] : null;
    $cnpjDoador = isset($_POST['cnpj-doador']) ? $_POST['cnpj-doador'] : null;
    $documento = isset($_POST['documento']) ? $_POST['documento'] : null;
    $emailOng = isset($_POST['email-ong']) ? $_POST['email-ong'] : null;
    $senhaOng = isset($_POST['senha-ong']) ? password_hash($_POST['senha-ong'], PASSWORD_DEFAULT) : null;
    $telefoneOng = isset($_POST['telefone-ong']) ? $_POST['telefone-ong'] : null;

    if($nomeOng == null) {
      $smnt=$conexao->prepare("INSERT INTO DOADOR(nm_doador, cd_cpf_doador, nm_email_doador, cd_telefone_doador, pw_doador) VALUES (:nome, :cpf, :email, :telefone, :senha)");
      $smnt->bindParam(":nome", $nomeDoador);
      $smnt->bindParam(":cpf", $cpfDoador);
      $smnt->bindParam(":email", $emailDoador);
      $smnt->bindParam(":telefone", $telefoneDoador);
      $smnt->bindParam(":senha", $senhaDoador);
      $smnt->execute();
      
    } else {
      $smnt=$conexao->prepare("INSERT INTO ONG(nm_ong, cd_cnpj, nm_email_ong, cd_telefone_ong, pw_ong) VALUES (:nome, :cnpj, :email, :telefone, :senha)");
      $smnt->bindParam(":nome", $nomeOng);
      $smnt->bindParam(":cnpj", $cnpjOng);
      $smnt->bindParam(":email", $emailOng);
      $smnt->bindParam(":telefone", $telefoneOng);
      $smnt->bindParam(":senha", $senhaOng);
      $smnt->execute();
    }
    $conexao = null;
  }
  
  ?>
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

          <div class="signup-tabs">
            <button class="tab-button active" onclick="trocarAbaCadastro('doador')">Doador</button>
            <button class="tab-button" onclick="trocarAbaCadastro('ong')">ONG</button>
          </div>

          <form class="signup-form active" id="form-signup-doador" action="#" method="POST">
            <div class="form-group">
              <label for="nome-doador">Nome Completo</label>
              <input type="text" id="nome-doador" name="nome-doador" placeholder="Seu nome completo" required>
            </div>

            <div class="form-group">
              <label for="email-doador">Email</label>
              <input type="email" id="email-doador" name="email-doador" placeholder="seu@email.com" required>
            </div>

            <div class="form-group">
              <label for="telefone-doador">Telefone</label>
              <input type="tel" id="telefone-doador" name="telefone-doador" placeholder="(11) 9XXXX-XXXX" required>
            </div>

            <div class="form-group">
              <label for="endereco-doador">Endereço</label>
              <input type="text" id="endereco-doador" name="endereco-doador" placeholder="Rua, número, complemento" required>
            </div>

            <div class="form-group">
              <label for="cpf-doador">CPF</label>
              <input type="text" id="cpf-doador" name="cpf-doador" placeholder="XXX.XXX.XXX-XX" required>
            </div>

            <div class="form-group">
              <label for="senha-doador">Senha</label>
              <input type="password" id="senha-doador" name="senha-doador" placeholder="••••••••" required>
            </div>

            <div class="form-group">
              <label for="confirmar-senha-doador">Confirmar Senha</label>
              <input type="password" id="confirmar-senha-doador" name="confirmar-senha" placeholder="••••••••" required>
            </div>

            <div class="form-agreement">
              <label class="checkbox-agreement">
                <input type="checkbox" name="agreement" required>
                Concordo com os termos de serviço
              </label>
            </div>

            <button type="submit" class="btn-signup">Cadastrar como Doador</button>
          </form>

          <form class="signup-form" id="form-signup-ong" action="#" method="POST">
            <div class="form-group">
              <label for="nome-ong">Nome da ONG</label>
              <input type="text" id="nome-ong" name="nome-ong" placeholder="Nome da organização" required>
            </div>

            <div class="form-group">
              <label for="cnpj-ong">CNPJ</label>
              <input type="text" id="cnpj-ong" name="cnpj-ong" placeholder="XX.XXX.XXX/XXXX-XX" required>
            </div>

            <div class="form-group">
              <label for="documento-ong">Documento (opcional)</label>
              <input type="text" id="documento-ong" name="documento" placeholder="Comprovante ou documento adicional">
            </div>

            <div class="form-group">
              <label for="telefone-ong">Telefone</label>
              <input type="tel" id="telefone-ong" name="telefone-ong" placeholder="(11) 9XXXX-XXXX" required>
            </div>

            <div class="form-group">
              <label for="email-ong">Email</label>
              <input type="email" id="email-ong" name="email-ong" placeholder="contato@ong.com.br" required>
            </div>

            <div class="form-group">
              <label for="senha-ong">Senha</label>
              <input type="password" id="senha-ong" name="senha-ong" placeholder="••••••••" required>
            </div>

            <div class="form-group">
              <label for="confirmar-senha-ong">Confirmar Senha</label>
              <input type="password" id="confirmar-senha-ong" name="confirmar-senha" placeholder="••••••••" required>
            </div>

            <div class="form-agreement">
              <label class="checkbox-agreement">
                <input type="checkbox" name="agreement" required>
                Concordo com os termos de serviço
              </label>
            </div>

            <button type="submit" class="btn-signup">Cadastrar como ONG</button>
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
