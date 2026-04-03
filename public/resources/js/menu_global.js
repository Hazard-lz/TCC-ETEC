// ==========================================
// ARQUIVO: public/resources/js/menu_global.js
// ==========================================

const BASE_URL = '/TCC-ETEC'; // Altere se o caminho do seu servidor for diferente

document.addEventListener("DOMContentLoaded", () => {
    const userString = localStorage.getItem('belezou_user');
    const isPublicPage = window.location.pathname.includes('login') || window.location.pathname.includes('cadastro');
    
    if (!userString && !isPublicPage) {
        window.location.href = BASE_URL + "/public/views/auth/login.php";
        return;
    }

    if (userString && !isPublicPage) {
        const usuarioLogado = JSON.parse(userString);
        renderizarLayoutGlobal(usuarioLogado);
    }
});

function renderizarLayoutGlobal(usuario) {
    const isAdmin = usuario.tipo === 'admin';
    const primeiroNome = usuario.nome ? usuario.nome.split(' ')[0] : 'Usuário';
    const inicialNome = usuario.nome ? usuario.nome.charAt(0).toUpperCase() : 'U';
    const pathAtual = window.location.pathname;

    let navLinks = `
        <li class="nav-item"><a href="${BASE_URL}/funcionario/dashboard" class="nav-link ${pathAtual.includes('dashboard') ? 'active' : ''}" title="Painel Inicial"><i class="bi bi-house me-2"></i> <span>Painel Inicial</span></a></li>
        <li class="nav-item"><a href="${BASE_URL}/funcionario/agenda" class="nav-link ${pathAtual.includes('agendamentos') ? 'active' : ''}" title="Agendamentos"><i class="bi bi-calendar-check me-2"></i> <span>Agendamentos</span></a></li>
        <li class="nav-item"><a href="${BASE_URL}/funcionario/servicos" class="nav-link ${pathAtual.includes('servicos') && !pathAtual.includes('admin') ? 'active' : ''}" title="Meus Serviços"><i class="bi bi-scissors me-2"></i> <span>Meus Serviços</span></a></li>
        <li class="nav-item"><a href="${BASE_URL}/funcionario/clientes" class="nav-link ${pathAtual.includes('clientes') ? 'active' : ''}" title="Clientes"><i class="bi bi-people me-2"></i> <span>Clientes</span></a></li>
        <li class="nav-item"><a href="${BASE_URL}/funcionario/disponibilidade" class="nav-link ${pathAtual.includes('disponibilidade') ? 'active' : ''}" title="Disponibilidade"><i class="bi bi-clock me-2"></i> <span>Disponibilidade</span></a></li>
    `;

    if (isAdmin) {
        navLinks += `
            <li class="nav-title" style="margin-top: 1rem; padding-left: 1.5rem; font-size: 0.75rem; font-weight: bold; color: rgba(255,255,255,0.6); text-transform: uppercase; letter-spacing: 1px;">Administração</li>
            <li class="nav-item"><a href="${BASE_URL}/admin/servicos" class="nav-link ${pathAtual.includes('/admin/servicos') ? 'active' : ''}" title="Catálogo de Serviços"><i class="bi bi-card-checklist me-2"></i> <span>Catálogo de Serviços</span></a></li>
            <li class="nav-item"><a href="${BASE_URL}/admin/funcionarios" class="nav-link ${pathAtual.includes('funcionarios') ? 'active' : ''}" title="Funcionários"><i class="bi bi-person-badge me-2"></i> <span>Funcionários</span></a></li>
        `;
    }

    // NOVA ESTRUTURA BASEADA NO MENU_GLOBAL DE REFERÊNCIA
    const layoutHTML = `
        <div class="sidebar" id="sidebar-global">
            <div class="sidebar-header">
                <img src="${BASE_URL}/public/resources/images/Belezou.png" alt="Belezou App Logo" class="sidebar-logo" onerror="this.style.display='none'">    
            </div>
            
            <ul class="sidebar-nav">
                ${navLinks}
            </ul>

            <div class="sidebar-footer">
                <div class="user-avatar">${inicialNome}</div>
                <div class="user-info">
                    <h6>${primeiroNome}</h6>
                    <small>${usuario.tipo.toUpperCase()}</small>
                </div>
            </div>
        </div>

        <div class="main-wrapper" id="main-wrapper-global">
            <header class="topbar" id="topbar-global">
                <div style="display: flex; align-items: center; gap: 15px;">
                    <button id="menuToggle" class="btn-toggle-menu" title="Recolher/Expandir Menu">
                        <i class="bi bi-list"></i>
                    </button>
                        
                </div>
                
                <div class="user-profile" style="display: flex; align-items: center; gap: 1rem;">
                    <label class="theme-switch" title="Alternar Modo Escuro">
                        <input type="checkbox" id="themeToggle">
                        <span class="slider"></span>
                    </label>
                    <span class="nome-topo" style="color: var(--text-main); font-weight: 500;">${primeiroNome}</span> 
                    
                    <div style="position: relative;">
                        <div id="btnProfileDropdown" class="avatar" style="width: 40px; height: 40px; border-radius: 50%; background: var(--gradient-brand, linear-gradient(135deg, #8b5cf6, #ec4899)); color: white; display: flex; align-items: center; justify-content: center; font-weight: bold; font-size: 1.2rem; cursor: pointer;">
                            ${inicialNome}
                        </div>
                        
                        <div id="profileMenu" class="profile-menu shadow" style="display: none; position: absolute; right: 0; top: 50px; background: var(--surface-color); border: 1px solid var(--border-color); border-radius: 8px; width: 180px; z-index: 1000; overflow: hidden;">
                            <a href="${BASE_URL}/funcionario/perfil" class="profile-dropdown-item"><i class="bi bi-person me-2"></i> Editar Perfil</a>
                            <a href="javascript:void(0)" onclick="fazerLogout()" class="profile-dropdown-item text-danger"><i class="bi bi-box-arrow-right me-2"></i> Sair do Sistema</a>
                        </div>
                    </div>
                </div>
            </header>
            
            <div class="sidebar-overlay" id="sidebar-overlay"></div>
            
            <main class="main-content" id="main-content-global">
                <div class="content-area" id="content-area-global"></div>
            </main>
        </div>
    `;

    document.body.insertAdjacentHTML('afterbegin', layoutHTML);

    const conteudoTemporario = document.getElementById('conteudo-temporario');
    const contentArea = document.getElementById('content-area-global');

    if (conteudoTemporario && contentArea) {
        conteudoTemporario.style.display = 'block';
        while (conteudoTemporario.childNodes.length > 0) {
            contentArea.appendChild(conteudoTemporario.childNodes[0]);
        }
        conteudoTemporario.remove();
    }

    iniciarEventosLayout();
}

