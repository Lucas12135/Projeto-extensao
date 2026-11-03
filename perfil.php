<?php
require_once __DIR__ . "/BD/conexao.php";
require_once __DIR__ . "/PHP/uploadImagem.php";

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
    $colunaEmail = "nm_email_ong";
    $colunaImagem = "nm_imagem_ong";
    $tipoPerfil = "Perfil da organização";
    $campos = [
        "nome" => ["label" => "Nome da ONG", "coluna" => "nm_ong", "tipo" => "text", "max" => 30],
        "email" => ["label" => "E-mail", "coluna" => $colunaEmail, "tipo" => "email", "max" => 50],
        "telefone" => ["label" => "Telefone", "coluna" => "cd_telefone_ong", "tipo" => "tel", "max" => 20],
        "localizacao" => ["label" => "Localização", "coluna" => "ds_localizacao", "tipo" => "text", "max" => 255],
        "doacoes" => ["label" => "Doações aceitas", "coluna" => "ds_doacoes", "tipo" => "textarea"],
        "sobre" => ["label" => "Sobre", "coluna" => "ds_sobre", "tipo" => "textarea"],
        "missao" => ["label" => "Missão", "coluna" => "ds_missao", "tipo" => "textarea"],
        "projetos" => ["label" => "Projetos", "coluna" => "ds_projetos", "tipo" => "textarea"],
        "cnpj" => ["label" => "CNPJ", "coluna" => "cd_cnpj", "tipo" => "text", "max" => 18],
    ];
} else {
    $tabela = "DOADOR";
    $colunaEmail = "nm_email_doador";
    $colunaImagem = "nm_imagem_doador";
    $tipoPerfil = "Perfil do doador";
    $campos = [
        "nome" => ["label" => "Nome completo", "coluna" => "nm_doador", "tipo" => "text", "max" => 50],
        "email" => ["label" => "E-mail", "coluna" => $colunaEmail, "tipo" => "email", "max" => 50],
        "telefone" => ["label" => "Telefone", "coluna" => "cd_telefone_doador", "tipo" => "tel", "max" => 20],
        "endereco" => ["label" => "Endereço", "coluna" => "ds_endereco_doador", "tipo" => "text", "max" => 255],
        "cpf" => ["label" => "CPF", "coluna" => "cd_cpf_doador", "tipo" => "text", "max" => 14],
    ];
}

$mensagem = null;
$sucesso = false;

// Processa a alteração de um campo ou da foto.
if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $acao = $_POST["acao"] ?? "";
    if ($acao === "atualizar_campo") {
        $chaveCampo = $_POST["campo"] ?? "";
        $valor = $_POST["valor"] ?? null;

        if (!is_string($chaveCampo) || !isset($campos[$chaveCampo]) || !is_string($valor)) {
            $mensagem = "Não foi possível identificar o dado para edição.";
        } else {
            $campo = $campos[$chaveCampo];
            $valor = trim($valor);
            $quantidadeCaracteres = preg_match_all("/./us", $valor);

            if ($valor === "") {
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
    "SELECT " . implode(", ", $colunas) . " FROM $tabela WHERE $colunaEmail = :email"
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
                    <?php foreach ($campos as $chave => $campo): ?>
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
                                    maxlength="<?= (int) $campo["max"] ?>" disabled required>
                            <?php endif; ?>

                            <div class="profile-field-actions">
                                <button type="button" class="profile-edit-button" data-profile-edit>Editar</button>
                                <button type="submit" class="profile-save-button" data-profile-save hidden>Salvar</button>
                                <button type="button" class="profile-cancel-button" data-profile-cancel hidden>Cancelar</button>
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
    </main>

    <footer>
        <p>© 2026 REUNE+. Juntos pela transformação social.</p>
    </footer>
    <script src="script.js"></script>
</body>

</html>