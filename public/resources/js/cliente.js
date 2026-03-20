/* =========================================
   CLIENTE.JS - REGRAS DE CLIENTES
   ========================================= */

// Chamado pelo botão de lixeira na tabela
function confirmarExclusaoCliente(id) {
    if (confirm("Tem certeza que deseja remover o cliente #" + id + " do sistema?")) {
        alert("Cliente " + id + " seria excluído no banco de dados.");
    }
}

// Chamado pelo botão de lápis na tabela
function preencherModalEdicaoCliente(id, nome, telefone, email, nascimento, observacoes) {
    // Atualiza título
    document.getElementById("modalTitleCliente").textContent = "Editar Cliente: " + nome;
    
    // Preenche os inputs
    document.getElementById("id_cliente").value = id;
    document.getElementById("nome").value = nome;
    document.getElementById("email").value = email;
    document.getElementById("nascimento").value = nascimento;
    document.getElementById("observacoes").value = observacoes;
    
    // Preenche o telefone já aplicando a máscara
    const inputTelefone = document.getElementById("telefone");
    if (inputTelefone) {
        inputTelefone.value = aplicarMascaraTelefone(telefone);
    }
    
    // Na edição, a senha não é obrigatória
    document.getElementById("senha").removeAttribute('required');
}

// Função para aplicar a Máscara de Telefone Brasileira
function aplicarMascaraTelefone(valor) {
    if (!valor) return "";
    valor = valor.replace(/\D/g, ""); // Remove tudo o que não é dígito
    valor = valor.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em volta do DDD
    valor = valor.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos
    valor = valor.substring(0, 15);// Limita a 11 dígitos
    return valor;
}

// Validação e aplicação da máscara
document.addEventListener("DOMContentLoaded", () => {
    const formCliente = document.getElementById("formCliente");
    const errorMsg = document.getElementById("clienteError");
    const inputTelefone = document.getElementById("telefone");

    // Aplica a máscara no input de telefone enquanto o usuário digita
    if (inputTelefone) {
        inputTelefone.addEventListener("input", function (e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });
        
        // Força a máscara caso o campo já venha preenchido pelo navegador
        inputTelefone.value = aplicarMascaraTelefone(inputTelefone.value);
    }

    if (formCliente) {
        formCliente.addEventListener("submit", function (event) {
            const senha = document.getElementById("senha").value.trim();
            const idCliente = document.getElementById("id_cliente").value;
            const nascimento = document.getElementById("nascimento").value;
            
            let errorMessage = "";

            // Se for cadastro novo, valida a senha do cliente
            if (idCliente === "" && senha.length > 0 && senha.length < 6) {
                errorMessage = "Se definir uma senha, ela deve ter pelo menos 6 caracteres.";
            } 
            // Valida se a data de nascimento não está no futuro
            else if (nascimento !== "") {
                const dataNasc = new Date(nascimento);
                const dataHoje = new Date();
                if (dataNasc > dataHoje) {
                    errorMessage = "A data de nascimento não pode estar no futuro.";
                }
            }

            // Exibe o erro ou passa
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