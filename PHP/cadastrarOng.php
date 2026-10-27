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

    $sql = "INSERT INTO ONG
                (nm_ong, cd_cnpj, ds_documento_ong, nm_email_ong, cd_telefone_ong, pw_ong,
                 cd_distancia_km, ds_doacoes, ds_sobre, ds_missao, ds_projetos, ds_localizacao)
            VALUES
                (:nome, :cnpj, :documento, :email, :telefone, :senha,
                 :distancia, :doacoes, :sobre, :missao, :projetos, :localizacao)";

    try {
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
        ]);

        return [
            'sucesso'  => true,
            'mensagem' => 'ONG cadastrada com sucesso.',
            'id'       => (int) $conexao->lastInsertId(),
        ];
    } catch (PDOException $e) {
        error_log('Erro ao cadastrar ONG: ' . $e->getMessage());
        return ['sucesso' => false, 'mensagem' => 'Não foi possível cadastrar a ONG.', 'id' => null];
    }
}