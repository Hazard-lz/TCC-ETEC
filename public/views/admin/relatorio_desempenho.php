<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Relatório de Desempenho - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>

    <style>
        .filtro-form {
            display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;
            background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 2rem;
        }
        .filtro-form .form-group { flex: 1; min-width: 180px; }
        .filtro-form label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem; }
        .filtro-form select, .filtro-form input[type="date"] {
            width: 100%; padding: 0.6rem 0.8rem; border: 1px solid var(--border-color);
            border-radius: var(--radius-md); background: var(--bg-color); color: var(--text-main);
            font-size: 0.95rem; transition: border-color 0.2s;
        }
        .filtro-form select:focus, .filtro-form input[type="date"]:focus { border-color: var(--color-purple); outline: none; }
        .filtro-form .btn-gerar {
            padding: 0.6rem 1.5rem; background: var(--gradient-brand); color: white; border: none;
            border-radius: var(--radius-md); font-weight: 600; cursor: pointer; font-size: 0.95rem;
            transition: transform 0.15s, box-shadow 0.15s; white-space: nowrap;
        }
        .filtro-form .btn-gerar:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(139,92,246,0.4); }

        .metricas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
        .metrica-card {
            background: var(--surface-color); border-radius: var(--radius-lg); padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); position: relative; overflow: hidden;
        }
        .metrica-card::before {
            content: ''; position: absolute; top: 0; left: 0; width: 4px; height: 100%;
            border-radius: 4px 0 0 4px;
        }
        .metrica-card:nth-child(1)::before { background: var(--color-purple); }
        .metrica-card:nth-child(2)::before { background: #2ecc71; }
        .metrica-card:nth-child(3)::before { background: #3498db; }
        .metrica-card:nth-child(4)::before { background: var(--color-pink); }
        .metrica-card .metrica-titulo { font-size: 0.8rem; font-weight: 600; color: var(--text-muted); text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 0.5rem; }
        .metrica-card .metrica-valor { font-size: 2rem; font-weight: 800; color: var(--text-main); line-height: 1.1; }
        .metrica-card .metrica-sub { font-size: 0.8rem; color: var(--text-muted); margin-top: 0.3rem; }

        .secao-relatorio { margin-bottom: 2rem; }
        .secao-relatorio h3 { color: var(--text-main); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
        .secao-relatorio h3 i { color: var(--color-purple); }

        .barra-visual { display: flex; align-items: center; gap: 0.8rem; }
        .barra-visual .barra {
            height: 8px; border-radius: 4px; background: var(--gradient-brand);
            transition: width 0.5s ease; min-width: 4px;
        }
        .barra-visual .barra-label { font-size: 0.85rem; color: var(--text-muted); font-weight: 600; white-space: nowrap; }

        .badge-frequencia {
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #a78bfa, var(--color-purple));
            color: white; font-weight: 700; font-size: 0.85rem;
            width: 32px; height: 32px; border-radius: 50%;
        }

        .btn-exportar {
            padding: 0.6rem 1.5rem; background: #2ecc71; color: white; border: none;
            border-radius: var(--radius-md); font-weight: 600; cursor: pointer; font-size: 0.95rem;
            transition: transform 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; gap: 0.5rem;
        }
        .btn-exportar:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(46,204,113,0.4); }

        .estado-vazio { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
        .estado-vazio .icone { font-size: 3rem; margin-bottom: 1rem; }
        .estado-vazio p { font-size: 1.1rem; }

        /* PDF: esconder botões e ajustar cores */
        @media print {
            .filtro-form, .btn-exportar, .sidebar, .topbar, .sidebar-overlay { display: none !important; }
            .main-wrapper { margin-left: 0 !important; }
            body { background: white !important; }
            .metrica-card, .base-card { box-shadow: none !important; border: 1px solid #e2e8f0; }
        }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header">
        <div class="page-title">
            <h2>Relatório de Desempenho</h2>
            <p>Analise o desempenho individual de cada profissional do salão.</p>
        </div>
    </div>

    <!-- FILTROS -->
    <form class="filtro-form" method="GET" action="<?= BASE_URL ?>/admin/relatorios/desempenho">
        <div class="form-group">
            <label>Funcionário</label>
            <select name="id_funcionario" required>
                <option value="">Selecione...</option>
                <?php foreach ($listaFuncionarios as $func): ?>
                    <option value="<?= $func['id_funcionario'] ?>" <?= ($idFuncionario == $func['id_funcionario']) ? 'selected' : '' ?>>
                        <?= htmlspecialchars($func['nome']) ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="form-group">
            <label>Data Início</label>
            <input type="date" name="data_inicio" value="<?= htmlspecialchars($dataInicio) ?>" required>
        </div>
        <div class="form-group">
            <label>Data Fim</label>
            <input type="date" name="data_fim" value="<?= htmlspecialchars($dataFim) ?>" required>
        </div>
        <button type="submit" class="btn-gerar">📊 Gerar Relatório</button>

        <?php if ($metricas): ?>
            <button type="button" class="btn-exportar" onclick="exportarPDF()">
                <i class="bi bi-file-earmark-pdf"></i> Exportar PDF
            </button>
        <?php endif; ?>
    </form>

    <!-- CONTEÚDO DO RELATÓRIO -->
    <div id="area-relatorio">

        <?php if ($metricas): ?>

            <?php if ($funcionarioSelecionado): ?>
                <div style="margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: var(--surface-color); border-radius: var(--radius-lg); box-shadow: 0 2px 10px rgba(0,0,0,0.04); display: flex; align-items: center; gap: 1rem;">
                    <div style="width: 48px; height: 48px; border-radius: 50%; background: var(--gradient-brand); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.3rem;">
                        <?= strtoupper(substr($funcionarioSelecionado['nome'], 0, 1)) ?>
                    </div>
                    <div>
                        <h3 style="margin: 0; color: var(--text-main); font-size: 1.2rem;"><?= htmlspecialchars($funcionarioSelecionado['nome']) ?></h3>
                        <p style="margin: 0; color: var(--text-muted); font-size: 0.85rem;">
                            <?= htmlspecialchars($funcionarioSelecionado['especialidade'] ?? 'Profissional') ?> •
                            <?= date('d/m/Y', strtotime($dataInicio)) ?> a <?= date('d/m/Y', strtotime($dataFim)) ?>
                        </p>
                    </div>
                </div>
            <?php endif; ?>

            <!-- CARDS DE MÉTRICAS -->
            <div class="metricas-grid">
                <div class="metrica-card">
                    <div class="metrica-titulo">Atendimentos Concluídos</div>
                    <div class="metrica-valor"><?= $totalConcluidos ?></div>
                    <div class="metrica-sub">no período selecionado</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-titulo">Faturamento Bruto</div>
                    <div class="metrica-valor">R$ <?= number_format($faturamentoBruto, 2, ',', '.') ?></div>
                    <div class="metrica-sub">total em serviços concluídos</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-titulo">Ticket Médio</div>
                    <div class="metrica-valor">R$ <?= number_format($ticketMedio, 2, ',', '.') ?></div>
                    <div class="metrica-sub">valor médio por atendimento</div>
                </div>
                <div class="metrica-card">
                    <div class="metrica-titulo">Taxa de Cancelamento</div>
                    <div class="metrica-valor"><?= number_format($taxaCancelamento, 1, ',', '.') ?>%</div>
                    <div class="metrica-sub"><?= $totalCancelados ?> cancelado(s) de <?= $totalGeral ?> total</div>
                </div>
            </div>

            <!-- RANKING DE SERVIÇOS -->
            <div class="secao-relatorio">
                <h3><i class="bi bi-bar-chart-fill"></i> Ranking de Serviços</h3>
                <div class="base-card">
                    <?php if (!empty($rankingServicos)): ?>
                        <?php $maxQtd = $rankingServicos[0]['quantidade']; ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th style="width: 40px;">#</th>
                                    <th>Serviço</th>
                                    <th>Quantidade</th>
                                    <th style="width: 40%;">Proporção</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($rankingServicos as $i => $srv): ?>
                                    <?php $pct = ($maxQtd > 0) ? ($srv['quantidade'] / $maxQtd) * 100 : 0; ?>
                                    <tr>
                                        <td style="font-weight: 700; color: var(--color-purple);"><?= $i + 1 ?></td>
                                        <td style="font-weight: 500;"><?= htmlspecialchars($srv['nome_servico']) ?></td>
                                        <td style="font-weight: 700;"><?= $srv['quantidade'] ?>x</td>
                                        <td>
                                            <div class="barra-visual">
                                                <div class="barra" style="width: <?= $pct ?>%;"></div>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum serviço concluído no período.</p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- RETENÇÃO DE CLIENTES -->
            <div class="secao-relatorio">
                <h3><i class="bi bi-people-fill"></i> Retenção e Fidelização de Clientes</h3>
                <div class="base-card">
                    <?php if (!empty($retencaoClientes)): ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Cliente</th>
                                    <th style="width: 120px; text-align: center;">Frequência</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($retencaoClientes as $cli): ?>
                                    <tr>
                                        <td style="font-weight: 500;"><?= htmlspecialchars($cli['cliente_nome']) ?></td>
                                        <td style="text-align: center;">
                                            <span class="badge-frequencia"><?= $cli['frequencia'] ?></span>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php else: ?>
                        <p style="text-align: center; padding: 2rem; color: var(--text-muted);">Nenhum cliente atendido no período.</p>
                    <?php endif; ?>
                </div>
            </div>

        <?php else: ?>
            <div class="estado-vazio">
                <div class="icone">📊</div>
                <p>Selecione um funcionário e o período para gerar o relatório.</p>
            </div>
        <?php endif; ?>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>

    <!-- html2pdf.js para exportação PDF -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2pdf.js/0.10.1/html2pdf.bundle.min.js"></script>
    <script>
        function exportarPDF() {
            const area = document.getElementById('area-relatorio');
            const nomeFunc = document.querySelector('.page-title h2')?.textContent || 'Relatorio';
            
            const opt = {
                margin:       [10, 10, 10, 10],
                filename:     'relatorio_desempenho.pdf',
                image:        { type: 'jpeg', quality: 0.98 },
                html2canvas:  { scale: 2, useCORS: true },
                jsPDF:        { unit: 'mm', format: 'a4', orientation: 'portrait' },
                pagebreak:    { mode: ['avoid-all', 'css', 'legacy'] }
            };

            // Temporariamente força cores claras para o PDF ficar legível
            const html = document.documentElement;
            const temaAtual = html.getAttribute('data-theme');
            html.removeAttribute('data-theme');

            html2pdf().set(opt).from(area).save().then(() => {
                if (temaAtual) html.setAttribute('data-theme', temaAtual);
            });
        }
    </script>
</body>
</html>
