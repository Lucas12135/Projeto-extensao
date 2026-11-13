<?php
session_start();
require_once __DIR__ . "/BD/conexao.php";
require_once __DIR__ . "/BD/criarTabelas.php";

$isOng = isset($_SESSION["email-ong"]) &&
  is_string($_SESSION["email-ong"]) &&
  $_SESSION["email-ong"] !== "";
$isDoador = !$isOng &&
  isset($_SESSION["email-doador"]) &&
  is_string($_SESSION["email-doador"]) &&
  $_SESSION["email-doador"] !== "";
if (!isset($_SESSION["token_doacao"])) {
  $_SESSION["token_doacao"] = bin2hex(random_bytes(32));
}

$mensagemDoacao = $_SESSION["mensagem_doacao"] ?? null;
$sucessoDoacao = $_SESSION["sucesso_doacao"] ?? false;
unset($_SESSION["mensagem_doacao"], $_SESSION["sucesso_doacao"]);
$tipoDoacao = "";
$quantidadeDoacao = "";
$ongSelecionadaId = filter_input(INPUT_GET, "ong", FILTER_VALIDATE_INT) ?: null;

$smnt = $conexao->prepare(
  "SELECT id_ONG, nm_ong, nm_email_ong, cd_telefone_ong, cd_distancia_km,
          ds_doacoes, ds_sobre, ds_missao, ds_projetos, ds_localizacao, nm_imagem_ong
   FROM ONG
   ORDER BY nm_ong"
);
$smnt->execute();
$ongs = $smnt->fetchAll(PDO::FETCH_ASSOC);
$ongSelecionada = null;
foreach ($ongs as $ong) {
  if ((int) $ong["id_ONG"] === $ongSelecionadaId) {
    $ongSelecionada = $ong;
    break;
  }
}
$ongSelecionada ??= $ongs[0] ?? null;

if ($_SERVER["REQUEST_METHOD"] === "POST" && ($_POST["acao"] ?? "") === "registrar_doacao") {
  $ongSelecionadaId = filter_input(INPUT_POST, "ong_id", FILTER_VALIDATE_INT) ?: null;
  $tipoDoacao = trim((string) ($_POST["tipo_doacao"] ?? ""));
  $quantidadeDoacao = trim((string) ($_POST["quantidade"] ?? ""));
  $caracteresTipo = preg_match_all("/./us", $tipoDoacao);

  if (!$isDoador) {
    $mensagemDoacao = "Entre com uma conta de doador para registrar uma doação.";
  } elseif (
    !isset($_POST["token_doacao"]) ||
    !is_string($_POST["token_doacao"]) ||
    !hash_equals($_SESSION["token_doacao"], $_POST["token_doacao"])
  ) {
    $mensagemDoacao = "Não foi possível validar o envio. Atualize a página e tente novamente.";
  } elseif (
    $ongSelecionadaId === null ||
    $tipoDoacao === "" ||
    $caracteresTipo === false ||
    $caracteresTipo > 50
  ) {
    $mensagemDoacao = "Informe a ONG e o tipo de doação (até 50 caracteres).";
  } elseif (
    filter_var($quantidadeDoacao, FILTER_VALIDATE_INT, [
      "options" => ["min_range" => 1, "max_range" => 2147483647],
    ]) === false
  ) {
    $mensagemDoacao = "Informe uma quantidade inteira maior que zero.";
  } else {
    try {
      $consultaDoador = $conexao->prepare(
        "SELECT id_Doador FROM DOADOR WHERE nm_email_doador = :email"
      );
      $consultaDoador->execute([":email" => $_SESSION["email-doador"]]);
      $idDoador = $consultaDoador->fetchColumn();

      $consultaOng = $conexao->prepare("SELECT 1 FROM ONG WHERE id_ONG = :id");
      $consultaOng->execute([":id" => $ongSelecionadaId]);

      if ($idDoador === false) {
        unset($_SESSION["email-doador"]);
        header("Location: login.php");
        exit;
      }

      if (!$consultaOng->fetchColumn()) {
        $mensagemDoacao = "A ONG selecionada não foi encontrada.";
      } else {
        $insercao = $conexao->prepare(
          "INSERT INTO DOACAO
            (ONG_id_ONG, DOADOR_id_Doador, nm_tipo_doacao, ds_doacao, nm_status_doacao, dt_doacao)
           VALUES
            (:ong, :doador, :tipo, :quantidade, 'Pendente', CURRENT_DATE)"
        );
        $insercao->execute([
          ":ong" => $ongSelecionadaId,
          ":doador" => $idDoador,
          ":tipo" => $tipoDoacao,
          ":quantidade" => (int) $quantidadeDoacao,
        ]);

        $_SESSION["mensagem_doacao"] = "Doação registrada com sucesso. A ONG poderá acompanhar sua solicitação.";
        $_SESSION["sucesso_doacao"] = true;
        header("Location: ongs.php?ong=" . $ongSelecionadaId . "#ongDetails");
        exit;
      }
    } catch (PDOException $erro) {
      error_log("Erro ao registrar doação: " . $erro->getMessage());
      $mensagemDoacao = "Não foi possível registrar a doação. Tente novamente.";
    }
  }

  foreach ($ongs as $ong) {
    if ((int) $ong["id_ONG"] === $ongSelecionadaId) {
      $ongSelecionada = $ong;
      break;
    }
  }
}

