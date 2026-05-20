/* =========================================
   CADASTRO.JS - VALIDAÇÃO DE NOVA CONTA
   ========================================= */

// Função para aplicar a Máscara de Telefone Brasileira
function aplicarMascaraTelefone(valor) {
    if (!valor) return "";
    valor = valor.replace(/\D/g, ""); // Remove tudo o que não é dígito
    valor = valor.replace(/(\d{2})(\d)/, "($1) $2"); // Coloca parênteses em volta do DDD
    valor = valor.replace(/(\d)(\d{4})$/, "$1-$2"); // Coloca hífen entre o quarto e o quinto dígitos
    valor = valor.substring(0, 15); // Limita a 15 caracteres
    return valor;
}

// Função para capitalizar a primeira letra do Nome e Sobrenome
function capitalizarNome(valor) {
    if (!valor) return "";
    
    // Lista de preposições comuns em nomes que devem continuar minúsculas
    const preposicoes = ["de", "da", "do", "das", "dos"];
    
    return valor.split(" ").map((palavra, index) => {
        if (palavra.length > 0) {
            const palavraMinuscula = palavra.toLowerCase();
            // Se for preposição (e não for a primeira palavra do nome), mantém minúscula
            if (preposicoes.includes(palavraMinuscula) && index !== 0) {
                return palavraMinuscula;
            }
            // Coloca a primeira letra maiúscula e o resto minúsculo
            return palavra.charAt(0).toUpperCase() + palavra.slice(1).toLowerCase();
        }
        return "";
    }).join(" ");
}

