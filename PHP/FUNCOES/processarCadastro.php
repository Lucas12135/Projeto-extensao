<?php
use Reune\Objetos\DadosCadastroDoador;
use Reune\Objetos\DadosCadastroOng;
use Reune\Objetos\Endereco;
use Reune\Objetos\GerenciadorImagemCadastro;
use Reune\Objetos\ResultadoCadastro;

require_once __DIR__ . "/../../BD/conexao.php";
require_once __DIR__ . "/cadastrarOng.php";
require_once __DIR__ . "/cadastrarDoador.php";
require_once __DIR__ . "/uploadImagem.php";
require_once __DIR__ . "/validarDocumentos.php";
require_once __DIR__ . "/validarCampos.php";

require_once __DIR__ . "/../OBJETOS/DadosCadastroDoador.php";
require_once __DIR__ . "/../OBJETOS/DadosCadastroOng.php";
require_once __DIR__ . "/../OBJETOS/Endereco.php";
require_once __DIR__ . "/../OBJETOS/GerenciadorImagemCadastro.php";
require_once __DIR__ . "/../OBJETOS/ResultadoCadastro.php";
require_once __DIR__ . "/../OBJETOS/ResultadoUploadImagem.php";

function campo(string $nome, ?string $default = null): ?string
{
    return isset($_POST[$nome]) && $_POST[$nome] !== ''
        ? trim($_POST[$nome])
        : $default;
}

function processarCadastro(PDO $conexao): ?ResultadoCadastro
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        return null;
    }

    $tipo = campo('tipo', campo('nome-ong') !== null ? 'ong' : 'doador');
    $gerenciadorImagem = new GerenciadorImagemCadastro();

    if ($tipo === 'ong') {
        $cep = campo('cep-ong');
        $logradouro = campo('logradouro-ong');
        $numero = campo('numero-ong');
        $complemento = campo('complemento-ong');
        $bairro = campo('bairro-ong');
        $cidade = campo('cidade-ong');
        $uf = campo('uf-ong');

        if (in_array(null, [$cep, $logradouro, $numero, $bairro, $cidade, $uf], true)) {
            return ResultadoCadastro::falha('Preencha todos os campos obrigatórios do endereço da ONG.');
        }

        $dados = new DadosCadastroOng(
            campo('nome-ong'),
            campo('cnpj-ong'),
            campo('documento'),
            campo('email-ong'),
            campo('telefone-ong'),
            campo('senha-ong'),
            campo('distancia-ong'),
            campo('doacoes-ong'),
            campo('sobre-ong'),
            campo('missao-ong'),
            campo('projetos-ong'),
            new Endereco($cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf)
        );

        if (!validarCnpj($dados->cnpj)) {
            return ResultadoCadastro::falha('Informe um CNPJ válido.');
        }
        if (!validarTelefone($dados->telefone)) {
            return ResultadoCadastro::falha('Informe um telefone válido com DDD.');
        }
        if (!validarCep($dados->endereco->cep) || !validarUf($dados->endereco->uf)) {
            return ResultadoCadastro::falha('Informe um CEP e uma UF válidos.');
        }
        if ($dados->senha !== campo('confirmar-senha') || campo('agreement') === null) {
            return ResultadoCadastro::falha('Confirme a senha e aceite os termos.');
        }

        $upload = $gerenciadorImagem->salvar($_FILES['imagem-ong'] ?? null, true);
        if (!$upload->sucesso) {
            return ResultadoCadastro::falha($upload->mensagem);
        }

        $dados->imagem = $upload->caminho;
        $resultado = cadastrarOng($conexao, $dados);
        if (!$resultado->sucesso) {
            $gerenciadorImagem->remover($upload->arquivo);
        }

        return $resultado;
    }

    if ($tipo !== 'doador') {
        return ResultadoCadastro::falha('Tipo de cadastro inválido.');
    }

    $cep = campo('cep-doador');
    $logradouro = campo('logradouro-doador');
    $numero = campo('numero-doador');
    $complemento = campo('complemento-doador');
    $bairro = campo('bairro-doador');
    $cidade = campo('cidade-doador');
    $uf = campo('uf-doador');

    if (in_array(null, [$cep, $logradouro, $numero, $bairro, $cidade, $uf], true)) {
        return ResultadoCadastro::falha('Preencha todos os campos obrigatórios do endereço.');
    }

    $dados = new DadosCadastroDoador(
        campo('nome-doador'),
        campo('cpf-doador'),
        campo('email-doador'),
        campo('telefone-doador'),
        campo('senha-doador'),
        new Endereco($cep, $logradouro, $numero, $complemento, $bairro, $cidade, $uf)
    );

    if (!validarCpf($dados->cpf)) {
        return ResultadoCadastro::falha('Informe um CPF válido.');
    }
    if (!validarTelefone($dados->telefone)) {
        return ResultadoCadastro::falha('Informe um telefone válido com DDD.');
    }
    if (!validarCep($dados->endereco->cep) || !validarUf($dados->endereco->uf)) {
        return ResultadoCadastro::falha('Informe um CEP e uma UF válidos.');
    }
    if ($dados->senha !== campo('confirmar-senha') || campo('agreement') === null) {
        return ResultadoCadastro::falha('Confirme a senha e aceite os termos.');
    }

    $upload = $gerenciadorImagem->salvar($_FILES['imagem-doador'] ?? null, false);
    if (!$upload->sucesso) {
        return ResultadoCadastro::falha($upload->mensagem);
    }

    $dados->imagem = $upload->caminho;
    $resultado = cadastrarDoador($conexao, $dados);
    if (!$resultado->sucesso) {
        $gerenciadorImagem->remover($upload->arquivo);
    }

    return $resultado;
}

$resultado = processarCadastro($conexao);
$conexao = null;
