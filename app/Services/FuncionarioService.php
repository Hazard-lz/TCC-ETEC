<?php

require_once __DIR__ . '/BaseService.php';
require_once __DIR__ . '/../../database/Conexao.php';
require_once __DIR__ . '/UsuarioService.php';
require_once __DIR__ . '/../Models/Funcionario.php';
require_once __DIR__ . '/../Models/Usuario.php';

class FuncionarioService extends BaseService {

    private $usuarioService;
    private $funcionarioModel;
    private $conn;

    public function __construct() {
        $this->usuarioService = new UsuarioService();
        $this->funcionarioModel = new Funcionario();
        $this->conn = Conexao::getConexao();
    }

    public function registrarFuncionario($nome, $email, $telefone, $especialidade, $salario, $tipo = 'comum') {
        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            // 1. O UsuarioService cria o login e manda o e-mail
            $resultadoUsuario = $this->usuarioService->registrarUsuarioDaEquipe($nome, $email, $telefone, $tipo);

            if (!$resultadoUsuario['sucesso']) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            $idUsuario = $resultadoUsuario['id'];

            // 2. Cria o vínculo na tabela funcionarios
            $idFuncionario = $this->funcionarioModel->cadastrar($idUsuario, $especialidade, $salario);

            if (!$idFuncionario) {
                throw new Exception("Falha ao registrar os dados profissionais.");
            }

            $this->conn->commit();
            return $this->sucesso($resultadoUsuario['mensagem'], ['id_funcionario' => $idFuncionario]);

        } catch (Exception $e) {
            $this->conn->rollBack();
            return $this->erro('Erro ao registrar funcionário: ' . $e->getMessage());
        }
    }

    public function atualizarDadosFuncionario($id_usuario, $id_funcionario, $nome, $telefone, $especialidade, $salario) {
        
        if (empty($id_usuario) || empty($id_funcionario)) {
            return $this->erro('Parâmetros de identificação do funcionário estão ausentes.');
        }

        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            $resultadoUsuario = $this->usuarioService->atualizarUsuario($id_usuario, $nome, $telefone);
            
            if ($resultadoUsuario['sucesso'] === false) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            if (!$this->funcionarioModel->atualizar($id_funcionario, $especialidade, $salario)) {
                throw new Exception("Erro ao atualizar o contrato do funcionário.");
            }


            $this->conn->commit();

            return $this->sucesso('Dados do funcionário atualizados com sucesso!');

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro na atualização do funcionário: " . $e->getMessage());
            
            return $this->erro('Não foi possível salvar as alterações do funcionário.');
        }
    }

    public function atualizarServicosFuncionario($id_funcionario, $array_servicos = []) {
        if (empty($id_funcionario)) {
            return $this->erro('ID do funcionário não foi fornecido.');
        }

        if ($this->funcionarioModel->atualizarServicos($id_funcionario, $array_servicos)) {
            return $this->sucesso('Serviços vinculados com sucesso!');
        } else {
            return $this->erro('Ocorreu um erro ao vincular os serviços ao funcionário.');
        }
    }

    /**
     * ARQUITETURA: Lógica de negócio isolada no Service.
     * Altera o status de acesso do usuário vinculado ao funcionário.
     */
    public function alterarStatusFuncionario($id_usuario, $novo_status) {
        if (empty($id_usuario) || !in_array($novo_status, ['ativo', 'inativo'])) {
            return $this->erro('Parâmetros inválidos fornecidos para a alteração.');
        }

        try {
            // Reutiliza o Model de Usuário que já tem o método pronto
            $usuarioModel = new Usuario();
            
            if ($usuarioModel->atualizarStatus($id_usuario, $novo_status)) {
                $acao = ($novo_status === 'ativo') ? 'ativado' : 'inativado';
                return $this->sucesso("O acesso do funcionário foi {$acao} com sucesso!");
            } else {
                return $this->erro('Não foi possível alterar o status. O registro pode não existir.');
            }
        } catch (Exception $e) {
            error_log("Erro ao alterar status do funcionário (ID Usuário: $id_usuario): " . $e->getMessage());
            return $this->erro('Ocorreu um erro interno ao tentar atualizar o status.');
        }
    }
}