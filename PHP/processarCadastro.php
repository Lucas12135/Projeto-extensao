<?php
require_once __DIR__ . "/../BD/conexao.php";
require_once __DIR__ . "/cadastrarOng.php";
require_once __DIR__ . "/cadastrarDoador.php";

function campo(string $nome, $default = null)
{
    return isset($_POST[$nome]) && $_POST[$nome] !== '' ? trim($_POST[$nome]) : $default;
}

function processarCadastro(PDO $conexao): ?array
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $tipo = campo('tipo', campo('nome-ong') !== null ? 'ong' : 'doador');

    if ($tipo === 'ong') {
        $dados = [
            'nome'        => campo('nome-ong'),
            'cnpj'        => campo('cnpj-ong'),
            'documento'   => campo('documento'),
            'email'       => campo('email-ong'),
            'telefone'    => campo('telefone-ong'),
            'senha'       => campo('senha-ong'),
            'distancia'   => campo('distancia-ong'),
            'doacoes'     => campo('doacoes-ong'),
            'sobre'       => campo('sobre-ong'),
            'missao'      => campo('missao-ong'),
            'projetos'    => campo('projetos-ong'),
            'localizacao' => campo('localizacao-ong'),
        ];

        if ($dados['senha'] !== campo('confirmar-senha') || campo('agreement') === null) {
            return ['sucesso' => false, 'mensagem' => 'Confirme a senha e aceite os termos.', 'id' => null];
        }

        return cadastrarOng($conexao, $dados);
    }

    if ($tipo !== 'doador') {
        return ['sucesso' => false, 'mensagem' => 'Tipo de cadastro inválido.', 'id' => null];
    }

    $dados = [
        'nome'     => campo('nome-doador'),
        'cpf'      => campo('cpf-doador'),
        'email'    => campo('email-doador'),
        'telefone' => campo('telefone-doador'),
        'endereco' => campo('endereco-doador'),
        'senha'    => campo('senha-doador'),
    ];

    if ($dados['senha'] !== campo('confirmar-senha') || campo('agreement') === null) {
        return ['sucesso' => false, 'mensagem' => 'Confirme a senha e aceite os termos.', 'id' => null];
    }

    return cadastrarDoador($conexao, $dados);
}

$resultado = processarCadastro($conexao);
$conexao = null;
?>