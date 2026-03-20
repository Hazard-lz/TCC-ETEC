/* =========================================
   CADASTRO.JS - VALIDAÇÃO DE NOVA CONTA
   ========================================= */

// Função para aplicar a Máscara de Telefone Brasileira
function aplicarMascaraTelefone(valor) {
    if (!valor) return "";
    valor = valor.replace(/\D/g, ""); // Remove tudo o que não é dígito
    valor = valor.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em volta do DDD
    valor = valor.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos
    valor = valor.substring(0, 15); // Limita a 15 caracteres
    return valor;
}

// Função para capitalizar a primeira letra do Nome e Sobrenome
function capitalizarNome(valor) {
    if (!valor) return "";
    
    // Lista de preposições comuns em nomes que devem continuar minúsculas
    const preposicoes = ["de", "da", "do", "das", "dos"];
    
    return valor.split(" ").map((palavra, index) => {
        if (palavra.length > 0) {
            const palavraMinuscula = palavra.toLowerCase();
            // Se for preposição (e não for a primeira palavra do nome), mantém minúscula
            if (preposicoes.includes(palavraMinuscula) && index !== 0) {
                return palavraMinuscula;
            }
            // Coloca a primeira letra maiúscula e o resto minúsculo
            return palavra.charAt(0).toUpperCase() + palavra.slice(1).toLowerCase();
        }
        return "";
    }).join(" ");
}

document.addEventListener("DOMContentLoaded", () => {
    const cadastroForm = document.getElementById("cadastroForm");
    const errorMsg = document.getElementById("cadastroError");

    // --- 1. APLICAÇÃO DA MÁSCARA DE TELEFONE ---
    const inputTelefone = document.getElementById("telefone");
    if (inputTelefone) {
        inputTelefone.addEventListener("input", function (e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });
        // Formata logo de início caso o navegador preencha automaticamente
        inputTelefone.value = aplicarMascaraTelefone(inputTelefone.value);
    }

    // --- 2. CAPITALIZAÇÃO DO NOME ---
    const inputNome = document.getElementById("nome");
    if (inputNome) {
        inputNome.addEventListener("input", function (e) {
            e.target.value = capitalizarNome(e.target.value);
        });
    }

    // --- 3. VALIDAÇÃO DO FORMULÁRIO ---
    if (cadastroForm) {
        cadastroForm.addEventListener("submit", function (event) {
            const email = document.getElementById("email").value;
            const confirmaEmail = document.getElementById("confirma_email").value;
            const senha = document.getElementById("senha").value;
            const confirmaSenha = document.getElementById("confirma_senha").value;

            let errorMessage = "";
            
            // Validações
            if (email !== confirmaEmail) {
                errorMessage = "Os e-mails informados não coincidem.";
            } else if (senha.length < 8) {
                errorMessage = "A senha deve ter no mínimo 8 caracteres.";
            } else if (senha !== confirmaSenha) {
                errorMessage = "As senhas não coincidem. Verifique a digitação.";
            }

            if (errorMessage !== "") {
                event.preventDefault(); 
                errorMsg.textContent = errorMessage;
                errorMsg.style.display = "block";
            } else {
                errorMsg.style.display = "none";
            }
        });
    }
});