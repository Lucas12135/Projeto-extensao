<?php
require_once __DIR__ . "/../../BD/conexao.php";
require_once __DIR__ . "/cadastrarOng.php";
require_once __DIR__ . "/cadastrarDoador.php";
require_once __DIR__ . "/uploadImagem.php";
require_once __DIR__ . "/validarDocumentos.php";

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
        $cepOng = campo('cep-ong');
        $logradouroOng = campo('logradouro-ong');
        $numeroOng = campo('numero-ong');
        $complementoOng = campo('complemento-ong');
        $bairroOng = campo('bairro-ong');
        $cidadeOng = campo('cidade-ong');
        $ufOng = campo('uf-ong');

        if (in_array(null, [$cepOng, $logradouroOng, $numeroOng, $bairroOng, $cidadeOng, $ufOng], true)) {
            return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios do endereço da ONG.', 'id' => null];
        }

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
            'localizacao' => implode(', ', array_filter([
                $logradouroOng,
                $numeroOng,
                $complementoOng,
                $bairroOng,
                $cidadeOng,
                strtoupper($ufOng),
                $cepOng
            ], static fn ($parte) => $parte !== null)),
            'endereco_detalhado' => [
                'cep'          => $cepOng,
                'logradouro'   => $logradouroOng,
                'numero'       => $numeroOng,
                'complemento'  => $complementoOng,
                'bairro'       => $bairroOng,
                'cidade'       => $cidadeOng,
                'uf'           => strtoupper($ufOng)
            ]
        ];

        if (!validarCnpj($dados['cnpj'])) {
            return ['sucesso' => false, 'mensagem' => 'Informe um CNPJ válido.', 'id' => null];
        }

        if ($dados['senha'] !== campo('confirmar-senha') || campo('agreement') === null) {
            return ['sucesso' => false, 'mensagem' => 'Confirme a senha e aceite os termos.', 'id' => null];
        }

        $upload = salvarImagemCadastro($_FILES['imagem-ong'] ?? null, true);
        if (!$upload['sucesso']) {
            return ['sucesso' => false, 'mensagem' => $upload['mensagem'], 'id' => null];
        }

        $dados['imagem'] = $upload['caminho'];
        $resultado = cadastrarOng($conexao, $dados);
        if (!$resultado['sucesso']) {
            removerImagemCadastro($upload['arquivo']);
        }

        return $resultado;
    }

    if ($tipo !== 'doador') {
        return ['sucesso' => false, 'mensagem' => 'Tipo de cadastro inválido.', 'id' => null];
    }

    $cep = campo('cep-doador');
    $logradouro = campo('logradouro-doador');
    $numero = campo('numero-doador');
    $complemento = campo('complemento-doador');
    $bairro = campo('bairro-doador');
    $uf = campo('uf-doador');

    $cidade = campo('cidade-doador');
    if (in_array(null, [$cep, $logradouro, $numero, $bairro, $cidade, $uf], true)) {
        return ['sucesso' => false, 'mensagem' => 'Preencha todos os campos obrigatórios do endereço.', 'id' => null];
    }

    $dados = [
        'nome'     => campo('nome-doador'),
        'cpf'      => campo('cpf-doador'),
        'email'    => campo('email-doador'),
        'telefone' => campo('telefone-doador'),
        'endereco' => implode(', ', array_filter([
            $logradouro,
            $numero,
            $complemento,
            $bairro,
            $cidade,
            strtoupper($uf),
            $cep
        ], static fn ($parte) => $parte !== null)),
        'endereco_detalhado' => [
            'cep'         => $cep,
            'logradouro'  => $logradouro,
            'numero'      => $numero,
            'complemento' => $complemento,
            'bairro'      => $bairro,
            'cidade'      => $cidade,
            'uf'          => strtoupper($uf)
        ],
        'senha'    => campo('senha-doador')
    ];

    if (!validarCpf($dados['cpf'])) {
        return ['sucesso' => false, 'mensagem' => 'Informe um CPF válido.', 'id' => null];
    }

    if ($dados['senha'] !== campo('confirmar-senha') || campo('agreement') === null) {
        return ['sucesso' => false, 'mensagem' => 'Confirme a senha e aceite os termos.', 'id' => null];
    }

    $upload = salvarImagemCadastro($_FILES['imagem-doador'] ?? null, false);
    if (!$upload['sucesso']) {
        return ['sucesso' => false, 'mensagem' => $upload['mensagem'], 'id' => null];
    }

    $dados['imagem'] = $upload['caminho'];
    $resultado = cadastrarDoador($conexao, $dados);
    if (!$resultado['sucesso']) {
        removerImagemCadastro($upload['arquivo']);
    }

    return $resultado;
}

$resultado = processarCadastro($conexao);
$conexao = null;
?>