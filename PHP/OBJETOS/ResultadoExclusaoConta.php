<?php
namespace Reune\Objetos;

final class ResultadoExclusaoConta
{
    public const NAO_ENCONTRADA = 'nao_encontrada';
    public const DOACOES_PENDENTES = 'doacoes_pendentes';
    public const EXCLUIDA = 'excluida';

    public string $status;

    public function __construct(string $status)
    {
        $this->status = $status;
    }
}
