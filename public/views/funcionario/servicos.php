<?php
// Carrega os models necessários
$servicoModel = new Servico();
$funcionarioModel = new Funcionario();

// 1. Pega TODOS os serviços ativos do salão
$todosServicos = $servicoModel->listarPorStatus('ativo');

// 2. Pega os dados do funcionário do usuário que está logado
$dadosFunc = $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);

// 3. Verifica se a consulta realmente retornou dados antes de extrair o ID
if ($dadosFunc) {
    $idFuncionarioLogado = $dadosFunc['id_funcionario'];
    // Pega os IDs dos serviços que este funcionário já faz
    $meusServicos = $funcionarioModel->buscarIdsServicosPorFuncionario($idFuncionarioLogado, 'ativo');
} else {
    // Se o banco retornou false (ex: é um Admin que não corta cabelo)
    $idFuncionarioLogado = null;
    $meusServicos = [];
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meus Serviços - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header" style="margin-bottom: 2rem;">
        <div class="page-title">
            <h2 style="color: var(--text-main); margin-bottom: 0.5rem;">Meus Serviços</h2>
            <p style="color: var(--text-muted);">Selecione os serviços que você está apto a realizar no salão.</p>
        </div>
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

    <style>
        .servicos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1rem;
            margin-bottom: 2rem;
        }

        .servico-card-item {
            display: flex;
            align-items: flex-start;
            gap: 1rem;
            padding: 1.2rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
            background: var(--bg-card);
        }

        .servico-card-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(0,0,0,0.05);
            border-color: #a78bfa;
        }

        .servico-card-item:has(input:checked) {
            border-color: var(--color-purple);
            background-color: rgba(139, 92, 246, 0.05);
        }

        .servico-card-item input[type="checkbox"] {
            width: 22px;
            height: 22px;
            margin-top: 2px;
            accent-color: var(--color-purple);
            cursor: pointer;
        }

        .servico-info {
            flex: 1;
        }

        .servico-info strong {
            display: block;
            color: var(--text-main);
            font-size: 1.05rem;
            margin-bottom: 0.4rem;
        }

        .servico-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
            margin-top: 0.5rem;
        }

        .s-badge {
            font-size: 0.8rem;
            color: var(--text-muted);
            background: var(--bg-body);
            padding: 2px 8px;
            border-radius: 12px;
            border: 1px solid var(--border-color);
            display: inline-flex;
            align-items: center;
            gap: 4px;
        }
    </style>

    <div class="base-card" style="max-width: 800px; padding: 2rem;">
        
        <?php if ($idFuncionarioLogado): ?>
            <div class="form-group mb-3" style="margin-bottom: 1.5rem;">
                <input type="text" class="form-control input-pesquisa-tabela" placeholder="Pesquisar serviço..." id="pesquisaServicos">
            </div>
            <form action="<?= BASE_URL ?>/funcionario/servicos/salvar" method="POST">
                                        <?= CsrfGuard::campoHidden() ?>
                
                <div class="servicos-grid" id="listaServicos">
                    <?php foreach ($todosServicos as $servico): ?>
                        <?php 
                            $marcado = in_array($servico['id_servico'], $meusServicos) ? 'checked' : ''; 
                        ?>
                        <label class="servico-card-item" data-nome="<?= strtolower(htmlspecialchars($servico['nome_servico'])) ?>">
                            <input type="checkbox" name="servicos[]" value="<?= $servico['id_servico'] ?>" <?= $marcado ?>>
                            <div class="servico-info">
                                <strong><?= htmlspecialchars($servico['nome_servico']) ?></strong>
                                <div class="servico-badges">
                                    <span class="s-badge">⏱️ <?= $servico['duracao'] ?> min</span>
                                    <span class="s-badge">💰 R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div style="display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-primary" style="margin: 0; min-width: 200px;">Salvar Especialidades</button>
                </div>
            </form>

        <?php else: ?>
            <div style="text-align: center; padding: 2rem 0;">
                <p style="font-size: 3rem; margin-bottom: 1rem;">💼</p>
                <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">Sem perfil de atendimento</h3>
                <p style="color: var(--text-muted);">A sua conta possui nível de Administração, mas não está cadastrada como um prestador de serviços na tabela de funcionários. Por isso, não possui uma agenda de serviços própria.</p>
            </div>
        <?php endif; ?>

    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
    
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputPesquisa = document.getElementById('pesquisaServicos');
            const cards = document.querySelectorAll('.servico-card-item');

            if (inputPesquisa) {
                inputPesquisa.addEventListener('input', function() {
                    const termo = this.value.toLowerCase().trim();
                    
                    cards.forEach(card => {
                        const nome = card.getAttribute('data-nome');
                        if (nome.includes(termo)) {
                            card.style.display = 'flex';
                        } else {
                            card.style.display = 'none';
                        }
                    });
                });
            }
        });
    </script>
</body>
</html>