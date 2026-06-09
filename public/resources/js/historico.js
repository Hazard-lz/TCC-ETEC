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

let fpRemarcarInstance = null;

function abrirModalRemarcar(idAgendamento, nomeServico, nomeProfissional, idFuncionario, idServico, status) {
    document.getElementById('remarcar-id-agendamento').value = idAgendamento;
    document.getElementById('remarcar-nome-servico').textContent = nomeServico;
    document.getElementById('remarcar-nome-profissional').textContent = nomeProfissional;
    
    remarcarFuncionarioId = idFuncionario;
    remarcarServicoId = idServico;

    // Reseta campos do form
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
    const minDataStr = `${ano}-${mes}-${dia}`;
    
    let maxDataStr = null;
    if (typeof LIMITE_FUTURO_DIAS !== 'undefined' && LIMITE_FUTURO_DIAS !== 'sem_limite') {
        const limiteDias = parseInt(LIMITE_FUTURO_DIAS, 10);
        if (!isNaN(limiteDias)) {
            const dataMax = new Date();
            dataMax.setDate(dataMax.getDate() + limiteDias);
            const maxAno = dataMax.getFullYear();
            const maxMes = String(dataMax.getMonth() + 1).padStart(2, '0');
            const maxDia = String(dataMax.getDate()).padStart(2, '0');
            maxDataStr = `${maxAno}-${maxMes}-${maxDia}`;
        }
    }

    if (!fpRemarcarInstance) {
        fpRemarcarInstance = flatpickr("#remarcar-data", {
            locale: "pt",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            altInputClass: "form-control flatpickr-alt-input",
            minDate: minDataStr,
            maxDate: maxDataStr || undefined,
            disableMobile: true,
            onChange: function(selectedDates, dateStr, instance) {
                atualizarHorariosRemarcar();
            }
        });
    } else {
        fpRemarcarInstance.clear();
        fpRemarcarInstance.set('minDate', minDataStr);
        fpRemarcarInstance.set('maxDate', maxDataStr || undefined);
    }

    // Exibe o modal
    document.getElementById('modalRemarcar').classList.add('active');
    document.body.classList.add('modal-open');
}

function fecharModalRemarcar() {
    document.getElementById('modalRemarcar').classList.remove('active');
    document.body.classList.remove('modal-open');
    if (fpRemarcarInstance) {
        fpRemarcarInstance.clear();
    }
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

    // Validação de limite futuro no frontend: bloqueia datas além do limite configurado
    if (typeof LIMITE_FUTURO_DIAS !== 'undefined' && LIMITE_FUTURO_DIAS !== 'sem_limite') {
        const limiteDias = parseInt(LIMITE_FUTURO_DIAS, 10);
        if (!isNaN(limiteDias)) {
            const hoje = new Date();
            hoje.setHours(0, 0, 0, 0);
            const dataSel = new Date(dataSelecionada + 'T00:00:00');
            const dataMax = new Date();
            dataMax.setDate(dataMax.getDate() + limiteDias);
            dataMax.setHours(0, 0, 0, 0);
            if (dataSel > dataMax) {
                containerHorarios.innerHTML = '<p style="color: var(--color-pink); grid-column: 1/-1; text-align: center;">A data selecionada excede o limite permitido para agendamentos futuros.</p>';
                return;
            }
        }
    }

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
            const msg = data.mensagem || 'Nenhum horário disponível nesta data.';
            containerHorarios.innerHTML = `<p style="color: var(--color-pink); grid-column: 1/-1; text-align: center;">${msg}</p>`;
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

    // Inicializa Flatpickr para os filtros de data
    if (document.getElementById('data_inicio')) {
        flatpickr("#data_inicio", {
            locale: "pt",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            altInputClass: "form-control flatpickr-alt-input",
            disableMobile: true
        });
    }
    if (document.getElementById('data_fim')) {
        flatpickr("#data_fim", {
            locale: "pt",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            altInputClass: "form-control flatpickr-alt-input",
            disableMobile: true
        });
    }
});

// ── ATUALIZAÇÃO VIA AJAX (POLLING SEM RELOAD) ──

async function atualizarHistoricoPorAjax() {
    const dataInicio = document.getElementById('data_inicio')?.value || '';
    const dataFim = document.getElementById('data_fim')?.value || '';
    
    // Constrói a URL passando o parâmetro ajax=1 e os filtros de data atuais
    const url = `${BASE_URL}/historico?ajax=1&data_inicio=${encodeURIComponent(dataInicio)}&data_fim=${encodeURIComponent(dataFim)}`;
    
    try {
        const response = await fetch(url);
        const data = await response.json();
        
        if (data.sucesso) {
            renderProximos(data.proximos, data.antecedenciaHoras);
            renderAnteriores(data.anteriores);
        }
    } catch (error) {
        console.error('Erro ao atualizar histórico via AJAX:', error);
    }
}

