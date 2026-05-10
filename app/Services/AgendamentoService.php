<?php
require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Agendamento.php';
require_once __DIR__ . '/../Models/Servico.php';
require_once __DIR__ . '/../../database/Conexao.php';
require_once __DIR__ . '/OneSignalService.php';

class AgendamentoService extends BaseService
{

    private $agendamentoModel;
    private $servicoModel;
    private $conn;

    public function __construct()
    {
        $this->agendamentoModel = new Agendamento();
        $this->servicoModel = new Servico();
        $this->conn = Conexao::getConexao();
    }

    /**
     * Orquestra a criação do agendamento validando os tempos e valores no backend.
     * $idCliente e $idFuncionario devem ser os IDs das tabelas `clientes` e `funcionarios`, e não de `usuarios`.
     */
    public function realizarAgendamento($idCliente, $idFuncionario, $idServico, $data, $horaInicio, $idFuncionarioCriador = null)
    {
        try {
            // 1. Validação de data: impede agendamentos no passado
            if ($data < date('Y-m-d')) {
                return ['sucesso' => false, 'mensagem' => 'Não é possível agendar em datas passadas.'];
            }

            // 2. Inicia Transação (Garante que agendamento e itens_agendamento sejam salvos juntos)
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            // 3. Busca do serviço: usa os nomes reais do schema (preco e duracao)
            $servico = $this->servicoModel->buscarPorId($idServico);
            if (!$servico) {
                throw new Exception("O serviço selecionado não existe ou está inativo.");
            }

            $duracao = (int) $servico['duracao'];
            $preco = (float) $servico['preco'];
            $nomeServico = $servico['nome_servico'];


            // 3. Cálculos de Tempo: Evita falhas usando a classe nativa do PHP
            $horaFimObj = new DateTime($horaInicio);
            $horaFimObj->modify("+{$duracao} minutes");
            $horaFim = $horaFimObj->format('H:i:s'); // TIME format do MySQL

            // 4. Verificação de Overbooking
            $conflito = $this->agendamentoModel->verificarConflitoHorario($idFuncionario, $data, $horaInicio, $horaFim);
            if ($conflito) {
                throw new Exception("O profissional já possui um agendamento neste horário.");
            }

            // 5. Verificação da tabela associativa (funcionario_servicos)
            $vinculo = $this->agendamentoModel->buscarVinculoFuncionarioServico($idFuncionario, $idServico);
            if (!$vinculo) {
                throw new Exception("Este profissional não está vinculado para realizar este serviço.");
            }

            // Se não houver idFuncionarioCriador, significa que foi o próprio cliente no site
            $statusInicial = ($idFuncionarioCriador === null) ? 'pendente' : 'marcado';
            // 6. Insere na tabela 'agendamentos' (Capa)
            $idAgendamento = $this->agendamentoModel->cadastrarAgendamento(
                $idCliente,
                $idFuncionarioCriador,
                $data,
                $statusInicial
            );

            if (!$idAgendamento) {
                throw new Exception("Falha ao registrar os dados principais do agendamento.");
            }

            // 7. Insere na tabela 'itens_agendamento'
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

            // 8. Efetiva a gravação no banco de dados
            $this->conn->commit();

            // ==========================================
            // INTEGRAÇÃO ONESIGNAL (Novo Agendamento)
            // ==========================================
            try {
                $agendamentoCompleto = $this->agendamentoModel->buscarPorId($idAgendamento);
                if ($agendamentoCompleto) {
                    $oneSignal = new OneSignalService();
                    
                    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $urlBase = "$protocolo://$host" . BASE_URL;

                    if ($idFuncionarioCriador === null) {
                        // Criado pelo CLIENTE -> Avisa o FUNCIONÁRIO
                        if (!empty($agendamentoCompleto['funcionario_cod_usuario'])) {
                            $dataPt = date('d/m', strtotime($agendamentoCompleto['data_agendamento']));
                            $horaPt = substr($agendamentoCompleto['hora_inicio'], 0, 5);
                            $msg = "📅 Novo Agendamento: {$agendamentoCompleto['cliente_nome']} solicitou {$agendamentoCompleto['nome_servico']} para o dia $dataPt às $horaPt.";
                            
                            $oneSignal->enviarNotificacao(
                                $agendamentoCompleto['funcionario_cod_usuario'], 
                                $msg, 
                                $urlBase . '/funcionario/agenda',
                                "Novo Agendamento"
                            );
                        }
                    } else {
                        // Criado pelo FUNCIONÁRIO -> Avisa o CLIENTE
                        if (!empty($agendamentoCompleto['cliente_cod_usuario'])) {
                            $dataPt = date('d/m', strtotime($agendamentoCompleto['data_agendamento']));
                            $horaPt = substr($agendamentoCompleto['hora_inicio'], 0, 5);
                            $msg = "✨ Agendamento Marcado: Um novo horário de {$agendamentoCompleto['nome_servico']} foi reservado para você no dia $dataPt às $horaPt.";
                            
                            $oneSignal->enviarNotificacao(
                                $agendamentoCompleto['cliente_cod_usuario'], 
                                $msg, 
                                $urlBase . '/historico',
                                "Novo Agendamento"
                            );
                        }
                    }
                }
            } catch (Exception $e) {
                error_log("Aviso: Falha ao enviar notificação de novo agendamento: " . $e->getMessage());
            }

            return [
                'sucesso' => true,
                'mensagem' => 'Agendamento confirmado com sucesso!',
                'id_agendamento' => $idAgendamento
            ];
        } catch (Exception $e) {
            if ($this->conn->inTransaction()) {
                $this->conn->rollBack();
            }

            // Log discreto no servidor para análise futura
            error_log("Erro no AgendamentoService: " . $e->getMessage());

            return [
                'sucesso' => false,
                'mensagem' => $e->getMessage()
            ];
        }
    }

