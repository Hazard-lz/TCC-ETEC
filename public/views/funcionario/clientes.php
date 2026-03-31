<?php
// Inicia a sessão para verificar o tipo de acesso
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Requisita a Model para buscar os dados reais da base de dados
require_once __DIR__ . '/../../../app/Models/Cliente.php';
$clienteModel = new Cliente();
$clientes = $clienteModel->listarTodos();

// Verifica se o utilizador logado é admin
$isAdmin = (isset($_SESSION['usuario_tipo']) && $_SESSION['usuario_tipo'] === 'admin');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Clientes - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/cliente.css">
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
                        <h2>Gerenciar Clientes</h2>
                        <p>Acesse o histórico e os dados de contato dos clientes do salão.</p>
                    </div>
                    <?php if ($isAdmin): ?>
                        <button data-modal-target="#modalCliente" class="btn-primary btn-new" onclick="abrirCadastroRapido()">+Cadastro Rápido</button>
                    <?php endif; ?>
                </div>

                <?php if (isset($_SESSION['flash_sucesso'])): ?>
                    <div style="background-color: #dcfce7; color: #166534; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <?= $_SESSION['flash_sucesso']; unset($_SESSION['flash_sucesso']); ?>
                    </div>
                <?php endif; ?>
                <?php if (isset($_SESSION['flash_erro'])): ?>
                    <div style="background-color: #fee2e2; color: #991b1b; padding: 1rem; border-radius: 8px; margin-bottom: 1.5rem;">
                        <?= $_SESSION['flash_erro']; unset($_SESSION['flash_erro']); ?>
                    </div>
                <?php endif; ?>

                <div class="base-card">
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
                                    <tr style="<?= $cli['status'] === 'inativo' ? 'opacity: 0.6;' : '' ?>">
                                        <td style="font-weight: 500;">
                                            <?= htmlspecialchars($cli['nome']) ?>
                                            <?= $cli['status'] === 'inativo' ? '<small style="color:red; margin-left: 5px;">(Inativo)</small>' : '' ?>
                                        </td>
                                        <td><?= preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cli['telefone']) ?></td>
                                        <td><?= htmlspecialchars($cli['email'] ?? 'Sem e-mail') ?></td>
                                        <td><?= !empty($cli['data_nascimento']) ? date('d/m/Y', strtotime($cli['data_nascimento'])) : 'N/A' ?></td>
                                        <td>
                                            <div class="action-buttons">
                                                <button type="button" data-modal-target="#modalCliente" class="btn-action btn-edit" title="<?= $isAdmin ? 'Editar' : 'Adicionar Observações' ?>" 
                                                    onclick='preencherModalEdicaoCliente(<?= $cli["id_cliente"] ?>, <?= $cli["cod_usuario"] ?>, <?= json_encode($cli["nome"]) ?>, <?= json_encode($cli["telefone"]) ?>, <?= json_encode($cli["data_nascimento"] ?? "") ?>, <?= json_encode($cli["observacoes"] ?? "") ?>)'>
                                                    ✏️
                                                </button>

                                                <?php if ($isAdmin): ?>
                                                    <form action="<?= BASE_URL ?>/cliente/alterar-status" method="POST" style="display:inline;">
                                                        <input type="hidden" name="cod_usuario" value="<?= $cli['cod_usuario'] ?>">
                                                        <input type="hidden" name="status_atual" value="<?= $cli['status'] ?>">
                                                        
                                                        <button type="submit" class="btn-action <?= $cli['status'] === 'ativo' ? 'btn-delete' : 'btn-success' ?>" 
                                                                title="<?= $cli['status'] === 'ativo' ? 'Inativar' : 'Ativar' ?>"
                                                                onclick="return confirm('Tem certeza que deseja <?= $cli['status'] === 'ativo' ? 'inativar' : 'ativar' ?> este cliente?')">
                                                            <?= $cli['status'] === 'ativo' ? '🚫' : '✅' ?>
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
            </section>
        </main>
    </div>

    <div id="modalCliente" class="modal-overlay">
        <div class="modal-content cliente-card" style="box-shadow: none; margin: 0; padding: 0;">
            
            <div class="modal-header">
                <h3 id="modalTitleCliente">Cadastro Rápido</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formCliente" action="<?= BASE_URL ?>/cliente/salvar" method="POST">
                    
                    <input type="hidden" id="id_cliente" name="id_cliente" value="">
                    <input type="hidden" id="id_usuario" name="id_usuario" value="">

                    <h3 class="section-title">Dados Pessoais</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required <?= !$isAdmin ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed;"' : '' ?>>
                        </div>
                        <div class="form-group">
                            <label for="telefone">Telefone / WhatsApp</label>
                            <input placeholder="Ex: (11) 98765-4321" type="tel" id="telefone" name="telefone" class="form-control" required <?= !$isAdmin ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed;"' : '' ?>>
                        </div>
                    </div>

                    <div class="form-group" id="container_nascimento">
                        <label for="nascimento">Data de Nascimento</label>
                        <input type="date" id="nascimento" name="nascimento" class="form-control" <?= !$isAdmin ? 'readonly style="background-color: #f1f5f9; cursor: not-allowed;"' : '' ?>>
                    </div>

                    <div class="form-group" id="container_observacoes">
                        <label for="observacoes">Observações (Alergias, preferências...)</label>
                        <textarea id="observacoes" name="observacoes" class="form-control" placeholder="Ex: Tem sensibilidade a alguns produtos químicos..."></textarea>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0;">Salvar</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/cliente.js"></script>

    <script>
        function abrirCadastroRapido() {
            document.getElementById('formCliente').reset();
            document.getElementById('id_cliente').value = '';
            document.getElementById('id_usuario').value = ''; 
            
            document.getElementById('modalTitleCliente').innerText = 'Cadastro Rápido';
            
            // Esconde nascimento e desabilita input
            document.getElementById('container_nascimento').style.display = 'none';
            document.getElementById('nascimento').disabled = true;

            // Esconde observacoes e desabilita input
            document.getElementById('container_observacoes').style.display = 'none';
            document.getElementById('observacoes').disabled = true;

            document.getElementById('nome').readOnly = false;
            document.getElementById('nome').style.backgroundColor = '';
            document.getElementById('telefone').readOnly = false;
            document.getElementById('telefone').style.backgroundColor = '';
        }

        function preencherModalEdicaoCliente(id_cliente, id_usuario, nome, telefone, nascimento, observacoes) {
            document.getElementById('id_cliente').value = id_cliente;
            document.getElementById('id_usuario').value = id_usuario; 
            document.getElementById('nome').value = nome;
            document.getElementById('telefone').value = telefone;
            document.getElementById('nascimento').value = nascimento || '';
            document.getElementById('observacoes').value = observacoes || '';
            
            const isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;
            document.getElementById('modalTitleCliente').innerText = isAdmin ? 'Editar Cliente' : 'Observações do Cliente';

            // Reexibe nascimento e habilita input
            document.getElementById('container_nascimento').style.display = 'block';
            document.getElementById('nascimento').disabled = false;

            // Reexibe observações e habilita input
            document.getElementById('container_observacoes').style.display = 'block';
            document.getElementById('observacoes').disabled = false;
        }
    </script>
</body>
</html>