function renderProximos(proximos, antecedenciaHoras) {
    const container = document.getElementById('proximos-cards-list');
    if (!container) return;
    
    if (proximos.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-calendar2-x"></i>
                <p>Não tem agendamentos futuros.</p>
            </div>
        `;
        return;
    }
    
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    let html = '';
    proximos.forEach(ag => {
        const estilo = getBadgeCssJs(ag.status);
        
        // Verifica se o cliente tem permissão para cancelar/remarcar
        let podeCancelar = false;
        if (ag.status === 'pendente') {
            podeCancelar = true;
        } else if (ag.status === 'marcado') {
            // Conversão de data estrita para cálculo de antecedência de horas
            const dataHoraAgendamento = new Date(ag.data_agendamento + 'T' + ag.hora_inicio);
            const agora = new Date();
            const diferencaMs = dataHoraAgendamento - agora;
            const horasDiferenca = diferencaMs / (1000 * 60 * 60);
            if (horasDiferenca >= antecedenciaHoras) {
                podeCancelar = true;
            }
        }
        
        let botoesHtml = '';
        if (podeCancelar) {
            // Escapar strings de nomes para injeção inline segura no atributo onclick do HTML
            const svcNomeEscaped = escapeHtml(ag.nome_servico).replace(/'/g, "\\'");
            const funcNomeEscaped = escapeHtml(ag.funcionario_nome).replace(/'/g, "\\'");
            
            botoesHtml = `
                <div style="margin-top: 15px; border-top: 1px solid var(--border-color); padding-top: 10px; display: flex; gap: 10px;">
                    <form id="form-cancelar-${ag.id_agendamento}" action="${BASE_URL}/historico/cancelar" method="POST" style="flex: 1;">
                        <input type="hidden" name="csrf_token" value="${csrfToken}">
                        <input type="hidden" name="id_agendamento" value="${ag.id_agendamento}">
                        <button type="button"
                            onclick="cancelarAgendamento(${ag.id_agendamento})"
                            style="width: 100%; padding: 10px; border-radius: 8px; background-color: #ef4444; color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Cancelar</button>
                    </form>
                    <button type="button"
                        onclick="abrirModalRemarcar(${ag.id_agendamento}, '${svcNomeEscaped}', '${funcNomeEscaped}', ${ag.cod_funcionario}, ${ag.id_servico}, '${ag.status}')"
                        style="flex: 1; padding: 10px; border-radius: 8px; background-color: var(--color-purple); color: white; border: none; cursor: pointer; font-weight: 600; font-size: 0.85rem;">Remarcar</button>
                </div>
            `;
        }
        
        html += `
            <div class="history-card ${estilo.card}">
                <div class="history-header">
                    <span class="history-date">📅 ${ag.data_formatada} às ${ag.hora_formatada}</span>
                    <span class="history-badge ${estilo.badge}">${estilo.label}</span>
                </div>
                <div class="history-body">
                    <div>
                        <div class="history-service">${escapeHtml(ag.nome_servico)}</div>
                        <div class="history-pro">com ${escapeHtml(ag.funcionario_nome)}</div>
                    </div>
                    <div class="history-price">R$ ${ag.preco_formatado}</div>
                </div>
                ${botoesHtml}
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function renderAnteriores(anteriores) {
    const container = document.getElementById('anteriores-cards-list');
    const totalSpan = document.getElementById('total-anteriores-count');
    if (totalSpan) {
        totalSpan.textContent = `${anteriores.length} agendamentos`;
    }
    
    if (!container) return;
    
    if (anteriores.length === 0) {
        container.innerHTML = `
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <p>Ainda não tens histórico de visitas.</p>
            </div>
        `;
        return;
    }
    
    let html = '';
    anteriores.forEach(ag => {
        const estilo = getBadgeCssJs(ag.status);
        html += `
            <div class="history-card ${estilo.card}">
                <div class="history-header">
                    <span class="history-date">📅 ${ag.data_formatada} às ${ag.hora_formatada}</span>
                    <span class="history-badge ${estilo.badge}">${estilo.label}</span>
                </div>
                <div class="history-body">
                    <div>
                        <div class="history-service">${escapeHtml(ag.nome_servico)}</div>
                        <div class="history-pro">com ${escapeHtml(ag.funcionario_nome)}</div>
                    </div>
                    <div class="history-price">R$ ${ag.preco_formatado}</div>
                </div>
            </div>
        `;
    });
    
    container.innerHTML = html;
}

function getBadgeCssJs(status) {
    const map = {
        'pendente': { card: 'status-pendente', badge: 'badge-orange', label: 'Pendente' },
        'marcado': { card: 'status-marcado', badge: 'badge-purple', label: 'Marcado' },
        'concluido': { card: 'status-concluido', badge: 'badge-green', label: 'Concluído' },
        'cancelado': { card: 'status-cancelado', badge: 'badge-pink', label: 'Cancelado' }
    };
    return map[status] || { card: '', badge: '', label: status.charAt(0).toUpperCase() + status.slice(1) };
}

function escapeHtml(string) {
    return String(string)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}