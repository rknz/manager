-- MariaDB dump 10.19  Distrib 10.4.32-MariaDB, for Win64 (AMD64)
--
-- Host: localhost    Database: lily_app
-- ------------------------------------------------------
-- Server version	10.4.32-MariaDB

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `app_activity_log`
--

DROP TABLE IF EXISTS `app_activity_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_activity_log` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` enum('create','update','delete') NOT NULL,
  `module` varchar(50) NOT NULL,
  `record_id` int(11) NOT NULL,
  `old_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`old_value`)),
  `new_value` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`new_value`)),
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `app_activity_log_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_activity_log`
--

LOCK TABLES `app_activity_log` WRITE;
/*!40000 ALTER TABLE `app_activity_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_activity_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_attendance`
--

DROP TABLE IF EXISTS `app_attendance`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_attendance` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `worker_id` int(11) NOT NULL,
  `work_date` date NOT NULL,
  `daily_rate` decimal(10,2) NOT NULL,
  `attendance_multiplier` decimal(3,1) NOT NULL,
  `earned` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `category_id` (`category_id`),
  KEY `worker_id` (`worker_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_attendance_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_attendance_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`),
  CONSTRAINT `app_attendance_ibfk_3` FOREIGN KEY (`worker_id`) REFERENCES `app_workers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_attendance_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=55 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_attendance`
--

LOCK TABLES `app_attendance` WRITE;
/*!40000 ALTER TABLE `app_attendance` DISABLE KEYS */;
INSERT INTO `app_attendance` VALUES (9,14,NULL,3,'2026-05-05',1100.00,1.0,1100.00,'',1,1,'2026-08-28 17:09:35','2026-08-28 20:29:59',0),(10,14,NULL,3,'2026-05-06',1100.00,1.0,1100.00,'',1,1,'2026-08-28 17:09:35','2026-08-28 20:30:04',0),(11,14,NULL,3,'2026-05-07',1100.00,1.0,1100.00,'',0,1,'2026-08-28 17:09:35','2026-08-28 17:09:35',0),(12,14,NULL,2,'2099-12-10',1500.00,1.5,2250.00,'',1,1,'2026-08-28 17:51:32','2026-08-28 17:52:07',0),(13,14,NULL,2,'2099-12-02',1200.00,1.0,1200.00,'',1,1,'2026-08-28 17:51:32','2026-08-28 17:52:07',0),(14,14,NULL,2,'2099-12-03',1200.00,1.0,1200.00,'',1,1,'2026-08-28 17:51:32','2026-08-28 17:52:07',0),(15,14,NULL,4,'2026-05-17',1100.00,1.0,1100.00,'',0,1,'2026-08-28 17:59:02','2026-08-28 17:59:02',0),(16,11,NULL,5,'2026-08-28',1200.00,1.0,1200.00,'',0,1,'2026-08-28 18:02:12','2026-08-28 18:02:12',0),(17,14,NULL,4,'2099-01-05',700.00,1.0,700.00,NULL,1,1,'2026-08-28 18:29:47','2026-08-28 18:31:20',0),(18,14,NULL,5,'2099-01-05',600.00,1.0,600.00,NULL,1,1,'2026-08-28 18:29:47','2026-08-28 18:31:20',0),(19,14,NULL,4,'2099-01-06',700.00,1.0,700.00,NULL,1,1,'2026-08-28 18:29:47','2026-08-28 18:31:20',0),(20,14,NULL,8,'2026-08-28',1000.00,1.0,1000.00,'',0,1,'2026-08-28 18:46:29','2026-08-28 18:46:29',0),(21,11,NULL,8,'2026-08-20',1000.00,1.0,1000.00,'',0,1,'2026-08-28 18:46:46','2026-08-28 18:46:46',0),(22,11,NULL,8,'2026-08-21',1000.00,1.0,1000.00,'',1,1,'2026-08-28 18:46:46','2026-08-29 18:18:09',0),(23,11,NULL,8,'2026-08-22',1000.00,1.0,1000.00,'',0,1,'2026-08-28 18:46:46','2026-08-28 18:46:46',0),(24,11,NULL,8,'2026-08-23',1000.00,1.0,1000.00,'',0,1,'2026-08-28 18:47:01','2026-08-28 18:47:01',0),(25,11,NULL,4,'2026-08-14',1200.00,1.0,1200.00,'',0,1,'2026-08-28 19:34:55','2026-08-28 19:34:55',0),(26,11,NULL,8,'2026-08-01',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(27,11,NULL,8,'2026-08-02',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(28,11,NULL,8,'2026-08-03',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(29,11,NULL,8,'2026-08-04',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(30,11,NULL,8,'2026-08-05',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(31,11,NULL,8,'2026-08-06',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(32,11,NULL,8,'2026-08-07',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(33,11,NULL,8,'2026-08-08',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(34,11,NULL,8,'2026-08-09',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(35,11,NULL,8,'2026-08-10',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(36,11,NULL,8,'2026-08-11',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(37,11,NULL,8,'2026-08-12',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(38,11,NULL,8,'2026-08-13',1000.00,1.0,1000.00,'',0,1,'2026-08-28 20:12:11','2026-08-28 20:12:11',0),(39,11,NULL,8,'2026-08-14',1000.00,1.0,1000.00,'',1,1,'2026-08-28 20:12:11','2026-08-29 18:18:32',0),(40,11,NULL,8,'2026-08-15',1000.00,1.0,1000.00,'',1,1,'2026-08-28 20:12:11','2026-08-29 18:18:22',0),(41,11,2,1,'2026-09-04',800.00,1.0,800.00,'Test attendance',0,1,'2026-09-04 18:12:58','2026-09-04 18:12:58',0),(44,11,NULL,4,'2026-09-04',1200.00,1.0,1200.00,'',0,1,'2026-09-04 19:03:28','2026-09-04 19:03:28',0),(45,11,NULL,2,'2026-09-04',1100.00,1.0,1100.00,'',0,1,'2026-09-04 19:03:37','2026-09-04 19:03:37',0),(46,14,NULL,2,'2026-09-04',1100.00,1.0,1100.00,'',0,1,'2026-09-04 19:03:48','2026-09-04 19:03:48',0),(48,14,NULL,8,'2026-09-04',1000.00,0.5,500.00,'',0,1,'2026-09-04 19:48:27','2026-09-04 19:48:27',0),(49,11,NULL,4,'2026-09-06',1200.00,1.0,1200.00,'',1,1,'2026-09-04 20:46:36','2026-09-06 14:03:22',0),(50,14,NULL,4,'2026-09-05',1200.00,2.0,2400.00,'',0,1,'2026-09-06 13:51:53','2026-09-06 13:51:53',0),(51,14,NULL,4,'2026-09-06',1200.00,1.0,1200.00,'',0,3,'2026-09-06 14:30:11','2026-09-06 14:30:11',0),(52,14,NULL,2,'2026-09-06',1100.00,1.0,1100.00,'',0,8,'2026-09-06 18:31:36','2026-09-06 18:31:36',0),(53,14,NULL,9,'2026-09-04',1300.00,1.0,1300.00,'',0,8,'2026-09-06 18:33:30','2026-09-06 18:33:30',0),(54,14,NULL,9,'2026-09-05',1300.00,1.0,1300.00,'',0,8,'2026-09-06 18:33:40','2026-09-06 18:33:40',0);
/*!40000 ALTER TABLE `app_attendance` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_categories`
--

DROP TABLE IF EXISTS `app_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `billing_type` enum('purchase_only','purchase_contractor','attendance') NOT NULL,
  `is_default` tinyint(4) DEFAULT 0,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_categories`
--

LOCK TABLES `app_categories` WRITE;
/*!40000 ALTER TABLE `app_categories` DISABLE KEYS */;
INSERT INTO `app_categories` VALUES (1,'Board & Wood','purchase_contractor',1,1,'2026-08-18 12:01:29','2026-08-18 12:01:29',0,0),(2,'Paint','attendance',1,1,'2026-08-18 12:01:29','2026-08-18 12:01:29',0,0),(3,'Electrical & Sanitary','attendance',1,1,'2026-08-18 12:01:29','2026-08-18 12:01:29',0,0),(4,'Thai & Glass','purchase_contractor',1,1,'2026-08-18 12:01:29','2026-08-18 12:01:29',0,0),(5,'Supply','purchase_only',1,1,'2026-08-18 12:01:29','2026-08-18 12:01:29',0,0),(6,'Hardware','purchase_contractor',0,1,'2026-09-06 18:54:29','2026-09-06 18:54:29',0,0);
/*!40000 ALTER TABLE `app_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_client_payments`
--

DROP TABLE IF EXISTS `app_client_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_client_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Cheque','bKash','Nagad') NOT NULL,
  `receipt_photo_path` text DEFAULT NULL,
  `who_received` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_client_payments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_client_payments_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_client_payments`
--

LOCK TABLES `app_client_payments` WRITE;
/*!40000 ALTER TABLE `app_client_payments` DISABLE KEYS */;
INSERT INTO `app_client_payments` VALUES (4,14,5000.00,'2026-07-02','Cash',NULL,NULL,'44',0,1,'2026-08-28 17:25:24','2026-08-28 17:25:24',0);
/*!40000 ALTER TABLE `app_client_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_contractor_advances`
--

DROP TABLE IF EXISTS `app_contractor_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_contractor_advances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Cheque','bKash','Nagad') NOT NULL,
  `who_paid` varchar(100) DEFAULT NULL,
  `who_received` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_contractor_advances_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_contractor_advances_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_contractor_advances_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`),
  CONSTRAINT `app_contractor_advances_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_contractor_advances`
--

LOCK TABLES `app_contractor_advances` WRITE;
/*!40000 ALTER TABLE `app_contractor_advances` DISABLE KEYS */;
INSERT INTO `app_contractor_advances` VALUES (5,14,1,NULL,5000.00,'2026-08-28','Cash','admin','','',0,1,'2026-08-28 17:26:03','2026-08-28 17:26:03',0),(6,11,5,NULL,500.00,'2026-08-28','Cash','admin','bab','',0,1,'2026-08-28 18:24:33','2026-08-28 18:24:33',0),(7,14,5,NULL,1000.00,'2099-01-06','Cash','admin',NULL,NULL,1,1,'2026-08-28 18:29:47','2026-08-28 18:31:20',0),(8,14,5,NULL,1000.00,'2026-08-28','Cash','admin','babu','',0,1,'2026-08-28 18:45:28','2026-08-28 18:45:28',0),(9,11,1,NULL,1000.00,'2026-08-28','Cash','admin','','',0,1,'2026-08-28 18:51:36','2026-08-28 18:51:36',0),(10,11,1,NULL,50000.00,'2026-08-28','Cash','admin','musa','',0,1,'2026-08-28 20:58:36','2026-08-28 20:58:36',0),(11,11,1,NULL,5000.00,'2026-09-06','Cash','Rukon','musa','',0,8,'2026-09-06 16:14:01','2026-09-06 16:14:01',0);
/*!40000 ALTER TABLE `app_contractor_advances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_contractor_bills`
--

DROP TABLE IF EXISTS `app_contractor_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_contractor_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `bill_data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`bill_data`)),
  `sub_total` decimal(15,2) DEFAULT 0.00,
  `labour_charge` decimal(15,2) DEFAULT 0.00,
  `other_charge` decimal(15,2) DEFAULT 0.00,
  `grand_total` decimal(15,2) NOT NULL,
  `total_paid` decimal(15,2) NOT NULL,
  `balance_due` decimal(15,2) NOT NULL,
  `bill_language` enum('bn','en') NOT NULL DEFAULT 'bn',
  `bill_date` date NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_contractor_bills_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_contractor_bills_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_contractor_bills_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`),
  CONSTRAINT `app_contractor_bills_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_contractor_bills`
--

LOCK TABLES `app_contractor_bills` WRITE;
/*!40000 ALTER TABLE `app_contractor_bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_contractor_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_contractors`
--

DROP TABLE IF EXISTS `app_contractors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_contractors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `trade` varchar(50) NOT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_contractors`
--

LOCK TABLES `app_contractors` WRITE;
/*!40000 ALTER TABLE `app_contractors` DISABLE KEYS */;
INSERT INTO `app_contractors` VALUES (1,'Musa Seikh','01311389641','','Carpenter','',1,'2026-08-19 14:33:36','2026-08-26 12:33:20',0),(2,'Shakhawat Hossain','01879218041',NULL,'Thai Glass','',1,'2026-08-19 17:31:51','2026-08-19 18:42:35',0),(3,'Arif','01714376116',NULL,'Painter','',1,'2026-08-19 18:41:46','2026-08-19 18:41:46',0),(4,'Nuruzzaman','01725117354','Jahaj Building - Uttar Badda','Paint','',0,'2026-08-27 02:05:44','2026-08-28 18:42:08',0),(5,'Afsar','01818628034','Uttar Badda ORG: FENI','Electritian','',1,'2026-08-28 18:00:33','2026-08-28 18:43:04',0);
/*!40000 ALTER TABLE `app_contractors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_estimate_catalog`
--

DROP TABLE IF EXISTS `app_estimate_catalog`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_estimate_catalog` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category` varchar(50) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `specifications` text NOT NULL,
  `unit` varchar(20) NOT NULL DEFAULT 'S.ft',
  `default_rate` decimal(12,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_estimate_catalog`
--

LOCK TABLES `app_estimate_catalog` WRITE;
/*!40000 ALTER TABLE `app_estimate_catalog` DISABLE KEYS */;
INSERT INTO `app_estimate_catalog` VALUES (1,'Ceiling','Plain Particle Ceiling','Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with matt enamel/plastic paint finish as per design.','S.ft',520.00,'2026-09-07 12:42:11'),(2,'Ceiling','Drop Beam Ceiling','Drop Beam: Supply fitting/fixing of drop ceiling over 7\'-0\" height plain particle board on garjan wooden frame all complete plastic paint as per design.','S.ft',540.00,'2026-09-07 12:42:11'),(3,'Ceiling','Gypsum Ceiling','Gypsum Ceiling: Supply fitting/fixing of gypsum ceiling over 8\'-6\" height 24\"x24\" board on garjan wooden frame all complete.','S.ft',80.00,'2026-09-07 12:42:11'),(4,'Ceiling','Duco Finish Particle Ceiling','Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with doccu paint finish.','S.ft',600.00,'2026-09-07 12:42:11'),(5,'Furniture','Full Height Wardrobe Cabinet','Cabinet: Making, supplying and fixing of full height cabinet made of 18mm HPL pasting board shutter & outer side with inside shelf by 18mm G.ply(Akij) with booth side .5mm waterproof indian formica auto machine pasting on almunium T-Bit bordering and big size groove handrail, made Length VariableX 102\"(H) X16\"/18\"(D) including all accessories matching edging with backside extra damp protection all complete as per design.','S.ft',2150.00,'2026-09-07 12:42:11'),(6,'Furniture','Low Height Cabinet (LHC)','Low Height Cabinet (LHC): Making, supplying and fixing of LHC cabinet made Length VariableX 30\"(H) X24\"(D) with 18mm melamine / HPL board & matching edging all complete.','S.ft',1700.00,'2026-09-07 12:42:11'),(7,'Furniture','Kitchen Overhead Cabinet (OHC)','Kitchen (O.H.C): Making, supplying and fixing of kitchen cabinet made of 18mm garjan ply(Akij) with HPL pasting and aluminium profile handle bar made with including all accessories matching edging all complete.','S.ft',2250.00,'2026-09-07 12:42:11'),(8,'Furniture','Kitchen Middle Cabinet (MHC)','Kitchen (M.H.C): Making, supplying and fixing of kitchen cabinet made of 3/4\" garjan ply(Akij) with HPL sheet pasting and aluminium profile handle bar made with including all accessories matching edging all complete.','S.ft',3100.00,'2026-09-07 12:42:11'),(9,'Furniture','Kitchen Lower Cabinet (LHC)','Kitchen (L.H.C): Making, supplying and fixing of kitchen cabinet made of 3/4\" marine ply(Akij) with HPL sheet pasting aluminium profile handle bar including all SS accessories matching edging all complete.','S.ft',2850.00,'2026-09-07 12:42:11'),(10,'Furniture','Dinner Wagon Cabinet','Dinner Wagon: Making, supplying and fixing of dinner wagon cabinet made of 18mm HPL with Akij G.Plywood(main body) & 18mm glass profile (shutter) with shutter front side are mercury glass finishing & middle space design glass on marble top made Length VariableX 96\"(H) X16\"(D) with including all accessories matching edging all complete.','S.ft',2450.00,'2026-09-07 12:42:11'),(11,'Furniture','TV Cabinet with Charcoal Finish','TV Cabinet: Making, supplying and fixing of TV cabinet made of 18mm HPL with Akij G.ply board on Garjan framing and booth side imported charcoal finishing & including all accessories matching edging all complete as per design.','S.ft',1350.00,'2026-09-07 12:42:11'),(12,'Furniture','Dressing Unit with Touch LED Mirror','Dressing: Making, supplying and fixing of dressing cabinet for bedroom made of 18mm HPL Pasting Akij Plywood boards and inside shelf by white/gray formica on ahmed g.ply including best quality mirror with touch lighting and imported cabinet glass frame, making best quality dressing stool/seater all complete as per design.','S.ft',1450.00,'2026-09-07 12:42:11'),(13,'Furniture','Curtain Pelmet Box','Palmet Box & Paneling: Making, supplying and fixing of curtain pelmet box made of 18mm G.Ply(Akij) on BT.Garjan wooden frame with matching edging including all accessories all complete with doccu paint as per design.','S.ft',1050.00,'2026-09-07 12:42:11'),(14,'Furniture','King Size Sleeping Bed (5\'-6\"x7\'-0\")','Sleeping Bed: Making decorative king size sleeping bed for master bedroom(size:5\'-6\"x7\'-0\") supplying, fixing and made of 18mm Garjan Plywood(Akij) board and Top side & down side are solid wood and original indian fabrics,foaming finishing with Eurasia mattress (10year warranty) including Eurasia mattress topper matching edging complete.','nos',115000.00,'2026-09-07 12:42:11'),(15,'Furniture','Modern Reception Table','Reception Table: Making, supplying and fixing of modern reception table made Length VariableX 40\"(H) x96\"(L)x18\"D with 18mm HPL board with matching edging all complete accessories.','nos',44000.00,'2026-09-07 12:42:11'),(16,'Furniture','Meeting Table 8\' x 3\'','Meeting Table: Supply fitting/fixing of meeting table 8\'-0\"x3\'-0\" HPL board with metal leg all complete as per design.','nos',30500.00,'2026-09-07 12:42:11'),(17,'Furniture','Workstation 6-Person (Clear Glass Divider)','Workstation 6p: Making workstation made Length Variable 32\"X30\"(Height) X24\"(depth) with keyboard tray with upper divider by 10mm clear glass(1FT height) and frosted paper design on lower part 18mm melamine board & matching edging. All complete as per design.','per.',8000.00,'2026-09-07 12:42:11'),(18,'Furniture','Workstation 3-Person','Workstation 3p: Making workstation table made Length Variable 48\"X30\"(Height) X24\"(depth) with keyboard tray and drawer unit Table Top (HPL) lower part 18mm melamine board & matching edging. All complete as per design.','per.',12500.00,'2026-09-07 12:42:11'),(19,'Furniture','Counselor Table (4\'x2\')','Counselor Table: Making fitting/fixing of counselor table 4\'-0\"x2\'-0\" HPL board(top) with lower part 18mm melamine board all complete.','nos',12500.00,'2026-09-07 12:42:11'),(20,'Furniture','Hanging Common Basin Set','Common Basin set: Making, supplying and fixing of Hanging common basin with big size design round mirror & basin top granite (lower part 18mm Akij Marine.Ply with doccu) including all accessories all complete.','nos',42300.00,'2026-09-07 12:42:11'),(21,'Furniture','Solid Segun/Chambol Wood Folding Door','Folding Door (2 pcs): Making, supplying and fixing of folding door made of ctg segun/teakchambol solid wood with frosted glass and doccu paint frame made Length VariableX 84\"(H) X16\" including all accessories matching edging with all complete as per design.','S.ft',1850.00,'2026-09-07 12:42:11'),(22,'Wall & Floor','10mm Frameless Tempered Glass Partition','Glass Partition: Supply fitting/fixing frameless glass partition made of 10mm thick glass with all hardware/accessories such as stainless steel handles, protector bit and VVP auto-closer,stopper lock etc. All complete as per design.','S.ft',290.00,'2026-09-07 12:42:11'),(23,'Wall & Floor','Tempered Glass Door 3\'x7\'','Tempered Glass Door: Supply fitting/fixing frameless glass door (size: 3\'-0\"x7\'-0\") made of 10mm thick tempered glass with all hardware/accessories such as stainless steel handles and VVP auto-closer,stopper lock etc. All complete as per design.','nos',11500.00,'2026-09-07 12:42:11'),(24,'Wall & Floor','Tempered Glass Door 2\'-6\"x7\'','Tempered Glass Door: Supply fitting/fixing frameless glass door (size: 2\'-6\"x7\'-0\") made of 10mm thick tempered glass with all hardware/accessories such as stainless steel handles and VVP auto-closer,stopper lock etc. All complete as per design.','nos',11000.00,'2026-09-07 12:42:11'),(25,'Wall & Floor','Wall Partition 12mm Garjan Ply HPL','Wall Partition Work: Making, Supplying, Fitting fixing of wall partition 12mm garjan ply board with garjan wood framing and HPL pasting all complete as per design.','S.ft',800.00,'2026-09-07 12:42:11'),(26,'Wall & Floor','Wall Paneling / Sofa Back Decoration','Wall Decoration (sofa back): Making, supplying and fixing of wall paneling made of 18mm G.Ply(Akij) with matching edging including all accessories all complete with doccu paint as per design.','S.ft',1050.00,'2026-09-07 12:42:11'),(27,'Wall & Floor','PVC CNC Bit Cut Wall Paneling','Wall Paneling(washroom wall): Making, supplying and fixing of master bedroom toilet side wall paneling made of 18/12mm pvc board CNC bit cutting all complete as per design.','S.ft',460.00,'2026-09-07 12:42:11'),(28,'Wall & Floor','Imported Charcoal Louver Paneling','Counter Table paneling work: Making, supplying and fixing of kitchen counter / wall paneling made of imported charcoal louver all complete as per design.','S.ft',750.00,'2026-09-07 12:42:11'),(29,'Wall & Floor','Imported PVC Floor Carpet','PVC Floor Carpet: Supply fitting/ fixing of pvc floor carpet(imported) for lift / kitchen area all complete.','S.ft',165.00,'2026-09-07 12:42:11'),(30,'Wall & Floor','Imported Floor Carpet (Rooms)','Floor Carpet: Supply fitting/ fixing of floor carpet(imported) all room & common space all complete.','S.ft',107.00,'2026-09-07 12:42:11'),(31,'Wall & Floor','35mm Green Grass Carpet','Veranda Floor Grass Carpet: Supply fitting/ fixing of 35mm green grass floor carpet(imported) all complete.','S.ft',130.00,'2026-09-07 12:42:11'),(32,'Wall & Floor','SS Sliding Security Gate','SS Security Gate: Supply fitting/fixing of 6\'-2\"x7\'-4\" sliding ss security gate all complete.','S.ft',1050.00,'2026-09-07 12:42:11'),(33,'Paint','Berger Plastic Paint (ECE Roller Finish)','Plastic paint (roller finish): Plastic paint (Berger/Asian brand) of approved color to on plaster and others surfaces (1coat sealer+2coat putty+2coat ECE) Easy Clean Emulsion finish after necessary sealer, lime putty,cleaning,sand papering the base surfaces as per standard.','S.ft',45.00,'2026-09-07 12:42:11'),(34,'Paint','Berger Luxury BEE Paint (Foam Finish)','BEE paint (foam finish): Luxury paint (Berger brand) of approved color to on plaster surfaces (1coat sealer+3coat putty+1coat sealer+2coat BEE) Breathe easy Emulsion finish after necessary sealer, lime putty,cleaning,sand papering the base surfaces as per standard.','S.ft',70.00,'2026-09-07 12:42:11'),(35,'Paint','Berger Enamel Paint (Window / Grill)','Enamel Paint: All bed rooms window & verandah grill enamel paint (Berger brand) of approved color to on metal surfaces (1coat RFLPR+2coat Thinner T-6+2coat RSE) after necessary cleaning,sand papering the base surfaces as per direction.','S.ft',50.00,'2026-09-07 12:42:11'),(36,'Electrical','LED Office Hang Light 4FT 72w','LED Office Hang Light 72w: Supplying fitting and fixing of complete set of 4FT length LED ceiling Light (Energy+) with all accessories.','nos',2550.00,'2026-09-07 12:42:11'),(37,'Electrical','LED Office Hang Light 8FT 120w','LED Office Hang Light 120w: Supplying fitting and fixing of complete set of 8FT length LED ceiling Light (Energy+) with all accessories.','nos',5150.00,'2026-09-07 12:42:11'),(38,'Electrical','LED Panel Light 12w Round','LED Panel Light 12w: Supplying fitting and fixing of LED panel Light (Energy+) with all accessories etc. all complete as per design.','nos',650.00,'2026-09-07 12:42:11'),(39,'Electrical','LED Conceal Panel Light 2\'x2\' 48w','LED Panel Light 48w: Supplying fitting and fixing of 2\'x2\' LED conceal panel Light (Energy+) with all accessories etc. all complete as per design.','nos',2800.00,'2026-09-07 12:42:11'),(40,'Electrical','LED Exclusive 8-Ring Chandelier','LED Exclusive hanging Light 8 ring: Supplying fitting and fixing of LED exclusive hanging Light (Energy+) with all accessories etc. all complete as per design.','nos',31240.00,'2026-09-07 12:42:11'),(41,'Electrical','Wall Surface Down Spot Light 12w','Wall Surface down Spot Light 12w: Supplying fitting and fixing of LED spot Light (Energy+) 12watt with all accessories etc. all complete as per direction.','nos',1790.00,'2026-09-07 12:42:11'),(42,'Electrical','Heavy Duty 3-Faces Strip Light','Three faces Strip Light: Supply, fitting & fixing imported Heavy duty Energy+ copper strip light with warm color best quality of approved color with all other accessories etc.','mtr.',220.00,'2026-09-07 12:42:11'),(43,'Electrical','Heavy Duty Profile Light with Diffuser','Profile Light: Supply, fitting & fixing imported Heavy duty profile light best quality of approved color with all other accessories etc.','RFT',255.00,'2026-09-07 12:42:11'),(44,'Electrical','BRB BYA Cable 1C x 1.5 rm','Electric cabling work (BRB): 1C X 1.5 rm BYA cable.','coil',4800.00,'2026-09-07 12:42:11'),(45,'Electrical','BRB BYA Cable 1C x 2.5 rm','Electric cabling work (BRB): 1C X 2.5 rm BYA cable.','coil',7659.00,'2026-09-07 12:42:11'),(46,'Electrical','BRB BYA Cable 1C x 4.0 rm','Electric cabling work (BRB): 1C X 4.0 rm BYA cable.','coil',11923.00,'2026-09-07 12:42:11'),(47,'Electrical','MK / ART-DNA 13A/15A Multi Socket','M.K / ART-DNA SOCKET (Deluxe/Premium): 13A/15A, multi socket with gang box.','nos',464.00,'2026-09-07 12:42:11'),(48,'Electrical','MK / ART-DNA Switch 2-Gang / 3-Gang','M.K / ART-DNA SWITCH (Deluxe/Premium): 2-Gang / 3-Gang (1 way) switch.','nos',395.00,'2026-09-07 12:42:11'),(49,'Electrical','AC Power Circuit Breaker','Circuit Breaker: AC Power circuit breaker.','nos',580.00,'2026-09-07 12:42:11'),(50,'Electrical','Full Project Electrical Wiring & Fitting Labor','Electric work wiring and fitting charges for full Project (Single Unit).','Job',42000.00,'2026-09-07 12:42:11'),(53,'Ceiling','sdasdas','dasdasdasdas','S.ft',45454.00,'2026-09-07 14:20:49'),(54,'Ceiling','sdfsdafasdf','sdafasdfsadfasdf','S.ft',54654654.00,'2026-09-07 14:21:23'),(55,'Ceiling','Test Item 1788769572','Test specs','S.ft',550.00,'2026-09-07 14:26:12'),(59,'Thai Glass','aSdsa','sadasdasdsd','S.ft',345.00,'2026-09-07 14:58:14');
/*!40000 ALTER TABLE `app_estimate_catalog` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_estimate_categories`
--

DROP TABLE IF EXISTS `app_estimate_categories`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_estimate_categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_estimate_categories`
--

LOCK TABLES `app_estimate_categories` WRITE;
/*!40000 ALTER TABLE `app_estimate_categories` DISABLE KEYS */;
INSERT INTO `app_estimate_categories` VALUES (1,'Ceiling','2026-09-07 14:18:22'),(2,'Furniture','2026-09-07 14:18:22'),(3,'Wall & Floor','2026-09-07 14:18:22'),(4,'Paint','2026-09-07 14:18:22'),(5,'Electrical','2026-09-07 14:18:22'),(8,'Sanitery','2026-09-07 14:19:33'),(18,'Thai Glass','2026-09-07 14:57:55');
/*!40000 ALTER TABLE `app_estimate_categories` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_estimate_items`
--

DROP TABLE IF EXISTS `app_estimate_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_estimate_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_id` int(11) NOT NULL,
  `section_name` varchar(100) NOT NULL,
  `section_order` int(11) NOT NULL DEFAULT 1,
  `sl_no` int(11) NOT NULL DEFAULT 1,
  `description` text NOT NULL,
  `unit` varchar(20) NOT NULL,
  `quantity` decimal(10,2) NOT NULL DEFAULT 0.00,
  `unit_price` decimal(12,2) NOT NULL DEFAULT 0.00,
  `amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `estimate_id` (`estimate_id`)
) ENGINE=InnoDB AUTO_INCREMENT=248 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_estimate_items`
--

LOCK TABLES `app_estimate_items` WRITE;
/*!40000 ALTER TABLE `app_estimate_items` DISABLE KEYS */;
INSERT INTO `app_estimate_items` VALUES (230,1,'1. Paint Work',1,1,'Plastic paint (roller finish): Berger/Asian brand Easy Clean Emulsion with sealer & lime putty.','S.ft',2000.00,45.00,90000.00,'2026-09-07 19:56:51'),(231,1,'1. Paint Work',1,2,'Luxury BEE Paint (foam finish): Berger Breathe Easy Emulsion luxury finish.','S.ft',766.71,70.00,53670.00,'2026-09-07 19:56:51'),(232,1,'1. Paint Work',1,3,'Drop Beam: Supply fitting/fixing of drop ceiling over 7\'-0\" height plain particle board on garjan wooden frame all complete plastic paint as per design.','S.ft',1.00,540.00,540.00,'2026-09-07 19:56:51'),(233,1,'1. Paint Work',1,4,'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with doccu paint finish.','S.ft',1.00,600.00,600.00,'2026-09-07 19:56:51'),(234,1,'1. Paint Work',1,5,'Drop Beam: Supply fitting/fixing of drop ceiling over 7\'-0\" height plain particle board on garjan wooden frame all complete plastic paint as per design.','S.ft',1.00,540.00,540.00,'2026-09-07 19:56:51'),(235,1,'1. Paint Work',1,6,'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with matt enamel/plastic paint finish as per design.','S.ft',1.00,520.00,520.00,'2026-09-07 19:56:51'),(236,1,'1. Paint Work',1,7,'Gypsum Ceiling: Supply fitting/fixing of gypsum ceiling over 8\'-6\" height 24\"x24\" board on garjan wooden frame all complete.','S.ft',1.00,80.00,80.00,'2026-09-07 19:56:51'),(237,1,'2. Wall & Floor Treatment',2,1,'10mm Frameless Tempered Glass Partition with SS hardware & VVP closer.','S.ft',450.00,290.00,130500.00,'2026-09-07 19:56:51'),(238,1,'2. Wall & Floor Treatment',2,2,'Tempered Glass Door (3\'x7\') 10mm with VVP auto closer.','nos',4.00,11500.00,46000.00,'2026-09-07 19:56:51'),(239,1,'2. Wall & Floor Treatment',2,3,'Imported Floor Carpet all common space.','S.ft',685.42,107.00,73340.00,'2026-09-07 19:56:51'),(240,1,'2. Wall & Floor Treatment',2,4,'Gypsum Ceiling: Supply fitting/fixing of gypsum ceiling over 8\'-6\" height 24\"x24\" board on garjan wooden frame all complete.','S.ft',1.00,80.00,80.00,'2026-09-07 19:56:51'),(241,1,'2. Wall & Floor Treatment',2,5,'Gypsum Ceiling: Supply fitting/fixing of gypsum ceiling over 8\'-6\" height 24\"x24\" board on garjan wooden frame all complete.','S.ft',1.00,80.00,80.00,'2026-09-07 19:56:51'),(242,1,'3. Furniture Work',3,1,'Modern Reception Table made of 18mm HPL board with matching edging.','nos',1.00,44000.00,44000.00,'2026-09-07 19:56:51'),(243,1,'3. Furniture Work',3,2,'Meeting Table 8\'x3\' HPL board with metal leg.','nos',1.00,30500.00,30500.00,'2026-09-07 19:56:51'),(244,1,'3. Furniture Work',3,3,'Workstation 6-Person with 10mm glass divider & keyboard tray.','per.',6.00,8000.00,48000.00,'2026-09-07 19:56:51'),(245,1,'3. Furniture Work',3,4,'Full Height Cabinet with 18mm HPL & Akij G.ply, formica auto pasting.','S.ft',155.49,2150.00,334304.00,'2026-09-07 19:56:51'),(246,13,'Ceiling',1,1,'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with doccu paint finish.','S.ft',1.00,600.00,600.00,'2026-09-07 19:58:23'),(247,13,'Ceiling',1,2,'Plain Particle ceiling: Designed decorative fixed ceiling of plain particle Board-Star/super. Thickness: 12mm on BT.garjan wooden frame all complete with matt enamel/plastic paint finish as per design.','S.ft',1.00,520.00,520.00,'2026-09-07 19:58:23');
/*!40000 ALTER TABLE `app_estimate_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_estimates`
--

DROP TABLE IF EXISTS `app_estimates`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_estimates` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `estimate_no` varchar(50) NOT NULL,
  `client_name` varchar(150) NOT NULL,
  `client_designation` varchar(100) DEFAULT NULL,
  `client_company` varchar(150) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `client_phone` varchar(50) DEFAULT NULL,
  `subject` varchar(255) NOT NULL,
  `project_type` enum('commercial','residential') DEFAULT 'commercial',
  `group_by` enum('category','room') DEFAULT 'category',
  `working_days` varchar(50) DEFAULT '25-30 working days',
  `advance_pct` decimal(5,2) DEFAULT 60.00,
  `running_pct` decimal(5,2) DEFAULT 30.00,
  `final_pct` decimal(5,2) DEFAULT 10.00,
  `total_amount` decimal(14,2) NOT NULL DEFAULT 0.00,
  `discount_amount` decimal(14,2) DEFAULT 0.00,
  `grand_total` decimal(14,2) NOT NULL DEFAULT 0.00,
  `status` enum('draft','sent','approved','converted') DEFAULT 'draft',
  `converted_project_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `prepared_by` varchar(100) DEFAULT 'Md. Rukonuzzaman',
  `prepared_by_designation` varchar(100) DEFAULT 'Interior Designer',
  `approved_by` varchar(100) DEFAULT 'Md. Mustafizur Rahman',
  `approved_by_designation` varchar(100) DEFAULT 'Managing Director',
  `created_by` int(11) NOT NULL DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `estimate_no` (`estimate_no`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_estimates`
--

LOCK TABLES `app_estimates` WRITE;
/*!40000 ALTER TABLE `app_estimates` DISABLE KEYS */;
INSERT INTO `app_estimates` VALUES (1,'EST-2025-001','Managing Director','','Managing Director','Suite-1307, Level-13, Computer City Center (Multiplan Center) New Elephant Road, Dhaka','','Agreement for Commercial Space Interior works (1st floor) At Elephant road Dhaka','commercial','category','25-30 working days',60.00,30.00,10.00,852754.00,0.00,852754.00,'draft',NULL,'','Md. Rukonuzzaman','Interior Designer','Md. Mustafizur Rahman','Managing Director',1,'2025-09-03 10:00:00','2026-09-07 19:56:49'),(13,'EST-2026-010','ফদ্গদসগদফগ','','ফদ্গদসগদফগ','দফগদ্গ','','দ্গদস','commercial','category','25-30 working days',60.00,30.00,10.00,1120.00,0.00,1120.00,'draft',NULL,'','Md. Rukonuzzaman','Interior Designer','Md. Mustafizur Rahman','Managing Director',1,'2026-09-07 19:55:18','2026-09-07 19:58:23');
/*!40000 ALTER TABLE `app_estimates` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_expenses`
--

DROP TABLE IF EXISTS `app_expenses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_expenses` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `category` varchar(50) DEFAULT NULL,
  `description` varchar(255) DEFAULT NULL,
  `vendor` varchar(100) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `paid` decimal(15,2) NOT NULL DEFAULT 0.00,
  `payment_method` enum('Cash','Bank Transfer','Cheque','bKash','Nagad') DEFAULT NULL,
  `expense_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `category` (`category`),
  KEY `expense_date` (`expense_date`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_expenses_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_expenses_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_expenses`
--

LOCK TABLES `app_expenses` WRITE;
/*!40000 ALTER TABLE `app_expenses` DISABLE KEYS */;
INSERT INTO `app_expenses` VALUES (1,14,'Transport','updated','V2',1600.00,1600.00,'bKash','2026-08-30','x',1,1,'2026-08-29 21:05:35','2026-08-29 21:06:25',0),(2,14,'Transport','UI harness expense','Harness Vendor',2200.00,2200.00,'Cash','2026-08-29','created by verify25',1,1,'2026-08-29 21:07:29','2026-08-29 21:13:50',0);
/*!40000 ALTER TABLE `app_expenses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_glass_advances`
--

DROP TABLE IF EXISTS `app_glass_advances`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_glass_advances` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Cheque','bKash','Nagad') NOT NULL,
  `who_paid` varchar(100) DEFAULT NULL,
  `who_received` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_glass_advances_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_glass_advances_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_glass_advances_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_glass_advances`
--

LOCK TABLES `app_glass_advances` WRITE;
/*!40000 ALTER TABLE `app_glass_advances` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_glass_advances` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_items_master`
--

DROP TABLE IF EXISTS `app_items_master`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_items_master` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `category_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `default_unit` varchar(20) DEFAULT NULL,
  `default_rate` decimal(10,2) DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `app_items_master_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_items_master`
--

LOCK TABLES `app_items_master` WRITE;
/*!40000 ALTER TABLE `app_items_master` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_items_master` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_notifications`
--

DROP TABLE IF EXISTS `app_notifications`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_notifications` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `type` enum('purchase','contractor_payment','labor_payment','client_payment','attendance','system') NOT NULL DEFAULT 'system',
  `title` varchar(255) NOT NULL,
  `body` text DEFAULT NULL,
  `project_id` int(11) DEFAULT NULL,
  `record_id` int(11) DEFAULT NULL,
  `record_table` varchar(50) DEFAULT NULL,
  `amount` decimal(15,2) DEFAULT NULL,
  `is_read` tinyint(4) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `app_notifications_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `app_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `app_notifications_ibfk_2` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_notifications`
--

LOCK TABLES `app_notifications` WRITE;
/*!40000 ALTER TABLE `app_notifications` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_notifications` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_printouts`
--

DROP TABLE IF EXISTS `app_printouts`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_printouts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `file_path` varchar(500) NOT NULL,
  `type` enum('bill','purchase_report','payment_report','attendance_report') NOT NULL,
  `file_size` int(11) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_printouts_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_printouts_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_printouts`
--

LOCK TABLES `app_printouts` WRITE;
/*!40000 ALTER TABLE `app_printouts` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_printouts` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_project_contractors`
--

DROP TABLE IF EXISTS `app_project_contractors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_project_contractors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `category_id` (`category_id`),
  CONSTRAINT `app_project_contractors_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_project_contractors_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_project_contractors_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_project_contractors`
--

LOCK TABLES `app_project_contractors` WRITE;
/*!40000 ALTER TABLE `app_project_contractors` DISABLE KEYS */;
INSERT INTO `app_project_contractors` VALUES (8,14,3,NULL,NULL,NULL,'2026-08-28 17:19:42','2026-08-28 17:19:42',0),(9,14,1,NULL,NULL,NULL,'2026-08-28 17:19:48','2026-08-28 17:19:48',0),(10,11,5,NULL,NULL,NULL,'2026-08-28 18:20:01','2026-08-28 18:20:01',0),(11,11,3,NULL,NULL,NULL,'2026-08-28 18:50:29','2026-08-28 18:50:29',0),(12,11,1,NULL,NULL,NULL,'2026-08-28 18:50:49','2026-08-28 18:50:49',0),(13,14,5,NULL,NULL,NULL,'2026-09-06 17:58:08','2026-09-06 17:58:08',0);
/*!40000 ALTER TABLE `app_project_contractors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_project_images`
--

DROP TABLE IF EXISTS `app_project_images`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_project_images` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `image_path` varchar(500) NOT NULL,
  `caption` varchar(255) DEFAULT NULL,
  `is_primary` tinyint(4) DEFAULT 0,
  `sort_order` int(11) DEFAULT 0,
  `created_at` datetime DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  CONSTRAINT `app_project_images_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_project_images`
--

LOCK TABLES `app_project_images` WRITE;
/*!40000 ALTER TABLE `app_project_images` DISABLE KEYS */;
INSERT INTO `app_project_images` VALUES (5,11,'uploads/projects/proj_11_1787913890_6604c4d7.jpg',NULL,1,0,'2026-08-28 16:44:50'),(12,14,'uploads/projects/proj_14_1787917173_91f04709.jpg',NULL,1,0,'2026-08-28 17:39:33');
/*!40000 ALTER TABLE `app_project_images` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_projects`
--

DROP TABLE IF EXISTS `app_projects`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_projects` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `client_name` varchar(100) NOT NULL,
  `client_phone` varchar(20) NOT NULL,
  `client_email` varchar(100) DEFAULT NULL,
  `client_address` text DEFAULT NULL,
  `project_type` enum('Residential','Commercial','Office','Shop','Other') NOT NULL,
  `status` enum('Ongoing','Completed','On Hold') NOT NULL DEFAULT 'Ongoing',
  `estimated_budget` decimal(15,2) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `project_image` varchar(500) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_projects_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_projects`
--

LOCK TABLES `app_projects` WRITE;
/*!40000 ALTER TABLE `app_projects` DISABLE KEYS */;
INSERT INTO `app_projects` VALUES (11,'Shiddeshsori','','Client Test','','','','Residential','Ongoing',0.00,'2026-08-28','2026-09-28','uploads/projects/proj_11_1787913890_6604c4d7.jpg','',0,1,'2026-08-28 16:40:03','2026-09-06 18:05:50',0),(14,'Gendaria','dfds','dsfdgd','','fdgf','','Residential','Ongoing',0.00,'2026-08-28','2026-09-28','uploads/projects/proj_14_1787917173_91f04709.jpg','',0,1,'2026-08-28 17:09:35','2026-09-06 19:18:18',0);
/*!40000 ALTER TABLE `app_projects` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_purchases`
--

DROP TABLE IF EXISTS `app_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `category_id` int(11) NOT NULL,
  `item_name` varchar(100) NOT NULL,
  `thickness_size` varchar(50) DEFAULT NULL,
  `color_finish` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `purchase_date` date NOT NULL,
  `who_purchased` varchar(100) DEFAULT NULL,
  `receipt_photo_path` text DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_purchases_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_purchases_ibfk_2` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`),
  CONSTRAINT `app_purchases_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_purchases`
--

LOCK TABLES `app_purchases` WRITE;
/*!40000 ALTER TABLE `app_purchases` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_schedules`
--

DROP TABLE IF EXISTS `app_schedules`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_schedules` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) DEFAULT NULL,
  `schedule_date` date NOT NULL,
  `category` enum('Board','Paint','Glass','Electric','Payment') DEFAULT NULL,
  `description` text NOT NULL,
  `is_done` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_schedules_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_schedules_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_schedules`
--

LOCK TABLES `app_schedules` WRITE;
/*!40000 ALTER TABLE `app_schedules` DISABLE KEYS */;
INSERT INTO `app_schedules` VALUES (1,NULL,'2026-08-26','Board','dsfdsfdsdfd\r\ndfdslfdsl',1,1,'2026-08-26 12:31:11','2026-08-26 12:31:59',0),(2,NULL,'2026-05-27','Board','dsfdsfdsdfd\r\ndfd\r\ndfsf slfdsl',0,1,'2026-08-26 12:31:53','2026-08-26 12:31:53',0),(3,NULL,'2026-09-27','Board','15pc particle',0,1,'2026-08-27 00:35:19','2026-08-27 00:35:19',0),(4,NULL,'2026-09-28','Board','15pc particle',0,1,'2026-08-27 00:35:43','2026-08-27 00:35:43',0),(5,NULL,'2026-08-05',NULL,'board kinbo pvc',0,1,'2026-08-27 02:25:58','2026-08-27 02:25:58',0),(9,NULL,'2026-08-30',NULL,'vuisuiit',0,1,'2026-08-28 16:55:06','2026-08-28 16:55:06',0);
/*!40000 ALTER TABLE `app_schedules` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_settings`
--

DROP TABLE IF EXISTS `app_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_settings`
--

LOCK TABLES `app_settings` WRITE;
/*!40000 ALTER TABLE `app_settings` DISABLE KEYS */;
INSERT INTO `app_settings` VALUES (1,'company_name','Lily Interiors - লিলি ইন্টেরিয়র\'স','2026-08-29 18:01:14'),(2,'company_address','36 Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205','2026-08-19 18:44:34'),(3,'company_phone','+88 02 44612456, +88 01734182694','2026-08-19 18:44:34'),(4,'company_email','','2026-08-18 12:01:30'),(5,'pdf_margin_top','30','2026-08-18 12:01:30'),(6,'pdf_margin_bottom','30','2026-08-18 12:01:30'),(7,'session_timeout','7200','2026-08-18 12:01:30'),(8,'default_language','en','2026-08-19 17:14:28'),(9,'default_theme','light','2026-08-18 12:01:30'),(13,'currency_symbol','Tk.','2026-08-24 12:18:18'),(25,'company_whatsapp','','2026-08-19 20:18:55'),(26,'company_facebook','','2026-08-19 20:18:55'),(27,'company_website','','2026-08-19 20:18:55'),(29,'app_version','1.0.0','2026-08-19 20:18:55'),(30,'text_language','both','2026-08-19 20:18:55'),(33,'pdf_margin_left','15','2026-08-24 12:18:04'),(34,'pdf_margin_right','15','2026-08-24 12:18:04'),(35,'print_language','en','2026-08-24 12:18:04');
/*!40000 ALTER TABLE `app_settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_supply_purchases`
--

DROP TABLE IF EXISTS `app_supply_purchases`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_supply_purchases` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `item_name` varchar(100) NOT NULL,
  `supply_category` varchar(50) DEFAULT NULL,
  `category_id` int(11) DEFAULT NULL,
  `board_type` varchar(50) DEFAULT NULL,
  `board_thickness` varchar(50) DEFAULT NULL,
  `board_size` varchar(50) DEFAULT NULL,
  `color_finish` varchar(50) DEFAULT NULL,
  `size` varchar(50) DEFAULT NULL,
  `quantity` decimal(10,2) NOT NULL,
  `unit` varchar(20) NOT NULL,
  `rate` decimal(10,2) NOT NULL,
  `total` decimal(15,2) NOT NULL,
  `supplier` varchar(100) DEFAULT NULL,
  `purchase_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `purchased_by` varchar(100) DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `created_by` (`created_by`),
  KEY `fk_supply_contractor` (`contractor_id`),
  KEY `fk_sp_category` (`category_id`),
  CONSTRAINT `app_supply_purchases_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_supply_purchases_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_sp_category` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`) ON DELETE SET NULL,
  CONSTRAINT `fk_supply_contractor` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=27 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_supply_purchases`
--

LOCK TABLES `app_supply_purchases` WRITE;
/*!40000 ALTER TABLE `app_supply_purchases` DISABLE KEYS */;
INSERT INTO `app_supply_purchases` VALUES (12,14,NULL,'Partex','Board & Wood',NULL,NULL,NULL,NULL,NULL,NULL,5.00,'pcs',1500.00,7500.00,NULL,'2026-08-28',NULL,'admin',0,1,'2026-08-28 17:36:54','2026-08-28 17:36:54',0),(13,14,NULL,'Melamine','Board & Wood',NULL,NULL,NULL,NULL,NULL,NULL,5.00,'pcs',2400.00,12000.00,NULL,'2026-08-28',NULL,'admin',0,1,'2026-08-28 17:37:22','2026-08-28 17:37:22',0),(14,14,NULL,'UPvc pipe','Electrical & Sanitary',NULL,NULL,NULL,NULL,NULL,NULL,10.00,'ft',40.00,400.00,NULL,'2026-08-29',NULL,'admin',0,1,'2026-08-29 18:29:23','2026-08-29 18:29:23',0),(15,14,NULL,'Malamine','Board & Wood',NULL,NULL,NULL,NULL,NULL,NULL,50.00,'pcs',2250.00,112500.00,'Super','2026-05-05',NULL,'admin',0,1,'2026-09-06 13:38:23','2026-09-06 13:38:23',0),(16,14,NULL,'Anamel -Assian','Paint',NULL,NULL,NULL,NULL,NULL,NULL,1.00,'GLN',1850.00,1850.00,'Lokkh Hardware','2026-05-05',NULL,'admin',0,1,'2026-09-06 13:39:04','2026-09-06 13:39:04',0),(17,14,NULL,'BRB Cable','Electrical & Sanitary',NULL,NULL,NULL,NULL,NULL,NULL,1.00,'Coil',4200.00,4200.00,NULL,'2026-05-02',NULL,'Rokon',0,3,'2026-09-06 14:25:58','2026-09-06 14:25:58',0),(18,11,NULL,'Board','Electrical & Sanitary',NULL,NULL,NULL,NULL,NULL,NULL,20.00,'pcs',1500.00,30000.00,NULL,'2026-09-06',NULL,'Rokon',0,3,'2026-09-06 14:26:26','2026-09-06 14:26:26',0),(19,11,NULL,'Glass Self','Thai & Glass',NULL,NULL,NULL,NULL,NULL,NULL,5.00,'sft',500.00,2500.00,NULL,'2026-09-01',NULL,'Rokon',0,3,'2026-09-06 14:27:11','2026-09-06 14:27:11',0),(20,14,NULL,'fdsfds','Electrical & Sanitary',NULL,NULL,NULL,NULL,NULL,NULL,23.00,'pcs',3243.00,74589.00,NULL,'2026-09-06',NULL,'Rokon',0,3,'2026-09-06 14:27:44','2026-09-06 14:27:44',0),(21,11,NULL,'badd','Electrical & Sanitary',NULL,NULL,NULL,NULL,NULL,NULL,3.00,'pcs',4354.00,13062.00,NULL,'2026-09-06',NULL,'Rukon',0,8,'2026-09-06 16:13:24','2026-09-06 16:13:24',0),(22,11,NULL,'gfhfghf','Paint',NULL,NULL,NULL,NULL,NULL,NULL,76.00,'pcs',76.00,5776.00,NULL,'2026-09-06',NULL,'Rukon',0,8,'2026-09-06 16:13:33','2026-09-06 16:13:33',0),(23,14,NULL,'partex','Board & Wood',NULL,'Malamine','10mm',NULL,NULL,NULL,45.00,'pcs',1200.00,54000.00,NULL,'2026-09-06',NULL,'Rukon',0,8,'2026-09-06 19:00:57','2026-09-06 19:00:57',0),(24,11,NULL,'PVC','Board & Wood',NULL,'PVC','12mm',NULL,NULL,NULL,12.00,'pcs',1500.00,18000.00,NULL,'2026-09-06',NULL,'Rukon',0,8,'2026-09-06 19:08:40','2026-09-06 19:08:40',0),(25,11,NULL,'MDF','Board & Wood',NULL,'MDF','10mm',NULL,NULL,NULL,29.00,'pcs',3435.00,99615.00,NULL,'2026-09-06',NULL,'Rukon',0,8,'2026-09-06 19:09:03','2026-09-06 19:09:03',0);
/*!40000 ALTER TABLE `app_supply_purchases` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_thai_glass_bills`
--

DROP TABLE IF EXISTS `app_thai_glass_bills`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_thai_glass_bills` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `contractor_id` int(11) NOT NULL,
  `bill_rows` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`bill_rows`)),
  `grand_total` decimal(15,2) NOT NULL,
  `total_paid` decimal(15,2) NOT NULL,
  `balance_due` decimal(15,2) NOT NULL,
  `bill_date` date NOT NULL,
  `bill_language` enum('bn','en') NOT NULL DEFAULT 'bn',
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  `is_deleted` tinyint(1) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `contractor_id` (`contractor_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_thai_glass_bills_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_thai_glass_bills_ibfk_2` FOREIGN KEY (`contractor_id`) REFERENCES `app_contractors` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_thai_glass_bills_ibfk_3` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_thai_glass_bills`
--

LOCK TABLES `app_thai_glass_bills` WRITE;
/*!40000 ALTER TABLE `app_thai_glass_bills` DISABLE KEYS */;
/*!40000 ALTER TABLE `app_thai_glass_bills` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_users`
--

DROP TABLE IF EXISTS `app_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('owner','manager') NOT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `assigned_projects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_projects`)),
  `is_active` tinyint(4) DEFAULT 1,
  `is_deleted` tinyint(1) NOT NULL DEFAULT 0,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_users`
--

LOCK TABLES `app_users` WRITE;
/*!40000 ALTER TABLE `app_users` DISABLE KEYS */;
INSERT INTO `app_users` VALUES (1,'admin','admin@lilyinteriorsbd.com','$2y$10$DGneiL/4OXyXPCFvqJSwAePA9LvhpCHamd9Cx/LW5FoW2NR71.NeW','owner',NULL,NULL,1,0,'2026-08-29 14:37:37','2026-08-18 12:01:30','2026-09-06 16:35:07',0),(2,'lilyweb','lilyweb@lilyinteriorsbd.com','$2y$10$DGneiL/4OXyXPCFvqJSwAePA9LvhpCHamd9Cx/LW5FoW2NR71.NeW','owner',NULL,NULL,1,0,NULL,'2026-09-04 15:45:12','2026-09-06 16:35:07',0),(3,'Rokon','Rokon@lilyinteriorsbd.com','$2y$10$DGneiL/4OXyXPCFvqJSwAePA9LvhpCHamd9Cx/LW5FoW2NR71.NeW','manager','uploads/users/user_1788687411_0a09367b.jpg',NULL,1,0,NULL,'2026-09-06 14:21:07','2026-09-06 16:35:07',0),(8,'Rukon','Rukon@lilyinteriorsbd.com','$2y$10$DGneiL/4OXyXPCFvqJSwAePA9LvhpCHamd9Cx/LW5FoW2NR71.NeW','manager','uploads/users/user_1788687411_0a09367b.jpg',NULL,1,0,NULL,'2026-09-06 15:36:51','2026-09-06 16:35:07',0);
/*!40000 ALTER TABLE `app_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_worker_payments`
--

DROP TABLE IF EXISTS `app_worker_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_worker_payments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `project_id` int(11) NOT NULL,
  `worker_id` int(11) NOT NULL,
  `category_id` int(11) DEFAULT NULL,
  `amount` decimal(15,2) NOT NULL,
  `payment_date` date NOT NULL,
  `payment_method` enum('Cash','Bank Transfer','Cheque','bKash','Nagad') NOT NULL,
  `who_paid` varchar(100) DEFAULT NULL,
  `who_received` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_deleted` tinyint(4) DEFAULT 0,
  `created_by` int(11) DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `project_id` (`project_id`),
  KEY `worker_id` (`worker_id`),
  KEY `category_id` (`category_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `app_worker_payments_ibfk_1` FOREIGN KEY (`project_id`) REFERENCES `app_projects` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_worker_payments_ibfk_2` FOREIGN KEY (`worker_id`) REFERENCES `app_workers` (`id`) ON DELETE CASCADE,
  CONSTRAINT `app_worker_payments_ibfk_3` FOREIGN KEY (`category_id`) REFERENCES `app_categories` (`id`),
  CONSTRAINT `app_worker_payments_ibfk_4` FOREIGN KEY (`created_by`) REFERENCES `app_users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=12 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_worker_payments`
--

LOCK TABLES `app_worker_payments` WRITE;
/*!40000 ALTER TABLE `app_worker_payments` DISABLE KEYS */;
INSERT INTO `app_worker_payments` VALUES (6,14,3,NULL,500.00,'2026-05-03','Cash','admin','','',0,1,'2026-08-28 17:09:35','2026-08-28 17:09:35',0),(7,14,5,NULL,300.00,'2099-01-06','Cash','admin',NULL,NULL,1,1,'2026-08-28 18:29:47','2026-08-28 18:31:20',0),(8,11,8,NULL,3500.00,'2026-08-28','Cash','admin','','',0,1,'2026-08-28 19:25:49','2026-08-28 19:25:49',0),(9,11,4,NULL,500.00,'2026-08-14','Cash','admin','','',0,1,'2026-08-28 19:35:25','2026-08-28 19:35:25',0),(10,14,4,NULL,500.00,'2026-09-04','Cash','Rkn','','',0,1,'2026-09-04 19:48:42','2026-09-04 19:48:42',0),(11,14,4,NULL,500.00,'2026-09-06','Cash','Rokon','','',0,3,'2026-09-06 14:30:25','2026-09-06 14:30:25',0);
/*!40000 ALTER TABLE `app_worker_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `app_workers`
--

DROP TABLE IF EXISTS `app_workers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8 */;
CREATE TABLE `app_workers` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `trade` varchar(50) DEFAULT NULL,
  `contractor_id` int(11) DEFAULT NULL,
  `default_daily_rate` decimal(10,2) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `is_active` tinyint(4) DEFAULT 1,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `idx_worker_contractor` (`contractor_id`)
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `app_workers`
--

LOCK TABLES `app_workers` WRITE;
/*!40000 ALTER TABLE `app_workers` DISABLE KEYS */;
INSERT INTO `app_workers` VALUES (1,'Sihab','06655151151','','paint',NULL,800.00,'',1,'2026-08-19 17:24:34','2026-08-26 12:30:04',0),(2,'Sazzad','01317388443','','Painter',3,1100.00,'',1,'2026-08-26 12:38:34','2026-08-28 18:41:46',0),(3,'Worker',NULL,NULL,'',NULL,NULL,NULL,0,'2026-08-28 17:09:35','2026-08-28 18:41:55',0),(4,'Afsar','01818628034','Uttar Badda, Origin: FENI','Electrician',5,1200.00,'',1,'2026-08-28 17:58:09','2026-08-28 18:40:04',0),(5,'Babu','','','Electrician',5,1200.00,'',0,'2026-08-28 17:58:22','2026-08-28 18:38:16',0),(6,'zzCrewTest','','','Electrician',5,500.00,'',0,'2026-08-28 18:31:48','2026-08-28 18:31:57',0),(7,'Babu','','','Electrician',5,1000.00,'',0,'2026-08-28 18:37:59','2026-08-28 18:38:21',0),(8,'Babu','01737225108','Badda','Electrician',5,1000.00,'',1,'2026-08-28 18:39:12','2026-08-28 18:39:12',0),(9,'Arif','01737225108','','Painter',3,1300.00,'',1,'2026-09-06 18:32:51','2026-09-06 18:32:51',0);
/*!40000 ALTER TABLE `app_workers` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-07 20:42:45
