DROP DATABASE IF EXISTS lavagem_veiculos;
CREATE DATABASE lavagem_veiculos CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE lavagem_veiculos;

    CREATE TABLE usuarios (
        id INT PRIMARY KEY AUTO_INCREMENT,
        nome VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        senha VARCHAR(255) NOT NULL,
        tipo ENUM('admin', 'funcionario') DEFAULT 'funcionario',
        ativo TINYINT(1) DEFAULT 1,
        data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_email (email),
        INDEX idx_tipo (tipo)
    ) ENGINE=InnoDB;

CREATE TABLE clientes (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    endereco VARCHAR(255) NOT NULL,
    cidade VARCHAR(100) NOT NULL,
    estado VARCHAR(2) NOT NULL,
    cep VARCHAR(10) NOT NULL,
    data_cadastro TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_email (email),
    INDEX idx_nome (nome),
    INDEX idx_telefone (telefone)
) ENGINE=InnoDB;

CREATE TABLE categorias_servicos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_ativo (ativo)
) ENGINE=InnoDB;

CREATE TABLE servicos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    categoria_id INT NOT NULL,
    nome VARCHAR(100) NOT NULL,
    descricao TEXT,
    preco_moto DECIMAL(10,2) NOT NULL,
    preco_carro DECIMAL(10,2) NOT NULL,
    preco_camioneta DECIMAL(10,2) NOT NULL,
    duracao_minutos INT NOT NULL DEFAULT 60,
    estoque_disponivel INT DEFAULT 999,
    ativo TINYINT(1) DEFAULT 1,
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (categoria_id) REFERENCES categorias_servicos(id),
    INDEX idx_categoria (categoria_id),
    INDEX idx_ativo (ativo),
    INDEX idx_estoque (estoque_disponivel)
) ENGINE=InnoDB;

CREATE TABLE agendamentos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    cliente_id INT NOT NULL,
    data_agendamento DATE NOT NULL,
    hora_agendamento TIME NOT NULL,
    tipo_veiculo ENUM('moto', 'carro', 'camioneta') NOT NULL,
    placa_veiculo VARCHAR(20),
    modelo_veiculo VARCHAR(100),
    observacoes TEXT,
    valor_total DECIMAL(10,2) NOT NULL,
    status ENUM('pendente', 'confirmado', 'em_andamento', 'concluido', 'cancelado') DEFAULT 'pendente',
    data_criacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    data_atualizacao TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (cliente_id) REFERENCES clientes(id),
    INDEX idx_cliente (cliente_id),
    INDEX idx_data (data_agendamento),
    INDEX idx_status (status),
    INDEX idx_data_hora (data_agendamento, hora_agendamento)
) ENGINE=InnoDB;

CREATE TABLE agendamento_itens (
    id INT PRIMARY KEY AUTO_INCREMENT,
    agendamento_id INT NOT NULL,
    servico_id INT NOT NULL,
    quantidade INT DEFAULT 1,
    valor_unitario DECIMAL(10,2) NOT NULL,
    valor_total DECIMAL(10,2) NOT NULL,
    FOREIGN KEY (agendamento_id) REFERENCES agendamentos(id) ON DELETE CASCADE,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    INDEX idx_agendamento (agendamento_id),
    INDEX idx_servico (servico_id)
) ENGINE=InnoDB;

CREATE TABLE auditoria_precos (
    id INT PRIMARY KEY AUTO_INCREMENT,
    servico_id INT NOT NULL,
    tipo_veiculo ENUM('moto', 'carro', 'camioneta') NOT NULL,
    preco_anterior DECIMAL(10,2) NOT NULL,
    preco_novo DECIMAL(10,2) NOT NULL,
    usuario VARCHAR(100),
    data_alteracao TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (servico_id) REFERENCES servicos(id),
    INDEX idx_servico (servico_id),
    INDEX idx_data (data_alteracao)
) ENGINE=InnoDB;

DELIMITER $$

CREATE TRIGGER trg_auditoria_preco_moto
AFTER UPDATE ON servicos
FOR EACH ROW
BEGIN
    IF OLD.preco_moto != NEW.preco_moto THEN
        INSERT INTO auditoria_precos (servico_id, tipo_veiculo, preco_anterior, preco_novo, usuario)
        VALUES (NEW.id, 'moto', OLD.preco_moto, NEW.preco_moto, USER());
    END IF;
END$$

