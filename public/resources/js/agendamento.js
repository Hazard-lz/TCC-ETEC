/* =========================================
   AGENDAMENTO.JS - REGRAS DO CALENDÁRIO
   ========================================= */

// Função Global chamada pelo bloco colorido no calendário (HTML)
function verDetalhes(cliente, servico, horario, status) {
    document.getElementById('detalhesCliente').textContent = cliente;
    document.getElementById('detalhesServico').textContent = servico;
    document.getElementById('detalhesHorario').textContent = horario;
    document.getElementById('detalhesStatus').textContent = status;
    
    // Usa a função genérica que criamos no modal.js
    window.openModal('#modalDetalhes');
}

// Validação do formulário de Novo Agendamento
document.addEventListener("DOMContentLoaded", () => {
    const formAgendamento = document.getElementById("formAgendamento");

    if (formAgendamento) {
        formAgendamento.addEventListener("submit", function (event) {
            const dataAgendamento = document.getElementById("data_agendamento").value;
            const horaInicio = document.getElementById("hora_inicio").value;
            
            let errorMessage = "";

            // Validação de Expediente: O salão funciona das 09:00 às 18:00
            if (horaInicio < "09:00" || horaInicio > "18:00") {
                errorMessage = "O horário de início deve estar entre 09:00 e 18:00.";
            } 
            // Validação simples de data: não permitir agendar no passado (opcional)
            else if (dataAgendamento !== "") {
                const dataEscolhida = new Date(dataAgendamento + "T" + horaInicio);
                const dataHoje = new Date();
                
                if (dataEscolhida < dataHoje) {
                    errorMessage = "Não é possível criar um novo agendamento no passado.";
                }
            }

            const errorDiv = document.getElementById("agendamentoError");

if (errorMessage !== "") {
    event.preventDefault();
    errorDiv.textContent = errorMessage;
    errorDiv.style.display = "block";
} else {
    errorDiv.style.display = "none";
}

            // ... código anterior (validação do formAgendamento) ...

    // Validação do formulário de Bloqueio de Horário
    const formBloqueio = document.getElementById("formBloqueio");
    const bloqueioError = document.getElementById("bloqueioError");

    if (formBloqueio) {
        formBloqueio.addEventListener("submit", function (event) {
            const horaInicio = document.getElementById("bloqueio_inicio").value;
            const horaFim = document.getElementById("bloqueio_fim").value;
            
            let errorMessage = "";

            // O sistema só deve permitir bloquear se o horário de fim for DEPOIS do início
            if (horaInicio >= horaFim) {
                errorMessage = "O horário de término deve ser posterior ao horário de início.";
            }

            if (errorMessage !== "") {
                event.preventDefault(); // Bloqueia o envio do formulário
                bloqueioError.textContent = errorMessage;
                bloqueioError.style.display = "block";
            } else {
                bloqueioError.style.display = "none";
            }
        });
    }
        });
    }
});