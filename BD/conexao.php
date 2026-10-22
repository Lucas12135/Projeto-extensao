<?php
$servidor = "localhost";
$usuario = "root";
$senha = "";
$banco = "reuneMais";

try {
    $conexao = new PDO("mysql:host=$servidor;charset=utf8mb4", $usuario, $senha);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conexao->exec(
        "CREATE DATABASE IF NOT EXISTS `$banco` " .
        "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci"
    );

    $conexao = new PDO("mysql:host=$servidor;dbname=$banco;charset=utf8mb4", $usuario, $senha);
    $conexao->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $erro) {
    exit("Falha na conexão: " . $erro->getMessage());
}
?>