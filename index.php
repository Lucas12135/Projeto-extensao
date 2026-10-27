<?php
require_once __DIR__ .  "/BD/conexao.php";
require_once __DIR__ . "/BD/criarTabelas.php";
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
              <strong>15+</strong>
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