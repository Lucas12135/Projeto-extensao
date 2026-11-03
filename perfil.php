<?php
require_once __DIR__ . "/BD/conexao.php";
require_once __DIR__ . "/BD/criarTabelas.php";
require_once __DIR__ . "/PHP/FUNCOES/uploadImagem.php";

session_start();

// Descobre quem está conectado e quais dados pertencem a esse tipo de conta.
$isOng = isset($_SESSION["email-ong"]);
$emailSessao = $_SESSION[$isOng ? "email-ong" : "email-doador"] ?? null;

if (!is_string($emailSessao) || $emailSessao === "") {
    header("Location: login.php");
    exit;
}

if ($isOng) {
    $tabela = "ONG";
    $colunaId = "id_ONG";
    $colunaEnderecoDono = "ONG_id_ONG";
    $colunaLocalizacao = "ds_localizacao";
    $colunaEmail = "nm_email_ong";
    $colunaImagem = "nm_imagem_ong";
    $tipoPerfil = "Perfil da organização";
    $campos = [
        "nome" => ["label" => "Nome da ONG", "coluna" => "nm_ong", "tipo" => "text", "max" => 30],
        "email" => ["label" => "E-mail", "coluna" => $colunaEmail, "tipo" => "email", "max" => 50],
        "telefone" => ["label" => "Telefone", "coluna" => "cd_telefone_ong", "tipo" => "tel", "max" => 20],
        "doacoes" => ["label" => "Doações aceitas", "coluna" => "ds_doacoes", "tipo" => "textarea"],
        "sobre" => ["label" => "Sobre", "coluna" => "ds_sobre", "tipo" => "textarea"],
        "missao" => ["label" => "Missão", "coluna" => "ds_missao", "tipo" => "textarea"],
        "projetos" => ["label" => "Projetos", "coluna" => "ds_projetos", "tipo" => "textarea"],
        "cnpj" => ["label" => "CNPJ", "coluna" => "cd_cnpj", "tipo" => "text", "max" => 18],
    ];
} else {
    $tabela = "DOADOR";
    $colunaId = "id_Doador";
    $colunaEnderecoDono = "DOADOR_id_Doador";
    $colunaLocalizacao = "ds_endereco_doador";
    $colunaEmail = "nm_email_doador";
    $colunaImagem = "nm_imagem_doador";
    $tipoPerfil = "Perfil do doador";
    $campos = [
        "nome" => ["label" => "Nome completo", "coluna" => "nm_doador", "tipo" => "text", "max" => 50],
        "email" => ["label" => "E-mail", "coluna" => $colunaEmail, "tipo" => "email", "max" => 50],
        "telefone" => ["label" => "Telefone", "coluna" => "cd_telefone_doador", "tipo" => "tel", "max" => 20],
        "cpf" => ["label" => "CPF", "coluna" => "cd_cpf_doador", "tipo" => "text", "max" => 14],
    ];
}

$camposEndereco = [
    "cep" => ["label" => "CEP", "coluna" => "cd_cep", "tipo" => "text", "max" => 9],
];
$camposPerfil = array_merge($campos, $camposEndereco);

$mensagem = null;
$sucesso = false;

