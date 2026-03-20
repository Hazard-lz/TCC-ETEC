/* =========================================
   MODAL.JS - GENÉRICO
   ========================================= */

// Função Global para Abrir Modal
window.openModal = function(modalSelector) {
    const modal = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
    if (modal == null) return;
    modal.classList.add('active');
    document.body.style.overflow = 'hidden'; // Trava rolagem do fundo
};

// Função Global para Fechar Modal
window.closeModal = function(modalSelector) {
    const modal = typeof modalSelector === 'string' ? document.querySelector(modalSelector) : modalSelector;
    if (modal == null) return;
    modal.classList.remove('active');
    document.body.style.overflow = 'auto'; // Devolve rolagem
    
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