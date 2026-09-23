-- MySQL dump
-- Database: valistoque_testes

CREATE DATABASE IF NOT EXISTS `valistoque_testes`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `valistoque_testes`;

-- --------------------------------------------------------
-- Tabela: alertas
-- --------------------------------------------------------
CREATE TABLE IF NOT EXISTS `alertas` (
    `id_alerta` INT NOT NULL AUTO_INCREMENT,
    `id_estoque` INT NOT NULL,
    `tipo_alerta` VARCHAR(50) NOT NULL,
    `mensagem` VARCHAR(255) NOT NULL,
    `data_alerta` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,
    PRIMARY KEY (`id_alerta`),
    KEY `id_estoque` (`id_estoque`),
    CONSTRAINT `alertas_ibfk_1`
        FOREIGN KEY (`id_estoque`)
        REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_0900_ai_ci;

-- --------------------------------------------------------
-- Tabela: estoque
-- --------------------------------------------------------

DROP TABLE IF EXISTS `estoque`;

CREATE TABLE `estoque` (
  `id_estoque` int NOT NULL AUTO_INCREMENT,
  `nome_produto` varchar(150) NOT NULL,
  `lote` int NOT NULL,
  `data_validade` date NOT NULL,
  `total_itens` int NOT NULL,
  `peso_un` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_estoque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: prateleiras
-- --------------------------------------------------------

DROP TABLE IF EXISTS `prateleiras`;

CREATE TABLE `prateleiras` (
  `id_prat` int NOT NULL AUTO_INCREMENT,
  `id_estoque` int NOT NULL,
  `peso_prat` int NOT NULL,
  `quantidade_atual` int NOT NULL,
  `numero_prat` int NOT NULL,
  PRIMARY KEY (`id_prat`),
  KEY `id_estoque` (`id_estoque`),
  CONSTRAINT `prateleiras_ibfk_1`
    FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: produto
-- --------------------------------------------------------

DROP TABLE IF EXISTS `produto`;

CREATE TABLE `produto` (
  `id_produto` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(150) NOT NULL,
  `descricao` text,
  `categoria` varchar(100) DEFAULT NULL,
  `quantidade` int DEFAULT 0,
  `preco` decimal(10,2) DEFAULT 0.00,
  `validade` date DEFAULT NULL,
  PRIMARY KEY (`id_produto`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------
-- Tabela: usuarios
-- --------------------------------------------------------

DROP TABLE IF EXISTS `usuarios`;

CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(15) COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------
-- Usuários
-- --------------------------------------------------------

INSERT INTO `usuarios`
(`id`, `nome`, `email`, `cpf`, `senha`, `tipo`, `criado_em`)
VALUES
(14, 'ana', 'ana@email.com', '12345647', '1234', 'funcionario', '2026-09-09 17:14:05'),
(15, 'Administrador', 'admin@valistoque.com', '111.111.111-11', 'admin123', 'administrador', CURRENT_TIMESTAMP),
(16, 'Funcionario', 'funcionario@valistoque.com', '222.222.222-22', 'func123', 'funcionario', CURRENT_TIMESTAMP);




--------------------------------------------------------------
--tabelas Configurar Alertas
CREATE TABLE IF NOT EXISTS `config_alertas` (
    `id` INT NOT NULL DEFAULT 1,
    `dias_antes_validade` INT NOT NULL DEFAULT 30,
    `unidades_minimas_central` INT NOT NULL DEFAULT 10,
    `unidades_minimas_prateleira` INT NOT NULL DEFAULT 5,
    `intervalo_minutos` INT NOT NULL DEFAULT 15,
    `exibir_popups` TINYINT(1) NOT NULL DEFAULT 1,
    PRIMARY KEY (`id`)
) ENGINE=InnoDB
DEFAULT CHARSET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

INSERT INTO `config_alertas`
(
    `id`,
    `dias_antes_validade`,
    `unidades_minimas_central`,
    `unidades_minimas_prateleira`,
    `intervalo_minutos`,
    `exibir_popups`
)
VALUES
(
    1,
    30,
    10,
    5,
    5,
    1
)
ON DUPLICATE KEY UPDATE
    `id` = `id`;