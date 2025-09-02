-- MySQL dump 10.13  Distrib 8.0.35, for Win64 (x86_64)
--
-- Host: localhost    Database: project_pkm_v2
-- ------------------------------------------------------
-- Server version	8.0.35

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `attendance_records`
--

DROP TABLE IF EXISTS `attendance_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `attendance_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eschool_id` bigint unsigned NOT NULL,
  `member_id` bigint unsigned NOT NULL,
  `recorder_id` bigint unsigned NOT NULL,
  `date` timestamp NOT NULL,
  `is_present` tinyint(1) NOT NULL DEFAULT '0',
  `notes` text COLLATE utf8mb4_unicode_ci,
  `proof_document_path` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_document_name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_document_type` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `proof_document_size` int DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `attendance_records_eschool_id_foreign` (`eschool_id`),
  KEY `attendance_records_member_id_foreign` (`member_id`),
  KEY `attendance_records_recorder_id_foreign` (`recorder_id`),
  CONSTRAINT `attendance_records_eschool_id_foreign` FOREIGN KEY (`eschool_id`) REFERENCES `eschools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE,
  CONSTRAINT `attendance_records_recorder_id_foreign` FOREIGN KEY (`recorder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=91 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `attendance_records`
--

LOCK TABLES `attendance_records` WRITE;
/*!40000 ALTER TABLE `attendance_records` DISABLE KEYS */;
INSERT INTO `attendance_records` VALUES (1,1,1,3,'2025-08-26 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,1,4,3,'2025-08-26 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,1,5,3,'2025-08-26 08:30:00',1,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,1,6,3,'2025-08-26 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,2,2,4,'2025-08-26 09:00:00',1,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,2,7,4,'2025-08-26 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(7,2,8,4,'2025-08-26 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(8,2,1,4,'2025-08-26 09:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(9,3,3,5,'2025-08-26 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(10,3,9,5,'2025-08-26 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(11,3,4,5,'2025-08-26 07:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(12,4,10,15,'2025-08-26 08:00:00',0,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(13,4,12,15,'2025-08-26 08:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(14,4,13,15,'2025-08-26 08:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(15,5,11,16,'2025-08-26 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(16,5,12,16,'2025-08-26 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(17,5,10,16,'2025-08-26 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(18,1,1,3,'2025-08-27 08:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(19,1,4,3,'2025-08-27 08:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(20,1,5,3,'2025-08-27 08:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(21,1,6,3,'2025-08-27 08:30:00',1,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(22,2,2,4,'2025-08-27 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(23,2,7,4,'2025-08-27 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(24,2,8,4,'2025-08-27 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(25,2,1,4,'2025-08-27 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(26,3,3,5,'2025-08-27 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(27,3,9,5,'2025-08-27 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(28,3,4,5,'2025-08-27 07:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(29,4,10,15,'2025-08-27 08:00:00',0,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(30,4,12,15,'2025-08-27 08:00:00',1,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(31,4,13,15,'2025-08-27 08:00:00',0,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(32,5,11,16,'2025-08-27 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(33,5,12,16,'2025-08-27 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(34,5,10,16,'2025-08-27 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(35,1,1,3,'2025-08-28 08:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(36,1,4,3,'2025-08-28 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(37,1,5,3,'2025-08-28 08:30:00',1,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(38,1,6,3,'2025-08-28 08:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(39,2,2,4,'2025-08-28 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(40,2,7,4,'2025-08-28 09:00:00',1,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(41,2,8,4,'2025-08-28 09:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(42,2,1,4,'2025-08-28 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(43,3,3,5,'2025-08-28 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(44,3,9,5,'2025-08-28 07:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(45,3,4,5,'2025-08-28 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(46,4,10,15,'2025-08-28 08:00:00',1,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(47,4,12,15,'2025-08-28 08:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(48,4,13,15,'2025-08-28 08:00:00',0,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(49,5,11,16,'2025-08-28 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(50,5,12,16,'2025-08-28 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(51,5,10,16,'2025-08-28 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(52,1,1,3,'2025-08-29 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(53,1,4,3,'2025-08-29 08:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(54,1,5,3,'2025-08-29 08:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(55,1,6,3,'2025-08-29 08:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(56,2,2,4,'2025-08-29 09:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(57,2,7,4,'2025-08-29 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(58,2,8,4,'2025-08-29 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(59,2,1,4,'2025-08-29 09:00:00',1,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(60,3,3,5,'2025-08-29 07:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(61,3,9,5,'2025-08-29 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(62,3,4,5,'2025-08-29 07:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(63,4,10,15,'2025-08-29 08:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(64,4,12,15,'2025-08-29 08:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(65,4,13,15,'2025-08-29 08:00:00',1,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(66,5,11,16,'2025-08-29 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(67,5,12,16,'2025-08-29 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(68,5,10,16,'2025-08-29 09:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(69,1,1,3,'2025-08-30 08:30:00',0,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(70,1,4,3,'2025-08-30 08:30:00',1,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(71,1,5,3,'2025-08-30 08:30:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(72,1,6,3,'2025-08-30 08:30:00',1,'Sakit',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(73,2,2,4,'2025-08-30 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(74,2,7,4,'2025-08-30 09:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(75,2,8,4,'2025-08-30 09:00:00',0,'Izin',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(76,2,1,4,'2025-08-30 09:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(77,3,3,5,'2025-08-30 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(78,3,9,5,'2025-08-30 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(79,3,4,5,'2025-08-30 07:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(80,4,10,15,'2025-08-30 08:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(81,4,12,15,'2025-08-30 08:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(82,4,13,15,'2025-08-30 08:00:00',1,'Terlambat',NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(83,5,11,16,'2025-08-30 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(84,5,12,16,'2025-08-30 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(85,5,10,16,'2025-08-30 09:30:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(86,1,1,3,'2025-08-30 17:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-08-31 00:41:05','2025-08-31 00:41:05'),(87,1,4,3,'2025-08-30 17:00:00',0,NULL,NULL,NULL,NULL,NULL,'2025-08-31 00:41:05','2025-08-31 00:41:05'),(88,1,1,3,'2025-08-31 17:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-09-01 04:57:36','2025-09-01 05:03:08'),(89,1,16,3,'2025-09-01 17:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-09-01 21:10:04','2025-09-01 21:10:04'),(90,1,1,3,'2025-09-01 17:00:00',1,NULL,NULL,NULL,NULL,NULL,'2025-09-01 21:11:57','2025-09-01 21:11:57');
/*!40000 ALTER TABLE `attendance_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache`
--

DROP TABLE IF EXISTS `cache`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `value` mediumtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache`
--

LOCK TABLES `cache` WRITE;
/*!40000 ALTER TABLE `cache` DISABLE KEYS */;
INSERT INTO `cache` VALUES ('laravel-cache-lLcBoTL0w2Bdszcw','s:7:\"forever\";',2072150233);
/*!40000 ALTER TABLE `cache` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `cache_locks`
--

DROP TABLE IF EXISTS `cache_locks`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cache_locks` (
  `key` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `owner` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `expiration` int NOT NULL,
  PRIMARY KEY (`key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cache_locks`
--

LOCK TABLES `cache_locks` WRITE;
/*!40000 ALTER TABLE `cache_locks` DISABLE KEYS */;
/*!40000 ALTER TABLE `cache_locks` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eschool_member`
--

DROP TABLE IF EXISTS `eschool_member`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eschool_member` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eschool_id` bigint unsigned NOT NULL,
  `member_id` bigint unsigned NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `eschool_member_eschool_id_member_id_unique` (`eschool_id`,`member_id`),
  KEY `eschool_member_member_id_foreign` (`member_id`),
  CONSTRAINT `eschool_member_eschool_id_foreign` FOREIGN KEY (`eschool_id`) REFERENCES `eschools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `eschool_member_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=21 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eschool_member`
--

LOCK TABLES `eschool_member` WRITE;
/*!40000 ALTER TABLE `eschool_member` DISABLE KEYS */;
INSERT INTO `eschool_member` VALUES (1,1,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,1,4,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,1,5,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,1,6,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,2,2,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,2,7,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(7,2,8,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(8,2,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(9,3,3,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(10,3,9,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(11,3,4,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(12,4,10,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(13,4,12,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(14,4,13,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(15,5,11,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(16,5,12,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(17,5,10,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(18,1,16,NULL,NULL),(19,1,17,NULL,NULL),(20,1,18,NULL,NULL);
/*!40000 ALTER TABLE `eschool_member` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `eschools`
--

DROP TABLE IF EXISTS `eschools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `eschools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned NOT NULL,
  `coordinator_id` bigint unsigned NOT NULL,
  `treasurer_id` bigint unsigned NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci,
  `monthly_kas_amount` int NOT NULL DEFAULT '20000',
  `schedule_days` json DEFAULT NULL,
  `total_schedule_days` int NOT NULL DEFAULT '3',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `eschools_school_id_foreign` (`school_id`),
  KEY `eschools_coordinator_id_foreign` (`coordinator_id`),
  KEY `eschools_treasurer_id_foreign` (`treasurer_id`),
  CONSTRAINT `eschools_coordinator_id_foreign` FOREIGN KEY (`coordinator_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `eschools_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `eschools_treasurer_id_foreign` FOREIGN KEY (`treasurer_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `eschools`
--

LOCK TABLES `eschools` WRITE;
/*!40000 ALTER TABLE `eschools` DISABLE KEYS */;
INSERT INTO `eschools` VALUES (1,1,3,6,'Basket','Ekstrakurikuler Bola Basket untuk mengembangkan kemampuan olahraga dan kerjasama tim',25000,'\"[\\\"Selasa\\\",\\\"Kamis\\\",\\\"Sabtu\\\"]\"',3,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,1,4,7,'Voli','Ekstrakurikuler Bola Voli untuk meningkatkan koordinasi dan sportivitas',20000,'\"[\\\"Senin\\\",\\\"Rabu\\\",\\\"Jumat\\\"]\"',3,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,1,5,8,'Lukis','Ekstrakurikuler Seni Lukis untuk mengembangkan kreativitas dan bakat seni',30000,'\"[\\\"Kamis\\\",\\\"Sabtu\\\"]\"',2,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,2,15,17,'Matematika','Ekstrakurikuler Olimpiade Matematika untuk mengasah kemampuan logika dan pemecahan masalah',15000,'\"[\\\"Selasa\\\",\\\"Kamis\\\"]\"',2,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,2,16,18,'Teater','Ekstrakurikuler Teater untuk mengembangkan kepercayaan diri dan kemampuan berakting',35000,'\"[\\\"Rabu\\\",\\\"Jumat\\\",\\\"Sabtu\\\"]\"',3,1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,1,27,28,'Brian Puckett','In modi voluptate eu',5,'\"[\\\"Selasa\\\",\\\"Kamis\\\",\\\"Sabtu\\\"]\"',3,1,'2025-08-31 04:20:54','2025-08-31 04:20:54');
/*!40000 ALTER TABLE `eschools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `failed_jobs`
--

DROP TABLE IF EXISTS `failed_jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `failed_jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `uuid` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `connection` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `queue` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `exception` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `failed_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `failed_jobs_uuid_unique` (`uuid`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `failed_jobs`
--

LOCK TABLES `failed_jobs` WRITE;
/*!40000 ALTER TABLE `failed_jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `failed_jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `job_batches`
--

DROP TABLE IF EXISTS `job_batches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `job_batches` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `total_jobs` int NOT NULL,
  `pending_jobs` int NOT NULL,
  `failed_jobs` int NOT NULL,
  `failed_job_ids` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `options` mediumtext COLLATE utf8mb4_unicode_ci,
  `cancelled_at` int DEFAULT NULL,
  `created_at` int NOT NULL,
  `finished_at` int DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `job_batches`
--

LOCK TABLES `job_batches` WRITE;
/*!40000 ALTER TABLE `job_batches` DISABLE KEYS */;
/*!40000 ALTER TABLE `job_batches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `jobs`
--

DROP TABLE IF EXISTS `jobs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `jobs` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `queue` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `attempts` tinyint unsigned NOT NULL,
  `reserved_at` int unsigned DEFAULT NULL,
  `available_at` int unsigned NOT NULL,
  `created_at` int unsigned NOT NULL,
  PRIMARY KEY (`id`),
  KEY `jobs_queue_index` (`queue`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `jobs`
--

LOCK TABLES `jobs` WRITE;
/*!40000 ALTER TABLE `jobs` DISABLE KEYS */;
/*!40000 ALTER TABLE `jobs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_payments`
--

DROP TABLE IF EXISTS `kas_payments`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_payments` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `member_id` bigint unsigned NOT NULL,
  `kas_record_id` bigint unsigned NOT NULL,
  `amount` int NOT NULL,
  `month` int NOT NULL,
  `year` int NOT NULL,
  `is_paid` tinyint(1) NOT NULL DEFAULT '0',
  `paid_date` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_payments_member_id_foreign` (`member_id`),
  KEY `kas_payments_kas_record_id_foreign` (`kas_record_id`),
  CONSTRAINT `kas_payments_kas_record_id_foreign` FOREIGN KEY (`kas_record_id`) REFERENCES `kas_records` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_payments_member_id_foreign` FOREIGN KEY (`member_id`) REFERENCES `members` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=22 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_payments`
--

LOCK TABLES `kas_payments` WRITE;
/*!40000 ALTER TABLE `kas_payments` DISABLE KEYS */;
INSERT INTO `kas_payments` VALUES (1,1,1,25000,8,2025,1,'2025-08-01 03:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,4,1,25000,8,2025,1,'2025-08-02 04:30:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,5,1,25000,8,2025,1,'2025-08-03 02:45:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,6,1,25000,8,2025,0,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,2,4,20000,8,2025,1,'2025-08-01 04:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,7,4,20000,8,2025,1,'2025-08-04 07:15:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(7,8,4,20000,8,2025,1,'2025-08-05 09:30:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(8,1,4,20000,8,2025,0,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(9,3,6,30000,8,2025,1,'2025-08-01 05:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(10,9,6,30000,8,2025,1,'2025-08-06 03:45:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(11,4,6,30000,8,2025,0,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(12,10,7,15000,8,2025,1,'2025-08-01 06:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(13,12,7,15000,8,2025,1,'2025-08-08 08:20:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(14,13,7,15000,8,2025,0,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(15,11,9,35000,8,2025,1,'2025-08-01 07:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(16,12,9,35000,8,2025,1,'2025-08-09 06:10:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(17,10,9,35000,8,2025,0,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(18,1,11,10000,9,2025,1,'2025-08-31 17:00:00','2025-08-30 05:35:51','2025-08-30 05:35:51'),(19,4,11,10000,9,2025,1,'2025-08-31 17:00:00','2025-08-30 05:35:51','2025-08-30 05:35:51'),(20,1,12,25000,11,2025,1,'2025-09-01 17:00:00','2025-09-01 21:43:40','2025-09-01 21:43:40'),(21,5,12,10000,11,2025,1,'2025-09-01 17:00:00','2025-09-01 21:43:40','2025-09-01 21:43:40');
/*!40000 ALTER TABLE `kas_payments` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `kas_records`
--

DROP TABLE IF EXISTS `kas_records`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `kas_records` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `eschool_id` bigint unsigned NOT NULL,
  `recorder_id` bigint unsigned NOT NULL,
  `type` enum('income','expense') COLLATE utf8mb4_unicode_ci NOT NULL,
  `amount` int NOT NULL,
  `description` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `category` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date` timestamp NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `kas_records_eschool_id_foreign` (`eschool_id`),
  KEY `kas_records_recorder_id_foreign` (`recorder_id`),
  CONSTRAINT `kas_records_eschool_id_foreign` FOREIGN KEY (`eschool_id`) REFERENCES `eschools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `kas_records_recorder_id_foreign` FOREIGN KEY (`recorder_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=14 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `kas_records`
--

LOCK TABLES `kas_records` WRITE;
/*!40000 ALTER TABLE `kas_records` DISABLE KEYS */;
INSERT INTO `kas_records` VALUES (1,1,6,'income',100000,'Kas bulanan anggota bulan Agustus','Kas Bulanan','2025-08-01 03:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,1,6,'expense',50000,'Pembelian bola basket baru','Peralatan','2025-08-05 07:30:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,1,3,'expense',25000,'Biaya transportasi ke pertandingan','Transportasi','2025-08-10 02:15:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,2,7,'income',80000,'Kas bulanan anggota bulan Agustus','Kas Bulanan','2025-08-01 04:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,2,7,'expense',30000,'Pembelian net voli','Peralatan','2025-08-07 09:20:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,3,8,'expense',45000,'Pembelian cat dan kuas lukis','Peralatan','2025-08-08 06:45:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(7,4,17,'income',45000,'Kas bulanan anggota bulan Agustus','Kas Bulanan','2025-08-01 06:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(8,4,17,'expense',20000,'Pembelian buku latihan soal olimpiade','Buku dan Materi','2025-08-12 08:30:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(9,5,18,'income',105000,'Kas bulanan anggota bulan Agustus','Kas Bulanan','2025-08-01 07:00:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(10,5,18,'expense',60000,'Pembelian kostum untuk pementasan','Kostum dan Properti','2025-08-15 03:20:00','2025-08-30 01:27:19','2025-08-30 01:27:19'),(11,1,3,'income',20000,'Pembayaran kas bulan september',NULL,'2025-08-31 17:00:00','2025-08-30 05:35:51','2025-08-30 05:35:51'),(12,1,6,'income',35000,'pembayaran kas dibulan november tapi bayar di september',NULL,'2025-09-01 17:00:00','2025-09-01 21:43:40','2025-09-01 21:43:40'),(13,1,6,'expense',80000,'beli cat','equipment','2025-09-01 17:00:00','2025-09-01 21:45:20','2025-09-01 21:45:20');
/*!40000 ALTER TABLE `kas_records` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `members`
--

DROP TABLE IF EXISTS `members`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `members` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `school_id` bigint unsigned DEFAULT NULL,
  `user_id` bigint unsigned NOT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `status` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'active',
  `student_id` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `members_student_id_unique` (`student_id`),
  KEY `members_user_id_foreign` (`user_id`),
  KEY `members_school_id_foreign` (`school_id`),
  CONSTRAINT `members_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE CASCADE,
  CONSTRAINT `members_user_id_foreign` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `members`
--

LOCK TABLES `members` WRITE;
/*!40000 ALTER TABLE `members` DISABLE KEYS */;
INSERT INTO `members` VALUES (1,1,6,NULL,'Andi Pratama','2007-03-15','L','Jl. Mawar No. 12, Jakarta Pusat','active','2024001','081234567890',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(2,1,7,NULL,'Eka Putri','2007-05-20','P','Jl. Melati No. 25, Jakarta Pusat','active','2024002','081234567891',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(3,1,8,NULL,'Hani Sari','2007-08-10','P','Jl. Anggrek No. 8, Jakarta Pusat','active','2024003','081234567892',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(4,1,9,NULL,'Budi Setiawan','2007-01-25','L','Jl. Kenanga No. 15, Jakarta Pusat','active','2024004','081234567893',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(5,1,10,NULL,'Cici Amanda','2007-06-30','P','Jl. Dahlia No. 22, Jakarta Pusat','active','2024005','081234567894',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(6,1,11,NULL,'Dedi Rahman','2007-09-12','L','Jl. Tulip No. 5, Jakarta Pusat','active','2024006','081234567895',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(7,1,12,NULL,'Fani Lestari','2007-11-18','P','Jl. Sakura No. 18, Jakarta Pusat','active','2024007','081234567896',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(8,1,13,NULL,'Gita Sari','2007-04-08','P','Jl. Cempaka No. 30, Jakarta Pusat','active','2024008','081234567897',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(9,1,14,NULL,'Iko Firmansyah','2007-07-22','L','Jl. Seruni No. 7, Jakarta Pusat','active','2024009','081234567898',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(10,2,17,NULL,'Rina Permata','2007-02-14','P','Jl. Gardenia No. 45, Jakarta Selatan','active','2024010','081234567899',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(11,2,18,NULL,'Doni Setiawan','2007-10-05','L','Jl. Flamboyan No. 33, Jakarta Selatan','active','2024011','081234567800',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(12,2,19,NULL,'Lisa Anggraini','2007-12-03','P','Jl. Bougenville No. 28, Jakarta Selatan','active','2024012','081234567801',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(13,2,20,NULL,'Raka Mahendra','2007-04-17','L','Jl. Kamboja No. 11, Jakarta Selatan','active','2024013','081234567802',1,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(14,1,27,'Non officiis vel sae','Noelani Garrett','1979-04-03','L','Magni totam expedita','active',NULL,'+1 (934) 513-1006',1,'2025-08-31 04:20:54','2025-08-31 04:20:54'),(15,1,28,'Eum velit adipisici','Sacha Donovan','2002-12-05','P','Natus quia voluptatu','active',NULL,'+1 (675) 681-5822',1,'2025-08-31 04:20:54','2025-08-31 04:20:54'),(16,1,16,'NIP18237','Zulfikar','2001-10-12','L','address','active','STD02993','0812387283',1,'2025-09-01 05:07:42','2025-09-01 05:07:42'),(17,1,17,'Nisi id qui est ut v','Perspiciatis recusa','2020-11-11','L','Dolorem quis qui eos','active','Voluptatibus et adip','+1 (269) 227-8996',1,'2025-09-01 21:24:00','2025-09-01 21:24:00'),(18,1,29,'Deserunt non sit mod','Eveniet ut tenetur','2000-11-15','P','Cum doloribus id ame','inactive','Maxime id delectus','+1 (737) 817-2336',1,'2025-09-01 21:25:53','2025-09-01 21:25:53');
/*!40000 ALTER TABLE `members` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `migrations`
--

DROP TABLE IF EXISTS `migrations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `migrations` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `migration` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `batch` int NOT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=216 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `migrations`
--

LOCK TABLES `migrations` WRITE;
/*!40000 ALTER TABLE `migrations` DISABLE KEYS */;
INSERT INTO `migrations` VALUES (39,'2025_08_30_100000_add_school_consistency_trigger_to_eschool_member',1),(192,'0001_01_01_000000_create_users_table',2),(193,'0001_01_01_000001_create_cache_table',2),(194,'0001_01_01_000002_create_jobs_table',2),(195,'2025_08_22_063244_create_personal_access_tokens_table',2),(196,'2025_08_22_063856_add_role_to_users_table',2),(197,'2025_08_23_020513_create_schools_table',2),(198,'2025_08_23_020604_create_eschools_table',2),(199,'2025_08_23_020906_create_members_table',2),(200,'2025_08_23_020926_create_kas_records_table',2),(201,'2025_08_23_085748_create_kas_payments_table',2),(202,'2025_08_26_115720_attendance_record',2),(203,'2025_08_29_100000_create_eschool_member_table',2),(204,'2025_08_29_100001_remove_eschool_id_from_members_table',2),(205,'2025_08_29_100002_add_member_details_to_members_table',2),(206,'2025_08_29_100003_add_email_to_members_table',2),(207,'2025_08_29_100005_add_school_id_foreign_key_to_members_table',2),(208,'2025_08_29_100006_remove_email_and_position_from_members_table',2),(209,'2025_08_30_002403_add_school_id_to_users_table',2),(210,'2025_08_30_034456_add_category_to_kas_records_table',2),(211,'2025_08_31_000000_add_additional_fields_to_users_table',3),(215,'2025_08_31_150000_add_proof_document_to_attendance_records_table',4);
/*!40000 ALTER TABLE `migrations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `password_reset_tokens`
--

DROP TABLE IF EXISTS `password_reset_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `password_reset_tokens` (
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `password_reset_tokens`
--

LOCK TABLES `password_reset_tokens` WRITE;
/*!40000 ALTER TABLE `password_reset_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `password_reset_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `personal_access_tokens`
--

DROP TABLE IF EXISTS `personal_access_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `personal_access_tokens`
--

LOCK TABLES `personal_access_tokens` WRITE;
/*!40000 ALTER TABLE `personal_access_tokens` DISABLE KEYS */;
/*!40000 ALTER TABLE `personal_access_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `schools`
--

DROP TABLE IF EXISTS `schools`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `schools` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `address` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `schools`
--

LOCK TABLES `schools` WRITE;
/*!40000 ALTER TABLE `schools` DISABLE KEYS */;
INSERT INTO `schools` VALUES (1,'SMA Negeri 1 Jakarta','Jl. Merdeka No. 123, Jakarta Pusat','021-12345678','info@sman1jakarta.sch.id','2025-08-30 01:27:14','2025-08-30 01:27:14'),(2,'SMA Negeri 2 Jakarta','Jl. Kemerdekaan No. 456, Jakarta Selatan','021-87654321','info@sman2jakarta.sch.id','2025-08-30 01:27:14','2025-08-30 01:27:14'),(3,'SMA Negeri 3 Jakarta','Jl. Pemuda No. 789, Jakarta Timur','021-11223344','info@sman3jakarta.sch.id','2025-08-30 01:27:14','2025-08-30 01:27:14');
/*!40000 ALTER TABLE `schools` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sessions`
--

DROP TABLE IF EXISTS `sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `sessions` (
  `id` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `user_id` bigint unsigned DEFAULT NULL,
  `ip_address` varchar(45) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `user_agent` text COLLATE utf8mb4_unicode_ci,
  `payload` longtext COLLATE utf8mb4_unicode_ci NOT NULL,
  `last_activity` int NOT NULL,
  PRIMARY KEY (`id`),
  KEY `sessions_user_id_index` (`user_id`),
  KEY `sessions_last_activity_index` (`last_activity`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sessions`
--

LOCK TABLES `sessions` WRITE;
/*!40000 ALTER TABLE `sessions` DISABLE KEYS */;
/*!40000 ALTER TABLE `sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email_verified_at` timestamp NULL DEFAULT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `role` enum('siswa','bendahara','koordinator','staff') COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'siswa',
  `school_id` bigint unsigned DEFAULT NULL,
  `nip` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `date_of_birth` date DEFAULT NULL,
  `gender` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `address` text COLLATE utf8mb4_unicode_ci,
  `phone` varchar(255) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `remember_token` varchar(100) COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `users_email_unique` (`email`),
  KEY `users_school_id_foreign` (`school_id`),
  CONSTRAINT `users_school_id_foreign` FOREIGN KEY (`school_id`) REFERENCES `schools` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Bu Sari Wijaya','sari.wijaya@sman1jakarta.sch.id','2025-08-30 01:27:14','$2y$12$FRS7tf2iHkeA3GIX4PdUIuMc57Lvz0hhcjwxr1i42Aww4TXU.dzGe','staff',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:14','2025-08-30 01:27:14'),(2,'Pak Budi Santoso','budi.santoso@sman1jakarta.sch.id','2025-08-30 01:27:15','$2y$12$CCMWjNIkDABjK64aoB4q..YSwiDFbYado4WFitp.9zQsa/LxFdMrW','staff',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:15','2025-08-30 01:27:15'),(3,'Pak Joko Susilo','joko.susilo@sman1jakarta.sch.id','2025-08-30 01:27:15','$2y$12$xdnx1LuV7t7jN82WrppVautEousafaxIGaDaavbzzGv8nWHb1dUAm','koordinator',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:15','2025-08-30 01:27:15'),(4,'Bu Rina Sari','rina.sari@sman1jakarta.sch.id','2025-08-30 01:27:15','$2y$12$Y72.244xHYeYsIkxdRKsxOCX.Z.0k8NW0AqWAqpkkBQWQqB4zHoii','koordinator',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:15','2025-08-30 01:27:15'),(5,'Pak Heru Prasetyo','heru.prasetyo@sman1jakarta.sch.id','2025-08-30 01:27:15','$2y$12$S9ZloqccGI/l7Q9fWa47zu61NX1LKZ7xhtgUyMWxNuOdWgnfV5fAK','koordinator',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:15','2025-08-30 01:27:15'),(6,'Andi Pratama','andi.pratama@student.sman1jakarta.sch.id','2025-08-30 01:27:15','$2y$12$w2ID12JJCRHNVJrT096T4eBvunZOF7vlSErQ42Mv9bVTCnF3UTJju','bendahara',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:15','2025-08-30 01:27:15'),(7,'Eka Putri','eka.putri@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$QmLVIqta2kQEYcqcZRa6pO8lLa5mLZ4MzP6RATMX1x1eyc9sH8AZG','bendahara',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(8,'Hani Sari','hani.sari@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$A/IZiXypxvw.H3rGTJRT9OsdcOO3E.HefQ7FDinOfu5CrALKYdeDu','bendahara',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(9,'Budi Setiawan','budi.setiawan@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$85YBAScLnWSc4ttvbbIUiuBWvrTNu9UueHHNbeuCiACVI92STTKmC','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(10,'Cici Amanda','cici.amanda@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$BnSNIU6eu9GYeee0SB7LiegF8Pc95c5AJdv5CnTM/0XNfAu4cM6Iy','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(11,'Dedi Rahman','dedi.rahman@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$RqaBxIZG5nI29BjqPSzNYOdpaNBQe.OfjlkWQaHwQWlBLERauRyg2','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(12,'Fani Lestari','fani.lestari@student.sman1jakarta.sch.id','2025-08-30 01:27:16','$2y$12$ynBqFtYu9M.ZYZAsqTp6b.Vvvk39w8G.9TOp88a.Pwpm4aNZGES1a','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:16','2025-08-30 01:27:16'),(13,'Gita Sari','gita.sari@student.sman1jakarta.sch.id','2025-08-30 01:27:17','$2y$12$lVkQ7PJhxmjbsF6IBBw0YuKQLkR4Rgw1DF0PJ14iB.Ut8zb0KLOXq','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:17','2025-08-30 01:27:17'),(14,'Iko Firmansyah','iko.firmansyah@student.sman1jakarta.sch.id','2025-08-30 01:27:17','$2y$12$0bcNkKwQcVvbzHeSQETiZ.p8Lb7GixkisZPHSSgeeZLbWdjl1UpL6','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:17','2025-08-30 01:27:17'),(15,'Joni Kurniawan','joni.kurniawan@student.sman1jakarta.sch.id','2025-08-30 01:27:17','$2y$12$MvxkJssO7NQnxIgrbYrgr.ZUWVLzHhksc.pc1EXeGC6M7FBDgpK9O','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:17','2025-08-30 01:27:17'),(16,'zulfikar','zulfikar@student.sman1jakarta.sch.id','2025-08-30 01:27:17','$2y$12$LFUPHIpUWSbKLqxBD6vY5ekxDq0KUO1R/Bp6BVFoWXfKnfA9LOeOK','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:17','2025-08-30 01:27:17'),(17,'edwin','edwin@student.sman1jakarta.sch.id','2025-08-30 01:27:17','$2y$12$SRq1Ook6D/H.WI65Ao.nCusv14l5CyeoJeo57Ri76ocuxTxna.FTG','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:17','2025-08-30 01:27:17'),(18,'Imron','imron@student.sman1jakarta.sch.id','2025-08-30 01:27:18','$2y$12$4875KFIJ2TaPdJQgVg93sOklI7jlmfMeCO0UZd6nWxlSohifHIheq','siswa',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(19,'Bu Maya Indri','maya.indri@sman2jakarta.sch.id','2025-08-30 01:27:18','$2y$12$uh0RFRyiC4qGUs6S6Ly79eoQzZHvner63Up734b.iKF9OGPfCm3aK','staff',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(20,'Pak Ahmad Fauzi','ahmad.fauzi@sman2jakarta.sch.id','2025-08-30 01:27:18','$2y$12$S3hb5cvsj9ym90/XgEFJJeZi.NPBtxd0unLfRMyY//gmeCLLXfM36','koordinator',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(21,'Bu Lina Marlina','lina.marlina@sman2jakarta.sch.id','2025-08-30 01:27:18','$2y$12$qOrodoeH4NwAjypgAB0Uju30gk.PW91sBdbeKMoic98kfJVXfIPjq','koordinator',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(22,'Rina Permata','rina.permata@student.sman2jakarta.sch.id','2025-08-30 01:27:18','$2y$12$.OH.sHW4iha26ZevkjDTFOHTL9Wf5/Zqb6RszLNBBQ8LMCKMNQDLq','bendahara',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(23,'Doni Setiawan','doni.setiawan@student.sman2jakarta.sch.id','2025-08-30 01:27:18','$2y$12$75OSTqMQgtGHoMuj2dhJB.04kSwuY4FQ8D0v/omMF/Glr2Os94CEi','bendahara',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:18','2025-08-30 01:27:18'),(24,'Lisa Anggraini','lisa.anggraini@student.sman2jakarta.sch.id','2025-08-30 01:27:19','$2y$12$Hc1kOaKyxCtIiYhXSM3PC.uDx48r.Gf14N63USnzwddSEONhmPGUi','siswa',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(25,'Raka Mahendra','raka.mahendra@student.sman2jakarta.sch.id','2025-08-30 01:27:19','$2y$12$K1o6Nzb8XwNOTVSn5FhWo.DY31wDlqYu0en16t8h4B7xbrQjfcrBi','siswa',2,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-30 01:27:19','2025-08-30 01:27:19'),(26,'ujang','ujangcoor@sman1jakarta.sch.id',NULL,'$2y$12$nZvtrVEYw5vPWumWheWz1.Gb48.vXbuOUdDlSEmfZ1YuWbcv0ixky','koordinator',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-31 04:02:59','2025-08-31 04:02:59'),(27,'Noelani Garrett','hunyxuke@mailinator.com',NULL,'$2y$12$slNOE4oyCmo1i/TtDJe1deXU7TZOZh4LeRSe9dMBafkyT.tspYHPu','koordinator',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-31 04:20:54','2025-08-31 04:20:54'),(28,'Sacha Donovan','suqo@mailinator.com',NULL,'$2y$12$829..ZJnI9etRGCk8HG02.VGwQfVmc8ttwlmpdkl0bI9igqLX.FBS','bendahara',1,NULL,NULL,NULL,NULL,NULL,NULL,'2025-08-31 04:20:54','2025-08-31 04:20:54'),(29,'Rerum ut voluptas ul','fuzi@mailinator.com',NULL,'$2y$12$lalf2ONu2sBio6124jGg7edyLJnLixCVjeurVNU4GvevDY31c2VS.','siswa',NULL,NULL,NULL,NULL,NULL,NULL,NULL,'2025-09-01 21:25:53','2025-09-01 21:25:53');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-09-02 12:58:44
