<?php
// 1. Inicia a sessão
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// 2. Busca os dados existentes da grelha do funcionário
require_once __DIR__ . '/../../../app/Models/Disponibilidade.php';
$dispModel = new Disponibilidade();
$gradeExistente = $dispModel->buscarPorFuncionario($_SESSION['usuario_id']);

$idDisponibilidade = '';
$dadosDias = []; 

// Se o funcionário já tem horários, mapeamos os dados para um array fácil de ler na View
if (!empty($gradeExistente)) {
    $idDisponibilidade = $gradeExistente[0]['id_disponibilidade'];
    
    foreach ($gradeExistente as $row) {
        $dadosDias[$row['dia_semana']] = [
            'inicio' => substr($row['hora_inicio_trabalho'], 0, 5),
            'fim' => substr($row['hora_fim_trabalho'], 0, 5),
            'int_inicio' => !empty($row['intervalo_inicio']) ? substr($row['intervalo_inicio'], 0, 5) : '',
            'int_fim' => !empty($row['intervalo_fim']) ? substr($row['intervalo_fim'], 0, 5) : '',
            'status' => $row['status']
        ];
    }
}

// Lista fixa de dias para gerar o formulário
$diasSemana = [
    'Dom' => 'Domingo', 'Seg' => 'Segunda', 'Ter' => 'Terça', 
    'Qua' => 'Quarta', 'Qui' => 'Quinta', 'Sex' => 'Sexta', 'Sab' => 'Sábado'
];
?>

<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Disponibilidade - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/disponibilidade.css">
    
    <style>
        .day-row {
            background: var(--bg-secondary);
            padding: 15px;
            border-radius: 8px;
            border: 1px solid var(--border-color);
            margin-bottom: 15px;
            display: flex;
            flex-wrap: wrap;
            gap: 15px;
            align-items: center;
            transition: all 0.3s ease;
        }
        .day-row:hover {
            border-color: var(--primary-color);
            box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        }
        .time-input-group {
            display: flex;
            gap: 10px;
            align-items: center;
        }
        .day-row input[type="time"], .day-row select {
            width: auto;
            padding: 6px 10px;
        }
        .divider {
            border-left: 2px solid var(--border-color);
            padding-left: 15px;
            margin-left: 5px;
        }
        
        /* Layout responsivo para telas menores */
        @media (max-width: 768px) {
            .divider { border-left: none; padding-left: 0; margin-left: 0; border-top: 1px dashed var(--border-color); padding-top: 10px; width: 100%; }
            .day-row { flex-direction: column; align-items: flex-start; }
        }
    </style>
</head>
<body>
    <div class="admin-wrapper">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php require_once __DIR__ . '/../partials/header.php'; ?>

            <section class="content-area">
                <div class="page-header" style="margin-bottom: 2rem;">
                    <div class="page-title">
                        <h2 style="color: var(--text-main); margin-bottom: 0.5rem;">Minha Disponibilidade</h2>
                        <p style="color: var(--text-muted);">Configure os seus dias e horários de atendimento de forma individual.</p>
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

                <div class="base-card" style="max-width: 900px; padding: 2rem;">
                    
                    <form action="<?= BASE_URL ?>/funcionario/disponibilidade/salvar" method="POST">
                        <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                        
                        <p style="color: var(--text-muted); font-size: 0.95rem; margin-bottom: 1.5rem;">
                            Marque a caixa de seleção nos dias em que trabalha. Para pausas, preencha caso necessário.
                        </p>

                        <div class="dias-grid">
                            <?php foreach($diasSemana as $sigla => $rotulo): 
                                // 1. Verifica se o dia já foi salvo no banco alguma vez
                                $existe = isset($dadosDias[$sigla]);
                                
                                // 2. Preenche com os dados reais ou com valores em branco
                                $d = $existe ? $dadosDias[$sigla] : ['inicio'=>'', 'fim'=>'', 'int_inicio'=>'', 'int_fim'=>'', 'status'=>'disponivel'];
                                
                                // 3. A caixa SÓ deve ficar marcada se o dia existe E o status for 'disponivel'
                                $ativo = $existe && $d['status'] === 'disponivel';
                            ?>
                                <div class="day-row" id="row_<?= $sigla ?>" style="<?= !$ativo ? 'opacity: 0.5; filter: grayscale(100%);' : '' ?>">
                                    
                                    <div style="min-width: 160px; display: flex; align-items: center; gap: 10px;">
                                        <input type="checkbox" name="dias[<?= $sigla ?>][ativo]" value="1" id="dia_<?= $sigla ?>" <?= $ativo ? 'checked' : '' ?> style="transform: scale(1.4); cursor: pointer;" onchange="toggleDayRow('<?= $sigla ?>')">
                                        <label for="dia_<?= $sigla ?>" style="font-weight: 600; color: var(--text-main); cursor:pointer; font-size: 1.1rem;"><?= $rotulo ?></label>
                                    </div>

                                    <div class="time-input-group">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">Trabalha das</span>
                                        <input type="time" name="dias[<?= $sigla ?>][hora_inicio]" value="<?= $d['inicio'] ?>" class="form-control">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">às</span>
                                        <input type="time" name="dias[<?= $sigla ?>][hora_fim]" value="<?= $d['fim'] ?>" class="form-control">
                                    </div>

                                    <div class="time-input-group divider">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">Pausa das</span>
                                        <input type="time" name="dias[<?= $sigla ?>][intervalo_inicio]" value="<?= $d['int_inicio'] ?>" class="form-control">
                                        <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">às</span>
                                        <input type="time" name="dias[<?= $sigla ?>][intervalo_fim]" value="<?= $d['int_fim'] ?>" class="form-control">
                                    </div>

                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                            <button type="submit" class="btn-primary" style="max-width: 300px;">
                                <?= empty($idDisponibilidade) ? 'Salvar Configurações' : 'Atualizar Horários' ?>
                            </button>

                            <?php if (!empty($idDisponibilidade)): ?>
                                <button type="button" class="btn-danger" style="max-width: 250px; background-color: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold;" onclick="if(confirm('Tem certeza que deseja marcar toda a sua grelha como indisponível?')) { document.getElementById('form-excluir').submit(); }">
                                    Inativar Grelha Inteira
                                </button>
                            <?php endif; ?>
                        </div>
                    </form>

                    <?php if (!empty($idDisponibilidade)): ?>
                    <form id="form-excluir" action="<?= BASE_URL ?>/funcionario/disponibilidade/excluir" method="POST" style="display: none;">
                        <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                    </form>
                    <?php endif; ?>

                </div>
            </section>
        </main>
    </div>
    
    <button id="themeToggle" class="btn-theme-toggle" title="Alternar Tema Escuro/Claro">🌓</button>

    <script src="<?= BASE_URL ?>/public/resources/js/admin-layout.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
    
    <script>
        function toggleDayRow(sigla) {
            const checkbox = document.getElementById('dia_' + sigla);
            const row = document.getElementById('row_' + sigla);
            if (checkbox.checked) {
                row.style.opacity = '1';
            } else {
                row.style.opacity = '0.6';
            }
        }
    </script>
</body>
</html>