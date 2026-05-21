<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Garante o nível de acesso (Somente Admin e Subadmin na camada de view, mas o middleware já trata de barrar)
$tipoLogado = $_SESSION['usuario_tipo'] ?? '';
if (!in_array($tipoLogado, ['admin', 'subadmin'])) {
    header("Location: " . BASE_URL . "/funcionario/dashboard");
    exit;
}

$isAdmin = ($tipoLogado === 'admin');
?>
<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <?= CsrfGuard::metaTag() ?>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configurações do Sistema - Belezou App</title>
    <link rel="icon" type="image/png" href="<?= BASE_URL ?>/public/resources/images/favicon.png">

    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/listas.css">
    <style>
        .config-grid {
            display: grid;
            grid-template-columns: 1fr;
            gap: 1.5rem;
        }
        @media (min-width: 768px) {
            .config-grid {
                grid-template-columns: 2fr 1fr;
            }
        }
        .config-card {
            background: var(--surface-color, #ffffff);
            border: 1px solid var(--border-color, #e2e8f0);
            border-radius: 12px;
            padding: 1.75rem;
            box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05);
            transition: transform 0.2s, box-shadow 0.2s;
        }
        .status-option {
            border: 2px solid var(--border-color, #e2e8f0);
            border-radius: 8px;
            padding: 1.25rem;
            margin-bottom: 1rem;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            display: flex;
            align-items: flex-start;
            gap: 1rem;
        }
        .status-option:hover {
            border-color: var(--color-primary-light, #c084fc);
            background: rgba(139, 92, 246, 0.03);
        }
        .status-option.active-option {
            border-color: #10b981;
            background: rgba(16, 185, 129, 0.05);
        }
        .status-option.inactive-option {
            border-color: #ef4444;
            background: rgba(239, 68, 68, 0.05);
        }
        .status-option input[type="radio"] {
            margin-top: 0.25rem;
            accent-color: var(--color-primary, #8b5cf6);
            width: 18px;
            height: 18px;
        }
        .status-option-content h4 {
            margin: 0 0 0.25rem 0;
            font-size: 1.05rem;
            font-weight: 600;
            color: var(--text-main, #1e293b);
        }
        .status-option-content p {
            margin: 0;
            font-size: 0.9rem;
            color: var(--text-muted, #64748b);
            line-height: 1.4;
        }
        .badge-status {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
            padding: 0.25rem 0.75rem;
            border-radius: 9999px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .badge-status-active {
            background-color: #d1fae5;
            color: #065f46;
        }
        .badge-status-inactive {
            background-color: #fee2e2;
            color: #991b1b;
        }
        .btn-comunicado {
            background: linear-gradient(135deg, #f59e0b, #d97706);
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            width: 100%;
            justify-content: center;
        }
        .btn-comunicado:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
        .btn-save-config {
            background: var(--gradient-brand, linear-gradient(135deg, #8b5cf6, #ec4899));
            color: white;
            border: none;
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
        }
        .btn-save-config:hover {
            filter: brightness(1.05);
            transform: translateY(-1px);
        }
    </style>
</head>

<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="page-header">
        <div class="page-title">
            <h2>Configurações do Sistema</h2>
            <p>Gerencie o status operacional do estabelecimento e controle a contingência global.</p>
        </div>
    </div>

    <?php if (isset($_SESSION['flash_sucesso'])): ?>
        <div class="alert alert-success" style="margin-bottom: 1.5rem;">
            <strong>Sucesso!</strong> <?= $_SESSION['flash_sucesso'] ?>
        </div>
        <?php unset($_SESSION['flash_sucesso']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['flash_erro'])): ?>
        <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
            <strong>Erro:</strong> <?= htmlspecialchars($_SESSION['flash_erro']) ?>
        </div>
        <?php unset($_SESSION['flash_erro']); ?>
    <?php endif; ?>

    <div class="config-grid">
        <!-- Card 1: Controle de Funcionamento -->
        <div class="config-card">
            <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 600; color: var(--text-main, #1e293b); display: flex; align-items: center; gap: 0.5rem;">
                <i class="bi bi-power" style="color: var(--color-primary, #8b5cf6);"></i> Status de Funcionamento do Estabelecimento
            </h3>

            <form action="<?= BASE_URL ?>/admin/configuracoes/salvar" method="POST" id="formConfig">
                <?= CsrfGuard::campoHidden() ?>

                <label class="status-option <?= $statusFuncionamento === 'ativo' ? 'active-option' : '' ?>" for="opt_ativo">
                    <input type="radio" name="status_funcionamento" id="opt_ativo" value="ativo" <?= $statusFuncionamento === 'ativo' ? 'checked' : '' ?> onchange="atualizarClassesStatus()">
                    <div class="status-option-content">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <h4>Estabelecimento Aberto (Normal)</h4>
                            <span class="badge-status badge-status-active"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Ativo</span>
                        </div>
                        <p>O fluxo de agendamento online para clientes funciona perfeitamente. Clientes podem buscar profissionais, horários livres e realizar marcações no site.</p>
                    </div>
                </label>

                <label class="status-option <?= $statusFuncionamento === 'inativo' ? 'inactive-option' : '' ?>" for="opt_inativo">
                    <input type="radio" name="status_funcionamento" id="opt_inativo" value="inativo" <?= $statusFuncionamento === 'inativo' ? 'checked' : '' ?> onchange="atualizarClassesStatus()">
                    <div class="status-option-content">
                        <div style="display: flex; align-items: center; gap: 0.5rem; margin-bottom: 0.25rem;">
                            <h4>Fechamento Global (Contingência)</h4>
                            <span class="badge-status badge-status-inactive"><i class="bi bi-circle-fill" style="font-size: 0.5rem;"></i> Inativo</span>
                        </div>
                        <p>O agendamento para clientes comuns é completamente suspenso. Qualquer tentativa de acessar a tela de agendamento ou listar horários livres será bloqueada e redirecionada para a página de contingência explicativa. A gerência/equipe ainda pode usar a agenda interna.</p>
                    </div>
                </label>

                <!-- ══ PERSONALIZAÇÃO VISUAL (WHITE-LABEL) ══ -->
                <hr style="border: 0; border-top: 1px solid var(--border-color, #e2e8f0); margin: 2rem 0;">

                <h3 style="margin-top: 0; margin-bottom: 1.5rem; font-size: 1.25rem; font-weight: 600; color: var(--text-main, #1e293b); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-palette" style="color: var(--color-primary, #8b5cf6);"></i> Personalização da Marca (White-Label)
                </h3>

                <div style="display: flex; flex-wrap: wrap; gap: 1.5rem; margin-bottom: 1.5rem;">
                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.95rem; font-weight: 600; color: var(--text-muted, #64748b);">Cor Primária (Padrão Pink)</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="cor_primaria_picker" value="<?= htmlspecialchars($corPrimaria ?? '#f45b69') ?>" oninput="document.getElementById('cor_primaria').value = this.value" style="width: 45px; height: 45px; border-radius: 8px; border: 1.5px solid var(--border-color, #e2e8f0); cursor: pointer; padding: 0; background: none;">
                            <input type="text" name="cor_primaria" id="cor_primaria" value="<?= htmlspecialchars($corPrimaria ?? '#f45b69') ?>" oninput="document.getElementById('cor_primaria_picker').value = this.value" class="form-control" style="flex: 1;" placeholder="#f45b69">
                        </div>
                    </div>

                    <div class="form-group" style="flex: 1; min-width: 200px;">
                        <label style="display: block; margin-bottom: 0.5rem; font-size: 0.95rem; font-weight: 600; color: var(--text-muted, #64748b);">Cor Secundária (Padrão Roxo)</label>
                        <div style="display: flex; gap: 0.5rem; align-items: center;">
                            <input type="color" id="cor_secundaria_picker" value="<?= htmlspecialchars($corSecundaria ?? '#8b5cf6') ?>" oninput="document.getElementById('cor_secundaria').value = this.value" style="width: 45px; height: 45px; border-radius: 8px; border: 1.5px solid var(--border-color, #e2e8f0); cursor: pointer; padding: 0; background: none;">
                            <input type="text" name="cor_secundaria" id="cor_secundaria" value="<?= htmlspecialchars($corSecundaria ?? '#8b5cf6') ?>" oninput="document.getElementById('cor_secundaria_picker').value = this.value" class="form-control" style="flex: 1;" placeholder="#8b5cf6">
                        </div>
                    </div>
                </div>

                <div class="form-group" style="margin-bottom: 1.5rem;">
                    <label style="display: block; margin-bottom: 0.5rem; font-size: 0.95rem; font-weight: 600; color: var(--text-muted, #64748b);">URL do Logotipo da Marca</label>
                    <input type="url" name="logo_url" id="logo_url" value="<?= htmlspecialchars($logoUrl ?? '') ?>" class="form-control" placeholder="https://exemplo.com/sua-logo.png">
                    <small style="color: var(--text-muted, #64748b); font-size: 0.85rem; display: block; margin-top: 0.25rem;">Deixe em branco para usar o logotipo padrão do sistema (Belezou App).</small>
                </div>

                <div style="margin-top: 2rem; display: flex; justify-content: flex-end;">
                    <button type="submit" class="btn-save-config">
                        <i class="bi bi-check-lg"></i> Salvar Alterações
                    </button>
                </div>
            </form>
        </div>

        <!-- Card 2: Comunicados em Lote -->
        <div class="config-card" style="display: flex; flex-direction: column; justify-content: space-between;">
            <div>
                <h3 style="margin-top: 0; margin-bottom: 1.25rem; font-size: 1.2rem; font-weight: 600; color: var(--text-main, #1e293b); display: flex; align-items: center; gap: 0.5rem;">
                    <i class="bi bi-megaphone" style="color: #f59e0b;"></i> Comunicado Oficial
                </h3>
                <p style="font-size: 0.9rem; color: var(--text-muted, #64748b); line-height: 1.5; margin-bottom: 1.5rem;">
                    Selecione esta opção para enviar um aviso oficial a todos os clientes cadastrados e ativos no sistema informando o fechamento temporário do estabelecimento.
                </p>
                <div style="background: rgba(245, 158, 11, 0.05); border: 1px dashed #f59e0b; border-radius: 8px; padding: 1rem; margin-bottom: 1.5rem; font-size: 0.85rem; color: #b45309; line-height: 1.4;">
                    <i class="bi bi-info-circle-fill" style="margin-right: 0.35rem;"></i>
                    <strong>Nota:</strong> O sistema enviará automaticamente e-mails personalizados e notificações push em massa por meio dos canais oficiais (OneSignal e PHPMailer).
                </div>
            </div>

            <?php if ($isAdmin): ?>
                <button type="button" class="btn-comunicado" id="btnDispararComunicado">
                    <i class="bi bi-send-fill"></i> Notificar Todos os Clientes
                </button>
            <?php else: ?>
                <button type="button" class="btn-comunicado" style="opacity: 0.5; cursor: not-allowed;" title="Apenas administradores podem disparar em lote" disabled>
                    <i class="bi bi-lock-fill"></i> Apenas Admin Geral
                </button>
            <?php endif; ?>
        </div>
    </div>

    <script>
        function atualizarClassesStatus() {
            // Remove classes ativas de ambas as opções
            const options = document.querySelectorAll('.status-option');
            options.forEach(opt => {
                const input = opt.querySelector('input[type="radio"]');
                opt.classList.remove('active-option', 'inactive-option');
                if (input.checked) {
                    if (input.value === 'ativo') {
                        opt.classList.add('active-option');
                    } else {
                        opt.classList.add('inactive-option');
                    }
                }
            });
        }

        // Integração SweetAlert2 para o disparo em lote
        document.addEventListener('DOMContentLoaded', () => {
            const btnDisparar = document.getElementById('btnDispararComunicado');
            if (btnDisparar) {
                btnDisparar.addEventListener('click', () => {
                    Swal.fire({
                        ...window._swalDefaults,
                        title: 'Confirmar Disparo em Massa?',
                        text: 'Esta ação enviará uma notificação push e um e-mail para todos os clientes ativos. Deseja prosseguir?',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Sim, Notificar Todos',
                        cancelButtonText: 'Cancelar'
                    }).then((result) => {
                        if (result.isConfirmed) {
                            // Exibe carregador com design limpo do Belezou App
                            Swal.fire({
                                ...window._swalDefaults,
                                title: 'Enviando Comunicado...',
                                html: 'Processando envios em lote via e-mail e push notification. Por favor, aguarde.',
                                icon: 'info',
                                allowOutsideClick: false,
                                showConfirmButton: false,
                                didOpen: () => {
                                    Swal.showLoading();
                                }
                            });

                            // Faz chamada AJAX para o backend
                            const csrfToken = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
                            
                            fetch('<?= BASE_URL ?>/admin/configuracoes/disparar-comunicado', {
                                method: 'POST',
                                headers: {
                                    'Content-Type': 'application/x-www-form-urlencoded',
                                },
                                body: 'csrf_token=' + encodeURIComponent(csrfToken)
                            })
                            .then(response => response.json())
                            .then(data => {
                                if (data.sucesso) {
                                    Swal.fire({
                                        ...window._swalDefaults,
                                        title: 'Sucesso!',
                                        text: data.mensagem,
                                        icon: 'success',
                                        confirmButtonText: 'Fechar'
                                    });
                                } else {
                                    Swal.fire({
                                        ...window._swalDefaults,
                                        title: 'Erro no Envio',
                                        text: data.mensagem,
                                        icon: 'error',
                                        confirmButtonText: 'Ok'
                                    });
                                }
                            })
                            .catch(error => {
                                console.error('Erro na requisição:', error);
                                Swal.fire({
                                    ...window._swalDefaults,
                                    title: 'Falha Técnica',
                                    text: 'Ocorreu um erro ao processar o envio em lote.',
                                    icon: 'error',
                                    confirmButtonText: 'Ok'
                                });
                            });
                        }
                    });
                });
            }
        });
    </script>

</body>

</html>
