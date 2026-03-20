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

    public function registrarFuncionario($nome, $email, $senha, $telefone, $especialidade, $salario, $array_servicos = []) {
        
        try {
            $this->conn->beginTransaction();

            $resultadoUsuario = $this->usuarioService->registrarUsuario($nome, $email, $senha, 'comum', $telefone);

            if ($resultadoUsuario['sucesso'] === false) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            $idNovoUsuario = $resultadoUsuario['id'];

            $idFuncionario = $this->funcionarioModel->cadastrar($idNovoUsuario, $especialidade, $salario);

            if (!$idFuncionario) {
                throw new Exception("Falha ao registrar os dados contratuais do funcionário no banco.");
            }

            if (!empty($array_servicos)) {
                $vinculou = $this->funcionarioModel->cadastrarServicos($idFuncionario, $array_servicos);
                
                if (!$vinculou) {
                    throw new Exception("Falha ao vincular a lista de serviços ao funcionário.");
                }
            }

            $this->conn->commit();

            return $this->sucesso('Funcionário registrado com sucesso!', [
                'id_funcionario' => $idFuncionario
            ]);

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro no registro unificado de funcionário: " . $e->getMessage());
            
            return $this->erro('Ocorreu um erro interno ao cadastrar o funcionário.');
        }
    }

    public function atualizarDadosFuncionario($id_usuario, $id_funcionario, $nome, $telefone, $especialidade, $salario) {
        
        if (empty($id_usuario) || empty($id_funcionario)) {
            return $this->erro('Parâmetros de identificação do funcionário estão ausentes.');
        }

        try {
            $this->conn->beginTransaction();

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
}
?>