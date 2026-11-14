<?php
namespace Reune\Objetos;

final class CampoPerfil
{
    public string $rotulo;
    public string $coluna;
    public string $tipo;
    public ?int $limite;
    public bool $opcional;

    public function __construct(
        string $rotulo,
        string $coluna,
        string $tipo,
        ?int $limite = null,
        bool $opcional = false
    ) {
        $this->rotulo = $rotulo;
        $this->coluna = $coluna;
        $this->tipo = $tipo;
        $this->limite = $limite;
        $this->opcional = $opcional;
    }
}
