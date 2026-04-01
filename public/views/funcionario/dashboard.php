<?php
// Descobre se quem está logado é admin ou não
$isAdmin = ($_SESSION['usuario_tipo'] === 'admin');

// Pega apenas o primeiro nome para uma saudação amigável
$nomePrimeiro = explode(' ', $_SESSION['usuario_nome'])[0];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Painel Inicial - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/dashboard.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css"> 
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="dashboard-header" style="margin-bottom: 2rem;">
        <h3>Olá, <?= htmlspecialchars($nomePrimeiro) ?>! 👋</h3>
        <p><?= $isAdmin ? 'Acompanhe o desempenho geral do salão em tempo real.' : 'Aqui está o resumo do seu dia de trabalho.' ?></p>
    </div>

    <div class="summary-grid">
        <?php if ($isAdmin): ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅</div>
                <div class="card-info">
                    <h4>Geral Hoje</h4><span class="card-value">12</span><span class="card-label">agendamentos totais</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💰</div>
                <div class="card-info">
                    <h4>Faturamento</h4><span class="card-value">R$ 4.250</span><span class="card-label">neste mês</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(52, 152, 219, 0.1); color: #3498db;">👥</div>
                <div class="card-info">
                    <h4>Clientes</h4><span class="card-value">+8</span><span class="card-label">novos na semana</span>
                </div>
            </div>
        <?php else: ?>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(139, 92, 246, 0.1); color: var(--color-purple);">📅</div>
                <div class="card-info">
                    <h4>Meus Hoje</h4><span class="card-value">5</span><span class="card-label">clientes na agenda</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(244, 91, 105, 0.1); color: var(--color-pink);">⏳</div>
                <div class="card-info">
                    <h4>Pendentes</h4><span class="card-value">1</span><span class="card-label">aguardando ação</span>
                </div>
            </div>
            <div class="summary-card">
                <div class="card-icon" style="background-color: rgba(46, 204, 113, 0.1); color: #2ecc71;">💵</div>
                <div class="card-info">
                    <h4>Comissões</h4><span class="card-value">R$ 350</span><span class="card-label">estimativa do mês</span>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <div class="base-card mt-4">
        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
            <h3 style="margin: 0; color: var(--text-main);"><?= $isAdmin ? 'Todos os Próximos Atendimentos' : 'Minha Agenda (Próximos)' ?></h3>
            
            <?php if ($isAdmin): ?>
                <button class="btn-primary" style="width: auto; margin: 0; padding: 0.5rem 1rem;">+ Novo Agendamento</button>
            <?php endif; ?>
        </div>
        
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Horário</th>
                        <th>Cliente</th>
                        <th>Serviço</th>
                        <?php if ($isAdmin): ?><th>Profissional</th><?php endif; ?>
                        <th>Status</th>
                        <?php if ($isAdmin): ?><th>Ações</th><?php endif; ?>
                    </tr>
                </thead>
                <tbody>
                    <tr>
                        <td style="font-weight: bold; color: var(--color-purple);">14:30</td>
                        <td>Ana Pereira</td>
                        <td>Corte Feminino</td>
                        <?php if ($isAdmin): ?><td>Maria Oliveira</td><?php endif; ?>
                        <td><span class="badge badge-marcado">Marcado</span></td>
                        
                        <?php if ($isAdmin): ?>
                            <td>
                                <div class="action-buttons">
                                    <button class="btn-action btn-edit" title="Editar">✏️</button>
                                    <button class="btn-action btn-delete" title="Cancelar">🗑️</button>
                                </div>
                            </td>
                        <?php endif; ?>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
</body>
</html>