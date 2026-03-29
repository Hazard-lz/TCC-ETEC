/*
   FUNCIONARIO.JS - REGRAS DE FUNCIONÁRIOS
   */

// Funções chamadas diretamente pelo HTML da tabela
function confirmarExclusaoFuncionario(id) {
    if (confirm("Deseja realmente remover o acesso deste funcionário (#" + id + ")?")) {
        alert("Funcionário " + id + " seria removido no banco de dados.");
    }
}

function preencherModalEdicaoFuncionario(id, nome, telefone, email, especialidade, salario, tipo) {
    // Muda o título do modal
    document.getElementById("modalTitleFunc").textContent = "Editar Funcionário: " + nome;
    
    // Preenche os campos do formulário
    document.getElementById("id_funcionario").value = id;
    document.getElementById("nome").value = nome;
    document.getElementById("telefone").value = telefone;
    document.getElementById("email").value = email;
    document.getElementById("especialidade").value = especialidade;
    document.getElementById("salario").value = salario;
    document.getElementById("tipo").value = tipo;
    
    // Bloqueia a edição do e-mail e remove a obrigatoriedade da senha
    document.getElementById("email").setAttribute('readonly', true);
    document.getElementById("senha").removeAttribute('required');
}

// Validação do formulário ao enviar
document.addEventListener("DOMContentLoaded", () => {
    const formFuncionario = document.getElementById("formFuncionario");
    const errorMsg = document.getElementById("funcionarioError");

    if (formFuncionario) {
        formFuncionario.addEventListener("submit", function (event) {
            const senha = document.getElementById("senha").value.trim();
            const salario = parseFloat(document.getElementById("salario").value);
            const idFuncionario = document.getElementById("id_funcionario").value;
            
            let errorMessage = "";

            // Se for cadastro novo (ID vazio), a senha é obrigatória e deve ter 6 caracteres
            if (idFuncionario === "" && senha.length < 6) {
                errorMessage = "A senha temporária deve ter pelo menos 6 caracteres.";
            } 
            // Se for edição e o usuário digitou algo na senha, também valida
            else if (idFuncionario !== "" && senha.length > 0 && senha.length < 6) {
                errorMessage = "A nova senha deve ter pelo menos 6 caracteres.";
            }
            // Valida o salário
            else if (isNaN(salario) || salario < 0) {
                errorMessage = "O salário não pode ser negativo.";
            }

            // Exibe erro ou deixa passar
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