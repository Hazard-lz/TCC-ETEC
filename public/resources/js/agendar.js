/* =========================================
   AGENDAR.JS - LÓGICA DO PASSO A PASSO
   ========================================= */

// Função para avançar de passo
function irParaPasso(passoAtual, proximoPasso) {
  // Esconde o atual
  document.getElementById(`step-${passoAtual}`).classList.remove("active");
  // Mostra o próximo
  document.getElementById(`step-${proximoPasso}`).classList.add("active");

  // Atualiza os indicadores (bolinhas lá em cima)
  document.getElementById(`ind-${passoAtual}`).classList.add("completed");
  document.getElementById(`ind-${proximoPasso}`).classList.add("active");
}

// Função para voltar de passo
function voltarPasso(passoAnterior) {
  const passoAtual = passoAnterior + 1;

  document.getElementById(`step-${passoAtual}`).classList.remove("active");
  document.getElementById(`step-${passoAnterior}`).classList.add("active");

  document.getElementById(`ind-${passoAnterior}`).classList.remove("completed");
  document.getElementById(`ind-${passoAtual}`).classList.remove("active");
}

/* --- LÓGICA DO PASSO 1: SERVIÇOS --- */
function selecionarServico(id, nome) {
  // Salva nos inputs ocultos
  document.getElementById("servico_id").value = id;
  document.getElementById("servico_nome").value = nome;

  // Tira a classe 'selected' de todos os cartões
  const cards = document.querySelectorAll("#step-1 .selectable-card");
  cards.forEach((card) => card.classList.remove("selected"));

  // Pinta o cartão clicado
  event.currentTarget.classList.add("selected");

  // Libera o botão "Continuar"
  document.getElementById("btn-next-1").removeAttribute("disabled");

  // EXPLICAÇÃO: Aqui estava o bug! Temos de chamar a API para buscar
  // os profissionais logo que o serviço é escolhido.
  buscarProfissionais(id);
}

async function buscarProfissionais(idServico) {
  const container = document.getElementById("container-profissionais");
  container.innerHTML =
    '<p style="color: var(--text-muted); text-align: center;">A buscar especialistas...</p>';

  try {
    const response = await fetch(
      `${BASE_URL}/api/profissionais-por-servico?id_servico=${idServico}`,
    );
    const data = await response.json();

    container.innerHTML = ""; // Limpa a mensagem

    if (data.sucesso && data.profissionais.length > 0) {
      data.profissionais.forEach((prof) => {
        const especialidade = prof.especialidade
          ? prof.especialidade
          : "Profissional";
        const div = document.createElement("div");
        div.className = "base-card selectable-card";
        div.style = "padding: 1rem; margin-bottom: 0.8rem; cursor: pointer;";

        div.onclick = function () {
          selecionarProfissional(prof.id_funcionario, prof.nome);
        };

        div.innerHTML = `
                    <h4 style="color: var(--text-main); font-size: 1.1rem; margin-bottom: 0.3rem;">${prof.nome}</h4>
                    <p style="color: var(--text-muted); font-size: 0.9rem;">${especialidade}</p>
                `;
        container.appendChild(div);
      });
    } else {
      container.innerHTML =
        '<p style="color: #ef4444; text-align: center;">Não há profissionais para este serviço.</p>';
      // Bloqueia o botão se a API retornar vazio
      document.getElementById("btn-next-1").disabled = true;
    }
  } catch (error) {
    console.error("Erro na API:", error);
    container.innerHTML =
      '<p style="color: #ef4444; text-align: center;">Erro ao carregar profissionais.</p>';
  }
}

// Evento do botão avançar 1
document.getElementById("btn-next-1").addEventListener("click", () => {
  irParaPasso(1, 2);
});

/* --- LÓGICA DO PASSO 2: PROFISSIONAIS --- */
function selecionarProfissional(id, nome) {
  document.getElementById("funcionario_id").value = id;
  document.getElementById("funcionario_nome").value = nome;

  const cards = document.querySelectorAll("#step-2 .selectable-card");
  cards.forEach((card) => card.classList.remove("selected"));

  event.currentTarget.classList.add("selected");
  document.getElementById("btn-next-2").removeAttribute("disabled");
}

document.getElementById("btn-next-2").addEventListener("click", () => {
  irParaPasso(2, 3);

  // Configura o calendário para não permitir datas passadas
  const hoje = new Date().toISOString().split("T")[0];
  document.getElementById("data_agendamento").setAttribute("min", hoje);
});

/* --- LÓGICA DO PASSO 3: DATA E HORA --- */
async function liberarHorarios() {
    const dataSelecionada = document.getElementById("data_agendamento").value;
    const idServico = document.getElementById("servico_id").value;
    const idFuncionario = document.getElementById("funcionario_id").value;

    const boxHorarios = document.getElementById("box-horarios");
    const containerHorarios = document.getElementById("container-horarios-dinamicos");
    const btnNext3 = document.getElementById("btn-next-3");

    boxHorarios.style.display = "block";
    document.getElementById("horario_selecionado").value = "";
    btnNext3.setAttribute("disabled", "true");

    containerHorarios.innerHTML = "<p>A calcular horários disponíveis...</p>";

    if (!dataSelecionada || !idServico || !idFuncionario) return;

    try {
        const response = await fetch(BASE_URL + "/api/horarios-livres", {
            method: "POST",
            headers: { "Content-Type": "application/json" },
            body: JSON.stringify({
                id_funcionario: idFuncionario,
                data: dataSelecionada,
                id_servico: idServico,
            }),
        });
        
        const data = await response.json();
        containerHorarios.innerHTML = "";

        if (data.sucesso && data.horarios.length > 0) {
            data.horarios.forEach((hora) => {
                const horaFormatada = hora.substring(0, 5); 
                const div = document.createElement("div");
                div.className = "time-slot";
                div.textContent = horaFormatada;
                
                div.onclick = function () {
                    selecionarHorario(hora, this);
                };
                containerHorarios.appendChild(div);
            });
        } else {
            containerHorarios.innerHTML = '<p style="color: red;">Nenhum horário disponível.</p>';
        }
    } catch (error) {
        console.error("Erro na API de Disponibilidade:", error);
        containerHorarios.innerHTML = "<p>Erro ao carregar horários.</p>";
    }
}

function selecionarHorario(hora, elemento) {
  document.getElementById("horario_selecionado").value = hora;

  const slots = document.querySelectorAll(".time-slot");
  slots.forEach((s) => s.classList.remove("selected"));

  elemento.classList.add("selected");
  document.getElementById("btn-next-3").removeAttribute("disabled");
}

/* --- LÓGICA DO PASSO 4: RESUMO --- */
function montarResumo() {
  irParaPasso(3, 4);

  const servico = document.getElementById("servico_nome").value;
  const profissional = document.getElementById("funcionario_nome").value;
  const dataBruta = document.getElementById("data_agendamento").value;
  const hora = document.getElementById("horario_selecionado").value;

  // Formata a data (de YYYY-MM-DD para DD/MM/YYYY)
  const partesData = dataBruta.split("-");
  const dataFormatada = `${partesData[2]}/${partesData[1]}/${partesData[0]}`;

  // Joga os textos na tela final
  document.getElementById("resumo_servico").textContent = servico;
  document.getElementById("resumo_pro").textContent = profissional;
  document.getElementById("resumo_datahora").textContent =
    `${dataFormatada} às ${hora}`;
}
