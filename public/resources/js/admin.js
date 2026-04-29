document.addEventListener("DOMContentLoaded", () => {
  const menuToggle = document.getElementById("menuToggle");
  const sidebar = document.querySelector(".sidebar");

  // Real-time search filter for tables
  const inputPesquisa = document.querySelectorAll(".input-pesquisa-tabela");
  inputPesquisa.forEach(input => {
    input.addEventListener("input", function() {
      const termo = this.value.toLowerCase();
      // Find the closest table related to this input, usually next to it or in a container
      const table = this.closest('.base-card')?.querySelector('.data-table tbody') || document.querySelector('.data-table tbody');
      
      if (table) {
        const rows = table.querySelectorAll('tr');
        rows.forEach(row => {
          const textoLinha = row.textContent.toLowerCase();
          if (textoLinha.includes(termo)) {
            row.style.display = ""; // default display for tr
          } else {
            row.style.display = "none";
          }
        });
      } else {
        // Fallback for checkbox list (e.g. meus servicos)
        const labels = this.closest('.base-card')?.querySelectorAll('label');
        if (labels) {
          labels.forEach(label => {
            const textoLabel = label.textContent.toLowerCase();
            if (textoLabel.includes(termo)) {
              label.style.display = "flex";
            } else {
              label.style.display = "none";
            }
          });
        }
      }
    });
  });
});