$escapar = static function ($valor) {
  return htmlspecialchars((string) ($valor ?? ''), ENT_QUOTES, 'UTF-8');
};

$smnt = $conexao->prepare("SELECT COUNT(id_ONG) AS qt FROM ONG");

$smnt->execute();
$qnt = $smnt->fetch(PDO::FETCH_ASSOC);
if (isset($_SESSION["email-ong"])) {
  $smnt = $conexao->prepare("SELECT nm_imagem_ong FROM ONG WHERE nm_email_ong = :email");
  $smnt->execute([':email' => $_SESSION["email-ong"]]);

  $tmp = $smnt->fetch(PDO::FETCH_ASSOC) ?: [];
  $img = $tmp['nm_imagem_ong'] ?? null;
} else if (isset($_SESSION["email-doador"])) {
  $smnt = $conexao->prepare("SELECT nm_imagem_doador FROM DOADOR WHERE nm_email_doador = :email");
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
          <a href="perfil.php">
            <?php
            if (!isset($_SESSION["email-doador"]) && (!isset($_SESSION["email-ong"]))) {
              echo "<svg xmlns='http://www.w3.org/2000/svg' width='50' height='50' fill='currentColor' class='bi bi-person-circle' viewBox='0 0 16 16'>
  <path d='M11 6a3 3 0 1 1-6 0 3 3 0 0 1 6 0'/>
  <path fill-rule='evenodd' d='M0 8a8 8 0 1 1 16 0A8 8 0 0 1 0 8m8-7a7 7 0 0 0-5.468 11.37C3.242 11.226 4.805 10 8 10s4.757 1.225 5.468 2.37A7 7 0 0 0 8 1'/>
</svg>";
            } else {
              echo '<img class="profile-image" src="' . htmlspecialchars($imagemPerfil, ENT_QUOTES, 'UTF-8') . '" alt="Imagem do perfil">';
            }
            ?>
          </a>
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

        <?php if ($mensagemDoacao !== null): ?>
          <p class="donation-notice<?= $sucessoDoacao ? ' is-success' : ' is-error' ?>" role="status">
            <?= $escapar($mensagemDoacao) ?>
          </p>
        <?php endif; ?>

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
                $selecionada = (int) $ong['id_ONG'] === (int) ($ongSelecionada['id_ONG'] ?? 0);
                ?>
                <button
                  type="button"
                  class="ong-card<?= $selecionada ? ' active' : '' ?>"
                  data-id="<?= (int) $ong['id_ONG'] ?>"
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
                  aria-pressed="<?= $selecionada ? 'true' : 'false' ?>">
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

              <section class="donation-section" aria-labelledby="donation-title">
                <h3 id="donation-title">Quero fazer uma doação</h3>
                <p>Confira as doações aceitas pela ONG antes de enviar sua solicitação. A doação será registrada como pendente para alinhamento com a organização.</p>
                <?php if ($isDoador): ?>
                  <form class="donation-form" method="POST">
                    <input type="hidden" name="acao" value="registrar_doacao">
                    <input type="hidden" name="token_doacao" value="<?= $escapar($_SESSION['token_doacao']) ?>">
                    <input type="hidden" name="ong_id" id="donation-ong-id" value="<?= (int) $ongSelecionada['id_ONG'] ?>">
                    <label for="donation-type">O que deseja doar?</label>
                    <input id="donation-type" name="tipo_doacao" type="text" maxlength="50" placeholder="Ex.: arroz (kg), roupas (unidades)" value="<?= $escapar($tipoDoacao) ?>" required>
                    <label for="donation-quantity">Quantidade</label>
                    <input id="donation-quantity" name="quantidade" type="number" min="1" max="2147483647" value="<?= $escapar($quantidadeDoacao) ?>" required>
                    <button class="btn btn-primary" type="submit">Registrar doação</button>
                  </form>
                <?php elseif (!$isOng): ?>
                  <p><a class="btn btn-primary donation-login-link" href="login.php">Entre como doador para doar</a></p>
                <?php else: ?>
                  <p>Somente contas de doador podem registrar doações.</p>
                <?php endif; ?>
              </section>
            <?php else: ?>
              <p class="details-placeholder">Nenhuma ONG cadastrada.</p>
            <?php endif; ?>
          </section>
        </div>
      </section>
    </div>
  </main>

  <?php include __DIR__ . "/PHP/acessibilidade.php"; ?>

  <footer>
    <p>© 2026 REUNE+. Juntos pela transformação social.</p>
  </footer>
  <script src="JS/cardOng.js"></script>
</body>

</html>