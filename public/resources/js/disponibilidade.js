// Defina estas variáveis no HTML antes de carregar o arquivo JS:
// <script>
//   window.acaoDisponibilidade = <?= json_encode(empty($idDisponibilidade) ? 'criar esta nova' : 'salvar as alterações nesta') ?>;
//   window.isNovaGrade = <?= $isNovaGrade ? 'true' : 'false' ?>;
// </script>

const acaoDisponibilidade = window.acaoDisponibilidade ?? "salvar esta";
const isNovaGrade = window.isNovaGrade ?? false;

function confirmarSalvamento(event) {
    event.preventDefault();

    const form = event.currentTarget || event.target.closest("form");
    if (!form) return;

    if (confirm(`Deseja confirmar e ${acaoDisponibilidade} grade de horários?`)) {
        HTMLFormElement.prototype.submit.call(form);
    }
}

function confirmarExclusaoGrade() {
    const formExcluir = document.getElementById("form-excluir");
    if (!formExcluir) return;

    if (confirm("ATENÇÃO: Tem certeza que deseja EXCLUIR esta grade permanentemente? Esta ação não pode ser desfeita.")) {
        HTMLFormElement.prototype.submit.call(formExcluir);
    }
}

function cancelarNovaGrade() {
    const formCancelar = document.getElementById("form-cancelar");
    if (!formCancelar) return;

    HTMLFormElement.prototype.submit.call(formCancelar);
}

function toggleDayRow(sigla) {
    const checkbox = document.getElementById(`dia_${sigla}`);
    const row = document.getElementById(`row_${sigla}`);

    if (!checkbox || !row) return;

    if (checkbox.checked) {
        row.style.opacity = "1";
        row.style.filter = "none";
        row.style.borderLeft = "4px solid #28a745";
    } else {
        row.style.opacity = "0.5";
        row.style.filter = "grayscale(100%)";
        row.style.borderLeft = "1px solid var(--border-color, #ccc)";
    }
}

function limparPausa(sigla) {
    const campoInicio = document.getElementById(`int_ini_${sigla}`);
    const campoFim = document.getElementById(`int_fim_${sigla}`);

    if (campoInicio) campoInicio.value = "";
    if (campoFim) campoFim.value = "";
}

document.addEventListener("DOMContentLoaded", function () {
    const modalOverlay = document.getElementById("modalEdicaoGrade");

    if (modalOverlay) {
        modalOverlay.addEventListener("click", function (event) {
            if (event.target === modalOverlay && isNovaGrade) {
                cancelarNovaGrade();
            }
        });
    }

    try {
        const scrollPos = sessionStorage.getItem("scrollPosition");
        if (scrollPos !== null) {
            window.scrollTo(0, parseInt(scrollPos, 10) || 0);
            sessionStorage.removeItem("scrollPosition");
        }
    } catch (error) {
        // ignora se sessionStorage estiver indisponível
    }
});

window.addEventListener("beforeunload", function () {
    try {
        sessionStorage.setItem("scrollPosition", String(window.scrollY));
    } catch (error) {
        // ignora se sessionStorage estiver indisponível
    }
});