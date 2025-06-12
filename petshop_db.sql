-- Cria o banco de dados se ele não existir
CREATE DATABASE IF NOT EXISTS petshop_db;

-- Seleciona o banco de dados
USE petshop_db;

-- Tabela para Clientes
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(255),
    nomePet VARCHAR(255) NOT NULL,
    tipoPet ENUM('cachorro', 'gato', 'outros') NOT NULL
);

-- Tabela para Atendentes
CREATE TABLE IF NOT EXISTS atendentes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nome VARCHAR(255) NOT NULL,
    telefone VARCHAR(20) NOT NULL,
    email VARCHAR(255)
);

-- Tabela para Atendimentos
CREATE TABLE IF NOT EXISTS atendimentos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    cliente_id INT NOT NULL,
    atendente_id INT NOT NULL,
    tipoServico ENUM('banho-e-tosa', 'banho', 'tosa') NOT NULL,
    valor DECIMAL(10, 2) NOT NULL,
    dataAtendimento DATE NOT NULL,
    horaAtendimento TIME NOT NULL,
    timestamp_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP, -- Para ordenação e auditoria
    FOREIGN KEY (cliente_id) REFERENCES clientes(id) ON DELETE CASCADE,
    FOREIGN KEY (atendente_id) REFERENCES atendentes(id) ON DELETE CASCADE
);
