<?php
use Reune\Objetos\DadosCadastroOng;
use Reune\Objetos\RepositorioCadastro;
use Reune\Objetos\ResultadoCadastro;

require_once __DIR__ . '/../OBJETOS/DadosCadastroOng.php';
require_once __DIR__ . '/../OBJETOS/RepositorioCadastro.php';
require_once __DIR__ . '/../OBJETOS/ResultadoCadastro.php';
require_once __DIR__ . '/../OBJETOS/Endereco.php';

function cadastrarOng(PDO $conexao, DadosCadastroOng $ong): ResultadoCadastro
{
    return (new RepositorioCadastro($conexao))->cadastrarOng($ong);
}
