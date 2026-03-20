<aside class="sidebar">
    <div class="sidebar-header">
        <img src="<?= BASE_URL ?>/public/resources/images/Belezou.png" alt="Belezou App Logo" class="sidebar-logo">    
    </div>

    <nav class="sidebar-nav">
        <ul>
            <li><a href="<?= BASE_URL ?>/funcionario/dashboard" class="nav-link">Painel Inicial</a></li>
            <li><a href="<?= BASE_URL ?>/funcionario/agendamentos" class="nav-link">Agendamentos</a></li>
            <li><a href="<?= BASE_URL ?>/funcionario/servicos" class="nav-link">Meus Serviços</a></li>
            <li><a href="<?= BASE_URL ?>/funcionario/clientes" class="nav-link">Clientes</a></li>
            <li><a href="<?= BASE_URL ?>/funcionario/disponibilidade" class="nav-link">Disponibilidade</a></li>

            <?php if ($_SESSION['usuario_tipo'] === 'admin'): ?>
                <li style="margin-top: 1rem; padding-left: 1.5rem; font-size: 0.8rem; font-weight: bold; color: #a0aec0; text-transform: uppercase;">Administração</li>
                <li><a href="<?= BASE_URL ?>/admin/servicos" class="nav-link">Catálogo de Serviços</a></li>
                <li><a href="<?= BASE_URL ?>/admin/funcionarios" class="nav-link">Funcionários</a></li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= BASE_URL ?>/login/sair" class="nav-link logout-link">Sair do Sistema</a>
    </div>
</aside>