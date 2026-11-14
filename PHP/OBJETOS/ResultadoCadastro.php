<?php
namespace Reune\Objetos;

final class ResultadoCadastro
{
    public bool $sucesso;
    public string $mensagem;
    public ?int $id;

    public function __construct(bool $sucesso, string $mensagem, ?int $id = null)
    {
        $this->sucesso = $sucesso;
        $this->mensagem = $mensagem;
        $this->id = $id;
    }

    public static function sucesso(string $mensagem, int $id): self
    {
        return new self(true, $mensagem, $id);
    }

    public static function falha(string $mensagem): self
    {
        return new self(false, $mensagem);
    }
}
