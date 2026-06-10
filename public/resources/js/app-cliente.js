/* =========================================
   APP-CLIENTE.JS - INTERAÇÕES DO APP
   ========================================= */

document.addEventListener("DOMContentLoaded", () => {
    // Adiciona interatividade no menu inferior
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            // Remove a classe 'active' de todos
            navItems.forEach(nav => nav.classList.remove('active'));
            
            // Adiciona no que foi clicado
            this.classList.add('active');
            
            // O navegador seguirá o href naturalmente logo em seguida.
        });
    });
});

/* =========================================
   APP-CLIENTE.JS - INTERAÇÕES DO APP
   ========================================= */

document.addEventListener("DOMContentLoaded", () => {
    
    // 1. Interatividade do Menu Inferior
    const navItems = document.querySelectorAll('.nav-item');

    navItems.forEach(item => {
        item.addEventListener('click', function() {
            navItems.forEach(nav => nav.classList.remove('active'));
            this.classList.add('active');
        });
    });

  // 2. Lógica do Modo Escuro (Dark Mode) COM CHAVINHA
    const themeToggle = document.getElementById("themeToggle");
    const currentTheme = localStorage.getItem("theme") || "light";
    
    // Verifica qual tema está salvo no PC/Celular do usuário ao carregar a página
    if (currentTheme === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
        if (themeToggle) themeToggle.checked = true; // Deixa a chavinha visualmente ligada
    }
    
    // Evento de mudança (change) na chavinha
    if (themeToggle) {
        themeToggle.addEventListener("change", function() {
            if (this.checked) {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("theme", "dark"); // Salva tema escuro
            } else {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("theme", "light"); // Salva tema claro
            }
        });
    }

    // 3. Dropdown do Perfil do Cliente
    const btnProfile = document.getElementById('btnProfileDropdown');
    const profileMenu = document.getElementById('profileMenu');
    if (btnProfile && profileMenu) {
        btnProfile.addEventListener('click', (e) => {
            e.stopPropagation();
            profileMenu.style.display = profileMenu.style.display === 'block' ? 'none' : 'block';
        });

        document.addEventListener('click', (e) => {
            if (!btnProfile.contains(e.target) && !profileMenu.contains(e.target)) {
                profileMenu.style.display = 'none';
            }
        });
    }
});

// Confirmação global para sair da conta (SweetAlert2 — Tema Belezou)
window.confirmarSaida = window.confirmarSaida || function(urlSair) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            text:              'Tem certeza que deseja sair do aplicativo?',
            icon:              'question',
            showCancelButton:  true,
            confirmButtonText: 'Sim, sair',
            cancelButtonText:  'Cancelar',
            customClass: {
                popup:         'swal-belezou-popup',
                title:         'swal-belezou-title',
                htmlContainer: 'swal-belezou-text',
                confirmButton: 'swal-belezou-btn-confirm',
                cancelButton:  'swal-belezou-btn-cancel',
                icon:          'swal-belezou-icon'
            },
            buttonsStyling: false,
            showClass: { popup: 'swal-belezou-show' },
            hideClass: { popup: 'swal-belezou-hide' }
        }).then(result => {
            if (result.isConfirmed) {
                Swal.showLoading();
                window.location.href = urlSair;
            }
        });
    } else {
        if (confirm('Tem certeza que deseja sair do aplicativo?')) {
            window.location.href = urlSair;
        }
    }
};

// Fecha seletores de data (Flatpickr) ao rolar a página ou containers de modais
window.addEventListener('scroll', function() {
    document.querySelectorAll('.flatpickr-input').forEach(el => {
        if (el._flatpickr && el._flatpickr.isOpen) {
            el._flatpickr.close();
        }
    });
}, true);