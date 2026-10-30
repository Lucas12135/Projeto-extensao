<?php
session_start();
require_once __DIR__ .  "/BD/conexao.php";
require_once __DIR__ . "/BD/criarTabelas.php";

$smnt= $conexao->prepare("SELECT COUNT(id_ONG) AS qt FROM ONG");

$smnt->execute();
$qnt = $smnt->fetch(PDO::FETCH_ASSOC);

if (isset($_SESSION["email-ong"])) {
$smnt= $conexao->prepare("SELECT nm_imagem_ong FROM ONG WHERE nm_email_ong = :email");
$smnt->execute([':email' => $_SESSION["email-ong"]]);

$tmp = $smnt->fetch(PDO::FETCH_ASSOC) ?: [];
$img = $tmp['nm_imagem_ong'] ?? null;

} else if (isset($_SESSION["email-doador"])) {
$smnt= $conexao->prepare("SELECT nm_imagem_doador FROM DOADOR WHERE nm_email_doador = :email");
$smnt->execute([':email' => $_SESSION["email-doador"]]);

$tmp = $smnt->fetch(PDO::FETCH_ASSOC) ?: [];
$img = $tmp['nm_imagem_doador'] ?? null;
}

$imagemPerfil = $img ?? 'resource/logo.jpeg';
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
        <div class="brand"><img src="resource/logo.jpeg" alt="Ícone de REUNE+" class="logo">
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
          <a href="#sobre">Sobre nós</a>
          <a href="ongs.php">ONGs Parceiras</a>
          <a href="contato.php">Contato</a>
        </nav>
      </div>

      <div class="hero">
        <div>
          <p>Conectando doadores, ONGs e voluntários em um só lugar.</p>
          <h1>Uma plataforma digital para facilitar a doação e distribuição de alimentos.</h1>
          <p>O Reune+ é uma plataforma centralizadora que conecta doadores, voluntários e organizações beneficentes, tornando o processo de arrecadação e doação mais organizado, acessível e eficiente.</p>
          <div class="buttons">
            <a class="btn btn-primary" href="login.php">Comece a ajudar</a>
            <a class="btn btn-secondary" href="ongs.php">Conheça as ONGs parceiras</a>
          </div>
        </div>

        <div class="hero-visual">
          <p>Impacto da plataforma:</p>
          <div class="stats">
            <div class="stat-card">
              <strong><?= $qnt['qt'] ?></strong>
              ONGs parceiras
            </div>
            <div class="stat-card">
              <strong>1.200+</strong>
              Doações realizadas
            </div>
          </div>
        </div>
      </div>
    </div>
  </header>

  <main class="container">
    <section id="sobre">
      <div class="section-intro">
        <h2>Como funciona o Reune+</h2>
        <p>Uma plataforma moderna que utiliza a tecnologia como ferramenta de impacto social, promovendo solidariedade e integração entre ONGs para apoiar a comunidade.</p>
      </div>
      <div class="cards">
        <article class="card">
          <h3>Conecta Doadores</h3>
          <p>Facilita a conexão entre pessoas dispostas a ajudar e instituições que necessitam de apoio, tornando a doação simples, rápida e transparente.</p>
        </article>
        <article class="card">
          <h3>Integra ONGs</h3>
          <p>Centraliza diversas organizações beneficentes em um único ambiente digital, ampliando o alcance das campanhas e necessidades sociais das instituições parceiras.</p>
        </article>
        <article class="card">
          <h3>Reduz Desperdício</h3>
          <p>Otimiza a distribuição de alimentos e recursos, conectando oferta e demanda de forma eficiente, reduzindo o desperdício e maximizando o impacto social.</p>
        </article>
      </div>
    </section>
  </main>

  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>
</body>
</html>