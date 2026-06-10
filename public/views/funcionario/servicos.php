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
            <h2 style="color: var(--text-main); margin-bottom: 0.5rem; display: flex; align-items: center; gap: 0.75rem; flex-wrap: wrap;">
                <span>Meus Serviços</span>
                <span id="saveStatus" style="font-size: 0.8rem; font-weight: 600; display: inline-flex; align-items: center; gap: 0.35rem; padding: 4px 12px; border-radius: 20px; transition: all 0.3s ease; opacity: 0; pointer-events: none; transform: translateY(-1px);"></span>
            </h2>
            <p style="color: var(--text-muted);">Selecione os serviços que você está apto a realizar no salão.</p>
        </div>
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

    <style>
        .servicos-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 1.25rem;
            margin-bottom: 2rem;
        }

        .servico-card-item {
            display: flex;
            align-items: center;
            gap: 1.2rem;
            padding: 1.25rem;
            border: 2px solid var(--border-color);
            border-radius: 12px;
            cursor: pointer;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background: var(--surface-color);
            position: relative;
            user-select: none;
        }

        .servico-card-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 20px rgba(139, 92, 246, 0.1);
            border-color: var(--color-purple);
        }

        .servico-card-item:active {
            transform: translateY(-1px) scale(0.98);
        }

        .servico-card-item:has(input:checked) {
            border-color: var(--color-purple);
            background-color: rgba(139, 92, 246, 0.05);
            box-shadow: 0 0 0 4px rgba(139, 92, 246, 0.1);
        }

        .servico-card-item input[type="checkbox"] {
            position: absolute;
            opacity: 0;
            width: 0;
            height: 0;
        }

        /* Checkbox Customizado */
        .custom-checkbox {
            width: 24px;
            height: 24px;
            border: 2px solid var(--border-color);
            border-radius: 6px;
            display: flex;
            justify-content: center;
            align-items: center;
            flex-shrink: 0;
            transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
            background-color: var(--surface-color);
            color: transparent;
            font-size: 0.9rem;
        }

        .servico-card-item:hover .custom-checkbox {
            border-color: var(--color-purple);
        }

        .servico-card-item input:checked + .custom-checkbox {
            background-color: var(--color-purple);
            border-color: var(--color-purple);
            color: #ffffff;
            transform: scale(1.1);
            box-shadow: 0 4px 10px rgba(139, 92, 246, 0.25);
        }

        .servico-info {
            flex: 1;
        }

        .servico-info strong {
            display: block;
            color: var(--text-main);
            font-size: 1.1rem;
            margin-bottom: 0.35rem;
            font-weight: 600;
        }

        .servico-badges {
            display: flex;
            flex-wrap: wrap;
            gap: 0.5rem;
        }

        .s-badge {
            font-size: 0.78rem;
            color: var(--text-muted);
            background: var(--bg-color);
            padding: 5px 12px;
            border-radius: 20px;
            border: 1px solid var(--border-color);
            display: inline-flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
            transition: all 0.2s ease;
        }

        .servico-card-item:has(input:checked) .s-badge {
            background-color: rgba(139, 92, 246, 0.1);
            border-color: rgba(139, 92, 246, 0.15);
            color: var(--color-purple);
        }

        /* Wrapper da Busca */
        .search-wrapper {
            position: relative;
            margin-bottom: 1.5rem;
        }

        .search-wrapper i {
            position: absolute;
            left: 1.1rem;
            top: 50%;
            transform: translateY(-50%);
            color: var(--text-muted);
            font-size: 1.15rem;
            pointer-events: none;
            transition: color 0.2s ease;
        }

        .search-wrapper .form-control {
            padding-left: 2.8rem;
            border-radius: 50px;
            height: 48px;
            border: 2px solid var(--border-color);
            transition: all 0.25s ease;
        }

        .search-wrapper .form-control:focus {
            border-color: var(--color-purple);
            box-shadow: 0 4px 20px rgba(139, 92, 246, 0.12);
        }

        .search-wrapper .form-control:focus + i {
            color: var(--color-purple);
        }

        /* Estado vazio da busca */
        .no-services-found {
            display: none;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem 1.5rem;
            color: var(--text-muted);
            animation: fadeIn 0.3s ease;
        }

        .no-services-found i {
            font-size: 3rem;
            color: var(--color-purple);
            margin-bottom: 1rem;
            opacity: 0.7;
        }

        .no-services-found h4 {
            color: var(--text-main);
            margin-bottom: 0.5rem;
            font-weight: 700;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Responsividade */
        @media (max-width: 768px) {
            .servicos-grid {
                grid-template-columns: 1fr 1fr;
                gap: 1rem;
            }
        }

        @media (max-width: 500px) {
            .servicos-grid {
                grid-template-columns: 1fr;
            }

            .servico-card-item {
                padding: 1rem;
            }
        }
    </style>

    <div class="base-card" style="max-width: 800px; padding: 2rem;">
        
        <?php if ($idFuncionarioLogado): ?>
            <div class="search-wrapper">
                <input type="text" class="form-control" placeholder="Pesquisar serviço..." id="pesquisaServicos">
                <i class="bi bi-search"></i>
            </div>
            <form id="formServicos" action="<?= BASE_URL ?>/funcionario/servicos/salvar" method="POST">
                <?= CsrfGuard::campoHidden() ?>
                
                <div class="servicos-grid" id="listaServicos">
                    <?php foreach ($todosServicos as $servico): ?>
                        <?php 
                            $marcado = in_array($servico['id_servico'], $meusServicos) ? 'checked' : ''; 
                        ?>
                        <label class="servico-card-item" data-nome="<?= strtolower(htmlspecialchars($servico['nome_servico'])) ?>">
                            <input type="checkbox" name="servicos[]" value="<?= $servico['id_servico'] ?>" <?= $marcado ?>>
                            <div class="custom-checkbox">
                                <i class="bi bi-check-lg"></i>
                            </div>
                            <div class="servico-info">
                                <strong><?= htmlspecialchars($servico['nome_servico']) ?></strong>
                                <div class="servico-badges">
                                    <span class="s-badge"><i class="bi bi-clock"></i> <?= $servico['duracao'] ?> min</span>
                                    <span class="s-badge"><i class="bi bi-tag"></i> R$ <?= number_format($servico['preco'], 2, ',', '.') ?></span>
                                </div>
                            </div>
                        </label>
                    <?php endforeach; ?>
                </div>

                <div class="no-services-found" id="noServicesFound">
                    <i class="bi bi-search-heart"></i>
                    <h4>Nenhum serviço encontrado</h4>
                    <p>Tente buscar por outro nome ou termo.</p>
                </div>
            </form>

        <?php else: ?>
            <div style="text-align: center; padding: 2rem 0;">
                <p style="font-size: 3rem; margin-bottom: 1rem; color: var(--color-purple);"><i class="bi bi-briefcase"></i></p>
                <h3 style="color: var(--text-main); margin-bottom: 0.5rem;">Sem perfil de atendimento</h3>
                <p style="color: var(--text-muted);">A sua conta possui nível de Administração, mas não está cadastrada como um prestador de serviços na tabela de funcionários. Por isso, não possui uma agenda de serviços própria.</p>
            </div>
        <?php endif; ?>

    </div>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/servico.js"></script>
    
    <!-- SweetAlert2 -->
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.min.css" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.32/dist/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', () => {
            const inputPesquisa = document.getElementById('pesquisaServicos');
            const cards = document.querySelectorAll('.servico-card-item');
            const noServices = document.getElementById('noServicesFound');

            // Filtro de Busca com Estado Vazio
            if (inputPesquisa) {
                inputPesquisa.addEventListener('input', function() {
                    const termo = this.value.toLowerCase().trim();
                    let matches = 0;
                    
                    cards.forEach(card => {
                        const nome = card.getAttribute('data-nome');
                        if (nome.includes(termo)) {
                            card.style.display = 'flex';
                            matches++;
                        } else {
                            card.style.display = 'none';
                        }
                    });

                    if (matches === 0) {
                        noServices.style.display = 'flex';
                    } else {
                        noServices.style.display = 'none';
                    }
                });
            }

            // Indicador de status inline para o salvamento automático
            const saveStatus = document.getElementById('saveStatus');
            const form = document.getElementById('formServicos');
            let saveTimeout;

            function showStatus(type) {
                saveStatus.style.opacity = '1';
                if (type === 'saving') {
                    saveStatus.style.background = 'rgba(139, 92, 246, 0.1)';
                    saveStatus.style.color = 'var(--color-purple)';
                    saveStatus.style.border = '1px solid rgba(139, 92, 246, 0.2)';
                    saveStatus.innerHTML = '<i class="bi bi-arrow-repeat spinner-loading" style="font-size: 0.85rem;"></i> Salvando...';
                } else if (type === 'success') {
                    saveStatus.style.background = 'var(--alert-success-bg)';
                    saveStatus.style.color = 'var(--alert-success-text)';
                    saveStatus.style.border = '1px solid var(--alert-success-border)';
                    saveStatus.innerHTML = '<i class="bi bi-check-lg"></i> Salvo!';
                    
                    clearTimeout(saveTimeout);
                    saveTimeout = setTimeout(() => {
                        saveStatus.style.opacity = '0';
                    }, 2000);
                } else if (type === 'error') {
                    saveStatus.style.background = 'var(--alert-danger-bg)';
                    saveStatus.style.color = 'var(--alert-danger-text)';
                    saveStatus.style.border = '1px solid var(--alert-danger-border)';
                    saveStatus.innerHTML = '<i class="bi bi-x-lg"></i> Erro ao salvar';
                }
            }

            if (form) {
                form.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
                    checkbox.addEventListener('change', () => {
                        showStatus('saving');

                        const formData = new FormData(form);

                        fetch(form.action, {
                            method: 'POST',
                            body: formData,
                            headers: { 'X-Requested-With': 'XMLHttpRequest' }
                        })
                        .then(res => res.json())
                        .then(data => {
                            if (data && data.sucesso) {
                                showStatus('success');
                            } else {
                                showStatus('error');
                            }
                        })
                        .catch(err => {
                            console.error('Erro ao atualizar especialidades:', err);
                            showStatus('error');
                        });
                    });
                });
            }
        });
    </script>
</body>
</html>