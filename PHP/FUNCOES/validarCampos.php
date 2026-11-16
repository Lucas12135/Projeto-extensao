<?php
function validarTelefone(?string $telefone): bool
{
    if ($telefone === null || !preg_match('/\A[\d()\-\s]+\z/', $telefone)) {
        return false;
    }

    $digitos = preg_replace('/\D/', '', $telefone);
    if (!is_string($digitos) || !in_array(strlen($digitos), [10, 11], true)) {
        return false;
    }

    $ddds = [
        "11", "12", "13", "14", "15", "16", "17", "18", "19",
        "21", "22", "24", "27", "28", "31", "32", "33", "34", "35", "37", "38",
        "41", "42", "43", "44", "45", "46", "47", "48", "49",
        "51", "53", "54", "55", "61", "62", "63", "64", "65", "66", "67", "68", "69",
        "71", "73", "74", "75", "77", "79",
        "81", "82", "83", "84", "85", "86", "87", "88", "89",
        "91", "92", "93", "94", "95", "96", "97", "98", "99",
    ];

    return in_array(substr($digitos, 0, 2), $ddds, true)
        && !preg_match('/\A(\d)\1+\z/', $digitos);
}

function validarCep(?string $cep): bool
{
    if ($cep === null || !preg_match('/\A\d{5}-?\d{3}\z/', $cep)) {
        return false;
    }

    return preg_replace('/\D/', '', $cep) !== "00000000";
}

function validarUf(?string $uf): bool
{
    static $ufs = [
        "AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO",
        "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI",
        "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO",
    ];

    return is_string($uf) && in_array(strtoupper(trim($uf)), $ufs, true);
}