    /**
     * Altera o status verificando a integridade com o ENUM do Schema.
     */
    public function alterarStatus($idAgendamento, $novoStatus, $origem = 'funcionario')
    {
        $statusValidos = ['pendente', 'concluido', 'cancelado', 'marcado'];

        if (!in_array($novoStatus, $statusValidos)) {
            return ['sucesso' => false, 'mensagem' => 'Status inválido.'];
        }

        // Validação de transição de estado: status finalizados não podem regredir
        $agendamento = $this->agendamentoModel->buscarPorId($idAgendamento);
        if ($agendamento && in_array($agendamento['status'], ['concluido', 'cancelado']) && $novoStatus !== $agendamento['status']) {
            return ['sucesso' => false, 'mensagem' => 'Não é possível alterar um agendamento que já foi ' . $agendamento['status'] . '.'];
        }

        $atualizou = $this->agendamentoModel->atualizarStatus($idAgendamento, $novoStatus);

        if ($atualizou) {
            // ==========================================
            // INTEGRAÇÃO ONESIGNAL (Cenários 1 e 3)
            // ==========================================
            try {
                $agendamentoAtualizado = $this->agendamentoModel->buscarPorId($idAgendamento);
                if ($agendamentoAtualizado) {
                    $oneSignal = new OneSignalService();
                    
                    // Monta a URL base dinamicamente (para funcionar tanto local quanto na Hostinger)
                    $protocolo = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on') ? "https" : "http";
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $urlBase = "$protocolo://$host" . BASE_URL;

                    if ($origem === 'cliente' && $novoStatus === 'cancelado') {
                        // O cliente cancelou, avisa o funcionário
                        if (!empty($agendamentoAtualizado['funcionario_cod_usuario'])) {
                            $dataPt = date('d/m', strtotime($agendamentoAtualizado['data_agendamento']));
                            $msg = "⚠️ Cancelamento: {$agendamentoAtualizado['cliente_nome']} cancelou o agendamento de {$agendamentoAtualizado['nome_servico']} para o dia $dataPt às {$agendamentoAtualizado['hora_inicio']}.";
                            
                            $oneSignal->enviarNotificacao(
                                $agendamentoAtualizado['funcionario_cod_usuario'], 
                                $msg, 
                                $urlBase . '/funcionario/agenda',
                                "Agenda Atualizada"
                            );
                        }
                    } else {
                        // O funcionário/admin alterou, avisa o cliente
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
                    }
                }
            } catch (Exception $e) {
                error_log("Aviso: Falha ao enviar notificação push: " . $e->getMessage());
            }

            return ['sucesso' => true, 'mensagem' => 'Status do agendamento atualizado.'];
        }

        return ['sucesso' => false, 'mensagem' => 'Falha ao atualizar o status.'];
    }
}
