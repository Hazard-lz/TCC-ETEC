<?php
// Bloqueio de segurança e inicialização limpa
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - Belezou App</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">

    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/index.global.min.js'></script>
    <script src='https://cdn.jsdelivr.net/npm/fullcalendar@6.1.11/locales/pt-br.global.min.js'></script>
    <link href="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/css/tom-select.bootstrap5.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/tom-select@2.2.2/dist/js/tom-select.complete.min.js"></script>
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agenda.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- O sidebar.php já abre <div id="conteudo-temporario"> sem fechar.
         O conteúdo abaixo entra dentro dele automaticamente. -->

        <!-- Os alertas agora são exibidos via SweetAlert no final da página -->

        <div class="header-actions">
            <div>
                <h2 id="titulo-agenda-container" style="display: flex; align-items: center; gap: 0.5rem; flex-wrap: wrap; margin: 0;">
                    <span id="titulo-agenda" style="margin: 0; font-size: 1.4rem; font-weight: 700; color: var(--text-main);">Minha Agenda</span>
                    <span id="alerta-pendentes" style="display: none;">
                        <i class="bi bi-bell-fill"></i> <span id="contador-pendentes">0</span> pendente(s)
                    </span>
                </h2>
                <p style="margin:0; color: var(--text-muted); font-size:0.9rem;">Clique em um evento para ver detalhes e gerenciar</p>
            </div>

            <?php 
            $isGerencia = in_array($_SESSION['usuario_tipo'] ?? '', ['admin', 'subadmin']);
            if ($isGerencia): 
            ?>
                <!-- Seletor de profissionais para a gerência -->
                <div class="agenda-filter-container">
                    <label for="filtro-profissional" class="agenda-filter-label">
                        <i class="bi bi-funnel-fill"></i> Filtrar por Profissional:
                    </label>
                    <select id="filtro-profissional" class="agenda-filter-select">
                        <?php if (!empty($profissionais)): ?>
                            <?php foreach ($profissionais as $p): ?>
                                <option value="<?= $p['id_funcionario'] ?>" <?= $p['id_funcionario'] == $idFuncionarioLogado ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($p['nome']) ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </div>
            <?php endif; ?>

            <div class="action-buttons">
                <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/disponibilidade'" class="btn-secondary btn-desktop">⏱ Gerenciar Horário</button>
                <button data-modal-target="#modalNovoAgendamento" class="btn-primary btn-desktop">+ Novo Agendamento</button>
            </div>
        </div>

        <!-- Legenda de cores -->
        <div class="legenda-status">
            <span class="legenda-item"><span class="legenda-dot" style="background:#8b5cf6;"></span> Marcado</span>
            <span class="legenda-item"><span class="legenda-dot" style="background:#f45b69;"></span> Pendente</span>
            <span class="legenda-item"><span class="legenda-dot" style="background:#2ecc71;"></span> Concluído</span>
        </div>

        <div id="calendario-agendamentos"></div>

        <!-- Botão flutuante para mobile -->
        <button class="fab-novo" data-modal-target="#modalNovoAgendamento" title="Novo Agendamento">+</button>

    <div id="modalNovoAgendamento" class="modal-overlay">

        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Novo Agendamento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAgendamento" action="<?= BASE_URL ?>/funcionario/agenda" method="POST">
                    <?= CsrfGuard::campoHidden() ?>
                    
                    <div class="form-group">
                        <label>Cliente</label>
                        <select id="id_cliente" name="id_cliente" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php if (!empty($clientes)):
                                foreach ($clientes as $cli): ?>
                                    <option value="<?= $cli['id_cliente'] ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                                <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Profissional</label>
                            <select id="id_funcionario" name="id_funcionario" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php if (!empty($profissionais)):
                                    foreach ($profissionais as $prof): ?>
                                        <option value="<?= $prof['id_funcionario'] ?>"><?= htmlspecialchars($prof['nome']) ?>
                                        </option>
                                    <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Serviço</label>
                            <select id="id_servico" name="id_servico" class="form-control" required disabled>
                                <option value="">Selecione um Profissional primeiro</option>
                            </select>
                        </div>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Data</label>
                            <input type="date" id="data" name="data" class="form-control" value="<?= date('Y-m-d') ?>" required>
                        </div>
                        <div class="form-group">
                            <label>Horário</label>
                            <select id="hora" name="hora" class="form-control" required>
                                <option value="">Selecione Profissional/Serviço/Data</option>
                            </select>
                        </div>
                    </div>

                    <button type="submit" class="btn-primary" style="width: 100%; margin-top: 1rem;">Confirmar Agendamento</button>
                </form>
            </div>
        </div>
    </div>

    <div id="modalDetalhes" class="modal-overlay">
        <div class="modal-content" style="max-width: 420px;">
            <div class="modal-header">
                <h3>Detalhes do Agendamento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">

                <!-- Info rows com ícones -->
                <div style="display:flex; flex-direction:column; gap:0.75rem; margin-bottom:1.25rem;">
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">👤</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Cliente</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesCliente"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">✂️</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Serviço</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesServico"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">🧑‍💼</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Profissional</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesProfissional"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">📅</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Data</div>
                            <div style="font-weight:600; color:var(--text-main);" id="detalhesData"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">🕐</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Horário</div>
                            <div style="font-weight:600; color:var(--color-purple);" id="detalhesHorario"></div>
                        </div>
                    </div>
                    <div style="display:flex; align-items:center; gap:0.75rem;">
                        <span style="font-size:1.2rem; width:24px; text-align:center;">📋</span>
                        <div>
                            <div style="font-size:0.75rem; color:var(--text-muted); text-transform:uppercase; letter-spacing:0.5px;">Status</div>
                            <span id="detalhesStatus" style="font-size:0.8rem; font-weight:700; padding:0.2rem 0.7rem; border-radius:20px;"></span>
                        </div>
                    </div>
                </div>

                <hr style="border:none; border-top:1px solid var(--border-color); margin-bottom:1.25rem;">

                <div id="areaAcoes" style="display: flex; gap: 0.5rem; flex-direction: column;">
                    <div id="boxAcoesPendente" style="display: none; gap: 0.5rem; flex-direction: column;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="marcado">
                            <button type="submit" class="btn-primary" style="background:#10b981; width:100%; box-shadow:0 4px 12px rgba(16,185,129,0.3);">✔ Confirmar Agendamento</button>
                        </form>
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="cancelado">
                            <button type="button" class="btn-secondary" style="width:100%; color:#ef4444; border-color:#ef4444;" onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja realmente recusar este agendamento?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">✕ Recusar</button>
                        </form>
                    </div>

                    <div id="boxAcoesMarcado" style="display: none; gap: 0.5rem; flex-direction: column;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="concluido">
                            <button type="submit" class="btn-primary" style="background:#3b82f6; width:100%; box-shadow:0 4px 12px rgba(59,130,246,0.3);">✔ Concluir Atendimento</button>
                        </form>
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <?= CsrfGuard::campoHidden() ?>
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="cancelado">
                            <button type="button" class="btn-secondary" style="width:100%; color:#ef4444; border-color:#ef4444;" onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja realmente cancelar este agendamento?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">✕ Cancelar Agendamento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Novo modal de lista de agendamentos pendentes -->
    <div id="modalListaPendentes" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3>Agendamentos Pendentes</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <div class="lista-pendentes-body" id="lista-pendentes-container">
                    <!-- Gerado dinamicamente via JS -->
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var calendarEl = document.getElementById('calendario-agendamentos');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: window.innerWidth < 768 ? 'listWeek' : 'timeGridWeek',
                slotMinTime: '<?= $slotMinTime ?? "06:00:00" ?>',
                slotMaxTime: '<?= $slotMaxTime ?? "23:59:00" ?>',
                slotDuration: '00:15:00', // Grade de 15 em 15 min
                allDaySlot: false,
                slotEventOverlap: false, // Evita sobreposição visual de agendamentos próximos (lado a lado puro)
                expandRows: true, // Faz as linhas preencherem o espaço disponível
                handleWindowResize: true,
                height: window.innerWidth < 992 ? 'parent' : 'auto',

                eventMinHeight: 84, // Força altura mínima para os blocos serem legíveis (cabe cliente, serviço e funcionário)

                headerToolbar: {
                    left: 'prev,next today',
                    center: 'title',
                    right: 'timeGridWeek,timeGridDay,listWeek'
                },

                buttonText: {
                    today: 'Hoje',
                    month: 'Mês',
                    week: 'Semana',
                    day: 'Dia',
                    list: 'Lista'
                },

                windowResize: function (view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('listWeek');
                    } else {
                        calendar.changeView('timeGridWeek');
                    }
                },

                events: {
                    url: '<?= BASE_URL ?>/api/agenda-eventos',
                    extraParams: function () {
                        var filtro = document.getElementById('filtro-profissional');
                        return {
                            funcionario_id: filtro ? filtro.value : ''
                        };
                    }
                },

                eventContent: function (arg) {
                    const props = arg.event.extendedProps;
                    const isList = arg.view.type.includes('list');

                    const status = props.status;
                    let statusLabel = status;
                    let statusClass = 'status-badge-marcado';

                    if (status === 'pendente') {
                        statusLabel = 'Pendente';
                        statusClass = 'status-badge-pendente';
                    } else if (status === 'marcado') {
                        statusLabel = 'Marcado';
                        statusClass = 'status-badge-marcado';
                    } else if (status === 'concluido') {
                        statusLabel = 'Concluído';
                        statusClass = 'status-badge-concluido';
                    } else if (status === 'cancelado') {
                        statusLabel = 'Cancelado';
                        statusClass = 'status-badge-cancelado';
                    }

                    if (isList) {
                        return {
                            html: `
                                <div class="fc-list-event-custom">
                                    <div class="fc-list-event-main-info">
                                        <span class="fc-list-event-client-name">${props.cliente || ''}</span>
                                        <span class="fc-list-event-service-name">
                                            <i class="bi bi-scissors"></i> ${props.servico || ''}
                                        </span>
                                    </div>
                                    <div class="fc-list-event-meta">
                                        <span class="fc-list-event-prof">
                                            <i class="bi bi-person-badge"></i> ${props.profissional || ''}
                                        </span>
                                        <span class="status-badge ${statusClass}">${statusLabel}</span>
                                    </div>
                                </div>
                            `
                        };
                    } else {
                        return {
                            html: `
                                <div class="fc-grid-event-custom">
                                    <div class="fc-event-client">${props.cliente || ''}</div>
                                    <span class="status-badge grid-badge ${statusClass}">${statusLabel}</span>
                                    <div class="fc-event-service">
                                        <i class="bi bi-scissors"></i>
                                        <span>${props.servico || ''}</span>
                                    </div>
                                    <div class="fc-event-prof">
                                        <i class="bi bi-person-fill"></i>
                                        <span>${props.profissional || ''}</span>
                                    </div>
                                </div>
                            `
                        };
                    }
                },

                eventClick: function (info) {
                    const props = info.event.extendedProps;
                    document.getElementById('detalhesCliente').textContent = props.cliente;
                    document.getElementById('detalhesServico').textContent = props.servico;
                    document.getElementById('detalhesProfissional').textContent = props.profissional;
                    document.getElementById('detalhesData').textContent = info.event.start.toLocaleDateString('pt-BR');
                    document.getElementById('detalhesHorario').textContent = info.event.start.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });

                    // Badge de status colorido
                    const statusEl = document.getElementById('detalhesStatus');
                    const statusMap = {
                        pendente:  { label: 'Pendente',  bg: 'rgba(244,91,105,0.15)', color: '#f45b69' },
                        marcado:   { label: 'Marcado',   bg: 'rgba(139,92,246,0.15)', color: '#8b5cf6' },
                        concluido: { label: 'Concluído', bg: 'rgba(46,204,113,0.15)', color: '#2ecc71' },
                        cancelado: { label: 'Cancelado', bg: 'rgba(239,68,68,0.15)',  color: '#ef4444' }
                    };
                    const st = statusMap[props.status] || { label: props.status, bg: 'transparent', color: 'inherit' };
                    statusEl.textContent = st.label;
                    statusEl.style.background = st.bg;
                    statusEl.style.color = st.color;

                    document.querySelectorAll('.inputIdAgendamento').forEach(input => input.value = info.event.id);

                    document.getElementById('boxAcoesPendente').style.display = props.status === 'pendente' ? 'flex' : 'none';
                    document.getElementById('boxAcoesMarcado').style.display  = props.status === 'marcado'  ? 'flex' : 'none';

                    openModal('#modalDetalhes');
                }
            });

            calendar.render();

            // Expõe o calendário globalmente para o updateSize funcionar
            window.belezouCalendar = calendar;
            
            // ─── Refresh Dinâmico (Polling) ───
            // Atualiza os eventos do calendário e os alertas pendentes a cada 30 segundos
            setInterval(() => {
                if (!document.hidden) {
                    calendar.refetchEvents();
                    atualizarAlertasPendentes();
                }
            }, 30000);

            // ─── Exibição de Alertas (Flash Messages) via SweetAlert ───
            <?php if (isset($_SESSION['flash_sucesso'])): ?>
                Swal.fire({
                    title: 'Sucesso!',
                    text: '<?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>',
                    icon: 'success',
                    customClass: {
                        popup: 'swal-belezou-popup',
                        title: 'swal-belezou-title',
                        htmlContainer: 'swal-belezou-text',
                        confirmButton: 'swal-belezou-btn-confirm'
                    },
                    buttonsStyling: false
                });
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_erro'])): ?>
                Swal.fire({
                    title: 'Ops!',
                    text: '<?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>',
                    icon: 'error',
                    customClass: {
                        popup: 'swal-belezou-popup',
                        title: 'swal-belezou-title',
                        htmlContainer: 'swal-belezou-text',
                        confirmButton: 'swal-belezou-btn-danger'
                    },
                    buttonsStyling: false
                });
            <?php endif; ?>

            // Recalcula o tamanho do calendário quando a sidebar abre/fecha
            // O CSS transition dura 300ms (veja admin-layout.css), esperamos um pouco mais
            document.addEventListener('click', function(e) {
                const menuToggle = document.getElementById('menuToggle');
                if (menuToggle && menuToggle.contains(e.target)) {
                    setTimeout(function() {
                        calendar.updateSize();
                    }, 350);
                }
            });

            // Inicializar Tom Select para Clientes
            new TomSelect("#id_cliente", {
                create: false,
                sortField: {
                    field: "text",
                    direction: "asc"
                },
                placeholder: "Pesquisar cliente..."
            });

            // Cascata de Serviços via AJAX
            document.getElementById('id_funcionario').addEventListener('change', function () {
                const idFuncionario = this.value;
                const selectServico = document.getElementById('id_servico');
                const selectHora = document.getElementById('hora');

                // Limpar horário e serviço
                selectHora.innerHTML = '<option value="">Selecione Profissional/Serviço/Data</option>';
                selectServico.innerHTML = '<option value="">Carregando...</option>';
                selectServico.disabled = true;

                if (!idFuncionario) {
                    selectServico.innerHTML = '<option value="">Selecione um Profissional primeiro</option>';
                    return;
                }

                // Buscar serviços do profissional
                fetch("<?= BASE_URL ?>/api/servicos-profissional", {
                    method: "POST",
                    headers: { "Content-Type": "application/json" },
                    body: JSON.stringify({ id_funcionario: idFuncionario })
                })
                .then(res => res.json())
                .then(res => {
                    selectServico.innerHTML = '<option value="">Selecione o Serviço...</option>';
                    if (res.sucesso && res.servicos && res.servicos.length > 0) {
                        selectServico.disabled = false;
                        res.servicos.forEach(s => {
                            const opt = document.createElement('option');
                            opt.value = s.id_servico;
                            opt.textContent = s.nome_servico;
                            selectServico.appendChild(opt);
                        });
                    } else {
                        selectServico.innerHTML = '<option value="">Nenhum serviço encontrado</option>';
                    }
                })
                .catch(err => {
                    console.error("Erro ao carregar serviços:", err);
                    selectServico.innerHTML = '<option value="">Erro ao carregar</option>';
                });
            });

            // API de Horários Livres
            const inputsDispo = ['id_funcionario', 'id_servico', 'data'];
            inputsDispo.forEach(id => {
                document.getElementById(id).addEventListener('change', function () {
                    const func = document.getElementById('id_funcionario').value;
                    const serv = document.getElementById('id_servico').value;
                    const data = document.getElementById('data').value;
                    const selectHora = document.getElementById('hora');

                    if (func && serv && data) {
                        selectHora.innerHTML = '<option>Carregando...</option>';
                        fetch("<?= BASE_URL ?>/api/horarios-livres", {
                            method: "POST",
                            headers: { "Content-Type": "application/json" },
                            body: JSON.stringify({ id_funcionario: func, id_servico: serv, data: data })
                        })
                            .then(res => res.json())
                            .then(res => {
                                selectHora.innerHTML = '<option value="">Selecione...</option>';
                                if (res.sucesso) {
                                    res.horarios.forEach(h => {
                                        const opt = document.createElement('option');
                                        opt.value = h;
                                        opt.textContent = h.substring(0, 5);
                                        selectHora.appendChild(opt);
                                    });
                                }
                            })
                            .catch(err => {
                                console.error("Erro ao carregar horários:", err);
                                selectHora.innerHTML = '<option value="">Erro ao carregar</option>';
                            });
                    } else {
                        selectHora.innerHTML = '<option value="">Selecione Profissional/Serviço/Data</option>';
                    }
                });
            });

            // --- Integração do Filtro de Profissionais (Gerência) ---
            var filtroProf = document.getElementById('filtro-profissional');
            
            function atualizarTituloAgenda() {
                var titulo = document.getElementById('titulo-agenda');
                if (filtroProf && titulo) {
                    var nomeProf = filtroProf.options[filtroProf.selectedIndex].text;
                    var idLogado = '<?= $idFuncionarioLogado ?>';
                    if (filtroProf.value == idLogado) {
                        titulo.textContent = 'Minha Agenda';
                    } else {
                        titulo.textContent = 'Agenda de ' + nomeProf;
                    }
                }
            }

            // Inicializa o título correto
            atualizarTituloAgenda();

            if (filtroProf) {
                filtroProf.addEventListener('change', function () {
                    atualizarTituloAgenda();
                    calendar.refetchEvents();
                    atualizarAlertasPendentes();
                });
            }

            // ─── Sistema de Alerta de Agendamentos Pendentes ───
            var cachePendentes = [];

            function atualizarAlertasPendentes() {
                var params = new URLSearchParams();
                if (filtroProf) {
                    params.set('funcionario_id', filtroProf.value);
                }

                fetch('<?= BASE_URL ?>/api/agendamentos-pendentes?' + params.toString())
                    .then(function(res) { return res.json(); })
                    .then(function(data) {
                        cachePendentes = data || [];
                        var alerta = document.getElementById('alerta-pendentes');
                        var contador = document.getElementById('contador-pendentes');

                        if (cachePendentes.length > 0) {
                            alerta.style.display = 'inline-flex';
                            contador.textContent = cachePendentes.length;
                        } else {
                            alerta.style.display = 'none';
                        }
                    })
                    .catch(function(err) {
                        console.error('Erro ao buscar pendentes:', err);
                    });
            }

            // Clique no sino abre a lista de pendentes
            document.getElementById('alerta-pendentes').addEventListener('click', function() {
                var container = document.getElementById('lista-pendentes-container');

                if (cachePendentes.length === 0) {
                    container.innerHTML = '<p style="text-align:center; color:var(--text-muted); padding:1rem;">Nenhum agendamento pendente encontrado.</p>';
                } else {
                    var html = '';
                    cachePendentes.forEach(function(p) {
                        html += '<div class="pendente-item-card" onclick="verDetalhesPendente(' + p.id_agendamento + ')">';
                        html += '  <div class="pendente-item-info">';
                        html += '    <div class="pendente-item-cliente"><i class="bi bi-person-fill"></i> ' + (p.cliente_nome || '') + '</div>';
                        html += '    <div class="pendente-item-servico"><i class="bi bi-scissors"></i> ' + (p.nome_servico || '') + '</div>';
                        html += '  </div>';
                        html += '  <div class="pendente-item-meta">';
                        html += '    <span class="pendente-item-data"><i class="bi bi-calendar3"></i> ' + (p.data_formatada || '') + '</span>';
                        html += '    <span class="pendente-item-hora"><i class="bi bi-clock"></i> ' + (p.hora_inicio_formatada || '') + '</span>';
                        html += '  </div>';
                        html += '</div>';
                    });
                    container.innerHTML = html;
                }

                openModal('#modalListaPendentes');
            });

            // Abrir detalhes de um agendamento pendente a partir da lista
            window.verDetalhesPendente = function(idAgendamento) {
                var item = cachePendentes.find(function(p) { return p.id_agendamento == idAgendamento; });
                if (!item) return;

                // Fecha o modal da lista primeiro
                closeModal('#modalListaPendentes');

                // Abre o modal de detalhes com pequeno delay para transição suave
                setTimeout(function() {
                    document.getElementById('detalhesCliente').textContent = item.cliente_nome || '';
                    document.getElementById('detalhesServico').textContent = item.nome_servico || '';
                    document.getElementById('detalhesProfissional').textContent = item.profissional_nome || '';
                    document.getElementById('detalhesData').textContent = item.data_formatada || '';
                    document.getElementById('detalhesHorario').textContent = (item.hora_inicio_formatada || '') + ' - ' + (item.hora_fim_formatada || '');

                    var statusEl = document.getElementById('detalhesStatus');
                    statusEl.textContent = 'Pendente';
                    statusEl.style.background = 'rgba(244,91,105,0.15)';
                    statusEl.style.color = '#f45b69';

                    document.querySelectorAll('.inputIdAgendamento').forEach(function(input) { input.value = idAgendamento; });

                    document.getElementById('boxAcoesPendente').style.display = 'flex';
                    document.getElementById('boxAcoesMarcado').style.display = 'none';

                    openModal('#modalDetalhes');
                }, 200);
            };

            // Inicializa o alerta ao carregar a página
            atualizarAlertasPendentes();
        });
    </script>
</body>

</html>
