<?php
use Reune\Objetos\GerenciadorImagemCadastro;
use Reune\Objetos\ResultadoUploadImagem;

require_once __DIR__ . '/../OBJETOS/ResultadoUploadImagem.php';
require_once __DIR__ . '/../OBJETOS/GerenciadorImagemCadastro.php';

function salvarImagemCadastro(?array $arquivo, bool $obrigatoria): ResultadoUploadImagem
{
    return (new GerenciadorImagemCadastro())->salvar($arquivo, $obrigatoria);
}

function removerImagemCadastro(?string $arquivo): void
{
    (new GerenciadorImagemCadastro())->remover($arquivo);
}
