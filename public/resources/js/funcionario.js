/*
   FUNCIONARIO.JS - REGRAS DE FUNCIONÁRIOS (Admin Único)
*/

function confirmarExclusaoFuncionario(id, nome) {
    Swal.fire({
        title: 'Atenção',
        text: `Deseja realmente remover o acesso do funcionário "${nome}"?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc3545',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Confirmar',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            alert("Funcionário " + id + " seria removido no banco de dados.");
        }
    });
}

function abrirEdicaoFuncionario(button) {

    const func = JSON.parse(button.getAttribute("data-funcionario"));
    const isLogado = button.getAttribute("data-is-logado") === "true";

    document.getElementById("modalTitleFunc").textContent = "Editar Funcionário: " + func.nome;

    document.getElementById("id_funcionario").value = func.id_funcionario;
    document.getElementById("nome").value = func.nome;
    document.getElementById("telefone").value = func.telefone || "";
    document.getElementById("email").value = func.email;
    document.getElementById("especialidade").value = func.especialidade;
    document.getElementById("salario").value = func.salario;

    const selectTipo = document.getElementById("tipo");

    // Limpa os bloqueios anteriores (se existirem)
    const hiddenTipo = document.getElementById("hidden_tipo_lock");
    if (hiddenTipo) hiddenTipo.remove();
    
    const fakeInput = document.getElementById("fake_tipo_lock");
    if (fakeInput) fakeInput.remove();

    // ═══ REGRA: ADMIN ÚNICO ═══
    const optionAdmin = document.getElementById("optionAdmin");

    if (isLogado && func.tipo === "admin") {
        // CASO 1: Admin editando A SI MESMO → Campo trancado
        selectTipo.style.display = "none";

        const inputFalso = document.createElement("input");
        inputFalso.type = "text";
        inputFalso.id = "fake_tipo_lock";
        inputFalso.className = "form-control";
        inputFalso.value = "Administrador (Transfira o cargo para outro funcionário)";
        inputFalso.readOnly = true;
        inputFalso.style.backgroundColor = "var(--bg-disabled)";
        inputFalso.style.cursor = "not-allowed";
        inputFalso.style.color = "var(--text-muted)";
        inputFalso.style.opacity = "0.8";
        inputFalso.title = "Para deixar de ser admin, transfira o cargo editando outro funcionário.";
        
        selectTipo.parentNode.insertBefore(inputFalso, selectTipo.nextSibling);

        // Input hidden para manter o tipo admin no envio do form
        const inputEscondido = document.createElement("input");
        inputEscondido.type = "hidden";
        inputEscondido.id = "hidden_tipo_lock";
        inputEscondido.name = "tipo";
        inputEscondido.value = "admin";
        document.getElementById("formFuncionario").appendChild(inputEscondido);

    } else {
        // CASO 2: Admin editando OUTRO funcionário → Mostra select normalmente
        selectTipo.style.display = "block";
        selectTipo.value = func.tipo || "comum";

        // Mostra a opção "Transferir Admin" apenas se o logado for admin
        if (optionAdmin) {
            optionAdmin.style.display = "block";
        }
    }

    // Trava a edição do e-mail na atualização
    document.getElementById("email").setAttribute("readonly", "true");
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

    // Esconde a opção admin no novo cadastro (admin só via transferência)
    const optionAdmin = document.getElementById("optionAdmin");
    if (optionAdmin) {
        optionAdmin.style.display = "none";
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
            let errorMessage = "";
            
            if (salarioInput.trim() !== "") {
                const salario = parseFloat(salarioInput.replace(',', '.'));
                if (isNaN(salario) || salario < 0) {
                    errorMessage = "O salário não pode ser negativo ou inválido.";
                }
            }

            if (errorMessage !== "") {
                event.preventDefault(); 
                errorMsg.textContent = errorMessage;
                errorMsg.style.display = "block";
                return;
            }

            // ═══ CONFIRMAÇÃO DE TRANSFERÊNCIA DE ADMIN ═══
            const selectTipo = document.getElementById("tipo");
            if (selectTipo && selectTipo.value === "admin") {
                event.preventDefault(); // Pausa o envio
                const nomeFuncionario = document.getElementById("nome").value;
                Swal.fire({
                    title: 'Atenção',
                    text: "⚠️ ATENÇÃO: TRANSFERÊNCIA DE CARGO\n\nAo promover \"" + nomeFuncionario + "\" a Administrador, você PERDERÁ seu cargo de Admin e se tornará Subadministrador.\n\nEssa ação é imediata. Deseja continuar?",
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#dc3545',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Confirmar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        // Fecha o modal visualmente para não sobrepor o alerta ou o processamento
                        const modal = document.querySelector('#modalFuncionario');
                        if (modal) modal.classList.remove('active');
                        document.body.classList.remove('modal-open');

                        HTMLFormElement.prototype.submit.call(formFuncionario);
                    }
                });
                return;
            }

            errorMsg.style.display = "none";
        });
    }
});