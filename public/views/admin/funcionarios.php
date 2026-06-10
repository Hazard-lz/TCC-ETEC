<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$idLogado = $_SESSION['usuario_id'];

require_once __DIR__ . '/../../../app/Models/Funcionario.php';
require_once __DIR__ . '/../../../app/Models/Usuario.php';

$funcionarioModel = new Funcionario();
$usuarioModel = new Usuario();

$listaFuncionarios = $funcionarioModel->listarTodos();
$totalAdmins = $usuarioModel->contarAdminsAtivos();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Funcionários - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?? '' ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/funcionario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?? '' ?>/public/resources/css/modal.css">
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
            <h2>Gerenciar Funcionários</h2>
            <p>Controle os dados da equipe e os níveis de acesso ao sistema.</p>
        </div>
        <button data-modal-target="#modalFuncionario" class="btn-primary btn-new" onclick="limparModalFuncionario()">+
            Novo Funcionário</button>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success">
            <strong>Sucesso!</strong> <?= $_SESSION['flash_sucesso'] ?>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger">
            <strong>Erro:</strong> <?= htmlspecialchars($_SESSION['flash_erro']) ?>
        </div>
        <?php unset($_SESSION['flash_erro']); ?>
    <?php endif; ?>

    <div class="base-card">
        <div class="table-filters" style="display: flex; gap: 1rem; align-items: center; justify-content: space-between; margin-bottom: 1.5rem; flex-wrap: wrap;">
            <div class="search-box" style="flex: 1; min-width: 250px;">
                <input type="text" class="form-control input-pesquisa-tabela" placeholder="Pesquisar funcionário...">
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
                        <th>Nome</th>
                        <th>Especialidade</th>
                        <th>Telefone</th>
                        <th>Status</th>
                        <th>Acesso</th>
                        <th>Ações</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($listaFuncionarios)): ?>
                        <?php foreach ($listaFuncionarios as $func): ?>
                            <?php $isLogado = ($func['cod_usuario'] == $idLogado); ?>

                            <tr class="<?= $func['status'] === 'inativo' ? 'row-inactive' : '' ?>"
                                style="<?= $isLogado ? 'border-left: 4px solid #8b5cf6;' : '' ?>">

                                <td data-label="Funcionário" style="font-weight: 500;">
                                    <?= htmlspecialchars($func['nome']) ?>
                                    <?php if ($isLogado): ?>
                                        <span class="user-self-label">(Você)</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Especialidade"><?= htmlspecialchars($func['especialidade']) ?></td>
                                <td data-label="Telefone">
                                    <?= !empty($func['telefone']) ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $func['telefone']) : 'Não informado' ?>
                                </td>

                                <td data-label="Status">
                                    <?php if ($func['status'] === 'ativo'): ?>
                                        <span class="badge badge-ativo">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge badge-inativo">Inativo</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Acesso">
                                    <?php if (isset($func['tipo']) && $func['tipo'] === 'admin'): ?>
                                        <span class="badge badge-admin">Admin</span>
                                    <?php elseif (isset($func['tipo']) && $func['tipo'] === 'subadmin'): ?>
                                        <span class="badge badge-subadmin">Subadmin</span>
                                    <?php else: ?>
                                        <span class="badge badge-inativo">Comum</span>
                                    <?php endif; ?>
                                </td>

                                <td data-label="Ações">
                                    <div class="action-buttons">
                                        <button data-modal-target="#modalFuncionario" class="btn-action btn-edit" title="Editar"
                                            data-funcionario='<?= htmlspecialchars(json_encode($func), ENT_QUOTES, 'UTF-8') ?>'
                                            data-is-logado="<?= $isLogado ? 'true' : 'false' ?>"
                                            data-total-admins="<?= $totalAdmins ?>" onclick="abrirEdicaoFuncionario(this)">
                                            <i class="bi bi-pencil-square"></i>
                                        </button>

                                        <?php if (isset($func['email_verificado']) && $func['email_verificado'] == 0): ?>
                                            <form action="<?= BASE_URL ?? '' ?>/admin/funcionarios/reenviar-email" method="POST"
                                                style="margin: 0;">
                                                <?= CsrfGuard::campoHidden() ?>
                                                <input type="hidden" name="cod_usuario" value="<?= $func['cod_usuario'] ?>">
                                                <button type="submit" class="btn-action" title="Reenviar E-mail de Configuração"
                                                    onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja reenviar o link de criação de senha para o funcionário <?= htmlspecialchars(addslashes($func['nome'])) ?>?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">
                                                    <i class="bi bi-envelope"></i>
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <?php
                                        $tipoLogado = $_SESSION['usuario_tipo'] ?? '';
                                        $alvoEhAdmin = (isset($func['tipo']) && $func['tipo'] === 'admin');
                                        $bloqueadoPorHierarquia = ($tipoLogado === 'subadmin' && $alvoEhAdmin);
                                        ?>
                                        <?php if ($isLogado || $bloqueadoPorHierarquia): ?>
                                            <button type="button" class="btn-action btn-disabled"
                                                title="<?= $isLogado ? 'Você não pode inativar a si mesmo.' : 'Sem permissão para alterar um administrador.' ?>">
                                                <i class="bi bi-slash-circle"></i>
                                            </button>
                                        <?php else: ?>
                                            <form action="<?= BASE_URL ?? '' ?>/admin/funcionarios/status" method="POST"
                                                style="margin: 0;">
                                                <?= CsrfGuard::campoHidden() ?>
                                                <input type="hidden" name="cod_usuario" value="<?= $func['cod_usuario'] ?>">
                                                <input type="hidden" name="status_atual" value="<?= $func['status'] ?>">

                                                <?php if ($func['status'] === 'ativo'): ?>
                                                    <button type="submit" class="btn-action btn-delete" title="Inativar Acesso"
                                                        onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja realmente INATIVAR o funcionário <?= htmlspecialchars(addslashes($func['nome'])) ?>? Ele não poderá mais acessar o sistema ou receber novos agendamentos.', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">
                                                        <i class="bi bi-slash-circle"></i>
                                                    </button>
                                                <?php else: ?>
                                                    <button type="submit" class="btn-action btn-edit" title="Reativar Acesso"
                                                        onclick="event.preventDefault(); Swal.fire({title: 'Atenção', text: 'Deseja ATIVAR o funcionário <?= htmlspecialchars(addslashes($func['nome'])) ?> novamente?', icon: 'warning', showCancelButton: true, confirmButtonColor: '#dc3545', cancelButtonColor: '#6c757d', confirmButtonText: 'Confirmar', cancelButtonText: 'Cancelar'}).then((result) => { if (result.isConfirmed) { this.closest('form').submit(); } });">
                                                        <i class="bi bi-check-circle"></i>
                                                    </button>
                                                <?php endif; ?>
                                            </form>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="6" style="text-align: center;">Nenhum funcionário cadastrado no sistema.</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <div id="modalFuncionario" class="modal-overlay">
        <div class="modal-content funcionario-card">
            <div class="modal-header">
                <h3 id="modalTitleFunc">Cadastrar Novo Funcionário</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formFuncionario" action="<?= BASE_URL ?? '' ?>/admin/funcionarios/salvar" method="POST">
                    <?= CsrfGuard::campoHidden() ?>
                    <input type="hidden" id="id_funcionario" name="id_funcionario" value="">

                    <h3 class="section-title">Dados Pessoais e Acesso</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" placeholder="Ex: (11) 98765-4321">
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="email">E-mail de Acesso (Receberá o link para criar a senha)</label>
                        <input type="email" id="email" name="email" class="form-control" required>
                    </div>

                    <h3 class="section-title">Dados Profissionais</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="especialidade">Especialidade Principal</label>
                            <input type="text" id="especialidade" name="especialidade" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="salario">Salário Base</label>
                            <div class="input-group">
                                <span class="input-group-text">R$</span>
                                <input type="number" id="salario" name="salario" class="form-control input-money"
                                    min="0" value="0">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Nível de Acesso no Sistema</label>
                        <select id="tipo" name="tipo" class="form-control form-select">
                            <option value="comum">Profissional Comum</option>
                            <option value="subadmin">Subadministrador (Gestão sem relatórios)</option>
                            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                                <option value="admin" id="optionAdmin" style="display: none;">Transferir Cargo de
                                    Administrador</option>
                            <?php endif; ?>
                        </select>
                    </div>

                    <div id="funcionarioError" class="error-message">Verifique os campos preenchidos.</div>

                    <div class="modal-actions">
                        <button type="submit" id="btnSalvarFuncionario" class="btn-primary">Cadastrar Funcionário</button>
                        <button type="button" data-close-modal class="btn-secondary">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>



    <script>
        const LOGGED_USER_TYPE = '<?= $_SESSION['usuario_tipo'] ?? '' ?>';
    </script>
    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/funcionario.js"></script>
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
                        const headersCount = document.querySelectorAll(".data-table th").length || 6;
                        td.colSpan = headersCount;
                        td.style.textAlign = "center";
                        td.style.padding = "2rem";
                        td.style.color = "var(--text-muted)";
                        td.textContent = "Nenhum funcionário corresponde aos filtros selecionados.";
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
</body>

</html>