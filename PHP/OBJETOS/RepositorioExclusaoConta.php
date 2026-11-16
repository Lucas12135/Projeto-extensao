<?php
namespace Reune\Objetos;

use PDO;

require_once __DIR__ . '/TipoConta.php';
require_once __DIR__ . '/ResultadoExclusaoConta.php';

final class RepositorioExclusaoConta
{
    private PDO $conexao;

    public function __construct(PDO $conexao)
    {
        $this->conexao = $conexao;
    }

    public function excluir(TipoConta $tipo, string $email): ResultadoExclusaoConta
    {
        $this->conexao->beginTransaction();

        $consultaConta = $this->conexao->prepare(
            "SELECT {$tipo->colunaId} FROM {$tipo->tabela}
             WHERE {$tipo->colunaEmail} = :email FOR UPDATE"
        );
        $consultaConta->execute([':email' => $email]);
        $idConta = $consultaConta->fetchColumn();

        if ($idConta === false) {
            $this->conexao->rollBack();
            return new ResultadoExclusaoConta(ResultadoExclusaoConta::NAO_ENCONTRADA);
        }

        $consultaDoacoes = $this->conexao->prepare(
            "SELECT nm_status_doacao FROM DOACAO
             WHERE {$tipo->colunaReferencia} = :id
             FOR UPDATE"
        );
        $consultaDoacoes->execute([':id' => $idConta]);
        while (($status = $consultaDoacoes->fetchColumn()) !== false) {
            if (!in_array($status, ['Finalizada', 'Rejeitada'], true)) {
                $this->conexao->rollBack();
                return new ResultadoExclusaoConta(ResultadoExclusaoConta::DOACOES_PENDENTES);
            }
        }

        $exclusaoEntregas = $this->conexao->prepare(
            "DELETE FROM ENTREGA
             WHERE DOACAO_id_DOACAO IN (
                 SELECT id_DOACAO FROM DOACAO
                 WHERE {$tipo->colunaReferencia} = :id
             )"
        );
        $exclusaoEntregas->execute([':id' => $idConta]);

        $exclusaoDoacoes = $this->conexao->prepare(
            "DELETE FROM DOACAO WHERE {$tipo->colunaReferencia} = :id"
        );
        $exclusaoDoacoes->execute([':id' => $idConta]);

        $exclusaoConta = $this->conexao->prepare(
            "DELETE FROM {$tipo->tabela} WHERE {$tipo->colunaId} = :id"
        );
        $exclusaoConta->execute([':id' => $idConta]);

        if ($exclusaoConta->rowCount() !== 1) {
            $this->conexao->rollBack();
            return new ResultadoExclusaoConta(ResultadoExclusaoConta::NAO_ENCONTRADA);
        }

        $this->conexao->commit();
        return new ResultadoExclusaoConta(ResultadoExclusaoConta::EXCLUIDA);
    }
}
