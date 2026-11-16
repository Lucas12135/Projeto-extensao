const ufsBrasileiras = new Set([
    "AC", "AL", "AP", "AM", "BA", "CE", "DF", "ES", "GO",
    "MA", "MT", "MS", "MG", "PA", "PB", "PR", "PE", "PI",
    "RJ", "RN", "RS", "RO", "RR", "SC", "SP", "SE", "TO",
]);
const dddsBrasileiros = new Set([
    "11", "12", "13", "14", "15", "16", "17", "18", "19",
    "21", "22", "24", "27", "28", "31", "32", "33", "34", "35", "37", "38",
    "41", "42", "43", "44", "45", "46", "47", "48", "49",
    "51", "53", "54", "55", "61", "62", "63", "64", "65", "66", "67", "68", "69",
    "71", "73", "74", "75", "77", "79",
    "81", "82", "83", "84", "85", "86", "87", "88", "89",
    "91", "92", "93", "94", "95", "96", "97", "98", "99",
]);

function cpfValido(valor) {
    const cpf = valor.replace(/\D/g, "");
    if (cpf.length !== 11 || /^(\d)\1{10}$/.test(cpf)) {
        return false;
    }

    const digitos = cpf.split("").map(Number);
    const primeiroDigito = calcularDigitoVerificador(
        digitos.slice(0, 9),
        [10, 9, 8, 7, 6, 5, 4, 3, 2],
    );
    const segundoDigito = calcularDigitoVerificador(
        digitos.slice(0, 10),
        [11, 10, 9, 8, 7, 6, 5, 4, 3, 2],
    );
    return digitos[9] === primeiroDigito && digitos[10] === segundoDigito;
}

function cnpjValido(valor) {
    const cnpj = valor.replace(/\D/g, "");
    if (cnpj.length !== 14 || /^(\d)\1{13}$/.test(cnpj)) {
        return false;
    }

    const digitos = cnpj.split("").map(Number);
    const primeiroDigito = calcularDigitoVerificador(
        digitos.slice(0, 12),
        [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    );
    const segundoDigito = calcularDigitoVerificador(
        digitos.slice(0, 13),
        [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2],
    );
    return digitos[12] === primeiroDigito && digitos[13] === segundoDigito;
}

function mensagemValidacao(tipo, valor) {
    const digitos = valor.replace(/\D/g, "");
    if (valor === "") {
        return "";
    }

    if (tipo === "cpf" && !cpfValido(valor)) {
        return "Informe um CPF válido.";
    }
    if (tipo === "cnpj" && !cnpjValido(valor)) {
        return "Informe um CNPJ válido.";
    }
    if (
        tipo === "telefone" &&
        (
            ![10, 11].includes(digitos.length) ||
            !dddsBrasileiros.has(digitos.slice(0, 2)) ||
            /^(\d)\1+$/.test(digitos)
        )
    ) {
        return "Informe um telefone válido com DDD.";
    }
    if (tipo === "cep" && (digitos.length !== 8 || /^0+$/.test(digitos))) {
        return "Informe um CEP válido com 8 números.";
    }
    if (tipo === "uf" && !ufsBrasileiras.has(valor.toUpperCase())) {
        return "Informe a sigla válida de um estado brasileiro.";
    }
    return "";
}

function verificarCampoFormatado(input, exibirErro = false) {
    const mensagem = mensagemValidacao(input.dataset.format, input.value.trim());
    input.setCustomValidity(mensagem);
    input.classList.toggle("documento-invalido", mensagem !== "");

    if (mensagem) {
        input.setAttribute("aria-invalid", "true");
    } else {
        input.removeAttribute("aria-invalid");
    }

    const statusId = input.dataset.validationStatus || `${input.id}-status`;
    const status = statusId ? document.getElementById(statusId) : null;
    if (status) {
        status.textContent = mensagem;
        status.classList.toggle("field-validation-error", mensagem !== "");
    }

    if (exibirErro && mensagem) {
        input.reportValidity();
    }
    return mensagem === "";
}

function verificarCPF(exibirErro = false) {
    const input = document.getElementById("cpf-doador");
    return !(input instanceof HTMLInputElement) || verificarCampoFormatado(input, exibirErro);
}

function verificarCNPJ(exibirErro = false) {
    const input = document.getElementById("cnpj-ong");
    return !(input instanceof HTMLInputElement) || verificarCampoFormatado(input, exibirErro);
}

function calcularDigitoVerificador(digitos, pesos) {
    const soma = digitos.reduce((total, digito, indice) => total + digito * pesos[indice], 0);
    const resto = soma % 11;
    return resto < 2 ? 0 : 11 - resto;
}

document.addEventListener("input", (event) => {
    if (event.target instanceof HTMLInputElement && event.target.dataset.format) {
        verificarCampoFormatado(event.target);
    }
});

document.addEventListener("blur", (event) => {
    if (event.target instanceof HTMLInputElement && event.target.dataset.format) {
        verificarCampoFormatado(event.target);
    }
}, true);

document.querySelectorAll("input[data-format]").forEach((input) => {
    verificarCampoFormatado(input);
});
