/*
   FUNCIONARIO.JS - REGRAS DE FUNCIONÁRIOS
*/

// Funções chamadas diretamente pelo HTML da tabela
function confirmarExclusaoFuncionario(id) {
    if (confirm("Deseja realmente remover o acesso deste funcionário (#" + id + ")?")) {
        alert("Funcionário " + id + " seria removido no banco de dados.");
    }
}

// Preenche o modal de edição lendo o atributo data-funcionario do botão
function abrirEdicaoFuncionario(button) {
    const func = JSON.parse(button.getAttribute('data-funcionario'));

    document.getElementById("modalTitleFunc").textContent = "Editar Funcionário: " + func.nome;

    document.getElementById("id_funcionario").value = func.id_funcionario;
    document.getElementById("nome").value = func.nome;
    document.getElementById("telefone").value = func.telefone || "";
    document.getElementById("email").value = func.email;
    document.getElementById("especialidade").value = func.especialidade;
    document.getElementById("salario").value = func.salario;
    document.getElementById("tipo").value = func.tipo || 'comum';

    // Trava a edição do e-mail na atualização (o acesso/email é fixo)
    document.getElementById("email").setAttribute('readonly', true);
    
    // Remove a obrigatoriedade da senha na edição
    const senhaInput = document.getElementById("senha");
    if(senhaInput) senhaInput.removeAttribute('required');
}

// Limpa o modal para preparar um novo cadastro
function limparModalFuncionario() {
    document.getElementById("modalTitleFunc").textContent = "Cadastrar Novo Funcionário";
    document.getElementById("formFuncionario").reset();
    document.getElementById("id_funcionario").value = "";

    // Libera o e-mail para um novo cadastro
    document.getElementById("email").removeAttribute('readonly');
    
    // Torna a senha obrigatória novamente para novos cadastros
    const senhaInput = document.getElementById("senha");
    if(senhaInput) senhaInput.setAttribute('required', true);
}

// Validação do formulário ao enviar
document.addEventListener("DOMContentLoaded", () => {
    const formFuncionario = document.getElementById("formFuncionario");
    const errorMsg = document.getElementById("funcionarioError");

    if (formFuncionario) {
        formFuncionario.addEventListener("submit", function (event) {
            const senhaInput = document.getElementById("senha");
            const senha = senhaInput ? senhaInput.value.trim() : "";
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