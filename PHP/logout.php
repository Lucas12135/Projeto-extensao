<?php
session_start();

$isOng = isset($_SESSION["email-ong"]);
$emailSessao = $_SESSION[$isOng ? "email-ong" : "email-doador"] ?? null;

if (!is_string($emailSessao) || $emailSessao === "") {
    header("Location: login.php");
    exit;
} else {
    session_unset();
    session_destroy();
    header("Location: ../login.php");
    exit;
}
?>