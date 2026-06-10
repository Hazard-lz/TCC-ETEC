<?php
// =========================================================================
// SERVICE DE AGENDAMENTO (AgendamentoService.php)
// =========================================================================
// Este serviço concentra as regras de negócio mais importantes do sistema:
// validação de datas, prevenção de overbooking (conflitos de horário),
// controle de transações no banco, políticas de cancelamento e disparos
// automatizados de push notifications e e-mails de confirmação.
// =========================================================================

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Agendamento.php';
require_once __DIR__ . '/../Models/Servico.php';
require_once __DIR__ . '/../../database/Conexao.php';
require_once __DIR__ . '/OneSignalService.php';
require_once __DIR__ . '/EmailService.php';

class AgendamentoService extends BaseService
{
    private $agendamentoModel;
    private $servicoModel;
    private $conn;

    /**
     * Construtor do serviço: instancia os modelos e obtém a conexão PDO singleton
     */
    public function __construct()
    {
        $this->agendamentoModel = new Agendamento();
        $this->servicoModel = new Servico();
        $this->conn = Conexao::getConexao();
    }

    /**
     * Orquestra a criação segura de um novo agendamento no sistema.
     * 
     * @param int $idCliente ID da tabela 'clientes' (não do 'usuarios')
     * @param int $idFuncionario ID da tabela 'funcionarios' (não do 'usuarios')
     * @param int $idServico ID da tabela 'servicos'
     * @param string $data Data do agendamento (YYYY-MM-DD)
     * @param string $horaInicio Hora de início do atendimento (HH:MM:SS)
     * @param int|null $idFuncionarioCriador ID do funcionário que está agendando via balcão (null se for o cliente)
     * @return array Resposta padronizada de sucesso ou falha
     */
    public function realizarAgendamento($idCliente, $idFuncionario, $idServico, $data, $horaInicio, $idFuncionarioCriador = null)
    {
        try {
            // 1. Validação de data: impede a criação de agendamentos retroativos
            if ($data < date('Y-m-d')) {
                return ['sucesso' => false, 'mensagem' => 'Não é possível agendar em datas passadas.'];
            }

            // 1.5 Validação do limite de agendamento futuro
            // Se quem está criando é o próprio cliente, valida as restrições de antecedência máxima configuradas
            if ($idFuncionarioCriador === null) {
                require_once __DIR__ . '/../Models/Configuracao.php';
                $configModel = new Configuracao();
                $limiteDiasVal = $configModel->obterValor('limite_agendamento_futuro_dias', 'sem_limite');
                if ($limiteDiasVal !== 'sem_limite' && is_numeric($limiteDiasVal)) {
                    $limiteDiasInt = (int)$limiteDiasVal;
                    $maxDataObj = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                    $maxDataObj->modify("+{$limiteDiasInt} days");
                    $maxData = $maxDataObj->format('Y-m-d');
                    
                    if ($data > $maxData) {
                        $dataPt = date('d/m/Y', strtotime($maxData));
                        return [
                            'sucesso' => false,
                            'mensagem' => "Não é possível realizar agendamentos com mais de " . $this->traduzirDiasParaTexto($limiteDiasInt) . " de antecedência (limite: $dataPt)."
                        ];
                    }
                }
            }

            // 2. Inicia uma Transação de Banco de Dados
            // Isso garante que se houver falha ao inserir os itens de serviço, a capa do agendamento não fique órfã (Rollback automático)
            if (!$this->conn->inTransaction()) { 
                $this->conn->beginTransaction(); 
            }

            // 3. Recupera os detalhes do serviço (Preço e Duração)
            $servico = $this->servicoModel->buscarPorId($idServico);
            if (!$servico) {
                throw new Exception("O serviço selecionado não existe ou está inativo.");
            }

            $duracao = (int) $servico['duracao'];
            $preco = (float) $servico['preco'];
            $nomeServico = $servico['nome_servico'];

            // 3.5 Calcula a hora de término baseada na duração do serviço
            $horaFimObj = new DateTime($horaInicio);
            $horaFimObj->modify("+{$duracao} minutes");
            $horaFim = $horaFimObj->format('H:i:s'); // Converte para o tipo TIME do MySQL

            // 4. Prevenção de Overbooking (Conflito de Horários)
            // Impede que um profissional seja agendado em um horário onde ele já tem um compromisso marcado
            $conflito = $this->agendamentoModel->verificarConflitoHorario($idFuncionario, $data, $horaInicio, $horaFim);
            if ($conflito) {
                throw new Exception("O profissional já possui um agendamento neste horário.");
            }

            // 5. Verifica se o profissional de fato atende a esse serviço (Integridade Referencial)
            $vinculo = $this->agendamentoModel->buscarVinculoFuncionarioServico($idFuncionario, $idServico);
            if (!$vinculo) {
                throw new Exception("Este profissional não está vinculado para realizar este serviço.");
            }

            // Se foi o cliente quem agendou, o status inicial é 'pendente' (aguardando aprovação).
            // Se foi criado pela gerência/balcão, o status já nasce confirmado como 'marcado'.
            $statusInicial = ($idFuncionarioCriador === null) ? 'pendente' : 'marcado';
            
            // 6. Cadastra os dados principais na tabela 'agendamentos' (Capa)
            $idAgendamento = $this->agendamentoModel->cadastrarAgendamento(
                $idCliente,
                $idFuncionarioCriador,
                $data,
                $statusInicial
            );

            if (!$idAgendamento) {
                throw new Exception("Falha ao registrar os dados principais do agendamento.");
            }

            // 7. Cadastra a especificação do atendimento na tabela 'itens_agendamento'
            $itemInserido = $this->agendamentoModel->cadastrarItem(
                $idAgendamento,
                $vinculo['id_sv_funcionario'],
                $nomeServico,
                $horaInicio,
                $horaFim,
                $preco,
                $duracao
            );

            if (!$itemInserido) {
                throw new Exception("Falha ao registrar o serviço no agendamento.");
            }

            // 8. Efetiva as alterações gravando tudo definitivamente no banco
            $this->conn->commit();

            // =================================================================
            // DISPARO DE NOTIFICAÇÕES (Assíncrono via Shutdown Function)
            // =================================================================
            // Permite que o PHP responda e redirecione o cliente imediatamente,
            // processando os disparos lentos de rede (SMTP e Push) em background.
            ignore_user_abort(true);
            register_shutdown_function(function() use ($idAgendamento, $idFuncionarioCriador) {
                try {
                    $agendamentoCompleto = $this->agendamentoModel->buscarPorId($idAgendamento);
                    if ($agendamentoCompleto) {
                        $oneSignal = new OneSignalService();
                        $emailService = new EmailService();
                        
                        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $urlBase = "$protocolo://$host" . BASE_URL;

                        $dataPt = date('d/m/Y', strtotime($agendamentoCompleto['data_agendamento']));
                        $horaPt = substr($agendamentoCompleto['hora_inicio'], 0, 5);

                        if ($idFuncionarioCriador === null) {
                            // Agendado pelo CLIENTE -> Notifica o profissional
                            if (!empty($agendamentoCompleto['funcionario_cod_usuario'])) {
                                $msg = "📅 Novo Agendamento: {$agendamentoCompleto['cliente_nome']} solicitou {$agendamentoCompleto['nome_servico']} para o dia $dataPt às $horaPt.";
                                $oneSignal->enviarNotificacao(
                                    $agendamentoCompleto['funcionario_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/funcionario/agenda',
                                    "Novo Agendamento"
                                );
                            }
                            if (!empty($agendamentoCompleto['funcionario_email'])) {
                                $assunto = "Novo Agendamento Solicitado - Belezou App";
                                $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                    <h2>Olá, {$agendamentoCompleto['funcionario_nome']}!</h2>
                                    <p>Um novo agendamento foi solicitado por um cliente:</p>
                                    <p><strong>Cliente:</strong> {$agendamentoCompleto['cliente_nome']}</p>
                                    <p><strong>Serviço:</strong> {$agendamentoCompleto['nome_servico']}</p>
                                    <p><strong>Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                    <p>Acesse o painel do sistema para gerenciar sua agenda.</p>
                                </div>";
                                $emailService->enviar($agendamentoCompleto['funcionario_email'], $agendamentoCompleto['funcionario_nome'], $assunto, $corpoHtml);
                            }
                        } else {
                            // Agendado pelo FUNCIONÁRIO/BALCÃO -> Notifica o cliente
                            if (!empty($agendamentoCompleto['cliente_cod_usuario'])) {
                                $msg = "✨ Agendamento Marcado: Um novo horário de {$agendamentoCompleto['nome_servico']} foi reservado para você no dia $dataPt às $horaPt.";
                                $oneSignal->enviarNotificacao(
                                    $agendamentoCompleto['cliente_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/historico',
                                    "Novo Agendamento"
                                );
                            }
                            if (!empty($agendamentoCompleto['cliente_email'])) {
                                $assunto = "Novo Agendamento Confirmado - Belezou App";
                                $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                    <h2>Olá, {$agendamentoCompleto['cliente_nome']}!</h2>
                                    <p>Seu agendamento foi cadastrado no sistema:</p>
                                    <p><strong>Serviço:</strong> {$agendamentoCompleto['nome_servico']}</p>
                                    <p><strong>Profissional:</strong> {$agendamentoCompleto['funcionario_nome']}</p>
                                    <p><strong>Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                    <p>Agradecemos a preferência!</p>
                                </div>";
                                $emailService->enviar($agendamentoCompleto['cliente_email'], $agendamentoCompleto['cliente_nome'], $assunto, $corpoHtml);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Aviso: Falha ao enviar notificação de novo agendamento em background: " . $e->getMessage());
                }
            });

            return [
                'sucesso' => true,
                'mensagem' => 'Agendamento confirmado com sucesso!',
                'id_agendamento' => $idAgendamento
            ];
        } catch (Exception $e) {
            // Desfaz qualquer operação no banco caso ocorra algum erro durante o processo
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            error_log("Erro no AgendamentoService: " . $e->getMessage());

            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    /**
     * Transiciona o status de um agendamento (Confirmar, Recusar ou Cancelar).
     * 
     * @param int $idAgendamento ID do agendamento
     * @param string $novoStatus Novo status ('pendente', 'concluido', 'cancelado', 'marcado')
     * @param string $origem Quem solicitou a alteração ('cliente' ou 'funcionario')
     * @return array Resposta de sucesso ou falha
     */
    public function alterarStatus($idAgendamento, $novoStatus, $origem = 'funcionario')
    {
        $statusValidos = ['pendente', 'concluido', 'cancelado', 'marcado'];

        if (!in_array($novoStatus, $statusValidos)) {
            return ['sucesso' => false, 'mensagem' => 'Status inválido.'];
        }

        // Validação da transição de estados: agendamentos finalizados (concluídos ou cancelados) são imutáveis
        $agendamento = $this->agendamentoModel->buscarPorId($idAgendamento);
        if (!$agendamento) {
            return ['sucesso' => false, 'mensagem' => 'Agendamento não encontrado.'];
        }

        if (in_array($agendamento['status'], ['concluido', 'cancelado']) && $novoStatus !== $agendamento['status']) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível alterar um agendamento que já foi ' . $agendamento['status'] . '.'];
        }

        // Validação de colisão de horário ao transitar o agendamento de Pendente para Marcado (Confirmado)
        if ($novoStatus === 'marcado' && $agendamento['status'] !== 'marcado') {
            $conflito = $this->agendamentoModel->verificarConflitoHorario(
                $agendamento['cod_funcionario'],
                $agendamento['data_agendamento'],
                $agendamento['hora_inicio'],
                $agendamento['hora_fim'],
                $idAgendamento
            );
            if ($conflito) {
                return ['sucesso' => false, 'mensagem' => 'O profissional já possui um agendamento neste horário.'];
            }
        }

        // Verificação das regras de antecedência mínima para cancelamento (Exemplo: limite de 24h)
        if ($novoStatus === 'cancelado' && $agendamento['status'] === 'marcado') {
            require_once __DIR__ . '/../Models/Configuracao.php';
            $configModel = new Configuracao();
            $antecedenciaHoras = (int)$configModel->obterValor('antecedencia_cancelamento_horas', '24');

            if ($antecedenciaHoras > 0) {
                date_default_timezone_set('America/Sao_Paulo');
                $dataHoraAgendamento = new DateTime($agendamento['data_agendamento'] . ' ' . $agendamento['hora_inicio']);
                $agora = new DateTime();

                if ($dataHoraAgendamento > $agora) {
                    $intervalo = $agora->diff($dataHoraAgendamento);
                    $horasDiferenca = ($intervalo->days * 24) + $intervalo->h + ($intervalo->i / 60);

                    if ($horasDiferenca < $antecedenciaHoras) {
                        // Administradores e Subadministradores têm permissão para cancelar sem restrição de tempo
                        $isGerencia = isset($_SESSION['usuario_tipo']) && in_array($_SESSION['usuario_tipo'], ['admin', 'subadmin']);
                        if (!$isGerencia) {
                            return [
                                'sucesso' => false,
                                'mensagem' => "Não é possível cancelar com menos de {$antecedenciaHoras} horas de antecedência."
                            ];
                        }
                    }
                } else {
                    // Impede o cliente de cancelar agendamentos passados
                    if ($origem === 'cliente') {
                        return [
                            'sucesso' => false,
                            'mensagem' => "Não é possível cancelar agendamentos passados."
                        ];
                    }
                }
            }
        }

        // Atualiza fisicamente o status no banco de dados
        $atualizou = $this->agendamentoModel->atualizarStatus($idAgendamento, $novoStatus);

        if ($atualizou) {
            // =================================================================
            // DISPARO DE ALERTAS PÓS ALTERAÇÃO DE STATUS (Assíncrono via Shutdown Function)
            // =================================================================
            // Permite que o PHP responda e redirecione o cliente imediatamente,
            // processando os disparos lentos de rede (SMTP e Push) em background.
            ignore_user_abort(true);
            register_shutdown_function(function() use ($idAgendamento, $origem, $novoStatus) {
                try {
                    $agendamentoAtualizado = $this->agendamentoModel->buscarPorId($idAgendamento);
                    if ($agendamentoAtualizado) {
                        $oneSignal = new OneSignalService();
                        $emailService = new EmailService();
                        
                        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $urlBase = "$protocolo://$host" . BASE_URL;

                        $dataPt = date('d/m/Y', strtotime($agendamentoAtualizado['data_agendamento']));
                        $horaPt = substr($agendamentoAtualizado['hora_inicio'], 0, 5);

                        if ($origem === 'cliente' && $novoStatus === 'cancelado') {
                            // Cliente cancelou -> Notifica o profissional
                            if (!empty($agendamentoAtualizado['funcionario_cod_usuario'])) {
                                $msg = "⚠️ Cancelamento: {$agendamentoAtualizado['cliente_nome']} cancelou o agendamento de {$agendamentoAtualizado['nome_servico']} para o dia $dataPt às {$agendamentoAtualizado['hora_inicio']}.";
                                $oneSignal->enviarNotificacao(
                                    $agendamentoAtualizado['funcionario_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/funcionario/agenda',
                                    "Agenda Atualizada"
                                );
                            }
                            if (!empty($agendamentoAtualizado['funcionario_email'])) {
                                $assunto = "Agendamento Cancelado pelo Cliente - Belezou App";
                                $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                    <h2>Olá, {$agendamentoAtualizado['funcionario_nome']}!</h2>
                                    <p>O cliente cancelou o agendamento agendado:</p>
                                    <p><strong>Cliente:</strong> {$agendamentoAtualizado['cliente_nome']}</p>
                                    <p><strong>Serviço:</strong> {$agendamentoAtualizado['nome_servico']}</p>
                                    <p><strong>Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                    <p>Seu horário foi liberado na agenda.</p>
                                </div>";
                                $emailService->enviar($agendamentoAtualizado['funcionario_email'], $agendamentoAtualizado['funcionario_nome'], $assunto, $corpoHtml);
                            }
                        } else {
                            // Funcionário/Admin confirmou ou cancelou -> Notifica o cliente
                            if (($novoStatus === 'marcado' || $novoStatus === 'cancelado') && !empty($agendamentoAtualizado['cliente_cod_usuario'])) {
                                $msg = ($novoStatus === 'marcado') 
                                    ? "✅ Seu agendamento de {$agendamentoAtualizado['nome_servico']} foi confirmado!" 
                                    : "❌ Infelizmente seu agendamento de {$agendamentoAtualizado['nome_servico']} foi cancelado. Verifique os motivos no app.";
                                
                                $oneSignal->enviarNotificacao(
                                    $agendamentoAtualizado['cliente_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/historico',
                                    "Status do Agendamento"
                                );
                            }
                            if (!empty($agendamentoAtualizado['cliente_email'])) {
                                if ($novoStatus === 'marcado') {
                                    $assunto = "Seu Agendamento foi Confirmado! - Belezou App";
                                    $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                        <h2>Olá, {$agendamentoAtualizado['cliente_nome']}!</h2>
                                        <p>Temos uma ótima notícia! Seu agendamento foi confirmado:</p>
                                        <p><strong>Serviço:</strong> {$agendamentoAtualizado['nome_servico']}</p>
                                        <p><strong>Profissional:</strong> {$agendamentoAtualizado['funcionario_nome']}</p>
                                        <p><strong>Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                        <p>Esperamos você!</p>
                                    </div>";
                                    $emailService->enviar($agendamentoAtualizado['cliente_email'], $agendamentoAtualizado['cliente_nome'], $assunto, $corpoHtml);
                                } elseif ($novoStatus === 'cancelado') {
                                    $assunto = "Agendamento Cancelado - Belezou App";
                                    $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                        <h2>Olá, {$agendamentoAtualizado['cliente_nome']}!</h2>
                                        <p>Infelizmente seu agendamento foi cancelado:</p>
                                        <p><strong>Serviço:</strong> {$agendamentoAtualizado['nome_servico']}</p>
                                        <p><strong>Profissional:</strong> {$agendamentoAtualizado['funcionario_nome']}</p>
                                        <p><strong>Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                        <p>Para dúvidas ou para agendar um novo horário, entre em contato conosco.</p>
                                    </div>";
                                    $emailService->enviar($agendamentoAtualizado['cliente_email'], $agendamentoAtualizado['cliente_nome'], $assunto, $corpoHtml);
                                }
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Aviso: Falha ao enviar notificação push ou e-mail em background: " . $e->getMessage());
                }
            });

            return ['sucesso' => true, 'mensagem' => 'Status do agendamento atualizado.'];
        }

        return ['sucesso' => false, 'mensagem' => 'Falha ao atualizar o status.'];
    }

    /**
     * Remarca um agendamento existente com checagem de conflitos e segurança transacional.
     * 
     * @param int $idAgendamento ID do agendamento
     * @param string $novaData Nova data (YYYY-MM-DD)
     * @param string $novaHoraInicio Nova hora de início (HH:MM:SS)
     * @param string $origem Quem executou a remarcação ('cliente' ou 'funcionario')
     * @return array Resposta padronizada de sucesso ou falha
     */
    public function remarcarAgendamento($idAgendamento, $novaData, $novaHoraInicio, $origem = 'cliente')
    {
        try {
            // 1. Impede remarcação retroativa no passado
            if ($novaData < date('Y-m-d')) {
                return ['sucesso' => false, 'mensagem' => 'Não é possível remarcar para datas passadas.'];
            }

            // 1.5 Validação de limites futuros configurados no painel administrativo
            if ($origem === 'cliente') {
                require_once __DIR__ . '/../Models/Configuracao.php';
                $configModel = new Configuracao();
                $limiteDiasVal = $configModel->obterValor('limite_agendamento_futuro_dias', 'sem_limite');
                
                if ($limiteDiasVal !== 'sem_limite' && is_numeric($limiteDiasVal)) {
                    $limiteDiasInt = (int)$limiteDiasVal;
                    $maxDataObj = new DateTime('now', new DateTimeZone('America/Sao_Paulo'));
                    $maxDataObj->modify("+{$limiteDiasInt} days");
                    $maxData = $maxDataObj->format('Y-m-d');
                    
                    if ($novaData > $maxData) {
                        $dataPt = date('d/m/Y', strtotime($maxData));
                        return [
                            'sucesso' => false,
                            'mensagem' => "Não é possível remarcar agendamentos com mais de " . $this->traduzirDiasParaTexto($limiteDiasInt) . " de antecedência (limite: $dataPt)."
                        ];
                    }
                }
            }

            // 2. Inicia a Transação
            if (!$this->conn->inTransaction()) {
                $this->conn->beginTransaction();
            }

            // 3. Recupera o agendamento atual
            $agendamento = $this->agendamentoModel->buscarPorId($idAgendamento);
            if (!$agendamento) {
                throw new Exception("Agendamento não encontrado.");
            }

            // Impede remarcação de atendimentos já cancelados ou finalizados
            if (in_array($agendamento['status'], ['concluido', 'cancelado'])) {
                throw new Exception("Não é possível remarcar um agendamento concluído ou cancelado.");
            }

            $idFuncionario = $agendamento['cod_funcionario'];
            $duracao = (int) $agendamento['duracao'];

            // 4. Calcula a nova hora final baseando-se na duração original do serviço
            $horaFimObj = new DateTime($novaHoraInicio);
            $horaFimObj->modify("+{$duracao} minutes");
            $novaHoraFim = $horaFimObj->format('H:i:s');

            // 5. Prevenção de Overbooking: ignora o próprio ID atual para não colidir consigo mesmo
            $conflito = $this->agendamentoModel->verificarConflitoHorario($idFuncionario, $novaData, $novaHoraInicio, $novaHoraFim, $idAgendamento);
            if ($conflito) {
                throw new Exception("O profissional já possui um agendamento neste horário.");
            }

            // 6. Verifica se a nova data não bate com bloqueios manuais (férias, folgas) definidos pelo profissional
            require_once __DIR__ . '/../Models/Disponibilidade.php';
            $disponibilidadeModel = new Disponibilidade();
            $bloqueios = $disponibilidadeModel->buscarBloqueiosDia($idFuncionario, $novaData);
            
            if (is_array($bloqueios)) {
                $inicioNovo = strtotime($novaHoraInicio);
                $fimNovo = strtotime($novaHoraFim);
                foreach ($bloqueios as $b) {
                    $inicioBloqueio = strtotime($b['hora_inicio']);
                    $fimBloqueio = strtotime($b['hora_fim']);
                    
                    // Se houver sobreposição nos intervalos de tempo, impede a remarcação
                    if ($inicioNovo < $fimBloqueio && $fimNovo > $inicioBloqueio) {
                        throw new Exception("Este horário está bloqueado pelo profissional.");
                    }
                }
            }

            // 7. Atualiza os dados no banco de dados (Capa e Itens)
            $sqlCapa = "UPDATE agendamentos SET data_agendamento = :data WHERE id_agendamento = :id";
            $stmtCapa = $this->conn->prepare($sqlCapa);
            $stmtCapa->execute([
                ':data' => $novaData,
                ':id' => $idAgendamento
            ]);

            $sqlItem = "UPDATE itens_agendamento SET hora_inicio = :hora_inicio, hora_fim = :hora_fim WHERE cod_agendamento = :id";
            $stmtItem = $this->conn->prepare($sqlItem);
            $stmtItem->execute([
                ':hora_inicio' => $novaHoraInicio,
                ':hora_fim' => $novaHoraFim,
                ':id' => $idAgendamento
            ]);

            // Confirma todas as alterações no banco
            $this->conn->commit();

            // =================================================================
            // DISPARO DE NOTIFICAÇÕES DE REMARCAÇÃO (Assíncrono via Shutdown Function)
            // =================================================================
            ignore_user_abort(true);
            register_shutdown_function(function() use ($idAgendamento, $origem) {
                try {
                    $agendamentoCompleto = $this->agendamentoModel->buscarPorId($idAgendamento);
                    if ($agendamentoCompleto) {
                        $oneSignal = new OneSignalService();
                        $emailService = new EmailService();
                        
                        $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                        $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                        $urlBase = "$protocolo://$host" . BASE_URL;

                        $dataPt = date('d/m/Y', strtotime($agendamentoCompleto['data_agendamento']));
                        $horaPt = substr($agendamentoCompleto['hora_inicio'], 0, 5);

                        if ($origem === 'cliente') {
                            // Remarcado pelo cliente -> Notifica o profissional
                            if (!empty($agendamentoCompleto['funcionario_cod_usuario'])) {
                                $msg = "📅 Agendamento Remarcado: {$agendamentoCompleto['cliente_nome']} alterou {$agendamentoCompleto['nome_servico']} para o dia $dataPt às $horaPt.";
                                $oneSignal->enviarNotificacao(
                                    $agendamentoCompleto['funcionario_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/funcionario/agenda',
                                    "Agendamento Remarcado"
                                );
                            }
                            if (!empty($agendamentoCompleto['funcionario_email'])) {
                                $assunto = "Agendamento Remarcado pelo Cliente - Belezou App";
                                $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                    <h2>Olá, {$agendamentoCompleto['funcionario_nome']}!</h2>
                                    <p>O cliente remarcou um agendamento existente:</p>
                                    <p><strong>Cliente:</strong> {$agendamentoCompleto['cliente_nome']}</p>
                                    <p><strong>Serviço:</strong> {$agendamentoCompleto['nome_servico']}</p>
                                    <p><strong>Nova Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                    <p>Acesse o painel do sistema para gerenciar sua agenda.</p>
                                </div>";
                                $emailService->enviar($agendamentoCompleto['funcionario_email'], $agendamentoCompleto['funcionario_nome'], $assunto, $corpoHtml);
                            }
                        } else {
                            // Remarcado pelo profissional/gerência -> Notifica o cliente
                            if (!empty($agendamentoCompleto['cliente_cod_usuario'])) {
                                $msg = "✨ Agendamento Remarcado: O seu horário de {$agendamentoCompleto['nome_servico']} foi remarcado para o dia $dataPt às $horaPt.";
                                $oneSignal->enviarNotificacao(
                                    $agendamentoCompleto['cliente_cod_usuario'], 
                                    $msg, 
                                    $urlBase . '/historico',
                                    "Agendamento Remarcado"
                                );
                            }
                            if (!empty($agendamentoCompleto['cliente_email'])) {
                                $assunto = "Seu Agendamento foi Remarcado! - Belezou App";
                                $corpoHtml = "<div style='padding: 20px; font-family: sans-serif; color: #333;'>
                                    <h2>Olá, {$agendamentoCompleto['cliente_nome']}!</h2>
                                    <p>O seu agendamento foi remarcado pelo profissional:</p>
                                    <p><strong>Serviço:</strong> {$agendamentoCompleto['nome_servico']}</p>
                                    <p><strong>Profissional:</strong> {$agendamentoCompleto['funcionario_nome']}</p>
                                    <p><strong>Nova Data/Hora:</strong> {$dataPt} às {$horaPt}</p>
                                    <p>Agradecemos a preferência!</p>
                                </div>";
                                $emailService->enviar($agendamentoCompleto['cliente_email'], $agendamentoCompleto['cliente_nome'], $assunto, $corpoHtml);
                            }
                        }
                    }
                } catch (Exception $e) {
                    error_log("Aviso: Falha ao enviar notificação de remarcação em background: " . $e->getMessage());
                }
            });

            return [
                'sucesso' => true,
                'mensagem' => 'Agendamento remarcado com sucesso!'
            ];

        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }
            error_log("Erro em remarcarAgendamento: " . $e->getMessage());
            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    /**
     * Traduz uma contagem de dias em uma representação legível para exibições de limites ao cliente.
     */
    private function traduzirDiasParaTexto($dias)
    {
        if ($dias == 7) return "1 semana";
        if ($dias == 14) return "2 semanas";
        if ($dias == 21) return "3 semanas";
        if ($dias == 30) return "1 mês";
        if ($dias == 60) return "2 meses";
        if ($dias == 90) return "3 meses";
        if ($dias == 180) return "6 meses";
        return "{$dias} dias";
    }
}
