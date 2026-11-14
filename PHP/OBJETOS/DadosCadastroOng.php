<?php
namespace Reune\Objetos;

final class DadosCadastroOng
{
    public ?string $nome;
    public ?string $cnpj;
    public ?string $documento;
    public ?string $email;
    public ?string $telefone;
    public ?string $senha;
    public ?string $distancia;
    public ?string $doacoes;
    public ?string $sobre;
    public ?string $missao;
    public ?string $projetos;
    public Endereco $endereco;
    public ?string $imagem;

    public function __construct(
        ?string $nome,
        ?string $cnpj,
        ?string $documento,
        ?string $email,
        ?string $telefone,
        ?string $senha,
        ?string $distancia,
        ?string $doacoes,
        ?string $sobre,
        ?string $missao,
        ?string $projetos,
        Endereco $endereco,
        ?string $imagem = null
    ) {
        $this->nome = $nome;
        $this->cnpj = $cnpj;
        $this->documento = $documento;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->senha = $senha;
        $this->distancia = $distancia;
        $this->doacoes = $doacoes;
        $this->sobre = $sobre;
        $this->missao = $missao;
        $this->projetos = $projetos;
        $this->endereco = $endereco;
        $this->imagem = $imagem;
    }

    public function campoObrigatorioAusente(): ?string
    {
        if (empty($this->nome)) {
            return 'nome';
        }
        if (empty($this->cnpj)) {
            return 'cnpj';
        }
        if (empty($this->email)) {
            return 'email';
        }
        if (empty($this->telefone)) {
            return 'telefone';
        }
        if (empty($this->senha)) {
            return 'senha';
        }
        if (empty($this->distancia)) {
            return 'distancia';
        }
        if (empty($this->doacoes)) {
            return 'doacoes';
        }
        if (empty($this->sobre)) {
            return 'sobre';
        }
        if (empty($this->missao)) {
            return 'missao';
        }
        if (empty($this->projetos)) {
            return 'projetos';
        }
        if (empty($this->endereco->comoTexto())) {
            return 'localizacao';
        }

        return null;
    }
}
