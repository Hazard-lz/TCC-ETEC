<?php
// ARQUITETURA: Iniciamos a sessão apenas se ela ainda não existir, evitando erros de "headers already sent"
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../../app/Models/Disponibilidade.php';
$dispModel = new Disponibilidade();
$idFuncionario = $_SESSION['usuario_id'];

// 1. Busca todas as grades que o funcionário já criou no banco de dados
$todasGrades = $dispModel->buscarGradesFuncionario($idFuncionario);

// 2. ARQUITETURA UX (POST em vez de GET): Lemos a grade selecionada da Sessão
$idDisponibilidade = $_SESSION['grade_visualizada'] ?? '';
$isNovaGrade = ($idDisponibilidade === 'nova');

$dadosDias = []; 
$nomeGradeAtual = '';
$antecedenciaHorasAtual = 0;
$isGradeAtiva = false;

// 3. Descobrir qual é a grade REALMENTE ativa para mostrar no painel global
$nomeGradePrincipal = 'Nenhuma';
if (!empty($todasGrades)) {
    foreach ($todasGrades as $g) {
        if ($g['is_ativa'] == 1) {
            $nomeGradePrincipal = $g['nome_grade'];
            break;
        }
    }
}

// 4. Lógica de preenchimento dos campos da grade selecionada
if (!$isNovaGrade && !empty($todasGrades)) {
    if (empty($idDisponibilidade)) {
        foreach($todasGrades as $g) {
            if ($g['is_ativa'] == 1) {
                $idDisponibilidade = $g['id_disponibilidade'];
                break;
            }
        }
        if (empty($idDisponibilidade)) {
            $idDisponibilidade = $todasGrades[0]['id_disponibilidade'];
        }
    }

    foreach($todasGrades as $g) {
        if ($g['id_disponibilidade'] == $idDisponibilidade) {
            $nomeGradeAtual = $g['nome_grade'];
            $antecedenciaHorasAtual = $g['antecedencia_horas'] ?? 0;
            $isGradeAtiva = ($g['is_ativa'] == 1);
            break;
        }
    }

    $diasBanco = $dispModel->buscarDiasDaGrade($idDisponibilidade);
    foreach ($diasBanco as $row) {
        $dadosDias[$row['dia_semana']] = [
            'inicio' => substr($row['hora_inicio_trabalho'], 0, 5),
            'fim' => substr($row['hora_fim_trabalho'], 0, 5),
            'int_inicio' => !empty($row['intervalo_inicio']) ? substr($row['intervalo_inicio'], 0, 5) : '',
            'int_fim' => !empty($row['intervalo_fim']) ? substr($row['intervalo_fim'], 0, 5) : '',
            'status' => $row['status']
        ];
    }
} elseif ($isNovaGrade) {
    $idDisponibilidade = ''; 
}

$diasSemana = [
    'Dom' => 'Domingo', 'Seg' => 'Segunda', 'Ter' => 'Terça', 
    'Qua' => 'Quarta', 'Qui' => 'Quinta', 'Sex' => 'Sexta', 'Sab' => 'Sábado'
];

