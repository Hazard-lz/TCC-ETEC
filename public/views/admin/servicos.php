<?php
// Busca os dados reais do banco
require_once __DIR__ . '/../../../app/Models/Servico.php';
$servicoModel = new Servico();

$ativos = $servicoModel->listarPorStatus('ativo') ?? [];
$inativos = $servicoModel->listarPorStatus('inativo') ?? [];
$servicos = array_merge($ativos, $inativos);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Serviços - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/servico.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
    <style>
        /* --- ESTILOS DOS BOTÕES DE FILTRO DE STATUS --- */
        .status-filters {
            display: flex;
            gap: 2px;
            background: var(--bg-color);
            padding: 3px !important;
            border-radius: 10px !important;
            border: 1px solid var(--border-color) !important;
            align-items: center;
        }

        .btn-filter-status {
            border: none !important;
            background: transparent !important;
            color: var(--text-muted) !important;
            border-radius: 8px !important;
            font-weight: 600 !important;
            font-size: 0.82rem !important;
            padding: 0.35rem 1rem !important;
            height: auto !important; /* Reseta altura padrão de 42px do admin.css */
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1) !important;
            box-shadow: none !important;
            cursor: pointer;
            display: inline-flex;
            align-items: center;
            justify-content: center;
        }

        .btn-filter-status:not(.active):hover {
            color: var(--color-purple) !important;
            background: rgba(139, 92, 246, 0.06) !important;
        }

        .btn-filter-status.active {
            background: var(--color-purple) !important;
            color: white !important;
            box-shadow: 0 2px 6px rgba(139, 92, 246, 0.25) !important;
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header">
        <div class="page-title">
            <h2>Gerenciar Serviços</h2>
            <p>Visualize, edite ou cadastre os serviços do salão.</p>
        </div>
        <button data-modal-target="#modalServico" class="btn-primary btn-new" onclick="limparModalServico()">+ Novo
            Serviço</button>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success">
            <strong>Sucesso!</strong> <?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>
        </div>
    <?php endif; ?>

    <div class="base-card">
        <div class="table-filters" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <div class="search-box" style="flex: 1; min-width: 250px;">
                <input type="text" class="form-control input-pesquisa-tabela" placeholder="Pesquisar serviço...">
            </div>
            <div class="status-filters">
                <button type="button" class="btn-filter-status active" data-filter="todos">Todos</button>
                <button type="button" class="btn-filter-status" data-filter="ativo">Ativos</button>
                <button type="button" class="btn-filter-status" data-filter="inativo">Inativos</button>
            </div>
        </div>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Nome do Serviço</th>
                        <th>Preço</th>
                        <th>Duração</th>
                        <th>Status</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($servicos)): ?>
                        <tr>
                            <td colspan="5" style="text-align: center;">Nenhum serviço cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>

                    <?php foreach ($servicos as $servico): ?>
                        <tr class="<?= $servico['status'] === 'inativo' ? 'row-inactive' : '' ?>">
                            <td data-label="Serviço" style="font-weight: 500;">
                                <?= htmlspecialchars($servico['nome_servico']) ?></td>
                            <td data-label="Preço">R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                            <td data-label="Duração"><?= $servico['duracao'] ?> min</td>
                            <td data-label="Status">
                                <?php if ($servico['status'] === 'ativo'): ?>
                                    <span class="badge badge-ativo">Ativo</span>
                                <?php else: ?>
                                    <span class="badge badge-inativo">Inativo</span>
                                <?php endif; ?>
                            </td>
                            <td data-label="Ações">
                                <div class="action-buttons">
                                    <button data-modal-target="#modalServico" class="btn-action btn-edit" title="Editar"
                                        onclick="preencherModalEdicao(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>', '<?= htmlspecialchars(addslashes($servico['descricao'])) ?>', <?= $servico['preco'] ?>, <?= $servico['duracao'] ?>, '<?= $servico['status'] ?>')">
                                        <i class="bi bi-pencil-square"></i>
                                    </button>

                                    <?php if ($servico['status'] === 'ativo'): ?>
                                        <button class="btn-action btn-delete" title="Inativar"
                                            onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>', 'inativo')">
                                            <i class="bi bi-slash-circle"></i>
                                        </button>
                                    <?php else: ?>
                                        <button class="btn-action btn-edit" title="Ativar"
                                            onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>', 'ativo')">
                                            <i class="bi bi-check-circle"></i>
                                        </button>
                                        <button class="btn-action btn-delete" title="Excluir Permanentemente"
                                            onclick="excluirServico(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>')">
                                            <i class="bi bi-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalServico" class="modal-overlay">
        <div class="modal-content">

            <div class="modal-header">
                <h3 id="modalTitle">Cadastrar Novo Serviço</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formServico">
                    <div id="servicoError" style="color: red; margin-bottom: 10px; display: none; font-weight: 500;">
                    </div>

                    <input type="hidden" id="id_servico" name="id_servico" value="">

                    <div class="form-group">
                        <label for="nome_servico">Nome do Serviço</label>
                        <input type="text" id="nome_servico" name="nome_servico" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="descricao">Descrição</label>
                        <textarea id="descricao" name="descricao" class="form-control" required></textarea>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label for="preco">Preço (R$)</label>
                            <input type="number" id="preco" name="preco" class="form-control" step="0.01" min="0"
                                required>
                        </div>

                        <div class="form-group">
                            <label for="duracao">Duração (Minutos)</label>
                            <input type="number" id="duracao" name="duracao" class="form-control" min="5" step="5"
                                required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="ativo" selected>Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Salvar Serviço</button>
                        <button type="button" data-close-modal class="btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
    <script>
        document.addEventListener("DOMContentLoaded", () => {
            const inputPesquisa = document.querySelector(".input-pesquisa-tabela");
            const filterButtons = document.querySelectorAll(".btn-filter-status");
            const tableBody = document.querySelector(".data-table tbody");
            
            let filtroAtivo = "todos"; // todos, ativo, inativo
            let termoPesquisa = "";

            function aplicarFiltros() {
                if (!tableBody) return;
                const rows = tableBody.querySelectorAll("tr");
                let hasVisibleRow = false;

                rows.forEach(row => {
                    // Ignora linha de "nenhum registro encontrado" ou de tabela vazia do php
                    if (row.id === "no-result-row") {
                        row.remove();
                        return;
                    }

                    // Se a tabela já estiver vazia originalmente no PHP
                    if (row.cells.length === 1 && row.cells[0].getAttribute("colspan") == "5" && row.textContent.includes("Nenhum serviço")) {
                        row.style.display = "none";
                        return;
                    }

                    const textoLinha = row.textContent.toLowerCase();
                    const matchesSearch = textoLinha.includes(termoPesquisa);
                    
                    const isRowInactive = row.classList.contains("row-inactive");
                    let matchesStatus = true;
                    if (filtroAtivo === "ativo") {
                        matchesStatus = !isRowInactive;
                    } else if (filtroAtivo === "inativo") {
                        matchesStatus = isRowInactive;
                    }

                    if (matchesSearch && matchesStatus) {
                        row.style.display = "";
                        hasVisibleRow = true;
                    } else {
                        row.style.display = "none";
                    }
                });

                // Gerencia mensagem de "Nenhum resultado"
                const noResultRow = document.getElementById("no-result-row");
                if (!hasVisibleRow) {
                    if (!noResultRow) {
                        const tr = document.createElement("tr");
                        tr.id = "no-result-row";
                        const td = document.createElement("td");
                        const headersCount = document.querySelectorAll(".data-table th").length || 5;
                        td.colSpan = headersCount;
                        td.style.textAlign = "center";
                        td.style.padding = "2rem";
                        td.style.color = "var(--text-muted)";
                        td.textContent = "Nenhum serviço corresponde aos filtros selecionados.";
                        tr.appendChild(td);
                        tableBody.appendChild(tr);
                    }
                } else {
                    if (noResultRow) {
                        noResultRow.remove();
                    }
                }
            }

            if (inputPesquisa) {
                inputPesquisa.addEventListener("input", function(e) {
                    e.stopImmediatePropagation(); // Evita execução do script padrão do admin.js
                    termoPesquisa = this.value.toLowerCase();
                    aplicarFiltros();
                });
            }

            filterButtons.forEach(btn => {
                btn.addEventListener("click", function() {
                    filterButtons.forEach(b => b.classList.remove("active"));
                    this.classList.add("active");
                    filtroAtivo = this.getAttribute("data-filter");
                    aplicarFiltros();
                });
            });
        });
    </script>

</body>

</html>