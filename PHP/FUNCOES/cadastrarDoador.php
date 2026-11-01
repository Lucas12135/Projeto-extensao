<?php
function cadastrarDoador(PDO $conexao, array $dados): array
{
    $obrigatorios = ['nome', 'cpf', 'email', 'telefone', 'endereco', 'senha'];
    foreach ($obrigatorios as $campo) {
        if (empty($dados[$campo])) {
            return ['sucesso' => false, 'mensagem' => "Campo obrigatório ausente: $campo", 'id' => null];
        }
    }

    $camposEndereco = ['cep', 'logradouro', 'numero', 'bairro', 'cidade', 'uf'];
    if (!isset($dados['endereco_detalhado']) || !is_array($dados['endereco_detalhado'])) {
        return ['sucesso' => false, 'mensagem' => 'Endereço detalhado do doador ausente.', 'id' => null];
    }
    foreach ($camposEndereco as $campo) {
        if (!isset($dados['endereco_detalhado'][$campo]) || trim((string) $dados['endereco_detalhado'][$campo]) === '') {
            return ['sucesso' => false, 'mensagem' => "Campo obrigatório ausente no endereço: $campo", 'id' => null];
        }
    }

    $sql = "INSERT INTO DOADOR
                (nm_doador, cd_cpf_doador, nm_email_doador, cd_telefone_doador, ds_endereco_doador, pw_doador, nm_imagem_doador)
            VALUES
                (:nome, :cpf, :email, :telefone, :endereco, :senha, :imagem)";

    try {
        $conexao->beginTransaction();

        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':nome'     => $dados['nome'],
            ':cpf'      => $dados['cpf'],
            ':email'    => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':endereco' => $dados['endereco'] ?? null,
            ':senha'    => password_hash($dados['senha'], PASSWORD_DEFAULT),
            ':imagem'   => $dados['imagem'] ?? null,
        ]);

        $idDoador = (int) $conexao->lastInsertId();
        $stmtEndereco = $conexao->prepare(
            "INSERT INTO ENDERECO
                (DOADOR_id_Doador, cd_cep, ds_logradouro, nr_endereco, ds_complemento, nm_bairro, nm_cidade, sg_uf)
             VALUES
                (:doador, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf)"
        );
        $stmtEndereco->execute([
            ':doador'      => $idDoador,
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
            'mensagem' => 'Doador cadastrado com sucesso.',
            'id'       => $idDoador,
        ];
    } catch (PDOException $e) {
        if ($conexao->inTransaction()) {
            $conexao->rollBack();
        }
        error_log('Erro ao cadastrar doador: ' . $e->getMessage());
        return ['sucesso' => false, 'mensagem' => 'Não foi possível cadastrar o doador.', 'id' => null];
    }
}

?>