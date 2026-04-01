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
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

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
                    <tr style="<?= $servico['status'] === 'inativo' ? 'opacity: 0.6;' : '' ?>">
                        <td style="font-weight: 500;"><?= htmlspecialchars($servico['nome_servico']) ?></td>
                        <td>R$ <?= number_format($servico['preco'], 2, ',', '.') ?></td>
                        <td><?= $servico['duracao'] ?> min</td>
                        <td>
                            <?php if ($servico['status'] === 'ativo'): ?>
                                <span class="badge" style="background-color: #dcfce7; color: #166534; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Ativo</span>
                            <?php else: ?>
                                <span class="badge" style="background-color: #fee2e2; color: #991b1b; padding: 4px 8px; border-radius: 4px; font-size: 0.8rem;">Inativo</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <div class="action-buttons" style="display: flex; gap: 8px; align-items: center;">
                                <button data-modal-target="#modalServico" class="btn-action btn-edit" title="Editar" 
                                    onclick="preencherModalEdicao(<?= $servico['id_servico'] ?>, '<?= htmlspecialchars(addslashes($servico['nome_servico'])) ?>', '<?= htmlspecialchars(addslashes($servico['descricao'])) ?>', <?= $servico['preco'] ?>, <?= $servico['duracao'] ?>, '<?= $servico['status'] ?>')"
                                    style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">✏️</button>
                                
                                <?php if($servico['status'] === 'ativo'): ?>
                                    <button class="btn-action btn-delete" title="Inativar" onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, 'inativo')" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">🚫</button>
                                <?php else: ?>
                                    <button class="btn-action btn-edit" title="Ativar" onclick="alterarStatusServico(<?= $servico['id_servico'] ?>, 'ativo')" style="background: none; border: none; cursor: pointer; font-size: 1.2rem;">✅</button>
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
                    
                    <div class="form-row" style="display: flex; gap: 15px;">
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

    </div>

    <script>const BASE_URL = '<?= BASE_URL ?>';</script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
        
</body>
</html> 