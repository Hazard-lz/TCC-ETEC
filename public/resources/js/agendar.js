/* =========================================
   AGENDAR.JS - LÓGICA DO PASSO A PASSO
   ========================================= */

// ─── Helpers do Resumo Lateral ────────────────────────────────────────────────

/**
 * Atualiza um campo do resumo lateral.
 * Se o valor for vazio/null, aplica a classe "placeholder".
 */
function atualizarResumo(id, valor) {
  const el = document.getElementById(id);
  if (!el) return;

  if (!valor) {
    el.textContent = 'A selecionar...';
    el.classList.add('placeholder');
  } else {
    el.textContent = valor;
    el.classList.remove('placeholder');
  }
}

/** Formata número como moeda brasileira: 1234.5 → "R$ 1.234,50" */
function formatarPreco(valor) {
  if (!valor && valor !== 0) return 'R$ --';
  return 'R$ ' + parseFloat(valor).toLocaleString('pt-BR', {
    minimumFractionDigits: 2,
    maximumFractionDigits: 2
  });
}

// ─── Estado dos botões ───────────────────────────────────────────────────────

/**
 * Habilita/desabilita o botão global (aside) e o botão mobile do passo atual.
 * No último passo (4), o botão global vira "submit" e o texto muda.
 */
function setBotoesPasso(passo, habilitado) {
  const btnGlobal = document.getElementById('btn-continuar-global');
  const btnMobile = document.getElementById(`btn-next-${passo}`);

  if (btnGlobal) {
    btnGlobal.disabled = !habilitado;

    if (passo === 4) {
      btnGlobal.textContent = '✅ Confirmar Agendamento';
    } else if (passo === 3) {
      btnGlobal.textContent = 'Ver Resumo';
    } else {
      btnGlobal.textContent = 'Continuar';
    }
  }

  if (btnMobile) {
    btnMobile.disabled = !habilitado;
  }
}

// ─── Navegação entre Passos ───────────────────────────────────────────────────

/** Retorna todos os .step-connector em ordem */
function obterConectores() {
  return document.querySelectorAll('.step-connector');
}

/**
 * Atualiza a cor dos conectores com base no passo atual.
 * index 0 = conector entre passo 1 e 2, index 1 = entre 2 e 3, etc.
 */
function atualizarConectores(passoAtual) {
  const conectores = obterConectores();
  conectores.forEach((c, i) => {
    if (i < passoAtual - 1) {
      c.classList.add('done');
    } else {
      c.classList.remove('done');
    }
  });
}

function irParaPasso(passoAtual, proximoPasso) {
  document.getElementById(`step-${passoAtual}`).classList.remove('active');
  document.getElementById(`step-${proximoPasso}`).classList.add('active');
  document.getElementById(`ind-${passoAtual}`).classList.add('completed');
  document.getElementById(`ind-${proximoPasso}`).classList.add('active');
  atualizarConectores(proximoPasso);

  // Bloqueia o botão ao entrar no novo passo (exceto passo 4 que já pode confirmar)
  if (proximoPasso < 4) {
    setBotoesPasso(proximoPasso, false);
  } else {
    // No passo 4 o botão global já pode enviar o formulário
    setBotoesPasso(4, true);
  }
}

function voltarPasso(passoAnterior) {
  const passoAtual = passoAnterior + 1;
  document.getElementById(`step-${passoAtual}`).classList.remove('active');
  document.getElementById(`step-${passoAnterior}`).classList.add('active');
  document.getElementById(`ind-${passoAnterior}`).classList.remove('completed');
  document.getElementById(`ind-${passoAtual}`).classList.remove('active');
  atualizarConectores(passoAnterior);

  // Ao voltar, o botão deve refletir se há seleção no passo destino
  // Verificamos os valores já salvos
  let jaTemSelecao = false;
  if (passoAnterior === 1) jaTemSelecao = !!document.getElementById('servico_id').value;
  if (passoAnterior === 2) jaTemSelecao = !!document.getElementById('funcionario_id').value;
  if (passoAnterior === 3) jaTemSelecao = !!document.getElementById('horario_selecionado').value;

  setBotoesPasso(passoAnterior, jaTemSelecao);
}

/** Descobre qual passo está atualmente ativo (1-4) */
function passoAtivo() {
  for (let i = 4; i >= 1; i--) {
    const el = document.getElementById(`step-${i}`);
    if (el && el.classList.contains('active')) return i;
  }
  return 1;
}

