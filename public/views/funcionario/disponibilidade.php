<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minha Disponibilidade - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/disponibilidade.css">
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
                        <p style="color: var(--text-muted);">Configure os seus dias e horários de atendimento.</p>
                    </div>
                </div>

                <div class="base-card" style="max-width: 700px; padding: 2rem;">
                    <form action="<?= BASE_URL ?>/funcionario/disponibilidade/salvar" method="POST">
                        
                        <h3 class="section-title" style="margin-bottom: 1rem; color: var(--text-main);">Dias de Trabalho</h3>
                        
                        <div class="days-container">
                            <?php 
                            $dias = [
                                'Seg' => 'Segunda', 
                                'Ter' => 'Terça', 
                                'Qua' => 'Quarta', 
                                'Qui' => 'Quinta', 
                                'Sex' => 'Sexta', 
                                'Sab' => 'Sábado', 
                                'Dom' => 'Domingo'
                            ];
                            foreach($dias as $valor => $rotulo): ?>
                                <div>
                                    <input type="checkbox" name="dias_semana[]" value="<?= $valor ?>" id="dia_<?= $valor ?>" class="day-checkbox">
                                    <label for="dia_<?= $valor ?>" class="day-label"><?= $rotulo ?></label>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <h3 class="section-title" style="margin-bottom: 1rem; margin-top: 2rem; color: var(--text-main);">Horário de Expediente</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="hora_inicio">Início do Turno</label>
                                <input type="time" name="hora_inicio" id="hora_inicio" class="form-control" required>
                            </div>
                            <div class="form-group">
                                <label for="hora_fim">Término do Turno</label>
                                <input type="time" name="hora_fim" id="hora_fim" class="form-control" required>
                            </div>
                        </div>

                        <h3 class="section-title" style="margin-bottom: 1rem; margin-top: 1rem; color: var(--text-main);">Horário de Intervalo (Opcional)</h3>
                        <div class="form-grid">
                            <div class="form-group">
                                <label for="intervalo_inicio">Saída para o Intervalo</label>
                                <input type="time" name="intervalo_inicio" id="intervalo_inicio" class="form-control">
                            </div>
                            <div class="form-group">
                                <label for="intervalo_fim">Retorno do Intervalo</label>
                                <input type="time" name="intervalo_fim" id="intervalo_fim" class="form-control">
                            </div>
                        </div>

                        <button type="submit" class="btn-primary" style="max-width: 300px;">Salvar Disponibilidade</button>
                    </form>
                </div>
            </section>
        </main>
    </div>
    
    <button id="themeToggle" class="btn-theme-toggle" title="Alternar Tema Escuro/Claro">🌓</button>

    <script src="<?= BASE_URL ?>/public/resources/js/admin-layout.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>