drop database gotavermelha;
create database gotavermelha;
use gotavermelha;

CREATE TABLE IF NOT EXISTS bolsas (
    tipo_sangue VARCHAR(5),
    quantidade INT DEFAULT 1,
    data_validade DATE
);

INSERT INTO bolsas VALUES 
('A+',5,'2025-12-31'),('A-',2,'2025-12-31'),
('B+',3,'2025-12-15'),('O-',4,'2025-12-15');

CREATE TABLE IF NOT EXISTS usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    cpf VARCHAR(11) UNIQUE NOT NULL,
    data_nascimento DATE NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    senha VARCHAR(255) NOT NULL,
    tipo_sanguineo ENUM('A', 'B', 'AB', 'O') NOT NULL,
    fator_rh ENUM('POSITIVO', 'NEGATIVO') NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    endereco TEXT NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_cpf (cpf),
    INDEX idx_email (email)
);

select * from usuarios;

CREATE TABLE IF NOT EXISTS doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_doacao DATE NOT NULL,
    local_doacao VARCHAR(255) NOT NULL,
    quantidade_ml INT DEFAULT 450,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data_doacao)
);

-- Dados de exemplo (substitua '1' pelo ID de um usuário real)
INSERT INTO doacoes (usuario_id, data_doacao, local_doacao, quantidade_ml) VALUES
(1, '2024-10-15', 'Hemocentro São Paulo', 450),
(1, '2024-08-20', 'Hospital das Clínicas', 450),
(1, '2024-06-10', 'Hemocentro São Paulo', 450);