$mostrarBotaoEditar = (!$isNovaGrade && !empty($idDisponibilidade)) ? 'block' : 'none';
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Disponibilidade - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/disponibilidade.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
</head>
<body>
    
    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header" style="margin-bottom: 2rem;">
        <div class="page-title">
            <h2 style="color: var(--text-main); margin-bottom: 0.5rem;">Minha Disponibilidade</h2>
            <p style="color: var(--text-muted);">Crie diferentes versões da sua agenda (Ex: Férias, Inverno) e alterne entre elas com 1 clique.</p>
        </div>
    </div>

    <div style="background: var(--bg-secondary); border: 2px solid <?= $nomeGradePrincipal !== 'Nenhuma' ? '#28a745' : '#dc3545' ?>; padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; display: flex; align-items: center; gap: 15px;">
        <span style="font-size: 2rem;">📅</span>
        <div>
            <span style="display: block; font-size: 0.85rem; font-weight: bold; color: var(--text-muted); text-transform: uppercase;">Status da Grade no App</span>
            <?php if($nomeGradePrincipal !== 'Nenhuma'): ?>
                <span style="font-size: 1.1rem; color: var(--text-main);">Os seus clientes estão marcando horários baseados na grade: <strong style="color: #28a745;"><?= htmlspecialchars($nomeGradePrincipal) ?></strong></span>
            <?php else: ?>
                <span style="font-size: 1.1rem; color: #dc3545; font-weight: bold;">Nenhuma grade ativa. Sua agenda está fechada para novos clientes!</span>
            <?php endif; ?>
        </div>
    </div>

    <?php if (isset($_SESSION['msg_sucesso'])): ?>
        <div class="alert alert-success" style="background: #d4edda; color: #155724; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $_SESSION['msg_sucesso']; unset($_SESSION['msg_sucesso']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['msg_erro'])): ?>
        <div class="alert alert-danger" style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 8px; margin-bottom: 20px;">
            <?= $_SESSION['msg_erro']; unset($_SESSION['msg_erro']); ?>
        </div>
    <?php endif; ?>

    <div class="base-card" style="max-width: 1000px; padding: 2rem;">
        
        <div class="grade-header">
            <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap; flex-grow: 1;">
                <label style="font-weight: bold; color: var(--text-main);">Grade Visualizada:</label>
                
                <form action="<?= BASE_URL ?>/funcionario/disponibilidade/selecionar" method="POST" style="margin: 0; display: flex; gap: 15px;">
                    <select name="grade_selecionada" class="form-control" style="width: auto; min-width: 250px;" onchange="this.form.submit()">
                        <?php if(empty($todasGrades)): ?>
                            <option value="">Nenhuma grade criada</option>
                        <?php else: ?>
                            <?php foreach($todasGrades as $g): ?>
                                <option value="<?= $g['id_disponibilidade'] ?>" <?= ($g['id_disponibilidade'] == $idDisponibilidade) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($g['nome_grade']) ?> <?= $g['is_ativa'] ? '(ATIVA)' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </select>
                </form>
                
                <form action="<?= BASE_URL ?>/funcionario/disponibilidade/selecionar" method="POST" style="margin: 0;">
                    <input type="hidden" name="grade_selecionada" value="nova">
                    <button type="submit" class="btn-secondary" style="padding: 8px 15px; border-radius: 5px; border: 1px solid var(--border-color); color: var(--text-main); background: transparent; cursor: pointer;">+ Nova Grade</button>
                </form>
            </div>

            <div style="display: flex; gap: 10px; align-items: center; flex-wrap: wrap;">
                <?php if(!empty($idDisponibilidade) && !$isNovaGrade): ?>
                    <form action="<?= BASE_URL ?>/funcionario/disponibilidade/salvar_antecedencia" method="POST" style="margin: 0; display: flex; align-items: center; gap: 8px; background: var(--bg-secondary); padding: 5px 15px; border-radius: 8px; border: 1px solid var(--border-color);">
                        <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                        <label style="font-weight: bold; color: var(--text-main); font-size: 0.9rem;">Antecedência <small>(Horas)</small>:</label>
                        <input type="number" name="antecedencia_horas" value="<?= htmlspecialchars($antecedenciaHorasAtual) ?>" min="0" max="24" class="form-control" style="width: 70px; padding: 4px; height: 32px;" title="Bloqueia agendamentos de última hora nesta grade.">
                        <button type="submit" class="btn-primary" style="padding: 4px 12px; height: 32px; background-color: #8b5cf6; color: #ffffff !important;">Salvar</button>
                    </form>
                <?php endif; ?>
                
                <?php if(!empty($idDisponibilidade) && !$isGradeAtiva): ?>
                    <form action="<?= BASE_URL ?>/funcionario/disponibilidade/ativar" method="POST" style="margin: 0;">
                        <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                        <button type="submit" class="btn-primary" style="background: #28a745; border-color: #28a745; padding: 8px 15px; height: 42px;">Ativar Grade</button>
                    </form>
                <?php endif; ?>
            </div>
        </div>

        <?php if (!$isNovaGrade && !empty($idDisponibilidade)): ?>
            <div class="grade-preview-container">
                <?php foreach($diasSemana as $sigla => $rotulo): 
                    $d = isset($dadosDias[$sigla]) ? $dadosDias[$sigla] : null;
                    $ativo = $d && $d['status'] === 'disponivel';
                ?>
                    <div class="day-card <?= $ativo ? 'active' : 'inactive' ?>">
                        <h4><?= $rotulo ?></h4>
                        <?php if($ativo): ?>
                            <p>🕒 <?= htmlspecialchars($d['inicio']) ?> às <?= htmlspecialchars($d['fim']) ?></p>
                            <?php if(!empty($d['int_inicio'])): ?>
                                <p class="pausa">☕ Pausa: <?= htmlspecialchars($d['int_inicio']) ?> às <?= htmlspecialchars($d['int_fim']) ?></p>
                            <?php else: ?>
                                <p class="pausa">Sem pausa</p>
                            <?php endif; ?>
                        <?php else: ?>
                            <p class="fechado">❌ Sem horário</p>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

        <div id="box-botao-editar" style="display: <?= $mostrarBotaoEditar ?>; margin-bottom: 25px;">
            <button type="button" class="btn-secondary" style="padding: 10px 20px; font-weight: bold; border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; border-radius: 8px; cursor: pointer;" data-modal-target="#modalEdicaoGrade">
                ✏️ Editar Horários Desta Grade
            </button>
            
            <?php if (!empty($idDisponibilidade)): ?>
                <button type="button" class="btn-danger" style="margin-left: 10px; background-color: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; padding: 12px 15px;" onclick="confirmarExclusaoGrade()">
                    Excluir Grade
                </button>
            <?php endif; ?>
        </div>

        <div class="modal-overlay <?= $isNovaGrade ? 'active' : '' ?>" id="modalEdicaoGrade">
            <div class="modal-content" style="max-width: 800px;">
                <div class="modal-header">
                    <h3><?= empty($idDisponibilidade) ? 'Criar Nova Grade' : '✏️ Editar Grade' ?></h3>
                    <button class="btn-close" type="button" <?= $isNovaGrade ? 'onclick="cancelarNovaGrade()"' : 'data-close-modal' ?>>&times;</button>
                </div>
                
                <div class="modal-body">
                    <?php if(empty($idDisponibilidade)): ?>
                        <div class="ux-banner ux-banner-new">
                            <span style="font-size: 0.9rem;">Preencha os horários abaixo. Ela não afetará sua agenda atual a menos que você marque a caixa para ativá-la.</span>
                        </div>
                    <?php endif; ?>

                    <form action="<?= BASE_URL ?>/funcionario/disponibilidade/salvar" method="POST" onsubmit="return confirmarSalvamento(event)">
                        <input type="hidden" name="id_disponibilidade" value="<?= htmlspecialchars($idDisponibilidade) ?>">
                        <input type="hidden" name="antecedencia_horas" value="<?= htmlspecialchars($antecedenciaHorasAtual) ?>">

                        <div style="display: flex; gap: 20px; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap;">
                            <div style="flex-grow: 1; min-width: 200px;">
                                <label style="font-weight: bold; color: var(--text-main); display: block; margin-bottom: 8px;">
                                    <?= empty($idDisponibilidade) ? 'Nome da Nova Grade' : 'Renomear esta Grade' ?>
                                </label>
                                <input type="text" name="nome_grade" class="form-control" value="<?= htmlspecialchars($nomeGradeAtual) ?>" required placeholder="Ex: Horário de Verão">
                            </div>
                            
                            <?php if (empty($idDisponibilidade)): ?>
                                <div style="padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                    <input type="checkbox" name="is_ativa" id="is_ativa" value="1" style="transform: scale(1.3);">
                                    <label for="is_ativa" style="font-weight: bold; color: var(--text-main); cursor: pointer;">Ativar como principal</label>
                                </div>
                            <?php endif; ?>
                        </div>

                        <div class="dias-grid">
                            <?php foreach($diasSemana as $sigla => $rotulo): 
                                $existe = isset($dadosDias[$sigla]);
                                $d = $existe ? $dadosDias[$sigla] : ['inicio'=>'08:00', 'fim'=>'18:00', 'int_inicio'=>'12:00', 'int_fim'=>'13:00', 'status'=>'disponivel'];
                                $ativo = $existe ? ($d['status'] === 'disponivel') : false;
                            ?>
                                <div class="day-row" id="row_<?= $sigla ?>" style="<?= !$ativo ? 'opacity: 0.5; filter: grayscale(100%); border-color: transparent;' : 'border-left: 4px solid #28a745;' ?>">
                                    
                                    <label class="toggle-container" style="min-width: 160px; display: flex; align-items: center; gap: 12px; cursor: pointer;">
                                        
                                        <div class="toggle-switch">
                                            <input type="checkbox" name="dias[<?= $sigla ?>][ativo]" value="1" id="dia_<?= $sigla ?>" <?= $ativo ? 'checked' : '' ?> onchange="toggleDayRow('<?= $sigla ?>')">
                                            <span class="toggle-slider"></span>
                                        </div>
                                        
                                        <span style="font-weight: 600; color: var(--text-main); font-size: 1.1rem; user-select: none;"><?= $rotulo ?></span>
                                    </label>

                                    <div class="time-input-group">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">Das</span>
                                        <input type="time" name="dias[<?= $sigla ?>][hora_inicio]" value="<?= htmlspecialchars($d['inicio']) ?>" class="form-control">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">às</span>
                                        <input type="time" name="dias[<?= $sigla ?>][hora_fim]" value="<?= htmlspecialchars($d['fim']) ?>" class="form-control">
                                    </div>

                                    <div class="time-input-group divider">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">Pausa:</span>
                                        <input type="time" id="int_ini_<?= $sigla ?>" name="dias[<?= $sigla ?>][intervalo_inicio]" value="<?= htmlspecialchars($d['int_inicio']) ?>" class="form-control">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">às</span>
                                        <input type="time" id="int_fim_<?= $sigla ?>" name="dias[<?= $sigla ?>][intervalo_fim]" value="<?= htmlspecialchars($d['int_fim']) ?>" class="form-control">
                                        
                                        <button type="button" class="btn-limpar-pausa" onclick="limparPausa('<?= $sigla ?>')" title="Remover horário de pausa">Limpar</button>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem; justify-content: flex-end;">
                            <button type="button" class="btn-secondary" <?= $isNovaGrade ? 'onclick="cancelarNovaGrade()"' : 'data-close-modal' ?>>Cancelar</button>
                            <button type="submit" class="btn-primary" style="min-width: 200px;">
                                <?= empty($idDisponibilidade) ? 'Salvar Nova Grade' : 'Salvar Alterações' ?>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <?php if (!empty($idDisponibilidade)): ?>
        <form id="form-excluir" action="<?= BASE_URL ?>/funcionario/disponibilidade/excluir" method="POST" style="display: none;">
            <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
        </form>
        <?php endif; ?>
        
        <form id="form-cancelar" action="<?= BASE_URL ?>/funcionario/disponibilidade/selecionar" method="POST" style="display: none;">
            <input type="hidden" name="grade_selecionada" value="">
        </form>

    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/disponibilidade.js"></script>

    <script>
        function confirmarSalvamento(event) {
            event.preventDefault(); 
            const acao = "<?= empty($idDisponibilidade) ? 'criar esta nova' : 'salvar as alterações nesta' ?>";
            if (confirm(`Deseja confirmar e ${acao} grade de horários?`)) {
                event.target.submit(); 
            }
        }

        function confirmarExclusaoGrade() {
            if(confirm('ATENÇÃO: Tem certeza que deseja EXCLUIR esta grade permanentemente? Esta ação não pode ser desfeita.')) { 
                document.getElementById('form-excluir').submit(); 
            }
        }

        function cancelarNovaGrade() {
            document.getElementById('form-cancelar').submit();
        }

        function toggleDayRow(sigla) {
            const checkbox = document.getElementById('dia_' + sigla);
            const row = document.getElementById('row_' + sigla);
            
            if (checkbox.checked) {
                row.style.opacity = '1';
                row.style.filter = 'none';
                row.style.borderLeft = '4px solid #28a745';
            } else {
                row.style.opacity = '0.5';
                row.style.filter = 'grayscale(100%)';
                row.style.borderLeft = '1px solid var(--border-color)';
            }
        }

        function limparPausa(sigla) {
            document.getElementById('int_ini_' + sigla).value = '';
            document.getElementById('int_fim_' + sigla).value = '';
        }

        window.addEventListener("beforeunload", function () {
            sessionStorage.setItem("scrollPosition", window.scrollY);
        });

        window.addEventListener("load", function () {
            const scrollPos = sessionStorage.getItem("scrollPosition");
            if (scrollPos !== null) {
                window.scrollTo(0, parseInt(scrollPos));
                sessionStorage.removeItem("scrollPosition"); 
            }
        });

        const modalOverlay = document.getElementById('modalEdicaoGrade');
        if (modalOverlay) {
            modalOverlay.addEventListener('click', function(event) {
                if (event.target === this) {
                    <?php if ($isNovaGrade): ?>
                        cancelarNovaGrade();
                    <?php endif; ?>
                }
            });
        }
    </script>
</body>
</html>