// Processa a alteração de um campo ou da foto.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    if ($acao === "atualizar_campo") {
        $chaveCampo = $_POST["campo"] ?? "";
        $valor = $_POST["valor"] ?? null;

        if (!is_string($chaveCampo) || !isset($camposPerfil[$chaveCampo]) || !is_string($valor)) {
            $mensagem = "Não foi possível identificar o dado para edição.";
        } else {
            $campo = $camposPerfil[$chaveCampo];
            $valor = trim($valor);
            if ($chaveCampo === "uf") {
                $valor = strtoupper($valor);
            }
            $quantidadeCaracteres = preg_match_all("/./us", $valor);

            if ($valor === "" && empty($campo["opcional"])) {
                $mensagem = "Este campo não pode ficar vazio.";
            } elseif ($quantidadeCaracteres === false) {
                $mensagem = "O valor informado contém caracteres inválidos.";
            } elseif (isset($campo["max"]) && $quantidadeCaracteres > $campo["max"]) {
                $mensagem = "O valor informado é muito longo.";
            } elseif ($campo["tipo"] === "email" && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                $mensagem = "Informe um e-mail válido.";
            } else {
                try {
                    $emailEmUso = false;

                    if ($chaveCampo === "email") {
                        $consultaEmail = $conexao->prepare(
                            "SELECT 1 FROM $tabela
                             WHERE $colunaEmail = :novo_email AND $colunaEmail <> :email_atual"
                        );
                        $consultaEmail->execute([
                            ":novo_email" => $valor,
                            ":email_atual" => $emailSessao,
                        ]);
                        $emailEmUso = (bool) $consultaEmail->fetchColumn();
                    }

                    if ($emailEmUso) {
                        $mensagem = "Este e-mail já está sendo usado por outra conta.";
                    } elseif (isset($camposEndereco[$chaveCampo])) {
                        $consultaId = $conexao->prepare(
                            "SELECT $colunaId FROM $tabela WHERE $colunaEmail = :email"
                        );
                        $consultaId->execute([":email" => $emailSessao]);
                        $idPerfil = $consultaId->fetchColumn();

                        $consultaEndereco = $conexao->prepare(
                            "SELECT id_ENDERECO FROM ENDERECO WHERE $colunaEnderecoDono = :id"
                        );
                        $consultaEndereco->execute([":id" => $idPerfil]);

                        if ($consultaEndereco->fetchColumn() === false) {
                            $mensagem = "O endereço deste perfil não foi encontrado.";
                        } else {
                            $conexao->beginTransaction();
                            $atualizacaoEndereco = $conexao->prepare(
                                "UPDATE ENDERECO SET {$campo["coluna"]} = :valor
                                 WHERE $colunaEnderecoDono = :id"
                            );
                            $atualizacaoEndereco->execute([
                                ":valor" => $valor === "" ? null : $valor,
                                ":id" => $idPerfil,
                            ]);

                            $consultaEndereco = $conexao->prepare(
                                "SELECT ds_logradouro, nr_endereco, ds_complemento,
                                        nm_bairro, nm_cidade, sg_uf, cd_cep
                                 FROM ENDERECO WHERE $colunaEnderecoDono = :id"
                            );
                            $consultaEndereco->execute([":id" => $idPerfil]);
                            $endereco = $consultaEndereco->fetch(PDO::FETCH_ASSOC);
                            $localizacao = implode(", ", array_filter(
                                $endereco,
                                static fn($parte) => $parte !== null && $parte !== ""
                            ));
                            $atualizacaoLocalizacao = $conexao->prepare(
                                "UPDATE $tabela SET $colunaLocalizacao = :localizacao
                                 WHERE $colunaId = :id"
                            );
                            $atualizacaoLocalizacao->execute([
                                ":localizacao" => $localizacao,
                                ":id" => $idPerfil,
                            ]);
                            $conexao->commit();

                            $mensagem = "Endereço atualizado com sucesso.";
                            $sucesso = true;
                        }
                    } else {
                        $atualizacao = $conexao->prepare(
                            "UPDATE $tabela SET {$campo["coluna"]} = :valor
                             WHERE $colunaEmail = :email_atual"
                        );
                        $atualizacao->execute([
                            ":valor" => $valor,
                            ":email_atual" => $emailSessao,
                        ]);

                        if ($chaveCampo === "email") {
                            $_SESSION[$isOng ? "email-ong" : "email-doador"] = $valor;
                            $emailSessao = $valor;
                        }

                        $mensagem = "Dado atualizado com sucesso.";
                        $sucesso = true;
                    }
                } catch (PDOException $erro) {
                    if ($conexao->inTransaction()) {
                        $conexao->rollBack();
                    }
                    error_log("Erro ao atualizar perfil: " . $erro->getMessage());
                    $mensagem = "Não foi possível atualizar este dado. Tente novamente.";
                }
            }
        }
    } elseif ($acao === "atualizar_imagem") {
        $upload = salvarImagemCadastro($_FILES["imagem-perfil"] ?? null, true);

        if (!$upload["sucesso"]) {
            $mensagem = $upload["mensagem"];
        } else {
            try {
                $atualizacao = $conexao->prepare(
                    "UPDATE $tabela SET $colunaImagem = :imagem WHERE $colunaEmail = :email"
                );
                $atualizacao->execute([
                    ":imagem" => $upload["caminho"],
                    ":email" => $emailSessao,
                ]);
                $mensagem = "Foto de perfil atualizada com sucesso.";
                $sucesso = true;
            } catch (PDOException $erro) {
                removerImagemCadastro($upload["arquivo"]);
                error_log("Erro ao atualizar foto de perfil: " . $erro->getMessage());
                $mensagem = "Não foi possível atualizar a foto. Tente novamente.";
            }
        }
    } else {
        $mensagem = "Ação de edição inválida.";
    }
}

