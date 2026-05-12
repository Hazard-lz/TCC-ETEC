<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id']) || !isset($_SESSION['is_funcionario'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuda / FAQ - Belezou App</title>

    <!-- Bootstrap 5 para o Accordion -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">

    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/root.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin-layout.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/admin.css">
    <link rel="stylesheet" href="<?= BASE_URL ?>/public/resources/css/ajuda.css">

    <style>
        body { font-family: "Inter", "Segoe UI", Roboto, Helvetica, Arial, sans-serif; }
    </style>
</head>
<body>

    <?php require_once __DIR__ . '/../partials/sidebar.php'; ?>

    <!-- Page Header -->
    <div class="page-header">
        <div class="page-title">
            <h2><i class="bi bi-question-circle me-2" style="color: var(--color-purple);"></i>Ajuda / FAQ</h2>
            <p>Respostas sobre o uso do sistema de agendamentos para profissionais.</p>
        </div>
    </div>

    <div class="base-card desktop-ajuda-container">

        <!-- Hero -->
        <div class="ajuda-hero">
            <div class="ajuda-hero-icon"><i class="bi bi-person-badge-fill"></i></div>
            <div>
                <h1>Central de Ajuda — Funcionário</h1>
                <p>Tudo o que você precisa saber para gerenciar sua agenda, disponibilidade e perfil no Belezou.</p>
            </div>
        </div>

        <!-- SEÇÃO 1: DISPONIBILIDADE -->
        <div class="ajuda-badge badge-purple">
            <i class="bi bi-clock-history"></i> Grade de Disponibilidade
        </div>

        <div class="ajuda-accordion" id="accordionDisponibilidade">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#di1" aria-expanded="true">
                        <span class="ajuda-num">1</span>
                        Como criar e configurar minha grade de horários?
                    </button>
                </h2>
                <div id="di1" class="accordion-collapse collapse show" data-bs-parent="#accordionDisponibilidade">
                    <div class="accordion-body">
                        Acesse <strong>Disponibilidade</strong> no menu lateral e siga os passos:
                        <ol>
                            <li>Clique em <strong>"Nova Grade"</strong> e dê um nome (ex: "Semana Padrão").</li>
                            <li>Para cada dia da semana, defina o <strong>horário de início e fim</strong> do expediente.</li>
                            <li>Se houver almoço ou pausa, preencha os campos de <strong>Intervalo</strong> (início e fim). O sistema bloqueia esses horários automaticamente.</li>
                            <li>Dias que você não atende devem ter o status <strong>"Folga"</strong>.</li>
                            <li>Marque a opção <strong>"Ativar esta grade"</strong> para que ela passe a reger os seus agendamentos.</li>
                        </ol>
                        <div class="ajuda-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Os dias aceitos são: <strong>Dom, Seg, Ter, Qua, Qui, Sex, Sab</strong>. O horário de fim sempre deve ser maior que o de início.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#di2">
                        <span class="ajuda-num">2</span>
                        Posso ter mais de uma grade de horários?
                    </button>
                </h2>
                <div id="di2" class="accordion-collapse collapse" data-bs-parent="#accordionDisponibilidade">
                    <div class="accordion-body">
                        Sim! Você pode criar múltiplas grades (ex: "Horário de Verão", "Semana Reduzida"). Porém, <strong>apenas uma pode estar ativa</strong> por vez. Para trocar de grade, basta clicar no botão <strong>"Ativar"</strong> na grade desejada. A grade anteriormente ativa será automaticamente desativada.
                        <div class="ajuda-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span><strong>Não é possível excluir a grade ativa.</strong> Para excluí-la, ative primeiro outra grade.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#di3">
                        <span class="ajuda-num">3</span>
                        O que é a "Antecedência Mínima" e como funciona?
                    </button>
                </h2>
                <div id="di3" class="accordion-collapse collapse" data-bs-parent="#accordionDisponibilidade">
                    <div class="accordion-body">
                        A antecedência mínima define com <strong>quantas horas de antecedência</strong> um cliente pode agendar. Por exemplo, se configurada para <strong>2 horas</strong>, às 14h de hoje o cliente só conseguirá ver horários a partir das 16h.
                        <div class="ajuda-tip">
                            <i class="bi bi-lightbulb-fill"></i>
                            <span>Defina este valor em "Disponibilidade" no campo de configuração da grade. Útil para evitar agendamentos de última hora.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- SEÇÃO 2: AGENDA DO DIA -->
        <div class="ajuda-badge badge-pink" style="margin-top: 0.5rem;">
            <i class="bi bi-calendar-week"></i> Agenda e Agendamentos
        </div>

        <div class="ajuda-accordion" id="accordionAgenda">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag1">
                        <span class="ajuda-num">1</span>
                        Como visualizar minha agenda do dia?
                    </button>
                </h2>
                <div id="ag1" class="accordion-collapse collapse" data-bs-parent="#accordionAgenda">
                    <div class="accordion-body">
                        Acesse <strong>Agendamentos</strong> no menu lateral. Você verá um <strong>calendário semanal interativo</strong> com todos os seus clientes do dia, organizados por horário.
                        <ul>
                            <li>Clique em qualquer evento para ver os <strong>detalhes do cliente e do serviço</strong>.</li>
                            <li>Use as setas de navegação para avançar ou retroceder semanas.</li>
                            <li>A grade respeita os limites da sua disponibilidade ativa.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag2">
                        <span class="ajuda-num">2</span>
                        Como confirmar ou finalizar um atendimento?
                    </button>
                </h2>
                <div id="ag2" class="accordion-collapse collapse" data-bs-parent="#accordionAgenda">
                    <div class="accordion-body">
                        Na tela de Agendamentos, clique no evento e use os botões de ação:
                        <ul>
                            <li><strong>Marcado</strong> — confirma que o cliente compareceu e o atendimento vai acontecer.</li>
                            <li><strong>Concluído</strong> — marca o serviço como finalizado.</li>
                            <li><strong>Cancelado</strong> — cancela o agendamento (o cliente será notificado automaticamente via push).</li>
                        </ul>
                        <div class="ajuda-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Agendamentos com status <strong>Concluído</strong> ou <strong>Cancelado</strong> não podem ser revertidos para status anteriores.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag3">
                        <span class="ajuda-num">3</span>
                        Como agendar um cliente pelo balcão (presencialmente)?
                    </button>
                </h2>
                <div id="ag3" class="accordion-collapse collapse" data-bs-parent="#accordionAgenda">
                    <div class="accordion-body">
                        Na tela de Agendamentos, clique no botão <strong>"Novo Agendamento"</strong>. Um modal será aberto onde você:
                        <ol>
                            <li>Seleciona o <strong>cliente</strong> cadastrado.</li>
                            <li>Escolhe o <strong>serviço, profissional, data e hora</strong>.</li>
                            <li>Confirma o agendamento.</li>
                        </ol>
                        <div class="ajuda-tip">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Agendamentos criados por funcionários recebem automaticamente o status <strong>Marcado</strong> (em vez de Pendente). O cliente é notificado por push.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#ag4">
                        <span class="ajuda-num">4</span>
                        Como ver meu histórico de atendimentos?
                    </button>
                </h2>
                <div id="ag4" class="accordion-collapse collapse" data-bs-parent="#accordionAgenda">
                    <div class="accordion-body">
                        Acesse <strong>Histórico</strong> no menu lateral. A tela exibe todos os seus atendimentos, organizados em duas abas:
                        <ul>
                            <li><strong>Próximos</strong> — pendentes e marcados.</li>
                            <li><strong>Anteriores</strong> — concluídos e cancelados.</li>
                        </ul>
                    </div>
                </div>
            </div>

        </div>

        <!-- SEÇÃO 3: PERFIL -->
        <div class="ajuda-badge badge-blue" style="margin-top: 0.5rem;">
            <i class="bi bi-person-gear"></i> Perfil e Serviços
        </div>

        <div class="ajuda-accordion" id="accordionPerfil">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf1">
                        <span class="ajuda-num">1</span>
                        Como editar meu perfil e dados pessoais?
                    </button>
                </h2>
                <div id="pf1" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                    <div class="accordion-body">
                        Clique no seu avatar no canto superior direito e selecione <strong>"Editar Perfil"</strong>. Você pode atualizar:
                        <ul>
                            <li><strong>Nome</strong> e <strong>telefone</strong> de contato.</li>
                            <li><strong>Especialidade principal</strong> (ex: "Cabeleireira", "Manicure").</li>
                        </ul>
                        <div class="ajuda-tip">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>O salário base não pode ser alterado pelo próprio funcionário — apenas um administrador pode ajustá-lo.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf2">
                        <span class="ajuda-num">2</span>
                        Como definir quais serviços eu realizo?
                    </button>
                </h2>
                <div id="pf2" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                    <div class="accordion-body">
                        Acesse <strong>Meus Serviços</strong> no menu lateral. Você verá a lista de serviços ativos do catálogo. Marque os que você realiza e clique em <strong>"Salvar Especialidades"</strong>. Apenas serviços vinculados ao seu perfil aparecerão disponíveis para o cliente agendar com você.
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#pf3">
                        <span class="ajuda-num">3</span>
                        Como alterar minha senha de acesso?
                    </button>
                </h2>
                <div id="pf3" class="accordion-collapse collapse" data-bs-parent="#accordionPerfil">
                    <div class="accordion-body">
                        Acesse seu <strong>Perfil</strong> e vá na aba de <strong>Segurança</strong>. Insira a senha atual e a nova senha (mínimo <strong>8 caracteres</strong>). Se esqueceu a senha, use a opção <strong>"Esqueci minha senha"</strong> na tela de login — um código de recuperação (válido por 30 minutos) será enviado para o seu e-mail.
                    </div>
                </div>
            </div>

        </div>

        <!-- Rodapé -->
        <div class="ajuda-footer-card" style="margin-top: 2rem;">
            <p><i class="bi bi-shield-lock" style="color: var(--color-purple); font-size: 1.5rem;"></i></p>
            <strong>Problema técnico ou de acesso?</strong>
            <p style="margin-top:0.3rem;">Entre em contato com o administrador do sistema para suporte avançado.</p>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
</body>
</html>