function iniciarEventosLayout() {
    const menuToggle = document.getElementById('menuToggle');
    const sidebar = document.getElementById('sidebar-global');
    const mainWrapper = document.getElementById('main-wrapper-global');
    const overlay = document.getElementById('sidebar-overlay');
    const themeToggle = document.getElementById('themeToggle');
    
    const btnProfile = document.getElementById('btnProfileDropdown');
    const profileMenu = document.getElementById('profileMenu');

    // 1. Menu Recolhível (Mesma lógica do ficheiro de referência)
    if (window.innerWidth > 992 && localStorage.getItem('belezou_sidebar_collapsed') === 'true') {
        sidebar.classList.add('collapsed');
        mainWrapper.classList.add('expanded');
    }

    if (menuToggle && sidebar && mainWrapper) {
        menuToggle.addEventListener('click', () => {
            if (window.innerWidth > 992) {
                // Desktop: Alterna entre expandido e recolhido
                sidebar.classList.toggle('collapsed');
                mainWrapper.classList.toggle('expanded');
                localStorage.setItem('belezou_sidebar_collapsed', sidebar.classList.contains('collapsed'));
            } else {
                // Mobile: Menu por cima (Overlay)
                sidebar.classList.add('mobile-open');
                overlay.classList.add('open');
            }
        });

        overlay.addEventListener('click', () => {
            sidebar.classList.remove('mobile-open');
            overlay.classList.remove('open');
        });
    }

    // 2. Submenu do Perfil
    if (btnProfile && profileMenu) {
        btnProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.style.display = profileMenu.style.display === 'none' ? 'block' : 'none';
        });

        document.addEventListener('click', (e) => {
            if (!btnProfile.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.style.display = 'none';
            }
        });
    }

    // 3. Modo Escuro
    if (themeToggle) {
        const currentTheme = localStorage.getItem('theme') || 'light';
        if (currentTheme === 'dark') {
            document.documentElement.setAttribute('data-theme', 'dark');
            themeToggle.checked = true;
        }

        themeToggle.addEventListener('change', (e) => {
            if (e.target.checked) {
                document.documentElement.setAttribute('data-theme', 'dark');
                localStorage.setItem('theme', 'dark');
            } else {
                document.documentElement.removeAttribute('data-theme');
                localStorage.setItem('theme', 'light');
            }
        });
    }
}

function fazerLogout() {
    if(confirm("Deseja realmente sair do sistema?")) {
        localStorage.removeItem('belezou_user');
        window.location.href = BASE_URL + "/login/sair";
    }
}