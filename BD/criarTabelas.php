<?php
require_once __DIR__ . "/conexao.php";

$tabelas = [
    "CREATE TABLE IF NOT EXISTS ONG (
    id_ONG INT AUTO_INCREMENT PRIMARY KEY,
    nm_ong VARCHAR(30) NOT NULL,
    cd_cnpj VARCHAR(18),
    ds_documento_ong VARCHAR(255),
    nm_email_ong VARCHAR(50),
    cd_telefone_ong VARCHAR(20),
    pw_ong VARCHAR(255),
    cd_distancia_km DECIMAL(8,2),
    ds_doacoes TEXT,
    ds_sobre TEXT,
    ds_missao TEXT,
    ds_projetos TEXT,
    ds_localizacao VARCHAR(255),
    nm_imagem_ong VARCHAR(255)
)",

    "CREATE TABLE IF NOT EXISTS DOADOR (
    id_Doador INT AUTO_INCREMENT PRIMARY KEY,
    nm_doador VARCHAR(50) NOT NULL,
    cd_cpf_doador VARCHAR(14),
    nm_email_doador VARCHAR(50),
    cd_telefone_doador VARCHAR(20),
    ds_endereco_doador VARCHAR(255),
    pw_doador VARCHAR(255),
    nm_imagem_doador VARCHAR(255)
)",

    "CREATE TABLE IF NOT EXISTS ENDERECO (
    id_ENDERECO INT AUTO_INCREMENT PRIMARY KEY,
    ONG_id_ONG INT NULL UNIQUE,
    DOADOR_id_Doador INT NULL UNIQUE,
    cd_cep CHAR(9) NOT NULL,
    ds_logradouro VARCHAR(150) NOT NULL,
    nr_endereco VARCHAR(20) NOT NULL,
    ds_complemento VARCHAR(100),
    nm_bairro VARCHAR(100) NOT NULL,
    nm_cidade VARCHAR(100) NOT NULL,
    sg_uf CHAR(2) NOT NULL,
    CONSTRAINT fk_endereco_ong FOREIGN KEY (ONG_id_ONG)
        REFERENCES ONG(id_ONG) ON DELETE CASCADE,
    CONSTRAINT fk_endereco_doador FOREIGN KEY (DOADOR_id_Doador)
        REFERENCES DOADOR(id_Doador) ON DELETE CASCADE,
    CONSTRAINT chk_endereco_proprietario CHECK (
        (ONG_id_ONG IS NOT NULL AND DOADOR_id_Doador IS NULL) OR
        (ONG_id_ONG IS NULL AND DOADOR_id_Doador IS NOT NULL)
    )
)",

    "CREATE TABLE IF NOT EXISTS TRANSPORTADORA (
    id_TRANSPORTADORA INT AUTO_INCREMENT PRIMARY KEY,
    nm_transportadora VARCHAR(30) NOT NULL,
    cd_telefone_transportadora DECIMAL(13,0)
)",

    "CREATE TABLE IF NOT EXISTS DOACAO (
    id_DOACAO INT AUTO_INCREMENT PRIMARY KEY,
    ONG_id_ONG INT NOT NULL,
    DOADOR_id_Doador INT NOT NULL,
    nm_tipo_doacao VARCHAR(50),
    ds_doacao INT,
    nm_status_doacao VARCHAR(20),
    dt_doacao DATE,

    FOREIGN KEY (ONG_id_ONG)
        REFERENCES ONG(id_ONG),

    FOREIGN KEY (DOADOR_id_Doador)
        REFERENCES DOADOR(id_Doador)
)",

    "CREATE TABLE IF NOT EXISTS ENTREGA (
    id_ENTREGA INT AUTO_INCREMENT PRIMARY KEY,
    DOACAO_id_DOACAO INT NOT NULL UNIQUE,
    TRANSPORTADORA_id_TRANSPORTADORA INT NOT NULL,
    nm_status_entrega VARCHAR(30),
    dt_entrega DATE,

    FOREIGN KEY (DOACAO_id_DOACAO)
        REFERENCES DOACAO(id_DOACAO),

    FOREIGN KEY (TRANSPORTADORA_id_TRANSPORTADORA)
        REFERENCES TRANSPORTADORA(id_TRANSPORTADORA)
)"
];

foreach ($tabelas as $sql) {
    $conexao->exec($sql);
}
?>