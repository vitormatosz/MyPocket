CREATE DATABASE mypocket;

USE mypocket;

CREATE TABLE usuarios(
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    nome VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    senha VARCHAR(255) NOT NULL,
    criado_em TIMESTAMP  DEFAULT CURRENT_TIMESTAMP;
);

SELECT * FROM usuarios;

CREATE TABLE recorrencias (
    id INT AUTO_INCREMENT PRIMARY KEY,
    descricao VARCHAR(255) NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('Entrada','Saida','Diario') NOT NULL,
    frequencia ENUM('fixa','parc') NOT NULL,
    data_inicio DATE NOT NULL,
    data_fim DATE NULL,
    dia_vencimento INT NULL,
    ativa TINYINT(1) NOT NULL DEFAULT 1,
    id_usuario INT NOT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id)
);

SELECT * FROM recorrencias;

CREATE TABLE transacoes(
    id INT AUTO_INCREMENT PRIMARY KEY NOT NULL,
    valor DECIMAL(10,2) NOT NULL,
    tipo ENUM('Entrada', 'Saida', 'Diario') NOT NULL,
    descricao VARCHAR(255) NOT NULL,
    data DATE NOT NULL,
    id_usuario INT NOT NULL,
    id_recorrencia INT NULL,
    FOREIGN KEY (id_usuario) REFERENCES usuarios(id),
    FOREIGN KEY (id_recorrencia) REFERENCES recorrencias(id)
);

SELECT * FROM transacoes;