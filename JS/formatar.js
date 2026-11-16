function formatarValor(tipo, valor) {
  const texto = String(valor ?? "");
  const digitos = texto.replace(/\D/g, "");

  if (tipo === "cpf") {
    return digitos.slice(0, 11)
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d{1,2})$/, "$1-$2");
  }

  if (tipo === "cnpj") {
    return digitos.slice(0, 14)
      .replace(/(\d{2})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1.$2")
      .replace(/(\d{3})(\d)/, "$1/$2")
      .replace(/(\d{4})(\d{1,2})$/, "$1-$2");
  }

  if (tipo === "telefone") {
    const telefone = digitos.slice(0, 11);
    if (telefone.length <= 2) {
      return telefone;
    }

    const tamanhoPrefixo = telefone.length > 10 ? 5 : 4;
    const prefixo = telefone.slice(2, 2 + tamanhoPrefixo);
    const sufixo = telefone.slice(2 + tamanhoPrefixo);
    return `(${telefone.slice(0, 2)}) ${prefixo}${sufixo ? `-${sufixo}` : ""}`;
  }

  if (tipo === "cep") {
    const cep = digitos.slice(0, 8);
    return cep.length > 5 ? `${cep.slice(0, 5)}-${cep.slice(5)}` : cep;
  }

  if (tipo === "uf") {
    return texto.toUpperCase();
  }

  return texto;
}

function aplicarFormatacao(input) {
  const tipo = input.dataset.format;
  if (tipo) {
    input.value = formatarValor(tipo, input.value);
  }
}

function formatarPorId(id, tipo) {
  const input = document.getElementById(id);
  if (input instanceof HTMLInputElement) {
    input.value = formatarValor(tipo, input.value);
  }
}

function formatarCPF() {
  formatarPorId("cpf-doador", "cpf");
}

function formatarTelefoneDoador() {
  formatarPorId("telefone-doador", "telefone");
}

function formatarTelefoneOng() {
  formatarPorId("telefone-ong", "telefone");
}

function formatarCNPJ() {
  formatarPorId("cnpj-ong", "cnpj");
}

document.addEventListener("input", (event) => {
  if (event.target instanceof HTMLInputElement) {
    aplicarFormatacao(event.target);
  }
});

document.querySelectorAll("input[data-format]").forEach(aplicarFormatacao);
