-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: localhost    Database: valistoque_testes
-- ------------------------------------------------------
-- Server version	8.0.45

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `alertas`
--

DROP TABLE IF EXISTS `alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `alertas` (
  `id_alerta` int NOT NULL AUTO_INCREMENT,
  `id_estoque` int NOT NULL,
  `tipo_alerta` varchar(50) NOT NULL,
  `mensagem` varchar(255) NOT NULL,
  `data_alerta` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_alerta`),
  KEY `id_estoque` (`id_estoque`),
  CONSTRAINT `alertas_ibfk_1` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=205 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
INSERT INTO `alertas` VALUES (139,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:06:46'),(140,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:06:46'),(141,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:06:46'),(142,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:06:46'),(143,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:06:46'),(144,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:06:46'),(145,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:12:27'),(146,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:12:27'),(147,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:12:27'),(148,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:12:27'),(149,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:12:27'),(150,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:12:27'),(151,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:18:57'),(152,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:18:57'),(153,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:18:57'),(154,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:18:57'),(155,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:18:57'),(156,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:18:57'),(157,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:23:57'),(158,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:23:57'),(159,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:23:57'),(160,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:23:57'),(161,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:23:57'),(162,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:23:57'),(163,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:28:57'),(164,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:28:57'),(165,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:28:57'),(166,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:28:57'),(167,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:28:57'),(168,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:28:57'),(169,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:33:57'),(170,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:33:57'),(171,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:33:57'),(172,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:33:57'),(173,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:33:57'),(174,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:33:57'),(175,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:43:06'),(176,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:43:06'),(177,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:43:06'),(178,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:43:06'),(179,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:43:06'),(180,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:43:06'),(181,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:52:16'),(182,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:52:16'),(183,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:52:16'),(184,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:52:16'),(185,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:52:16'),(186,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:52:16'),(187,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 12:57:22'),(188,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:57:22'),(189,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 12:57:22'),(190,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 12:57:22'),(191,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 12:57:22'),(192,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 12:57:22'),(193,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 13:05:24'),(194,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 13:05:24'),(195,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 13:05:24'),(196,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 13:05:24'),(197,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 13:05:24'),(198,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 13:05:24'),(199,21,'Produto Vencido','O produto \'Produtoteste\' (Lote: 1) está vencido há 1 dias. Data de validade: 2026-09-29.','2026-09-30 13:16:49'),(200,21,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'Produtoteste\' está com 0 unidades. Mínimo configurado: 5 unidades.','2026-09-30 13:16:49'),(201,20,'Produto Vencido','O produto \'Feijão\' (Lote: 1230009) está vencido há 3906 dias. Data de validade: 2016-01-20.','2026-09-30 13:16:49'),(202,20,'Estoque Central Baixo','Atenção: o estoque central de \'Feijão\' está com 0 unidades. Mínimo configurado: 10 unidades.','2026-09-30 13:16:49'),(203,18,'Validade Próxima','O produto \'bolacha\' (Lote: 42695808) vence em 30 dias. Data de validade: 2026-10-30.','2026-09-30 13:16:49'),(204,18,'Estoque Baixo Prateleira','Atenção: a prateleira do produto \'bolacha\' está com 2 unidades. Mínimo configurado: 5 unidades.','2026-09-30 13:16:49');
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_alertas`
--

DROP TABLE IF EXISTS `config_alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_alertas` (
  `id` int NOT NULL DEFAULT '1',
  `dias_antes_validade` int NOT NULL DEFAULT '30',
  `unidades_minimas_central` int NOT NULL DEFAULT '10',
  `unidades_minimas_prateleira` int NOT NULL DEFAULT '5',
  `intervalo_minutos` int NOT NULL DEFAULT '15',
  `exibir_popups` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_alertas`
--

LOCK TABLES `config_alertas` WRITE;
/*!40000 ALTER TABLE `config_alertas` DISABLE KEYS */;
INSERT INTO `config_alertas` VALUES (1,30,10,5,5,1);
/*!40000 ALTER TABLE `config_alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `estoque`
--

DROP TABLE IF EXISTS `estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `estoque` (
  `id_estoque` int NOT NULL AUTO_INCREMENT,
  `nome_produto` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `lote` int NOT NULL,
  `data_validade` date NOT NULL,
  `total_itens` int NOT NULL,
  `peso_un` decimal(10,2) NOT NULL,
  `ativo` tinyint(1) DEFAULT '1',
  PRIMARY KEY (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
INSERT INTO `estoque` VALUES (15,'arroz',1234567,'2027-06-24',1700,2.00,1),(17,'feijao',63,'2027-04-04',1102,1.00,0),(18,'bolacha',42695808,'2026-10-30',1998,0.50,1),(19,'Sabão',89076,'2026-12-12',30,1.00,1),(20,'Feijão',1230009,'2016-01-20',0,12.00,1),(21,'Produtoteste',1,'2026-09-29',50,1.00,1);
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prateleiras`
--

DROP TABLE IF EXISTS `prateleiras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prateleiras` (
  `id_prat` int NOT NULL AUTO_INCREMENT,
  `id_estoque` int NOT NULL,
  `peso_prat` int NOT NULL,
  `quantidade_atual` int NOT NULL,
  `numero_prat` int NOT NULL,
  PRIMARY KEY (`id_prat`),
  KEY `id_estoque` (`id_estoque`),
  CONSTRAINT `prateleiras_ibfk_1` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prateleiras`
--

LOCK TABLES `prateleiras` WRITE;
/*!40000 ALTER TABLE `prateleiras` DISABLE KEYS */;
INSERT INTO `prateleiras` VALUES (8,18,1,2,18),(10,19,6,6,19),(11,15,20,10,15),(13,20,216,18,20);
/*!40000 ALTER TABLE `prateleiras` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nome` varchar(100) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(150) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `cpf` varchar(14) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `senha` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `tipo` varchar(15) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `criado_em` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  UNIQUE KEY `cpf` (`cpf`)
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (14,'ana','ana@email.com','12345647','1234','funcionario','2026-09-09 17:14:05'),(15,'Administrador','admin@valistoque.com','111.111.111-11','admin123','administrador','2026-09-23 11:16:57'),(16,'Funcionario','funcionario@valistoque.com','222.222.222-22','func123','funcionario','2026-09-23 11:16:57'),(18,'kaua','kaua@gmail.com','1234356789','kaua','administrador','2026-09-23 14:36:14');
/*!40000 ALTER TABLE `usuarios` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-30 10:38:02
