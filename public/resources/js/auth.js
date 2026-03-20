// Aguarda o DOM (estrutura do HTML) carregar completamente
document.addEventListener("DOMContentLoaded", () => {
  // Seleciona o formulário e a div de erro pelo ID
  const loginForm = document.getElementById("loginForm");
  const errorMsg = document.getElementById("loginError");

  // Intercepta o evento de 'submit' (envio) do formulário
  loginForm.addEventListener("submit", function (event) {
    // Pega os valores dos inputs
    const email = document.getElementById("email").value.trim();
    const senha = document.getElementById("senha").value.trim();

    let hasError = false;

    // Validação simples: verifica se os campos estão vazios
    if (email === "" || senha === "") {
      hasError = true;
    }

    // Se houver erro, impede o envio para o PHP e mostra a mensagem
    if (hasError) {
      event.preventDefault(); // Cancela o recarregamento da página
      errorMsg.style.display = "block"; // Mostra a mensagem de erro
      errorMsg.textContent = "E-mail e senha são obrigatórios.";
    } else {
      // Se estiver tudo ok, esconde o erro e o form segue para o action ("/login/autenticar")
      errorMsg.style.display = "none";
    }
  });
});
