<?php
namespace Reune\Objetos;

require_once __DIR__ . '/ResultadoUploadImagem.php';

final class GerenciadorImagemCadastro
{
    public function salvar(?array $arquivo, bool $obrigatoria): ResultadoUploadImagem
    {
        if ($arquivo === null || (isset($arquivo['error']) && $arquivo['error'] === UPLOAD_ERR_NO_FILE)) {
            return $this->resultadoSemArquivo($obrigatoria);
        }

        if (!isset($arquivo['error'])) {
            return new ResultadoUploadImagem(false, null, null, 'Não foi possível receber a imagem enviada.');
        }

        if (!isset($arquivo['tmp_name'], $arquivo['size'])
            || $arquivo['error'] !== UPLOAD_ERR_OK
            || !is_string($arquivo['tmp_name'])
            || !is_int($arquivo['size'])
            || !is_uploaded_file($arquivo['tmp_name'])) {
            return new ResultadoUploadImagem(false, null, null, 'Não foi possível receber a imagem enviada.');
        }

        if ($arquivo['size'] > 5 * 1024 * 1024) {
            return new ResultadoUploadImagem(false, null, null, 'A imagem deve ter no máximo 5 MB.');
        }

        $informacao = finfo_open(FILEINFO_MIME_TYPE);
        if ($informacao === false) {
            return new ResultadoUploadImagem(false, null, null, 'Não foi possível validar a imagem enviada.');
        }

        $tipo = finfo_file($informacao, $arquivo['tmp_name']);
        finfo_close($informacao);

        $extensoes = [
            'image/jpeg' => 'jpg',
            'image/png' => 'png',
            'image/gif' => 'gif',
            'image/webp' => 'webp',
        ];
        if (!is_string($tipo) || !isset($extensoes[$tipo])) {
            return new ResultadoUploadImagem(false, null, null, 'Formato inválido. Envie uma imagem JPG, PNG, GIF ou WEBP.'
            );
        }

        $diretorio = __DIR__ . '/../../resource/uploads';
        if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
            return new ResultadoUploadImagem(false, null, null, 'Não foi possível preparar a pasta de imagens.');
        }

        $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$tipo];
        $caminhoArquivo = $diretorio . '/' . $nomeArquivo;
        if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
            return new ResultadoUploadImagem(false, null, null, 'Não foi possível salvar a imagem enviada.');
        }

        return new ResultadoUploadImagem(
            true,
            'resource/uploads/' . $nomeArquivo,
            $caminhoArquivo
        );
    }

    public function remover(?string $arquivo): void
    {
        if ($arquivo !== null && is_file($arquivo) && !unlink($arquivo)) {
            error_log('Não foi possível remover imagem de cadastro: ' . $arquivo);
        }
    }

    private function resultadoSemArquivo(bool $obrigatoria): ResultadoUploadImagem
    {
        if ($obrigatoria) {
            return new ResultadoUploadImagem(false, null, null, 'Selecione uma imagem.');
        }

        return new ResultadoUploadImagem(true);
    }
}
