    <?php
    // Bloqueia acesso de não logados
    if (!isset($_SESSION['usuario_id'])) {
        header("Location: " . BASE_URL . "/login");
        exit;
    }

    $clienteModel = new Cliente();
    $dadosCliente = $clienteModel->buscarPorCodUsuario($_SESSION['usuario_id']);

    $clienteNome = $_SESSION['usuario_nome'];
    $clienteEmail = $_SESSION['usuario_email'];
    $clienteTelefone = $_SESSION['usuario_telefone'];
    $clienteNascimento = $dadosCliente ? $dadosCliente['data_nascimento'] : '';

    // Lógica para saber qual aba deve vir aberta por padrão
    $abaAtiva = 'dados';
    if (isset($_SESSION['sucesso_senha']) || isset($_SESSION['erro_senha'])) {
        $abaAtiva = 'senha';
    }
    ?>
    <!DOCTYPE html>
    <html lang="pt-BR">

    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
        <title>Meu Perfil - Belezou App</title>

        <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
        <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/perfil.css">
    </head>

    <body>

        <div class="app-wrapper">
            <div class="mobile-container">

            <header class="app-header" style="justify-content: space-between; align-items: center;">
        <div style="width: 50px;"></div>
        <h2 style="color: var(--text-main); font-size: 1.2rem; margin: 0;">Meu Perfil</h2>
        <label class="theme-switch" title="Alternar Modo Escuro" style="margin: 0;">
            <input type="checkbox" id="themeToggle">
            <span class="slider"></span>
        </label>
    </header>

                <main class="app-content">

                    <div class="profile-header">
                        <div class="profile-avatar-large">
                            <?= strtoupper(substr($clienteNome, 0, 1)) ?>
                        </div>
                        <div class="profile-name"><?= htmlspecialchars($clienteNome) ?></div>
                        <div class="profile-email"><?= htmlspecialchars($clienteEmail) ?></div>
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

                                    <form id="formDados" action="<?= BASE_URL ?>/cliente/atualizar" method="POST">
                                        <div class="form-group">
                                            <label for="nome">Nome Completo</label>
                                            <input type="text" id="nome" name="nome" class="form-control" value="<?= htmlspecialchars($clienteNome) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="telefone">Telefone / WhatsApp</label>
                                            <input type="tel" id="telefone" name="telefone" class="form-control" value="<?= htmlspecialchars($clienteTelefone) ?>" required>
                                        </div>

                                        <div class="form-group">
                                            <label for="nascimento">Data de Nascimento</label>
                                            <input type="date" id="nascimento" name="nascimento" class="form-control" value="<?= $clienteNascimento ?>" required>
                                        </div>

                                        <button type="submit" class="btn-primary" style="padding: 0.8rem; font-size: 0.95rem;">Salvar Dados</button>
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

                                        <button type="submit" class="btn-secondary" style="padding: 0.8rem; font-size: 0.95rem;">Alterar Senha</button>
                                    </form>
                                </div>
                            </div>

                        </div> </div> <button class="btn-logout" onclick="confirmarSaida()">
                        <span>🚪</span> Sair da Conta
                    </button>

                </main>

                <nav class="bottom-nav">
                    <a href="<?= BASE_URL ?>/" class="nav-item">
                        <span class="nav-icon">🏠</span><span>Início</span>
                    </a>
                    <a href="<?= BASE_URL ?>/agendar" class="nav-item">
                        <span class="nav-icon">📅</span><span>Agendar</span>
                    </a>
                    <a href="<?= BASE_URL ?>/historico" class="nav-item">
                        <span class="nav-icon">🕒</span><span>Histórico</span>
                    </a>
                    <a href="<?= BASE_URL ?>/perfil" class="nav-item active">
                        <span class="nav-icon">👤</span><span>Perfil</span>
                    </a>
                </nav>

            </div>
        </div>


        <script src="<?= BASE_URL ?>/public/resources/js/perfil.js"></script>
        <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
    </body>

    </html>