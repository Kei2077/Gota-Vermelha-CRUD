create database gotavermelha;
use gotavermelha;

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

SELECT * FROM usuarios