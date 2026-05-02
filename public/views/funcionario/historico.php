<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Histórico de Agendamentos - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/historico.css">
    
    <style>
        .history-grid {
            display: grid;
            gap: 1.5rem;
            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));
            margin-top: 1.5rem;
        }

        .tab-content {
            display: none;
        }

        .tab-content.active {
            display: grid;
        }

        .tabs-container {
            display: flex;
            gap: 1rem;
            margin-bottom: 1.5rem;
            border-bottom: 1px solid var(--border-color);
            padding-bottom: 0.5rem;
        }

        .tab-btn {
            background: transparent !important;
            border: none;
            padding: 0.5rem 1rem;
            font-weight: 600;
            color: var(--text-muted);
            cursor: pointer;
            position: relative;
        }

        .tab-btn.aba-active {
            color: var(--color-purple) !important;
        }

        .tab-btn.aba-active::after {
            content: '';
            position: absolute;
            bottom: -0.6rem;
            left: 0;
            right: 0;
            height: 3px;
            background-color: var(--color-purple);
            border-radius: 3px 3px 0 0;
        }

        .filter-section {
            background: var(--surface-color);
            padding: 1.5rem;
            border-radius: var(--radius-lg);
            margin-bottom: 1.5rem;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.05);
        }
    </style>
    
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div style="padding: 1.5rem;">
        
        <h2 style="color: var(--text-main); margin-bottom: 1.5rem;">Histórico de Agendamentos</h2>

        <?php if ($isAdmin): ?>
        <div class="filter-section" style="max-width: 600px;">
            <form action="<?= BASE_URL ?>/funcionario/historico" method="GET" style="display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;">
                <div style="flex: 1; min-width: 250px;">
                    <label for="id_funcionario" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: var(--text-main);">Selecione o Funcionário:</label>
                    <select name="id_funcionario" id="id_funcionario" class="form-control" onchange="this.form.submit()" style="width: 100%;">
                        <option value="">-- Todos --</option>
                        <?php foreach($funcionarios as $func): ?>
                            <option value="<?= $func['id_funcionario'] ?>" <?= ($funcionarioIdParaBuscar == $func['id_funcionario']) ? 'selected' : '' ?>>
                                <?= htmlspecialchars($func['nome']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <button type="submit" class="btn-primary" style="height: 48px; padding: 0 2rem; flex-shrink: 0;">Filtrar</button>
            </form>
        </div>
        <?php endif; ?>

        <div class="tabs-container">
            <button class="tab-btn aba-active" onclick="mudarAba('proximos', this)">Próximos</button>
            <button class="tab-btn" onclick="mudarAba('anteriores', this)">Histórico Passado</button>
        </div>

        <?php 
        function getBadgeCss($status) {
            $map = [
                'pendente' => ['card' => 'status-pendente', 'badge' => 'badge-orange', 'label' => 'Pendente'],
                'marcado'  => ['card' => 'status-marcado', 'badge' => 'badge-purple', 'label' => 'Marcado'],
                'concluido'=> ['card' => 'status-concluido', 'badge' => 'badge-green', 'label' => 'Concluído'],
                'cancelado'=> ['card' => 'status-cancelado', 'badge' => 'badge-pink', 'label' => 'Cancelado']
            ];
            return $map[$status] ?? ['card' => '', 'badge' => '', 'label' => ucfirst($status)];
        }
        ?>

        <div id="aba-proximos" class="tab-content active">
            <?php if (!empty($proximos)): ?>
                <?php foreach ($proximos as $ag): 
                    $estilo = getBadgeCss($ag['status']);
                ?>
                    <div class="history-card <?= $estilo['card'] ?>" style="background: var(--surface-color); border-radius: var(--radius-lg); padding: 1.2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid transparent;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--text-main);">📅 <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                            <span class="history-badge <?= $estilo['badge'] ?>" style="padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold;"><?= $estilo['label'] ?></span>
                        </div>
                        <div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.3rem;"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="bi bi-person me-1"></i> Cliente: <?= htmlspecialchars($ag['cliente_nome']) ?> (<?= htmlspecialchars($ag['cliente_telefone'] ?? 'Sem telefone') ?>)</div>
                            <?php if ($isAdmin): ?>
                                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="bi bi-person-badge me-1"></i> Funcionário: <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                            <?php endif; ?>
                            <div style="font-weight: bold; color: var(--color-purple); margin-top: 1rem;">R$ <?= $ag['preco_formatado'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem; background: var(--surface-color); border-radius: var(--radius-lg);">
                    <i class="bi bi-calendar-x" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                    Nenhum agendamento futuro encontrado.
                </div>
            <?php endif; ?>
        </div> 
        
        <div id="aba-anteriores" class="tab-content">
            <?php if (!empty($anteriores)): ?>
                <?php foreach ($anteriores as $ag): 
                    $estilo = getBadgeCss($ag['status']);
                ?>
                    <div class="history-card <?= $estilo['card'] ?>" style="background: var(--surface-color); border-radius: var(--radius-lg); padding: 1.2rem; box-shadow: 0 4px 6px rgba(0,0,0,0.05); border-left: 4px solid transparent;">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem;">
                            <span style="font-weight: 600; color: var(--text-main);">📅 <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                            <span class="history-badge <?= $estilo['badge'] ?>" style="padding: 0.3rem 0.6rem; border-radius: 20px; font-size: 0.8rem; font-weight: bold;"><?= $estilo['label'] ?></span>
                        </div>
                        <div>
                            <div style="font-size: 1.1rem; font-weight: 700; color: var(--text-main); margin-bottom: 0.3rem;"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                            <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="bi bi-person me-1"></i> Cliente: <?= htmlspecialchars($ag['cliente_nome']) ?> (<?= htmlspecialchars($ag['cliente_telefone'] ?? 'Sem telefone') ?>)</div>
                            <?php if ($isAdmin): ?>
                                <div style="color: var(--text-muted); font-size: 0.9rem; margin-bottom: 0.5rem;"><i class="bi bi-person-badge me-1"></i> Funcionário: <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                            <?php endif; ?>
                            <div style="font-weight: bold; color: var(--color-purple); margin-top: 1rem;">R$ <?= $ag['preco_formatado'] ?></div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php else: ?>
                <div style="grid-column: 1 / -1; text-align: center; color: var(--text-muted); padding: 3rem; background: var(--surface-color); border-radius: var(--radius-lg);">
                    <i class="bi bi-clock-history" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 1rem; display: block;"></i>
                    Nenhum histórico passado encontrado.
                </div>
            <?php endif; ?>
        </div> 

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script>
        function mudarAba(abaId, btnElement) {
            // Remove aba-active de todos os botoes
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('aba-active'));
            // Adiciona no clicado
            btnElement.classList.add('aba-active');

            // Esconde todas as abas
            document.querySelectorAll('.tab-content').forEach(aba => aba.classList.remove('active'));
            // Mostra a aba correspondente
            document.getElementById('aba-' + abaId).classList.add('active');
        }
    </script>
</body>
</html>