CREATE TRIGGER trg_auditoria_preco_carro
AFTER UPDATE ON servicos
FOR EACH ROW
BEGIN
    IF OLD.preco_carro != NEW.preco_carro THEN
        INSERT INTO auditoria_precos (servico_id, tipo_veiculo, preco_anterior, preco_novo, usuario)
        VALUES (NEW.id, 'carro', OLD.preco_carro, NEW.preco_carro, USER());
    END IF;
END$$

CREATE TRIGGER trg_auditoria_preco_camioneta
AFTER UPDATE ON servicos
FOR EACH ROW
BEGIN
    IF OLD.preco_camioneta != NEW.preco_camioneta THEN
        INSERT INTO auditoria_precos (servico_id, tipo_veiculo, preco_anterior, preco_novo, usuario)
        VALUES (NEW.id, 'camioneta', OLD.preco_camioneta, NEW.preco_camioneta, USER());
    END IF;
END$$

DELIMITER ;

DELIMITER $$

CREATE PROCEDURE sp_inserir_servicos_massivo(
    IN p_categoria_id INT,
    IN p_quantidade INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_nome VARCHAR(100);
    
    WHILE i <= p_quantidade DO
        SET v_nome = CONCAT('Serviço ', i, ' - Categoria ', p_categoria_id);
        
        INSERT INTO servicos (
            categoria_id, 
            nome, 
            descricao, 
            preco_moto, 
            preco_carro, 
            preco_camioneta,
            duracao_minutos,
            estoque_disponivel
        )
        VALUES (
            p_categoria_id,
            v_nome,
            CONCAT('Descrição do ', v_nome),
            ROUND(20 + (RAND() * 30), 2),
            ROUND(30 + (RAND() * 50), 2),
            ROUND(50 + (RAND() * 70), 2),
            30 + (FLOOR(RAND() * 4) * 15),
            100 + FLOOR(RAND() * 900)
        );
        
        SET i = i + 1;
    END WHILE;
    
    SELECT CONCAT(p_quantidade, ' serviços inseridos com sucesso!') AS resultado;
END$$

DELIMITER $$

CREATE PROCEDURE sp_inserir_clientes_massivo(
    IN p_quantidade INT
)
BEGIN
    DECLARE i INT DEFAULT 1;
    DECLARE v_nome VARCHAR(100);
    DECLARE v_email VARCHAR(100);
    
    WHILE i <= p_quantidade DO
        SET v_nome = CONCAT('Cliente Teste ', i);
        SET v_email = CONCAT('cliente', i, '@teste.com');
        
        INSERT INTO clientes (
            nome, 
            email, 
            senha, 
            telefone, 
            endereco,
            cidade,
            estado,
            cep
        )
        VALUES (
            v_nome,
            v_email,
            MD5('senha123'),
            CONCAT('(11) 9', LPAD(FLOOR(RAND() * 100000000), 8, '0')),
            CONCAT('Rua Teste, ', FLOOR(RAND() * 1000)),
            'São Paulo',
            'SP',
            CONCAT(LPAD(FLOOR(RAND() * 100000), 5, '0'), '-', LPAD(FLOOR(RAND() * 1000), 3, '0'))
        );
        
        SET i = i + 1;
    END WHILE;
    
    SELECT CONCAT(p_quantidade, ' clientes inseridos com sucesso!') AS resultado;
END$$

DELIMITER ;

DELIMITER $$

CREATE FUNCTION fn_verificar_disponibilidade(
    p_servico_id INT,
    p_quantidade INT
) RETURNS VARCHAR(50)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_estoque INT;
    
    SELECT estoque_disponivel INTO v_estoque
    FROM servicos
    WHERE id = p_servico_id AND ativo = 1;
    
    IF v_estoque IS NULL THEN
        RETURN 'SERVICO_INVALIDO';
    ELSEIF v_estoque >= p_quantidade THEN
        RETURN 'DISPONIVEL';
    ELSE
        RETURN 'INDISPONIVEL';
    END IF;
END$$

CREATE FUNCTION fn_calcular_valor_servico(
    p_servico_id INT,
    p_tipo_veiculo VARCHAR(20),
    p_quantidade INT
) RETURNS DECIMAL(10,2)
DETERMINISTIC
READS SQL DATA
BEGIN
    DECLARE v_preco DECIMAL(10,2);
    
    IF p_tipo_veiculo = 'moto' THEN
        SELECT preco_moto INTO v_preco FROM servicos WHERE id = p_servico_id;
    ELSEIF p_tipo_veiculo = 'carro' THEN
        SELECT preco_carro INTO v_preco FROM servicos WHERE id = p_servico_id;
    ELSEIF p_tipo_veiculo = 'camioneta' THEN
        SELECT preco_camioneta INTO v_preco FROM servicos WHERE id = p_servico_id;
    ELSE
        SET v_preco = 0;
    END IF;
    
    RETURN v_preco * p_quantidade;
END$$

DELIMITER ;

INSERT INTO usuarios (nome, email, senha, tipo) VALUES 
('Administrador', 'admin@lavagem.com', MD5('admin123'), 'admin'),
('Gerente', 'gerente@lavagem.com', MD5('gerente123'), 'funcionario');

INSERT INTO categorias_servicos (nome, descricao) VALUES
('Lavagem Básica', 'Serviços de lavagem simples e rápida'),
('Lavagem Completa', 'Lavagem completa com detalhamento'),
('Polimento', 'Serviços de polimento e cristalização'),
('Higienização', 'Limpeza interna profunda'),
('Proteção', 'Aplicação de cera e proteção de pintura');

INSERT INTO servicos (categoria_id, nome, descricao, preco_moto, preco_carro, preco_camioneta, duracao_minutos, estoque_disponivel) VALUES
(1, 'Lavagem Simples', 'Lavagem externa do veículo', 25.00, 40.00, 60.00, 30, 999),
(1, 'Lavagem com Cera', 'Lavagem externa + aplicação de cera', 40.00, 60.00, 90.00, 45, 999),
(2, 'Lavagem Completa', 'Lavagem externa + interna completa', 50.00, 80.00, 120.00, 60, 999),
(2, 'Lavagem Premium', 'Lavagem completa + aspiração + limpeza de estofados', 70.00, 110.00, 160.00, 90, 999),
(3, 'Polimento Simples', 'Polimento básico da pintura', 80.00, 150.00, 220.00, 120, 999),
(3, 'Polimento Completo', 'Polimento profissional + cristalização', 150.00, 280.00, 400.00, 180, 999),
(4, 'Higienização Básica', 'Limpeza interna com produtos especializados', 60.00, 90.00, 130.00, 60, 999),
(4, 'Higienização Completa', 'Higienização profunda + ozônio', 100.00, 150.00, 220.00, 90, 999),
(5, 'Aplicação de Cera', 'Cera protetora de pintura', 50.00, 80.00, 110.00, 45, 999),
(5, 'Vitrificação', 'Proteção vitrificada de longa duração', 200.00, 350.00, 500.00, 240, 999);

INSERT INTO clientes (nome, email, senha, telefone, endereco, cidade, estado, cep) VALUES
('João Silva', 'joao@email.com', MD5('123456'), '(11) 98765-4321', 'Rua das Flores, 123', 'São Paulo', 'SP', '01234-567'),
('Maria Santos', 'maria@email.com', MD5('123456'), '(11) 97654-3210', 'Av. Paulista, 1000', 'São Paulo', 'SP', '01310-100'),
('Pedro Oliveira', 'pedro@email.com', MD5('123456'), '(11) 96543-2109', 'Rua Augusta, 500', 'São Paulo', 'SP', '01305-000');

INSERT INTO agendamentos (cliente_id, data_agendamento, hora_agendamento, tipo_veiculo, placa_veiculo, modelo_veiculo, valor_total, status) VALUES
(1, '2025-10-27', '09:00:00', 'carro', 'ABC-1234', 'Honda Civic', 80.00, 'confirmado'),
(2, '2025-10-27', '10:00:00', 'moto', 'XYZ-5678', 'Honda CB 500', 50.00, 'pendente'),
(3, '2025-10-28', '14:00:00', 'camioneta', 'DEF-9012', 'Toyota Hilux', 120.00, 'confirmado'),
(1, '2025-10-29', '11:00:00', 'carro', 'ABC-1234', 'Honda Civic', 110.00, 'pendente');

INSERT INTO agendamento_itens (agendamento_id, servico_id, quantidade, valor_unitario, valor_total) VALUES
(1, 3, 1, 80.00, 80.00),
(2, 3, 1, 50.00, 50.00),
(3, 3, 1, 120.00, 120.00),
(4, 4, 1, 110.00, 110.00);

    CREATE TABLE logs_alteracoes (
        id INT AUTO_INCREMENT PRIMARY KEY,
        data_hora DATETIME DEFAULT CURRENT_TIMESTAMP,
        usuario_id INT, 
        ip_usuario VARCHAR(45),
        acao VARCHAR(50) NOT NULL, 
        tabela_afetada VARCHAR(100) NOT NULL, 
        registro_id INT, 
        dados_antigos JSON, 
        dados_novos JSON,
        FOREIGN KEY (usuario_id) 
            REFERENCES usuarios(id) 
            ON DELETE SET NULL
    ) ENGINE=InnoDB;
