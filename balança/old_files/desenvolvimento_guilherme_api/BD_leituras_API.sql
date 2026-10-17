
CREATE DATABASE IF NOT EXISTS projeto_valistoque;
USE projeto_valistoque;

CREATE TABLE IF NOT EXISTS leituras (
    id INT AUTO_INCREMENT PRIMARY KEY,
    prateleira INT NOT NULL,
    peso DECIMAL(10, 2) NOT NULL, -- Suporta valores exatos com decimais em gramas (ex: 86.48)
    data_registro TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);