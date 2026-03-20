/* =========================================
   HISTORICO.JS - LÓGICA DAS ABAS
   ========================================= */

function mudarAba(abaId, elementoClicado) {
    // 1. Tira a classe 'active' de todas as abas (conteúdos)
    const conteudos = document.querySelectorAll('.tab-content');
    conteudos.forEach(conteudo => {
        conteudo.classList.remove('active');
    });

    // 2. Tira a classe 'active' de todos os botões
    const botoes = document.querySelectorAll('.tab-btn');
    botoes.forEach(botao => {
        botao.classList.remove('active');
    });

    // 3. Adiciona a classe 'active' na aba escolhida e no botão clicado
    document.getElementById(`aba-${abaId}`).classList.add('active');
    elementoClicado.classList.add('active');
}