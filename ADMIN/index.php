<?php
declare(strict_types=1);

ini_set("session.use_strict_mode", "1");
session_set_cookie_params([
    "httponly" => true,
    "secure" => !empty($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] !== "off",
    "samesite" => "Lax",
]);
session_start();

require_once __DIR__ . "/../BD/conexao.php";
require_once __DIR__ . "/../BD/criarTabelas.php";

$adminEmail = getenv("REUNE_ADMIN_EMAIL");
$adminPassword = getenv("REUNE_ADMIN_PASSWORD");
$adminConfigured = is_string($adminEmail)
    && filter_var($adminEmail, FILTER_VALIDATE_EMAIL) !== false
    && is_string($adminPassword)
    && strlen($adminPassword) >= 12;

if (!isset($_SESSION["token_admin"])) {
    $_SESSION["token_admin"] = bin2hex(random_bytes(32));
}

$escape = static fn ($value): string => htmlspecialchars(
    (string) ($value ?? ""),
    ENT_QUOTES,
    "UTF-8"
);
$tiposConta = [
    "ong" => [
        "tabela" => "ONG",
        "id" => "id_ONG",
        "chave_doacao" => "ONG_id_ONG",
        "nome" => "ONG",
    ],
    "doador" => [
        "tabela" => "DOADOR",
        "id" => "id_Doador",
        "chave_doacao" => "DOADOR_id_Doador",
        "nome" => "doador",
    ],
];
$erro = null;
$sucesso = $_SESSION["admin_sucesso"] ?? null;
unset($_SESSION["admin_sucesso"]);
$autenticado = ($_SESSION["admin_autenticado"] ?? false) === true;
$view = $_GET["view"] ?? "inicio";
if (!in_array($view, ["inicio", "doacoes", "ongs", "doadores"], true)) {
    $view = "inicio";
}

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    $token = $_POST["token_admin"] ?? "";

    if (!is_string($token) || !hash_equals($_SESSION["token_admin"], $token)) {
        $erro = "Não foi possível validar a solicitação. Atualize a página e tente novamente.";
    } elseif ($acao === "login") {
        $emailInformado = $_POST["email"] ?? "";
        $senhaInformada = $_POST["senha"] ?? "";
        if (
            $adminConfigured &&
            is_string($emailInformado) &&
            is_string($senhaInformada) &&
            hash_equals($adminEmail, $emailInformado) &&
            hash_equals($adminPassword, $senhaInformada)
        ) {
            session_regenerate_id(true);
            $_SESSION["admin_autenticado"] = true;
            $_SESSION["token_admin"] = bin2hex(random_bytes(32));
            header("Location: index.php");
            exit;
        }
        $erro = "E-mail ou senha incorretos.";
    } elseif (!$autenticado) {
        http_response_code(403);
        $erro = "Faça login para acessar esta página.";
    } elseif ($acao === "sair") {
        unset($_SESSION["admin_autenticado"]);
        session_regenerate_id(true);
        $_SESSION["token_admin"] = bin2hex(random_bytes(32));
        header("Location: index.php");
        exit;
    } elseif ($acao === "atualizar_doacao") {
        $idInformado = $_POST["doacao_id"] ?? "";
        $idDoacao = is_string($idInformado)
            ? filter_var($idInformado, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])
            : false;
        $status = $_POST["status"] ?? "";
        $viewInformada = $_POST["view"] ?? "doacoes";
        $viewRetorno = in_array($viewInformada, ["inicio", "doacoes"], true)
            ? $viewInformada
            : "doacoes";

        if ($idDoacao === false || !in_array($status, ["Pendente", "Finalizada", "Rejeitada"], true)) {
            $erro = "A doação ou o status informado é inválido.";
        } else {
            try {
                $atualizacao = $conexao->prepare(
                    "UPDATE DOACAO SET nm_status_doacao = :status WHERE id_DOACAO = :id"
                );
                $atualizacao->execute([":status" => $status, ":id" => $idDoacao]);

                if ($atualizacao->rowCount() === 0) {
                    $consulta = $conexao->prepare("SELECT 1 FROM DOACAO WHERE id_DOACAO = :id");
                    $consulta->execute([":id" => $idDoacao]);
                    if (!$consulta->fetchColumn()) {
                        $erro = "A doação selecionada não foi encontrada.";
                    }
                }

                if ($erro === null) {
                    $_SESSION["admin_sucesso"] = "Status da doação atualizado para {$status}.";
                    header("Location: index.php?view=" . urlencode($viewRetorno));
                    exit;
                }
            } catch (PDOException $exception) {
                error_log("Erro ao atualizar status da doação no painel administrativo: " . $exception->getMessage());
                $erro = "Não foi possível atualizar o status da doação. Tente novamente.";
            }
        }
        $view = $viewRetorno;
    } elseif ($acao === "excluir_conta") {
        $tipoConta = $_POST["tipo_conta"] ?? "";
        $idInformado = $_POST["conta_id"] ?? "";
        $confirmacao = $_POST["confirmacao"] ?? "";
        $idConta = is_string($idInformado)
            ? filter_var($idInformado, FILTER_VALIDATE_INT, ["options" => ["min_range" => 1]])
            : false;

        if (!is_string($tipoConta) || !isset($tiposConta[$tipoConta]) || $idConta === false) {
            $erro = "A conta selecionada é inválida.";
        } elseif (!is_string($confirmacao) || $confirmacao !== "CONFIRMAR") {
            $erro = "Digite CONFIRMAR para confirmar a exclusão.";
        } else {
            $configuracaoConta = $tiposConta[$tipoConta];
            $viewRetorno = $tipoConta === "ong" ? "ongs" : "doadores";

            try {
                $conexao->beginTransaction();
                $consultaConta = $conexao->prepare(
                    "SELECT {$configuracaoConta["id"]}
                     FROM {$configuracaoConta["tabela"]}
                     WHERE {$configuracaoConta["id"]} = :id
                     FOR UPDATE"
                );
                $consultaConta->execute([":id" => $idConta]);

                if (!$consultaConta->fetchColumn()) {
                    $conexao->rollBack();
                    $erro = "A conta selecionada não foi encontrada.";
                } else {
                    $consultaDoacoes = $conexao->prepare(
                        "SELECT id_DOACAO FROM DOACAO
                         WHERE {$configuracaoConta["chave_doacao"]} = :id"
                        . " FOR UPDATE"
                    );
                    $consultaDoacoes->execute([":id" => $idConta]);
                    $totalDoacoesConta = 0;
                    while ($consultaDoacoes->fetch(PDO::FETCH_COLUMN) !== false) {
                        $totalDoacoesConta++;
                    }

                    $exclusaoEntregas = $conexao->prepare(
                        "DELETE FROM ENTREGA
                         WHERE DOACAO_id_DOACAO IN (
                             SELECT id_DOACAO FROM DOACAO
                             WHERE {$configuracaoConta["chave_doacao"]} = :id
                         )"
                    );
                    $exclusaoEntregas->execute([":id" => $idConta]);

                    $exclusaoDoacoes = $conexao->prepare(
                        "DELETE FROM DOACAO
                         WHERE {$configuracaoConta["chave_doacao"]} = :id"
                    );
                    $exclusaoDoacoes->execute([":id" => $idConta]);

                    $exclusaoConta = $conexao->prepare(
                        "DELETE FROM {$configuracaoConta["tabela"]}
                         WHERE {$configuracaoConta["id"]} = :id"
                    );
                    $exclusaoConta->execute([":id" => $idConta]);
                    if ($exclusaoConta->rowCount() !== 1) {
                        $conexao->rollBack();
                        $erro = "Não foi possível localizar a conta para exclusão.";
                    } else {
                        $conexao->commit();
                        $detalheDoacoes = match ($totalDoacoesConta) {
                            0 => "",
                            1 => " Uma doação e as entregas vinculadas também foram removidas.",
                            default => " {$totalDoacoesConta} doações e as entregas vinculadas também foram removidas.",
                        };
                        $_SESSION["admin_sucesso"] = "Conta de {$configuracaoConta["nome"]} excluída com sucesso.{$detalheDoacoes}";
                        header("Location: index.php?view=" . urlencode($viewRetorno));
                        exit;
                    }
                }
            } catch (PDOException $exception) {
                if ($conexao->inTransaction()) {
                    $conexao->rollBack();
                }
                error_log("Erro ao excluir conta pelo painel administrativo: " . $exception->getMessage());
                if ($exception->getCode() === "23000") {
                    $erro = "Não foi possível excluir a conta e seu histórico vinculado. Nenhum dado foi alterado.";
                } else {
                    $erro = "Não foi possível excluir a conta. Tente novamente.";
                }
            }
            $view = $viewRetorno;
        }
    } else {
        $erro = "A ação solicitada é inválida.";
    }
}

