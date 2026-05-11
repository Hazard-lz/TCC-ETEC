/* =========================================
   HISTORICO.JS - LÓGICA DAS ABAS
   ========================================= */

function mudarAba(abaId, elementoClicado) {
    // 1. Tira a classe 'active' de todas as abas (conteúdos)
    const conteudos = document.querySelectorAll('.tab-content');
    conteudos.forEach(conteudo => {
        conteudo.classList.remove('active');
    });

    // 2. Tira a classe 'active' de todos os botões
    const botoes = document.querySelectorAll('.tab-btn');
    botoes.forEach(botao => {
        botao.classList.remove('active');
    });

    // 3. Adiciona a classe 'active' na aba escolhida e no botão clicado
    document.getElementById(`aba-${abaId}`).classList.add('active');
    elementoClicado.classList.add('active');
}

// ── Confirmação de cancelamento de agendamento (SweetAlert2 — Tema Belezou) ──
function cancelarAgendamento(idAgendamento) {
    Swal.fire({
        title:             'Cancelar Agendamento?',
        text:              'Esta ação não pode ser desfeita. Tem certeza que deseja cancelar?',
        icon:              'warning',
        showCancelButton:  true,
        confirmButtonText: 'Sim, cancelar',
        cancelButtonText:  'Não, manter',
        customClass: {
            popup:         'swal-belezou-popup',
            title:         'swal-belezou-title',
            htmlContainer: 'swal-belezou-text',
            confirmButton: 'swal-belezou-btn-danger',
            cancelButton:  'swal-belezou-btn-cancel',
            icon:          'swal-belezou-icon'
        },
        buttonsStyling: false,
        showClass: { popup: 'swal-belezou-show' },
        hideClass: { popup: 'swal-belezou-hide' }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById(`form-cancelar-${idAgendamento}`).submit();
        }
    });
}