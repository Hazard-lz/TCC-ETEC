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

    // 2. Lógica do Modo Escuro (Dark Mode)
    const themeToggle = document.getElementById("themeToggle");
    const currentTheme = localStorage.getItem("theme") || "light";
    
    // Verifica qual tema está salvo no PC/Celular do usuário ao carregar a página
    if (currentTheme === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
    }
    
    // Evento de clique no botão flutuante
    if (themeToggle) {
        themeToggle.addEventListener("click", () => {
            let theme = document.documentElement.getAttribute("data-theme");
            
            if (theme === "dark") {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("theme", "light"); // Salva tema claro
            } else {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("theme", "dark"); // Salva tema escuro
            }
        });
    }
});