// Busca os dados mais recentes para exibir na página.
$colunas = array_column($campos, "coluna");
$colunas[] = $colunaImagem;
$consulta = $conexao->prepare(
    "SELECT " . implode(", ", array_map(
        static fn($coluna) => "perfil.$coluna",
        $colunas
    )) . ",
        endereco.cd_cep AS cd_cep,
        endereco.ds_logradouro AS ds_logradouro,
        endereco.nr_endereco AS nr_endereco,
        endereco.ds_complemento AS ds_complemento,
        endereco.nm_bairro AS nm_bairro,
        endereco.nm_cidade AS nm_cidade,
        endereco.sg_uf AS sg_uf
     FROM $tabela perfil
     LEFT JOIN ENDERECO endereco ON endereco.$colunaEnderecoDono = perfil.$colunaId
     WHERE perfil.$colunaEmail = :email"
);
$consulta->execute([":email" => $emailSessao]);
$perfil = $consulta->fetch(PDO::FETCH_ASSOC);

if (!$perfil) {
    unset($_SESSION[$isOng ? "email-ong" : "email-doador"]);
    header("Location: login.php");
    exit;
}

function escaparPerfil(string $valor): string
{
    return htmlspecialchars($valor, ENT_QUOTES, "UTF-8");
}

$imagem = $perfil[$colunaImagem] ?: "resource/logo.jpeg";
$nome = $perfil[$campos["nome"]["coluna"]] ?? "Meu perfil";
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="icon" href="resource/logo.jpeg" type="image/x-icon">
    <link rel="stylesheet" href="style.css">
    <title>Meu perfil - REUNE+</title>
</head>

