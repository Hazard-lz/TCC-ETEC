<?php
// Simulação de dados unindo `usuarios` e `clientes` do seu Banco de Dados
$clientes = [
    ['id' => 1, 'nome' => 'João Silva', 'telefone' => '11987654321', 'email' => 'joao.silva@email.com', 'nascimento' => '1989-10-17', 'observacoes' => 'Cliente fiel do salão.'],
    ['id' => 2, 'nome' => 'Carlos Souza', 'telefone' => '11999887766', 'email' => 'carlos.souza@email.com', 'nascimento' => '2000-12-13', 'observacoes' => 'Costuma agendar aos sábados.'],
    ['id' => 3, 'nome' => 'Ana Pereira', 'telefone' => '11985740136', 'email' => 'ana.pereira@email.com', 'nascimento' => '1990-10-11', 'observacoes' => '']
];
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
                    <button data-modal-target="#modalCliente" class="btn-primary btn-new">+Cadastro Rápido</button>
                </div>

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
                                <?php foreach ($clientes as $cli): ?>
                                <tr>
                                    <td style="font-weight: 500;"><?= htmlspecialchars($cli['nome']) ?></td>
                                    <td><?= preg_replace('/(\d{2})(\d{5})(\d{4})/', '($1) $2-$3', $cli['telefone']) ?></td>
                                    <td><?= htmlspecialchars($cli['email']) ?></td>
                                    <td><?= date('d/m/Y', strtotime($cli['nascimento'])) ?></td>
                                    <td>
                                        <div class="action-buttons">
                                            <button data-modal-target="#modalCliente" class="btn-action btn-edit" title="Editar" 
                                                onclick="preencherModalEdicaoCliente(<?= $cli['id'] ?>, '<?= $cli['nome'] ?>', '<?= $cli['telefone'] ?>', '<?= $cli['email'] ?>', '<?= $cli['nascimento'] ?>', '<?= htmlspecialchars($cli['observacoes'], ENT_QUOTES) ?>')">
                                                ✏️
                                            </button>
                                            <button class="btn-action btn-delete" title="Excluir" onclick="confirmarExclusaoCliente(<?= $cli['id'] ?>)">🗑️</button>
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

    <div id="modalCliente" class="modal-overlay">
        <div class="modal-content cliente-card" style="box-shadow: none; margin: 0; padding: 0;">
            
            <div class="modal-header">
                <h3 id="modalTitleCliente">Cadastro Rápido</h3>
                <button data-close-modal class="btn-close">&times;</button>
            </div>
            
            <div class="modal-body">
                <form id="formCliente" action="/cliente/salvar" method="POST">
                    <input type="hidden" id="id_cliente" name="id_cliente" value="">

                    <h3 class="section-title">Dados Pessoais</h3>
                    
                    <div class="form-grid">
                        <div class="form-group">
                            <label for="nome">Nome Completo</label>
                            <input type="text" id="nome" name="nome" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label for="telefone" >Telefone / WhatsApp</label>
                            <input placeholder="Ex: (11) 98765-4321" type="tel" id="telefone" name="telefone" class="form-control" required>
                        </div>
                    </div>

                    
                    

                    <h3 class="section-title">Informações Adicionais</h3>

                    <div class="form-group">
                        <label for="nascimento">Data de Nascimento</label>
                        <input type="date" id="nascimento" name="nascimento" class="form-control" required>
                    </div>

                    <div class="form-group">
                        <label for="observacoes">Observações (Alergias, preferências...)</label>
                        <textarea id="observacoes" name="observacoes" class="form-control" placeholder="Ex: Tem sensibilidade a alguns produtos químicos..."></textarea>
                    </div>

                    <div id="clienteError" class="error-message">Verifique os campos preenchidos.</div>
                    
                    <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                        <button type="submit" class="btn-primary" style="margin-top: 0;">Salvar Cliente</button>
                        <button type="button" data-close-modal class="btn-primary" style="margin-top: 0; background: #e2e8f0; color: var(--text-main); box-shadow: none;">Cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/modal.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/cliente.js"></script>
</body>
</html>