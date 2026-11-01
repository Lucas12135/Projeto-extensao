function formatarCPF() {
  const cpfInput = document.getElementById('cpf-doador');
  let cpf = cpfInput.value.replace(/\D/g, '');

  if (cpf.length > 11) {
    cpf = cpf.slice(0, 11);
  }

  cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
  cpf = cpf.replace(/(\d{3})(\d)/, '$1.$2');
  cpf = cpf.replace(/(\d{3})(\d{1,2})$/, '$1-$2');

  cpfInput.value = cpf;
}

function formatarTelefoneDoador() {
  const telefoneInput = document.getElementById('telefone-doador');
  let telefone = telefoneInput.value.replace(/\D/g, '');

  if (telefone.length > 11) {
    telefone = telefone.slice(0, 11);
  }

  telefone = telefone.replace(/(\d{2})(\d)/, '($1) $2');
  telefone = telefone.replace(/(\d{5})(\d)/, '$1-$2');

  telefoneInput.value = telefone;
}

function formatarTelefoneOng() {
  const telefoneInput = document.getElementById('telefone-ong');
  let telefone = telefoneInput.value.replace(/\D/g, '');

  if (telefone.length > 11) {
    telefone = telefone.slice(0, 11);
  }

  telefone = telefone.replace(/(\d{2})(\d)/, '($1) $2');
  telefone = telefone.replace(/(\d{5})(\d)/, '$1-$2');

  telefoneInput.value = telefone;
}

function formatarCNPJ() {
  const cnpjInput = document.getElementById('cnpj-ong');
  let cnpj = cnpjInput.value.replace(/\D/g, '');

  if (cnpj.length > 14) {
    cnpj = cnpj.slice(0, 14);
  }

  cnpj = cnpj.replace(/(\d{2})(\d)/, '$1.$2');
  cnpj = cnpj.replace(/(\d{3})(\d)/, '$1.$2');
  cnpj = cnpj.replace(/(\d{3})(\d)/, '$1/$2');
  cnpj = cnpj.replace(/(\d{4})(\d{1,2})$/, '$1-$2');

  cnpjInput.value = cnpj;
}