/* =========================================
   MODAL.JS - GENÉRICO
   ========================================= */

// Variável para armazenar a posição do scroll
let scrollPosition = 0;

// Função Global para Abrir Modal
window.openModal = function(modalSelector) {
    const modal = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
    if (modal == null) return;
    
    // Salva a posição atual do scroll
    scrollPosition = window.pageYOffset;
    
    modal.classList.add('active');
    
    // Trava a rolagem de forma robusta para mobile
    document.body.classList.add('modal-open');
    document.body.style.top = `-${scrollPosition}px`;
};

// Função Global para Fechar Modal
window.closeModal = function(modalSelector) {
    const modal = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
    if (modal == null) return;
    
    modal.classList.remove('active');
    
    // Destrava a rolagem
    document.body.classList.remove('modal-open');
    document.body.style.top = '';
    window.scrollTo(0, scrollPosition);
    
    // Limpa o formulário dentro do modal ao fechar
    const form = modal.querySelector('form');
    if(form) form.reset();
};

document.addEventListener('DOMContentLoaded', () => {
    const openModalButtons = document.querySelectorAll('[data-modal-target]');
    const closeModalButtons = document.querySelectorAll('[data-close-modal]');
    const overlays = document.querySelectorAll('.modal-overlay');

    // Evento de Abrir
    openModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            openModal(button.dataset.modalTarget);
        });
    });

    // Evento de Fechar pelos botões (X ou Cancelar)
    closeModalButtons.forEach(button => {
        button.addEventListener('click', () => {
            const modal = button.closest('.modal-overlay');
            closeModal(modal);
        });
    });

    // Evento de Fechar clicando no fundo escuro
    overlays.forEach(overlay => {
        overlay.addEventListener('click', (event) => {
            if (event.target === overlay) {
                closeModal(overlay);
            }
        });
    });
});