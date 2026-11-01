function configurarBuscaCep(sufixo) {
  const campoCep = document.getElementById(`cep-${sufixo}`);
  if (!campoCep) {
    return;
  }

  const statusCep = document.getElementById(`cep-status-${sufixo}`);
  const camposEndereco = {
    logradouro: document.getElementById(`logradouro-${sufixo}`),
    bairro: document.getElementById(`bairro-${sufixo}`),
    uf: document.getElementById(`uf-${sufixo}`),
    cidade: document.getElementById(`cidade-${sufixo}`)
  };
  let consultaAtual;

  campoCep.addEventListener('input', function () {
    const numeros = campoCep.value.replace(/\D/g, '').slice(0, 8);
    campoCep.value = numeros.length > 5
      ? `${numeros.slice(0, 5)}-${numeros.slice(5)}`
      : numeros;
    consultaAtual?.abort();
    statusCep.textContent = '';
    Object.values(camposEndereco).forEach(campo => {
      campo.value = '';
    });
  });

  campoCep.addEventListener('blur', async function () {
    const cep = campoCep.value.replace(/\D/g, '');
    if (cep.length !== 8) {
      statusCep.textContent = 'Informe um CEP com 8 números.';
      return;
    }

    consultaAtual?.abort();
    consultaAtual = new AbortController();
    statusCep.textContent = 'Buscando endereço...';

    try {
      const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
        signal: consultaAtual.signal
      });
      if (!resposta.ok) {
        throw new Error('Não foi possível consultar o ViaCEP.');
      }

      const endereco = await resposta.json();
      if (campoCep.value.replace(/\D/g, '') !== cep) {
        return;
      }
      if (endereco.erro) {
        statusCep.textContent = 'CEP não encontrado. Confira o número e tente novamente.';
        return;
      }

      camposEndereco.logradouro.value = endereco.logradouro || '';
      camposEndereco.bairro.value = endereco.bairro || '';
      camposEndereco.uf.value = endereco.uf || '';
      camposEndereco.cidade.value = endereco.localidade || '';
      statusCep.textContent = 'Endereço preenchido. Informe o número e o complemento, se houver.';
    } catch (erro) {
      if (erro.name === 'AbortError') {
        return;
      }
      statusCep.textContent = 'Não foi possível buscar o CEP. Preencha o endereço manualmente.';
    }
  });
}

configurarBuscaCep('doador');
configurarBuscaCep('ong');