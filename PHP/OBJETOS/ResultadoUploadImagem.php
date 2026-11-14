<?php
namespace Reune\Objetos;

final class ResultadoUploadImagem
{
    public bool $sucesso;
    public ?string $caminho;
    public ?string $arquivo;
    public ?string $mensagem;

    public function __construct(
        bool $sucesso,
        ?string $caminho = null,
        ?string $arquivo = null,
        ?string $mensagem = null
    ) {
        $this->sucesso = $sucesso;
        $this->caminho = $caminho;
        $this->arquivo = $arquivo;
        $this->mensagem = $mensagem;
    }
}