document.addEventListener("DOMContentLoaded", () => {
    const cadastroForm = document.getElementById("cadastroForm");
    const errorMsg = document.getElementById("cadastroError");

    // --- 1. APLICAÇÃO DA MÁSCARA DE TELEFONE ---
    const inputTelefone = document.getElementById("telefone");
    if (inputTelefone) {
        inputTelefone.addEventListener("input", function (e) {
            e.target.value = aplicarMascaraTelefone(e.target.value);
        });
        // Formata logo de início caso o navegador preencha automaticamente
        inputTelefone.value = aplicarMascaraTelefone(inputTelefone.value);
    }

    // --- 2. CAPITALIZAÇÃO DO NOME ---
    const inputNome = document.getElementById("nome");
    if (inputNome) {
        inputNome.addEventListener("input", function (e) {
            e.target.value = capitalizarNome(e.target.value);
        });
    }

    // --- 3. VALIDAÇÃO DO FORMULÁRIO ---
    if (cadastroForm) {
        cadastroForm.addEventListener("submit", function (event) {
            const email = document.getElementById("email").value;
            const confirmaEmail = document.getElementById("confirma_email").value;
            const senha = document.getElementById("senha").value;
            const confirmaSenha = document.getElementById("confirma_senha").value;
            const termos = document.getElementById("termos");

            let errorMessage = "";
            
            // Validações
            if (email !== confirmaEmail) {
                errorMessage = "Os e-mails informados não coincidem.";
            } else if (senha.length < 8) {
                errorMessage = "A senha deve ter no mínimo 8 caracteres.";
            } else if (senha !== confirmaSenha) {
                errorMessage = "As senhas não coincidem. Verifique a digitação.";
            } else if (termos && !termos.checked) {
                errorMessage = "Você deve concordar com os Termos de Uso e a Política de Privacidade para se cadastrar.";
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

// --- FUNÇÕES GLOBAIS PARA EXIBIÇÃO DE TERMOS E LGPD COM SWEETALERT2 ---
function mostrarTermosDeUso() {
    const swalOptions = window._swalDefaults || {
        customClass: {
            popup: 'swal-belezou-popup',
            title: 'swal-belezou-title',
            htmlContainer: 'swal-belezou-text',
            confirmButton: 'swal-belezou-btn-confirm'
        },
        buttonsStyling: false,
        showClass: { popup: 'swal-belezou-show' },
        hideClass: { popup: 'swal-belezou-hide' }
    };

    Swal.fire({
        ...swalOptions,
        title: 'Termos de Uso — Belezou App',
        html: `
            <div style="text-align: left; max-height: 280px; overflow-y: auto; padding-right: 8px; font-size: 0.9rem; line-height: 1.55;">
                <p style="margin-bottom: 0.75rem;">Ao utilizar o aplicativo <strong>Belezou App</strong> para realizar seus agendamentos de beleza, você declara estar ciente e concordar com os termos abaixo:</p>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">1. AGENDAMENTO E PONTUALIDADE</h4>
                <p style="margin-bottom: 0.75rem;">Os horários reservados são exclusivos para você com o profissional escolhido. Solicitamos a gentileza de comparecer ao estabelecimento com pelo menos <strong>5 minutos de antecedência</strong> para evitar atrasos na grade de atendimento.</p>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">2. POLÍTICA DE CANCELAMENTO</h4>
                <p style="margin-bottom: 0.75rem;">Entendemos que imprevistos acontecem. Você poderá realizar o cancelamento do seu agendamento sem qualquer custo diretamente pelo aplicativo (na aba Histórico) com no mínimo <strong>24 horas de antecedência</strong>.</p>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">3. CONTATOS E REMARCAÇÕES</h4>
                <p style="margin-bottom: 0.5rem;">O salão poderá entrar em contato diretamente com você através do WhatsApp ou e-mail cadastrado caso ocorram imprevistos extremos de força maior na escala de profissionais, visando sempre o reagendamento confortável do seu horário.</p>
            </div>
        `,
        icon: 'info',
        confirmButtonText: 'Entendido e Fechar'
    });
}

function mostrarPoliticaPrivacidade() {
    const swalOptions = window._swalDefaults || {
        customClass: {
            popup: 'swal-belezou-popup',
            title: 'swal-belezou-title',
            htmlContainer: 'swal-belezou-text',
            confirmButton: 'swal-belezou-btn-confirm'
        },
        buttonsStyling: false,
        showClass: { popup: 'swal-belezou-show' },
        hideClass: { popup: 'swal-belezou-hide' }
    };

    Swal.fire({
        ...swalOptions,
        title: 'Política de Privacidade (LGPD) — Belezou App',
        html: `
            <div style="text-align: left; max-height: 280px; overflow-y: auto; padding-right: 8px; font-size: 0.9rem; line-height: 1.55;">
                <p style="margin-bottom: 0.75rem;">Em total conformidade com a <strong>Lei Geral de Proteção de Dados (Lei nº 13.709/18 - LGPD)</strong>, apresentamos de forma transparente como seus dados são coletados, protegidos e tratados:</p>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">1. DADOS COLETADOS</h4>
                <p style="margin-bottom: 0.75rem;">Para criar sua conta e efetuar agendamentos, coletamos apenas dados estritamente necessários: <strong>Nome Completo</strong>, <strong>WhatsApp</strong>, <strong>Data de Nascimento</strong> (para ofertas de aniversário e controle de idade) e <strong>E-mail</strong>.</p>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">2. FINALIDADE DO TRATAMENTO</h4>
                <p style="margin-bottom: 0.5rem;">Seus dados de contato são utilizados apenas para:</p>
                <ul style="margin-bottom: 0.75rem; padding-left: 1.25rem;">
                    <li>Garantir a autenticidade e segurança de acesso à sua conta;</li>
                    <li>Vincular e identificar suas marcações no salão;</li>
                    <li>Enviar avisos automáticos de confirmação de agendamentos e notificações push importantes.</li>
                </ul>
                
                <h4 style="margin: 1.25rem 0 0.5rem 0; color: var(--color-purple); font-weight: 700; font-size: 0.95rem;">3. SEGURANÇA E SEUS DIREITOS</h4>
                <p style="margin-bottom: 0.5rem;">Criptografamos suas credenciais e não compartilhamos suas informações com terceiros sob nenhuma hipótese. A qualquer momento você tem total direito de visualizar seus dados, solicitar a retificação ou a <strong>exclusão definitiva</strong> de sua conta e histórico de nossa base de dados.</p>
            </div>
        `,
        icon: 'success', // Ícone que simboliza segurança/conformidade
        confirmButtonText: 'Li e Compreendi'
    });
}