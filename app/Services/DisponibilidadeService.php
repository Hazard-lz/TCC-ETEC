<?php

// Importa o Model de Disponibilidade para poder gravar no banco
require_once __DIR__ . '/../Models/Disponibilidade.php';

class DisponibilidadeService {

    private $disponibilidadeModel;

    public function __construct() {
        $this->disponibilidadeModel = new Disponibilidade();
    }

    // FUNÇÃO AUXILIAR (PRIVADA)
    // Por que: Como as regras de tempo são chatas de validar, isolamos isso aqui.
    // Assim, o Cadastro e a Atualização chamam essa mesma função para conferir as horas.
    private function validarLogicaDeHorarios($inicio, $fim, $int_inicio, $int_fim) {
        
        // 1. Obrigatoriedade do expediente
        if (empty($inicio) || empty($fim)) {
            return ['sucesso' => false, 'mensagem' => 'A hora de início e fim do expediente são obrigatórias.'];
        }

        // strtotime() transforma "09:00" em um número calculável (timestamp). 
        // Isso permite saber matematicamente quem vem primeiro.
        $tempoInicio = strtotime($inicio);
        $tempoFim = strtotime($fim);

        // 2. Lógica de horário. 
        if ($tempoFim <= $tempoInicio) {
            return ['sucesso' => false, 'mensagem' => 'A hora de saída deve ser MAIOR que a hora de entrada.'];
        }

        // 3. Validação do Intervalo (Almoço/Pausa)
        // Se a pessoa preencheu apenas UMA das caixas de intervalo, o sistema barra. Tem que ser as duas ou nenhuma.
        if ((empty($int_inicio) && !empty($int_fim)) || (!empty($int_inicio) && empty($int_fim))) {
            return ['sucesso' => false, 'mensagem' => 'Se houver intervalo, preencha o início e o fim dele.'];
        }

        // Verifica se os dois intervalos foram preenchidos.
        if (!empty($int_inicio) && !empty($int_fim)) {
            $tempoIntInicio = strtotime($int_inicio);
            $tempoIntFim = strtotime($int_fim);

            if ($tempoIntFim <= $tempoIntInicio) {
                return ['sucesso' => false, 'mensagem' => 'O fim do intervalo deve ser DEPOIS do início do intervalo.'];
            }

            // O intervalo não pode começar antes do funcionário chegar no salão, nem terminar depois que ele for embora.
            if ($tempoIntInicio <= $tempoInicio || $tempoIntFim >= $tempoFim) {
                return ['sucesso' => false, 'mensagem' => 'O horário de intervalo deve estar DENTRO do horário de expediente.'];
            }
        }

        // Se não barrou em nenhum IF acima, os horários estão perfeitos!
        return ['sucesso' => true];
    }


    // MÉTODO DE CADASTRO
    public function registrarDisponibilidade($cod_funcionario, $hora_inicio, $hora_fim, $intervalo_inicio, $intervalo_fim, $dias_semana) {
        
        if (empty($cod_funcionario)) {
            return ['sucesso' => false, 'mensagem' => 'ID do funcionário não informado.'];
        }

        // 1. Valida se o gerente marcou pelo menos um dia na semana (Não dá pra trabalhar 0 dias).
        if (empty($dias_semana) || !is_array($dias_semana)) {
            return ['sucesso' => false, 'mensagem' => 'Selecione pelo menos um dia da semana.'];
        }

        // 2. Proteção contra injeção de dados falsos nos dias da semana.
        $diasValidos = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        foreach ($dias_semana as $dia) {
            if (!in_array($dia, $diasValidos)) {
                return ['sucesso' => false, 'mensagem' => 'Um ou mais dias da semana enviados são inválidos.'];
            }
        }

        // 3. Chama a função privada para conferir as horas.
        $validacaoHoras = $this->validarLogicaDeHorarios($hora_inicio, $hora_fim, $intervalo_inicio, $intervalo_fim);
        
        // Se a validação falhou, devolve o erro dela para o Controller.
        if (!$validacaoHoras['sucesso']) {
            return $validacaoHoras;
        }

        // Transformar strings vazias do formulário em NULL para o banco de dados.
        $int_inicio = empty(trim($intervalo_inicio)) ? null : $intervalo_inicio;
        $int_fim = empty(trim($intervalo_fim)) ? null : $intervalo_fim;

        // 4. Salva a grade principal no banco (Agora envia SÓ o funcionário).
        $idDisponibilidade = $this->disponibilidadeModel->cadastrar($cod_funcionario);

        if ($idDisponibilidade) {
            // 5. Salva os dias da semana atrelados e PASSA AS HORAS JUNTO.
            $vinculouDias = $this->disponibilidadeModel->cadastrarDias(
                $idDisponibilidade, 
                $dias_semana, 
                $hora_inicio, 
                $hora_fim, 
                $int_inicio, 
                $int_fim
            );
            
            if ($vinculouDias) {
                return ['sucesso' => true, 'mensagem' => 'Disponibilidade cadastrada com sucesso!'];
            } else {
                // Caso raríssimo: Criou a grade mas falhou nos dias. Apagamos a grade para não deixar lixo no banco (Manual Rollback).
                $this->disponibilidadeModel->excluir($idDisponibilidade);
                return ['sucesso' => false, 'mensagem' => 'Erro ao vincular os dias. O cadastro foi cancelado.'];
            }
        } else {
            return ['sucesso' => false, 'mensagem' => 'Ocorreu um erro interno ao salvar os horários.'];
        }
    }


