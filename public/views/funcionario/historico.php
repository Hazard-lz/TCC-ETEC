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
    
    <!-- Flatpickr (Calendário Estilizado) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    
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

    <?php 
    $mostrarAnterioresAtivo = (!empty($_GET['data_inicio']) || !empty($_GET['data_fim']) || ($_GET['tab'] ?? '') === 'anteriores');
    ?>

    <div class="tabs-container">
        <button class="tab-btn <?= !$mostrarAnterioresAtivo ? 'active' : '' ?>" onclick="mudarAba('proximos', this)">
            <i class="bi bi-calendar-check"></i> Próximos
            <?php if (!empty($proximos)): ?>
                <span class="tab-count"><?= count($proximos) ?></span>
            <?php endif; ?>
        </button>
        <button class="tab-btn <?= $mostrarAnterioresAtivo ? 'active' : '' ?>" onclick="mudarAba('anteriores', this)">
            <i class="bi bi-clock-history"></i> Histórico Passado
        </button>
    </div>

    <?php 
    function getBadgeCss($status) {
        $map = [
            'pendente' => ['card' => 'status-pendente', 'badge' => 'badge-orange', 'label' => 'Pendente', 'icon' => '<i class="bi bi-hourglass-split"></i>'],
            'marcado'  => ['card' => 'status-marcado', 'badge' => 'badge-purple', 'label' => 'Marcado', 'icon' => '<i class="bi bi-calendar-event"></i>'],
            'concluido'=> ['card' => 'status-concluido', 'badge' => 'badge-green', 'label' => 'Concluído', 'icon' => '<i class="bi bi-check-circle-fill"></i>'],
            'cancelado'=> ['card' => 'status-cancelado', 'badge' => 'badge-pink', 'label' => 'Cancelado', 'icon' => '<i class="bi bi-x-circle-fill"></i>']
        ];
        return $map[$status] ?? ['card' => '', 'badge' => '', 'label' => ucfirst($status), 'icon' => '<i class="bi bi-clipboard-data"></i>'];
    }
    ?>

    <div id="aba-proximos" class="tab-content <?= !$mostrarAnterioresAtivo ? 'active' : '' ?> history-grid">
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
    
    <div id="aba-anteriores" class="tab-content <?= $mostrarAnterioresAtivo ? 'active' : '' ?> history-grid">
        <!-- Filtro de Data para Histórico Passado -->
        <div class="filter-section" style="margin-bottom: 1.5rem; max-width: 100%; grid-column: 1 / -1; width: 100%;">
            <form action="<?= BASE_URL ?>/funcionario/historico" method="GET" class="filter-form" style="display: flex; gap: 1rem; align-items: flex-end; flex-wrap: wrap; justify-content: flex-start;">
                <?php if (isset($_GET['id_funcionario'])): ?>
                    <input type="hidden" name="id_funcionario" value="<?= htmlspecialchars($_GET['id_funcionario']) ?>">
                <?php endif; ?>
                <div class="filter-field" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="data_inicio" style="font-weight: 600; font-size: 0.85rem;">De:</label>
                    <input type="date" name="data_inicio" id="data_inicio" class="form-control" value="<?= htmlspecialchars($_GET['data_inicio'] ?? '') ?>" placeholder="Selecione uma data" style="height: 38px;">
                </div>
                <div class="filter-field" style="flex: 1; min-width: 150px; margin-bottom: 0;">
                    <label for="data_fim" style="font-weight: 600; font-size: 0.85rem;">Até:</label>
                    <input type="date" name="data_fim" id="data_fim" class="form-control" value="<?= htmlspecialchars($_GET['data_fim'] ?? '') ?>" placeholder="Selecione uma data" style="height: 38px;">
                </div>
                <button type="submit" class="btn-primary filter-btn" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; gap: 0.4rem; padding: 0 1.25rem;"><i class="bi bi-filter"></i> Filtrar</button>
                <?php if (!empty($_GET['data_inicio']) || !empty($_GET['data_fim'])): ?>
                    <a href="<?= BASE_URL ?>/funcionario/historico?tab=anteriores<?= isset($_GET['id_funcionario']) ? '&id_funcionario=' . htmlspecialchars($_GET['id_funcionario']) : '' ?>" class="btn-secondary" style="height: 38px; display: inline-flex; align-items: center; justify-content: center; text-decoration: none; padding: 0 1rem; border-radius: var(--radius-md); font-weight: 600; font-size: 0.9rem;">Limpar</a>
                <?php endif; ?>
            </form>
        </div>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1rem; width: 100%; grid-column: 1 / -1;">
            <h4 style="margin: 0; color: var(--text-main); font-size: 1.1rem;">Total de Agendamentos Passados: <span style="font-weight: 700; color: var(--color-purple);"><?= count($anteriores) ?></span></h4>
        </div>

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
            document.querySelectorAll('.tab-btn').forEach(btn => btn.classList.remove('active'));
            btnElement.classList.add('active');
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

            flatpickr("#data_inicio", {
                locale: "pt",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                altInputClass: "form-control flatpickr-alt-input",
                disableMobile: true
            });
            flatpickr("#data_fim", {
                locale: "pt",
                dateFormat: "Y-m-d",
                altInput: true,
                altFormat: "d/m/Y",
                altInputClass: "form-control flatpickr-alt-input",
                disableMobile: true
            });
        });
    </script>
</body>
</html>
