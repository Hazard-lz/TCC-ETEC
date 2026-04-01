/*
   FUNCIONARIO.JS - REGRAS DE FUNCIONÁRIOS
*/

function confirmarExclusaoFuncionario(id) {
    if (confirm("Deseja realmente remover o acesso deste funcionário (#" + id + ")?")) {
        alert("Funcionário " + id + " seria removido no banco de dados.");
    }
}

function abrirEdicaoFuncionario(button) {

    
    const func = JSON.parse(button.getAttribute("data-funcionario"));
    const isLogado = button.getAttribute("data-is-logado") === "true";
    const totalAdmins = parseInt(button.getAttribute("data-total-admins"));

    document.getElementById("modalTitleFunc").textContent = "Editar Funcionário: " + func.nome;

    document.getElementById("id_funcionario").value = func.id_funcionario;
    document.getElementById("nome").value = func.nome;
    document.getElementById("telefone").value = func.telefone || "";
    document.getElementById("email").value = func.email;
    document.getElementById("especialidade").value = func.especialidade;
    document.getElementById("salario").value = func.salario;

    const selectTipo = document.getElementById("tipo");
    selectTipo.value = func.tipo || "comum";

    // Trava a edição do e-mail na atualização
    document.getElementById("email").setAttribute("readonly", "true");

    // Limpa os bloqueios anteriores (se existirem)
    const hiddenTipo = document.getElementById("hidden_tipo_lock");
    if (hiddenTipo) hiddenTipo.remove();
    
    const fakeInput = document.getElementById("fake_tipo_lock");
    if (fakeInput) fakeInput.remove();  

    // REGRA DE SEGURANÇA: Se for o único admin, bloqueia o rebaixamento
    if (isLogado && func.tipo === "admin" && totalAdmins <= 1) {
        
        // 1. Esconde completamente o Select real (impossível clicar)
        selectTipo.style.display = "none";

        // 2. Cria um campo de texto falso, visualmente trancado
        const inputFalso = document.createElement("input");
        inputFalso.type = "text";
        inputFalso.id = "fake_tipo_lock";
        inputFalso.className = "form-control";
        inputFalso.value = "Administrador (Acesso Fixo)";
        inputFalso.readOnly = true;
        inputFalso.style.backgroundColor = "#e2e8f0";
        inputFalso.style.cursor = "not-allowed";
        inputFalso.style.color = "#4a5568";
        inputFalso.title = "Você é o único administrador ativo no sistema. Não é possível remover este acesso.";
        
        // Insere o campo falso logo após o Select escondido
        selectTipo.parentNode.insertBefore(inputFalso, selectTipo.nextSibling);

        // 3. Cria o input oculto para enviar o cargo 'admin' para o banco de dados
        const inputEscondido = document.createElement("input");
        inputEscondido.type = "hidden";
        inputEscondido.id = "hidden_tipo_lock";
        inputEscondido.name = "tipo";
        inputEscondido.value = "admin";
        document.getElementById("formFuncionario").appendChild(inputEscondido);
        
    } else {
        // Se for outro utilizador ou houver mais admins, mostra o Select normalmente
        selectTipo.style.display = "block";
    }
}

function limparModalFuncionario() {
    document.getElementById("modalTitleFunc").textContent = "Cadastrar Novo Funcionário";
    document.getElementById("formFuncionario").reset();
    document.getElementById("id_funcionario").value = "";

    // Libera o e-mail para um novo cadastro
    document.getElementById("email").removeAttribute("readonly");

    // Restaura o Select para a criação de um novo funcionário
    const selectTipo = document.getElementById("tipo");
    if (selectTipo) {
        selectTipo.style.display = "block";
    }

    // Limpa as travas
    const hiddenTipo = document.getElementById("hidden_tipo_lock");
    if (hiddenTipo) hiddenTipo.remove();
    
    const fakeInput = document.getElementById("fake_tipo_lock");
    if (fakeInput) fakeInput.remove();
}

document.addEventListener("DOMContentLoaded", () => {
    const formFuncionario = document.getElementById("formFuncionario");
    const errorMsg = document.getElementById("funcionarioError");

    if (formFuncionario) {
        formFuncionario.addEventListener("submit", function (event) {
            const salarioInput = document.getElementById("salario").value;
            const salario = parseFloat(salarioInput.replace(',', '.'));

            let errorMessage = "";

            if (isNaN(salario) || salario < 0) {
                errorMessage = "O salário não pode ser negativo ou inválido.";
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