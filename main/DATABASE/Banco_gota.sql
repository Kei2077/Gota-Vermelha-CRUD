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
('B+',3,'2025-12-15'),('O-',4,'2025-12-15'),
('AB+',3,'2025-12-15'),('O+',4,'2025-12-15');

ALTER TABLE bolsas 
ADD COLUMN id INT AUTO_INCREMENT PRIMARY KEY FIRST;
ALTER TABLE bolsas 
ADD INDEX idx_tipo_validade (tipo_sangue, data_validade);

select * from bolsas;
delete from bolsas;
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

INSERT INTO usuarios (nome, cpf, data_nascimento, email, senha, tipo_sanguineo, fator_rh, telefone, endereco) VALUES
	('Marcos','39732171813', '2004-07-12',
    'marcosteste2@gotavermelha.com',  '$2y$10$mtEVi24RK4MCqBe5Oe1tee6wNm8De7RWWIX3FH01yGCsItV0RbuKu',
    'O', 'POSITIVO',
    '6996967696', 'Rua tal, 900, bairro tal, cidade tal, estado tal') 
;

select * from usuarios;
DELETE FROM usuarios WHERE nome = 'João Chatão';

CREATE TABLE IF NOT EXISTS doacoes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    data_doacao DATE NOT NULL,
    local_doacao VARCHAR(255) NOT NULL,
    quantidade_ml INT DEFAULT 450,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data_doacao)
);


INSERT INTO doacoes (usuario_id, data_doacao, local_doacao, quantidade_ml) VALUES
(1, '2025-10-15', 'Hemocentro de Mogi das Cruzes', 450),
(1, '2025-08-20', 'Hemocentro de Mogi das Cruzes', 900),
(1, '2025-06-10', 'Hemocentro de Mogi das Cruzes', 90000);

select * from doacoes;
delete from doacoes;


CREATE TABLE IF NOT EXISTS agendamentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    telefone VARCHAR(11) NOT NULL,
    cpf VARCHAR(11) NOT NULL,
    unidade VARCHAR(250) NOT NULL,
    data DATE NOT NULL,
    hora VARCHAR(5) NOT NULL,
    status ENUM('PENDENTE', 'CONFIRMADO', 'CANCELADO', 'REALIZADO') DEFAULT 'PENDENTE',
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id) ON DELETE CASCADE,
    INDEX idx_usuario (usuario_id),
    INDEX idx_data (data)
);
DELETE from agendamentos;
select * from agendamentos;

CREATE TABLE IF NOT EXISTS  usuariosADM (
	id INT PRIMARY KEY auto_increment,
    nome VARCHAR(255) NOT NULL,
    email VARCHAR(255) NOT NULL,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_email (email)
);

INSERT INTO usuariosADM (nome, email, senha) VALUES 
('Admin Gota Vermelha', 'admin@gotavermelha.com', '$2y$10$6pgVuTIEM866XCBT/U.DGuzEBhUxyqzC2XLz2ZZrp49eyZ/yrRrbq');

/*Senha: Senha@teste1*/
INSERT INTO usuariosADM (nome, email, senha) VALUES
('Marcos', 'marcosadmin@gotavermelha.com', '$2y$10$mtEVi24RK4MCqBe5Oe1tee6wNm8De7RWWIX3FH01yGCsItV0RbuKu');

Select * from usuariosADM;

DELETE FROM usuariosADM WHERE nome = 'Marcos';

/*Para realização de testes ao vivo*/
SET SQL_SAFE_UPDATES = 0;
