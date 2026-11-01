<?php
include_once __DIR__ . '/../BD/conexao.php';

session_start();

$isOng = isset($_SESSION["email-ong"]);
$emailSessao = $_SESSION[$isOng ? "email-ong" : "email-doador"] ?? null;

if (!is_string($emailSessao) || $emailSessao === "") {
    header("Location: ../login.php");
    exit;
} else {
    try {
        session_unset();
        session_destroy();
        $exc = $conexao->prepare($isOng ? "DELETE FROM ONG WHERE nm_email_ong = :email" : "DELETE FROM DOADOR WHERE nm_email_doador = :email");
        $exc->execute([':email' => $emailSessao]);
        echo "Conta excluída com sucesso.";
        sleep(2);
        header("Location: login.php");
        exit;
    } catch (PDOException $e) {
        echo "Erro ao excluir conta: " . $e->getMessage();
    }
}