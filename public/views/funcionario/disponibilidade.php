<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }

require_once __DIR__ . '/../../../app/Models/Disponibilidade.php';
$dispModel = new Disponibilidade();
$idFuncionario = $_SESSION['usuario_id'];

// 1. Busca todas as grades que o funcionário já criou
$todasGrades = $dispModel->buscarGradesFuncionario($idFuncionario);

// 2. ARQUITETURA UX (POST em vez de GET): Lemos a grade selecionada da Sessão
$idDisponibilidade = $_SESSION['grade_visualizada'] ?? '';
$isNovaGrade = ($idDisponibilidade === 'nova');

$dadosDias = []; 
$nomeGradeAtual = '';
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

// 4. Lógica de preenchimento dos campos
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

$mostrarFormulario = $isNovaGrade ? 'block' : 'none';
$mostrarBotaoEditar = (!$isNovaGrade && !empty($idDisponibilidade)) ? 'block' : 'none';
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
        .day-row { background: var(--bg-secondary); padding: 15px; border-radius: 8px; border: 1px solid var(--border-color); margin-bottom: 15px; display: flex; flex-wrap: wrap; gap: 15px; align-items: center; transition: all 0.3s ease; }
        .day-row:hover { border-color: var(--primary-color); box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
        .time-input-group { display: flex; gap: 10px; align-items: center; }
        .day-row input[type="time"] { width: auto; padding: 6px 10px; }
        .divider { border-left: 2px solid var(--border-color); padding-left: 15px; margin-left: 5px; }
        .btn-limpar-pausa { background: none; border: none; color: #dc3545; font-size: 0.8rem; cursor: pointer; font-weight: bold; padding: 0 5px; text-decoration: underline; transition: color 0.2s; }
        .btn-limpar-pausa:hover { color: #a71d2a; }
        .grade-header { display: flex; justify-content: space-between; align-items: center; background: var(--bg-secondary); padding: 15px 20px; border-radius: 8px; margin-bottom: 25px; border: 1px solid var(--border-color); }
        
        .ux-banner { padding: 15px; border-radius: 6px; margin-bottom: 20px; border-left: 4px solid; display: flex; flex-direction: column; gap: 5px; }
        .ux-banner-new { background-color: rgba(40, 167, 69, 0.1); border-left-color: #28a745; color: var(--text-main); }
        .ux-banner-edit { background-color: rgba(23, 162, 184, 0.1); border-left-color: #17a2b8; color: var(--text-main); }

        #area-edicao { transition: opacity 0.4s ease-in-out; }

        @media (max-width: 768px) {
            .divider { border-left: none; padding-left: 0; margin-left: 0; border-top: 1px dashed var(--border-color); padding-top: 10px; width: 100%; }
            .day-row { flex-direction: column; align-items: flex-start; }
            .grade-header { flex-direction: column; align-items: flex-start; gap: 15px; }
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
                        <div style="display: flex; gap: 15px; align-items: center; flex-wrap: wrap;">
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

                        <?php if(!empty($idDisponibilidade) && !$isGradeAtiva): ?>
                            <form action="<?= BASE_URL ?>/funcionario/disponibilidade/ativar" method="POST" style="margin: 0;">
                                <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                                <button type="submit" class="btn-primary" style="background: #28a745; border-color: #28a745; padding: 8px 15px;">🚀 Ativar esta Grade</button>
                            </form>
                        <?php endif; ?>
                    </div>

                    <div id="box-botao-editar" style="display: <?= $mostrarBotaoEditar ?>; margin-bottom: 25px;">
                        <button type="button" class="btn-secondary" style="padding: 10px 20px; font-weight: bold; border: 2px solid var(--primary-color); color: var(--primary-color); background: transparent; border-radius: 8px; cursor: pointer;" onclick="abrirEdicao()">
                            ✏️ Editar Horários Desta Grade
                        </button>
                        
                        <?php if (!empty($idDisponibilidade)): ?>
                            <button type="button" class="btn-danger" style="margin-left: 10px; background-color: #dc3545; color: white; border: none; border-radius: 8px; cursor: pointer; font-weight: bold; padding: 12px 15px;" onclick="if(confirm('Tem certeza que deseja EXCLUIR esta grade permanentemente?')) { document.getElementById('form-excluir').submit(); }">
                                Excluir Grade
                            </button>
                        <?php endif; ?>
                    </div>

                    <div id="area-edicao" style="display: <?= $mostrarFormulario ?>; opacity: <?= $mostrarFormulario === 'block' ? '1' : '0' ?>;">
                        
                        <?php if(empty($idDisponibilidade)): ?>
                            <div class="ux-banner ux-banner-new">
                                <span style="font-size: 1.1rem; font-weight: bold;">✨ Criando uma Nova Grade</span>
                                <span style="font-size: 0.9rem;">Preencha os horários abaixo. Ela não afetará sua agenda atual a menos que você marque a caixa para ativá-la.</span>
                            </div>
                        <?php else: ?>
                            <div class="ux-banner ux-banner-edit">
                                <span style="font-size: 1.1rem; font-weight: bold;">✏️ Editando os Horários Desta Grade</span>
                            </div>
                        <?php endif; ?>

                        <form action="<?= BASE_URL ?>/funcionario/disponibilidade/salvar" method="POST">
                            <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                            
                            <div style="display: flex; gap: 20px; align-items: flex-end; margin-bottom: 25px; flex-wrap: wrap;">
                                <div style="flex-grow: 1; min-width: 200px;">
                                    <label style="font-weight: bold; color: var(--text-main); display: block; margin-bottom: 8px;">
                                        <?= empty($idDisponibilidade) ? 'Nome da Nova Grade' : 'Renomear esta Grade' ?>
                                    </label>
                                    <input type="text" name="nome_grade" class="form-control" value="<?= htmlspecialchars($nomeGradeAtual) ?>" required placeholder="Ex: Horário de Verão">
                                </div>
                                
                                <?php if (empty($idDisponibilidade)): ?>
                                    <div style="padding-bottom: 10px; display: flex; align-items: center; gap: 8px;">
                                        <input type="checkbox" name="is_ativa" id="is_ativa" value="1" checked style="transform: scale(1.3);">
                                        <label for="is_ativa" style="font-weight: bold; color: var(--text-main); cursor: pointer;">Ativar como regra principal agora</label>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <div class="dias-grid">
                                <?php foreach($diasSemana as $sigla => $rotulo): 
                                    $existe = isset($dadosDias[$sigla]);
                                    $d = $existe ? $dadosDias[$sigla] : ['inicio'=>'', 'fim'=>'', 'int_inicio'=>'', 'int_fim'=>'', 'status'=>'disponivel'];
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
                                            <input type="time" id="int_ini_<?= $sigla ?>" name="dias[<?= $sigla ?>][intervalo_inicio]" value="<?= $d['int_inicio'] ?>" class="form-control">
                                            <span style="font-size: 0.85rem; color: var(--text-muted); font-weight: bold;">às</span>
                                            <input type="time" id="int_fim_<?= $sigla ?>" name="dias[<?= $sigla ?>][intervalo_fim]" value="<?= $d['int_fim'] ?>" class="form-control">
                                            
                                            <button type="button" class="btn-limpar-pausa" onclick="limparPausa('<?= $sigla ?>')" title="Remover horário de pausa">Limpar pausa</button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>

                            <div style="display: flex; gap: 1rem; margin-top: 2rem; flex-wrap: wrap;">
                                <button type="submit" class="btn-primary" style="max-width: 300px;">
                                    <?= empty($idDisponibilidade) ? 'Salvar Nova Grade' : 'Salvar Alterações da Grade' ?>
                                </button>
                                
                                <button type="button" class="btn-secondary" style="max-width: 200px;" onclick="cancelarEdicao()">
                                    Cancelar
                                </button>
                            </div>
                        </form>
                    </div>

                    <?php if (!empty($idDisponibilidade)): ?>
                    <form id="form-excluir" action="<?= BASE_URL ?>/funcionario/disponibilidade/excluir" method="POST" style="display: none;">
                        <input type="hidden" name="id_disponibilidade" value="<?= $idDisponibilidade ?>">
                    </form>
                    
                    <form id="form-cancelar" action="<?= BASE_URL ?>/funcionario/disponibilidade/selecionar" method="POST" style="display: none;">
                        <input type="hidden" name="grade_selecionada" value="">
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
        function abrirEdicao() {
            document.getElementById('box-botao-editar').style.display = 'none';
            const area = document.getElementById('area-edicao');
            area.style.display = 'block';
            setTimeout(() => { area.style.opacity = '1'; }, 10);
        }

        function cancelarEdicao() {
            const isNovaGrade = <?= $isNovaGrade ? 'true' : 'false' ?>;
            if (isNovaGrade) {
                // Se estava a criar, o cancelar reseta a sessão enviando um POST vazio
                document.getElementById('form-cancelar').submit();
                return;
            }

            const area = document.getElementById('area-edicao');
            area.style.opacity = '0';
            
            setTimeout(() => { 
                area.style.display = 'none'; 
                document.getElementById('box-botao-editar').style.display = 'block';
            }, 400); 
        }

        function toggleDayRow(sigla) {
            const checkbox = document.getElementById('dia_' + sigla);
            const row = document.getElementById('row_' + sigla);
            if (checkbox.checked) {
                row.style.opacity = '1';
                row.style.filter = 'none';
            } else {
                row.style.opacity = '0.5';
                row.style.filter = 'grayscale(100%)';
            }
        }

        function limparPausa(sigla) {
            document.getElementById('int_ini_' + sigla).value = '';
            document.getElementById('int_fim_' + sigla).value = '';
        }
    </script>

    <script>
        // =====================================================================
        // ARQUITETURA UX: PRESERVAÇÃO DE ESTADO DE SCROLL
        // Evita que a página "pule" para o topo ao trocar de grade no dropdown
        // =====================================================================

        // 1. Escutador de evento "beforeunload": Dispara um milissegundo antes da página ser recarregada/fechada
        window.addEventListener("beforeunload", function () {
            // Guardamos a posição exata (em pixels) do scroll vertical na memória temporária do navegador
            sessionStorage.setItem("scrollPosition", window.scrollY);
        });

        // 2. Escutador de evento "load": Dispara assim que a página termina de carregar o HTML e CSS
        window.addEventListener("load", function () {
            // Verificamos se existe alguma posição guardada da navegação anterior
            const scrollPos = sessionStorage.getItem("scrollPosition");
            
            if (scrollPos !== null) {
                // Se existir, forçamos o navegador a rolar a página para a posição anotada
                window.scrollTo(0, parseInt(scrollPos));
                
                // Limpamos a memória para evitar que a página trave nessa posição se o utilizador clicar num link normal
                sessionStorage.removeItem("scrollPosition"); 
            }
        });

        // (O resto das suas funções abrirEdicao(), cancelarEdicao(), etc., continuam aqui para baixo...)
    </script>
</body>
</html>