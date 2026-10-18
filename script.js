const dadosOngs = {
  'ONG ABC': {
    distancia: '3 km',
    doacoes: 'Alimentos, Roupas',
    sobre: 'Organização focada em ações comunitárias diretas para famílias em situação de vulnerabilidade.',
    missao: 'Promover inclusão social e dignidade através de ações solidárias e apoio comunitário contínuo.',
    projetos: ['Distribuição de Alimentos', 'Coleta de Roupas', 'Atendimento Familiar'],
    localizacao: 'Caiçara - Praia Grande',
    telefone: '(11) 3456-7890',
    email: 'contato@acaocomunitaria.org'
  },
  'Educar para Crescer': {
    distancia: '5 km',
    doacoes: 'Livros, Alimentos',
    sobre: 'Dedicada à educação infantil e reforço escolar para crianças de baixa renda.',
    missao: 'Garantir oportunidades de aprendizado e desenvolvimento pessoal para crianças e adolescentes.',
    projetos: ['Reforço Escolar', 'Biblioteca Comunitária', 'Oficinas de Capacitação'],
    localizacao: 'Jardim Real - Praia Grande',
    telefone: '(11) 3456-7891',
    email: 'contato@educarparacrescer.org'
  },
  'Saúde para Todos': {
    distancia: '8 km',
    doacoes: 'Higiene, Doações',
    sobre: 'Oferece atendimento de saúde básico e campanhas de prevenção em comunidades carentes.',
    missao: 'Democratizar o acesso à saúde e bem-estar com ações de prevenção e orientação.',
    projetos: ['Campanhas de Vacinação', 'Atendimento Médico', 'Oficinas de Saúde'],
    localizacao: 'Samambaia - Praia Grande',
    telefone: '(11) 3456-7892',
    email: 'contato@saudepatodos.org'
  },
  'Bem-Vindo ao Lar': {
    distancia: '12 km',
    doacoes: 'Roupas, Higiene',
    sobre: 'Programa de acolhimento para pessoas em situação de rua com apoio integral.',
    missao: 'Oferecer acolhimento digno e apoio psicossocial para pessoas sem moradia.',
    projetos: ['Acolhimento Noturno', 'Higiene Pessoal', 'Reintegração Social'],
    localizacao: 'Boqueirão - Praia Grande',
    telefone: '(11) 3456-7893',
    email: 'contato@bemvindoaoar.org'
  },
  'Juntos Somos Fortes': {
    distancia: '15 km',
    doacoes: 'Alimentos, Livros, Doações',
    sobre: 'Rede colaborativa de voluntários que trabalha múltiplas frentes de assistência social.',
    missao: 'Mobilizar a comunidade para transformação social através de trabalho colaborativo.',
    projetos: ['Alimentação Solidária', 'Educação Infantil', 'Treinamento Profissional'],
    localizacao: 'Ocian - Praia Grande',
    telefone: '(11) 3456-7894',
    email: 'contato@juntossomofortes.org'
  },
  'Futuro Melhor': {
    distancia: '7 km',
    doacoes: 'Livros, Higiene',
    sobre: 'Iniciativa focada em preparar jovens para o mercado de trabalho e desenvolvimento pessoal.',
    missao: 'Criar oportunidades de emprego e autonomia para jovens em comunidades carentes.',
    projetos: ['Cursos Profissionalizantes', 'Mentoria Profissional', 'Inserção no Mercado'],
    localizacao: 'Solemar - Praia Grande',
    telefone: '(11) 3456-7895',
    email: 'contato@futuromelhor.org'
  }
};
    
    document.querySelectorAll('.ong-card').forEach(cartao => {
      cartao.addEventListener('click', function() {
        const nome = this.dataset.nome;
        
        const dados = dadosOngs[nome];

        document.querySelectorAll('.ong-card').forEach(c => c.classList.remove('active'));
        
        this.classList.add('active');

        document.querySelector('.details-placeholder').style.display = 'none';
        
        document.getElementById('detailsContent').style.display = 'block';

        document.getElementById('detailsImage').src = 'resource/logo.jpeg';
        document.getElementById('detailsImage').alt = nome;
        document.getElementById('detailsName').textContent = nome;
        document.getElementById('detailsDistance').textContent = `${dados.distancia}`;
        document.getElementById('detailsDonations').textContent = dados.doacoes;
        document.getElementById('detailsAbout').textContent = dados.sobre;
        document.getElementById('detailsMission').textContent = dados.missao;
        document.getElementById('detailsContact').textContent = dados.localizacao;
        document.getElementById('detailsPhone').innerHTML = `<strong>Telefone:</strong> <a href="">${dados.telefone}</a>`;
        document.getElementById('detailsEmail').innerHTML = `<strong>Email:</strong> <a href="">${dados.email}</a>`;

        const listaProjetos = document.getElementById('detailsProjects');
        
        listaProjetos.innerHTML = '';
        
        dados.projetos.forEach(projeto => {
          const item = document.createElement('li');
          item.textContent = projeto;
          listaProjetos.appendChild(item);
        });
      });
    });

    document.getElementById('searchInput').addEventListener('input', function() {
      const termoPesquisa = this.value.toLowerCase();
      
      document.querySelectorAll('.ong-card').forEach(cartao => {
        const nome = cartao.dataset.nome.toLowerCase();
        
        cartao.style.display = nome.includes(termoPesquisa) ? 'flex' : 'none';
      });
    });


    document.getElementById('distanceFilter').addEventListener('change', function() {
      const distanciaMaxima = this.value;
      
      document.querySelectorAll('.ong-card').forEach(cartao => {
        if (!distanciaMaxima) {
          cartao.style.display = 'flex';
        } else {
          const distancia = parseInt(cartao.dataset.distancia);
          cartao.style.display = distancia <= distanciaMaxima ? 'flex' : 'none';
        }
      });
    });

    document.getElementById('donationsFilter').addEventListener('change', function() {
      const tipoSelecionado = this.value;
      
      document.querySelectorAll('.ong-card').forEach(cartao => {
        if (!tipoSelecionado) {
          cartao.style.display = 'flex';
        } else {
          const doacoes = cartao.dataset.doacoes;
          cartao.style.display = doacoes.includes(tipoSelecionado) ? 'flex' : 'none';
        }
      });
    });

    document.getElementById('sortFilter').addEventListener('change', function() {
      const tipoOrdenacao = this.value;
      
      const contenedor = document.querySelector('.ongs-list');
      
      const cartoes = Array.from(document.querySelectorAll('.ong-card'));

      cartoes.sort((a, b) => {
        switch(tipoOrdenacao) {
          case 'alfabetico':
            return a.dataset.nome.localeCompare(b.dataset.nome);

          case 'alfabetico-desc':
            return b.dataset.nome.localeCompare(a.dataset.nome);

          case 'distancia':
            return parseInt(a.dataset.distancia) - parseInt(b.dataset.distancia);

          default:
            return 0;
        }
      });

      cartoes.forEach(cartao => contenedor.appendChild(cartao));
    });

    document.querySelector('.btn-reset').addEventListener('click', function() {
      document.getElementById('searchInput').value = '';
      
      document.getElementById('distanceFilter').value = '';
      
      document.getElementById('donationsFilter').value = '';

      document.getElementById('sortFilter').value = 'alfabetico';
      
      document.querySelectorAll('.ong-card').forEach(cartao => {
        cartao.style.display = 'flex';
      });
    });


function trocarAba(tab) {
  document.querySelectorAll('.tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.login-form').forEach(form => form.classList.remove('active'));

  event.target.classList.add('active');
  document.getElementById(`form-${tab}`).classList.add('active');
}


function trocarAbaCadastro(tab) {
  document.querySelectorAll('.signup-tabs .tab-button').forEach(btn => btn.classList.remove('active'));
  document.querySelectorAll('.signup-form').forEach(form => form.classList.remove('active'));

  event.target.classList.add('active');
  document.getElementById(`form-signup-${tab}`).classList.add('active');
}

  document.getElementById('form-signup-doador').addEventListener('submit', function(e) {
    const senha = document.getElementById('senha-doador').value;
    const confirmarSenha = document.getElementById('confirmar-senha-doador').value;
    
    if (senha !== confirmarSenha) {
      alert('As senhas não conferem!');
      return;
    }
  });

  document.getElementById('form-signup-ong').addEventListener('submit', function(e) {
    const senha = document.getElementById('senha-ong').value;
    const confirmarSenha = document.getElementById('confirmar-senha-ong').value;
    
    if (senha !== confirmarSenha) {
      alert('As senhas não conferem!');
      return;
    }
  });