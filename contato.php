<?php
session_start();
include_once __DIR__ . "/PHP/pegarSessao.php"; $conexao = null;
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>REUNE+</title>
  <link rel="icon" href="resource/logo.jpeg" type="image/x-icon">
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <header>
    <div class="container">
      <div class="topbar">
        <div class="brand"><img src="resource/logo.jpeg" alt="Ícone de mãos unidas" class="logo">
            REUNE<b>+</b>
        </div>
        <nav class="nav-links">
          <?php 
          if (!isset($_SESSION["email-doador"]) && (!isset($_SESSION["email-ong"]))) {
          echo "<svg xmlns='http://www.w3.org/2000/svg' width='32' height='32' fill='currentColor' class='bi bi-person-circle' viewBox='0 0 16 16'>
  <path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>
  <path fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1'/>
</svg>";
          } else {
            echo '<img class="profile-image" src="' . htmlspecialchars($imagemPerfil, ENT_QUOTES, 'UTF-8') . '" alt="Imagem do perfil">';
          }
          ?>
          <a href="index.php">Sobre nós</a>
          <a href="ongs.php">ONGs Parceiras</a>
          <a href="#contato">Contato</a>
        </nav>
      </div>
    </div>
  </header>
  <main>
    <section id="contato">
      <div class="container">
        <div class="section-intro">
          <h2>Entre em Contato</h2>
          <p>Tem dúvidas ou gostaria de saber mais sobre nossas iniciativas? Entre em contato conosco e faremos questão de responder em breve.</p>
        </div>

        <div class="contact">
          <div class="contact-grid">
            <div class="contact-info">
              <h3>Informações de Contato</h3>
              <p>
                <strong>Email:</strong><br>
                <a href="mailto:contato@reunemais.org">contato@reunemais.org</a>
              </p>
              <p>
                <strong>Telefone:</strong><br>
                <a href="tel:+5511999999999">(11) 99999-9999</a>
              </p>
              <p>
                <strong>Localização:</strong><br>
                Praia Grande - SP, Brasil
              </p>
              <p>
                <strong>Horário de Atendimento:</strong><br>
                Segunda a Sexta: 09h às 18h<br>
                Sábado: 10h às 14h
              </p>
            </div>

            <form class="contact-form">
              <input type="text" placeholder="Seu Nome" required>
              <input type="email" placeholder="Seu Email" required>
              <input type="tel" placeholder="Seu Telefone" required>
              <textarea placeholder="Sua Mensagem" required></textarea>
              <button type="submit" class="btn btn-primary">Enviar Mensagem</button>
            </form>
          </div>
        </div>
      </div>
    </section>
  </main>

  <?php include __DIR__ . "/PHP/acessibilidade.php"; ?>
  
  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>
</body>
</html>