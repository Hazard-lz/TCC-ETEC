<?php
// Bloqueia acesso de não logados ou de usuários que não sejam funcionários/admins
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_tipo'], ['funcionario', 'admin'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}

// Busca os dados complementares do funcionário no banco
require_once __DIR__ . '/../../../app/Models/Funcionario.php';
$funcionarioModel = new Funcionario();
$dadosFuncionario = $funcionarioModel->buscarPorCodUsuario($_SESSION['usuario_id']);

$funcNome = $_SESSION['usuario_nome'];
$funcEmail = $_SESSION['usuario_email'];
$funcTelefone = $dadosFuncionario ? $dadosFuncionario['telefone'] : '';
$funcEspecialidade = $dadosFuncionario ? $dadosFuncionario['especialidade'] : 'Não informada';

// Lógica para saber qual aba deve vir aberta por padrão (Dados ou Senha)
$abaAtiva = 'dados';
if (isset($_SESSION['sucesso_senha']) || isset($_SESSION['erro_senha'])) {
    $abaAtiva = 'senha';
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Meu Perfil - Belezou App</title>
    
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/perfil.css">
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <div class="dashboard-header" style="margin-bottom: 2rem; display: flex; justify-content: space-between; align-items: center;">
        <div>
            <h2>Meu Perfil 👤</h2>
            <p>Gerencie suas informações pessoais e credenciais de acesso do salão.</p>
        </div>
        
    </div>

    <div class="base-card" style="max-width: 800px; margin: 0 auto;">
        
        <div class="profile-header">
            <div class="profile-avatar-large">
                <?= strtoupper(substr($funcNome, 0, 1)) ?>
            </div>
            <div class="profile-name"><?= htmlspecialchars($funcNome) ?></div>
            <div class="profile-email"><?= htmlspecialchars($funcEmail) ?></div>
            <div style="margin-top: 0.5rem; display: inline-block; padding: 0.3rem 1rem; background: rgba(139, 92, 246, 0.1); color: var(--color-purple); border-radius: 20px; font-size: 0.85rem; font-weight: 600;">
                Especialidade: <?= htmlspecialchars($funcEspecialidade) ?>
            </div>
        </div>

        <div class="tabs-container">
            <div class="tabs-header">
                <button type="button" class="tab-btn <?= $abaAtiva === 'dados' ? 'active' : '' ?>" onclick="abrirAba('dados', this)">👤 Dados Gerais</button>
                <button type="button" class="tab-btn <?= $abaAtiva === 'senha' ? 'active' : '' ?>" onclick="abrirAba('senha', this)">🔒 Segurança</button>
            </div>

            <div class="tab-content">
                
                <div id="aba-dados" class="tab-pane <?= $abaAtiva === 'dados' ? 'active' : '' ?>">
                    <div class="profile-section">
                        <?php if (isset($_SESSION['sucesso_perfil'])): ?>
                            <div style="color: #15803d; background-color: #dcfce7; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
                                <?= htmlspecialchars($_SESSION['sucesso_perfil']) ?>
                            </div>
                            <?php unset($_SESSION['sucesso_perfil']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['erro_perfil'])): ?>
                            <div style="color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
                                <?= htmlspecialchars($_SESSION['erro_perfil']) ?>
                            </div>
                            <?php unset($_SESSION['erro_perfil']); ?>
                        <?php endif; ?>

                        <form id="formDados" action="<?= BASE_URL ?>/funcionario/atualizarPerfil" method="POST">
                            <div class="form-group">
                                <label for="nome">Nome Completo</label>
                                <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($funcNome) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="telefone">Telefone / WhatsApp</label>
                                <input type="tel" id="telefone" name="telefone" class="form-control" value="<?= htmlspecialchars($funcTelefone) ?>" required>
                            </div>

                            <div class="form-group">
                                <label for="email">E-mail de Acesso</label>
                                <input type="email" id="email" class="form-control" value="<?= htmlspecialchars($funcEmail) ?>" readonly style="background-color: var(--bg-body); cursor: not-allowed;" title="O e-mail não pode ser alterado por aqui.">
                            </div>

                            <button type="submit" class="btn-primary" style="padding: 0.8rem; font-size: 0.95rem; width: 100%;">Atualizar Meus Dados</button>
                        </form>
                    </div>
                </div>

                <div id="aba-senha" class="tab-pane <?= $abaAtiva === 'senha' ? 'active' : '' ?>">
                    <div class="profile-section">
                        <?php if (isset($_SESSION['sucesso_senha'])): ?>
                            <div style="color: #15803d; background-color: #dcfce7; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
                                <?= htmlspecialchars($_SESSION['sucesso_senha']) ?>
                            </div>
                            <?php unset($_SESSION['sucesso_senha']); ?>
                        <?php endif; ?>

                        <?php if (isset($_SESSION['erro_senha'])): ?>
                            <div style="color: #dc2626; background-color: #fee2e2; padding: 10px; border-radius: 8px; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">
                                <?= htmlspecialchars($_SESSION['erro_senha']) ?>
                            </div>
                            <?php unset($_SESSION['erro_senha']); ?>
                        <?php endif; ?>

                        <form id="formSenha" action="<?= BASE_URL ?>/auth/trocarSenha" method="POST">
                            <div class="form-group">
                                <label for="senha_atual">Senha Atual</label>
                                <input type="password" id="senha_atual" name="senha_atual" class="form-control" placeholder="Digite sua senha atual" required>
                            </div>

                            <div class="form-group">
                                <label for="nova_senha">Nova Senha</label>
                                <input type="password" id="nova_senha" name="nova_senha" class="form-control" placeholder="Mínimo de 8 caracteres" required>
                            </div>

                            <div class="form-group">
                                <label for="confirma_senha">Confirme a Nova Senha</label>
                                <input type="password" id="confirma_senha" name="confirma_senha" class="form-control" placeholder="Repita a nova senha" required>
                            </div>

                            <div id="senhaError" class="error-message" style="display: none; color: #dc2626; margin-bottom: 1rem; font-size: 0.9rem; text-align: center;">As senhas não coincidem.</div>

                            <button type="submit" class="btn-secondary" style="padding: 0.8rem; font-size: 0.95rem; width: 100%;">Alterar Minha Senha</button>
                        </form>
                    </div>
                </div>

            </div>
        </div>
        
        <button class="btn-logout" onclick="confirmarSaida()" style="max-width: 100%;">
            <span>🚪</span> Sair da Conta
        </button>

    </div>

    <script src="<?= BASE_URL ?>/public/resources/js/perfil.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
</body>
</html>