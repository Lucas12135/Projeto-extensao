<?php
session_start();
require_once __DIR__ . "/../BD/conexao.php";

if ($_SERVER["REQUEST_METHOD"] !== "POST") {
    header("Location: ../perfil.php");
    exit;
}

$isOng = isset($_SESSION["email-ong"]);
$chaveSessao = $isOng ? "email-ong" : "email-doador";
$emailSessao = $_SESSION[$chaveSessao] ?? null;
if (!is_string($emailSessao) || $emailSessao === "") {
    header("Location: ../login.php");
    exit;
}

$tokenInformado = $_POST["token_excluir_conta"] ?? null;
$confirmacao = $_POST["confirmacao"] ?? null;
if (
    !isset($_SESSION["token_excluir_conta"]) ||
    !is_string($tokenInformado) ||
    !hash_equals($_SESSION["token_excluir_conta"], $tokenInformado)
) {
    $_SESSION["mensagem_perfil"] = "Não foi possível validar a solicitação. Atualize a página e tente novamente.";
    $_SESSION["sucesso_perfil"] = false;
    header("Location: ../perfil.php");
    exit;
}

if (!is_string($confirmacao) || $confirmacao !== "CONFIRMAR") {
    $_SESSION["mensagem_perfil"] = "Digite CONFIRMAR para confirmar a exclusão da conta.";
    $_SESSION["sucesso_perfil"] = false;
    header("Location: ../perfil.php");
    exit;
}

$tabela = $isOng ? "ONG" : "DOADOR";
$colunaId = $isOng ? "id_ONG" : "id_Doador";
$colunaEmail = $isOng ? "nm_email_ong" : "nm_email_doador";
$colunaDoacao = $isOng ? "ONG_id_ONG" : "DOADOR_id_Doador";

try {
    $conexao->beginTransaction();
    $consultaConta = $conexao->prepare(
        "SELECT $colunaId FROM $tabela WHERE $colunaEmail = :email FOR UPDATE"
    );
    $consultaConta->execute([":email" => $emailSessao]);
    $idConta = $consultaConta->fetchColumn();

    if ($idConta === false) {
        $conexao->rollBack();
        $_SESSION["mensagem_perfil"] = "Não foi possível localizar a conta para exclusão.";
        $_SESSION["sucesso_perfil"] = false;
        header("Location: ../perfil.php");
        exit;
    }

    $consultaDoacoes = $conexao->prepare(
        "SELECT id_DOACAO, nm_status_doacao FROM DOACAO
         WHERE $colunaDoacao = :id
         FOR UPDATE"
    );
    $consultaDoacoes->execute([":id" => $idConta]);
    $totalDoacoesNaoFinalizadas = 0;
    while ($doacao = $consultaDoacoes->fetch(PDO::FETCH_ASSOC)) {
        if ($doacao["nm_status_doacao"] !== "Finalizada") {
            $totalDoacoesNaoFinalizadas++;
        }
    }

    if ($totalDoacoesNaoFinalizadas > 0) {
        $conexao->rollBack();
        $_SESSION["mensagem_perfil"] = "Finalize todas as doações vinculadas antes de excluir sua conta.";
        $_SESSION["sucesso_perfil"] = false;
        header("Location: ../perfil.php");
        exit;
    }

    $exclusaoEntregas = $conexao->prepare(
        "DELETE FROM ENTREGA
         WHERE DOACAO_id_DOACAO IN (
             SELECT id_DOACAO FROM DOACAO
             WHERE $colunaDoacao = :id
         )"
    );
    $exclusaoEntregas->execute([":id" => $idConta]);

    $exclusaoDoacoes = $conexao->prepare(
        "DELETE FROM DOACAO WHERE $colunaDoacao = :id"
    );
    $exclusaoDoacoes->execute([":id" => $idConta]);

    $exclusaoConta = $conexao->prepare(
        "DELETE FROM $tabela WHERE $colunaId = :id"
    );
    $exclusaoConta->execute([":id" => $idConta]);

    if ($exclusaoConta->rowCount() !== 1) {
        $conexao->rollBack();
        $_SESSION["mensagem_perfil"] = "Não foi possível localizar a conta para exclusão.";
        $_SESSION["sucesso_perfil"] = false;
        header("Location: ../perfil.php");
        exit;
    }

    $conexao->commit();
    $_SESSION = [];
    session_destroy();
    header("Location: ../login.php");
    exit;
} catch (PDOException $exception) {
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }
    error_log("Erro ao excluir conta pelo perfil: " . $exception->getMessage());
    $_SESSION["mensagem_perfil"] = $exception->getCode() === "23000"
        ? "Não foi possível excluir a conta por causa de registros vinculados. Nenhum dado foi alterado."
        : "Não foi possível excluir sua conta. Tente novamente.";
    $_SESSION["sucesso_perfil"] = false;
    header("Location: ../perfil.php");
    exit;
}
