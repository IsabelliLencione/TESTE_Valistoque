CREATE DATABASE IF NOT EXISTS valistoque_testes CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE valistoque_testes;

-- CREATE TABLE IF NOT EXISTS produtos (
  --  id_produto INT AUTO_INCREMENT PRIMARY KEY,
 --   nome VARCHAR(150) NOT NULL,
 --   peso_un DECIMAL(10,2) NOT NULL
-- ) ENGINE=InnoDB;

CREATE TABLE IF NOT EXISTS estoque ( 
    id_estoque INT AUTO_INCREMENT PRIMARY KEY, 
	nome_produto VARCHAR(150) NOT NULL,
     lote INT NOT NULL, 
    data_validade DATE NOT NULL, 
    total_itens INT NOT NULL,
    peso_un DECIMAL(10,2) NOT NULL
) ENGINE=InnoDB; 

CREATE TABLE IF NOT EXISTS prateleiras ( 
    id_prat INT AUTO_INCREMENT PRIMARY KEY, 
    id_estoque INT NOT NULL, 
    peso_prat INT NOT NULL, 
    quantidade_atual INT NOT NULL, 
    numero_prat INT NOT NULL, 
    FOREIGN KEY (id_estoque) REFERENCES estoque(id_estoque) 
) ENGINE=InnoDB; 

CREATE TABLE IF NOT EXISTS usuarios ( 
    id INT AUTO_INCREMENT PRIMARY KEY, 
    nome VARCHAR(100) NOT NULL, 
    email VARCHAR(150) NOT NULL UNIQUE, 
    cpf VARCHAR(14) NOT NULL UNIQUE, 
    senha VARCHAR(255) NOT NULL, 
    tipo VARCHAR(15) NOT NULL, 
    criado_em TIMESTAMP DEFAULT CURRENT_TIMESTAMP 
);