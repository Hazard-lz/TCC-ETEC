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
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gerenciar Serviços - Belezou App</title>
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/servico.css">
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
                        <h2>Gerenciar Serviços</h2>
                        <p>Visualize, edite ou cadastre os serviços do salão.</p>
                    </div>
                    <button data-modal-target="#modalServico" class="btn-primary btn-new" onclick="limparModalServico()">+ Novo Serviço</button>
                </div>

                <div class="base-card">
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
                                <?php if(empty($servicos)): ?>
                                    <tr><td colspan="5" style="text-align: center;">Nenhum serviço cadastrado ainda.</td></tr>
                                <?php endif; ?>

                                <?php foreach ($servicos as $servico): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($servico['nome_servico']) ?></td>
                                    <td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                                    <td><?= $servico['duracao'] ?> min</td>
                                    <td><span class="badge badge-<?= $servico['status'] ?>"><?= ucfirst($servico['status']) ?></span></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button data-modal-target="#modalServico" class="btn-action btn-edit" title="Editar" 
                                                    onclick="preencherModalEdicao(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>', '<?= htmlspecialchars(addslashes($servico['descricao'])) ?>', <?= $servico['preco'] ?>, <?= $servico['duracao'] ?>, '<?= $servico['status'] ?>')">✏️</button>
                                            
                                            <?php if($servico['status'] === 'ativo'): ?>
                                                <button class="btn-action btn-delete" title="Inativar" onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, 'inativo')">🗑️</button>
                                            <?php else: ?>
                                                <button class="btn-action btn-edit" style="background-color: #10b981;" title="Ativar" onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, 'ativo')">✅</button>
                                            <?php endif; ?>
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

    <div id="modalServico" class="modal-overlay">
        <div class="modal-content">
            
            <div class="modal-header">
                <h3 id="modalTitle">Cadastrar Novo Serviço</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formServico">
                    <div id="servicoError" style="color: red; margin-bottom: 10px; display: none; font-weight: 500;"></div>

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
                        <div class="form-group" style="flex: 1;">
                            <label for="preco">Preço (R$)</label>
                            <input type="number" id="preco" name="preco" class="form-control" step="0.01" min="0" required>
                        </div>
                        
                        <div class="form-group" style="flex: 1;">
                            <label for="duracao">Duração (Minutos)</label>
                            <input type="number" id="duracao" name="duracao" class="form-control" min="5" step="5" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="status">Status</label>
                        <select id="status" name="status" class="form-control">
                            <option value="ativo" selected>Ativo</option>
                            <option value="inativo">Inativo</option>
                        </select>
                    </div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0;">Salvar Serviço</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
        
</body>
</html>