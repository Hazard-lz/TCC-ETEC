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
    <style>


        /* --- AJUSTES DE DESIGN DO CALENDÁRIO --- */
        .fc {
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: 0 4px 20px rgba(0, 0, 0, 0.06);
            border: 1px solid var(--border-color) !important;
            width: 100% !important;
            min-width: 0 !important;
            box-sizing: border-box;
        }

        /* Garante que o container pai do calendário não transborda */
        #calendario-agendamentos {
            width: 100%;
            min-width: 0;
            overflow: hidden;
        }

        /* Corrigir o esmagamento do texto nos blocos de evento */
        .fc-v-event .fc-event-main {
            white-space: normal !important;
            overflow: hidden;
            display: flex;
            flex-direction: column;
            justify-content: flex-start;
            padding: 4px !important;
            font-size: 0.85rem;
            line-height: 1.2;
        }

        /* Esconder a hora dentro do bloco colorido para sobrar espaço para o nome */
        .fc-v-event .fc-event-time {
            display: none !important;
        }

        /* Personalização dos eventos por status */
        .evento-marcado {
            background: linear-gradient(135deg, #a78bfa, var(--color-purple)) !important;
            border: none !important;
        }

        .evento-concluido {
            background: linear-gradient(135deg, #2ecc71, #27ae60) !important;
            border: none !important;
        }

        .evento-pendente {
            background: linear-gradient(135deg, #f45b69, #e74c3c) !important;
            border: none !important;
        }

        .fc-event:hover {
            transform: scale(1.01);
            z-index: 10 !important;
            cursor: pointer;
        }

        /* --- ESTILOS DA AGENDA EM LISTA (listWeek) --- */
        .fc-theme-standard .fc-list {
            border: 1px solid var(--border-color) !important;
            border-radius: var(--radius-lg) !important;
            overflow: hidden;
            background: var(--surface-color);
        }

        /* Cabeçalho do dia */
        .fc-list-day-cushion {
            background-color: var(--bg-color) !important;
            padding: 10px 16px !important;
        }

        /* Borda do separador de dia — aplica na linha th */
        .fc-list-day th {
            border-color: var(--border-color) !important;
        }

        .fc-list-day-text, .fc-list-day-side-text {
            color: var(--text-main) !important;
            font-weight: 700 !important;
            font-size: 0.8rem;
            letter-spacing: 0.5px;
        }

        /* Remove o fundo colorido e aplica borda em TODOS os tds (inclusive o do dot) */
        .fc-list-event td {
            background: var(--surface-color) !important;
            border-bottom: 1px solid var(--border-color) !important;
            border-top: none !important;
        }

        /* A célula do dot não precisa de border-bottom (evita a linha branca) */
        .fc-list-event-graphic {
            border-bottom: none !important;
        }

        /* Força o background da <tr> como transparente para não vazar a cor do evento */
        .fc-list-event {
            background-color: transparent !important;
        }

        /* Hover: tint roxo visível no desktop */
        .fc-list-event:hover td {
            background: rgba(139, 92, 246, 0.18) !important;
            cursor: pointer;
        }

        .fc-list-event-time {
            color: var(--text-muted) !important;
            font-size: 0.85rem !important;
            padding: 14px 10px 14px 0 !important;
            white-space: nowrap;
            background: transparent !important;
        }

        .fc-list-event-title {
            color: var(--text-main) !important;
            font-weight: 500;
            font-size: 0.95rem;
            padding: 14px 16px !important;
            background: transparent !important;
        }

        /* Borda lateral colorida no lugar do fundo colorido */
        .evento-marcado td:first-child   { border-left: 4px solid var(--color-purple) !important; }
        .evento-concluido td:first-child { border-left: 4px solid #2ecc71 !important; }
        .evento-pendente td:first-child  { border-left: 4px solid #f45b69 !important; }

        /* Dot colorido */
        .fc-list-event-dot { border-width: 6px !important; }
        .evento-marcado .fc-list-event-dot   { border-color: var(--color-purple) !important; background: var(--color-purple) !important; }
        .evento-concluido .fc-list-event-dot { border-color: #2ecc71 !important; background: #2ecc71 !important; }
        .evento-pendente .fc-list-event-dot  { border-color: #f45b69 !important; background: #f45b69 !important; }

        /* Horário colorido com o status */
        .evento-marcado .fc-list-event-time   { color: var(--color-purple) !important; font-weight: 600 !important; }
        .evento-concluido .fc-list-event-time { color: #2ecc71 !important; font-weight: 600 !important; }
        .evento-pendente .fc-list-event-time  { color: #f45b69 !important; font-weight: 600 !important; }

        .fc-list-empty {
            background-color: var(--surface-color) !important;
            color: var(--text-muted) !important;
            padding: 4rem !important;
            text-align: center;
        }

        .fc-list-table { border-collapse: collapse !important; }
        .fc-list-table tr th,
        .fc-list-table tr td { border-color: var(--border-color) !important; }

        /* Ajuste do botão de hoje e setas */
        .fc-button-primary {
            background: var(--surface-color) !important;
            border-color: var(--border-color) !important;
            color: var(--text-main) !important;
            text-transform: capitalize !important;
            font-weight: 600 !important;
            box-shadow: none !important;
        }

        .fc-button-primary:hover {
            background: var(--bg-color) !important;
            border-color: var(--color-purple) !important;
        }

        .fc-button-active {
            background: var(--color-purple) !important;
            border-color: var(--color-purple) !important;
            color: white !important;
        }

        /* --- PÁGINA HEADER --- */
        .header-actions {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
            gap: 0.8rem;
        }

        .header-actions h2 {
            color: var(--text-main);
            margin: 0;
            font-size: 1.4rem;
        }

        .action-buttons {
            display: flex;
            gap: 0.8rem;
            flex-shrink: 0;
        }

        /* Botão flutuante no mobile */
        .fab-novo {
            display: none;
            position: fixed;
            bottom: 1.5rem;
            right: 1.5rem;
            width: 56px;
            height: 56px;
            border-radius: 50%;
            background: var(--gradient-brand);
            color: #fff;
            font-size: 1.8rem;
            font-weight: 300;
            line-height: 56px;
            text-align: center;
            border: none;
            box-shadow: 0 6px 20px rgba(139, 92, 246, 0.5);
            z-index: 900;
            cursor: pointer;
            padding: 0;
            transition: transform 0.2s, box-shadow 0.2s;
        }

        .fab-novo:hover {
            transform: scale(1.1);
            box-shadow: 0 8px 25px rgba(139, 92, 246, 0.6);
        }

        /* --- LEGENDA DE CORES --- */
        .legenda-status {
            display: flex;
            gap: 1.2rem;
            flex-wrap: wrap;
            margin-bottom: 1rem;
            font-size: 0.82rem;
            color: var(--text-muted);
        }

        .legenda-item {
            display: flex;
            align-items: center;
            gap: 0.4rem;
        }

        .legenda-dot {
            width: 10px;
            height: 10px;
            border-radius: 50%;
            flex-shrink: 0;
        }

        /* --- RESPONSIVIDADE MOBILE --- */
        @media (max-width: 992px) {
            /* Toolbar: tudo na mesma linha, que quebra quando precisa */
            .fc .fc-toolbar {
                flex-direction: row;
                flex-wrap: wrap;
                gap: 0.4rem;
                justify-content: center;
                align-items: center;
            }

            .fc .fc-toolbar-chunk {
                display: flex;
                align-items: center;
            }

            .fc-toolbar-title {
                font-size: 0.9rem !important;
                width: 100%;
                text-align: center;
                order: -1;
            }

            /* Esconder vista "Semana" no mobile */
            .fc-timeGridWeek-button {
                display: none !important;
            }


            /* Header da página empilha */
            .header-actions {
                flex-direction: column;
                align-items: flex-start;
            }

            /* Botoes de ação do header ficam ocultos — usa FAB */
            .action-buttons .btn-desktop {
                display: none;
            }

            /* FAB visível */
            .fab-novo {
                display: block;
            }

            /* Calendário ocupa toda a largura da tela no mobile */
            .fc {
                border-radius: 0 !important;
                border-left: none !important;
                border-right: none !important;
                padding: 0.5rem 0 !important;
                /* Cancela o padding horizontal de 1rem do content-area no mobile */
                width: calc(100% + 2rem) !important;
                margin-left: -1rem !important;
                margin-right: -1rem !important;
                box-sizing: border-box;
            }

            /* Padding interno da lista para compensar */
            .fc-list-event-graphic { padding-left: 8px !important; }
            .fc-list-event-title   { padding: 12px 12px !important; }
            .fc-list-event-time    { padding: 12px 8px 12px 0 !important; }
            .fc-list-day-cushion   { padding: 8px 12px !important; }
        }
    </style>
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- O sidebar.php já abre <div id="conteudo-temporario"> sem fechar.
         O conteúdo abaixo entra dentro dele automaticamente. -->

        <?php if (isset($_SESSION['flash_erro'])): ?>
            <div class="alert alert-danger"><?= $_SESSION['flash_erro'] ?></div>
            <?php unset($_SESSION['flash_erro']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_sucesso'])): ?>
            <div class="alert alert-success"><?= $_SESSION['flash_sucesso'] ?></div>
            <?php unset($_SESSION['flash_sucesso']); ?>
        <?php endif; ?>

        <div class="header-actions">
            <div>
                <h2>Minha Agenda</h2>
                <p style="margin:0; color: var(--text-muted); font-size:0.9rem;">Clique em um evento para ver detalhes e gerenciar</p>
            </div>
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
                            <button type="submit" class="btn-secondary" style="width:100%; color:#ef4444; border-color:#ef4444;">✕ Recusar</button>
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
                slotDuration: '00:15:00', // Grelha de 15 em 15 min
                allDaySlot: false,
                expandRows: true, // Faz as linhas preencherem o espaço disponível
                handleWindowResize: true,

                // --- SOLUÇÃO PARA O ESMAGAMENTO ---
                eventMinHeight: 60, // Força altura mínima para os blocos serem legíveis

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

                events: '<?= BASE_URL ?>/api/agenda-eventos',

                eventClick: function (info) {
                    const props = info.event.extendedProps;
                    document.getElementById('detalhesCliente').textContent = props.cliente;
                    document.getElementById('detalhesServico').textContent = props.servico;
                    document.getElementById('detalhesProfissional').textContent = props.profissional;
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

                    document.getElementById('modalDetalhes').classList.add('active');
                }
            });

            calendar.render();

            // Expõe o calendário globalmente para o updateSize funcionar
            window.belezouCalendar = calendar;

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
        });
    </script>
</body>

</html>