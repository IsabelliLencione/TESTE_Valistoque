-- =========================================================
-- BANCO DE DADOS VALISTOQUE
-- =========================================================

CREATE DATABASE IF NOT EXISTS `valistoque_testes`
DEFAULT CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;

USE `valistoque_testes`;

-- =========================================================
-- DESATIVAR CHAVES ESTRANGEIRAS
-- =========================================================

SET FOREIGN_KEY_CHECKS = 0;

-- =========================================================
-- APAGAR TABELAS EXISTENTES
-- =========================================================

DROP TABLE IF EXISTS `alertas`;
DROP TABLE IF EXISTS `prateleiras`;
DROP TABLE IF EXISTS `estoque`;
DROP TABLE IF EXISTS `produto`;
DROP TABLE IF EXISTS `usuarios`;

-- =========================================================
-- ATIVAR CHAVES ESTRANGEIRAS
-- =========================================================

SET FOREIGN_KEY_CHECKS = 1;

-- =========================================================
-- TABELA: ESTOQUE
-- =========================================================

CREATE TABLE `estoque` (
    `id_estoque` INT NOT NULL AUTO_INCREMENT,
    `nome_produto` VARCHAR(150) NOT NULL,
    `lote` INT NOT NULL,
    `data_validade` DATE NOT NULL,
    `total_itens` INT NOT NULL DEFAULT 0,
    `peso_un` DECIMAL(10,2) NOT NULL DEFAULT 0.00,

    PRIMARY KEY (`id_estoque`)

) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABELA: ALERTAS
-- =========================================================

CREATE TABLE `alertas` (
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
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABELA: PRATELEIRAS
-- =========================================================

CREATE TABLE `prateleiras` (
    `id_prat` INT NOT NULL AUTO_INCREMENT,
    `id_estoque` INT NOT NULL,
    `peso_prat` INT NOT NULL DEFAULT 0,
    `quantidade_atual` INT NOT NULL DEFAULT 0,
    `numero_prat` INT NOT NULL,

    PRIMARY KEY (`id_prat`),

    KEY `id_estoque` (`id_estoque`),

    CONSTRAINT `prateleiras_ibfk_1`
        FOREIGN KEY (`id_estoque`)
        REFERENCES `estoque` (`id_estoque`)
        ON DELETE CASCADE
        ON UPDATE CASCADE

) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABELA: PRODUTO
-- =========================================================

CREATE TABLE `produto` (
    `id_produto` INT NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(150) NOT NULL,
    `descricao` TEXT,
    `categoria` VARCHAR(100) DEFAULT NULL,
    `quantidade` INT DEFAULT 0,
    `preco` DECIMAL(10,2) DEFAULT 0.00,
    `validade` DATE DEFAULT NULL,

    PRIMARY KEY (`id_produto`)

) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- TABELA: USUARIOS
-- =========================================================

CREATE TABLE `usuarios` (
    `id` INT NOT NULL AUTO_INCREMENT,
    `nome` VARCHAR(100) NOT NULL,
    `email` VARCHAR(150) NOT NULL,
    `cpf` VARCHAR(14) NOT NULL,
    `senha` VARCHAR(255) NOT NULL,
    `tipo` VARCHAR(15) NOT NULL,
    `criado_em` TIMESTAMP NULL DEFAULT CURRENT_TIMESTAMP,

    PRIMARY KEY (`id`),

    UNIQUE KEY `email` (`email`),

    UNIQUE KEY `cpf` (`cpf`)

) ENGINE=InnoDB
DEFAULT CHARACTER SET=utf8mb4
COLLATE=utf8mb4_unicode_ci;

-- =========================================================
-- INSERIR USUÁRIOS
-- =========================================================

INSERT INTO `usuarios`
(
    `id`,
    `nome`,
    `email`,
    `cpf`,
    `senha`,
    `tipo`,
    `criado_em`
)
VALUES
(
    14,
    'ana',
    'ana@email.com',
    '12345647',
    '1234',
    'funcionario',
    '2026-09-09 17:14:05'
),
(
    15,
    'Administrador',
    'admin@valistoque.com',
    '111.111.111-11',
    'admin123',
    'administrador',
    CURRENT_TIMESTAMP
),
(
    16,
    'Funcionario',
    'funcionario@valistoque.com',
    '222.222.222-22',
    'func123',
    'funcionario',
    CURRENT_TIMESTAMP
);

-- =========================================================
-- DADOS DE TESTE - PRODUTOS
-- =========================================================

INSERT INTO `produto`
(
    `nome`,
    `descricao`,
    `categoria`,
    `quantidade`,
    `preco`,
    `validade`
)
VALUES
(
    'Arroz',
    'Arroz branco tipo 1',
    'Alimentos',
    100,
    25.90,
    '2027-01-15'
),
(
    'Feijão',
    'Feijão carioca',
    'Alimentos',
    80,
    8.50,
    '2027-02-20'
),
(
    'Macarrão',
    'Macarrão espaguete',
    'Alimentos',
    60,
    5.99,
    '2027-03-10'
);

-- =========================================================
-- DADOS DE TESTE - ESTOQUE
-- =========================================================

INSERT INTO `estoque`
(
    `nome_produto`,
    `lote`,
    `data_validade`,
    `total_itens`,
    `peso_un`
)
VALUES
(
    'Arroz',
    1001,
    '2027-01-15',
    100,
    5.00
),
(
    'Feijão',
    1002,
    '2027-02-20',
    80,
    1.00
),
(
    'Macarrão',
    1003,
    '2027-03-10',
    60,
    0.50
);

-- =========================================================
-- DADOS DE TESTE - PRATELEIRAS
-- =========================================================

INSERT INTO `prateleiras`
(
    `id_estoque`,
    `peso_prat`,
    `quantidade_atual`,
    `numero_prat`
)
VALUES
(
    1,
    500,
    50,
    1
),
(
    2,
    300,
    40,
    2
),
(
    3,
    200,
    30,
    3
);

-- =========================================================
-- DADOS DE TESTE - ALERTAS
-- =========================================================

INSERT INTO `alertas`
(
    `id_estoque`,
    `tipo_alerta`,
    `mensagem`
)
VALUES
(
    1,
    'ESTOQUE',
    'Produto com estoque disponível.'
),
(
    2,
    'ESTOQUE',
    'Verificar quantidade disponível.'
),
(
    3,
    'VALIDADE',
    'Verificar data de validade do produto.'
);

-- =========================================================
-- CONSULTAS PARA TESTAR
-- =========================================================

SELECT * FROM `usuarios`;

SELECT * FROM `produto`;

SELECT * FROM `estoque`;

SELECT * FROM `prateleiras`;

SELECT * FROM `alertas`;

-- =========================================================
-- VERIFICAR RELACIONAMENTO ENTRE ESTOQUE E PRATELEIRAS
-- =========================================================

SELECT
    e.id_estoque,
    e.nome_produto,
    e.lote,
    e.data_validade,
    e.total_itens,
    e.peso_un,
    p.id_prat,
    p.numero_prat,
    p.peso_prat,
    p.quantidade_atual
FROM `estoque` e
LEFT JOIN `prateleiras` p
    ON e.id_estoque = p.id_estoque;

-- =========================================================
-- VERIFICAR ALERTAS DO ESTOQUE
-- =========================================================

SELECT
    a.id_alerta,
    e.nome_produto,
    a.tipo_alerta,
    a.mensagem,
    a.data_alerta
FROM `alertas` a
INNER JOIN `estoque` e
    ON a.id_estoque = e.id_estoque;

-- =========================================================
-- FIM DO BANCO VALISTOQUE
-- =========================================================