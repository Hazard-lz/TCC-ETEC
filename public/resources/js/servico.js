/* =========================================
   SERVICO.JS - INTEGRAÇÃO COM A API
   ========================================= */

// Função para ativar/inativar
function alterarStatusServico(id, status) {
    const acao = status === 'inativo' ? 'inativar' : 'ativar';
    if (confirm(`Deseja realmente ${acao} o serviço #${id}?`)) {
        fetch(`${BASE_URL}/admin/servicos/status`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ id_servico: id, status: status })
        })
        .then(res => res.json())
        .then(data => {
            // CORREÇÃO: Lendo 'data.sucesso' em vez de 'data.status'
            if(data.sucesso === true || data.status === 'sucesso') {
                alert(data.mensagem);
                window.location.reload(); // Recarrega a página automaticamente
            } else {
                alert('Erro: ' + data.mensagem);
            }
        })
        .catch(err => console.error("Erro na comunicação:", err));
    }
}

// Preenche o modal ao clicar em Editar
function preencherModalEdicao(id, nome, descricao, preco, duracao, status) {
    document.getElementById("modalTitle").textContent = "Editar Serviço: " + nome;
    document.getElementById("id_servico").value = id;
    document.getElementById("nome_servico").value = nome;
    document.getElementById("descricao").value = descricao;
    document.getElementById("preco").value = preco;
    document.getElementById("duracao").value = duracao;
    document.getElementById("status").value = status;
}

// Limpa o modal ao clicar em "Novo Serviço"
function limparModalServico() {
    document.getElementById("modalTitle").textContent = "Cadastrar Novo Serviço";
    document.getElementById("id_servico").value = "";
    document.getElementById("formServico").reset();
    document.getElementById("servicoError").style.display = "none";
}

document.addEventListener("DOMContentLoaded", () => {
    const formServico = document.getElementById("formServico");
    const errorMsg = document.getElementById("servicoError");

    if (formServico) {
        formServico.addEventListener("submit", function (event) {
            event.preventDefault();

            const id = document.getElementById("id_servico").value;
            const nome = document.getElementById("nome_servico").value.trim();
            const descricao = document.getElementById("descricao").value.trim();
            const preco = parseFloat(document.getElementById("preco").value);
            const duracao = parseInt(document.getElementById("duracao").value);
            const status = document.getElementById("status").value;

            let errorMessage = "";

            if (nome === "" || descricao === "") {
                errorMessage = "O nome e a descrição do serviço são obrigatórios.";
            } else if (isNaN(preco) || preco < 0) {
                errorMessage = "Insira um preço válido (maior ou igual a zero).";
            } else if (isNaN(duracao) || duracao <= 0 || duracao % 5 !== 0) {
                errorMessage = "A duração deve ser de pelo menos 1 minuto e em blocos de 5 (ex: 15, 30, 45).";
            }

            if (errorMessage !== "") {
                errorMsg.textContent = errorMessage;
                errorMsg.style.display = "block";
                return;
            } 
            
            errorMsg.style.display = "none";

            const payload = {
                id_servico: id,
                nome_servico: nome,
                descricao: descricao,
                preco: preco,
                duracao: duracao,
                status: status
            };

            fetch(`${BASE_URL}/admin/servicos/salvar`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            })
            .then(response => response.json())
            .then(data => {
                // CORREÇÃO: Lendo 'data.sucesso' em vez de 'data.status'
                if (data.sucesso === true || data.status === 'sucesso') {
                    alert(data.mensagem);
                    window.location.reload(); // Recarrega a página automaticamente para exibir a edição/criação
                } else {
                    errorMsg.textContent = data.mensagem;
                    errorMsg.style.display = "block";
                }
            })
            .catch(error => {
                console.error("Erro:", error);
                errorMsg.textContent = "Erro de conexão com o servidor.";
                errorMsg.style.display = "block";
            });
        });
    }
});