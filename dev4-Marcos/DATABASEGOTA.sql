CREATE DATABASE GotaSangue;

USE GotaSangue;

CREATE TABLE doador (
id INT AUTO_INCREMENT PRIMARY KEY,
nome VARCHAR(100) NOT NULL,
idade INT NOT NULL,
cpf VARCHAR(11) UNIQUE NOT NULL,
email VARCHAR(110) UNIQUE,
telefone INT NOT NULL
);

CREATE TABLE Estoque (
tipo_sangue VARCHAR(3)
);

INSERT INTO doador (nome, idade, cpf, email)
VALUES ('Marcos', 19, '1234567891', 'marcos@email.com'),
	   ('Diego', 29, '42016204202', 'mako@email.com'),
       ('Diniz', 18, '1234567890', 'caua@email.com'),
       ('Matheus', 19 , '7776622290', 'matheus@emaik.com'),
       ('Wesley', 19, '99988833XX', 'wesley@email.com');

SELECT * FROM doador;

DROP TABLE doador;