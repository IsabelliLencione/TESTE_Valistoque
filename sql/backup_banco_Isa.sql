CREATE DATABASE  IF NOT EXISTS `valistoque_testes` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `valistoque_testes`;
-- MySQL dump 10.13  Distrib 8.0.45, for Win64 (x86_64)
--
-- Host: 127.0.0.1    Database: valistoque_testes
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
  `id` int NOT NULL AUTO_INCREMENT,
  `dias_antes_validade` int NOT NULL DEFAULT '30',
  `caixas_minimas_central` int NOT NULL DEFAULT '10',
  `caixas_minimas_prateleira` int NOT NULL DEFAULT '5',
  `intervalo_minutos` int NOT NULL DEFAULT '15',
  `updated_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `id_estoque_fk` int DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `id_estoque_idx` (`id_estoque_fk`),
  CONSTRAINT `id_estoque` FOREIGN KEY (`id_estoque_fk`) REFERENCES `estoque` (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `alertas`
--

LOCK TABLES `alertas` WRITE;
/*!40000 ALTER TABLE `alertas` DISABLE KEYS */;
INSERT INTO `alertas` VALUES (1,30,10,5,15,'2026-09-08 11:37:07',NULL);
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
  `nome_produto` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `lote` int NOT NULL,
  `data_validade` date NOT NULL,
  `total_itens` int NOT NULL,
  `peso_un` decimal(10,2) NOT NULL,
  PRIMARY KEY (`id_estoque`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `estoque`
--

LOCK TABLES `estoque` WRITE;
/*!40000 ALTER TABLE `estoque` DISABLE KEYS */;
INSERT INTO `estoque` VALUES (1,'Arroz',1234,'2027-10-12',0,1.00),(2,'Arroz',1234,'2027-10-12',0,1.00),(3,'Arroz',1234,'2027-10-12',24,1.00),(4,'Arroz',1234,'2027-10-12',24,1.00),(5,'Arroz',1234,'2027-10-12',24,1.00);
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `prateleiras`
--

LOCK TABLES `prateleiras` WRITE;
/*!40000 ALTER TABLE `prateleiras` DISABLE KEYS */;
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
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
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

-- Dump completed on 2026-09-09 14:14:54
