function verificarCPF(exibirErro = false) {
    const input = document.getElementById('cpf-doador');
    const cpf = input.value.replace(/\D/g, '');
    let valido = cpf.length === 0;

    if (cpf.length === 11 && !/^(\d)\1{10}$/.test(cpf)) {
        const digitos = cpf.split('').map(Number);
        const primeiroDigito = calcularDigitoVerificador(digitos.slice(0, 9), [10, 9, 8, 7, 6, 5, 4, 3, 2]);
        const segundoDigito = calcularDigitoVerificador(digitos.slice(0, 10), [11, 10, 9, 8, 7, 6, 5, 4, 3, 2]);
        valido = digitos[9] === primeiroDigito && digitos[10] === segundoDigito;
    }

    input.setCustomValidity(valido ? '' : 'Informe um CPF válido.');
    atualizarEstadoDocumento(input, 'cpf-status-doador', valido, 'CPF inválido.');
    if (exibirErro && !valido) {
        input.reportValidity();
    }
    return valido;
}

function verificarCNPJ(exibirErro = false) {
    const input = document.getElementById('cnpj-ong');
    const cnpj = input.value.replace(/\D/g, '');
    let valido = cnpj.length === 0;

    if (cnpj.length === 14 && !/^(\d)\1{13}$/.test(cnpj)) {
        const digitos = cnpj.split('').map(Number);
        const primeiroDigito = calcularDigitoVerificador(digitos.slice(0, 12), [5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        const segundoDigito = calcularDigitoVerificador(digitos.slice(0, 13), [6, 5, 4, 3, 2, 9, 8, 7, 6, 5, 4, 3, 2]);
        valido = digitos[12] === primeiroDigito && digitos[13] === segundoDigito;
    }

    input.setCustomValidity(valido ? '' : 'Informe um CNPJ válido.');
    atualizarEstadoDocumento(input, 'cnpj-status-ong', valido, 'CNPJ inválido.');
    if (exibirErro && !valido) {
        input.reportValidity();
    }
    return valido;
}

function atualizarEstadoDocumento(input, statusId, valido, mensagem) {
    const status = document.getElementById(statusId);
    const invalido = input.value.trim() !== '' && !valido;

    input.classList.toggle('documento-invalido', invalido);
    if (invalido) {
        input.setAttribute('aria-invalid', 'true');
        status.textContent = mensagem;
    } else {
        input.removeAttribute('aria-invalid');
        status.textContent = '';
    }
}

function calcularDigitoVerificador(digitos, pesos) {
    const soma = digitos.reduce((total, digito, indice) => total + digito * pesos[indice], 0);
    const resto = soma % 11;
    return resto < 2 ? 0 : 11 - resto;
}