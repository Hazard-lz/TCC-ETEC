<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Novo Agendamento - Belezou App</title>

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agendar.css">
</head>

<body>

    <div class="app-wrapper">
        <div class="mobile-container">

            <header class="app-header" style="justify-content: center;">
                <h2 style="color: var(--text-main); font-size: 1.2rem;">Novo Agendamento</h2>
            </header>

            <main class="app-content">

                <div class="progress-container">
                    <div class="step-indicator active" id="ind-1">1</div>
                    <div class="step-indicator" id="ind-2">2</div>
                    <div class="step-indicator" id="ind-3">3</div>
                    <div class="step-indicator" id="ind-4">✓</div>
                </div>

                <form id="formNovoAgendamento" action="<?= BASE_URL ?>/agendamento/salvar" method="POST">

                    <div class="step-content active" id="step-1">
                        <h3 class="section-title">O que vamos fazer hoje?</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Selecione o serviço desejado.</p>

                        <input type="hidden" id="servico_id" name="servico_id" required>
                        <input type="hidden" id="servico_nome">

                        <div class="cards-grid">
                            <div class="selectable-card" onclick="selecionarServico(1, 'Corte Masculino')">
                                <div>
                                    <div class="card-title">Corte Masculino</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 35,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(4, 'Escova Progressiva')">
                                <div>
                                    <div class="card-title">Escova Progressiva</div>
                                    <div class="card-subtitle">120 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 180,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(11, 'Manicure')">
                                <div>
                                    <div class="card-title">Manicure</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 30,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(1, 'Corte Masculino')">
                                <div>
                                    <div class="card-title">Corte Masculino</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 35,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(4, 'Escova Progressiva')">
                                <div>
                                    <div class="card-title">Escova Progressiva</div>
                                    <div class="card-subtitle">120 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 180,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(11, 'Manicure')">
                                <div>
                                    <div class="card-title">Manicure</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 30,00</div>
                            </div>

                             <div class="selectable-card" onclick="selecionarServico(1, 'Corte Masculino')">
                                <div>
                                    <div class="card-title">Corte Masculino</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 35,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(4, 'Escova Progressiva')">
                                <div>
                                    <div class="card-title">Escova Progressiva</div>
                                    <div class="card-subtitle">120 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 180,00</div>
                            </div>

                            <div class="selectable-card" onclick="selecionarServico(11, 'Manicure')">
                                <div>
                                    <div class="card-title">Manicure</div>
                                    <div class="card-subtitle">30 min</div>
                                </div>
                                <div style="font-weight: bold; color: var(--color-purple);">R$ 30,00</div>
                            </div>

                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn-primary" id="btn-next-1" disabled>Continuar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-2">
                        <h3 class="section-title">Escolha o Profissional</h3>

                        <input type="hidden" id="funcionario_id" name="funcionario_id" required>
                        <input type="hidden" id="funcionario_nome">

                        <div class="cards-grid">
                            <div class="selectable-card" onclick="selecionarProfissional(0, 'Qualquer Profissional')">
                                <div>
                                    <div class="card-title">Qualquer Profissional</div>
                                    <div class="card-subtitle">Menor tempo de espera</div>
                                </div>
                            </div>

                            <div class="selectable-card" onclick="selecionarProfissional(2, 'Maria Oliveira')">
                                <div>
                                    <div class="card-title">Maria Oliveira</div>
                                    <div class="card-subtitle">Especialista</div>
                                </div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(1)">Voltar</button>
                            <button type="button" class="btn-primary" id="btn-next-2" disabled>Continuar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-3">
                        <h3 class="section-title">Quando?</h3>

                        <input type="hidden" id="horario_selecionado" name="hora_inicio" required>

                        <div class="form-group">
                            <label>Selecione a Data</label>
                            <input type="date" id="data_agendamento" name="data_agendamento" class="form-control" required onchange="liberarHorarios()">
                        </div>

                        <div id="box-horarios" style="display: none;">
                            <label style="font-size: 0.9rem; font-weight: 600; color: var(--text-muted);">Horários Disponíveis</label>
                            <div class="time-grid">
                                <div class="time-slot" onclick="selecionarHorario('09:00', this)">09:00</div>
                                <div class="time-slot" onclick="selecionarHorario('10:00', this)">10:00</div>
                                <div class="time-slot" onclick="selecionarHorario('11:30', this)">11:30</div>
                                <div class="time-slot" onclick="selecionarHorario('14:00', this)">14:00</div>
                                <div class="time-slot" onclick="selecionarHorario('15:30', this)">15:30</div>
                                <div class="time-slot" onclick="selecionarHorario('17:00', this)">17:00</div>
                            </div>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(2)">Voltar</button>
                            <button type="button" class="btn-primary" id="btn-next-3" disabled onclick="montarResumo()">Revisar</button>
                        </div>
                    </div>

                    <div class="step-content" id="step-4">
                        <h3 class="section-title">Quase lá!</h3>
                        <p style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 1.5rem;">Revise os dados do seu agendamento.</p>

                        <div class="base-card" style="padding: 1.5rem; background: var(--bg-color); border: 1px solid var(--border-color); border-radius: var(--radius-md); box-shadow: none;">
                            <p style="margin-bottom: 0.5rem;"><strong style="color: var(--text-muted);">Serviço:</strong><br> <span id="resumo_servico" style="font-weight: 600; font-size: 1.1rem; color: var(--color-purple);"></span></p>
                            <p style="margin-bottom: 0.5rem;"><strong style="color: var(--text-muted);">Profissional:</strong><br> <span id="resumo_pro" style="color: var(--text-main);"></span></p>
                            <p><strong style="color: var(--text-muted);">Data e Hora:</strong><br> <span id="resumo_datahora" style="color: var(--text-main);"></span></p>
                        </div>

                        <div class="step-actions">
                            <button type="button" class="btn-secondary" onclick="voltarPasso(3)">Voltar</button>
                            <button type="submit" class="btn-primary">Confirmar Agendamento</button>
                        </div>
                    </div>

                </form>

            </main>

            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <a href="<?= BASE_URL ?>/agendar" class="nav-item active">
                    <span class="nav-icon">📅</span><span>Agendar</span>
                </a>
                <a href="<?= BASE_URL ?>/historico" class="nav-item">
                    <span class="nav-icon">🕒</span><span>Histórico</span>
                </a>
                <a href="<?= BASE_URL ?>/perfil" class="nav-item">
                    <span class="nav-icon">👤</span><span>Perfil</span>
                </a>
            </nav>

        </div>
    </div>

    <button id="themeToggle" class="btn-theme-toggle" title="Alternar Tema Escuro/Claro">🌓</button>

    <script src="<?= BASE_URL ?>/public/resources/js/agendar.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>

</html>