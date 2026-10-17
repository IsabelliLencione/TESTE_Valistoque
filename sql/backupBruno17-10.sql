CREATE DATABASE  IF NOT EXISTS `valistoque_testes` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `valistoque_testes`;
-- MySQL dump 10.13  Distrib 8.0.46, for Win64 (x86_64)
--
-- Host: localhost    Database: valistoque_testes
-- ------------------------------------------------------
-- Server version	8.0.46

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
) ENGINE=InnoDB AUTO_INCREMENT=62 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `config_alertas`
--

DROP TABLE IF EXISTS `config_alertas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `config_alertas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `dias_antes_validade` int NOT NULL DEFAULT '30',
  `unidades_minimas_central` int NOT NULL DEFAULT '10',
  `unidades_minimas_prateleira` int NOT NULL DEFAULT '5',
  `intervalo_minutos` int NOT NULL DEFAULT '15',
  `exibir_popups` tinyint(1) NOT NULL DEFAULT '1',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `config_alertas`
--

LOCK TABLES `config_alertas` WRITE;
/*!40000 ALTER TABLE `config_alertas` DISABLE KEYS */;
INSERT INTO `config_alertas` VALUES (1,30,10,5,15,1);
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
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
INSERT INTO `estoque` VALUES (15,'Arroz 2kg',1234567,'2027-06-24',1610,2000.00,1),(17,'Feijão 1kg',63,'2027-04-04',660,1000.00,1),(18,'Bolacha 100g',42695808,'2028-10-30',12,100.00,1),(19,'Farofa 500g',89075435,'2026-10-29',20,500.00,1),(20,'Tapioca 200g',312323,'2027-01-27',2980,200.00,1),(21,'Leite 1L',8943,'2027-01-23',812,1050.00,0),(22,'Macarrão 1kg',29093,'2027-02-16',800,1000.00,1),(24,'Salgadinho 85g',3456,'2027-12-30',900,85.00,1),(25,'Pipoca 100g',8932,'2026-10-07',100,100.00,1),(26,'Coca-Cola 1,5L',985778,'2027-01-01',1000,1500.00,1);
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `historico_estoque`
--

