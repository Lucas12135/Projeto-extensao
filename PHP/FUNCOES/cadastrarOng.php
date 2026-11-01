<?php
function cadastrarOng(PDO $conexao, array $dados): array
{
    $obrigatorios = [
        'nome', 'cnpj', 'email', 'telefone', 'senha',
        'distancia', 'doacoes', 'sobre', 'missao', 'projetos', 'localizacao'
    ];
    foreach ($obrigatorios as $campo) {
        if (empty($dados[$campo])) {
           return ['sucesso' => false, 'mensagem' => "Campo obrigatório ausente: $campo", 'id' => null];
        }
    }

    $camposEndereco = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'uf'];
    if (!isset($dados['endereco_detalhado']) || !is_array($dados['endereco_detalhado'])) {
        return ['sucesso' => false, 'mensagem' => 'Endereço detalhado da ONG ausente.', 'id' => null];
    }
    foreach ($camposEndereco as $campo) {
        if (!isset($dados['endereco_detalhado'][$campo]) || trim((string) $dados['endereco_detalhado'][$campo]) === '') {
            return ['sucesso' => false, 'mensagem' => "Campo obrigatório ausente no endereço: $campo", 'id' => null];
        }
    }

    $sql = "INSERT INTO ONG
                (nm_ong, cd_cnpj, ds_documento_ong, nm_email_ong, cd_telefone_ong, pw_ong,
                 cd_distancia_km, ds_doacoes, ds_sobre, ds_missao, ds_projetos, ds_localizacao,
                 nm_imagem_ong)
            VALUES
                (:nome, :cnpj, :documento, :email, :telefone, :senha,
                 :distancia, :doacoes, :sobre, :missao, :projetos, :localizacao, :imagem)";

    try {
        $conexao->beginTransaction();

        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':nome'        => $dados['nome'],
            ':cnpj'        => $dados['cnpj'],
            ':documento'   => $dados['documento'] ?? null,
            ':email'       => $dados['email'],
            ':telefone'    => $dados['telefone'],
            ':senha'       => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':distancia'   => $dados['distancia']   ?? null,
            ':doacoes'     => $dados['doacoes']     ?? null,
            ':sobre'       => $dados['sobre']       ?? null,
            ':missao'      => $dados['missao']      ?? null,
            ':projetos'    => $dados['projetos']    ?? null,
            ':localizacao' => $dados['localizacao'] ?? null,
            ':imagem'      => $dados['imagem']      ?? null,
        ]);

        $idOng = (int) $conexao->lastInsertId();
        $stmtEndereco = $conexao->prepare(
            "INSERT INTO ENDERECO
                (ONG_id_ONG, cd_cep, ds_logradouro, nr_endereco, ds_complemento, nm_bairro, nm_cidade, sg_uf)
             VALUES
                (:ong, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf)"
        );
        $stmtEndereco->execute([
            ':ong'         => $idOng,
            ':cep'         => $dados['endereco_detalhado']['cep'],
            ':logradouro'  => $dados['endereco_detalhado']['logradouro'],
            ':numero'      => $dados['endereco_detalhado']['numero'],
            ':complemento' => $dados['endereco_detalhado']['complemento'] ?? null,
            ':bairro'      => $dados['endereco_detalhado']['bairro'],
            ':cidade'      => $dados['endereco_detalhado']['cidade'],
            ':uf'          => $dados['endereco_detalhado']['uf'],
        ]);
        $conexao->commit();

        return [
            'sucesso'  => true,
            'mensagem' => 'ONG cadastrada com sucesso.',
            'id'       => $idOng,
        ];
    } catch (PDOException $e) {
        if ($conexao->inTransaction()) {
            $conexao->rollBack();
        }
        error_log('Erro ao cadastrar ONG: ' . $e->getMessage());
        return ['sucesso' => false, 'mensagem' => 'Não foi possível cadastrar a ONG.', 'id' => null];
    }
}