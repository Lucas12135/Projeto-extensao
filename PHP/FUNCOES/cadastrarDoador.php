<?php
use Reune\Objetos\DadosCadastroDoador;
use Reune\Objetos\RepositorioCadastro;
use Reune\Objetos\ResultadoCadastro;

require_once __DIR__ . '/../OBJETOS/DadosCadastroDoador.php';
require_once __DIR__ . '/../OBJETOS/RepositorioCadastro.php';
require_once __DIR__ . '/../OBJETOS/ResultadoCadastro.php';
require_once __DIR__ . '/../OBJETOS/Endereco.php';

function cadastrarDoador(PDO $conexao, DadosCadastroDoador $doador): ResultadoCadastro
{
    return (new RepositorioCadastro($conexao))->cadastrarDoador($doador);
}