// ─── Botão Global (aside — desktop) ──────────────────────────────────────────

document.getElementById('btn-continuar-global').addEventListener('click', () => {
  const atual = passoAtivo();

  if (atual === 4) {
    // Último passo: envia o formulário
    document.getElementById('formWizardAgendamento').submit();
    return;
  }

  if (atual === 3) {
    montarResumo(); // avança para passo 4
    return;
  }

  if (atual === 2) {
    // Avança para passo 3 e configura data mínima
    irParaPasso(2, 3);
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById('data_agendamento').setAttribute('min', hoje);
    return;
  }

  // Passo 1 → 2
  irParaPasso(1, 2);
});

// ─── PASSO 1: SERVIÇOS ────────────────────────────────────────────────────────

/**
 * @param {string}  id       - ID do serviço
 * @param {string}  nome     - Nome do serviço
 * @param {number}  preco    - Preço (float)
 * @param {Element} elemento - Card clicado
 */
function selecionarServico(id, nome, preco, elemento) {
  document.getElementById('servico_id').value = id;
  document.getElementById('servico_nome').value = nome;
  document.getElementById('servico_preco').value = preco;

  // Atualiza resumo lateral
  atualizarResumo('lat-servico', nome);
  const elPreco = document.getElementById('lat-preco');
  if (elPreco) elPreco.textContent = formatarPreco(preco);

  // Marca card visualmente
  document.querySelectorAll('#step-1 .selectable-card').forEach(c => c.classList.remove('selected'));
  elemento.classList.add('selected');

  // Habilita botões (global + mobile)
  setBotoesPasso(1, true);

  // Carrega profissionais
  buscarProfissionais(id);
}

async function buscarProfissionais(idServico) {
  const container = document.getElementById('container-profissionais');
  container.innerHTML = '<p style="color: var(--text-muted); text-align: center;">A buscar especialistas...</p>';

  try {
    const response = await fetch(`${BASE_URL}/api/profissionais-por-servico?id_servico=${idServico}`);
    const data = await response.json();
    container.innerHTML = '';

    if (data.sucesso && data.profissionais.length > 0) {
      data.profissionais.forEach((prof) => {
        const especialidade = prof.especialidade || 'Profissional';
        const div = document.createElement('div');
        div.className = 'base-card selectable-card';
        div.style.cssText = 'padding: 1rem; margin-bottom: 0.8rem; cursor: pointer;';
        div.onclick = function () { selecionarProfissional(prof.id_funcionario, prof.nome, this); };
        div.innerHTML = `
          <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.3rem;">${prof.nome}</h4>
          <p style="color: var(--text-muted); font-size: 0.9rem;">${especialidade}</p>
        `;
        container.appendChild(div);
      });
    } else {
      container.innerHTML = '<p style="color: #ef4444; text-align: center;">Não há profissionais para este serviço.</p>';
      alert('Nenhum profissional está configurado para realizar este serviço no momento. Por favor, escolha outro.');

      document.querySelectorAll('#step-1 .selectable-card').forEach(c => c.classList.remove('selected'));
      document.getElementById('servico_id').value = '';
      document.getElementById('servico_nome').value = '';
      document.getElementById('servico_preco').value = '';
      atualizarResumo('lat-servico', null);
      const elPreco = document.getElementById('lat-preco');
      if (elPreco) elPreco.textContent = 'R$ --';

      setBotoesPasso(1, false);
    }
  } catch (error) {
    console.error('Erro na API:', error);
    container.innerHTML = '<p style="color: #ef4444; text-align: center;">Erro ao carregar profissionais.</p>';
    alert('Falha ao contactar o servidor. Tente novamente.');
    setBotoesPasso(1, false);
  }
}

// Listener do botão mobile do passo 1
document.getElementById('btn-next-1').addEventListener('click', () => irParaPasso(1, 2));

// ─── PASSO 2: PROFISSIONAIS ───────────────────────────────────────────────────

function selecionarProfissional(id, nome, elemento) {
  document.getElementById('funcionario_id').value = id;
  document.getElementById('funcionario_nome').value = nome;

  atualizarResumo('lat-profissional', nome);

  document.querySelectorAll('#step-2 .selectable-card').forEach(c => c.classList.remove('selected'));
  elemento.classList.add('selected');

  setBotoesPasso(2, true);
}

