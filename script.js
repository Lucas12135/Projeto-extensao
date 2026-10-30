const ongsList = document.getElementById('ongsList');

if (ongsList) {
  const searchInput = document.getElementById('searchInput');
  const sortFilter = document.getElementById('sortFilter');
  const cards = Array.from(ongsList.querySelectorAll('.ong-card'));
  const details = document.getElementById('ongDetails');

  function showOng(card) {
    cards.forEach(item => {
      const selected = item === card;
      item.classList.toggle('active', selected);
      item.setAttribute('aria-pressed', selected);
    });

    document.getElementById('detailsName').textContent = card.dataset.nome;
    document.getElementById('detailsImage').src = card.dataset.imagem;
    document.getElementById('detailsLocation').textContent = card.dataset.localizacao || 'Não informada';
    document.getElementById('detailsDistance').textContent = card.dataset.distancia
      ? `${card.dataset.distancia} km`
      : 'Distância não informada';
    document.getElementById('detailsDonations').textContent = card.dataset.doacoes || 'Não informado';
    document.getElementById('detailsAbout').textContent = card.dataset.sobre || 'Não informado';
    document.getElementById('detailsMission').textContent = card.dataset.missao || 'Não informada';
    document.getElementById('detailsProjects').textContent = card.dataset.projetos || 'Não informados';
    document.getElementById('detailsPhone').textContent = card.dataset.telefone || 'Telefone não informado';
    document.getElementById('detailsEmail').textContent = card.dataset.email || 'E-mail não informado';
  }

  cards.forEach(card => {
    card.addEventListener('click', () => showOng(card));
  });

  function filterOngs() {
    const search = searchInput.value.trim().toLocaleLowerCase('pt-BR');

    cards.forEach(card => {
      const name = card.dataset.nome.toLocaleLowerCase('pt-BR');
      card.style.display = name.includes(search) ? 'flex' : 'none';
    });

    const visibleCard = cards.find(card => card.style.display !== 'none');
    const selectedCard = cards.find(card => card.classList.contains('active'));
    if (visibleCard && selectedCard.style.display === 'none') {
      showOng(visibleCard);
    }

    details.style.display = visibleCard ? 'block' : 'none';
  }

  function sortOngs() {
    cards.sort((a, b) => {
      const comparison = a.dataset.nome.localeCompare(b.dataset.nome, 'pt-BR');
      return sortFilter.value === 'alfabetico-desc' ? -comparison : comparison;
    });

    cards.forEach(card => ongsList.appendChild(card));
  }

  searchInput.addEventListener('input', filterOngs);
  sortFilter.addEventListener('change', sortOngs);

  document.querySelector('.btn-reset').addEventListener('click', function() {
    searchInput.value = '';
    sortFilter.value = 'alfabetico';
    sortOngs();
    filterOngs();
  });
}


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
  const stepLabel = document.getElementById('ong-step-label');

  document.getElementById('ong-next-step').addEventListener('click', function() {
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
    stepLabel.textContent = 'Etapa 2 de 2: Informações da ONG';
  });

  document.getElementById('ong-previous-step').addEventListener('click', function() {
    ongStep2.disabled = true;
    ongStep2.hidden = true;
    ongStep1.disabled = false;
    ongStep1.hidden = false;
    stepLabel.textContent = 'Etapa 1 de 2: Dados da ONG';
  });

  ongForm.addEventListener('submit', function() {
    ongStep1.disabled = false;
  });
}

document.querySelectorAll('.signup-form').forEach(form => {
  form.addEventListener('submit', function(e) {
    const senhas = form.querySelectorAll('input[type="password"]');

    if (senhas[0].value !== senhas[1].value) {
      e.preventDefault();
      alert('As senhas não conferem!');
    }
  });
});