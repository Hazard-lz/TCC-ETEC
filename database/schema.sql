-- 1. Tabela Base: usuarios
CREATE TABLE usuarios (
    id_usuario INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(100) NOT NULL,
    telefone VARCHAR(20) UNIQUE DEFAULT NULL,
    email VARCHAR(100) UNIQUE DEFAULT NULL,
    senha VARCHAR(255) DEFAULT NULL,
    tipo ENUM('admin', 'comum') NOT NULL DEFAULT 'comum',
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    codigo_verificacao VARCHAR(6) DEFAULT NULL,
    email_verificado TINYINT(1) NOT NULL DEFAULT 0,
    expiracao_codigo DATETIME DEFAULT NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
);

-- 2. Tabela: servicos
CREATE TABLE servicos (
    id_servico INT AUTO_INCREMENT PRIMARY KEY,
    nome_servico VARCHAR(100) NOT NULL,
    descricao TEXT NOT NULL, 
    preco DECIMAL(10, 2) NOT NULL,
    duracao INT NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo'
);

-- 3. Tabela: clientes 
CREATE TABLE clientes (
    id_cliente INT AUTO_INCREMENT PRIMARY KEY,
    cod_usuario INT UNIQUE NOT NULL,
    data_nascimento DATE DEFAULT NULL,
    observacoes TEXT DEFAULT NULL,
    -- CASCADE no usúario do cliente (se o usuário desse cliente for deletado, o cliente também é)
    FOREIGN KEY (cod_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- 4. Tabela: funcionarios 
CREATE TABLE funcionarios (
    id_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    cod_usuario INT UNIQUE NOT NULL,
    especialidade VARCHAR(150) DEFAULT NULL, 
    salario DECIMAL(10, 2) DEFAULT NULL,
    -- CASCADE no usúario do funcionário (se o usuário desse funcionário for deletado, o funcionário também é)
    FOREIGN KEY (cod_usuario) REFERENCES usuarios(id_usuario) ON DELETE CASCADE
);

-- 5. Tabela: disponibilidade 
CREATE TABLE disponibilidade (
    id_disponibilidade INT AUTO_INCREMENT PRIMARY KEY,
    cod_funcionario INT NOT NULL,
    nome_grade VARCHAR(100) NOT NULL DEFAULT 'Horário Padrão',
    is_ativa TINYINT(1) NOT NULL DEFAULT 0,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (cod_funcionario) REFERENCES funcionarios(id_funcionario) ON DELETE CASCADE
);

-- 5.5 Tabela: disponibilidade_dias
CREATE TABLE disponibilidade_dias (
    id_dia INT AUTO_INCREMENT PRIMARY KEY,
    cod_disponibilidade INT NOT NULL,
    dia_semana ENUM('Dom','Seg','Ter','Qua','Qui','Sex','Sab') NOT NULL,
    hora_inicio_trabalho TIME NOT NULL,
    hora_fim_trabalho TIME NOT NULL,
    intervalo_inicio TIME DEFAULT NULL, 
    intervalo_fim TIME DEFAULT NULL,
    status ENUM('disponivel', 'indisponivel') NOT NULL DEFAULT 'disponivel',
    -- CASCADE na disponibilidade (se a disponibilidade for excluida o disponibilidade_dias também vai ser)
    FOREIGN KEY (cod_disponibilidade) REFERENCES disponibilidade(id_disponibilidade) ON DELETE CASCADE
);

-- 6. Tabela Associativa: funcionario_servicos 
CREATE TABLE funcionario_servicos (
    id_sv_funcionario INT AUTO_INCREMENT PRIMARY KEY,
    cod_funcionario INT NOT NULL,
    cod_servico INT NOT NULL,
    status ENUM('ativo', 'inativo') NOT NULL DEFAULT 'ativo',
    -- RESTRICT: Impede apagar o funcionário ou serviço se eles já estiverem vinculados
    FOREIGN KEY (cod_funcionario) REFERENCES funcionarios(id_funcionario) ON DELETE RESTRICT,
    FOREIGN KEY (cod_servico) REFERENCES servicos(id_servico) ON DELETE RESTRICT
);

-- 7. Tabela: agendamentos 
CREATE TABLE agendamentos (
    id_agendamento INT AUTO_INCREMENT PRIMARY KEY,
    cod_cliente INT NOT NULL,
    cod_funcionario_criador INT DEFAULT NULL,
    data_agendamento DATE NOT NULL,
    data_criacao TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    status ENUM('pendente', 'concluido', 'cancelado', 'marcado') NOT NULL DEFAULT 'pendente',
    -- RESTRICT: O sistema não vai deixar excluir um cliente que já tenha agendamentos no histórico
    FOREIGN KEY (cod_cliente) REFERENCES clientes(id_cliente) ON DELETE RESTRICT
);

-- 8. Tabela: itens_agendamento 
CREATE TABLE itens_agendamento (
    id_item INT AUTO_INCREMENT PRIMARY KEY,
    cod_agendamento INT NOT NULL,
    cod_sv_func INT NOT NULL,
    hora_inicio TIME NOT NULL,
    hora_fim TIME NOT NULL,
    preco_cobrado DECIMAL(10, 2) NOT NULL,
    duracao_registrada INT NOT NULL,
    -- CASCADE no agendamento (se a reserva inteira for cancelada/excluída, os itens vão junto)
    FOREIGN KEY (cod_agendamento) REFERENCES agendamentos(id_agendamento) ON DELETE CASCADE,
    -- RESTRICT: Ninguém pode apagar o serviço prestado se ele já foi marcado.
    FOREIGN KEY (cod_sv_func) REFERENCES funcionario_servicos(id_sv_funcionario) ON DELETE RESTRICT
);