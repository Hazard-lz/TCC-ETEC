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

    public function atualizarDadosFuncionario($id_usuario, $id_funcionario, $nome, $telefone, $especialidade, $salario, $tipo = null, $idLogado = null, $tipoLogado = null, $email = null, $senha = null) {
        
        if (empty($id_usuario) || empty($id_funcionario)) {
            return $this->erro('Parâmetros de identificação do funcionário estão ausentes.');
        }

        // ═══ REGRA: ADMIN NÃO PODE REBAIXAR A SI MESMO DIRETAMENTE ═══
        if ($id_usuario === $idLogado && $tipoLogado === 'admin' && $tipo !== null && $tipo !== 'admin') {
            return $this->erro("Você não pode rebaixar seu próprio cargo. Para deixar de ser administrador, transfira o cargo para outro funcionário.");
        }

        try {
            if (!$this->conn->inTransaction()) { $this->conn->beginTransaction(); }

            $resultadoUsuario = $this->usuarioService->atualizarUsuario($id_usuario, $nome, $telefone, $email);
            
            if ($resultadoUsuario['sucesso'] === false) {
                $this->conn->rollBack();
                return $resultadoUsuario; 
            }

            if (!$this->funcionarioModel->atualizar($id_funcionario, $especialidade, $salario)) {
                throw new Exception("Erro ao atualizar o contrato do funcionário.");
            }

            $usuarioModel = new Usuario();

            // ═══ ATUALIZAÇÃO DE SENHA ═══
            if (!empty($senha)) {
                if (strlen($senha) < 8) {
                    throw new Exception("A senha deve ter no mínimo 8 caracteres.");
                }
                $senhaHash = password_hash($senha, PASSWORD_DEFAULT);
                $usuarioModel->atualizarSenha($id_usuario, $senhaHash);
            }

            // ═══ ATUALIZAÇÃO DE TIPO / ACESSO ═══
            if ($tipo !== null) {
                // Caso especial: Transferência de Admin (Admin Único)
                if ($tipo === 'admin' && $tipoLogado === 'admin' && $id_usuario !== $idLogado) {
                    // Promove o alvo
                    $usuarioModel->atualizarTipo($id_usuario, 'admin');
                    // Rebaixa o atual
                    $usuarioModel->atualizarTipo($idLogado, 'subadmin');
                    // Nota: A sessão deve ser atualizada no Controller
                } else {
                    // Atualização normal
                    $usuarioModel->atualizarTipo($id_usuario, $tipo);
                }
            }

            $this->conn->commit();
            return $this->sucesso('Dados do funcionário atualizados com sucesso!');

        } catch (Exception $e) {
            $this->conn->rollBack();
            error_log("Erro na atualização do funcionário: " . $e->getMessage());
            
            return $this->erro($e->getMessage() ?: 'Não foi possível salvar as alterações do funcionário.');
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
    public function alterarStatusFuncionario($id_usuario, $novo_status, $idLogado = null) {
        if (empty($id_usuario) || !in_array($novo_status, ['ativo', 'inativo'])) {
            return $this->erro('Parâmetros inválidos fornecidos para a alteração.');
        }

        // ═══ REGRA: NÃO É POSSÍVEL ALTERAR O PRÓPRIO STATUS ═══
        if ($id_usuario === $idLogado) {
            return $this->erro("Você não pode alterar o status do seu próprio acesso.");
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