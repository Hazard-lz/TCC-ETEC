<?php
if (session_status() === PHP_SESSION_NONE) { session_start(); }
if (!isset($_SESSION['usuario_id']) || !in_array($_SESSION['usuario_tipo'] ?? '', ['admin', 'subadmin'])) {
    header("Location: " . BASE_URL . "/login");
    exit;
}
$isAdmin = ($_SESSION['usuario_tipo'] ?? '') === 'admin';
?>
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Ajuda Admin / FAQ - Belezou</title>

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
            <h2><i class="bi bi-question-circle me-2" style="color: var(--color-purple);"></i>Ajuda Administrativa</h2>
            <p>Guia completo para gerenciar o salão, equipe, serviços e relatórios.</p>
        </div>
    </div>

    <div class="base-card desktop-ajuda-container">

        <!-- Hero -->
        <div class="ajuda-hero">
            <div class="ajuda-hero-icon"><i class="bi bi-shield-fill-check"></i></div>
            <div>
                <h1>Central de Ajuda — Administrador</h1>
                <p>Documentação das principais ferramentas de gestão do sistema Belezou.</p>
            </div>
        </div>

        <!-- SEÇÃO 1: SERVIÇOS -->
        <div class="ajuda-badge badge-purple">
            <i class="bi bi-scissors"></i> Catálogo de Serviços
        </div>

        <div class="ajuda-accordion" id="accordionServicos">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#sv1" aria-expanded="true">
                        <span class="ajuda-num">1</span>
                        <span>Como cadastrar um novo serviço?</span>
                    </button>
                </h2>
                <div id="sv1" class="accordion-collapse collapse show" data-bs-parent="#accordionServicos">
                    <div class="accordion-body">
                        Acesse <strong>Catálogo de Serviços</strong> no menu e clique em <strong>"+ Novo Serviço"</strong>:
                        <ol>
                            <li><strong>Nome</strong> — máximo 100 caracteres. Não pode haver duplicatas (mesmo nome em outro serviço já cadastrado).</li>
                            <li><strong>Descrição</strong> — obrigatória.</li>
                            <li><strong>Preço (R$)</strong> — não pode ser negativo.</li>
                            <li><strong>Duração (min)</strong> — deve ser um múltiplo de 5 minutos (ex: 30, 45, 60) e no máximo 480 minutos (8 horas).</li>
                        </ol>
                        <div class="ajuda-tip">
                            <i class="bi bi-lightbulb-fill"></i>
                            <span>Após criar o serviço, os profissionais devem vinculá-lo nos seus perfis (em "Meus Serviços") para que ele fique disponível para agendamento.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sv2">
                        <span class="ajuda-num">2</span>
                        <span>Como editar o preço ou duração de um serviço?</span>
                    </button>
                </h2>
                <div id="sv2" class="accordion-collapse collapse" data-bs-parent="#accordionServicos">
                    <div class="accordion-body">
                        Na lista de serviços, clique no ícone de <strong>edição ✏️</strong> do serviço desejado. Um modal será aberto com os campos editáveis. Altere o valor e salve. As mesmas validações do cadastro se aplicam:
                        <ul>
                            <li>Preço não pode ser negativo.</li>
                            <li>Duração deve ser múltiplo de 5, máximo 480 min.</li>
                            <li>O nome deve ser único no catálogo.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#sv3">
                        <span class="ajuda-num">3</span>
                        <span>Como desativar ou excluir um serviço?</span>
                    </button>
                </h2>
                <div id="sv3" class="accordion-collapse collapse" data-bs-parent="#accordionServicos">
                    <div class="accordion-body">
                        <strong>Desativar:</strong> Use o toggle de status (ativo/inativo) na lista. Serviços inativos deixam de aparecer para os clientes na tela de agendamento.
                        <br><br>
                        <strong>Excluir permanentemente:</strong> Somente serviços com status <strong>Inativo</strong> podem ser excluídos. Se houver agendamentos históricos vinculados, o sistema impedirá a exclusão para preservar o histórico.
                        <div class="ajuda-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Prefira <strong>inativar</strong> o serviço em vez de excluir. A exclusão é permanente e pode causar inconsistências no histórico.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <!-- SEÇÃO 2: EQUIPE -->
        <div class="ajuda-badge badge-pink" style="margin-top: 0.5rem;">
            <i class="bi bi-people-fill"></i> Gestão de Equipe
        </div>

        <div class="ajuda-accordion" id="accordionEquipe">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eq1">
                        <span class="ajuda-num">1</span>
                        <span>Como cadastrar um novo funcionário?</span>
                    </button>
                </h2>
                <div id="eq1" class="accordion-collapse collapse" data-bs-parent="#accordionEquipe">
                    <div class="accordion-body">
                        Em <strong>Funcionários</strong>, clique em <strong>"+ Novo Funcionário"</strong> e preencha:
                        <ol>
                            <li><strong>Nome Completo</strong> e <strong>Telefone/WhatsApp</strong>.</li>
                            <li><strong>E-mail de Acesso</strong> — o funcionário receberá um link neste e-mail para criar a própria senha (válido por <strong>48 horas</strong>).</li>
                            <li><strong>Especialidade Principal</strong> e <strong>Salário Base</strong>.</li>
                            <li><strong>Nível de Acesso</strong>: Profissional Comum ou Subadministrador.</li>
                        </ol>
                        <div class="ajuda-tip">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Não é possível cadastrar um funcionário já com o cargo de <strong>Administrador</strong>. O cargo de Admin só pode ser transferido para um funcionário já existente.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eq2">
                        <span class="ajuda-num">2</span>
                        <span>O que são os níveis de acesso (Admin, Subadmin, Comum)?</span>
                    </button>
                </h2>
                <div id="eq2" class="accordion-collapse collapse" data-bs-parent="#accordionEquipe">
                    <div class="accordion-body">
                        <ul>
                            <li><strong>Administrador (Admin)</strong> — Acesso total. Gerencia serviços, equipe, visualiza relatórios de desempenho e pode transferir o cargo de admin. Só pode haver <strong>um admin ativo</strong> no sistema.</li>
                            <li><strong>Subadministrador (Subadmin)</strong> — Pode gerenciar funcionários e serviços, mas <strong>não acessa relatórios</strong>. Não pode alterar o status de admins nem promover alguém a admin.</li>
                            <li><strong>Profissional Comum</strong> — Acesso à agenda, clientes, disponibilidade e próprio perfil.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eq3">
                        <span class="ajuda-num">3</span>
                        <span>Como reativar o acesso de um funcionário inativo?</span>
                    </button>
                </h2>
                <div id="eq3" class="accordion-collapse collapse" data-bs-parent="#accordionEquipe">
                    <div class="accordion-body">
                        Na lista de funcionários, clique no ícone <strong>✅</strong> ao lado do funcionário inativo. O sistema alternará o status para <strong>Ativo</strong> e o funcionário poderá fazer login novamente.
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eq4">
                        <span class="ajuda-num">4</span>
                        <span>O funcionário não recebeu o e-mail de configuração. O que fazer?</span>
                    </button>
                </h2>
                <div id="eq4" class="accordion-collapse collapse" data-bs-parent="#accordionEquipe">
                    <div class="accordion-body">
                        Na lista de funcionários, se o ícone de <strong>📧 envelope</strong> estiver visível, significa que o e-mail ainda não foi verificado. Clique no ícone para <strong>reenviar o link de criação de senha</strong>. Um novo token (válido por 48 horas) será gerado.
                        <div class="ajuda-tip">
                            <i class="bi bi-lightbulb-fill"></i>
                            <span>Peça ao funcionário para verificar a pasta de <strong>Spam</strong> caso o e-mail não apareça na caixa de entrada.</span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($isAdmin): ?>
            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#eq5">
                        <span class="ajuda-num">5</span>
                        <span>Como transferir o cargo de Administrador?</span>
                    </button>
                </h2>
                <div id="eq5" class="accordion-collapse collapse" data-bs-parent="#accordionEquipe">
                    <div class="accordion-body">
                        <ol>
                            <li>Abra o formulário de edição de um funcionário existente.</li>
                            <li>No campo <strong>"Nível de Acesso"</strong>, selecione <strong>"👑 Transferir Cargo de Administrador"</strong>.</li>
                            <li>Salve o formulário.</li>
                        </ol>
                        <div class="ajuda-warning">
                            <i class="bi bi-exclamation-triangle-fill"></i>
                            <span>Esta ação é <strong>imediata e irreversível</strong> sem intervenção manual. Ao transferir, você (o admin atual) será automaticamente rebaixado a <strong>Subadministrador</strong>.</span>
                        </div>
                    </div>
                </div>
            </div>
            <?php endif; ?>

        </div>

        <!-- SEÇÃO 3: PAINEL E RELATÓRIOS -->
        <?php if ($isAdmin): ?>
        <div class="ajuda-badge badge-blue" style="margin-top: 0.5rem;">
            <i class="bi bi-graph-up-arrow"></i> Dashboard e Relatórios
        </div>

        <div class="ajuda-accordion" id="accordionRelatorios">

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#re1">
                        <span class="ajuda-num">1</span>
                        <span>Como interpretar o Painel Inicial (Dashboard)?</span>
                    </button>
                </h2>
                <div id="re1" class="accordion-collapse collapse" data-bs-parent="#accordionRelatorios">
                    <div class="accordion-body">
                        O Painel Inicial exibe uma visão consolidada do salão (para Admin/Subadmin) ou individual (para funcionários comuns):
                        <ul>
                            <li><strong>Agendamentos Hoje</strong> — total de atendimentos no dia.</li>
                            <li><strong>Faturamento do Mês</strong> — soma dos serviços com status <em>Concluído</em> no mês atual.</li>
                            <li><strong>Total de Clientes</strong> — base total de clientes cadastrados.</li>
                            <li><strong>Próximos Agendamentos</strong> — lista resumida dos próximos 5 horários.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#re2">
                        <span class="ajuda-num">2</span>
                        <span>Como usar o Relatório de Desempenho?</span>
                    </button>
                </h2>
                <div id="re2" class="accordion-collapse collapse" data-bs-parent="#accordionRelatorios">
                    <div class="accordion-body">
                        Acesse <strong>Relatórios</strong> no menu lateral. Nesta tela você pode:
                        <ul>
                            <li><strong>Filtrar por período</strong> (especificando um intervalo de datas com início e fim, ou usando os atalhos rápidos como "Hoje", "Esta Semana" e "Este Mês") e por <strong>funcionário específico</strong> (ou visão geral).</li>
                            <li>Visualizar o <strong>volume de atendimentos</strong> no período — útil para identificar sazonalidade.</li>
                            <li>Analisar o <strong>faturamento consolidado</strong> por profissional.</li>
                            <li>Comparar a performance da equipe para tomada de decisão em comissões ou metas.</li>
                        </ul>
                        <div class="ajuda-tip">
                            <i class="bi bi-lightbulb-fill"></i>
                            <span>O relatório considera apenas agendamentos com status <strong>Concluído</strong>. Agendamentos cancelados ou pendentes não entram no faturamento.</span>
                        </div>
                    </div>
                </div>
            </div>

            <div class="ajuda-item accordion-item" style="border: none;">
                <h2 class="accordion-header">
                    <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#re3">
                        <span class="ajuda-num">3</span>
                        <span>Agendamentos pendentes expiram automaticamente?</span>
                    </button>
                </h2>
                <div id="re3" class="accordion-collapse collapse" data-bs-parent="#accordionRelatorios">
                    <div class="accordion-body">
                        Sim. O sistema possui uma <strong>rotina automática</strong> que cancela agendamentos com status <strong>"Pendente"</strong> cujos horários já passaram sem confirmação do funcionário. Esta rotina é executada automaticamente ao carregar as telas de Histórico e Agenda.
                        <div class="ajuda-tip">
                            <i class="bi bi-info-circle-fill"></i>
                            <span>Isso garante que o calendário do profissional esteja sempre limpo, sem horários "fantasmas" no passado.</span>
                        </div>
                    </div>
                </div>
            </div>

        </div>
        <?php endif; ?>

        <!-- Rodapé -->
        <div class="ajuda-footer-card" style="margin-top: 2rem;">
            <p><i class="bi bi-shield-exclamation" style="color: var(--color-purple); font-size: 1.5rem;"></i></p>
            <strong>Problema técnico ou de funcionamento?</strong>
            <p style="margin-top:0.3rem;">Entre em contato com a equipe de suporte ou com o desenvolvedor responsável para obter auxílio.</p>
        </div>

    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= BASE_URL ?>/public/resources/js/admin.js"></script>
</body>
</html>
