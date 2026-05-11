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

    <style>        .filtro-form {
            display: flex; flex-wrap: wrap; gap: 1rem; align-items: flex-end;
            background: var(--surface-color); padding: 1.5rem; border-radius: var(--radius-lg);
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 2rem;
        }
        .filtro-form .form-group { flex: 1; min-width: 200px; }
        .filtro-form label { display: block; font-size: 0.85rem; font-weight: 600; color: var(--text-muted); margin-bottom: 0.4rem; }
        .filtro-form select, .filtro-form input[type="date"] {
            width: 100%; padding: 0.7rem 0.8rem; border: 1px solid var(--border-color);
            border-radius: var(--radius-md); background: var(--bg-color); color: var(--text-main);
            font-size: 0.95rem; transition: border-color 0.2s;
        }
        .filtro-form select:focus, .filtro-form input[type="date"]:focus { border-color: var(--color-purple); outline: none; }
        
        .filtro-botoes-container {
            display: flex; gap: 0.8rem; flex-wrap: wrap; width: 100%; align-items: center;
        }

        .btn-gerar {
            padding: 0.7rem 1.5rem; background: var(--gradient-brand); color: white; border: none;
            border-radius: var(--radius-md); font-weight: 600; cursor: pointer; font-size: 0.95rem;
            transition: transform 0.15s, box-shadow 0.15s; white-space: nowrap;
        }
        .btn-gerar:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(139,92,246,0.4); }

        .metricas-grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(220px, 1fr)); gap: 1.2rem; margin-bottom: 2rem; }
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
        .metrica-card .metrica-valor { font-size: 1.8rem; font-weight: 800; color: var(--text-main); line-height: 1.1; }
        .metrica-card .metrica-sub { font-size: 0.75rem; color: var(--text-muted); margin-top: 0.3rem; }

        .header-funcionario {
            margin-bottom: 1.5rem; padding: 1rem 1.5rem; background: var(--surface-color); 
            border-radius: var(--radius-lg); box-shadow: 0 2px 10px rgba(0,0,0,0.04); 
            display: flex; align-items: center; gap: 1rem;
        }
        .header-funcionario .avatar {
            width: 48px; height: 48px; border-radius: 50%; background: var(--gradient-brand); 
            color: white; display: flex; align-items: center; justify-content: center; 
            font-weight: bold; font-size: 1.3rem; flex-shrink: 0;
        }
        .header-funcionario .info h3 { margin: 0; color: var(--text-main); font-size: 1.2rem; }
        .header-funcionario .info p { margin: 0; color: var(--text-muted); font-size: 0.85rem; }

        .secao-relatorio { margin-bottom: 2rem; }
        .secao-relatorio h3 { color: var(--text-main); margin-bottom: 1rem; font-size: 1.1rem; display: flex; align-items: center; gap: 0.5rem; }
        .secao-relatorio h3 i { color: var(--color-purple); }

        .barra-visual { display: flex; align-items: center; gap: 0.8rem; width: 100%; }
        .barra-visual .barra {
            height: 8px; border-radius: 4px; background: var(--gradient-brand);
            transition: width 0.5s ease; min-width: 4px;
        }
        
        .badge-frequencia {
            display: inline-flex; align-items: center; justify-content: center;
            background: linear-gradient(135deg, #a78bfa, var(--color-purple));
            color: white; font-weight: 700; font-size: 0.85rem;
            width: 32px; height: 32px; border-radius: 50%;
        }

        .btn-exportar {
            padding: 0.7rem 1.2rem; background: #2ecc71; color: white; border: none;
            border-radius: var(--radius-md); font-weight: 600; cursor: pointer; font-size: 0.9rem;
            transition: transform 0.15s, box-shadow 0.15s; display: inline-flex; align-items: center; gap: 0.5rem;
            white-space: nowrap;
        }
        .btn-exportar:hover { transform: translateY(-1px); box-shadow: 0 4px 15px rgba(46,204,113,0.4); }

        .estado-vazio { text-align: center; padding: 4rem 2rem; color: var(--text-muted); }
        .estado-vazio .icone { font-size: 3rem; margin-bottom: 1rem; }
        .estado-vazio p { font-size: 1.1rem; }

        .btn-filtro-rapido {
            background: none; border: 1px solid var(--color-purple); color: var(--color-purple);
            padding: 0.4rem 1rem; border-radius: 20px; font-size: 0.8rem; font-weight: 600;
            cursor: pointer; transition: all 0.2s; white-space: nowrap;
        }
        .btn-filtro-rapido:hover { background: var(--color-purple); color: white; }
        
        .chart-container {
            background: var(--surface-color); border-radius: var(--radius-lg); padding: 1.5rem;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06); margin-bottom: 2rem;
            height: 350px; position: relative;
        }

        @media (max-width: 768px) {
            .filtro-form { padding: 1rem; }
            .filtro-form .form-group { min-width: 100%; }
            .filtro-botoes-container { flex-direction: column; align-items: stretch; }
            .btn-gerar, .btn-exportar { width: 100%; justify-content: center; }
            .metricas-grid { grid-template-columns: 1fr 1fr; gap: 0.8rem; }
            .metrica-card { padding: 1rem; }
            .metrica-card .metrica-valor { font-size: 1.5rem; }
            .chart-container { height: 250px; padding: 0.8rem; }
            .header-funcionario { padding: 1rem; flex-direction: column; text-align: center; }
        }

        @media (max-width: 480px) {
            .metricas-grid { grid-template-columns: 1fr; }
        }

        /* PDF Nativo (window.print) */
        @media print {
            @page { margin: 10mm; size: A4 portrait; }
            
            html, body, .main-wrapper, #area-relatorio { 
                background: white !important; color: black !important; 
                margin: 0 !important; padding: 0 !important; 
                width: 100% !important; max-width: 100% !important;
                min-height: 0 !important; height: auto !important; 
                overflow: visible !important;
            }
            
            /* Remove margins from the page edges that might push a blank page */
            body::after, .main-wrapper::after { display: none !important; }
            
            .filtro-form, .btn-exportar, .sidebar, .topbar, .sidebar-overlay, .btn-filtro-rapido { display: none !important; }
            
            .metricas-grid { display: flex !important; flex-wrap: wrap !important; gap: 10px !important; margin-bottom: 1.5rem !important; }
            .metrica-card { flex: 1 1 45% !important; box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; page-break-inside: avoid; padding: 1rem !important; margin-bottom: 0 !important; }
            
            .chart-container { 
                box-shadow: none !important; border: 1px solid #ddd !important; 
                break-inside: avoid; page-break-inside: avoid; 
                height: 280px !important; margin-bottom: 1.5rem !important; 
                width: 100% !important; padding: 10px !important;
            }
            
            canvas#faturamentoChart { display: none !important; }
            img#chart-print-img { display: block !important; width: 100% !important; height: 100% !important; object-fit: contain !important; }
            
            .base-card { box-shadow: none !important; border: 1px solid #ddd !important; break-inside: avoid; page-break-inside: avoid; margin-bottom: 1.5rem !important; }
            
            .secao-relatorio:last-child { margin-bottom: 0 !important; }
            .secao-relatorio:last-child .base-card { margin-bottom: 0 !important; }
            
            * { -webkit-print-color-adjust: exact !important; print-color-adjust: exact !important; }
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
                <option value="todos" <?= ($idFuncionario === 'todos') ? 'selected' : '' ?>>🌟 Visão Geral do Salão</option>
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
            <input type="date" name="data_fim" id="data_fim" value="<?= htmlspecialchars($dataFim) ?>" required>
        </div>
        
        <div class="filtro-botoes-container">
            <button type="button" class="btn-filtro-rapido" onclick="setFiltroData('hoje')">Hoje</button>
            <button type="button" class="btn-filtro-rapido" onclick="setFiltroData('semana')">Esta Semana</button>
            <button type="button" class="btn-filtro-rapido" onclick="setFiltroData('mes')">Este Mês</button>
        </div>

        <div class="filtro-botoes-container" style="margin-top: 0.5rem;">
            <button type="submit" class="btn-gerar">📊 Gerar Relatório</button>

            <?php if ($metricas): ?>
                <button type="button" class="btn-exportar" onclick="exportarPDF()">
                    <i class="bi bi-file-earmark-pdf"></i> PDF
                </button>
                <button type="button" class="btn-exportar" style="background: #10b981;" onclick="exportarCSV()">
                    <i class="bi bi-file-earmark-spreadsheet"></i> CSV
                </button>
            <?php endif; ?>
        </div>
    </form>

    <!-- CONTEÚDO DO RELATÓRIO -->
    <div id="area-relatorio">

        <?php if ($metricas): ?>

            <?php if ($funcionarioSelecionado): ?>
                <div class="header-funcionario">
                    <div class="avatar">
                        <?= strtoupper(substr($funcionarioSelecionado['nome'], 0, 1)) ?>
                    </div>
                    <div class="info">
                        <h3><?= htmlspecialchars($funcionarioSelecionado['nome']) ?></h3>
                        <p>
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

            <!-- GRÁFICO DE FATURAMENTO DIÁRIO -->
            <?php if (!empty($dadosDiarios)): ?>
            <div class="chart-container" style="position: relative;">
                <canvas id="faturamentoChart"></canvas>
                <!-- A imagem fica invisível na tela normal, mas o CSS de impressão troca ela pelo canvas -->
                <img id="chart-print-img" src="" style="display: none; pointer-events: none;" alt="Grafico">
            </div>
            <?php endif; ?>

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
                                        <td data-label="#" style="font-weight: 700; color: var(--color-purple);"><?= $i + 1 ?></td>
                                        <td data-label="Serviço" style="font-weight: 500;"><?= htmlspecialchars($srv['nome_servico']) ?></td>
                                        <td data-label="Quantidade" style="font-weight: 700;"><?= $srv['quantidade'] ?>x</td>
                                        <td data-label="Proporção">
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
                                        <td data-label="Cliente" style="font-weight: 500;"><?= htmlspecialchars($cli['cliente_nome']) ?></td>
                                        <td data-label="Frequência" style="text-align: center;">
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

    <!-- Chart.js para gráficos -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    
    <script>
        // FILTROS RÁPIDOS DE DATA
        function setFiltroData(tipo) {
            const dataInicio = document.querySelector('input[name="data_inicio"]');
            const dataFim = document.querySelector('input[name="data_fim"]');
            const hoje = new Date();
            
            if (tipo === 'hoje') {
                const hj = hoje.toISOString().split('T')[0];
                dataInicio.value = hj;
                dataFim.value = hj;
            } else if (tipo === 'semana') {
                const primeiroDia = new Date(hoje.setDate(hoje.getDate() - hoje.getDay()));
                const ultimoDia = new Date(hoje.setDate(hoje.getDate() - hoje.getDay() + 6));
                dataInicio.value = primeiroDia.toISOString().split('T')[0];
                dataFim.value = ultimoDia.toISOString().split('T')[0];
            } else if (tipo === 'mes') {
                const primeiroDia = new Date(hoje.getFullYear(), hoje.getMonth(), 1);
                const ultimoDia = new Date(hoje.getFullYear(), hoje.getMonth() + 1, 0);
                dataInicio.value = primeiroDia.toISOString().split('T')[0];
                dataFim.value = ultimoDia.toISOString().split('T')[0];
            }
        }

        // GRÁFICO DE FATURAMENTO DIÁRIO (Chart.js)
        let faturamentoChartInstance = null;

        <?php if (!empty($dadosDiarios)): ?>
        document.addEventListener("DOMContentLoaded", function() {
            const ctx = document.getElementById('faturamentoChart').getContext('2d');
            const dadosPhp = <?= json_encode($dadosDiarios) ?>;
            
            const labels = dadosPhp.map(d => {
                const p = d.data.split('-');
                return `${p[2]}/${p[1]}`; // DD/MM
            });
            const dadosFaturamento = dadosPhp.map(d => parseFloat(d.faturamento));
            
            faturamentoChartInstance = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Faturamento Bruto Diário (R$)',
                        data: dadosFaturamento,
                        borderColor: '#8b5cf6',
                        backgroundColor: 'rgba(139, 92, 246, 0.2)',
                        borderWidth: 3,
                        pointBackgroundColor: '#ec4899',
                        pointRadius: 4,
                        fill: true,
                        tension: 0.3
                    }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    animation: false, // Desativa a animação para garantir renderização imediata
                    plugins: {
                        legend: { position: 'top' },
                        title: { display: true, text: 'Evolução de Faturamento no Período' }
                    },
                    scales: {
                        y: { beginAtZero: true }
                    }
                }
            });

            // Após renderizar o gráfico, tira a "foto" em Base64 para ficar aguardando a impressão
            setTimeout(() => {
                const img = document.getElementById('chart-print-img');
                if (img && faturamentoChartInstance) {
                    img.src = faturamentoChartInstance.toBase64Image();
                }
            }, 800);
        });
        <?php endif; ?>

        // EXPORTAR PARA CSV (EXCEL)
        function exportarCSV() {
            // O \uFEFF é crucial para o Excel reconhecer os acentos (UTF-8 com BOM)
            let csv = '\uFEFFRelatório de Desempenho\n\n';
            
            // Usando Ponto e Vírgula (;) ao invés de vírgula, pois é o padrão do Excel BR
            csv += 'Métricas Gerais\n';
            csv += `Atendimentos Concluídos;<?= $totalConcluidos ?? 0 ?>\n`;
            csv += `Faturamento Bruto;R$ <?= number_format($faturamentoBruto ?? 0, 2, ',', '') ?>\n`;
            csv += `Ticket Médio;R$ <?= number_format($ticketMedio ?? 0, 2, ',', '') ?>\n`;
            csv += `Taxa de Cancelamento (%);<?= number_format($taxaCancelamento ?? 0, 1, ',', '') ?>%\n\n`;

            // Ranking
            csv += 'Ranking de Serviços\n';
            csv += 'Serviço;Quantidade\n';
            <?php if (!empty($rankingServicos)): ?>
                <?php foreach($rankingServicos as $srv): ?>
                    csv += `"<?= $srv['nome_servico'] ?>";<?= $srv['quantidade'] ?>\n`;
                <?php endforeach; ?>
            <?php endif; ?>

            // Download
            const blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' });
            const url = URL.createObjectURL(blob);
            const link = document.createElement("a");
            link.setAttribute("href", url);
            link.setAttribute("download", "relatorio_desempenho.csv");
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        }

        function exportarPDF() {
            // Usa o motor nativo do Google Chrome/Navegador para salvar como PDF (muito superior e sem bugs de layout)
            window.print();
        }
    </script>
</body>
</html>