<body class="profile-page">
    <header>
        <div class="container">
            <div class="topbar">
                <a class="brand" href="index.php"><img src="resource/logo.jpeg" alt="Ícone de REUNE+" class="logo">
                    REUNE<b>+</b>
                </a>
                <nav class="nav-links" aria-label="Navegação principal">
                    <a href="index.php">Sobre nós</a>
                    <a href="ongs.php">ONGs Parceiras</a>
                    <a href="contato.php">Contato</a>
                </nav>
            </div>
        </div>
    </header>

    <main class="profile-main">
        <section class="profile-container" aria-labelledby="profile-title">
            <?php if ($mensagem !== null): ?>
                <p class="profile-notice<?= $sucesso ? " is-success" : " is-error" ?>" role="status">
                    <?= escaparPerfil($mensagem) ?>
                </p>
            <?php endif; ?>

            <div class="profile-header-card">
                <div class="profile-avatar-area">
                    <div class="profile-image-container">
                        <img src="<?= escaparPerfil($imagem) ?>" alt="Foto de perfil de <?= escaparPerfil($nome) ?>">
                    </div>
                    <form class="profile-photo-form" method="POST" enctype="multipart/form-data">
                        <input type="hidden" name="acao" value="atualizar_imagem">
                        <label class="profile-photo-label" for="imagem-perfil">Alterar foto</label>
                        <input id="imagem-perfil" class="profile-photo-input" type="file" name="imagem-perfil" accept="image/jpeg,image/png,image/gif,image/webp" required>
                        <button class="profile-save-button" type="submit">Salvar foto</button>
                    </form>
                </div>
                <div class="profile-heading">
                    <span class="profile-eyebrow"><?= escaparPerfil($tipoPerfil) ?></span>
                    <h1 id="profile-title"><?= escaparPerfil($nome) ?></h1>
                    <p>Gerencie suas informações e mantenha seu perfil atualizado.</p>
                </div>
            </div>

            <section class="profile-details-card" aria-labelledby="profile-details-title">
                <div class="profile-section-heading">
                    <div>
                        <h2 id="profile-details-title">Dados do perfil</h2>
                        <p>Edite cada informação individualmente.</p>
                    </div>
                </div>

                <div class="profile-info">
                    <?php foreach ($camposPerfil as $chave => $campo): ?>
                        <?php $id = "perfil-" . $chave; ?>
                        <form class="profile-field" method="POST">
                            <input type="hidden" name="acao" value="atualizar_campo">
                            <input type="hidden" name="campo" value="<?= escaparPerfil($chave) ?>">
                            <label class="profile-field-label" for="<?= escaparPerfil($id) ?>">
                                <?= escaparPerfil($campo["label"]) ?>
                            </label>

                            <?php if ($campo["tipo"] === "textarea"): ?>
                                <textarea id="<?= escaparPerfil($id) ?>" name="valor" disabled required><?= escaparPerfil((string) ($perfil[$campo["coluna"]] ?? "")) ?></textarea>
                            <?php else: ?>
                                <input id="<?= escaparPerfil($id) ?>" name="valor" type="<?= escaparPerfil($campo["tipo"]) ?>"
                                    value="<?= escaparPerfil((string) ($perfil[$campo["coluna"]] ?? "")) ?>"
                                    maxlength="<?= (int) $campo["max"] ?>" disabled <?= empty($campo["opcional"]) ? "required" : "" ?>>
                            <?php endif; ?>

                            <div class="profile-field-actions">
                                <button type="button" class="profile-edit-button" data-profile-edit>Editar</button>
                            </div>
                        </form>
                    <?php endforeach; ?>
                </div>
                <div class="profile-section-footer">
                    <form class="profile-logout-form" method="POST" action="./PHP/logout.php">
                        <button type="submit" class="profile-logout-button">Sair da conta</button>
                    </form>
                    <form class="profile-delete-form" method="POST" action="./PHP/excluirConta.php">
                        <button type="submit" class="profile-delete-button">Excluir conta
                            <svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" fill="currentColor" class="bi bi-trash3-fill" viewBox="0 0 16 16">
                                <path d="M11 1.5v1h3.5a.5.5 0 0 1 0 1h-.538l-.853 10.66A2 2 0 0 1 11.115 16h-6.23a2 2 0 0 1-1.994-1.84L2.038 3.5H1.5a.5.5 0 0 1 0-1H5v-1A1.5 1.5 0 0 1 6.5 0h3A1.5 1.5 0 0 1 11 1.5m-5 0v1h4v-1a.5.5 0 0 0-.5-.5h-3a.5.5 0 0 0-.5.5M4.5 5.029l.5 8.5a.5.5 0 1 0 .998-.06l-.5-8.5a.5.5 0 1 0-.998.06m6.53-.528a.5.5 0 0 0-.528.47l-.5 8.5a.5.5 0 0 0 .998.058l.5-8.5a.5.5 0 0 0-.47-.528M8 4.5a.5.5 0 0 0-.5.5v8.5a.5.5 0 0 0 1 0V5a.5.5 0 0 0-.5-.5" />
                            </svg>
                        </button>
                    </form>
                </div>
            </section>
        </section>

        <dialog class="profile-modal" id="profile-edit-modal" aria-labelledby="profile-edit-modal-title">
            <div class="profile-modal-header">
                <h2 id="profile-edit-modal-title">Editar informação</h2>
                <button type="button" class="profile-modal-close" data-profile-modal-close aria-label="Fechar">&times;</button>
            </div>
            <div class="profile-modal-content" id="profile-edit-control"></div>
            <div class="profile-modal-actions">
                <button type="button" class="profile-modal-button profile-modal-cancel" data-profile-modal-close>Cancelar</button>
                <button type="button" class="profile-modal-button profile-modal-save" id="profile-edit-save">Salvar alterações</button>
            </div>
        </dialog>

        <dialog class="profile-modal profile-warning-modal" id="profile-logout-modal" aria-labelledby="profile-logout-title">
            <div class="profile-modal-header">
                <h2 id="profile-logout-title">Sair da conta?</h2>
                <button type="button" class="profile-modal-close" data-profile-modal-close aria-label="Fechar">&times;</button>
            </div>
            <p>Você precisará entrar novamente para acessar sua conta.</p>
            <div class="profile-modal-actions">
                <button type="button" class="profile-modal-button profile-modal-cancel" data-profile-modal-close>Continuar no perfil</button>
                <button type="button" class="profile-modal-button profile-modal-confirm" data-confirm-form="profile-logout-form">Sair da conta</button>
            </div>
        </dialog>

        <dialog class="profile-modal profile-warning-modal" id="profile-delete-modal" aria-labelledby="profile-delete-title">
            <div class="profile-modal-header">
                <h2 id="profile-delete-title">Excluir conta?</h2>
                <button type="button" class="profile-modal-close" data-profile-modal-close aria-label="Fechar">&times;</button>
            </div>
            <p>Esta ação é permanente. Seus dados de conta serão excluídos e não poderão ser recuperados.</p>
            <div class="profile-modal-actions">
                <button type="button" class="profile-modal-button profile-modal-cancel" data-profile-modal-close>Manter minha conta</button>
                <button type="button" class="profile-modal-button profile-modal-confirm" data-confirm-form="profile-delete-form">Excluir conta</button>
            </div>
        </dialog>
    </main>

    <footer>
        <p>© 2026 REUNE+. Juntos pela transformação social.</p>
    </footer>
    <?php include_once __DIR__ . "/PHP/acessibilidade.php"; ?>
    <script src="JS/perfil.js"></script>
</body>

</html>