// Listener do botão mobile do passo 2
document.getElementById('btn-next-2').addEventListener('click', () => {
  irParaPasso(2, 3);
  const hoje = new Date().toISOString().split('T')[0];
  document.getElementById('data_agendamento').setAttribute('min', hoje);
});

// ─── PASSO 3: DATA E HORA ─────────────────────────────────────────────────────

async function liberarHorarios() {
  const dataSelecionada = document.getElementById('data_agendamento').value;
  const idServico = document.getElementById('servico_id').value;
  const idFuncionario = document.getElementById('funcionario_id').value;

  const boxHorarios = document.getElementById('box-horarios');
  const containerHorarios = document.getElementById('container-horarios-dinamicos');

  boxHorarios.style.display = 'block';
  document.getElementById('horario_selecionado').value = '';
  setBotoesPasso(3, false); // Bloqueia enquanto escolhe o horário

  containerHorarios.innerHTML = '<p>A calcular horários disponíveis...</p>';

  if (!dataSelecionada || !idServico || !idFuncionario) return;

  try {
    const response = await fetch(BASE_URL + '/api/horarios-livres', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ id_funcionario: idFuncionario, data: dataSelecionada, id_servico: idServico }),
    });
    const data = await response.json();
    containerHorarios.innerHTML = '';

    if (data.sucesso && data.horarios.length > 0) {
      data.horarios.forEach((hora) => {
        const horaFormatada = hora.substring(0, 5);
        const div = document.createElement('div');
        div.className = 'time-slot';
        div.textContent = horaFormatada;
        div.onclick = function () { selecionarHorario(hora, this); };
        containerHorarios.appendChild(div);
      });
    } else {
      containerHorarios.innerHTML = '<p style="color: red;">Nenhum horário disponível.</p>';
    }
  } catch (error) {
    console.error('Erro na API de Disponibilidade:', error);
    containerHorarios.innerHTML = '<p>Erro ao carregar horários.</p>';
  }
}

function selecionarHorario(hora, elemento) {
  document.getElementById('horario_selecionado').value = hora;

  // Atualiza resumo lateral
  const dataBruta = document.getElementById('data_agendamento').value;
  if (dataBruta) {
    const [ano, mes, dia] = dataBruta.split('-');
    atualizarResumo('lat-datahora', `${dia}/${mes}/${ano} às ${hora.substring(0, 5)}`);
  }

  document.querySelectorAll('.time-slot').forEach(s => s.classList.remove('selected'));
  elemento.classList.add('selected');

  setBotoesPasso(3, true);
}

// Listener do botão mobile do passo 3
document.getElementById('btn-next-3').addEventListener('click', () => montarResumo());

// ─── PASSO 4: CONFIRMAÇÃO ─────────────────────────────────────────────────────

function montarResumo() {
  irParaPasso(3, 4);
  // No passo 4 o botão global já está habilitado (feito dentro de irParaPasso)
}

// ─── Clique nos Indicadores do Stepper (navegar para passos anteriores) ────────

document.querySelectorAll('.step-indicator[data-passo]').forEach((indicador) => {
  indicador.addEventListener('click', () => {
    const destino = parseInt(indicador.dataset.passo, 10);
    const atual = passoAtivo();

    // Só permite navegar para passos já concluídos (não para frente)
    if (destino >= atual) return;

    // Esconde o passo atual e mostra o destino
    document.getElementById(`step-${atual}`).classList.remove('active');
    document.getElementById(`step-${destino}`).classList.add('active');

    // Limpa estados dos indicadores entre destino e atual
    for (let i = destino + 1; i <= atual; i++) {
      const ind = document.getElementById(`ind-${i}`);
      ind.classList.remove('active');
      if (i > destino) ind.classList.remove('completed');
    }

    // Destino fica como 'active', não 'completed'
    const indDestino = document.getElementById(`ind-${destino}`);
    indDestino.classList.remove('completed');
    indDestino.classList.add('active');

    atualizarConectores(destino);

    // Restaura estado do botão para o passo destino
    let jaTemSelecao = false;
    if (destino === 1) jaTemSelecao = !!document.getElementById('servico_id').value;
    if (destino === 2) jaTemSelecao = !!document.getElementById('funcionario_id').value;
    if (destino === 3) jaTemSelecao = !!document.getElementById('horario_selecionado').value;

    setBotoesPasso(destino, jaTemSelecao);
  });
});
