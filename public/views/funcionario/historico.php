<?php
$tipoUsuario = $_SESSION['usuario_tipo'] ?? '';
$isAdmin = ($tipoUsuario === 'admin');
$isGerencia = in_array($tipoUsuario, ['admin', 'subadmin']);
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
    
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header">
        <div class="page-title">
            <h2>Histórico de Agendamentos</h2>
            <p>Consulte o registro completo de todos os atendimentos.</p>
        </div>
    </div>

    <?php if ($isGerencia): ?>
    <div class="filter-section">
        <form action="<?= BASE_URL ?>/funcionario/historico" method="GET" class="filter-form">
            <div class="filter-field">
                <label for="id_funcionario">Selecione o Funcionário:</label>
                <select name="id_funcionario" id="id_funcionario" class="form-control form-select" onchange="this.form.submit()">
                    <?php foreach($funcionarios as $func): ?>
                        <option value="<?= $func['id_funcionario'] ?>" <?= ($funcionarioIdParaBuscar == $func['id_funcionario']) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($func['nome']) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <button type="submit" class="btn-primary filter-btn">Filtrar</button>
        </form>
    </div>
    <?php endif; ?>

    <div class="tabs-container">
        <button class="tab-btn aba-active" onclick="mudarAba('proximos', this)">
            <i class="bi bi-calendar-check"></i> Próximos
            <?php if (!empty($proximos)): ?>
                <span class="tab-count"><?= count($proximos) ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn" onclick="mudarAba('anteriores', this)">
            <i class="bi bi-clock-history"></i> Histórico Passado
            <?php if (!empty($anteriores)): ?>
                <span class="tab-count"><?= count($anteriores) ?></span>
            <?php endif; ?>
        </button>
    </div>

    <?php 
    function getBadgeCss($status) {
        $map = [
            'pendente' => ['card' => 'status-pendente', 'badge' => 'badge-orange', 'label' => 'Pendente', 'icon' => '⏳'],
            'marcado'  => ['card' => 'status-marcado', 'badge' => 'badge-purple', 'label' => 'Marcado', 'icon' => '📅'],
            'concluido'=> ['card' => 'status-concluido', 'badge' => 'badge-green', 'label' => 'Concluído', 'icon' => '✅'],
            'cancelado'=> ['card' => 'status-cancelado', 'badge' => 'badge-pink', 'label' => 'Cancelado', 'icon' => '❌']
        ];
        return $map[$status] ?? ['card' => '', 'badge' => '', 'label' => ucfirst($status), 'icon' => '📋'];
    }
    ?>

    <div id="aba-proximos" class="tab-content active history-grid">
        <?php if (!empty($proximos)): ?>
            <?php foreach ($proximos as $ag): 
                $estilo = getBadgeCss($ag['status']);
            ?>
                <div class="history-card <?= $estilo['card'] ?>">
                    <div class="history-header">
                        <span class="history-date"><?= $estilo['icon'] ?> <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                        <span class="history-badge <?= $estilo['badge'] ?>"><?= $estilo['label'] ?></span>
                    </div>
                    <div class="history-body">
                        <div>
                            <div class="history-service"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                            <div class="history-pro"><i class="bi bi-person"></i> Cliente: <?= htmlspecialchars($ag['cliente_nome']) ?> (<?= htmlspecialchars($ag['cliente_telefone'] ?? 'Sem telefone') ?>)</div>
                            <?php if ($isGerencia): ?>
                                <div class="history-pro"><i class="bi bi-person-badge"></i> Funcionário: <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="history-price">R$ <?= $ag['preco_formatado'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-calendar2-x"></i>
                <p>Nenhum agendamento futuro encontrado.</p>
            </div>
        <?php endif; ?>
        <div class="pagination-controls" id="pagination-proximos" style="display: none; justify-content: center; gap: 1rem; margin-top: 2rem; grid-column: 1 / -1; width: 100%;">
            <button class="btn-secondary" onclick="mudarPagina('proximos', -1)">Anterior</button>
            <span style="display: flex; align-items: center; color: var(--text-muted);" id="page-info-proximos">Página 1</span>
            <button class="btn-secondary" onclick="mudarPagina('proximos', 1)">Próxima</button>
        </div>
    </div> 
    
    <div id="aba-anteriores" class="tab-content history-grid">
        <?php if (!empty($anteriores)): ?>
            <?php foreach ($anteriores as $ag): 
                $estilo = getBadgeCss($ag['status']);
            ?>
                <div class="history-card <?= $estilo['card'] ?>">
                    <div class="history-header">
                        <span class="history-date"><?= $estilo['icon'] ?> <?= $ag['data_formatada'] ?> às <?= $ag['hora_formatada'] ?></span>
                        <span class="history-badge <?= $estilo['badge'] ?>"><?= $estilo['label'] ?></span>
                    </div>
                    <div class="history-body">
                        <div>
                            <div class="history-service"><?= htmlspecialchars($ag['nome_servico']) ?></div>
                            <div class="history-pro"><i class="bi bi-person"></i> Cliente: <?= htmlspecialchars($ag['cliente_nome']) ?> (<?= htmlspecialchars($ag['cliente_telefone'] ?? 'Sem telefone') ?>)</div>
                            <?php if ($isGerencia): ?>
                                <div class="history-pro"><i class="bi bi-person-badge"></i> Funcionário: <?= htmlspecialchars($ag['funcionario_nome']) ?></div>
                            <?php endif; ?>
                        </div>
                        <div class="history-price">R$ <?= $ag['preco_formatado'] ?></div>
                    </div>
                </div>
            <?php endforeach; ?>
        <?php else: ?>
            <div class="empty-state">
                <i class="bi bi-clock-history"></i>
                <p>Nenhum histórico passado encontrado.</p>
            </div>
        <?php endif; ?>
        <div class="pagination-controls" id="pagination-anteriores" style="display: none; justify-content: center; gap: 1rem; margin-top: 2rem; grid-column: 1 / -1; width: 100%;">
            <button class="btn-secondary" onclick="mudarPagina('anteriores', -1)">Anterior</button>
            <span style="display: flex; align-items: center; color: var(--text-muted);" id="page-info-anteriores">Página 1</span>
            <button class="btn-secondary" onclick="mudarPagina('anteriores', 1)">Próxima</button>
        </div>
    </div> 

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script>
        const itensPorPagina = 20;
        let paginas = { proximos: 1, anteriores: 1 };

        function mudarAba(abaId, btnElement) {
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('aba-active'));
            btnElement.classList.add('aba-active');
            document.querySelectorAll('.tab-content').forEach(aba => aba.classList.remove('active'));
            document.getElementById('aba-' + abaId).classList.add('active');
            
            // Re-renderiza a página correta da aba clicada
            renderizarPagina(abaId);
        }

        function renderizarPagina(abaId) {
            const container = document.getElementById('aba-' + abaId);
            const cards = container.querySelectorAll('.history-card');
            if (cards.length === 0) return;

            const paginaAtual = paginas[abaId];
            const inicio = (paginaAtual - 1) * itensPorPagina;
            const fim = inicio + itensPorPagina;
            const totalPaginas = Math.ceil(cards.length / itensPorPagina);

            cards.forEach((card, index) => {
                if (index >= inicio && index < fim) {
                    card.style.display = 'flex'; // ou 'block' dependendo do seu CSS
                } else {
                    card.style.display = 'none';
                }
            });

            // Controle dos botões
            const paginationDiv = document.getElementById('pagination-' + abaId);
            if (totalPaginas > 1) {
                paginationDiv.style.display = 'flex';
                document.getElementById('page-info-' + abaId).textContent = `Página ${paginaAtual} de ${totalPaginas}`;
                
                const btns = paginationDiv.querySelectorAll('button');
                btns[0].disabled = (paginaAtual === 1);
                btns[0].style.opacity = (paginaAtual === 1) ? '0.5' : '1';
                
                btns[1].disabled = (paginaAtual === totalPaginas);
                btns[1].style.opacity = (paginaAtual === totalPaginas) ? '0.5' : '1';
            } else {
                paginationDiv.style.display = 'none';
            }
        }

        function mudarPagina(abaId, direcao) {
            paginas[abaId] += direcao;
            renderizarPagina(abaId);
            window.scrollTo({ top: 0, behavior: 'smooth' });
        }

        // Inicializa a paginação na primeira carga
        document.addEventListener('DOMContentLoaded', () => {
            renderizarPagina('proximos');
            renderizarPagina('anteriores');
        });
    </script>
</body>
</html>
