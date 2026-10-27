<?php require_once __DIR__ . "/PHP/processarCadastro.php"; ?>
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

          <form class="signup-form active" id="form-signup-doador" action="#" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tipo" value="doador">
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
                <label for="imagem-doador">Imagem</label>
                <input type="file" id="imagem-doador" name="imagem-doador" accept="image/*">
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

          <form class="signup-form" id="form-signup-ong" action="#" method="POST" enctype="multipart/form-data">
            <input type="hidden" name="tipo" value="ong">
            <p class="signup-step-label" id="ong-step-label">Etapa 1 de 2: Dados da ONG</p>

            <fieldset class="ong-step" id="ong-step-1">
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

              <button type="button" class="btn-signup" id="ong-next-step">Continuar</button>
            </fieldset>

            <fieldset class="ong-step" id="ong-step-2" disabled hidden>
              <div class="form-group">
                <label for="distancia-ong">Distância (km)</label>
                <input type="number" id="distancia-ong" name="distancia-ong" min="0" step="0.1" placeholder="Ex.: 3" required>
              </div>

              <div class="form-group">
                <label for="localizacao-ong">Localização</label>
                <input type="text" id="localizacao-ong" name="localizacao-ong" placeholder="Bairro e cidade" required>
              </div>

              <div class="form-group">
                <label for="doacoes-ong">Doações aceitas</label>
                <textarea id="doacoes-ong" name="doacoes-ong" placeholder="Informe os tipos de doação aceitos" required></textarea>
              </div>

              <div class="form-group">
                <label for="sobre-ong">Sobre a ONG</label>
                <textarea id="sobre-ong" name="sobre-ong" required></textarea>
              </div>

              <div class="form-group">
                <label for="missao-ong">Missão</label>
                <textarea id="missao-ong" name="missao-ong" required></textarea>
              </div>

              <div class="form-group">
                <label for="projetos-ong">Projetos ativos</label>
                <textarea id="projetos-ong" name="projetos-ong" placeholder="Informe os projetos da ONG" required></textarea>
              </div>

              <div class="form-group">
                <label for="imagem-ong">Imagem</label>
                <input type="file" id="imagem-ong" name="imagem-ong" accept="image/*" required>
              </div>

              <div class="form-agreement">
                <label class="checkbox-agreement">
                  <input type="checkbox" name="agreement" required>
                  Concordo com os termos de serviço
                </label>
              </div>

              <div class="ong-step-actions">
                <button type="button" class="btn-step-back" id="ong-previous-step">Voltar</button>
                <button type="submit" class="btn-signup">Cadastrar como ONG</button>
              </div>
            </fieldset>
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
