/* =========================================
   PERFIL.JS - INTERAÇÕES DA TELA DE PERFIL
   ========================================= */

// Função para aplicar a Máscara de Telefone Brasileira
function aplicarMascaraTelefone(valor) {
    if (!valor) return "";
    valor = valor.replace(/\D/g, ""); // Remove tudo o que não é dígito
    valor = valor.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em volta do DDD
    valor = valor.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos
    valor = valor.substring(0, 15);// Limita a 15 caracteres (tamanho máximo da string formatada)
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

// Confirmação para sair da conta
function confirmarSaida() {
    if (confirm("Tem certeza que deseja sair do aplicativo?")) {
        // Redireciona para a rota configurada no index.php
        // Usa o caminho relativo a partir da raiz do projeto para evitar erros
        window.location.href = window.location.origin + "/TCC-ETEC/login/sair"; 
    }
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
});