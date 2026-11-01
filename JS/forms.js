function trocarAba(tab) {
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));

  event.target.classList.add('active');
  document.getElementById(`form-${tab}`).classList.add('active');
}


function trocarAbaCadastro(tab, botao) {
  document.querySelectorAll('.signup-tabs .tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.signup-form').forEach(form => form.classList.remove('active'));

  botao.classList.add('active');
  document.getElementById(`form-signup-${tab}`).classList.add('active');
}

const ongForm = document.getElementById('form-signup-ong');

if (ongForm) {
  const ongStep1 = document.getElementById('ong-step-1');
  const ongStep2 = document.getElementById('ong-step-2');
  const ongStep3 = document.getElementById('ong-step-3');
  const stepLabel = document.getElementById('ong-step-label');

  document.getElementById('ong-next-step').addEventListener('click', function () {
    if (!ongStep1.reportValidity()) {
      return;
    }

    const passwords = ongStep1.querySelectorAll('input[type="password"]');
    if (passwords[0].value !== passwords[1].value) {
      alert('As senhas não conferem!');
      return;
    }

    ongStep1.disabled = true;
    ongStep1.hidden = true;
    ongStep2.disabled = false;
    ongStep2.hidden = false;
    stepLabel.textContent = 'Etapa 2 de 3: CNPJ, localização e imagem';
  });

  document.getElementById('ong-previous-step').addEventListener('click', function () {
    ongStep2.disabled = true;
    ongStep2.hidden = true;
    ongStep1.disabled = false;
    ongStep1.hidden = false;
    stepLabel.textContent = 'Etapa 1 de 3: Dados da ONG';
  });

  document.getElementById('ong-next-step-2').addEventListener('click', function () {
    if (!ongStep2.reportValidity()) {
      return;
    }

    ongStep2.disabled = true;
    ongStep2.hidden = true;
    ongStep3.disabled = false;
    ongStep3.hidden = false;
    stepLabel.textContent = 'Etapa 3 de 3: Informações da ONG';
  });

  document.getElementById('ong-previous-step-2').addEventListener('click', function () {
    ongStep3.disabled = true;
    ongStep3.hidden = true;
    ongStep2.disabled = false;
    ongStep2.hidden = false;
    stepLabel.textContent = 'Etapa 2 de 3: CNPJ, localização e imagem';
  });

  ongForm.addEventListener('submit', function () {
    ongStep1.disabled = false;
    ongStep2.disabled = false;
  });
}

const doadorForm = document.getElementById('form-signup-doador');

if (doadorForm) {
  const doadorStep1 = document.getElementById('doador-step-1');
  const doadorStep2 = document.getElementById('doador-step-2');
  const stepLabel = document.getElementById('doador-step-label');

  document.getElementById('doador-next-step').addEventListener('click', function () {
    if (!doadorStep1.reportValidity()) {
      return;
    }

    const passwords = doadorStep1.querySelectorAll('input[type="password"]');
    if (passwords[0].value !== passwords[1].value) {
      alert('As senhas não conferem!');
      return;
    }

    doadorStep1.disabled = true;
    doadorStep1.hidden = true;
    doadorStep2.disabled = false;
    doadorStep2.hidden = false;
    stepLabel.textContent = 'Etapa 2 de 2: CPF e endereço';
  });

  document.getElementById('doador-previous-step').addEventListener('click', function () {
    doadorStep2.disabled = true;
    doadorStep2.hidden = true;
    doadorStep1.disabled = false;
    doadorStep1.hidden = false;
    stepLabel.textContent = 'Etapa 1 de 2: Dados pessoais';
  });

  doadorForm.addEventListener('submit', function () {
    doadorStep1.disabled = false;
  });
}

document.querySelectorAll('.signup-form').forEach(form => {
  form.addEventListener('submit', function (e) {
    const senhas = form.querySelectorAll('input[type="password"]');

    if (senhas[0].value !== senhas[1].value) {
      e.preventDefault();
      alert('As senhas não conferem!');
    }
  });
});