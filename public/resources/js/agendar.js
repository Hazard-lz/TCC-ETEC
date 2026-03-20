/* =========================================
   AGENDAR.JS - LÓGICA DO PASSO A PASSO
   ========================================= */

// Função para avançar de passo
function irParaPasso(passoAtual, proximoPasso) {
    // Esconde o atual
    document.getElementById(`step-${passoAtual}`).classList.remove('active');
    // Mostra o próximo
    document.getElementById(`step-${proximoPasso}`).classList.add('active');
    
    // Atualiza os indicadores (bolinhas lá em cima)
    document.getElementById(`ind-${passoAtual}`).classList.add('completed');
    document.getElementById(`ind-${proximoPasso}`).classList.add('active');
}

// Função para voltar de passo
function voltarPasso(passoAnterior) {
    const passoAtual = passoAnterior + 1;
    
    document.getElementById(`step-${passoAtual}`).classList.remove('active');
    document.getElementById(`step-${passoAnterior}`).classList.add('active');
    
    document.getElementById(`ind-${passoAnterior}`).classList.remove('completed');
    document.getElementById(`ind-${passoAtual}`).classList.remove('active');
}


/* --- LÓGICA DO PASSO 1: SERVIÇOS --- */
function selecionarServico(id, nome) {
    // Salva nos inputs ocultos
    document.getElementById('servico_id').value = id;
    document.getElementById('servico_nome').value = nome;

    // Tira a classe 'selected' de todos os cartões do passo 1
    const cards = document.querySelectorAll('#step-1 .selectable-card');
    cards.forEach(card => card.classList.remove('selected'));

    // Pinta o cartão que foi clicado
    event.currentTarget.classList.add('selected');

    // Libera o botão "Continuar"
    document.getElementById('btn-next-1').removeAttribute('disabled');
}

// Evento do botão avançar 1
document.getElementById('btn-next-1').addEventListener('click', () => {
    irParaPasso(1, 2);
});


/* --- LÓGICA DO PASSO 2: PROFISSIONAIS --- */
function selecionarProfissional(id, nome) {
    document.getElementById('funcionario_id').value = id;
    document.getElementById('funcionario_nome').value = nome;

    const cards = document.querySelectorAll('#step-2 .selectable-card');
    cards.forEach(card => card.classList.remove('selected'));
    
    event.currentTarget.classList.add('selected');
    document.getElementById('btn-next-2').removeAttribute('disabled');
}

document.getElementById('btn-next-2').addEventListener('click', () => {
    irParaPasso(2, 3);
    
    // Configura o calendário para não permitir datas passadas
    const hoje = new Date().toISOString().split('T')[0];
    document.getElementById("data_agendamento").setAttribute('min', hoje);
});


/* --- LÓGICA DO PASSO 3: DATA E HORA --- */
function liberarHorarios() {
    // Mostra os botões de horário só depois que escolher a data
    document.getElementById('box-horarios').style.display = 'block';
    
    // Limpa horário anterior se a pessoa trocar de dia
    document.getElementById('horario_selecionado').value = '';
    document.getElementById('btn-next-3').setAttribute('disabled', 'true');
    const slots = document.querySelectorAll('.time-slot');
    slots.forEach(s => s.classList.remove('selected'));
}

function selecionarHorario(hora, elemento) {
    document.getElementById('horario_selecionado').value = hora;

    const slots = document.querySelectorAll('.time-slot');
    slots.forEach(s => s.classList.remove('selected'));
    
    elemento.classList.add('selected');
    document.getElementById('btn-next-3').removeAttribute('disabled');
}


/* --- LÓGICA DO PASSO 4: RESUMO --- */
function montarResumo() {
    irParaPasso(3, 4);
    
    const servico = document.getElementById('servico_nome').value;
    const profissional = document.getElementById('funcionario_nome').value;
    const dataBruta = document.getElementById('data_agendamento').value;
    const hora = document.getElementById('horario_selecionado').value;
    
    // Formata a data (de YYYY-MM-DD para DD/MM/YYYY)
    const partesData = dataBruta.split('-');
    const dataFormatada = `${partesData[2]}/${partesData[1]}/${partesData[0]}`;

    // Joga os textos na tela final
    document.getElementById('resumo_servico').textContent = servico;
    document.getElementById('resumo_pro').textContent = profissional;
    document.getElementById('resumo_datahora').textContent = `${dataFormatada} às ${hora}`;
}