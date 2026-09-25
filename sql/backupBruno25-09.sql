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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
/*!40000 ALTER TABLE `alertas` ENABLE KEYS */;
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
INSERT INTO `estoque` VALUES (15,'Arroz 2kg',1234567,'2027-06-24',1710,2000.00,1),(17,'Feijão 1kg',63,'2027-04-04',860,1000.00,1),(18,'Bolacha 100g',42695808,'2028-10-30',12,100.00,1),(19,'Farofa 500g',89075435,'2026-10-29',20,500.00,1),(20,'Tapioca 200g',312323,'2027-01-27',4000,200.00,1),(21,'Leite 1L',8943,'2027-01-23',0,1050.00,0);
/*!40000 ALTER TABLE `estoque` ENABLE KEYS */;
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
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `leituras_prateleira`
--

LOCK TABLES `leituras_prateleira` WRITE;
/*!40000 ALTER TABLE `leituras_prateleira` DISABLE KEYS */;
INSERT INTO `leituras_prateleira` VALUES (1,1,17,4000.00,4,'2026-09-24 21:11:45'),(2,6,19,2000.00,4,'2026-09-24 21:12:22'),(3,4,20,200.00,1,'2026-09-24 21:19:37'),(4,4,20,198.00,1,'2026-09-24 21:19:44'),(5,4,20,205.00,1,'2026-09-24 21:19:55'),(6,4,20,299.00,1,'2026-09-24 21:20:02'),(7,4,20,301.00,2,'2026-09-24 21:20:08'),(8,4,20,400.00,2,'2026-09-24 21:20:15'),(9,4,20,490.00,2,'2026-09-24 21:20:19'),(10,4,20,410.00,2,'2026-09-24 21:20:26'),(11,4,20,600.00,3,'2026-09-25 12:59:57'),(12,5,NULL,600.00,NULL,'2026-09-25 13:02:04'),(13,5,NULL,1200.00,NULL,'2026-09-25 13:08:11'),(14,5,20,1200.00,6,'2026-09-25 14:15:54'),(15,5,19,1050.00,2,'2026-09-25 14:18:11'),(16,3,17,10000.00,10,'2026-09-25 15:12:33');
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
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prateleiras`
--

LOCK TABLES `prateleiras` WRITE;
/*!40000 ALTER TABLE `prateleiras` DISABLE KEYS */;
INSERT INTO `prateleiras` VALUES (1,17,4000.00,4,'2026-09-24 21:11:45'),(2,17,9000.00,9,NULL),(3,21,57750.00,55,NULL),(4,20,600.00,3,'2026-09-25 12:59:57'),(5,19,1050.00,2,'2026-09-25 14:18:11'),(6,19,2000.00,4,'2026-09-24 21:12:22');
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES (14,'ana','ana@email.com','12345647','1234','funcionario','2026-09-09 17:14:05');
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

-- Dump completed on 2026-09-25 13:36:48
