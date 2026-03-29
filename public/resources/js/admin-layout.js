/* =========================================
   ADMIN-LAYOUT.JS - Lógica do Menu, Topbar e Tema
   ========================================= */

document.addEventListener("DOMContentLoaded", () => {
    
    // =========================================
    // 1. LÓGICA DO MENU LATERAL (SIDEBAR)
    // =========================================
    const menuToggle = document.getElementById("menuToggle");
    const sidebar = document.querySelector(".sidebar");
    
    // Cria o overlay (película escura) dinamicamente via JS
    let overlay = document.querySelector(".sidebar-overlay");
    if (!overlay) {
        overlay = document.createElement("div");
        overlay.className = "sidebar-overlay";
        document.body.appendChild(overlay);
    }

    if (menuToggle && sidebar) {
        // TRUQUE MÁGICO: Clona o botão e substitui. 
        // Isto "mata" o evento do ficheiro admin.js antigo, acabando com o conflito!
        const novoMenuToggle = menuToggle.cloneNode(true);
        menuToggle.parentNode.replaceChild(novoMenuToggle, menuToggle);

        // Função para abrir/fechar o menu usando o botão NOVO
        novoMenuToggle.addEventListener("click", () => {
            sidebar.classList.toggle("open");
            overlay.classList.toggle("open");
            
            // Remove a classe antiga por precaução
            sidebar.classList.remove("show"); 
        });
    }

    // Fecha o menu ao clicar na parte escura (overlay)
    if (overlay) {
        overlay.addEventListener("click", () => {
            sidebar.classList.remove("open");
            overlay.classList.remove("open");
            
            // Remove a classe antiga por precaução
            sidebar.classList.remove("show");
        });
    }

   
    // =========================================
    // 2. LÓGICA DO MODO ESCURO (DARK MODE) COM CHAVINHA
    // =========================================
    const themeToggle = document.getElementById("themeToggle");
    const currentTheme = localStorage.getItem("theme") || "light";
    
    // Aplica o tema salvo assim que a página carrega
    if (currentTheme === "dark") {
        document.documentElement.setAttribute("data-theme", "dark");
        if (themeToggle) themeToggle.checked = true; // Deixa a chavinha visualmente "ligada"
    }
    
    if (themeToggle) {
        // Usa "change" em vez de "click" porque a chavinha é um checkbox
        themeToggle.addEventListener("change", function() {
            if (this.checked) {
                document.documentElement.setAttribute("data-theme", "dark");
                localStorage.setItem("theme", "dark");
            } else {
                document.documentElement.removeAttribute("data-theme");
                localStorage.setItem("theme", "light");
            }
        });
    }
});