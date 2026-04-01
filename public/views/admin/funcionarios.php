<?php
// Carrega apenas o Model necessário para esta tela
require_once __DIR__ . '/../../../app/Models/Funcionario.php';

$funcionarioModel = new Funcionario();
$listaFuncionarios = $funcionarioModel->listarTodos();
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
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
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header">
        <div class="page-title">
            <h2>Gerenciar Funcionários</h2>
            <p>Controle os dados da equipe e os níveis de acesso ao sistema.</p>
        </div>
        <button data-modal-target="#modalFuncionario" class="btn-primary btn-new" onclick="limparModalFuncionario()">+ Novo Funcionário</button>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #bbf7d0;">
            <strong>Sucesso!</strong> <?= $_SESSION['flash_sucesso'] ?>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem; border: 1px solid #fecaca;">
            <strong>Erro:</strong> <?= htmlspecialchars($_SESSION['flash_erro']) ?>
        </div>
        <?php unset($_SESSION['flash_erro']); ?>
    <?php endif; ?>

    <div class="base-card">
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
                            <tr style="<?= $func['status'] === 'inativo' ? 'opacity: 0.6;' : '' ?>">
                                <td style="font-weight: 500;"><?= htmlspecialchars($func['nome']) ?></td>
                                <td><?= htmlspecialchars($func['especialidade']) ?></td>
                                <td>
                                    <?= !empty($func['telefone']) ? preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $func['telefone']) : 'Não informado' ?>
                                </td>

                                <td>
                                    <?php if ($func['status'] === 'ativo'): ?>
                                        <span class="badge" style="background-color: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Ativo</span>
                                    <?php else: ?>
                                        <span class="badge" style="background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Inativo</span>
                                    <?php endif; ?>
                                </td>

                                <td>
                                    <?php if (isset($func['tipo']) && $func['tipo'] === 'admin'): ?>
                                        <span class="badge badge-ativo">Admin</span>
                                    <?php else: ?>
                                        <span class="badge badge-inativo" style="background-color: #e2e8f0; color: #4a5568;">Comum</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 8px; align-items: center;">
                                        <button data-modal-target="#modalFuncionario" class="btn-action btn-edit" title="Editar"
                                            data-funcionario='<?= htmlspecialchars(json_encode($func), ENT_QUOTES, 'UTF-8') ?>'
                                            onclick="abrirEdicaoFuncionario(this)"
                                            style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                            ✏️
                                        </button>

                                        <?php if (isset($func['email_verificado']) && $func['email_verificado'] == 0): ?>
                                            <form action="<?= BASE_URL ?? '' ?>/admin/funcionarios/reenviar-email" method="POST" style="margin: 0;">
                                                <input type="hidden" name="cod_usuario" value="<?= $func['cod_usuario'] ?>">
                                                <button type="submit" class="btn-action" title="Reenviar E-mail de Configuração"
                                                    onclick="return confirm('Deseja reenviar o link de criação de senha para este funcionário?');"
                                                    style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                                    📧
                                                </button>
                                            </form>
                                        <?php endif; ?>

                                        <form action="<?= BASE_URL ?? '' ?>/admin/funcionarios/status" method="POST" style="margin: 0;">
                                            <input type="hidden" name="cod_usuario" value="<?= $func['cod_usuario'] ?>">
                                            <input type="hidden" name="status_atual" value="<?= $func['status'] ?>">

                                            <?php if ($func['status'] === 'ativo'): ?>
                                                <button type="submit" class="btn-action" title="Inativar Acesso"
                                                    onclick="return confirm('Deseja realmente INATIVAR este funcionário? Ele não poderá mais acessar o sistema ou receber novos agendamentos.');"
                                                    style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                                    🚫
                                                </button>
                                            <?php else: ?>
                                                <button type="submit" class="btn-action" title="Reativar Acesso"
                                                    onclick="return confirm('Deseja ATIVAR este funcionário novamente?');"
                                                    style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">
                                                    ✅
                                                </button>
                                            <?php endif; ?>
                                        </form>
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
        <div class="modal-content funcionario-card" style="box-shadow: none; margin: 0; padding: 0;">
            <div class="modal-header">
                <h3 id="modalTitleFunc">Cadastrar Novo Funcionário</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>

            <div class="modal-body">
                <form id="formFuncionario" action="<?= BASE_URL ?? '' ?>/admin/funcionarios/salvar" method="POST">
                    <input type="hidden" id="id_funcionario" name="id_funcionario" value="">

                    <h3 class="section-title">Dados Pessoais e Acesso</h3>
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp</label>
                            <input type="tel" id="telefone" name="telefone" class="form-control" required>
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
                                <input type="number" id="salario" name="salario" class="form-control input-money" step="0.01" min="0" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="tipo">Nível de Acesso no Sistema</label>
                        <select id="tipo" name="tipo" class="form-control">
                            <option value="comum">Profissional Comum</option>
                            <option value="admin">Administrador (Acesso total)</option>
                        </select>
                    </div>

                    <div id="funcionarioError" class="error-message">Verifique os campos preenchidos.</div>

                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0;">Salvar Funcionário</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    </div>

    

    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?? '' ?>/public/resources/js/funcionario.js"></script>
</body>

</html>