<?php
use Reune\Objetos\RepositorioExclusaoConta;
use Reune\Objetos\ResultadoExclusaoConta;
use Reune\Objetos\TipoConta;

session_start();
require_once __DIR__ . '/../BD/conexao.php';
require_once __DIR__ . '/OBJETOS/RepositorioExclusaoConta.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: ../perfil.php');
    exit;
}

$isOng = isset($_SESSION['email-ong']);
$tipoConta = $isOng ? TipoConta::ong() : TipoConta::doador();
$emailSessao = $_SESSION[$tipoConta->chaveSessao] ?? null;
if (!is_string($emailSessao) || $emailSessao === '') {
    header('Location: ../login.php');
    exit;
}

$tokenInformado = $_POST['token_excluir_conta'] ?? null;
$confirmacao = $_POST['confirmacao'] ?? null;
if (
    !isset($_SESSION['token_excluir_conta']) ||
    !is_string($tokenInformado) ||
    !hash_equals($_SESSION['token_excluir_conta'], $tokenInformado)
) {
    $_SESSION['mensagem_perfil'] = 'Não foi possível validar a solicitação. Atualize a página e tente novamente.';
    $_SESSION['sucesso_perfil'] = false;
    header('Location: ../perfil.php');
    exit;
}

if (!is_string($confirmacao) || $confirmacao !== 'CONFIRMAR') {
    $_SESSION['mensagem_perfil'] = 'Digite CONFIRMAR para confirmar a exclusão da conta.';
    $_SESSION['sucesso_perfil'] = false;
    header('Location: ../perfil.php');
    exit;
}

try {
    $resultado = (new RepositorioExclusaoConta($conexao))->excluir($tipoConta, $emailSessao);

    if ($resultado->status === ResultadoExclusaoConta::NAO_ENCONTRADA) {
        $_SESSION['mensagem_perfil'] = 'Não foi possível localizar a conta para exclusão.';
        $_SESSION['sucesso_perfil'] = false;
        header('Location: ../perfil.php');
        exit;
    }

    if ($resultado->status === ResultadoExclusaoConta::DOACOES_PENDENTES) {
        $_SESSION['mensagem_perfil'] = 'Finalize ou rejeite todas as doações vinculadas antes de excluir sua conta.';
        $_SESSION['sucesso_perfil'] = false;
        header('Location: ../perfil.php');
        exit;
    }

    $_SESSION = [];
    session_destroy();
    header('Location: ../login.php');
    exit;
} catch (PDOException $exception) {
    if ($conexao->inTransaction()) {
        $conexao->rollBack();
    }
    error_log('Erro ao excluir conta pelo perfil: ' . $exception->getMessage());
    $_SESSION['mensagem_perfil'] = $exception->getCode() === '23000'
        ? 'Não foi possível excluir a conta por causa de registros vinculados. Nenhum dado foi alterado.'
        : 'Não foi possível excluir sua conta. Tente novamente.';
    $_SESSION['sucesso_perfil'] = false;
    header('Location: ../perfil.php');
    exit;
}
