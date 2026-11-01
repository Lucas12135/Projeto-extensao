<?php 
require_once __DIR__ .  "/../BD/conexao.php";
if (isset($_SESSION["email-ong"])) {
$smnt= $conexao->prepare("SELECT nm_imagem_ong FROM ONG WHERE nm_email_ong = :email");
$smnt->execute([':email' => $_SESSION["email-ong"]]);

$tmp = $smnt->fetch(PDO::FETCH_ASSOC) ?: [];
$img = $tmp['nm_imagem_ong'] ?? null;

} else if (isset($_SESSION["email-doador"])) {
$smnt= $conexao->prepare("SELECT nm_imagem_doador FROM DOADOR WHERE nm_email_doador = :email");
$smnt->execute([':email' => $_SESSION["email-doador"]]);

$tmp = $smnt->fetch(PDO::FETCH_ASSOC) ?: [];
$img = $tmp['nm_imagem_doador'] ?? null;
}

$imagemPerfil = $img ?? 'resource/logo.jpeg';
?>