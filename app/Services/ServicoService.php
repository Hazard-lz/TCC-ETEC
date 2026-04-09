<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../Models/Servico.php';

class ServicoService extends BaseService {

    private $servicoModel;

    public function __construct() {
        $this->servicoModel = new Servico();
    }

    public function registrarServico($nome, $descricao, $preco, $duracao) {
        
        $nome = trim($nome);
        $descricao = trim($descricao);

        if (empty($nome) || empty($descricao)) {
            return $this->erro('O nome e a descrição do serviço são obrigatórios.');
        }

        if (strlen($nome) > 100) {
            return $this->erro('O nome do serviço é muito grande. O limite é de 100 caracteres.');
        }

        if (!is_numeric($preco) || $preco < 0) {
            return $this->erro('O preço do serviço não pode ser negativo.');
        }

        if (!is_numeric($duracao) || $duracao <= 0 || $duracao > 480 || $duracao % 5 !== 0) {
            return $this->erro('Duração inválida. Deve ser entre 1 e 480, em blocos de 5 min.');
        }
        
        $servicosAtivos = $this->servicoModel->listarPorStatus('ativo');
        $servicosInativos = $this->servicoModel->listarPorStatus('inativo');
        $todosServicos = array_merge($servicosAtivos, $servicosInativos);
        
        foreach ($todosServicos as $servico) {
            if (strtolower(trim($servico['nome_servico'])) === strtolower($nome)) {
                return $this->erro('Já existe um serviço cadastrado com este exato nome e preço.');
            }
        }

        $idServico = $this->servicoModel->cadastrar($nome, $descricao, $preco, $duracao);

        if ($idServico) {
            return $this->sucesso('Serviço cadastrado com sucesso!', ['id_servico' => $idServico]);
        } else {
            return $this->erro('Ocorreu um erro interno ao cadastrar o serviço.');
        }
    }

    public function atualizarDadosServico($id_servico, $nome, $descricao, $preco, $duracao) {
        
        if (empty($id_servico)) {
            return $this->erro('ID do serviço não foi fornecido.');
        }

        $nome = trim($nome);
        $descricao = trim($descricao);

        if (empty($nome) || empty($descricao)) {
            return $this->erro('O nome e a descrição são obrigatórios.');
        }

        if (strlen($nome) > 100) {
            return $this->erro('O nome excede o limite de 100 caracteres.');
        }

        if (!is_numeric($preco) || $preco < 0) {
            return $this->erro('O preço não pode ser negativo.');
        }

        if (!is_numeric($duracao) || $duracao <= 0 || $duracao > 480 || $duracao % 5 !== 0) {
            return $this->erro('Duração inválida.');
        }
       
        $servicosAtivos = $this->servicoModel->listarPorStatus('ativo');
        $servicosInativos = $this->servicoModel->listarPorStatus('inativo');
        $todosServicos = array_merge($servicosAtivos, $servicosInativos);
        
        foreach ($todosServicos as $servico) {
            $mesmoNome = strtolower(trim($servico['nome_servico'])) === strtolower($nome);
            $idDiferente = $servico['id_servico'] != $id_servico; 

            if ($mesmoNome && $idDiferente) {
                return $this->erro('Outro serviço já usa esse mesmo nome.');
            }
        }

        $atualizou = $this->servicoModel->atualizar($id_servico, $nome, $descricao, $preco, $duracao);

        if ($atualizou) {
            return $this->sucesso('Serviço atualizado com sucesso!');
        } else {
            return $this->erro('Falha ao gravar as atualizações no banco de dados.');
        }
    }

    public function alterarStatusServico($id_servico, $status) {
        
        if (empty($id_servico)) {
            return $this->erro('ID do serviço ausente.');
        }

        if (!in_array($status, ['ativo', 'inativo'])) {
            return $this->erro('Status inválido. Use "ativo" ou "inativo".');
        }

        $alterou = $this->servicoModel->atualizarStatus($id_servico, $status);

        if ($alterou) {
            return $this->sucesso("O status do serviço foi alterado para $status.");
        } else {
            return $this->erro('Ocorreu um erro ao tentar mudar o status do serviço.');
        }
    }
}