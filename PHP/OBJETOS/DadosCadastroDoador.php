<?php
namespace Reune\Objetos;

final class DadosCadastroDoador
{
    public ?string $nome;
    public ?string $cpf;
    public ?string $email;
    public ?string $telefone;
    public ?string $senha;
    public Endereco $endereco;
    public ?string $imagem;

    public function __construct(
        ?string $nome,
        ?string $cpf,
        ?string $email,
        ?string $telefone,
        ?string $senha,
        Endereco $endereco,
        ?string $imagem = null
    ) {
        $this->nome = $nome;
        $this->cpf = $cpf;
        $this->email = $email;
        $this->telefone = $telefone;
        $this->senha = $senha;
        $this->endereco = $endereco;
        $this->imagem = $imagem;
    }

    public function campoObrigatorioAusente(): ?string
    {
        if (empty($this->nome)) {
            return 'nome';
        }
        if (empty($this->cpf)) {
            return 'cpf';
        }
        if (empty($this->email)) {
            return 'email';
        }
        if (empty($this->telefone)) {
            return 'telefone';
        }
        if (empty($this->endereco->comoTexto())) {
            return 'endereco';
        }
        if (empty($this->senha)) {
            return 'senha';
        }

        return null;
    }
}
