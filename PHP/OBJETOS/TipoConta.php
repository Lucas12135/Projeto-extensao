<?php
namespace Reune\Objetos;

final class TipoConta
{
    public string $tabela;
    public string $colunaId;
    public string $colunaEmail;
    public string $colunaReferencia;
    public string $colunaLocalizacao;
    public string $colunaImagem;
    public string $chaveSessao;
    public string $descricaoPerfil;

    private function __construct(
        string $tabela,
        string $colunaId,
        string $colunaEmail,
        string $colunaReferencia,
        string $colunaLocalizacao,
        string $colunaImagem,
        string $chaveSessao,
        string $descricaoPerfil
    ) {
        $this->tabela = $tabela;
        $this->colunaId = $colunaId;
        $this->colunaEmail = $colunaEmail;
        $this->colunaReferencia = $colunaReferencia;
        $this->colunaLocalizacao = $colunaLocalizacao;
        $this->colunaImagem = $colunaImagem;
        $this->chaveSessao = $chaveSessao;
        $this->descricaoPerfil = $descricaoPerfil;
    }

    public static function ong(): self
    {
        return new self(
            'ONG',
            'id_ONG',
            'nm_email_ong',
            'ONG_id_ONG',
            'ds_localizacao',
            'nm_imagem_ong',
            'email-ong',
            'Perfil da organização'
        );
    }

    public static function doador(): self
    {
        return new self(
            'DOADOR',
            'id_Doador',
            'nm_email_doador',
            'DOADOR_id_Doador',
            'ds_endereco_doador',
            'nm_imagem_doador',
            'email-doador',
            'Perfil do doador'
        );
    }
}
