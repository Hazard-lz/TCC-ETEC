<?php
// Requisita a Model para buscar os dados reais da base de dados
require_once __DIR__ . '/../../../app/Models/Cliente.php';
$clienteModel = new Cliente();
$clientes = $clienteModel->listarTodos();

// Verifica permissões do usuário logado
$tipoUsuario = $_SESSION['usuario_tipo'] ?? '';
$isAdmin = ($tipoUsuario === 'admin');
$isGerencia = in_array($tipoUsuario, ['admin', 'subadmin']);
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Belezou App</title>

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
    
    <!-- Flatpickr (Calendário Estilizado) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
    <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
    <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/l10n/pt.js"></script>
    
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
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
            <h2>Gerenciar Clientes</h2>
            <p>Acesse o histórico e os dados de contato dos clientes do salão.</p>
        </div>
        <?php if ($isGerencia): ?>
            <button data-modal-target="#modalCliente" class="btn-primary btn-new" onclick="abrirCadastroRapido()">+Cadastro
                Rápido</button>
        <?php endif; ?>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success">
            <?= $_SESSION['flash_sucesso'];
            unset($_SESSION['flash_sucesso']); ?>
        </div>
    <?php endif; ?>
    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger">
            <?= $_SESSION['flash_erro'];
            unset($_SESSION['flash_erro']); ?>
        </div>
    <?php endif; ?>

    <div class="base-card">
        <div class="table-filters" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <div class="search-box" style="flex: 1; min-width: 250px;">
                <input type="text" class="form-control input-pesquisa-tabela" placeholder="Pesquisar cliente...">
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
                        <th>Nome do Cliente</th>
                        <th>Telefone</th>
                        <th>E-mail</th>
                        <th>Nascimento</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($clientes)): ?>
                        <?php foreach ($clientes as $cli): ?>
                            <tr class="<?= $cli['status'] === 'inativo' ? 'row-inactive' : '' ?>">
                                <td data-label="Cliente" style="font-weight: 500;">
                                    <?= htmlspecialchars($cli['nome']) ?>
                                    <?= $cli['status'] === 'inativo' ? '<small class="status-inativo-text" style="margin-left: 5px;">(Inativo)</small>' : '' ?>
                                </td>
                                <td data-label="Telefone"><?= preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cli['telefone']) ?></td>
                                <td data-label="E-mail"><?= htmlspecialchars($cli['email'] ?? 'Sem e-mail') ?></td>
                                <td data-label="Nascimento"><?= !empty($cli['data_nascimento']) ? date('d/m/Y', strtotime($cli['data_nascimento'])) : 'N/A' ?>
                                </td>
                                <td data-label="Ações">
                                    <div class="action-buttons">
                                        <button type="button" data-modal-target="#modalCliente" class="btn-action btn-edit"
                                            title="<?= $isGerencia ? 'Editar' : 'Adicionar Observações' ?>"
                                            onclick='preencherModalEdicaoCliente(<?= $cli["id_cliente"] ?>, <?= $cli["cod_usuario"] ?>, <?= json_encode($cli["nome"]) ?>, <?= json_encode($cli["telefone"]) ?>, <?= json_encode($cli["data_nascimento"] ?? "") ?>, <?= json_encode($cli["observacoes"] ?? "") ?>)'>
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <?php if ($isGerencia): ?>
                                            <form action="<?= BASE_URL ?>/cliente/alterar-status" method="POST"
                                                style="display:inline;">
                                        <?= CsrfGuard::campoHidden() ?>
                                                <input type="hidden" name="cod_usuario" value="<?= $cli['cod_usuario'] ?>">
                                                <input type="hidden" name="status_atual" value="<?= $cli['status'] ?>">

                                                <button type="submit"
                                                    class="btn-action <?= $cli['status'] === 'ativo' ? 'btn-delete' : 'btn-success' ?>"
                                                    title="<?= $cli['status'] === 'ativo' ? 'Inativar' : 'Ativar' ?>"
                                                    onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Tem certeza que deseja <?= $cli['status'] === 'ativo' ? 'inativar' : 'ativar' ?> o cliente <?= htmlspecialchars(addslashes($cli['nome'])) ?>?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">
                                                    <?php if ($cli['status'] === 'ativo'): ?>
                                                        <i class="bi bi-person-x-fill"></i>
                                                    <?php else: ?>
                                                        <i class="bi bi-person-check-fill"></i>
                                                    <?php endif; ?>
                                                </button>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" style="text-align: center; padding: 2rem;">Nenhum cliente cadastrado ainda.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalCliente" class="modal-overlay">
        <div class="modal-content cliente-card">
            <div class="modal-header">
                <h3 id="modalTitleCliente">Cadastro Rápido</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            <div class="modal-body">
                <form id="formCliente" action="<?= BASE_URL ?>/cliente/salvar" method="POST">
                                        <?= CsrfGuard::campoHidden() ?>
                    <input type="hidden" id="id_cliente" name="id_cliente" value="">
                    <input type="hidden" id="id_usuario" name="id_usuario" value="">

                    <div class="alert alert-info" id="container_explicacao_cadastro" style="font-size: 0.85rem; line-height: 1.45; margin-bottom: 1.25rem; background-color: rgba(139, 92, 246, 0.08); border: 1px dashed rgba(139, 92, 246, 0.3); color: var(--text-main); border-radius: 8px; padding: 0.75rem 1rem; display: flex; gap: 0.5rem; align-items: start;">
                        <i class="bi bi-info-circle-fill" style="color: var(--color-purple); font-size: 1.1rem; flex-shrink: 0; margin-top: 1px;"></i>
                        <span>
                            <strong>O que é o Cadastro Rápido?</strong><br>
                            Permite registrar clientes que entram em contato por telefone ou WhatsApp para que você possa realizar agendamentos manuais para eles. O cadastro exige apenas o telefone do cliente. Quando ele criar uma conta completa no app no futuro com o mesmo número, o sistema unificará todo o histórico de agendamentos dele automaticamente!
                        </span>
                    </div>

                    <h3 class="section-title">Dados Pessoais</h3>

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required <?= !$isGerencia ? 'readonly' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp</label>
                            <input placeholder="Ex: (11) 98765-4321" type="tel" id="telefone" name="telefone"
                                class="form-control" <?= !$isAdmin ? 'readonly' : '' ?>>
                        </div>
                    </div>

                    <div class="form-group" id="container_nascimento">
                        <label for="nascimento">Data de Nascimento</label>
                        <input type="date" id="nascimento" name="nascimento" class="form-control" <?= !$isGerencia ? 'readonly' : '' ?> placeholder="Selecione uma data">
                    </div>

                    <div class="form-group" id="container_observacoes">
                        <label for="observacoes">Observações (Alergias, preferências...)</label>
                        <textarea id="observacoes" name="observacoes" class="form-control"
                            placeholder="Ex: Tem sensibilidade a alguns produtos químicos..."></textarea>
                    </div>

                    <div class="modal-actions">
                        <button type="submit" class="btn-primary">Salvar</button>
                        <button type="button" data-close-modal class="btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script>
        window.isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
        window.isGerencia = <?= $isGerencia ? 'true' : 'false' ?>;

        document.addEventListener("DOMContentLoaded", () => {
            if (window.isGerencia) {
                flatpickr("#nascimento", {
                    locale: "pt",
                    dateFormat: "Y-m-d",
                    altInput: true,
                    altFormat: "d/m/Y",
                    altInputClass: "form-control flatpickr-alt-input",
                    disableMobile: true,
                    maxDate: "today"
                });
            }

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
                    // Ignora linha de "nenhum registro encontrado"
                    if (row.id === "no-result-row") {
                        row.remove();
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
                        td.textContent = "Nenhum cliente corresponde aos filtros selecionados.";
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
                    e.stopImmediatePropagation(); // Evita execução do script padrão
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
    <script src="<?= BASE_URL ?>/public/resources/js/cliente.js"></script>

</body>

</html>