DROP TABLE IF EXISTS `historico_estoque`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `historico_estoque` (
  `id_historico` int NOT NULL AUTO_INCREMENT,
  `id_estoque` int NOT NULL,
  `tipo` varchar(30) COLLATE utf8mb4_unicode_ci NOT NULL,
  `quantidade` int NOT NULL,
  `id_prat` int DEFAULT NULL,
  `observacao` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `data_movimentacao` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_historico`),
  KEY `idx_historico_data` (`data_movimentacao`),
  KEY `idx_historico_tipo` (`tipo`),
  KEY `idx_historico_estoque` (`id_estoque`),
  KEY `fk_historico_prateleira` (`id_prat`),
  CONSTRAINT `fk_historico_estoque` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_historico_prateleira` FOREIGN KEY (`id_prat`) REFERENCES `prateleiras` (`id_prat`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `chk_historico_quantidade` CHECK ((`quantidade` > 0)),
  CONSTRAINT `chk_historico_tipo` CHECK ((`tipo` in (_utf8mb4'entrada',_utf8mb4'saida',_utf8mb4'ajuste_entrada',_utf8mb4'ajuste_saida')))
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `historico_estoque`
--

LOCK TABLES `historico_estoque` WRITE;
/*!40000 ALTER TABLE `historico_estoque` DISABLE KEYS */;
INSERT INTO `historico_estoque` VALUES (1,25,'entrada',750,NULL,'Cadastro inicial do lote','2026-10-16 15:09:24'),(2,25,'ajuste_entrada',50,NULL,'Ajuste manual de quantidade','2026-10-16 16:17:58'),(3,25,'ajuste_saida',700,NULL,'Ajuste manual de quantidade','2026-10-16 16:18:13'),(4,26,'entrada',300,NULL,'Cadastro inicial do lote','2026-10-16 16:19:22'),(5,26,'ajuste_entrada',200,NULL,'Ajuste manual de quantidade','2026-10-16 16:19:35'),(6,21,'ajuste_entrada',812,NULL,'Ajuste manual de quantidade','2026-10-16 16:20:20'),(7,26,'saida',100,5,'Transferência para prateleira','2026-10-16 16:32:56'),(8,26,'saida',150,5,'Transferência para prateleira','2026-10-16 16:33:04'),(9,26,'saida',55,4,'Transferência para prateleira','2026-10-16 16:34:26'),(10,26,'ajuste_entrada',5,NULL,'Ajuste manual de quantidade','2026-10-16 21:53:03'),(11,26,'ajuste_entrada',55,NULL,'Ajuste manual de quantidade','2026-10-16 22:16:09'),(12,26,'saida',32,4,'Transferência para prateleira','2026-10-16 22:16:18'),(13,26,'ajuste_entrada',566,NULL,'Ajuste manual de quantidade','2026-10-16 22:16:24'),(14,26,'ajuste_saida',89,NULL,'Ajuste manual de quantidade','2026-10-16 22:16:29'),(15,26,'saida',699,5,'Transferência para prateleira','2026-10-16 22:16:37'),(16,26,'ajuste_entrada',999,NULL,'Ajuste manual de quantidade','2026-10-16 22:16:45');
/*!40000 ALTER TABLE `historico_estoque` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `leituras_prateleira`
--

DROP TABLE IF EXISTS `leituras_prateleira`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `leituras_prateleira` (
  `id_leitura` int NOT NULL AUTO_INCREMENT,
  `id_prat` int NOT NULL,
  `id_estoque` int DEFAULT NULL,
  `peso` decimal(10,2) NOT NULL,
  `quantidade_calculada` int DEFAULT NULL,
  `data_leitura` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id_leitura`),
  KEY `id_prat` (`id_prat`),
  KEY `id_estoque` (`id_estoque`),
  CONSTRAINT `leituras_prateleira_ibfk_1` FOREIGN KEY (`id_prat`) REFERENCES `prateleiras` (`id_prat`),
  CONSTRAINT `leituras_prateleira_ibfk_2` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=131 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leituras_prateleira`
--

LOCK TABLES `leituras_prateleira` WRITE;
/*!40000 ALTER TABLE `leituras_prateleira` DISABLE KEYS */;
INSERT INTO `leituras_prateleira` VALUES (1,1,17,4000.00,4,'2026-09-24 21:11:45'),(2,6,19,2000.00,4,'2026-09-24 21:12:22'),(3,4,20,200.00,1,'2026-09-24 21:19:37'),(4,4,20,198.00,1,'2026-09-24 21:19:44'),(5,4,20,205.00,1,'2026-09-24 21:19:55'),(6,4,20,299.00,1,'2026-09-24 21:20:02'),(7,4,20,301.00,2,'2026-09-24 21:20:08'),(8,4,20,400.00,2,'2026-09-24 21:20:15'),(9,4,20,490.00,2,'2026-09-24 21:20:19'),(10,4,20,410.00,2,'2026-09-24 21:20:26'),(11,4,20,600.00,3,'2026-09-25 12:59:57'),(12,5,NULL,600.00,NULL,'2026-09-25 13:02:04'),(13,5,NULL,1200.00,NULL,'2026-09-25 13:08:11'),(14,5,20,1200.00,6,'2026-09-25 14:15:54'),(15,5,19,1050.00,2,'2026-09-25 14:18:11'),(16,3,17,10000.00,10,'2026-09-25 15:12:33'),(17,6,19,3000.00,6,'2026-09-28 15:26:09'),(18,4,20,1000.00,5,'2026-09-28 16:53:53'),(19,4,20,1000.00,5,'2026-09-28 16:54:08'),(20,4,20,1000.00,5,'2026-09-28 16:54:24'),(21,5,19,2000.00,4,'2026-09-28 16:54:49'),(22,5,19,2000.00,4,'2026-09-28 16:56:34'),(23,5,19,2000.00,4,'2026-09-28 16:56:49'),(24,5,19,2000.00,4,'2026-09-28 16:57:04'),(25,5,19,2000.00,4,'2026-09-28 16:57:20'),(26,5,19,2000.00,4,'2026-09-28 16:57:35'),(27,5,19,2000.00,4,'2026-09-28 16:57:51'),(28,5,19,2000.00,4,'2026-09-28 16:58:06'),(29,5,19,2000.00,4,'2026-09-28 16:58:21'),(30,5,19,2000.00,4,'2026-09-28 16:58:37'),(31,5,19,2000.00,4,'2026-09-28 16:58:52'),(32,5,19,2000.00,4,'2026-09-28 16:59:07'),(33,5,19,2000.00,4,'2026-09-28 16:59:23'),(34,5,19,2000.00,4,'2026-09-28 16:59:38'),(35,5,19,2000.00,4,'2026-09-28 16:59:53'),(36,5,19,2000.00,4,'2026-09-28 17:00:09'),(37,5,19,2000.00,4,'2026-09-28 17:00:24'),(38,5,19,2000.00,4,'2026-09-28 17:00:39'),(39,5,19,2000.00,4,'2026-09-28 17:00:55'),(40,5,19,2000.00,4,'2026-09-28 17:01:10'),(41,5,19,2000.00,4,'2026-09-28 17:01:26'),(42,5,19,2000.00,4,'2026-09-28 17:01:41'),(43,5,19,2000.00,4,'2026-09-28 17:01:56'),(44,5,19,2000.00,4,'2026-09-28 17:02:12'),(45,5,19,2000.00,4,'2026-09-28 17:02:27'),(46,5,19,2000.00,4,'2026-09-28 17:02:42'),(47,5,19,2000.00,4,'2026-09-28 17:02:58'),(48,5,19,2000.00,4,'2026-09-28 17:03:13'),(49,5,19,2000.00,4,'2026-09-28 17:03:28'),(50,5,19,2000.00,4,'2026-09-28 17:03:44'),(51,5,19,2000.00,4,'2026-09-28 17:03:59'),(52,5,19,2000.00,4,'2026-09-28 17:04:14'),(53,5,19,2000.00,4,'2026-09-28 17:04:30'),(54,5,19,2000.00,4,'2026-09-28 17:04:45'),(55,5,19,2000.00,4,'2026-09-28 17:05:01'),(56,5,19,2000.00,4,'2026-09-28 17:05:16'),(57,5,19,2000.00,4,'2026-09-28 17:05:31'),(58,5,19,2000.00,4,'2026-09-28 17:05:47'),(59,5,19,2000.00,4,'2026-09-28 17:06:02'),(60,5,19,2000.00,4,'2026-09-28 17:06:17'),(61,5,19,2000.00,4,'2026-09-28 17:06:33'),(62,5,19,2000.00,4,'2026-09-28 17:06:48'),(63,5,19,2000.00,4,'2026-09-28 17:07:03'),(64,5,19,2000.00,4,'2026-09-28 17:07:19'),(65,5,19,2000.00,4,'2026-09-28 17:07:34'),(66,5,19,2000.00,4,'2026-09-28 17:07:49'),(67,5,19,2000.00,4,'2026-09-28 17:08:05'),(68,5,19,2000.00,4,'2026-09-28 17:08:29'),(69,5,19,2000.00,4,'2026-09-28 17:08:45'),(70,6,19,1000.00,2,'2026-09-28 17:09:09'),(71,6,19,1000.00,2,'2026-09-28 17:09:24'),(72,6,19,1000.00,2,'2026-09-28 17:09:41'),(73,6,19,1000.00,2,'2026-09-28 17:09:57'),(74,6,19,1000.00,2,'2026-09-28 17:10:12'),(75,6,19,1000.00,2,'2026-09-28 17:10:28'),(76,6,19,1000.00,2,'2026-09-28 17:10:43'),(77,6,19,1000.00,2,'2026-09-28 17:11:00'),(78,6,19,1000.00,2,'2026-09-28 17:11:34'),(79,6,19,1000.00,2,'2026-09-28 17:11:52'),(80,6,19,1000.00,2,'2026-09-28 17:12:07'),(81,6,19,1000.00,2,'2026-09-28 17:12:24'),(82,6,19,1000.00,2,'2026-09-28 17:12:39'),(83,6,19,1000.00,2,'2026-09-28 17:13:12'),(84,6,19,1000.00,2,'2026-09-28 17:13:28'),(85,6,19,1000.00,2,'2026-09-28 17:13:44'),(86,6,19,1000.00,2,'2026-09-28 17:13:59'),(87,6,19,1000.00,2,'2026-09-28 17:14:14'),(88,6,19,1000.00,2,'2026-09-28 17:14:30'),(89,6,19,1000.00,2,'2026-09-28 17:14:46'),(90,6,19,1000.00,2,'2026-09-28 17:15:01'),(91,6,19,1000.00,2,'2026-09-28 17:15:17'),(92,6,19,1000.00,2,'2026-09-28 17:15:33'),(93,6,19,1000.00,2,'2026-09-28 17:15:49'),(94,6,19,1000.00,2,'2026-09-28 17:16:04'),(95,6,19,1000.00,2,'2026-09-28 17:16:19'),(96,6,19,1000.00,2,'2026-09-28 17:16:35'),(97,6,19,1000.00,2,'2026-09-28 17:16:51'),(98,6,19,1000.00,2,'2026-09-28 17:17:07'),(99,6,19,1000.00,2,'2026-09-28 17:17:23'),(100,6,19,1000.00,2,'2026-09-28 17:17:39'),(101,6,19,1000.00,2,'2026-09-28 17:18:42'),(102,4,20,3000.00,15,'2026-09-30 14:57:15'),(103,7,15,10000.00,5,'2026-09-30 15:01:23'),(104,2,22,2000.00,2,'2026-09-30 15:08:27'),(105,2,22,2010.00,2,'2026-09-30 15:09:15'),(106,1,24,200.80,2,'2026-10-14 15:11:33'),(107,1,24,200.80,2,'2026-10-14 18:00:53'),(108,1,24,200.80,2,'2026-10-14 18:04:11'),(109,1,24,0.00,0,'2026-10-14 18:08:05'),(110,1,24,260.98,3,'2026-10-14 18:08:22'),(111,1,24,260.55,3,'2026-10-14 18:08:39'),(112,1,24,0.01,0,'2026-10-14 18:08:56'),(113,1,24,0.00,0,'2026-10-14 18:09:12'),(114,1,24,0.00,0,'2026-10-14 18:09:29'),(115,1,24,0.00,0,'2026-10-14 18:09:47'),(116,1,24,0.00,0,'2026-10-14 18:10:04'),(117,1,24,0.00,0,'2026-10-14 18:10:20'),(118,1,24,0.00,0,'2026-10-14 18:10:37'),(119,1,24,0.00,0,'2026-10-14 18:10:54'),(120,1,24,0.00,0,'2026-10-14 18:11:11'),(121,1,24,0.00,0,'2026-10-14 18:11:27'),(122,1,24,0.00,0,'2026-10-14 18:11:44'),(123,1,24,0.00,0,'2026-10-14 18:12:01'),(124,1,24,0.00,0,'2026-10-14 18:12:18'),(125,1,24,0.00,0,'2026-10-14 18:12:35'),(126,1,24,0.00,0,'2026-10-14 18:13:22'),(127,1,24,0.29,0,'2026-10-14 18:14:09'),(128,1,24,0.20,0,'2026-10-14 18:14:26'),(129,1,24,0.00,0,'2026-10-14 18:15:01'),(130,1,24,0.25,0,'2026-10-14 18:15:19');
/*!40000 ALTER TABLE `leituras_prateleira` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `prateleiras`
--

DROP TABLE IF EXISTS `prateleiras`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `prateleiras` (
  `id_prat` int NOT NULL AUTO_INCREMENT,
  `id_estoque` int DEFAULT NULL,
  `peso_prat` decimal(10,2) DEFAULT NULL,
  `qte` int DEFAULT NULL,
  `ultima_leitura` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id_prat`),
  KEY `id_estoque` (`id_estoque`),
  CONSTRAINT `prateleiras_ibfk_1` FOREIGN KEY (`id_estoque`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prateleiras`
--

LOCK TABLES `prateleiras` WRITE;
/*!40000 ALTER TABLE `prateleiras` DISABLE KEYS */;
INSERT INTO `prateleiras` VALUES (1,24,0.25,0,'2026-10-14 18:15:19'),(2,22,2010.00,2,'2026-09-30 15:09:15'),(3,21,57750.00,55,NULL),(4,26,130500.00,87,NULL),(5,26,1423500.00,949,NULL),(6,19,1000.00,2,'2026-09-28 17:18:42'),(7,NULL,0.00,NULL,NULL),(8,24,4250.00,50,NULL);
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (2,'func','func@valistoque.com','98765432','func123','Funcionario','2026-10-16 13:00:54'),(14,'ana','ana@email.com','12345647','1234','funcionario','2026-09-09 17:14:05'),(16,'admin','admin@valistoque.com','11122233344','admin123','Administrador','2026-10-16 13:00:57');
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

-- Dump completed on 2026-10-17 19:58:55
