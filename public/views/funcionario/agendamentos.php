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
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/agenda.css?v=<?= time() ?>">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">

    <style>
        .alert { padding: 1rem; margin-bottom: 1rem; border-radius: 8px; font-weight: 500; font-size: 0.9rem; }
        .alert-error { background-color: #fee2e2; color: #b91c1c; border: 1px solid #f87171; }
        .alert-success { background-color: #d1fae5; color: #047857; border: 1px solid #34d399; }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="calendar-wrapper">
        
        <div style="padding: 1rem 1.5rem 0 1.5rem;">
            <?php if (isset($_SESSION['flash_erro'])): ?>
                <div class="alert alert-error"><?= $_SESSION['flash_erro'] ?></div>
                <?php unset($_SESSION['flash_erro']); ?>
            <?php endif; ?>

            <?php if (isset($_SESSION['flash_sucesso'])): ?>
                <div class="alert alert-success"><?= $_SESSION['flash_sucesso'] ?></div>
                <?php unset($_SESSION['flash_sucesso']); ?>
            <?php endif; ?>
        </div>

        <div class="calendar-toolbar">
            <div class="calendar-nav">
                <a href="?data=<?= $dataAnterior ?>" class="btn-icon" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">&lt;</a>
                <a href="?data=<?= $hoje ?>" class="btn-icon" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center; width:auto; padding:0 10px;">Hoje</a>
                <a href="?data=<?= $dataProxima ?>" class="btn-icon" style="text-decoration:none; display:inline-flex; align-items:center; justify-content:center;">&gt;</a>
                <h3 style="margin-left: 1rem; color: var(--text-main);"><?= $mesNome ?> <?= $ano ?></h3>
            </div>
            
            <div class="calendar-actions">
                <div id="agendamentoError" class="error-message" style="display: none; color: #dc2626; background: #fee2e2; padding: 10px; border-radius: 6px; margin-top: 10px; font-size: 0.9rem;"></div>
                <button onclick="window.location.href='<?= BASE_URL ?>/funcionario/disponibilidade'" class="btn-primary" style="width: auto; margin: 0; background: #718096; box-shadow: none;">Gerenciar Horário</button>
                <button data-modal-target="#modalNovoAgendamento" class="btn-primary" style="width: auto; margin: 0;">+ Novo Agendamento</button>
            </div>
        </div>

        <div class="calendar-scroll-area">
            <div class="calendar-grid">
                
                <div class="calendar-days-header">
                    <div class="day-header" style="border-right: none;">GMT-3</div>
                    <?php foreach ($diasSemanaInfo as $dia): ?>
                        <div class="day-header <?= $dia['is_today'] ? 'today' : '' ?>">
                            <?= $dia['nome'] ?> <span><?= $dia['dia_num'] ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="calendar-body">
                    <div class="time-column">
                        <?php for($h = 9; $h <= 18; $h++): ?>
                            <div class="time-slot-label"><?= str_pad($h, 2, '0', STR_PAD_LEFT) ?>:00</div>
                        <?php endfor; ?>
                    </div>

                    <?php foreach ($diasSemanaInfo as $dia): ?>
                        <div class="day-column">
                            <?php foreach ($dia['agendamentos'] as $ag): 
                                $hInicio = new DateTime($ag['hora_inicio']);
                                $hFim = new DateTime($ag['hora_fim']);
                                
                                $minutosInicio = ((int)$hInicio->format('H') * 60) + (int)$hInicio->format('i');
                                $minutosFim = ((int)$hFim->format('H') * 60) + (int)$hFim->format('i');
                                
                                $top = $minutosInicio - 540; 
                                $height = $minutosFim - $minutosInicio;
                                $classeCor = 'event-' . $ag['status']; 
                                $horaFormatada = $hInicio->format('H:i') . ' - ' . $hFim->format('H:i');
                            ?>
                                <div class="event-block <?= $classeCor ?>" 
                                     style="top: <?= $top ?>px; height: <?= $height ?>px;" 
                                     onclick="abrirModalDetalhes(<?= $ag['id_agendamento'] ?>, '<?= htmlspecialchars($ag['cliente_nome']) ?>', '<?= htmlspecialchars($ag['nome_servico']) ?>', '<?= $horaFormatada ?>', '<?= ucfirst($ag['status']) ?>', '<?= $ag['status'] ?>')">
                                    <div class="event-title"><?= htmlspecialchars($ag['cliente_nome']) ?></div>
                                    <?php if ($height >= 45): ?>
                                        <div class="event-time"><?= $horaFormatada ?> | <?= htmlspecialchars($ag['nome_servico']) ?></div>
                                    <?php endif; ?>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    </div>

    <div id="modalNovoAgendamento" class="modal-overlay">
        <div class="modal-content" style="max-width: 700px;">
            <div class="modal-header">
                <h3>Agendar Atendimento</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formAgendamento" action="<?= BASE_URL ?>/funcionario/agenda" method="POST">
                    
                    <div class="form-group">
                        <label for="id_cliente">Selecione o Cliente</label>
                        <select id="id_cliente" name="id_cliente" class="form-control" required>
                            <option value="">Selecione...</option>
                            <?php if(!empty($clientes)): foreach($clientes as $cli): ?>
                                <option value="<?= $cli['id_cliente'] ?>"><?= htmlspecialchars($cli['nome']) ?></option>
                            <?php endforeach; endif; ?>
                        </select>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mobile-grid-1">
                        <div class="form-group">
                            <label for="id_servico">Serviço</label>
                            <select id="id_servico" name="id_servico" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php if(!empty($servicos)): foreach($servicos as $sv): ?>
                                    <option value="<?= $sv['id_servico'] ?>">
                                        <?= htmlspecialchars($sv['nome_servico']) ?> (R$ <?= number_format($sv['preco'] ?? 0, 2, ',', '.') ?>)
                                    </option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="id_funcionario">Profissional</label>
                            <select id="id_funcionario" name="id_funcionario" class="form-control" required>
                                <option value="">Selecione...</option>
                                <?php if(!empty($profissionais)): foreach($profissionais as $prof): ?>
                                    <option value="<?= $prof['id_funcionario'] ?>"><?= htmlspecialchars($prof['nome']) ?></option>
                                <?php endforeach; endif; ?>
                            </select>
                        </div>
                    </div>

                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1.5rem;" class="mobile-grid-1">
                        <div class="form-group">
                            <label for="data">Data</label>
                            <input type="date" id="data" name="data" class="form-control" value="<?= htmlspecialchars($_GET['data'] ?? date('Y-m-d')) ?>" required>
                        </div>
                        <div class="form-group">
                            <label for="hora">Horários Disponíveis</label>
                            <select id="hora" name="hora" class="form-control" required>
                                <option value="">Preencha Profissional, Serviço e Data</option>
                            </select>
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
                <button data-close-modal class="btn-close" onclick="fecharModalDetalhes()">&times;</button>
            </div>
            <div class="modal-body">
                <p><strong>Cliente:</strong> <span id="detalhesCliente"></span></p>
                <p style="margin-top: 0.5rem;"><strong>Serviço:</strong> <span id="detalhesServico"></span></p>
                <p style="margin-top: 0.5rem;"><strong>Horário:</strong> <span id="detalhesHorario"></span></p>
                <p style="margin-top: 0.5rem;"><strong>Status:</strong> <span id="detalhesStatus"></span></p>
                
                <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                    <div id="boxAcoesPendente" style="display: none; width: 100%; gap: 1rem;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST" style="flex: 1;">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="marcado">
                            <button type="submit" class="btn-primary" style="margin-top: 0; background: #10b981; width: 100%;">Confirmar</button>
                        </form>
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST" style="flex: 1;">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="cancelado">
                            <button type="submit" class="btn-primary" style="margin-top: 0; background: #ef4444; width: 100%;">Recusar</button>
                        </form>
                    </div>

                    <div id="boxAcoesMarcado" style="display: none; width: 100%;">
                        <form action="<?= BASE_URL ?>/funcionario/agenda/status" method="POST" style="flex: 1;">
                            <input type="hidden" name="id_agendamento" class="inputIdAgendamento">
                            <input type="hidden" name="novo_status" value="concluido">
                            <button type="submit" class="btn-primary" style="margin-top: 0; background: #3b82f6; width: 100%;">Concluir Atendimento</button>
                        </form>
                    </div>

                    <button class="btn-primary" onclick="fecharModalDetalhes()" style="margin-top: 1rem; background: #e2e8f0; color: var(--text-main); box-shadow: none; width: 100%;">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>

    <script>
        function abrirModalDetalhes(id, cliente, servico, horario, statusLabel, statusValue) {
            document.getElementById('detalhesCliente').textContent = cliente;
            document.getElementById('detalhesServico').textContent = servico;
            document.getElementById('detalhesHorario').textContent = horario;
            document.getElementById('detalhesStatus').textContent = statusLabel;

            const inputsId = document.querySelectorAll('.inputIdAgendamento');
            inputsId.forEach(input => input.value = id);

            document.getElementById('boxAcoesPendente').style.display = 'none';
            document.getElementById('boxAcoesMarcado').style.display = 'none';

            if (statusValue === 'pendente') {
                document.getElementById('boxAcoesPendente').style.display = 'flex';
            } else if (statusValue === 'marcado') {
                document.getElementById('boxAcoesMarcado').style.display = 'block';
            }

            document.getElementById('modalDetalhes').classList.add('active');
        }

        function fecharModalDetalhes() {
            document.getElementById('modalDetalhes').classList.remove('active');
        }

        // ==========================================
        // CONSUMO DA API DE DISPONIBILIDADE
        // ==========================================
        function buscarHorariosLivres() {
            const idFuncionario = document.getElementById('id_funcionario').value;
            const idServico = document.getElementById('id_servico').value;
            const data = document.getElementById('data').value;
            const selectHora = document.getElementById('hora');

            if (!idFuncionario || !idServico || !data) {
                selectHora.innerHTML = '<option value="">Preencha Profissional, Serviço e Data</option>';
                return;
            }

            selectHora.innerHTML = '<option value="">A procurar horários livres...</option>';

            // Comunica com o teu Backend que já usas no ecrã do Cliente!
            fetch("<?= BASE_URL ?>/api/horarios-livres", {
                method: "POST",
                headers: { "Content-Type": "application/json" },
                body: JSON.stringify({
                    id_funcionario: idFuncionario,
                    id_servico: idServico,
                    data: data
                })
            })
            .then(response => response.json())
            .then(data => {
                selectHora.innerHTML = '<option value="">Selecione o Horário</option>';
                
                if (data.sucesso && data.horarios && data.horarios.length > 0) {
                    data.horarios.forEach(hora => {
                        const option = document.createElement('option');
                        option.value = hora;
                        option.textContent = hora.substring(0, 5); // Ex: "09:00"
                        selectHora.appendChild(option);
                    });
                } else {
                    selectHora.innerHTML = '<option value="">Nenhum horário disponível</option>';
                }
            })
            .catch(error => {
                console.error("Erro na API:", error);
                selectHora.innerHTML = '<option value="">Erro de comunicação.</option>';
            });
        }

        // Os eventos disparam sempre que mudas uma opção no Modal!
        document.getElementById('id_funcionario').addEventListener('change', buscarHorariosLivres);
        document.getElementById('id_servico').addEventListener('change', buscarHorariosLivres);
        document.getElementById('data').addEventListener('change', buscarHorariosLivres);
    </script>
</body>
</html>