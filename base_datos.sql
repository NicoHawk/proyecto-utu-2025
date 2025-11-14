CREATE DATABASE  IF NOT EXISTS `gestion_db` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_0900_ai_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;
USE `gestion_db`;
-- MySQL dump 10.13  Distrib 8.0.42, for Win64 (x86_64)
--
-- Host: localhost    Database: gestion_db
-- ------------------------------------------------------
-- Server version	8.0.42

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
-- Table structure for table `autos`
--

DROP TABLE IF EXISTS `autos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `autos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(100) NOT NULL,
  `modelo` varchar(100) NOT NULL,
  `marca` varchar(100) NOT NULL,
  `conector` enum('Tipo 1','Tipo 2','CCS Combo 1','CCS Combo 2','CHAdeMO','Tesla (NACS)','GB/T') NOT NULL DEFAULT 'Tipo 2',
  `autonomia` int NOT NULL,
  `anio` int NOT NULL,
  `bateria_actual` int DEFAULT '100' COMMENT 'Nivel de batería actual en porcentaje (0-100)',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `autos`
--

LOCK TABLES `autos` WRITE;
/*!40000 ALTER TABLE `autos` DISABLE KEYS */;
INSERT INTO `autos` VALUES (1,'usuario','A','Tesla','Tipo 1',600,2012,100),(5,'usuario','Spark','Chevrolet','Tipo 2',360,2025,100),(7,'usuario','yuan','byd','Tipo 2',450,2024,100),(8,'usuario','Ferrari Ultimate','Ferrari','CHAdeMO',200,2025,5),(10,'usuario','Lamborghini','Lambo','GB/T',500,2025,100),(11,'usuario','Celerio','Suzuki','Tipo 1',100,2025,100);
/*!40000 ALTER TABLE `autos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cargadores`
--

DROP TABLE IF EXISTS `cargadores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cargadores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(100) NOT NULL,
  `latitud` double NOT NULL,
  `longitud` double NOT NULL,
  `descripcion` text,
  `tipo` varchar(50) DEFAULT '',
  `estado` varchar(30) DEFAULT 'disponible',
  `potencia_kw` decimal(5,2) DEFAULT '0.00',
  `conectores` varchar(255) DEFAULT '',
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=31 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cargadores`
--

LOCK TABLES `cargadores` WRITE;
/*!40000 ALTER TABLE `cargadores` DISABLE KEYS */;
INSERT INTO `cargadores` VALUES (7,'cargador1',-34.722095966065,-55.956424580844,'','DC Rápido','disponible',50.00,'Tipo 2, CCS Combo 2'),(8,'cargador2',-34.719191383596,-55.95611852533,'','DC Rápido','disponible',22.00,'Tipo 1, Tipo 2'),(9,'cargador3',-34.716523765624,-55.963085748432,'','DC Ultra Rápido','disponible',150.00,'CCS Combo 1, CCS Combo 2, CHAdeMO'),(25,'Cargador4',-34.732774326214,-55.973571215718,'','AC Rápido','disponible',22.00,'Tipo 1, Tipo 2, GB/T'),(26,'Cargador5',-34.762396125753,-56.016634397153,'','DC Rápido','disponible',50.00,'Tipo 1, Tipo 2, CCS Combo 1, CCS Combo 2'),(27,'Cargador6',-34.725909513993,-55.963260769952,'','DC Ultra Rápido','disponible',150.00,'CCS Combo 1, CCS Combo 2, CHAdeMO, GB/T'),(28,'Cargador7',-34.725155134373,-55.961483788078,'','DC Rápido','disponible',100.00,'Tipo 1, Tipo 2, CHAdeMO, Tesla (NACS), GB/T'),(29,'prueba',-34.711173629921,-55.973121771407,'','DC Ultra Rápido','disponible',200.00,'Tipo 1, Tipo 2, CCS Combo 1, CCS Combo 2, CHAdeMO, Tesla (NACS), GB/T');
/*!40000 ALTER TABLE `cargadores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `facturas`
--

DROP TABLE IF EXISTS `facturas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `facturas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `pago_id` int NOT NULL,
  `numero` varchar(40) NOT NULL,
  `fecha_emision` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `total` decimal(10,2) NOT NULL,
  `moneda` varchar(10) DEFAULT 'UYU',
  `datos_json` json DEFAULT NULL,
  `pdf_path` varchar(255) DEFAULT NULL,
  `url_pdf` varchar(200) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `numero` (`numero`),
  KEY `fk_factura_pago` (`pago_id`),
  CONSTRAINT `fk_factura_pago` FOREIGN KEY (`pago_id`) REFERENCES `pagos` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `facturas`
--

LOCK TABLES `facturas` WRITE;
/*!40000 ALTER TABLE `facturas` DISABLE KEYS */;
INSERT INTO `facturas` VALUES (1,1,'FAC-20251112-000001','2025-11-12 03:22:17',500.00,'UYU','{\"monto\": \"500.00\", \"estado\": \"aprobado\", \"metodo_id\": 2, \"reserva_id\": 2, \"usuario_id\": \"usuario\"}','factura_FAC-20251112-000001.pdf',NULL,'2025-11-12 06:22:17'),(2,2,'FAC-20251113-000002','2025-11-13 23:25:28',250.00,'UYU','{\"monto\": \"250.00\", \"estado\": \"aprobado\", \"metodo_id\": 1, \"reserva_id\": 3, \"usuario_id\": \"usuario\"}','factura_FAC-20251113-000002.pdf',NULL,'2025-11-14 02:25:28');
/*!40000 ALTER TABLE `facturas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `metodos_pago`
--

DROP TABLE IF EXISTS `metodos_pago`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `metodos_pago` (
  `id` int NOT NULL AUTO_INCREMENT,
  `nombre` varchar(60) NOT NULL,
  `tipo` enum('tarjeta','prepaga','otros') NOT NULL,
  `activo` tinyint(1) DEFAULT '1',
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `metodos_pago`
--

LOCK TABLES `metodos_pago` WRITE;
/*!40000 ALTER TABLE `metodos_pago` DISABLE KEYS */;
INSERT INTO `metodos_pago` VALUES (1,'Tarjeta de Crédito','tarjeta',1,'2025-11-12 05:09:20'),(2,'Tarjeta de Débito','tarjeta',1,'2025-11-12 05:09:20'),(3,'Cuenta Prepaga','prepaga',1,'2025-11-12 05:09:20');
/*!40000 ALTER TABLE `metodos_pago` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pagos`
--

DROP TABLE IF EXISTS `pagos`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `pagos` (
  `id` int NOT NULL AUTO_INCREMENT,
  `reserva_id` int NOT NULL,
  `usuario_id` varchar(50) NOT NULL,
  `metodo_id` int NOT NULL,
  `monto` decimal(10,2) NOT NULL,
  `moneda` varchar(10) DEFAULT 'UYU',
  `estado` enum('iniciado','pendiente','aprobado','rechazado','cancelado') DEFAULT 'iniciado',
  `referencia_externa` varchar(120) DEFAULT NULL,
  `creado_en` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `confirmado_en` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `fk_pago_reserva` (`reserva_id`),
  KEY `fk_pago_metodo` (`metodo_id`),
  KEY `fk_pago_usuario` (`usuario_id`),
  CONSTRAINT `fk_pago_metodo` FOREIGN KEY (`metodo_id`) REFERENCES `metodos_pago` (`id`),
  CONSTRAINT `fk_pago_reserva` FOREIGN KEY (`reserva_id`) REFERENCES `reservas` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_pago_usuario` FOREIGN KEY (`usuario_id`) REFERENCES `usuarios` (`usuario`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pagos`
--

LOCK TABLES `pagos` WRITE;
/*!40000 ALTER TABLE `pagos` DISABLE KEYS */;
INSERT INTO `pagos` VALUES (1,2,'usuario',2,500.00,'UYU','aprobado',NULL,'2025-11-12 06:22:14','2025-11-12 06:22:14'),(2,3,'usuario',1,250.00,'UYU','aprobado',NULL,'2025-11-14 02:25:20','2025-11-14 02:25:20');
/*!40000 ALTER TABLE `pagos` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `reservas`
--

DROP TABLE IF EXISTS `reservas`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `reservas` (
  `id` int NOT NULL AUTO_INCREMENT,
  `usuario` varchar(50) NOT NULL,
  `cargador_id` int NOT NULL,
  `inicio` datetime NOT NULL,
  `fin` datetime NOT NULL,
  `estado` varchar(30) DEFAULT 'confirmada',
  `monto` decimal(10,2) DEFAULT '0.00',
  `pagado` tinyint(1) DEFAULT '0',
  `fecha_creacion` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `usuario` (`usuario`),
  KEY `cargador_id` (`cargador_id`),
  CONSTRAINT `reservas_ibfk_1` FOREIGN KEY (`usuario`) REFERENCES `usuarios` (`usuario`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `reservas_ibfk_2` FOREIGN KEY (`cargador_id`) REFERENCES `cargadores` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `reservas`
--

LOCK TABLES `reservas` WRITE;
/*!40000 ALTER TABLE `reservas` DISABLE KEYS */;
INSERT INTO `reservas` VALUES (1,'usuario',7,'2025-11-11 17:00:00','2025-11-11 18:00:00','cancelada',0.00,0,'2025-11-11 19:57:41'),(2,'usuario',7,'2025-11-12 02:45:00','2025-11-12 03:45:00','completada',500.00,1,'2025-11-12 05:41:33'),(3,'usuario',7,'2025-11-13 23:15:00','2025-11-14 00:15:00','completada',250.00,1,'2025-11-14 02:12:17');
/*!40000 ALTER TABLE `reservas` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `usuarios`
--

DROP TABLE IF EXISTS `usuarios`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `usuarios` (
  `usuario` varchar(50) NOT NULL,
  `correo` varchar(100) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `tipo_usuario` enum('admin','cliente','cargador') NOT NULL DEFAULT 'cliente',
  PRIMARY KEY (`usuario`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `usuarios`
--

LOCK TABLES `usuarios` WRITE;
/*!40000 ALTER TABLE `usuarios` DISABLE KEYS */;
INSERT INTO `usuarios` VALUES ('admin','admin@gmail.com','$2y$10$E39mbImb/JbZf8iFce/aoejoBoIvrVi.M00jWWSLPZEd4ncmHC42O','admin'),('cargador','cargador@gmail.com','$2y$10$qvqnDkepLc0J65mFD1EKYesJdN5cv7OvV359OmFrOjwI7.Cw5bfWu','cargador'),('usuario','usuario@gmail.com','$2y$10$NtMWSeUsLb5YSW.7CMliD.2d4nN4..6OFQlWVwghB1tW7RsVq3j56','cliente');
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

-- Dump completed on 2025-11-14 18:59:52
