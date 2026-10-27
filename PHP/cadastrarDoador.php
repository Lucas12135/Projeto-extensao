<?php
function cadastrarDoador(PDO $conexao, array $dados): array
{
    $obrigatorios = ['nome', 'cpf', 'email', 'telefone', 'endereco', 'senha'];
    foreach ($obrigatorios as $campo) {
        if (empty($dados[$campo])) {
            return ['sucesso' => false, 'mensagem' => "Campo obrigatório ausente: $campo", 'id' => null];
        }
    }

    $sql = "INSERT INTO DOADOR
                (nm_doador, cd_cpf_doador, nm_email_doador, cd_telefone_doador, ds_endereco_doador, pw_doador)
            VALUES
                (:nome, :cpf, :email, :telefone, :endereco, :senha)";

    try {
        $stmt = $conexao->prepare($sql);
        $stmt->execute([
            ':nome'     => $dados['nome'],
            ':cpf'      => $dados['cpf'],
            ':email'    => $dados['email'],
            ':telefone' => $dados['telefone'],
            ':endereco' => $dados['endereco'] ?? null,
            ':senha'    => password_hash($dados['senha'], PASSWORD_DEFAULT),
        ]);

        return [
            'sucesso'  => true,
            'mensagem' => 'Doador cadastrado com sucesso.',
            'id'       => (int) $conexao->lastInsertId(),
        ];
    } catch (PDOException $e) {
        error_log('Erro ao cadastrar doador: ' . $e->getMessage());
        return ['sucesso' => false, 'mensagem' => 'Não foi possível cadastrar o doador.', 'id' => null];
    }
}

?>