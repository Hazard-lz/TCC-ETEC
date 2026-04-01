/* =========================================
   CLIENTE.JS - REGRAS DE CLIENTES
   ========================================= */

// Defina isso no HTML antes de carregar o cliente.js, por exemplo:
// <script>window.isAdmin = <?= $isAdmin ? 'true' : 'false' ?>;</script>

const isAdmin = window.isAdmin ?? false;

// Chamado pelo botão de lixeira na tabela
function confirmarExclusaoCliente(id) {
    if (confirm("Tem certeza que deseja remover o cliente #" + id + " do sistema?")) {
        alert("Cliente " + id + " seria excluído no banco de dados.");
    }
}

// Função única para preencher modal de edição
function preencherModalEdicaoCliente(id_cliente, id_usuario, nome, telefone, email, nascimento, observacoes) {
    const campoIdCliente = document.getElementById("id_cliente");
    const campoIdUsuario = document.getElementById("id_usuario");
    const campoNome = document.getElementById("nome");
    const campoTelefone = document.getElementById("telefone");
    const campoEmail = document.getElementById("email");
    const campoNascimento = document.getElementById("nascimento");
    const campoObservacoes = document.getElementById("observacoes");
    const modalTitle = document.getElementById("modalTitleCliente");
    const containerNascimento = document.getElementById("container_nascimento");
    const containerObservacoes = document.getElementById("container_observacoes");

    if (campoIdCliente) campoIdCliente.value = id_cliente ?? "";
    if (campoIdUsuario) campoIdUsuario.value = id_usuario ?? "";
    if (campoNome) campoNome.value = nome ?? "";
    if (campoTelefone) campoTelefone.value = aplicarMascaraTelefone(telefone ?? "");
    if (campoEmail) campoEmail.value = email ?? "";
    if (campoNascimento) {
        campoNascimento.value = nascimento || "";
        campoNascimento.disabled = false;
    }
    if (campoObservacoes) {
        campoObservacoes.value = observacoes || "";
        campoObservacoes.disabled = false;
    }

    if (modalTitle) {
        modalTitle.innerText = isAdmin ? "Editar Cliente" : "Observações do Cliente";
    }

    if (containerNascimento) containerNascimento.style.display = "block";
    if (containerObservacoes) containerObservacoes.style.display = "block";

    if (!isAdmin) {
        if (campoNome) campoNome.readOnly = true;
        if (campoTelefone) campoTelefone.readOnly = true;
    } else {
        if (campoNome) campoNome.readOnly = false;
        if (campoTelefone) campoTelefone.readOnly = false;
    }
}

// Limpa modal
function limparModalCliente() {
    const formCliente = document.getElementById("formCliente");
    const campoIdCliente = document.getElementById("id_cliente");
    const campoIdUsuario = document.getElementById("id_usuario");
    const campoNome = document.getElementById("nome");
    const campoTelefone = document.getElementById("telefone");
    const campoEmail = document.getElementById("email");
    const campoNascimento = document.getElementById("nascimento");
    const campoObservacoes = document.getElementById("observacoes");
    const errorMsg = document.getElementById("clienteError");

    if (formCliente) formCliente.reset();
    if (campoIdCliente) campoIdCliente.value = "";
    if (campoIdUsuario) campoIdUsuario.value = "";
    if (campoNascimento) campoNascimento.disabled = false;
    if (campoObservacoes) campoObservacoes.disabled = false;
    if (campoNome) {
        campoNome.readOnly = false;
        campoNome.style.backgroundColor = "";
    }
    if (campoTelefone) {
        campoTelefone.readOnly = false;
        campoTelefone.style.backgroundColor = "";
    }
    if (campoEmail) {
        campoEmail.readOnly = false;
        campoEmail.style.backgroundColor = "";
    }
    if (errorMsg) {
        errorMsg.textContent = "";
        errorMsg.style.display = "none";
    }
}

// Abre cadastro rápido
function abrirCadastroRapido() {
    const formCliente = document.getElementById("formCliente");
    const campoIdCliente = document.getElementById("id_cliente");
    const campoIdUsuario = document.getElementById("id_usuario");
    const modalTitle = document.getElementById("modalTitleCliente");
    const containerNascimento = document.getElementById("container_nascimento");
    const campoNascimento = document.getElementById("nascimento");
    const containerObservacoes = document.getElementById("container_observacoes");
    const campoObservacoes = document.getElementById("observacoes");
    const campoNome = document.getElementById("nome");
    const campoTelefone = document.getElementById("telefone");
    const errorMsg = document.getElementById("clienteError");

    if (formCliente) formCliente.reset();
    if (campoIdCliente) campoIdCliente.value = "";
    if (campoIdUsuario) campoIdUsuario.value = "";

    if (modalTitle) modalTitle.innerText = "Cadastro Rápido";

    if (containerNascimento) containerNascimento.style.display = "none";
    if (campoNascimento) {
        campoNascimento.value = "";
        campoNascimento.disabled = true;
    }

    if (containerObservacoes) containerObservacoes.style.display = "none";
    if (campoObservacoes) {
        campoObservacoes.value = "";
        campoObservacoes.disabled = true;
    }

    if (campoNome) {
        campoNome.readOnly = false;
        campoNome.style.backgroundColor = "";
    }

    if (campoTelefone) {
        campoTelefone.readOnly = false;
        campoTelefone.style.backgroundColor = "";
        campoTelefone.value = aplicarMascaraTelefone(campoTelefone.value);
    }

    if (errorMsg) {
        errorMsg.textContent = "";
        errorMsg.style.display = "none";
    }
}

// Máscara de telefone brasileiro
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

document.addEventListener("DOMContentLoaded", () => {
    const formCliente = document.getElementById("formCliente");
    const errorMsg = document.getElementById("clienteError");
    const inputTelefone = document.getElementById("telefone");

    if (inputTelefone) {
        inputTelefone.addEventListener("input", function (e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });

        inputTelefone.value = aplicarMascaraTelefone(inputTelefone.value);
    }

    if (formCliente) {
        formCliente.addEventListener("submit", function (event) {
            const senhaInput = document.getElementById("senha");
            const nascimentoInput = document.getElementById("nascimento");
            const idClienteInput = document.getElementById("id_cliente");

            const senha = senhaInput ? senhaInput.value.trim() : "";
            const idCliente = idClienteInput ? idClienteInput.value : "";
            const nascimento = nascimentoInput ? nascimentoInput.value : "";

            let errorMessage = "";

            if (idCliente === "" && senha.length > 0 && senha.length < 6) {
                errorMessage = "Se definir uma senha, ela deve ter pelo menos 6 caracteres.";
            } else if (nascimento !== "") {
                const dataNasc = new Date(nascimento + "T00:00:00");
                const hoje = new Date();
                hoje.setHours(0, 0, 0, 0);

                if (dataNasc > hoje) {
                    errorMessage = "A data de nascimento não pode estar no futuro.";
                }
            }

            if (errorMsg) {
                if (errorMessage !== "") {
                    event.preventDefault();
                    errorMsg.textContent = errorMessage;
                    errorMsg.style.display = "block";
                } else {
                    errorMsg.textContent = "";
                    errorMsg.style.display = "none";
                }
            }
        });
    }
});