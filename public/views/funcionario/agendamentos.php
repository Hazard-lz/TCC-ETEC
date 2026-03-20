<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Agendamentos - Belezou App</title>
    
    <link rel="icon" type="image/png" href="/public/resources/images/favicon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agenda.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
</head>
<body>

    <div class="admin-wrapper">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php require_once __DIR__ . '/../partials/header.php'; ?>

            <section class="content-area">
                
                <div class="calendar-wrapper">
                    
                    <div class="calendar-toolbar">
                        <div class="calendar-nav">
                            <button class="btn-icon">&lt;</button>
                            <button class="btn-icon">Hoje</button>
                            <button class="btn-icon">&gt;</button>
                            <h3 style="margin-left: 1rem; color: var(--text-main);">Março 2026</h3>
                        </div>
                        
                        <div class="calendar-actions">
                            <button data-modal-target="#modalBloqueio" class="btn-primary" style="width: auto; margin: 0; background: #718096; box-shadow: none;">Bloquear Horário</button>
                            
                            <button data-modal-target="#modalNovoAgendamento" class="btn-primary" style="width: auto; margin: 0;">+ Novo Agendamento</button>
                        </div>
                    </div>

                    <div class="calendar-scroll-area">
                        <div class="calendar-grid">
                            
                            <div class="calendar-days-header">
                                <div class="day-header" style="border-right: none;">GMT-3</div>
                                <div class="day-header">Seg <span>09</span></div>
                                <div class="day-header today">Ter <span>10</span></div>
                                <div class="day-header">Qua <span>11</span></div>
                                <div class="day-header">Qui <span>12</span></div>
                                <div class="day-header">Sex <span>13</span></div>
                                <div class="day-header">Sáb <span>14</span></div>
                            </div>

                            <div class="calendar-body">
                                
                                <div class="time-column">
                                    <div class="time-slot-label">09:00</div>
                                    <div class="time-slot-label">10:00</div>
                                    <div class="time-slot-label">11:00</div>
                                    <div class="time-slot-label">12:00</div>
                                    <div class="time-slot-label">13:00</div>
                                    <div class="time-slot-label">14:00</div>
                                    <div class="time-slot-label">15:00</div>
                                    <div class="time-slot-label">16:00</div>
                                    <div class="time-slot-label">17:00</div>
                                    <div class="time-slot-label">18:00</div>
                                </div>

                                <div class="day-column"></div> <div class="day-column">
                                    <div class="event-block event-marcado" style="top: 60px; height: 90px;" onclick="verDetalhes('João Silva', 'Corte Masculino', '10:00 - 11:30', 'Marcado')">
                                        <div class="event-title">João Silva</div>
                                        <div class="event-time">10:00 - 11:30 | Corte</div>
                                    </div>
                                    <div class="event-block event-concluido" style="top: 330px; height: 90px;" onclick="verDetalhes('Ana Pereira', 'Escova Progressiva', '14:30 - 16:00', 'Concluído')">
                                        <div class="event-title">Ana Pereira</div>
                                        <div class="event-time">14:30 - 16:00 | Escova</div>
                                    </div>
                                </div>

                                <div class="day-column">
                                    <div class="event-block event-pendente" style="top: 30px; height: 30px;" onclick="verDetalhes('Carlos Souza', 'Barba', '09:30 - 10:00', 'Pendente')">
                                        <div class="event-title">Carlos Souza</div>
                                    </div>
                                </div>

                                <div class="day-column"></div> <div class="day-column"></div> <div class="day-column"></div> </div>
                        </div>
                    </div>
                </div>

            </section>
        </main>
    </div>

    <div id="modalNovoAgendamento" class="modal-overlay">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Agendar Atendimento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formAgendamento" action="/agendamento/salvar" method="POST">
                    
                    <div class="form-group">
                        <label for="cod_cliente">Selecione o Cliente</label>
                        <select id="cod_cliente" name="cod_cliente" class="form-control" required>
                            <option value="">Selecione...</option>
                            <option value="1">João Silva</option>
                            <option value="3">Ana Pereira</option>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mobile-grid-1">
                        <div class="form-group">
                            <label for="cod_servico">Serviço</label>
                            <select id="cod_servico" name="cod_servico" class="form-control" required>
                                <option value="">Selecione...</option>
                                <option value="1">Corte Masculino (R$ 35,00)</option>
                                <option value="4">Escova Progressiva (R$ 180,00)</option>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="cod_funcionario">Profissional</label>
                            <select id="cod_funcionario" name="cod_funcionario" class="form-control" required>
                                <option value="">Qualquer Profissional...</option>
                                <option value="2">Maria Oliveira (Admin)</option>
                                <option value="6">Fernanda Costa (Comum)</option>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mobile-grid-1">
                        <div class="form-group">
                            <label for="data_agendamento">Data</label>
                            <input type="date" id="data_agendamento" name="data_agendamento" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="hora_inicio">Horário de Início</label>
                            <input type="time" id="hora_inicio" name="hora_inicio" class="form-control" min="09:00" max="18:00" required>
                        </div>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 1rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0;">Confirmar Agendamento</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
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
                <p style="margin-top: 0.5rem;"><strong>Serviço:</strong> <span id="detalhesServico"></span></p>
                <p style="margin-top: 0.5rem;"><strong>Horário:</strong> <span id="detalhesHorario"></span></p>
                <p style="margin-top: 0.5rem;"><strong>Status:</strong> <span id="detalhesStatus"></span></p>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button class="btn-primary" style="margin-top: 0;">Editar</button>
                    <button data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <div id="modalBloqueio" class="modal-overlay">
        <div class="modal-content" style="max-width: 500px;">
            <div class="modal-header">
                <h3 style="color: #4a5568;">Bloquear Agenda</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formBloqueio" action="/agendamento/bloquear" method="POST">
                    
                    <div class="form-group">
                        <label for="bloqueio_funcionario">Profissional</label>
                        <select id="bloqueio_funcionario" name="cod_funcionario" class="form-control" required>
                            <option value="todos">Todos (Fechar o Salão inteiro)</option>
                            <option value="2">Maria Oliveira</option>
                            <option value="6">Fernanda Costa</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="bloqueio_data">Data</label>
                        <input type="date" id="bloqueio_data" name="data" class="form-control" required>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mobile-grid-1">
                        <div class="form-group">
                            <label for="bloqueio_inicio">Hora de Início</label>
                            <input type="time" id="bloqueio_inicio" name="hora_inicio" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="bloqueio_fim">Hora de Término</label>
                            <input type="time" id="bloqueio_fim" name="hora_fim" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="bloqueio_motivo">Motivo (Opcional)</label>
                        <input type="text" id="bloqueio_motivo" name="motivo" class="form-control" placeholder="Ex: Consulta Médica, Almoço, Feriado...">
                    </div>
                    
                    <div id="bloqueioError" class="error-message">A hora de término deve ser maior que a de início.</div>

                    <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0; background: #4a5568;">Confirmar Bloqueio</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

   <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/agendamento.js"></script>
</body>
</html>
   
</body>
</html>