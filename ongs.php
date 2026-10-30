<?php
require_once __DIR__ . "/BD/conexao.php";
$smnt = $conexao->prepare(
  "SELECT nm_ong, nm_email_ong, cd_telefone_ong, cd_distancia_km,
          ds_doacoes, ds_sobre, ds_missao, ds_projetos, ds_localizacao, nm_imagem_ong
   FROM ONG
   ORDER BY nm_ong"
);
$smnt->execute();
$ongs = $smnt->fetchAll(PDO::FETCH_ASSOC);
$ongSelecionada = $ongs[0] ?? null;
$escapar = static function ($valor) {
  return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>ONGs Parceiras - REUNE+</title>
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
          <a href="index.php">Sobre nós</a>
          <a href="ongs.php">ONGs Parceiras</a>
          <a href="contato.php">Contato</a>
        </nav>
      </div>
    </div>
  </header>

  <main>
    <div class="container">
      <section class="ongs-section">
        <div class="section-intro">
          <h2>ONGs Parceiras</h2>
          <p>Encontre organizações parceiras, conheça seus projetos e veja como ajudar.</p>
        </div>

        <div class="search-filters">
          <div class="search-bar">
            <input type="text" id="searchInput" placeholder="Pesquisar ONGs..." class="search-input">
          </div>
          
          <div class="filters-group">
            <div class="filter-item">
              <label for="sortFilter">Ordenar por:</label>
              <select id="sortFilter" class="filter-select">
                <option value="alfabetico">Alfabética (A-Z)</option>
                <option value="alfabetico-desc">Alfabética (Z-A)</option>
              </select>
            </div>

            <button class="btn-reset">Limpar Filtros</button>
          </div>
        </div>

        <div class="ongs-container">
          <div class="ongs-list" id="ongsList">
            <?php if (count($ongs) === 0): ?>
              <p>Nenhuma ONG cadastrada.</p>
            <?php else: ?>
              <?php foreach ($ongs as $indice => $ong): ?>
                <?php
                  $nome = $escapar($ong['nm_ong']);
                  $distancia = $ong['cd_distancia_km'] ?? '';
                  $doacoes = $ong['ds_doacoes'] ?? '';
                  $imagem = $ong['nm_imagem_ong'] ?: 'resource/logo.jpeg';
                ?>
                <button
                  type="button"
                  class="ong-card<?= $indice === 0 ? ' active' : '' ?>"
                  data-nome="<?= $nome ?>"
                  data-imagem="<?= $escapar($imagem) ?>"
                  data-distancia="<?= $escapar($distancia) ?>"
                  data-doacoes="<?= $escapar($doacoes) ?>"
                  data-sobre="<?= $escapar($ong['ds_sobre']) ?>"
                  data-missao="<?= $escapar($ong['ds_missao']) ?>"
                  data-projetos="<?= $escapar($ong['ds_projetos']) ?>"
                  data-localizacao="<?= $escapar($ong['ds_localizacao']) ?>"
                  data-telefone="<?= $escapar($ong['cd_telefone_ong']) ?>"
                  data-email="<?= $escapar($ong['nm_email_ong']) ?>"
                  aria-pressed="<?= $indice === 0 ? 'true' : 'false' ?>"
                >
                  <img src="<?= $escapar($imagem) ?>" alt="Logo da ONG">
                  <div class="card-info">
                    <h4><?= $nome ?></h4>
                    <p class="distance"><?= $distancia !== '' ? $escapar($distancia) . ' km' : 'Distância não informada' ?></p>
                    <p class="donations"><?= $doacoes !== '' ? $escapar($doacoes) : 'Doações não informadas' ?></p>
                  </div>
                </button>
              <?php endforeach; ?>
            <?php endif; ?>
          </div>

          <section class="ong-details" id="ongDetails" aria-live="polite">
            <?php if ($ongSelecionada): ?>
              <?php $imagemSelecionada = $ongSelecionada['nm_imagem_ong'] ?: 'resource/logo.jpeg'; ?>
              <div class="details-header">
                <img id="detailsImage" src="<?= $escapar($imagemSelecionada) ?>" alt="Logo da ONG">
                <div class="details-title">
                  <h2 id="detailsName"><?= $escapar($ongSelecionada['nm_ong']) ?></h2>
                </div>
              </div>

              <div class="details-body">
                <div class="detail-section">
                  <h3>Localização</h3>
                  <p id="detailsLocation"><?= $escapar($ongSelecionada['ds_localizacao'] ?: 'Não informada') ?></p>
                  <p id="detailsDistance"><?= $ongSelecionada['cd_distancia_km'] !== null ? $escapar($ongSelecionada['cd_distancia_km']) . ' km' : 'Distância não informada' ?></p>
                </div>
                <div class="detail-section">
                  <h3>Doações Aceitas</h3>
                  <p id="detailsDonations"><?= nl2br($escapar($ongSelecionada['ds_doacoes'] ?: 'Não informado'), false) ?></p>
                </div>
                <div class="detail-section">
                  <h3>Sobre</h3>
                  <p id="detailsAbout"><?= nl2br($escapar($ongSelecionada['ds_sobre'] ?: 'Não informado'), false) ?></p>
                </div>
                <div class="detail-section">
                  <h3>Missão</h3>
                  <p id="detailsMission"><?= nl2br($escapar($ongSelecionada['ds_missao'] ?: 'Não informada'), false) ?></p>
                </div>
                <div class="detail-section">
                  <h3>Projetos</h3>
                  <p id="detailsProjects"><?= nl2br($escapar($ongSelecionada['ds_projetos'] ?: 'Não informados'), false) ?></p>
                </div>
                <div class="detail-section">
                  <h3>Contato</h3>
                  <p id="detailsPhone"><?= $escapar($ongSelecionada['cd_telefone_ong'] ?: 'Telefone não informado') ?></p>
                  <p id="detailsEmail"><?= $escapar($ongSelecionada['nm_email_ong'] ?: 'E-mail não informado') ?></p>
                </div>
              </div>
            <?php else: ?>
              <p class="details-placeholder">Nenhuma ONG cadastrada.</p>
            <?php endif; ?>
          </section>
        </div>
      </section>
    </div>
  </main>

  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>
    <script src="script.js"></script>
</body>
</html>