$estatisticas = null;
$doacoes = [];
$ongs = [];
$doadores = [];
$filtroStatus = $_GET["status"] ?? "";
if (!in_array($filtroStatus, ["", "Pendente", "Finalizada", "Rejeitada"], true)) {
    $filtroStatus = "";
}

if ($autenticado) {
    $consulta = $conexao->query(
        "SELECT
            (SELECT COUNT(*) FROM ONG) AS total_ongs,
            (SELECT COUNT(*) FROM DOADOR) AS total_doadores,
            (SELECT COUNT(*) FROM DOACAO) AS total_doacoes,
            (SELECT COUNT(*) FROM DOACAO WHERE nm_status_doacao = 'Pendente') AS doacoes_pendentes"
    );
    $estatisticas = $consulta->fetch(PDO::FETCH_ASSOC);

    if ($view === "doacoes" || $view === "inicio") {
        $limite = $view === "inicio" ? 8 : 100;
        $sqlDoacoes = "SELECT d.id_DOACAO, d.nm_tipo_doacao, d.ds_doacao, d.nm_status_doacao,
                              d.dt_doacao, o.nm_ong, p.nm_doador
                       FROM DOACAO d
                       INNER JOIN ONG o ON o.id_ONG = d.ONG_id_ONG
                       INNER JOIN DOADOR p ON p.id_Doador = d.DOADOR_id_Doador";
        $parametros = [];
        if ($view === "doacoes" && $filtroStatus !== "") {
            $sqlDoacoes .= " WHERE d.nm_status_doacao = :status";
            $parametros[":status"] = $filtroStatus;
        }
        $sqlDoacoes .= " ORDER BY d.id_DOACAO DESC LIMIT " . $limite;
        $consulta = $conexao->prepare($sqlDoacoes);
        $consulta->execute($parametros);
        $doacoes = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($view === "ongs") {
        $consulta = $conexao->query(
            "SELECT o.id_ONG, o.nm_ong, o.nm_email_ong, o.cd_telefone_ong, o.cd_cnpj,
                    COUNT(d.id_DOACAO) AS total_doacoes
             FROM ONG o
             LEFT JOIN DOACAO d ON d.ONG_id_ONG = o.id_ONG
             GROUP BY o.id_ONG, o.nm_ong, o.nm_email_ong, o.cd_telefone_ong, o.cd_cnpj
             ORDER BY o.nm_ong"
        );
        $ongs = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }

    if ($view === "doadores") {
        $consulta = $conexao->query(
            "SELECT p.id_Doador, p.nm_doador, p.nm_email_doador, p.cd_telefone_doador,
                    COUNT(d.id_DOACAO) AS total_doacoes
             FROM DOADOR p
             LEFT JOIN DOACAO d ON d.DOADOR_id_Doador = p.id_Doador
             GROUP BY p.id_Doador, p.nm_doador, p.nm_email_doador, p.cd_telefone_doador
             ORDER BY p.nm_doador"
        );
        $doadores = $consulta->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="robots" content="noindex, nofollow">
    <title>Painel administrativo - REUNE+</title>
    <link rel="icon" href="../resource/logo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="admin.css">
</head>
<body class="admin-page">
<?php if (!$autenticado): ?>
    <main class="login-shell">
        <a class="brand" href="../index.php"><img src="../resource/logo.jpeg" alt=""> REUNE<b>+</b></a>
        <section class="login-card">
            <p class="eyebrow">Área restrita</p>
            <h1>Painel administrativo</h1>
            <p class="muted">Entre com as credenciais de administração para continuar.</p>
            <?php if (!$adminConfigured): ?>
                <p class="notice notice-error" role="alert">
                    O acesso administrativo ainda não foi configurado. Defina
                    <code>REUNE_ADMIN_EMAIL</code> e <code>REUNE_ADMIN_PASSWORD</code>
                    no ambiente do servidor; a senha deve ter pelo menos 12 caracteres.
                </p>
            <?php endif; ?>
            <?php if ($erro !== null): ?>
                <p class="notice notice-error" role="alert"><?= $escape($erro) ?></p>
            <?php endif; ?>
            <form method="post" class="login-form">
                <input type="hidden" name="acao" value="login">
                <input type="hidden" name="token_admin" value="<?= $escape($_SESSION["token_admin"]) ?>">
                <label for="email">E-mail administrativo</label>
                <input id="email" name="email" type="email" autocomplete="username" required>
                <label for="senha">Senha</label>
                <input id="senha" name="senha" type="password" autocomplete="current-password" required>
                <button class="button button-primary" type="submit" <?= !$adminConfigured ? "disabled" : "" ?>>Entrar</button>
            </form>
            <a class="back-link" href="../index.php">Voltar ao site</a>
        </section>
    </main>
<?php else: ?>
    <div class="admin-layout">
        <aside class="sidebar">
            <a class="brand" href="index.php"><img src="../resource/logo.jpeg" alt=""> REUNE<b>+</b></a>
            <p class="sidebar-label">ADMINISTRAÇÃO</p>
            <nav aria-label="Navegação administrativa">
                <a class="<?= $view === "inicio" ? "active" : "" ?>" href="index.php">Visão geral</a>
                <a class="<?= $view === "doacoes" ? "active" : "" ?>" href="index.php?view=doacoes">Doações</a>
                <a class="<?= $view === "ongs" ? "active" : "" ?>" href="index.php?view=ongs">ONGs</a>
                <a class="<?= $view === "doadores" ? "active" : "" ?>" href="index.php?view=doadores">Doadores</a>
            </nav>
            <a class="sidebar-site-link" href="../index.php">Voltar ao site</a>
            <form method="post" class="logout-form">
                <input type="hidden" name="acao" value="sair">
                <input type="hidden" name="token_admin" value="<?= $escape($_SESSION["token_admin"]) ?>">
                <button type="submit">Sair do painel</button>
            </form>
        </aside>

        <main class="admin-main">
            <header class="page-header">
                <div>
                    <p class="eyebrow">REUNE+ / ADMINISTRAÇÃO</p>
                    <h1><?= $view === "ongs" ? "Organizações" : ($view === "doadores" ? "Doadores" : ($view === "doacoes" ? "Doações" : "Visão geral")) ?></h1>
                </div>
                <span class="admin-badge">Administrador</span>
            </header>

            <?php if ($erro !== null): ?>
                <p class="notice notice-error" role="alert"><?= $escape($erro) ?></p>
            <?php endif; ?>
            <?php if (is_string($sucesso)): ?>
                <p class="notice notice-success" role="status"><?= $escape($sucesso) ?></p>
            <?php endif; ?>

            <section class="stats-grid" aria-label="Indicadores da plataforma">
                <article class="stat-card"><span>ONGs cadastradas</span><strong><?= (int) $estatisticas["total_ongs"] ?></strong></article>
                <article class="stat-card"><span>Doadores cadastrados</span><strong><?= (int) $estatisticas["total_doadores"] ?></strong></article>
                <article class="stat-card"><span>Total de doações</span><strong><?= (int) $estatisticas["total_doacoes"] ?></strong></article>
                <article class="stat-card stat-highlight"><span>Doações pendentes</span><strong><?= (int) $estatisticas["doacoes_pendentes"] ?></strong></article>
            </section>

            <?php if ($view === "ongs"): ?>
                <section class="panel-card">
                    <div class="section-heading">
                        <div><h2>ONGs cadastradas</h2><p>Organizações registradas na plataforma.</p></div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Organização</th><th>E-mail</th><th>Telefone</th><th>CNPJ</th><th>Doações</th><th>Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($ongs as $ong): ?>
                                <tr>
                                    <td><strong><?= $escape($ong["nm_ong"]) ?></strong></td>
                                    <td><?= $escape($ong["nm_email_ong"] ?: "—") ?></td>
                                    <td><?= $escape($ong["cd_telefone_ong"] ?: "—") ?></td>
                                    <td><?= $escape($ong["cd_cnpj"] ?: "—") ?></td>
                                    <td><?= (int) $ong["total_doacoes"] ?></td>
                                    <td>
                                        <button
                                            class="button button-small button-danger"
                                            type="button"
                                            data-delete-account
                                            data-account-type="ong"
                                            data-account-id="<?= (int) $ong["id_ONG"] ?>"
                                            data-account-name="<?= $escape($ong["nm_ong"]) ?>"
                                            data-donation-count="<?= (int) $ong["total_doacoes"] ?>"
                                            aria-label="Excluir conta da ONG <?= $escape($ong["nm_ong"]) ?>">
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($ongs === []): ?><tr><td class="empty-state" colspan="6">Nenhuma ONG cadastrada.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php elseif ($view === "doadores"): ?>
                <section class="panel-card">
                    <div class="section-heading">
                        <div><h2>Doadores cadastrados</h2><p>Contas de doadores registradas na plataforma.</p></div>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Doador</th><th>E-mail</th><th>Telefone</th><th>Doações</th><th>Ações</th></tr></thead>
                            <tbody>
                            <?php foreach ($doadores as $doador): ?>
                                <tr>
                                    <td><strong><?= $escape($doador["nm_doador"]) ?></strong></td>
                                    <td><?= $escape($doador["nm_email_doador"] ?: "—") ?></td>
                                    <td><?= $escape($doador["cd_telefone_doador"] ?: "—") ?></td>
                                    <td><?= (int) $doador["total_doacoes"] ?></td>
                                    <td>
                                        <button
                                            class="button button-small button-danger"
                                            type="button"
                                            data-delete-account
                                            data-account-type="doador"
                                            data-account-id="<?= (int) $doador["id_Doador"] ?>"
                                            data-account-name="<?= $escape($doador["nm_doador"]) ?>"
                                            data-donation-count="<?= (int) $doador["total_doacoes"] ?>"
                                            aria-label="Excluir conta do doador <?= $escape($doador["nm_doador"]) ?>">
                                            Excluir
                                        </button>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($doadores === []): ?><tr><td class="empty-state" colspan="5">Nenhum doador cadastrado.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </section>
            <?php elseif ($view === "doacoes" || $view === "inicio"): ?>
                <section class="panel-card">
                    <div class="section-heading">
                        <div>
                            <h2><?= $view === "inicio" ? "Doações recentes" : "Gerenciar doações" ?></h2>
                            <p><?= $view === "inicio" ? "Acompanhe as últimas movimentações." : "Atualize o status das doações registradas." ?></p>
                        </div>
                        <?php if ($view === "inicio"): ?>
                            <a class="text-link" href="index.php?view=doacoes">Ver todas</a>
                        <?php else: ?>
                            <form method="get" class="filter-form">
                                <input type="hidden" name="view" value="doacoes">
                                <label for="status-filter">Filtrar por status</label>
                                <select id="status-filter" name="status" onchange="this.form.submit()">
                                    <option value="">Todos</option>
                                    <?php foreach (["Pendente", "Finalizada", "Rejeitada"] as $opcaoStatus): ?>
                                        <option value="<?= $escape($opcaoStatus) ?>" <?= $filtroStatus === $opcaoStatus ? "selected" : "" ?>><?= $escape($opcaoStatus) ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </form>
                        <?php endif; ?>
                    </div>
                    <div class="table-wrap">
                        <table>
                            <thead><tr><th>Doação</th><th>Doador</th><th>ONG</th><th>Data</th><th>Status</th><th>Atualizar</th></tr></thead>
                            <tbody>
                            <?php foreach ($doacoes as $doacao): ?>
                                <?php $statusAtual = (string) ($doacao["nm_status_doacao"] ?? ""); ?>
                                <tr>
                                    <td><strong><?= $escape($doacao["nm_tipo_doacao"] ?: "Doação") ?></strong><small>Quantidade: <?= (int) ($doacao["ds_doacao"] ?? 0) ?></small></td>
                                    <td><?= $escape($doacao["nm_doador"]) ?></td>
                                    <td><?= $escape($doacao["nm_ong"]) ?></td>
                                    <td><?= $escape($doacao["dt_doacao"] ? date("d/m/Y", strtotime((string) $doacao["dt_doacao"])) : "—") ?></td>
                                    <td><span class="status-pill status-<?= strtolower($escape($statusAtual)) ?>"><?= $escape($statusAtual ?: "Sem status") ?></span></td>
                                    <td>
                                        <form method="post" class="status-form">
                                            <input type="hidden" name="acao" value="atualizar_doacao">
                                            <input type="hidden" name="token_admin" value="<?= $escape($_SESSION["token_admin"]) ?>">
                                            <input type="hidden" name="doacao_id" value="<?= (int) $doacao["id_DOACAO"] ?>">
                                            <input type="hidden" name="view" value="<?= $escape($view) ?>">
                                            <label class="sr-only" for="status-<?= (int) $doacao["id_DOACAO"] ?>">Novo status para doação <?= (int) $doacao["id_DOACAO"] ?></label>
                                            <select id="status-<?= (int) $doacao["id_DOACAO"] ?>" name="status">
                                                <?php foreach (["Pendente", "Finalizada", "Rejeitada"] as $opcaoStatus): ?>
                                                    <option value="<?= $escape($opcaoStatus) ?>" <?= $statusAtual === $opcaoStatus ? "selected" : "" ?>><?= $escape($opcaoStatus) ?></option>
                                                <?php endforeach; ?>
                                            </select>
                                            <button class="button button-small" type="submit">Salvar</button>
                                        </form>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                            <?php if ($doacoes === []): ?><tr><td class="empty-state" colspan="6">Nenhuma doação encontrada.</td></tr><?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                    <?php if ($view === "doacoes"): ?><p class="table-note">Exibindo até 100 doações mais recentes.</p><?php endif; ?>
                </section>
            <?php endif; ?>
            <dialog class="delete-modal" id="delete-account-modal" aria-labelledby="delete-modal-title">
                <div class="delete-modal-content">
                    <p class="eyebrow">Ação permanente</p>
                    <h2 id="delete-modal-title">Confirmar exclusão</h2>
                    <p id="delete-account-summary"></p>
                    <p class="delete-warning">Esta ação excluirá a conta e todas as doações e entregas vinculadas. Não será possível desfazê-la.</p>
                    <form id="delete-account-form" method="post">
                        <input type="hidden" name="acao" value="excluir_conta">
                        <input type="hidden" name="token_admin" value="<?= $escape($_SESSION["token_admin"]) ?>">
                        <input type="hidden" name="tipo_conta" id="delete-account-type">
                        <input type="hidden" name="conta_id" id="delete-account-id">
                        <label for="delete-account-confirmation">Digite <strong>CONFIRMAR</strong> para habilitar a exclusão</label>
                        <input
                            id="delete-account-confirmation"
                            name="confirmacao"
                            type="text"
                            autocomplete="off"
                            maxlength="9"
                            pattern="CONFIRMAR"
                            required>
                        <div class="delete-modal-actions">
                            <button class="button button-cancel" type="button" data-close-delete-modal>Cancelar</button>
                            <button class="button button-danger" id="confirm-delete-button" type="submit" disabled>Excluir permanentemente</button>
                        </div>
                    </form>
                </div>
            </dialog>
            <footer class="admin-footer">© 2026 REUNE+ · Painel administrativo</footer>
        </main>
    </div>
    <script>
        const deleteModal = document.getElementById("delete-account-modal");
        const deleteForm = document.getElementById("delete-account-form");
        const deleteConfirmation = document.getElementById("delete-account-confirmation");
        const deleteSubmit = document.getElementById("confirm-delete-button");

        document.querySelectorAll("[data-delete-account]").forEach((button) => {
            button.addEventListener("click", () => {
                document.getElementById("delete-account-type").value = button.dataset.accountType;
                document.getElementById("delete-account-id").value = button.dataset.accountId;
                const donationCount = Number(button.dataset.donationCount);
                const donationText = donationCount === 1 ? "1 doação vinculada" : `${donationCount} doações vinculadas`;
                document.getElementById("delete-account-summary").textContent =
                    `Você está prestes a excluir ${button.dataset.accountType === "ong" ? "a ONG" : "o doador"} “${button.dataset.accountName}” (${donationText}).`;
                deleteConfirmation.value = "";
                deleteSubmit.disabled = true;
                deleteModal.showModal();
                deleteConfirmation.focus();
            });
        });

        deleteConfirmation.addEventListener("input", () => {
            deleteSubmit.disabled = deleteConfirmation.value !== "CONFIRMAR";
        });

        deleteForm.addEventListener("submit", (event) => {
            if (deleteConfirmation.value !== "CONFIRMAR") {
                event.preventDefault();
                deleteConfirmation.focus();
                deleteSubmit.disabled = true;
            }
        });

        document.querySelector("[data-close-delete-modal]").addEventListener("click", () => {
            deleteModal.close();
        });

        deleteModal.addEventListener("close", () => {
            deleteForm.reset();
            deleteSubmit.disabled = true;
        });
    </script>
<?php endif; ?>
</body>
</html>
