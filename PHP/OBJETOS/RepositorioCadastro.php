<?php
namespace Reune\Objetos;

use PDO;
use PDOException;

require_once __DIR__ . '/DadosCadastroDoador.php';
require_once __DIR__ . '/DadosCadastroOng.php';
require_once __DIR__ . '/Endereco.php';
require_once __DIR__ . '/ResultadoCadastro.php';

final class RepositorioCadastro
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function cadastrarDoador(DadosCadastroDoador $doador): ResultadoCadastro
    {
        $campoAusente = $doador->campoObrigatorioAusente();
        if ($campoAusente !== null) {
            return ResultadoCadastro::falha("Campo obrigatório ausente: $campoAusente");
        }
        if (!$doador->endereco->estaCompleto()) {
            return ResultadoCadastro::falha('Endereço detalhado do doador ausente.');
        }

        try {
            $this->conexao->beginTransaction();
            $insercao = $this->conexao->prepare(
                "INSERT INTO DOADOR
                    (nm_doador, cd_cpf_doador, nm_email_doador, cd_telefone_doador,
                     ds_endereco_doador, pw_doador, nm_imagem_doador)
                 VALUES
                    (:nome, :cpf, :email, :telefone, :endereco, :senha, :imagem)"
            );
            $insercao->execute([
                ':nome' => $doador->nome,
                ':cpf' => $doador->cpf,
                ':email' => $doador->email,
                ':telefone' => $doador->telefone,
                ':endereco' => $doador->endereco->comoTexto(),
                ':senha' => password_hash($doador->senha, PASSWORD_DEFAULT),
                ':imagem' => $doador->imagem,
            ]);

            $idDoador = (int) $this->conexao->lastInsertId();
            $this->salvarEndereco($doador->endereco, 'DOADOR_id_Doador', $idDoador);
            $this->conexao->commit();

            return ResultadoCadastro::sucesso('Doador cadastrado com sucesso.', $idDoador);
        } catch (PDOException $erro) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            error_log('Erro ao cadastrar doador: ' . $erro->getMessage());

            return ResultadoCadastro::falha('Não foi possível cadastrar o doador.');
        }
    }

    public function cadastrarOng(DadosCadastroOng $ong): ResultadoCadastro
    {
        $campoAusente = $ong->campoObrigatorioAusente();
        if ($campoAusente !== null) {
            return ResultadoCadastro::falha("Campo obrigatório ausente: $campoAusente");
        }
        if (!$ong->endereco->estaCompleto()) {
            return ResultadoCadastro::falha('Endereço detalhado da ONG ausente.');
        }

        try {
            $this->conexao->beginTransaction();
            $insercao = $this->conexao->prepare(
                "INSERT INTO ONG
                    (nm_ong, cd_cnpj, ds_documento_ong, nm_email_ong, cd_telefone_ong, pw_ong,
                     cd_distancia_km, ds_doacoes, ds_sobre, ds_missao, ds_projetos, ds_localizacao,
                     nm_imagem_ong)
                 VALUES
                    (:nome, :cnpj, :documento, :email, :telefone, :senha,
                     :distancia, :doacoes, :sobre, :missao, :projetos, :localizacao, :imagem)"
            );
            $insercao->execute([
                ':nome' => $ong->nome,
                ':cnpj' => $ong->cnpj,
                ':documento' => $ong->documento,
                ':email' => $ong->email,
                ':telefone' => $ong->telefone,
                ':senha' => password_hash($ong->senha, PASSWORD_DEFAULT),
                ':distancia' => $ong->distancia,
                ':doacoes' => $ong->doacoes,
                ':sobre' => $ong->sobre,
                ':missao' => $ong->missao,
                ':projetos' => $ong->projetos,
                ':localizacao' => $ong->endereco->comoTexto(),
                ':imagem' => $ong->imagem,
            ]);

            $idOng = (int) $this->conexao->lastInsertId();
            $this->salvarEndereco($ong->endereco, 'ONG_id_ONG', $idOng);
            $this->conexao->commit();

            return ResultadoCadastro::sucesso('ONG cadastrada com sucesso.', $idOng);
        } catch (PDOException $erro) {
            if ($this->conexao->inTransaction()) {
                $this->conexao->rollBack();
            }
            error_log('Erro ao cadastrar ONG: ' . $erro->getMessage());

            return ResultadoCadastro::falha('Não foi possível cadastrar a ONG.');
        }
    }

    private function salvarEndereco(Endereco $endereco, string $colunaProprietario, int $idProprietario): void
    {
        $insercao = $this->conexao->prepare(
            "INSERT INTO ENDERECO
                ($colunaProprietario, cd_cep, ds_logradouro, nr_endereco, ds_complemento,
                 nm_bairro, nm_cidade, sg_uf)
             VALUES
                (:proprietario, :cep, :logradouro, :numero, :complemento, :bairro, :cidade, :uf)"
        );
        $insercao->execute([
            ':proprietario' => $idProprietario,
            ':cep' => $endereco->cep,
            ':logradouro' => $endereco->logradouro,
            ':numero' => $endereco->numero,
            ':complemento' => $endereco->complemento,
            ':bairro' => $endereco->bairro,
            ':cidade' => $endereco->cidade,
            ':uf' => $endereco->uf,
        ]);
    }
}
