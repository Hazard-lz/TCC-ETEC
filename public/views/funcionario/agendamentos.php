<?php
// Bloqueio de segurança e inicialização limpa
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
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
    <style>
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 500; font-size: 0.9rem; }
        .alert-error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background-color: #d1fae5; color: #047857; border: 1px solid #34d399; }
        
        /* --- AJUSTES DE DESIGN DO CALENDÁRIO --- */
        .fc {
            background: var(--surface-color);
            border-radius: var(--radius-lg);
            padding: 1rem;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.08);
            max-width: 100%;
        }

        /* Corrigir o esmagamento do texto nos blocos de evento */
        .fc-v-event .fc-event-main {
            white-space: normal !important; /* Permite que o texto quebre linha */
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
        .evento-marcado { background: linear-gradient(135deg, #a78bfa, var(--color-purple)) !important; border: none !important; }
        .evento-concluido { background: linear-gradient(135deg, #2ecc71, #27ae60) !important; border: none !important; }
        .evento-pendente { background: linear-gradient(135deg, #f45b69, #e74c3c) !important; border: none !important; }

        .fc-event:hover { transform: scale(1.01); z-index: 10 !important; cursor: pointer; }

        /* --- RESPONSIVIDADE MOBILE --- */
        @media (max-width: 768px) {
            .fc .fc-toolbar {
                flex-direction: column;
                gap: 1rem;
            }
            .fc-toolbar-title { font-size: 1.1rem !important; }
            .header-actions { flex-direction: column; align-items: stretch !important; gap: 1rem; }
            .action-buttons { flex-direction: column; }
            .action-buttons button { width: 100%; }
            
            /* Esconder a coluna de "Semana" no mobile para não esmagar a grade */
            .fc-timeGridWeek-button { display: none !important; }
        }

        .header-actions { display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; }
        .action-buttons { display: flex; gap: 0.8rem; }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div style="padding: 1.5rem;">
        
        <?php if (isset($_SESSION['flash_erro'])): ?>
            <div class="alert alert-error"><?= $_SESSION['flash_erro'] ?></div>
            <?php unset($_SESSION['flash_erro']); ?>
        <?php endif; ?>

        <?php if (isset($_SESSION['flash_sucesso'])): ?>
            <div class="alert alert-success"><?= $_SESSION['flash_sucesso'] ?></div>
            <?php unset($_SESSION['flash_sucesso']); ?>
        <?php endif; ?>

        <div class="header-actions">
            <h2 style="color: var(--text-main);">Minha Agenda</h2>
            <div class="action-buttons">
                <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/disponibilidade'" class="btn-primary" style="background: #718096; box-shadow: none;">Gerenciar Horário</button>
                <button data-modal-target="#modalNovoAgendamento" class="btn-primary">+ Novo Agendamento</button>
            </div>
        </div>

        <div id="calendario-agendamentos"></div>
    </div>

    <div id="modalNovoAgendamento" class="modal-overlay">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Novo Agendamento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAgendamento" action="<?= BASE_URL ?>/funcionario/agenda" method="POST">
                    <div class="form-group">
                        <label>Cliente</label>
                        <select name="id_cliente" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php if(!empty($clientes)): foreach($clientes as $cli): ?>
                                <option value="<?= $cli['id_cliente'] ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div class="form-grid">
                        <div class="form-group">
                            <label>Serviço</label>
                            <select id="id_servico" name="id_servico" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php if(!empty($servicos)): foreach($servicos as $sv): ?>
                                    <option value="<?= $sv['id_servico'] ?>"><?= htmlspecialchars($sv['nome_servico']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                        <div class="form-group">
                            <label>Profissional</label>
                            <select id="id_funcionario" name="id_funcionario" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php if(!empty($profissionais)): foreach($profissionais as $prof): ?>
                                    <option value="<?= $prof['id_funcionario'] ?>"><?= htmlspecialchars($prof['nome']) ?></option>
                                <?php endforeach; endif; ?>
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
        <div class="modal-content" style="max-width: 400px;">
            <div class="modal-header">
                <h3>Detalhes do Agendamento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Cliente:</strong> <span id="detalhesCliente"></span></p>
                <p><strong>Serviço:</strong> <span id="detalhesServico"></span></p>
                <p><strong>Horário:</strong> <span id="detalhesHorario"></span></p>
                <p><strong>Status:</strong> <span id="detalhesStatus"></span></p>
                
                <div id="areaAcoes" style="margin-top: 1.5rem; display: flex; gap: 0.5rem; flex-direction: column;">
                    <div id="boxAcoesPendente" style="display: none; gap: 0.5rem;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST" style="flex:1">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="marcado">
                            <button type="submit" class="btn-primary" style="background: #10b981; width: 100%;">Confirmar</button>
                        </form>
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST" style="flex:1">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="cancelado">
                            <button type="submit" class="btn-primary" style="background: #ef4444; width: 100%;">Recusar</button>
                        </form>
                    </div>

                    <div id="boxAcoesMarcado" style="display: none;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="concluido">
                            <button type="submit" class="btn-primary" style="background: #3b82f6; width: 100%;">Concluir Atendimento</button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            var calendarEl = document.getElementById('calendario-agendamentos');

            var calendar = new FullCalendar.Calendar(calendarEl, {
                locale: 'pt-br',
                initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
                slotMinTime: '08:00:00',
                slotMaxTime: '20:00:00',
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

                windowResize: function(view) {
                    if (window.innerWidth < 768) {
                        calendar.changeView('timeGridDay');
                    } else {
                        calendar.changeView('timeGridWeek');
                    }
                },

                events: [
                    <?php if(!empty($diasSemanaInfo)): ?>
                        <?php foreach ($diasSemanaInfo as $dia): ?>
                            <?php foreach ($dia['agendamentos'] as $ag): ?>
                            {
                                id: '<?= $ag['id_agendamento'] ?>',
                                title: '<?= addslashes($ag['cliente_nome']) ?>\n<?= addslashes($ag['nome_servico']) ?>',
                                start: '<?= (new DateTime($ag['hora_inicio']))->format("Y-m-d\TH:i:s") ?>',
                                end: '<?= (new DateTime($ag['hora_fim']))->format("Y-m-d\TH:i:s") ?>',
                                className: 'evento-<?= $ag['status'] ?>',
                                extendedProps: {
                                    cliente: '<?= addslashes($ag['cliente_nome']) ?>',
                                    servico: '<?= addslashes($ag['nome_servico']) ?>',
                                    status: '<?= $ag['status'] ?>'
                                }
                            },
                            <?php endforeach; ?>
                        <?php endforeach; ?>
                    <?php endif; ?>
                ],

                eventClick: function(info) {
                    const props = info.event.extendedProps;
                    document.getElementById('detalhesCliente').textContent = props.cliente;
                    document.getElementById('detalhesServico').textContent = props.servico;
                    document.getElementById('detalhesHorario').textContent = info.event.start.toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'});
                    document.getElementById('detalhesStatus').textContent = props.status.toUpperCase();

                    document.querySelectorAll('.inputIdAgendamento').forEach(input => input.value = info.event.id);
                    
                    document.getElementById('boxAcoesPendente').style.display = props.status === 'pendente' ? 'flex' : 'none';
                    document.getElementById('boxAcoesMarcado').style.display = props.status === 'marcado' ? 'block' : 'none';

                    document.getElementById('modalDetalhes').classList.add('active');
                }
            });

            calendar.render();

            // API de Horários Livres
            const inputsDispo = ['id_funcionario', 'id_servico', 'data'];
            inputsDispo.forEach(id => {
                document.getElementById(id).addEventListener('change', function() {
                    const func = document.getElementById('id_funcionario').value;
                    const serv = document.getElementById('id_servico').value;
                    const data = document.getElementById('data').value;
                    const selectHora = document.getElementById('hora');

                    if (func && serv && data) {
                        selectHora.innerHTML = '<option>A carregar...</option>';
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
                        });
                    }
                });
            });
        });
    </script>
</body>
</html>