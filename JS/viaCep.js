function configurarBuscaCep(sufixo) {
  const campoCep = document.getElementById(`cep-${sufixo}`);
  if (!campoCep) {
    return;
  }

  const statusCep = document.getElementById(`cep-status-${sufixo}`);
  const botaoInfo = document.querySelector(`[data-cep-suffix="${sufixo}"]`);
  const modal = document.getElementById(`cep-info-modal-${sufixo}`);
  const mensagemModal = document.getElementById(`cep-info-message-${sufixo}`);
  const detalhesModal = document.getElementById(`cep-info-details-${sufixo}`);
  const botaoFecharModal = modal?.querySelector(".cep-info-close");
  const atualizarStatusCep = (mensagem, estado = "") => {
    statusCep.textContent = mensagem;
    statusCep.classList.toggle("cep-status-sucesso", estado === "sucesso");
    statusCep.classList.toggle("cep-status-erro", estado === "erro");
  };
  const camposEndereco = {
    logradouro: document.getElementById(`logradouro-${sufixo}`),
    bairro: document.getElementById(`bairro-${sufixo}`),
    uf: document.getElementById(`uf-${sufixo}`),
    cidade: document.getElementById(`cidade-${sufixo}`)
  };
  let consultaAtual;

  const atualizarDetalhesModal = endereco => {
    const valores = {
      cep: campoCep.value,
      logradouro: endereco.logradouro || "Não informado",
      bairro: endereco.bairro || "Não informado",
      cidade: endereco.localidade || "Não informado",
      uf: endereco.uf || "Não informado"
    };

    detalhesModal.querySelectorAll("[data-cep-info]").forEach(campo => {
      campo.textContent = valores[campo.dataset.cepInfo] || "Não informado";
    });
    mensagemModal.textContent = `Endereço encontrado para o CEP ${campoCep.value}.`;
    detalhesModal.hidden = false;
  };

  const mostrarErroModal = mensagem => {
    mensagemModal.textContent = mensagem;
    detalhesModal.hidden = true;
  };

  const consultarCep = async (mostrarNoModal = false) => {
    const cep = campoCep.value.replace(/\D/g, "");
    if (cep.length !== 8) {
      atualizarStatusCep("Informe um CEP com 8 números.", "erro");
      if (mostrarNoModal) {
        mostrarErroModal("Informe um CEP válido com 8 números para consultar.");
      }
      return;
    }

    consultaAtual?.abort();
    consultaAtual = new AbortController();
    atualizarStatusCep("Buscando endereço...");
    if (mostrarNoModal) {
      mostrarErroModal("Buscando endereço...");
    }

    try {
      const resposta = await fetch(`https://viacep.com.br/ws/${cep}/json/`, {
        signal: consultaAtual.signal
      });
      if (!resposta.ok) {
        throw new Error("Não foi possível consultar o ViaCEP.");
      }

      const endereco = await resposta.json();
      if (campoCep.value.replace(/\D/g, "") !== cep) {
        return;
      }
      if (endereco.erro) {
        atualizarStatusCep("CEP não encontrado. Confira o número e tente novamente.", "erro");
        if (mostrarNoModal) {
          mostrarErroModal("CEP não encontrado. Confira o número e tente novamente.");
        }
        return;
      }

      camposEndereco.logradouro.value = endereco.logradouro || "";
      camposEndereco.bairro.value = endereco.bairro || "";
      camposEndereco.uf.value = endereco.uf || "";
      camposEndereco.cidade.value = endereco.localidade || "";
      atualizarStatusCep("Endereço preenchido. Informe o número e o complemento, se houver.", "sucesso");
      if (mostrarNoModal) {
        atualizarDetalhesModal(endereco);
      }
    } catch (erro) {
      if (erro.name === "AbortError") {
        return;
      }
      atualizarStatusCep("Não foi possível buscar o CEP. Preencha o endereço manualmente.", "erro");
      if (mostrarNoModal) {
        mostrarErroModal("Não foi possível buscar o CEP. Verifique sua conexão e tente novamente.");
      }
    }
  };

  campoCep.addEventListener('input', function () {
    const numeros = campoCep.value.replace(/\D/g, '').slice(0, 8);
    campoCep.value = numeros.length > 5
      ? `${numeros.slice(0, 5)}-${numeros.slice(5)}`
      : numeros;
    consultaAtual?.abort();
    atualizarStatusCep("");
    if (detalhesModal) {
      detalhesModal.hidden = true;
    }
    Object.values(camposEndereco).forEach(campo => {
      campo.value = '';
    });
  });

  campoCep.addEventListener("blur", () => consultarCep());

  if (
    botaoInfo instanceof HTMLButtonElement &&
    modal instanceof HTMLDialogElement &&
    mensagemModal instanceof HTMLElement &&
    detalhesModal instanceof HTMLDListElement &&
    botaoFecharModal instanceof HTMLButtonElement
  ) {
    botaoInfo.addEventListener("click", () => {
      modal.showModal();
      consultarCep(true);
    });

    botaoFecharModal.addEventListener("click", () => modal.close());
    modal.addEventListener("click", evento => {
      if (evento.target === modal) {
        modal.close();
      }
    });
  } else {
    throw new Error(`Não foi possível inicializar as informações do CEP (${sufixo}).`);
  }
}

configurarBuscaCep('doador');
configurarBuscaCep('ong');