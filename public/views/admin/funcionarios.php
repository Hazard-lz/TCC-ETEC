<?php
// Simulação de dados unindo as tabelas `usuarios` e `funcionarios` do seu BD
$funcionarios = [
    ['id' => 1, 'nome' => 'Maria Oliveira', 'telefone' => '11991234567', 'email' => 'maria@email.com', 'especialidade' => 'Atendimento Geral', 'salario' => 2500.00, 'tipo' => 'admin'],
    ['id' => 2, 'nome' => 'Lucas Santos', 'telefone' => '11995554433', 'email' => 'lucas@email.com', 'especialidade' => 'Barbeiro', 'salario' => 2000.00, 'tipo' => 'comum'],
    ['id' => 3, 'nome' => 'Fernanda Costa', 'telefone' => '11992223344', 'email' => 'fernanda@email.com', 'especialidade' => 'Manicure', 'salario' => 1800.00, 'tipo' => 'comum']
];
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Funcionários - Belezou App</title>

    <link rel="icon" type="image/png" href="/public/resources/images/favicon.png">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/funcionario.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/modal.css">
</head>
<body>

    <div class="admin-wrapper">
        <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

        <main class="main-content">
            <?php require_once __DIR__ . '/../partials/header.php'; ?>

            <section class="content-area">
                
                <div class="page-header">
                    <div class="page-title">
                        <h2>Gerenciar Funcionários</h2>
                        <p>Controle a equipe do salão e os níveis de acesso ao sistema.</p>
                    </div>
                    <button data-modal-target="#modalFuncionario" class="btn-primary btn-new">+ Novo Funcionário</button>
                </div>

                <div class="base-card">
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Nome</th>
                                    <th>Especialidade</th>
                                    <th>Telefone</th>
                                    <th>Acesso</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($funcionarios as $func): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($func['nome']) ?></td>
                                    <td><?= htmlspecialchars($func['especialidade']) ?></td>
                                    <td><?= preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $func['telefone']) ?></td>
                                    <td>
                                        <?php if($func['tipo'] === 'admin'): ?>
                                            <span class="badge badge-ativo">Admin</span>
                                        <?php else: ?>
                                            <span class="badge badge-inativo" style="background-color: #e2e8f0; color: #4a5568;">Comum</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-buttons">
                                            <button data-modal-target="#modalFuncionario" class="btn-action btn-edit" title="Editar" 
                                                onclick="preencherModalEdicaoFuncionario(<?= $func['id'] ?>, '<?= $func['nome'] ?>', '<?= $func['telefone'] ?>', '<?= $func['email'] ?>', '<?= $func['especialidade'] ?>', <?= $func['salario'] ?>, '<?= $func['tipo'] ?>')">
                                                ✏️
                                            </button>
                                            <button class="btn-action btn-delete" title="Excluir" onclick="confirmarExclusaoFuncionario(<?= $func['id'] ?>)">🗑️</button>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </section>
        </main>
    </div>

    <div id="modalFuncionario" class="modal-overlay">
        <div class="modal-content funcionario-card" style="box-shadow: none; margin: 0; padding: 0;">
            
            <div class="modal-header">
                <h3 id="modalTitleFunc">Cadastrar Novo Funcionário</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formFuncionario" action="/funcionario/salvar" method="POST">
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

                    <div class="form-grid">
                        <div class="form-group">
                            <label for="email">E-mail de Acesso</label>
                            <input type="email" id="email" name="email" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="senha">Senha Temporária</label>
                            <input type="password" id="senha" name="senha" class="form-control" placeholder="Deixe em branco para não alterar">
                        </div>
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

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/funcionario.js"></script>
</body>
</html>