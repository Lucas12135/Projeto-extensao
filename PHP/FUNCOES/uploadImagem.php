<?php
function salvarImagemCadastro(?array $arquivo, bool $obrigatoria): array
{
    if ($arquivo === null) {
        return $obrigatoria
            ? ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Selecione uma imagem.']
            : ['sucesso' => true, 'caminho' => null, 'arquivo' => null, 'mensagem' => null];
    }

    if (!isset($arquivo['error'])) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Não foi possível receber a imagem enviada.'];
    }

    if ($arquivo['error'] === UPLOAD_ERR_NO_FILE) {
        return $obrigatoria
            ? ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Selecione uma imagem.']
            : ['sucesso' => true, 'caminho' => null, 'arquivo' => null, 'mensagem' => null];
    }

    if (!isset($arquivo['tmp_name'], $arquivo['size'])
        || $arquivo['error'] !== UPLOAD_ERR_OK
        || !is_string($arquivo['tmp_name'])
        || !is_int($arquivo['size'])
        || !is_uploaded_file($arquivo['tmp_name'])) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Não foi possível receber a imagem enviada.'];
    }

    if ($arquivo['size'] > 5 * 1024 * 1024) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'A imagem deve ter no máximo 5 MB.'];
    }

    $informacao = finfo_open(FILEINFO_MIME_TYPE);
    if ($informacao === false) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Não foi possível validar a imagem enviada.'];
    }

    $tipo = finfo_file($informacao, $arquivo['tmp_name']);
    finfo_close($informacao);

    $extensoes = [
        'image/jpeg' => 'jpg',
        'image/png'  => 'png',
        'image/gif'  => 'gif',
        'image/webp' => 'webp',
    ];
    if (!is_string($tipo) || !isset($extensoes[$tipo])) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Formato inválido. Envie uma imagem JPG, PNG, GIF ou WEBP.'];
    }

    $diretorio = __DIR__ . '/../resource/uploads';
    if (!is_dir($diretorio) && !mkdir($diretorio, 0755, true) && !is_dir($diretorio)) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Não foi possível preparar a pasta de imagens.'];
    }

    $nomeArquivo = bin2hex(random_bytes(16)) . '.' . $extensoes[$tipo];
    $caminhoArquivo = $diretorio . '/' . $nomeArquivo;
    if (!move_uploaded_file($arquivo['tmp_name'], $caminhoArquivo)) {
        return ['sucesso' => false, 'caminho' => null, 'arquivo' => null, 'mensagem' => 'Não foi possível salvar a imagem enviada.'];
    }

    return [
        'sucesso'  => true,
        'caminho'  => 'resource/uploads/' . $nomeArquivo,
        'arquivo'  => $caminhoArquivo,
        'mensagem' => null,
    ];
}

function removerImagemCadastro(?string $arquivo): void
{
    if ($arquivo !== null && is_file($arquivo) && !unlink($arquivo)) {
        error_log('Não foi possível remover imagem de cadastro: ' . $arquivo);
    }
}
?>
