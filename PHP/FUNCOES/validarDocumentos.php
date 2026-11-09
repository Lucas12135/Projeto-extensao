<?php
function validarCpf($cpf): bool
{
    if (!is_string($cpf) || !preg_match('/\A[\d.\/\-\s]+\z/', $cpf)) {
        return false;
    }

    $cpf = preg_replace('/\D/', '', $cpf);
    if (strlen($cpf) !== 11 || preg_match('/\A(\d)\1{10}\z/', $cpf)) {
        return false;
    }

    for ($posicao = 9; $posicao <= 10; $posicao++) {
        $soma = 0;
        for ($indice = 0; $indice < $posicao; $indice++) {
            $soma += (int) $cpf[$indice] * ($posicao + 1 - $indice);
        }

        $resto = $soma % 11;
        $digito = $resto < 2 ? 0 : 11 - $resto;
        if ((int) $cpf[$posicao] !== $digito) {
            return false;
        }
    }

    return true;
}

function validarCnpj($cnpj): bool
{
    if (!is_string($cnpj) || !preg_match('/\A[\d.\/\-\s]+\z/', $cnpj)) {
        return false;
    }

    $cnpj = preg_replace('/\D/', '', $cnpj);
    if (strlen($cnpj) !== 14 || preg_match('/\A(\d)\1{13}\z/', $cnpj)) {
        return false;
    }

    $pesos = [
        [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
        [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    ];

    foreach ($pesos as $offset => $pesosDigito) {
        $soma = 0;
        foreach ($pesosDigito as $indice => $peso) {
            $soma += (int) $cnpj[$indice] * $peso;
        }

        $resto = $soma % 11;
        $digito = $resto < 2 ? 0 : 11 - $resto;
        if ((int) $cnpj[12 + $offset] !== $digito) {
            return false;
        }
    }

    return true;
}
