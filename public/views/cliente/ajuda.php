<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title>Ajuda / FAQ - Belezou App</title>

    <!-- Bootstrap 5 para o Accordion -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <!-- CSS do Projeto -->
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/app-cliente.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/ajuda.css">
    <?php require_once __DIR__ . '/../partials/onesignal.php'; ?>

    <style>
        /* Garante que o Bootstrap não quebre a fonte global */
        body { font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>

<body>
    <div class="app-wrapper">
        <div class="mobile-container">

            <!-- Header -->
            <header class="app-header" style="justify-content: center; position: relative;">
                <a href="<?= BASE_URL ?>/" style="position:absolute; left: 1rem; font-size: 1.3rem; color: var(--text-main); text-decoration:none;">
                    <i class="bi bi-chevron-left"></i>
                </a>
                <h2 class="ajuda-page-title">Ajuda / FAQ</h2>
            </header>

            <main class="app-content">
                <div class="mobile-ajuda-container">

                    <!-- Hero -->
                    <div class="ajuda-hero">
                        <div class="ajuda-hero-icon"><i class="bi bi-question-circle-fill"></i></div>
                        <div>
                            <h1>Central de Ajuda</h1>
                            <p>Respostas para as dúvidas mais frequentes do seu app de beleza.</p>
                        </div>
                    </div>

                    <!-- SEÇÃO 1: AGENDAMENTO -->
                    <div class="ajuda-badge badge-pink">
                        <i class="bi bi-calendar-check"></i> Agendamentos
                    </div>

                    <div class="ajuda-accordion" id="accordionAgendamento">

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#ag1" aria-expanded="true">
                                    <span class="ajuda-num">1</span>
                                    <span>Como fazer um novo agendamento?</span>
                                </button>
                            </h2>
                            <div id="ag1" class="accordion-collapse collapse show" data-bs-parent="#accordionAgendamento">
                                <div class="accordion-body">
                                    <ol>
                                        <li>No menu inferior, toque em <strong>📅 Agendar</strong>.</li>
                                        <li>Escolha o <strong>serviço desejado</strong> na lista (use a busca para filtrar).</li>
                                        <li>Selecione o <strong>profissional</strong> de sua preferência.</li>
                                        <li>Escolha a <strong>data</strong> e um dos <strong>horários disponíveis</strong>.</li>
                                        <li>Na etapa final, revise o resumo e clique em <strong>"✅ Confirmar"</strong>.</li>
                                    </ol>
                                    <div class="ajuda-tip">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>Após confirmar, você será redirecionado para o <strong>Histórico</strong> e receberá uma notificação push de confirmação.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag2">
                                    <span class="ajuda-num">2</span>
                                    <span>Por que não consigo ver horários disponíveis?</span>
                                </button>
                            </h2>
                            <div id="ag2" class="accordion-collapse collapse" data-bs-parent="#accordionAgendamento">
                                <div class="accordion-body">
                                    Os horários são gerados em tempo real com base na agenda do profissional. Isso pode acontecer quando:
                                    <ul>
                                        <li>O profissional <strong>não possui uma grade de disponibilidade ativa</strong>.</li>
                                        <li>O dia escolhido é uma <strong>folga</strong> do profissional.</li>
                                        <li>Todos os horários daquele dia já estão <strong>ocupados</strong>.</li>
                                        <li>Você está tentando agendar com menos antecedência do que o profissional exige.</li>
                                    </ul>
                                    <div class="ajuda-tip">
                                        <i class="bi bi-lightbulb-fill"></i>
                                        <span>Tente selecionar outra data ou um profissional diferente que realize o mesmo serviço.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag3">
                                    <span class="ajuda-num">3</span>
                                    <span>O que significa cada status de agendamento?</span>
                                </button>
                            </h2>
                            <div id="ag3" class="accordion-collapse collapse" data-bs-parent="#accordionAgendamento">
                                <div class="accordion-body">
                                    <ul style="list-style: none; padding: 0; gap: 0.6rem; display: flex; flex-direction: column;">
                                        <li><span class="status-pill pendente"><i class="bi bi-clock"></i> Pendente</span> — Você agendou, mas o salão ainda não confirmou.</li>
                                        <li><span class="status-pill marcado"><i class="bi bi-check-circle"></i> Marcado</span> — O salão confirmou o seu agendamento.</li>
                                        <li><span class="status-pill concluido"><i class="bi bi-star"></i> Concluído</span> — O serviço foi realizado com sucesso.</li>
                                        <li><span class="status-pill cancelado"><i class="bi bi-x-circle"></i> Cancelado</span> — O agendamento foi cancelado (por você ou pelo salão).</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- SEÇÃO 2: CANCELAMENTO -->
                    <div class="ajuda-badge badge-pink" style="margin-top: 0.5rem;">
                        <i class="bi bi-x-circle"></i> Cancelamento
                    </div>

                    <div class="ajuda-accordion" id="accordionCancelamento">

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ca1" aria-expanded="false">
                                    <span class="ajuda-num">1</span>
                                    <span>Como cancelar um agendamento?</span>
                                </button>
                            </h2>
                            <div id="ca1" class="accordion-collapse collapse" data-bs-parent="#accordionCancelamento">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Acesse o menu <strong>🕒 Histórico</strong>.</li>
                                        <li>Na aba <strong>"Próximos"</strong>, localize o agendamento que deseja cancelar.</li>
                                        <li>Clique no botão <strong>"Cancelar Agendamento"</strong> (aparece em vermelho).</li>
                                        <li>Confirme a ação na janela de confirmação.</li>
                                    </ol>
                                    <div class="ajuda-warning">
                                        <i class="bi bi-exclamation-triangle-fill"></i>
                                        <span><strong>Regra importante:</strong> o cancelamento só é permitido com <strong>no mínimo 1 dia de antecedência</strong>. No dia do serviço ou em datas passadas, o botão não estará disponível.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ca2">
                                    <span class="ajuda-num">2</span>
                                    <span>Posso remarcar para outro dia?</span>
                                </button>
                            </h2>
                            <div id="ca2" class="accordion-collapse collapse" data-bs-parent="#accordionCancelamento">
                                <div class="accordion-body">
                                    Ainda não há função de remarcação direta. Para remarcar:
                                    <ol>
                                        <li>Cancele o agendamento atual (com ao menos 1 dia de antecedência).</li>
                                        <li>Retorne à tela de <strong>Agendar</strong> e crie um novo horário.</li>
                                    </ol>
                                    <div class="ajuda-tip">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>Após o cancelamento, o horário fica imediatamente liberado para outro cliente.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- SEÇÃO 3: HISTÓRICO E PERFIL -->
                    <div class="ajuda-badge badge-purple" style="margin-top: 0.5rem;">
                        <i class="bi bi-person-circle"></i> Histórico e Conta
                    </div>

                    <div class="ajuda-accordion" id="accordionPerfil">

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pe1">
                                    <span class="ajuda-num">1</span>
                                    <span>Como ver meu histórico de visitas?</span>
                                </button>
                            </h2>
                            <div id="pe1" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                                <div class="accordion-body">
                                    Toque em <strong>🕒 Histórico</strong> no menu inferior. A tela é dividida em duas abas:
                                    <ul>
                                        <li><strong>Próximos</strong> — agendamentos com status <span class="status-pill pendente" style="font-size:0.75rem;">Pendente</span> ou <span class="status-pill marcado" style="font-size:0.75rem;">Marcado</span>.</li>
                                        <li><strong>Anteriores</strong> — serviços <span class="status-pill concluido" style="font-size:0.75rem;">Concluídos</span> ou <span class="status-pill cancelado" style="font-size:0.75rem;">Cancelados</span>.</li>
                                    </ul>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pe2">
                                    <span class="ajuda-num">2</span>
                                    <span>Como atualizar meus dados cadastrais?</span>
                                </button>
                            </h2>
                            <div id="pe2" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                                <div class="accordion-body">
                                    <ol>
                                        <li>Toque em <strong>👤 Perfil</strong> no menu inferior.</li>
                                        <li>Na aba <strong>"Dados Gerais"</strong>, edite seu nome, telefone e data de nascimento.</li>
                                        <li>Clique em <strong>"Salvar Dados"</strong> para confirmar.</li>
                                    </ol>
                                    <div class="ajuda-tip">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>O e-mail de acesso não pode ser alterado diretamente. Para trocar, entre em contato com o salão.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pe3">
                                    <span class="ajuda-num">3</span>
                                    <span>Como alterar minha senha?</span>
                                </button>
                            </h2>
                            <div id="pe3" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                                <div class="accordion-body">
                                    <strong>Se você lembra a senha atual:</strong>
                                    <ol>
                                        <li>Vá em <strong>👤 Perfil → aba "Segurança"</strong>.</li>
                                        <li>Digite a senha atual e a nova senha (mínimo <strong>8 caracteres</strong>).</li>
                                        <li>Clique em <strong>"Alterar Senha"</strong>.</li>
                                    </ol>
                                    <strong>Se esqueceu a senha:</strong>
                                    <ol>
                                        <li>Na tela de <strong>Login</strong>, clique em <strong>"Esqueceu sua senha?"</strong>.</li>
                                        <li>Informe seu e-mail cadastrado.</li>
                                        <li>Você receberá um <strong>código de 6 dígitos</strong> (válido por 30 minutos).</li>
                                        <li>Insira o código e cadastre uma nova senha.</li>
                                    </ol>
                                </div>
                            </div>
                        </div>

                        <div class="ajuda-item accordion-item" style="border: none;">
                            <h2 class="accordion-header">
                                <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pe4">
                                    <span class="ajuda-num">4</span>
                                    <span>Por que meu e-mail precisa ser verificado?</span>
                                </button>
                            </h2>
                            <div id="pe4" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                                <div class="accordion-body">
                                    Ao se cadastrar, enviamos um <strong>código de 6 dígitos</strong> para o seu e-mail. Ele é necessário para confirmar que o endereço está correto e ativar seu acesso. Se não receber o e-mail:
                                    <ul>
                                        <li>Verifique a pasta de <strong>Spam/Lixo eletrônico</strong>.</li>
                                        <li>Na tela de verificação, use o botão <strong>"Reenviar Código"</strong>.</li>
                                    </ul>
                                    <div class="ajuda-tip">
                                        <i class="bi bi-info-circle-fill"></i>
                                        <span>O código de verificação tem validade de <strong>30 minutos</strong>. Após expirar, solicite um novo.</span>
                                    </div>
                                </div>
                            </div>
                        </div>

                    </div>

                    <!-- Rodapé -->
                    <div class="ajuda-footer-card">
                        <p><i class="bi bi-headset" style="color: var(--color-purple); font-size: 1.5rem;"></i></p>
                        <strong>Ainda com dúvidas?</strong>
                        <p style="margin-top:0.3rem;">Entre em contato diretamente com o salão durante o horário de atendimento.</p>
                    </div>

                </div>
            </main>

            <!-- Bottom Navigation -->
            <nav class="bottom-nav">
                <a href="<?= BASE_URL ?>/" class="nav-item">
                    <span class="nav-icon">🏠</span><span>Início</span>
                </a>
                <a href="<?= BASE_URL ?>/agendar" class="nav-item">
                    <span class="nav-icon">📅</span><span>Agendar</span>
                </a>
                <a href="<?= BASE_URL ?>/historico" class="nav-item">
                    <span class="nav-icon">🕒</span><span>Histórico</span>
                </a>
                <a href="<?= BASE_URL ?>/perfil" class="nav-item">
                    <span class="nav-icon">👤</span><span>Perfil</span>
                </a>
                <a href="<?= BASE_URL ?>/cliente/ajuda" class="nav-item active">
                    <span class="nav-icon"><i class="bi bi-question-circle" style="font-size: 1.2rem;"></i></span>
                    <span>Ajuda</span>
                </a>
            </nav>

        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/app-cliente.js"></script>
</body>
</html>
