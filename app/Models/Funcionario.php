<?php
require_once __DIR__ . '/BaseModel.php';

class Funcionario extends BaseModel {

// CREATE/INSERT
    public function cadastrar($cod_usuario, $especialidade = null, $salario = null) {
        $especialidade = !empty(trim($especialidade)) ? trim($especialidade) : null;
        $salario = !empty(trim($salario)) ? str_replace(',', '.', trim($salario)) : null;

        $sql = "INSERT INTO funcionarios (cod_usuario, especialidade, salario) VALUES (:cod_usuario, :especialidade, :salario)";
        return $this->executarQuery($sql, [':cod_usuario' => $cod_usuario, ':especialidade' => $especialidade, ':salario' => $salario], 'id');
    }

    public function cadastrarServicos($cod_funcionario, $array_servicos) {
        try {
            $sql = "INSERT INTO funcionario_servicos (cod_funcionario, cod_servico) VALUES (:cod_funcionario, :cod_servico)";
            $stmt = $this->conn->prepare($sql);

            foreach ($array_servicos as $cod_servico) {
                $stmt->execute([':cod_funcionario' => $cod_funcionario, ':cod_servico' => $cod_servico]);
            }
            return true;
        } catch (PDOException $e) {
            error_log("Erro ao vincular serviços: " . $e->getMessage());
            return false;
        }
    }

// READ/SELECT
    public function listarTodos() {
        // ARQUITETURA: Adicionado u.email_verificado para sabermos quem já configurou a senha
        $sql = "SELECT f.id_funcionario, f.cod_usuario, f.especialidade, f.salario, 
                       u.nome, u.email, u.telefone, u.status, u.data_criacao, u.tipo, u.email_verificado
                FROM funcionarios f
                INNER JOIN usuarios u ON f.cod_usuario = u.id_usuario
                ORDER BY u.nome ASC";
        return $this->executarQuery($sql, [], 'todos');
    }

    public function buscarPorId($id_funcionario) {
        // Adicionado u.tipo no SELECT
        $sql = "SELECT f.*, u.nome, u.email, u.telefone, u.status, u.tipo 
                FROM funcionarios f
                INNER JOIN usuarios u ON f.cod_usuario = u.id_usuario
                WHERE f.id_funcionario = :id";
        return $this->executarQuery($sql, [':id' => $id_funcionario], 'unico');
    }

    public function buscarPorCodUsuario($cod_usuario) {
        $sql = "SELECT * FROM funcionarios WHERE cod_usuario = :cod_usuario";
        return $this->executarQuery($sql, [':cod_usuario' => $cod_usuario], 'unico');
    }

    public function buscarServicosPorFuncionario($cod_funcionario, $status_vinculo = 'ativo') {
        $sql = "SELECT fs.id_sv_funcionario, fs.cod_servico, fs.status AS status_vinculo, 
                       s.nome_servico, s.preco, s.duracao, s.status AS status_servico
                FROM funcionario_servicos fs
                INNER JOIN servicos s ON fs.cod_servico = s.id_servico
                WHERE fs.cod_funcionario = :cod_funcionario";
        
        $parametros = [':cod_funcionario' => $cod_funcionario];
        
        // Constrói a query dinamicamente usando a estrutura do BaseModel!
        if ($status_vinculo !== 'todos') {
            $sql .= " AND fs.status = :status_vinculo";
            $parametros[':status_vinculo'] = $status_vinculo;
        }
        
        return $this->executarQuery($sql, $parametros, 'todos');
    }

    public function buscarIdsServicosPorFuncionario($cod_funcionario, $status = 'ativo') {
        $sql = "SELECT cod_servico FROM funcionario_servicos WHERE cod_funcionario = :cod_funcionario";
        $parametros = [':cod_funcionario' => $cod_funcionario];
        
        if ($status !== 'todos') {
            $sql .= " AND status = :status";
            $parametros[':status'] = $status;
        }
        
        return $this->executarQuery($sql, $parametros, 'coluna');
    }

    public function buscarPorServico($id_servico) {
        $sql = "SELECT f.id_funcionario, f.especialidade, u.nome
                FROM funcionarios f
                INNER JOIN usuarios u ON f.cod_usuario = u.id_usuario
                INNER JOIN funcionario_servicos fs ON f.id_funcionario = fs.cod_funcionario
                WHERE u.status = 'ativo' 
                  AND fs.status = 'ativo' 
                  AND fs.cod_servico = :id_servico
                ORDER BY u.nome ASC";
                
        return $this->executarQuery($sql, [':id_servico' => $id_servico], 'todos');
    }

// UPDATE
    public function atualizar($id_funcionario, $especialidade, $salario) {
        $especialidade = !empty(trim($especialidade)) ? trim($especialidade) : null;
        $salario = !empty(trim($salario)) ? str_replace(',', '.', trim($salario)) : null;

        $sql = "UPDATE funcionarios SET especialidade = :especialidade, salario = :salario WHERE id_funcionario = :id";
        return $this->executarQuery($sql, [':especialidade' => $especialidade, ':salario' => $salario, ':id' => $id_funcionario]);
    }

    // Mantemos a transação complexa usando a conexão original (herdada do BaseModel)
    public function atualizarServicos($cod_funcionario, $novos_servicos) {
        try {
            $this->conn->beginTransaction();

            $sqlBusca = "SELECT cod_servico, status FROM funcionario_servicos WHERE cod_funcionario = :cod_funcionario";
            $stmtBusca = $this->conn->prepare($sqlBusca);
            $stmtBusca->execute([':cod_funcionario' => $cod_funcionario]);
            
            $historico_banco = $stmtBusca->fetchAll();
            $mapa_atual = []; 
            
            foreach ($historico_banco as $linha) {
                $mapa_atual[$linha['cod_servico']] = $linha['status'];
            }

            $sqlInsert = "INSERT INTO funcionario_servicos (cod_funcionario, cod_servico, status) VALUES (:cod_funcionario, :cod_servico, 'ativo')";
            $stmtInsert = $this->conn->prepare($sqlInsert);

            $sqlUpdate = "UPDATE funcionario_servicos SET status = :status WHERE cod_funcionario = :cod_funcionario AND cod_servico = :cod_servico";
            $stmtUpdate = $this->conn->prepare($sqlUpdate);

            if (!empty($novos_servicos)) {
                foreach ($novos_servicos as $cod_servico) {
                    if (array_key_exists($cod_servico, $mapa_atual)) {
                        if ($mapa_atual[$cod_servico] === 'inativo') {
                            $stmtUpdate->execute([':status' => 'ativo', ':cod_funcionario' => $cod_funcionario, ':cod_servico' => $cod_servico]);
                        }
                    } else {
                        $stmtInsert->execute([':cod_funcionario' => $cod_funcionario, ':cod_servico' => $cod_servico]);
                    }
                }
            }

            foreach ($mapa_atual as $cod_servico => $status) {
                if ($status === 'ativo' && !in_array($cod_servico, $novos_servicos)) {
                    $stmtUpdate->execute([':status' => 'inativo', ':cod_funcionario' => $cod_funcionario, ':cod_servico' => $cod_servico]);
                }
            }

            $this->conn->commit();
            return true;

        } catch (PDOException $e) {
            $this->conn->rollBack();
            error_log("Erro ao atualizar a grade de serviços: " . $e->getMessage());
            return false;
        }
    }
}
?>