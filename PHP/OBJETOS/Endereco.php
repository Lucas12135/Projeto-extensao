<?php
namespace Reune\Objetos;

final class Endereco
{
    public string $cep;
    public string $logradouro;
    public string $numero;
    public ?string $complemento;
    public string $bairro;
    public string $cidade;
    public string $uf;

    public function __construct(
        string $cep,
        string $logradouro,
        string $numero,
        ?string $complemento,
        string $bairro,
        string $cidade,
        string $uf
    ) {
        $this->cep = $cep;
        $this->logradouro = $logradouro;
        $this->numero = $numero;
        $this->complemento = $complemento;
        $this->bairro = $bairro;
        $this->cidade = $cidade;
        $this->uf = strtoupper($uf);
    }

    public function estaCompleto(): bool
    {
        return $this->cep !== ''
            && $this->logradouro !== ''
            && $this->numero !== ''
            && $this->bairro !== ''
            && $this->cidade !== ''
            && $this->uf !== '';
    }

    public function comoTexto(): string
    {
        return implode(', ', array_filter([
            $this->logradouro,
            $this->numero,
            $this->complemento,
            $this->bairro,
            $this->cidade,
            $this->uf,
            $this->cep,
        ], static function ($parte) {
            return $parte !== null && $parte !== '';
        }));
    }
}