    // MÉTODO DE ATUALIZAÇÃO
    public function atualizarDisponibilidade($id_disponibilidade, $hora_inicio, $hora_fim, $intervalo_inicio, $intervalo_fim, $dias_semana) {
        
        if (empty($id_disponibilidade)) {
            return ['sucesso' => false, 'mensagem' => 'Identificação da grade ausente.'];
        }

        if (empty($dias_semana) || !is_array($dias_semana)) {
            return ['sucesso' => false, 'mensagem' => 'Selecione pelo menos um dia da semana.'];
        }

        $diasValidos = ['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab'];
        foreach ($dias_semana as $dia) {
            if (!in_array($dia, $diasValidos)) {
                return ['sucesso' => false, 'mensagem' => 'Dia da semana inválido detectado.'];
            }
        }

        // Reaproveita a validação rigorosa de horários
        $validacaoHoras = $this->validarLogicaDeHorarios($hora_inicio, $hora_fim, $intervalo_inicio, $intervalo_fim);
        if (!$validacaoHoras['sucesso']) {
            return $validacaoHoras;
        }

        // Formata os intervalos (se vier vazio do formulário, transforma em NULL para o banco)
        $int_inicio = empty(trim($intervalo_inicio)) ? null : $intervalo_inicio;
        $int_fim = empty(trim($intervalo_fim)) ? null : $intervalo_fim;

        // 1. Exclui os dias velhos atrelados àquele ID (Isso limpa os dias e os horários antigos)
        $this->disponibilidadeModel->excluirDias($id_disponibilidade);
        
        // 2. Insere os novos dias com os horários atualizados que vieram do formulário de edição
        $atualizou = $this->disponibilidadeModel->cadastrarDias(
             $id_disponibilidade, 
             $dias_semana, 
             $hora_inicio, 
             $hora_fim, 
             $int_inicio, 
             $int_fim
        );

        if ($atualizou) {
            return ['sucesso' => true, 'mensagem' => 'Grade de horários atualizada com sucesso!'];
        } else {
            return ['sucesso' => false, 'mensagem' => 'Não foi possível atualizar a grade de horários.'];
        }
    }

    // MÉTODO DE EXCLUSÃO (HARD DELETE)
    public function excluirGrade($id_disponibilidade) {
        if (empty($id_disponibilidade)) {
            return ['sucesso' => false, 'mensagem' => 'ID da grade não informado.'];
        }

        // Como essa tabela não impacta o histórico de agendamentos, o DELETE é físico.
        $excluiu = $this->disponibilidadeModel->excluir($id_disponibilidade);

        if ($excluiu) {
            return ['sucesso' => true, 'mensagem' => 'A grade de horários foi removida com sucesso.'];
        } else {
            return ['sucesso' => false, 'mensagem' => 'Erro ao tentar remover a grade de horários.'];
        }
    }
}
?>