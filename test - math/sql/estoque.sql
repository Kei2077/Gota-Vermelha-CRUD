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