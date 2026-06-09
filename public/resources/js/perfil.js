/* =========================================
   PERFIL.JS - INTERAÇÕES DA TELA DE PERFIL
   ========================================= */

// Função para aplicar a Máscara de Telefone Brasileira
function aplicarMascaraTelefone(valor) {
    if (!valor) return "";

    valor = valor.replace(/\D/g, "").slice(0, 11);

    if (valor.length <= 10) {
        valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
        valor = valor.replace(/(\d{4})(\d{1,4})$/, "$1-$2");
    } else {
        valor = valor.replace(/(\d{2})(\d)/, "($1) $2");
        valor = valor.replace(/(\d{5})(\d{1,4})$/, "$1-$2");
    }

    return valor;
}

// Função para Alternar as Abas
function abrirAba(nomeDaAba, botaoClicado) {
    // 1. Esconde todos os conteúdos de aba
    const abasConteudo = document.querySelectorAll('.tab-pane');
    abasConteudo.forEach(aba => {
        aba.classList.remove('active');
    });

    // 2. Remove a cor "roxa/ativa" de todos os botões
    const abasBotoes = document.querySelectorAll('.tab-btn');
    abasBotoes.forEach(btn => {
        btn.classList.remove('active');
    });

    // 3. Mostra o conteúdo da aba selecionada
    const abaSelecionada = document.getElementById(`aba-${nomeDaAba}`);
    if (abaSelecionada) {
        abaSelecionada.classList.add('active');
    }

    // 4. Marca o botão clicado como ativo
    if (botaoClicado) {
        botaoClicado.classList.add('active');
    }
}

// Confirmação para sair da conta (SweetAlert2 — Tema Belezou)
function confirmarSaida(urlSair) {
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
            window.location.href = urlSair;
        }
    });
}

// Inicializações ao carregar a página
document.addEventListener("DOMContentLoaded", () => {
    
    // --- 1. APLICAÇÃO DA MÁSCARA DE TELEFONE ---
    const inputTelefone = document.getElementById("telefone");
    
    if (inputTelefone) {
        // Aplica a máscara enquanto o usuário digita
        inputTelefone.addEventListener("input", function (e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });
        
        // Aplica a máscara no valor que já vem preenchido da base de dados
        inputTelefone.value = aplicarMascaraTelefone(inputTelefone.value);
    }

    // --- 2. VALIDAÇÃO SIMPLES DA TROCA DE SENHA ---
    const formSenha = document.getElementById("formSenha");
    const errorMsg = document.getElementById("senhaError");

    if (formSenha) {
        formSenha.addEventListener("submit", function (event) {
            const novaSenha = document.getElementById("nova_senha").value;
            const confirmaSenha = document.getElementById("confirma_senha").value;

            if (novaSenha !== confirmaSenha) {
                event.preventDefault(); // Impede o envio do formulário
                errorMsg.style.display = "block";
            } else {
                errorMsg.style.display = "none";
            }
        });
    }

    // --- 3. INICIALIZAÇÃO DO FLATPICKR ---
    if (document.getElementById("nascimento")) {
        flatpickr("#nascimento", {
            locale: "pt",
            dateFormat: "Y-m-d",
            altInput: true,
            altFormat: "d/m/Y",
            altInputClass: "form-control flatpickr-alt-input",
            disableMobile: true,
            maxDate: "today"
        });
    }
});