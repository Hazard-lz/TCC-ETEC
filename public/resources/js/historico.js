/* =========================================
   HISTORICO.JS - LÓGICA DAS ABAS
   ========================================= */

function mudarAba(abaId, elementoClicado) {
    // 1. Tira a classe 'active' de todas as abas (conteúdos)
    const conteudos = document.querySelectorAll('.tab-content');
    conteudos.forEach(conteudo => {
        conteudo.classList.remove('active');
    });

    // 2. Tira a classe 'active' de todos os botões
    const botoes = document.querySelectorAll('.tab-btn');
    botoes.forEach(botao => {
        botao.classList.remove('active');
    });

    // 3. Adiciona a classe 'active' na aba escolhida e no botão clicado
    document.getElementById(`aba-${abaId}`).classList.add('active');
    elementoClicado.classList.add('active');
}

// ── Confirmação de cancelamento de agendamento (SweetAlert2 — Tema Belezou) ──
function cancelarAgendamento(idAgendamento) {
    Swal.fire({
        title:             'Cancelar Agendamento?',
        text:              'Esta ação não pode ser desfeita. Tem certeza que deseja cancelar?',
        icon:              'warning',
        showCancelButton:  true,
        confirmButtonText: 'Sim, cancelar',
        cancelButtonText:  'Não, manter',
        customClass: {
            popup:         'swal-belezou-popup',
            title:         'swal-belezou-title',
            htmlContainer: 'swal-belezou-text',
            confirmButton: 'swal-belezou-btn-danger',
            cancelButton:  'swal-belezou-btn-cancel',
            icon:          'swal-belezou-icon'
        },
        buttonsStyling: false,
        showClass: { popup: 'swal-belezou-show' },
        hideClass: { popup: 'swal-belezou-hide' }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(`form-cancelar-${idAgendamento}`).submit();
        }
    });
}

// ── LÓGICA DO MODAL DE REMARCAÇÃO ──

let remarcarFuncionarioId = null;
let remarcarServicoId = null;

function abrirModalRemarcar(idAgendamento, nomeServico, nomeProfissional, idFuncionario, idServico, status) {
    document.getElementById('remarcar-id-agendamento').value = idAgendamento;
    document.getElementById('remarcar-nome-servico').textContent = nomeServico;
    document.getElementById('remarcar-nome-profissional').textContent = nomeProfissional;
    
    remarcarFuncionarioId = idFuncionario;
    remarcarServicoId = idServico;

    // Reseta campos do form
    document.getElementById('remarcar-data').value = '';
    document.getElementById('remarcar-box-horarios').style.display = 'none';
    document.getElementById('remarcar-hora-selecionada').value = '';
    document.getElementById('btn-remarcar-confirmar').disabled = true;

    // Define data mínima baseada no status: hoje para pendentes, amanhã para marcados (confirmados)
    const dataMinima = new Date();
    if (status === 'marcado') {
        dataMinima.setDate(dataMinima.getDate() + 1);
    }
    const ano = dataMinima.getFullYear();
    const mes = String(dataMinima.getMonth() + 1).padStart(2, '0');
    const dia = String(dataMinima.getDate()).padStart(2, '0');
    document.getElementById('remarcar-data').setAttribute('min', `${ano}-${mes}-${dia}`);

    // Exibe o modal
    document.getElementById('modalRemarcar').classList.add('active');
    document.body.classList.add('modal-open');
}

function fecharModalRemarcar() {
    document.getElementById('modalRemarcar').classList.remove('active');
    document.body.classList.remove('modal-open');
}

async function atualizarHorariosRemarcar() {
    const dataSelecionada = document.getElementById('remarcar-data').value;
    const idAgendamento = document.getElementById('remarcar-id-agendamento').value;
    const boxHorarios = document.getElementById('remarcar-box-horarios');
    const containerHorarios = document.getElementById('remarcar-container-horarios');

    if (!dataSelecionada || !remarcarFuncionarioId || !remarcarServicoId) {
        return;
    }

    boxHorarios.style.display = 'block';
    containerHorarios.innerHTML = '<p style="color: var(--text-muted); grid-column: 1/-1; text-align: center;">Buscando horários disponíveis...</p>';
    document.getElementById('remarcar-hora-selecionada').value = '';
    document.getElementById('btn-remarcar-confirmar').disabled = true;

    try {
        const response = await fetch(window.location.origin + BASE_URL + '/api/horarios-livres', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                id_funcionario: remarcarFuncionarioId,
                data: dataSelecionada,
                id_servico: remarcarServicoId,
                id_agendamento_ignorar: idAgendamento
            })
        });
        
        const data = await response.json();
        containerHorarios.innerHTML = '';

        if (data.sucesso && data.horarios.length > 0) {
            data.horarios.forEach((hora) => {
                const horaFormatada = hora.substring(0, 5);
                const div = document.createElement('div');
                div.className = 'time-slot';
                div.textContent = horaFormatada;
                div.onclick = function () { selecionarHorarioRemarcar(hora, this); };
                containerHorarios.appendChild(div);
            });
        } else {
            containerHorarios.innerHTML = '<p style="color: var(--color-pink); grid-column: 1/-1; text-align: center;">Nenhum horário disponível nesta data.</p>';
        }
    } catch (error) {
        console.error('Erro ao buscar horários para remarcação:', error);
        containerHorarios.innerHTML = '<p style="color: var(--color-pink); grid-column: 1/-1; text-align: center;">Erro ao carregar horários livres.</p>';
    }
}

function selecionarHorarioRemarcar(hora, elemento) {
    document.getElementById('remarcar-hora-selecionada').value = hora;
    
    document.querySelectorAll('#remarcar-container-horarios .time-slot').forEach(slot => {
        slot.classList.remove('selected');
    });
    
    elemento.classList.add('selected');
    document.getElementById('btn-remarcar-confirmar').disabled = false;
}

// Fecha o modal de remarcação ao clicar no fundo escuro (fora da caixa de conteúdo)
document.addEventListener('DOMContentLoaded', () => {
    const modalRemarcar = document.getElementById('modalRemarcar');
    if (modalRemarcar) {
        modalRemarcar.addEventListener('click', (event) => {
            if (event.target === modalRemarcar) {
                fecharModalRemarcar();
            }
        });
    }
});