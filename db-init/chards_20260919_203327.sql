-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: chards
-- ------------------------------------------------------
-- Server version	8.0.46

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
-- Table structure for table `chards_card_ownership`
--

DROP TABLE IF EXISTS `chards_card_ownership`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_card_ownership` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `card_id` int unsigned NOT NULL,
  `quantity` int unsigned NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_ownership` (`user_id`,`card_id`),
  KEY `idx_ownership_user` (`user_id`),
  KEY `idx_ownership_card` (`card_id`),
  CONSTRAINT `fk_ownership_card` FOREIGN KEY (`card_id`) REFERENCES `chards_cards` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_ownership_user` FOREIGN KEY (`user_id`) REFERENCES `chards_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=231 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_card_ownership`
--

LOCK TABLES `chards_card_ownership` WRITE;
/*!40000 ALTER TABLE `chards_card_ownership` DISABLE KEYS */;
INSERT INTO `chards_card_ownership` VALUES (2,1,35,6,'2026-05-28 18:41:26','2026-09-19 17:21:57'),(4,1,36,4,'2026-05-28 18:41:26','2026-08-02 19:52:38'),(5,1,9,1,'2026-05-28 18:41:26','2026-05-28 18:41:26'),(8,1,18,2,'2026-05-29 19:10:31','2026-08-02 19:51:13'),(9,1,32,4,'2026-05-29 19:10:31','2026-08-03 08:41:10'),(10,1,17,1,'2026-05-29 19:10:31','2026-05-29 19:10:31'),(15,1,10,2,'2026-05-29 19:11:06','2026-07-18 11:06:04'),(19,1,25,2,'2026-05-29 19:11:33','2026-07-05 20:47:48'),(20,1,14,1,'2026-05-29 19:11:33','2026-05-29 19:11:33'),(24,1,26,2,'2026-05-29 19:11:57','2026-08-02 19:51:13'),(25,1,8,2,'2026-05-29 19:11:57','2026-07-05 20:47:48'),(29,1,20,3,'2026-05-29 19:12:38','2026-09-19 17:21:57'),(30,1,13,2,'2026-05-29 19:12:38','2026-09-19 17:21:57'),(35,1,11,1,'2026-05-29 19:13:02','2026-05-29 19:13:02'),(38,1,19,2,'2026-05-29 19:17:06','2026-07-18 11:06:46'),(39,1,29,2,'2026-05-29 19:17:06','2026-08-02 19:55:05'),(40,1,12,2,'2026-05-29 19:17:06','2026-07-11 17:39:30'),(42,1,40,1,'2026-05-30 07:20:02','2026-05-30 07:20:02'),(43,1,23,3,'2026-05-30 13:57:44','2026-08-02 19:52:38'),(44,2,43,1,'2026-05-30 21:39:40','2026-05-30 21:39:40'),(45,2,27,1,'2026-05-30 21:39:40','2026-05-30 21:39:40'),(46,2,35,1,'2026-05-30 21:39:40','2026-05-30 21:39:40'),(47,2,40,1,'2026-05-30 21:39:40','2026-05-30 21:39:40'),(48,2,6,1,'2026-05-30 21:39:40','2026-05-30 21:39:40'),(49,2,22,1,'2026-05-30 21:48:28','2026-05-30 21:48:28'),(50,2,39,1,'2026-05-30 21:48:28','2026-05-30 21:48:28'),(51,2,32,1,'2026-05-30 21:48:28','2026-05-30 21:48:28'),(52,2,36,1,'2026-05-30 21:48:28','2026-05-30 21:48:28'),(53,2,15,1,'2026-05-30 21:48:28','2026-05-30 21:48:28'),(55,1,39,7,'2026-05-31 05:48:55','2026-08-02 19:55:05'),(56,1,22,3,'2026-05-31 05:48:55','2026-07-11 17:39:30'),(57,1,30,1,'2026-05-31 05:48:55','2026-05-31 05:48:55'),(58,1,16,2,'2026-05-31 05:48:55','2026-07-08 17:46:26'),(62,1,45,3,'2026-05-31 05:49:14','2026-08-03 08:41:10'),(63,1,7,2,'2026-05-31 05:49:14','2026-07-08 17:46:09'),(64,1,31,4,'2026-05-31 05:49:24','2026-08-02 19:55:05'),(69,1,44,5,'2026-05-31 13:08:36','2026-08-03 08:41:10'),(70,1,43,5,'2026-07-05 20:47:48','2026-08-02 19:52:38'),(83,1,46,1,'2026-07-08 17:46:26','2026-07-08 17:46:26'),(87,1,37,1,'2026-07-11 17:39:30','2026-07-11 17:39:30'),(89,13,35,2,'2026-07-27 06:45:41','2026-08-01 21:28:27'),(90,13,31,7,'2026-07-27 06:45:41','2026-08-23 18:29:37'),(91,13,36,3,'2026-07-27 06:45:41','2026-08-23 18:28:43'),(92,13,28,1,'2026-07-27 06:45:41','2026-07-27 06:45:41'),(93,13,14,1,'2026-07-27 06:45:41','2026-07-27 06:45:41'),(94,13,27,5,'2026-07-27 18:44:34','2026-08-23 18:29:37'),(96,13,32,1,'2026-07-27 18:44:34','2026-07-27 18:44:34'),(97,13,19,3,'2026-07-27 18:44:34','2026-08-23 18:25:40'),(98,13,13,1,'2026-07-27 18:44:34','2026-07-27 18:44:34'),(99,15,27,3,'2026-07-30 16:44:55','2026-07-30 16:52:15'),(100,15,18,8,'2026-07-30 16:44:55','2026-07-30 16:52:28'),(101,15,19,4,'2026-07-30 16:44:55','2026-07-30 16:50:02'),(102,15,32,3,'2026-07-30 16:44:55','2026-07-30 16:48:51'),(103,15,9,2,'2026-07-30 16:44:55','2026-07-30 16:51:01'),(105,15,22,2,'2026-07-30 16:46:16','2026-07-30 16:49:19'),(106,15,39,5,'2026-07-30 16:46:16','2026-07-30 16:52:28'),(108,15,17,2,'2026-07-30 16:46:16','2026-07-30 16:48:51'),(109,15,31,6,'2026-07-30 16:46:48','2026-07-30 16:51:11'),(110,15,43,5,'2026-07-30 16:46:48','2026-07-30 16:52:28'),(111,15,35,1,'2026-07-30 16:46:48','2026-07-30 16:51:43'),(112,15,23,3,'2026-07-30 16:46:48','2026-07-30 16:50:50'),(113,15,6,2,'2026-07-30 16:46:48','2026-07-30 16:49:30'),(116,15,41,1,'2026-07-30 16:47:07','2026-07-30 16:47:07'),(117,15,25,1,'2026-07-30 16:47:07','2026-07-30 16:47:07'),(118,15,11,2,'2026-07-30 16:47:07','2026-07-30 16:49:47'),(122,15,26,1,'2026-07-30 16:48:15','2026-07-30 16:48:15'),(123,15,15,2,'2026-07-30 16:48:15','2026-07-30 16:52:15'),(126,15,44,5,'2026-07-30 16:48:51','2026-07-30 16:51:11'),(127,15,45,2,'2026-07-30 16:48:51','2026-07-30 16:49:47'),(130,15,28,2,'2026-07-30 16:49:08','2026-07-30 16:52:15'),(133,15,10,1,'2026-07-30 16:49:08','2026-07-30 16:49:08'),(137,15,40,5,'2026-07-30 16:49:19','2026-07-30 16:52:28'),(138,15,13,1,'2026-07-30 16:49:19','2026-07-30 16:49:19'),(152,15,29,1,'2026-07-30 16:50:02','2026-07-30 16:50:02'),(153,15,7,1,'2026-07-30 16:50:02','2026-07-30 16:50:02'),(158,15,12,1,'2026-07-30 16:50:50','2026-07-30 16:50:50'),(168,15,8,1,'2026-07-30 16:51:11','2026-07-30 16:51:11'),(169,15,36,2,'2026-07-30 16:51:43','2026-07-30 16:52:15'),(179,15,14,1,'2026-07-30 16:52:28','2026-07-30 16:52:28'),(180,13,18,3,'2026-08-01 21:28:27','2026-08-23 18:29:37'),(182,13,23,1,'2026-08-01 21:28:27','2026-08-01 21:28:27'),(183,13,37,1,'2026-08-01 21:28:27','2026-08-01 21:28:27'),(184,13,16,1,'2026-08-01 21:28:27','2026-08-01 21:28:27'),(186,13,39,1,'2026-08-02 07:51:25','2026-08-02 07:51:25'),(187,13,40,3,'2026-08-02 07:51:25','2026-08-23 18:29:37'),(188,13,45,3,'2026-08-02 07:51:25','2026-08-23 18:26:09'),(189,13,12,1,'2026-08-02 07:51:25','2026-08-02 07:51:25'),(193,1,28,3,'2026-08-03 08:41:10','2026-09-19 17:21:57'),(197,13,43,3,'2026-08-23 18:23:38','2026-08-23 18:27:30'),(199,13,41,1,'2026-08-23 18:23:38','2026-08-23 18:23:38'),(205,13,44,3,'2026-08-23 18:25:40','2026-08-23 18:28:43'),(206,13,22,4,'2026-08-23 18:26:09','2026-08-23 18:29:37'),(215,13,38,1,'2026-08-23 18:27:30','2026-08-23 18:27:30'),(226,1,27,1,'2026-09-19 17:21:57','2026-09-19 17:21:57');
/*!40000 ALTER TABLE `chards_card_ownership` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_cards`
--

DROP TABLE IF EXISTS `chards_cards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_cards` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `flavour_text` varchar(512) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `card_type` enum('story','collectible','event') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'collectible',
  `chapter_id` smallint unsigned NOT NULL,
  `card_number` smallint unsigned DEFAULT NULL,
  `panel_page` tinyint unsigned DEFAULT NULL,
  `panel_number` tinyint unsigned DEFAULT NULL,
  `rarity_id` tinyint unsigned NOT NULL,
  `is_unique` tinyint(1) NOT NULL DEFAULT '0',
  `print_run` int unsigned DEFAULT NULL,
  `image_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `artist` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `event_id` int unsigned DEFAULT NULL,
  `enters_packs_at` date DEFAULT NULL,
  `is_available` tinyint(1) NOT NULL DEFAULT '1',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `base_card_id` int unsigned DEFAULT NULL,
  `upgrade_cost` tinyint unsigned NOT NULL DEFAULT '5',
  PRIMARY KEY (`id`),
  KEY `idx_card_chapter` (`chapter_id`),
  KEY `idx_card_type` (`card_type`),
  KEY `idx_card_rarity` (`rarity_id`),
  KEY `idx_card_available` (`is_available`,`is_active`),
  KEY `idx_card_panel` (`chapter_id`,`panel_page`,`panel_number`),
  CONSTRAINT `fk_card_chapter` FOREIGN KEY (`chapter_id`) REFERENCES `chards_chapters` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE,
  CONSTRAINT `fk_card_rarity` FOREIGN KEY (`rarity_id`) REFERENCES `chards_rarities` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=301 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_cards`
--

LOCK TABLES `chards_cards` WRITE;
/*!40000 ALTER TABLE `chards_cards` DISABLE KEYS */;
INSERT INTO `chards_cards` VALUES (6,'The Nightfall — Panel 1','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,1,8,1,1,'/media/Story/Prologue/Page-1/01.png',NULL,NULL,NULL,1,1,'2026-05-04 19:33:39','2026-09-19 17:10:13',NULL,5),(7,'The Nightfall — Panel 2','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,2,8,1,1,'/media/Story/Prologue/Page-1/02.png',NULL,NULL,NULL,1,1,'2026-05-04 19:33:39','2026-09-19 17:10:29',NULL,5),(8,'The Nightfall — Panel 3','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,3,8,1,1,'/media/Story/Prologue/Page-1/03.png',NULL,NULL,NULL,1,1,'2026-05-04 19:33:39','2026-09-19 17:10:43',NULL,5),(9,'The Nightfall — Panel 4','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,4,8,1,1,'/media/Story/Prologue/Page-1/04.png',NULL,NULL,NULL,1,1,'2026-05-04 19:33:39','2026-09-19 17:11:01',NULL,5),(10,'The Nightfall — Panel 5','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,5,8,1,1,'/media/Story/Prologue/Page-1/05.png',NULL,NULL,NULL,1,1,'2026-05-04 19:33:39','2026-09-19 17:11:16',NULL,5),(11,'The Nightfall — Panel 6','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,6,8,1,1,'/media/Story/Prologue/Page-1/06.png',NULL,NULL,NULL,1,1,'2026-05-04 19:35:24','2026-09-19 17:11:31',NULL,5),(12,'The Nightfall — Panel 7','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,7,8,1,1,'/media/Story/Prologue/Page-1/07.png',NULL,NULL,NULL,0,1,'2026-05-04 19:35:24','2026-09-19 17:11:45',NULL,5),(13,'The Nightfall — Panel 8','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,8,8,1,1,'/media/Story/Prologue/Page-1/08.png',NULL,NULL,NULL,0,1,'2026-05-04 19:35:24','2026-09-19 17:21:57',NULL,5),(14,'The Nightfall — Panel 9','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,9,8,1,1,'/media/Story/Prologue/Page-1/09.png',NULL,NULL,NULL,1,1,'2026-05-04 19:35:24','2026-09-19 17:12:14',NULL,5),(15,'The Nightfall — Panel 10','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,10,8,1,1,'/media/Story/Prologue/Page-1/10.png',NULL,NULL,NULL,1,1,'2026-05-04 19:35:24','2026-09-19 17:12:29',NULL,5),(16,'The Nightfall — Panel 11','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,11,8,1,1,'/media/Story/Prologue/Page-1/11.png',NULL,NULL,NULL,1,1,'2026-05-04 19:36:02','2026-09-19 17:12:52',NULL,5),(17,'The Nightfall — Panel 12','The Fall','They say the sky did not open... They say it fell.','story',1,1,1,12,8,1,1,'/media/Story/Prologue/Page-1/12.png',NULL,NULL,NULL,1,1,'2026-05-04 19:36:02','2026-09-19 17:13:07',NULL,5),(18,'female warrior','female warrior','female warrior','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/qFOdDN77MO.png',NULL,NULL,NULL,1,1,'2026-05-07 19:55:53','2026-05-07 19:55:53',NULL,5),(19,'female warrior','female warrior','female warrior','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/3QIXU1wlF9.png',NULL,NULL,NULL,1,1,'2026-05-07 19:56:54','2026-05-07 19:56:54',18,5),(20,'female warrior','female warrior','female warrior','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/dbBJ7Bry2p.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',18,5),(21,'female warrior','female warrior','female warrior','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/R8otZ6mhRs.png',NULL,NULL,NULL,1,1,'2026-05-09 20:53:45','2026-05-09 20:53:45',18,5),(22,'male warrior','male warrior','male warrior','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/48_03hZPd0.png',NULL,NULL,NULL,1,1,'2026-05-09 20:53:45','2026-05-09 20:53:45',NULL,5),(23,'male warrior','male warrior','male warrior','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/TcHj0mRexh.png',NULL,NULL,NULL,1,1,'2026-05-09 20:54:11','2026-05-09 20:54:11',22,5),(25,'male warrior','male warrior','male warrior','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/FrQazPuV2m.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',22,5),(26,'male warrior','male warrior','male warrior','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/sJFTJ-P0TN.png',NULL,NULL,NULL,1,1,'2026-05-09 20:54:11','2026-05-09 20:54:11',22,5),(27,'male knight','male knight','male knight','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/eVhmiCsLVG.png',NULL,NULL,NULL,1,1,'2026-05-09 20:57:37','2026-05-09 20:57:37',NULL,5),(28,'male knight','male knight','male knight','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/4qtmPCzGgj.png',NULL,NULL,NULL,1,1,'2026-05-09 20:57:37','2026-05-09 20:57:37',27,5),(29,'male knight','male knight','male knight','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/q_e_nh3v33.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',27,5),(30,'male knight','male knight','male knight','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/b2SxUC8S88.png',NULL,NULL,NULL,1,1,'2026-05-09 20:57:37','2026-05-09 20:57:37',27,5),(31,'female mage','female mage','female mage','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/99ZyGq-P0L.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',NULL,5),(32,'female mage','female mage','female mage','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/NNnG0xZe6L.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',31,5),(33,'female mage','female mage','female mage','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/VorldwSkb5.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',31,5),(34,'female mage','female mage','female mage','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/NA6-Thhd46.png',NULL,NULL,NULL,1,1,'2026-05-11 16:44:15','2026-05-11 16:44:15',31,5),(35,'female priestess','female priestess','female priestess','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/Qq96SxfxIb.png',NULL,NULL,NULL,1,1,'2026-05-11 16:45:56','2026-05-11 16:45:56',NULL,5),(36,'female priestess','female priestess','female priestess','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/M_ERZNnqLl.png',NULL,NULL,NULL,1,1,'2026-05-11 16:45:56','2026-05-11 16:45:56',35,5),(37,'female priestess','female priestess','female priestess','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/_vewY2E4nA.png',NULL,NULL,NULL,1,1,'2026-05-11 16:45:56','2026-05-11 16:45:56',35,5),(38,'female priestess','female priestess','female priestess','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/9ewZYpsEHY.png',NULL,NULL,NULL,1,1,'2026-05-11 16:45:56','2026-05-11 16:45:56',35,5),(39,'male king','male king','male king','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/Umu1NH8Vp1.png',NULL,NULL,NULL,1,1,'2026-05-12 21:07:25','2026-05-12 21:07:25',NULL,5),(40,'male king','male king','male king','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/8A62vrGPgR.png',NULL,NULL,NULL,1,1,'2026-05-12 21:07:25','2026-05-12 21:07:25',39,5),(41,'male king','male king','male king','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/49-PFZ2FU4.png',NULL,NULL,NULL,1,1,'2026-05-12 21:07:25','2026-05-12 21:07:25',39,5),(42,'male king','male king','male king','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/xHoEzuLb_T.png',NULL,NULL,NULL,1,1,'2026-05-12 21:07:25','2026-05-12 21:07:25',39,5),(43,'female queen','female queen','female queen','collectible',1,1,NULL,NULL,1,0,NULL,'https://imglink.cc/cdn/ALtzXUUJSG.png',NULL,NULL,NULL,1,1,'2026-05-14 19:05:15','2026-05-14 19:05:15',NULL,5),(44,'female queen','female queen','female queen','collectible',1,1,NULL,NULL,2,0,NULL,'https://imglink.cc/cdn/gkW8pJlnZ7.png',NULL,NULL,NULL,1,1,'2026-05-14 19:05:15','2026-05-14 19:05:15',43,5),(45,'female queen','female queen','female queen','collectible',1,1,NULL,NULL,3,0,NULL,'https://imglink.cc/cdn/4cBXYxE25U.png',NULL,NULL,NULL,1,1,'2026-05-14 19:05:15','2026-05-14 19:05:15',43,5),(46,'female queen','female queen','female queen','collectible',1,1,NULL,NULL,4,0,NULL,'https://imglink.cc/cdn/kNM4_1C4UP.png',NULL,NULL,NULL,1,1,'2026-05-14 19:05:15','2026-05-14 19:05:15',43,5);
/*!40000 ALTER TABLE `chards_cards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `chards_cards_full`
--

DROP TABLE IF EXISTS `chards_cards_full`;
/*!50001 DROP VIEW IF EXISTS `chards_cards_full`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `chards_cards_full` AS SELECT 
 1 AS `id`,
 1 AS `name`,
 1 AS `description`,
 1 AS `flavour_text`,
 1 AS `card_type`,
 1 AS `chapter_id`,
 1 AS `card_number`,
 1 AS `panel_page`,
 1 AS `panel_number`,
 1 AS `rarity_id`,
 1 AS `is_unique`,
 1 AS `print_run`,
 1 AS `image_url`,
 1 AS `artist`,
 1 AS `event_id`,
 1 AS `enters_packs_at`,
 1 AS `is_available`,
 1 AS `is_active`,
 1 AS `created_at`,
 1 AS `updated_at`,
 1 AS `base_card_id`,
 1 AS `upgrade_cost`,
 1 AS `rarity_slug`,
 1 AS `rarity_label`,
 1 AS `rarity_border_url`,
 1 AS `rarity_drop_weight`,
 1 AS `rarity_sort_order`,
 1 AS `rarity_color_hex`,
 1 AS `chapter_name`,
 1 AS `chapter_slug`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chards_chapters`
--

DROP TABLE IF EXISTS `chards_chapters`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_chapters` (
  `id` smallint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cover_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `release_date` date DEFAULT NULL,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_chapter_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_chapters`
--

LOCK TABLES `chards_chapters` WRITE;
/*!40000 ALTER TABLE `chards_chapters` DISABLE KEYS */;
INSERT INTO `chards_chapters` VALUES (1,'prologue','Prologue','The sky fell... And the world changed.',NULL,'2026-05-04',1,'2026-05-04 19:33:14','2026-05-04 19:33:14');
/*!40000 ALTER TABLE `chards_chapters` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_fragmenta`
--

DROP TABLE IF EXISTS `chards_fragmenta`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_fragmenta` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `uid` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `type_id` tinyint unsigned NOT NULL,
  `owner_user_id` int unsigned DEFAULT NULL,
  `is_locked` tinyint(1) NOT NULL DEFAULT '0',
  `registered_at` timestamp NULL DEFAULT NULL,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fragmenta_uid` (`uid`),
  KEY `idx_fragmenta_owner` (`owner_user_id`),
  KEY `idx_fragmenta_type` (`type_id`),
  CONSTRAINT `fk_fragmenta_owner` FOREIGN KEY (`owner_user_id`) REFERENCES `chards_users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_fragmenta_type` FOREIGN KEY (`type_id`) REFERENCES `chards_fragmenta_types` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_fragmenta`
--

LOCK TABLES `chards_fragmenta` WRITE;
/*!40000 ALTER TABLE `chards_fragmenta` DISABLE KEYS */;
/*!40000 ALTER TABLE `chards_fragmenta` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_fragmenta_types`
--

DROP TABLE IF EXISTS `chards_fragmenta_types`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_fragmenta_types` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `name` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `cooldown_minutes` smallint unsigned NOT NULL DEFAULT '420',
  `cards_per_pack` tinyint unsigned NOT NULL DEFAULT '5',
  `story_chance` tinyint unsigned NOT NULL DEFAULT '10',
  `minimum_rarity` tinyint unsigned NOT NULL DEFAULT '0',
  `is_active` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_fragmenta_type_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_fragmenta_types`
--

LOCK TABLES `chards_fragmenta_types` WRITE;
/*!40000 ALTER TABLE `chards_fragmenta_types` DISABLE KEYS */;
INSERT INTO `chards_fragmenta_types` VALUES (1,'base','Base Fragmenta','The standard Fragmenta. 7 hour cooldown between pack openings, with a chance of finding a Story card.',420,5,1,0,1,'2026-05-10 06:44:44','2026-05-10 06:44:44');
/*!40000 ALTER TABLE `chards_fragmenta_types` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_pack_log`
--

DROP TABLE IF EXISTS `chards_pack_log`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_pack_log` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `fragmenta_id` int unsigned DEFAULT NULL,
  `pack_id` int unsigned NOT NULL,
  `opened_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_log_user` (`user_id`,`opened_at`),
  KEY `idx_log_fragmenta` (`fragmenta_id`,`opened_at`),
  KEY `idx_log_pack` (`pack_id`),
  CONSTRAINT `fk_log_fragmenta` FOREIGN KEY (`fragmenta_id`) REFERENCES `chards_fragmenta` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  CONSTRAINT `fk_log_pack` FOREIGN KEY (`pack_id`) REFERENCES `chards_packs` (`id`) ON DELETE CASCADE ON UPDATE CASCADE,
  CONSTRAINT `fk_log_user` FOREIGN KEY (`user_id`) REFERENCES `chards_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_pack_log`
--

LOCK TABLES `chards_pack_log` WRITE;
/*!40000 ALTER TABLE `chards_pack_log` DISABLE KEYS */;
/*!40000 ALTER TABLE `chards_pack_log` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_packs`
--

DROP TABLE IF EXISTS `chards_packs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_packs` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `session_id` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `finder_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'Anonymous',
  `card_ids` varchar(256) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('pending','opened') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_pack_session` (`session_id`,`status`)
) ENGINE=InnoDB AUTO_INCREMENT=168 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_packs`
--

LOCK TABLES `chards_packs` WRITE;
/*!40000 ALTER TABLE `chards_packs` DISABLE KEYS */;
INSERT INTO `chards_packs` VALUES (12,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','8,9,6,17,15','opened','2026-05-07 20:07:36'),(13,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','11,7,13,16,12','opened','2026-05-07 20:27:32'),(14,'rh13gtna670ka11ggkcudsmbcu','Anonymous','16,7,10,14,17','pending','2026-05-08 09:24:13'),(15,'6he7mlcfpf52lmhs0uht8mek66','Anonymous','15,9,12,6,13','pending','2026-05-08 09:24:14'),(16,'5lrpj7g121tppsk18m9uj2bk9g','Anonymous','8,11','pending','2026-05-08 09:24:15'),(17,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','8,15,6,9,17','opened','2026-05-08 14:25:35'),(18,'239hbk5o3d593l3a0ut4mdlcmr','Anonymous','13,14,7,12,9','pending','2026-05-09 07:06:47'),(19,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,21,24,25,15','opened','2026-05-09 21:07:38'),(20,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,18,24,22,11','opened','2026-05-09 21:08:47'),(21,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','24,21,18,25,17','opened','2026-05-09 21:12:05'),(22,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,24,18,26,10','opened','2026-05-09 21:13:34'),(23,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','24,18,21,19,6','opened','2026-05-09 21:15:27'),(24,'ckf1o17dk3v1bqiok00ilscsqh','Anonymous','21,24,18,22,8','opened','2026-05-09 22:18:13'),(25,'ckf1o17dk3v1bqiok00ilscsqh','Anonymous','24,21,18,23,16','opened','2026-05-09 22:18:45'),(26,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,24,21,25,9','opened','2026-05-10 05:55:43'),(27,'is18j813hf3sfmvkvuf0k2nit3','Anonymous','21,24,18,23,14','opened','2026-05-10 07:52:00'),(28,'is18j813hf3sfmvkvuf0k2nit3','Anonymous','24,18,25,22,13','pending','2026-05-10 07:52:23'),(29,'08gskskja1jijc076jjit1q3um','Anonymous','21,24,18,22,13','opened','2026-05-10 11:08:19'),(30,'08gskskja1jijc076jjit1q3um','Anonymous','24,21,18,25,8','opened','2026-05-10 11:08:42'),(31,'08gskskja1jijc076jjit1q3um','Anonymous','18,21,24,25,9','opened','2026-05-10 11:09:05'),(32,'08gskskja1jijc076jjit1q3um','Anonymous','21,18,24,19,7','opened','2026-05-10 11:09:57'),(33,'08gskskja1jijc076jjit1q3um','Anonymous','18,24,21,19,15','opened','2026-05-10 11:10:07'),(34,'08gskskja1jijc076jjit1q3um','Anonymous','18,24,21,19,10','opened','2026-05-10 11:10:36'),(35,'08gskskja1jijc076jjit1q3um','Anonymous','18,24,19,25,17','opened','2026-05-10 11:10:45'),(36,'08gskskja1jijc076jjit1q3um','Anonymous','18,24,21,22,14','opened','2026-05-10 11:10:58'),(37,'08gskskja1jijc076jjit1q3um','Anonymous','18,21,24,25,6','opened','2026-05-10 11:11:26'),(38,'08gskskja1jijc076jjit1q3um','Anonymous','21,24,18,25,12','opened','2026-05-10 11:11:35'),(39,'08gskskja1jijc076jjit1q3um','Anonymous','18,24,21,26,11','opened','2026-05-10 11:20:04'),(40,'08gskskja1jijc076jjit1q3um','Anonymous','24,21,18,22,16','opened','2026-05-10 11:20:19'),(41,'08gskskja1jijc076jjit1q3um','Anonymous','24,18,21,19,10','opened','2026-05-10 11:20:50'),(42,'nc28reomv991a3adpq54at4loa','Anonymous','24,21,18,19,7','pending','2026-05-10 11:21:09'),(43,'08gskskja1jijc076jjit1q3um','Anonymous','24,18,21,20,15','opened','2026-05-10 11:21:33'),(44,'08gskskja1jijc076jjit1q3um','Anonymous','18,21,24,25,17','opened','2026-05-10 11:21:55'),(45,'08gskskja1jijc076jjit1q3um','Anonymous','18,21,24,19,6','opened','2026-05-10 11:41:15'),(46,'08gskskja1jijc076jjit1q3um','Anonymous','24,19,23,20,16','opened','2026-05-10 12:46:59'),(47,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','24,21,18,25,22','opened','2026-05-10 15:38:49'),(48,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,21,24,19,7','opened','2026-05-10 15:39:21'),(49,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,18,24,25,8','opened','2026-05-10 15:39:39'),(50,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','24,18,20,26,6','opened','2026-05-10 15:45:12'),(51,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,24,18,19,10','opened','2026-05-10 16:08:07'),(52,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,24,25,19,11','opened','2026-05-10 16:08:40'),(53,'08gskskja1jijc076jjit1q3um','Anonymous','24,18,21,22,16','opened','2026-05-10 18:34:30'),(54,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','35,24,31,19,8','opened','2026-05-11 16:46:08'),(55,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,18,35,22,11','opened','2026-05-11 16:46:38'),(56,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','35,18,25,33,14','opened','2026-05-11 17:23:12'),(57,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','24,21,31,22,16','opened','2026-05-11 18:36:08'),(58,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,35,31,20,15','opened','2026-05-11 18:36:25'),(59,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','31,21,24,44,6','opened','2026-05-12 21:10:15'),(60,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,44,24,32,7','opened','2026-05-12 21:14:09'),(61,'08gskskja1jijc076jjit1q3um','Anonymous','21,35,24,32,10','opened','2026-05-13 18:26:16'),(62,'08gskskja1jijc076jjit1q3um','Anonymous','35,21,44,36,13','opened','2026-05-13 18:26:28'),(63,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','21,18,44,32,8','opened','2026-05-13 18:48:05'),(64,'08gskskja1jijc076jjit1q3um','Anonymous','24,35,18,21,16','opened','2026-05-14 05:16:33'),(65,'08gskskja1jijc076jjit1q3um','Anonymous','31,21,36,30,7','opened','2026-05-14 05:17:05'),(66,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','22,35,39,38,12','opened','2026-05-15 17:03:40'),(67,'08gskskja1jijc076jjit1q3um','Anonymous','35,31,27,43,10','opened','2026-05-15 19:17:39'),(68,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','22,31,35,30,13','opened','2026-05-16 13:28:45'),(69,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','22,35,27,36,8','opened','2026-05-16 13:30:38'),(70,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','31,22,35,36,15','opened','2026-05-16 16:28:45'),(71,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','22,35,31,43,13','opened','2026-05-16 16:30:17'),(72,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','43,39,22,35,9','opened','2026-05-16 16:30:38'),(73,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','31,22,43,18,12','opened','2026-05-16 16:30:49'),(74,'dv6ee5r6r7m02qknpsojmjnh0g','Anonymous','27,39,31,19,7','opened','2026-05-16 16:31:05'),(75,'0g3ko3vjhan38a1dmuai520hvu','testUser','22,39,35,31,11','opened','2026-05-18 20:20:23'),(76,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','22,18,39,31,17','opened','2026-05-19 04:26:29'),(77,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','35,39,27,33,10','opened','2026-05-19 19:21:50'),(78,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,31,18,27,6','opened','2026-05-20 19:42:51'),(79,'0g3ko3vjhan38a1dmuai520hvu','testUser','39,43,22,37,10','opened','2026-05-20 19:45:59'),(80,'0g3ko3vjhan38a1dmuai520hvu','testUser','35,31,22,34,9','opened','2026-05-20 19:46:08'),(81,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','18,22,27,36,7','opened','2026-05-20 20:46:22'),(82,'dv6ee5r6r7m02qknpsojmjnh0g','testCard','31,22,39,35,16','opened','2026-05-22 05:49:19'),(83,'dv6ee5r6r7m02qknpsojmjnh0g','testCard','27,31,22,19,14','opened','2026-05-22 19:10:50'),(84,'dv6ee5r6r7m02qknpsojmjnh0g','testCard','27,39,43,22,11','opened','2026-05-22 19:33:48'),(85,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','27,35,22,31,15','opened','2026-05-22 20:36:33'),(86,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','22,35,39,27,13','opened','2026-05-22 20:40:24'),(87,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','27,39,35,22,12','opened','2026-05-22 20:41:59'),(88,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,39,31,23,14','opened','2026-05-22 20:44:58'),(89,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','22,39,31,20,17','opened','2026-05-22 20:46:26'),(90,'dv6ee5r6r7m02qknpsojmjnh0g','testCard','35,18,39,43,6','opened','2026-05-22 20:51:59'),(91,'dv6ee5r6r7m02qknpsojmjnh0g','testCard','43,22,27,18,16','opened','2026-05-22 20:52:21'),(92,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','27,35,28,29,12','opened','2026-05-23 07:54:45'),(93,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','39,31,28,20,9','opened','2026-05-24 08:06:52'),(94,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','18,27,31,39,8','opened','2026-05-24 08:12:27'),(95,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','31,35,18,43,7','opened','2026-05-24 08:18:12'),(96,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','27,18,22,28,11','opened','2026-05-24 08:46:36'),(97,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','31,43,35,26,15','opened','2026-05-24 12:47:35'),(98,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','22,39,27,28,10','opened','2026-05-24 12:54:31'),(99,'0g3ko3vjhan38a1dmuai520hvu','testUser','27,31,22,18,13','opened','2026-05-24 13:01:17'),(100,'9d8vp040ro2ko9p2250e8h6r7b','testUser','39,35,18,36,15','opened','2026-05-26 18:16:46'),(101,'9d8vp040ro2ko9p2250e8h6r7b','testCard','18,22,39,19,17','opened','2026-05-26 18:36:36'),(102,'9d8vp040ro2ko9p2250e8h6r7b','testCard','43,35,31,26,16','opened','2026-05-26 18:37:16'),(103,'9d8vp040ro2ko9p2250e8h6r7b','testCard','43,31,39,38,9','opened','2026-05-26 18:38:17'),(104,'9d8vp040ro2ko9p2250e8h6r7b','testCard','39,43,22,30,13','opened','2026-05-26 18:39:06'),(105,'9d8vp040ro2ko9p2250e8h6r7b','testCard','43,35,22,32,12','opened','2026-05-26 18:40:14'),(106,'9d8vp040ro2ko9p2250e8h6r7b','testCard','43,35,27,31,14','opened','2026-05-26 18:40:35'),(107,'9d8vp040ro2ko9p2250e8h6r7b','testCard','27,35,43,39,11','opened','2026-05-26 18:40:50'),(108,'9d8vp040ro2ko9p2250e8h6r7b','testCard','27,43,35,19,8','opened','2026-05-26 18:41:01'),(109,'9d8vp040ro2ko9p2250e8h6r7b','testCard','27,39,43,32,7','opened','2026-05-26 18:41:17'),(110,'9d8vp040ro2ko9p2250e8h6r7b','testCard','35,43,31,22,6','opened','2026-05-26 18:41:26'),(111,'9d8vp040ro2ko9p2250e8h6r7b','testCard','39,27,43,31,10','opened','2026-05-26 18:41:37'),(112,'9d8vp040ro2ko9p2250e8h6r7b','testCard','18,43,35,22,39','opened','2026-05-26 18:42:04'),(113,'9d8vp040ro2ko9p2250e8h6r7b','testCard','18,31,43,35,38','opened','2026-05-26 18:42:17'),(114,'0g3ko3vjhan38a1dmuai520hvu','Anonymous','35,31,19,37,34','opened','2026-05-26 18:42:21'),(115,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,35,22,36,9','opened','2026-05-28 18:41:26'),(116,'0g3ko3vjhan38a1dmuai520hvu','testUser','39,22,18,32,17','opened','2026-05-29 19:10:31'),(117,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,39,18,31,10','opened','2026-05-29 19:11:06'),(118,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,31,36,25,14','opened','2026-05-29 19:11:33'),(119,'0g3ko3vjhan38a1dmuai520hvu','testUser','22,31,39,26,8','opened','2026-05-29 19:11:56'),(120,'0g3ko3vjhan38a1dmuai520hvu','testUser','39,27,22,20,13','opened','2026-05-29 19:12:38'),(121,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,39,31,22,11','opened','2026-05-29 19:13:02'),(122,'0g3ko3vjhan38a1dmuai520hvu','testUser','31,35,19,29,12','opened','2026-05-29 19:17:06'),(123,'9d8vp040ro2ko9p2250e8h6r7b','testCard','43,27,35,40,6','opened','2026-05-30 21:39:40'),(124,'9d8vp040ro2ko9p2250e8h6r7b','testCard','22,39,32,36,15','opened','2026-05-30 21:48:28'),(125,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','18,39,22,30,16','opened','2026-05-31 05:48:55'),(126,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','35,39,18,45,7','opened','2026-05-31 05:49:14'),(127,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','31,35,39,43,45','opened','2026-05-31 05:49:24'),(128,'0g3ko3vjhan38a1dmuai520hvu','testUser','43,31,23,25,8','opened','2026-07-05 20:47:48'),(129,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','39,18,43,44,7','opened','2026-07-08 17:46:09'),(130,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','43,22,32,46,16','opened','2026-07-08 17:46:26'),(131,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','39,22,27,37,12','opened','2026-07-11 17:39:30'),(132,'dv6ee5r6r7m02qknpsojmjnh0g','testUser','18,31,36,44,10','opened','2026-07-18 11:06:04'),(133,'dv6ee5r6r7m02qknpsojmjnh0g','testMelca','35,31,36,28,14','opened','2026-07-27 06:45:41'),(134,'g0m4896ii0kldlkhbs7i6lil5b','testMelca','27,31,32,19,13','opened','2026-07-27 18:44:34'),(135,'1m76v674439vkiipgg67ajeetb','Phoenix','27,18,19,32,9','opened','2026-07-30 16:44:55'),(136,'1m76v674439vkiipgg67ajeetb','Phoenix','18,22,39,32,17','opened','2026-07-30 16:46:16'),(137,'1m76v674439vkiipgg67ajeetb','Phoenix','31,43,35,23,6','opened','2026-07-30 16:46:48'),(138,'1m76v674439vkiipgg67ajeetb','Phoenix','18,31,41,25,11','opened','2026-07-30 16:47:07'),(139,'1m76v674439vkiipgg67ajeetb','Phoenix','18,35,23,26,15','opened','2026-07-30 16:48:15'),(140,'1m76v674439vkiipgg67ajeetb','Phoenix','18,32,44,45,17','opened','2026-07-30 16:48:51'),(141,'1m76v674439vkiipgg67ajeetb','Phoenix','18,28,19,44,10','opened','2026-07-30 16:49:07'),(142,'1m76v674439vkiipgg67ajeetb','Phoenix','35,39,22,40,13','opened','2026-07-30 16:49:19'),(143,'1m76v674439vkiipgg67ajeetb','Phoenix','35,31,19,44,6','opened','2026-07-30 16:49:30'),(144,'1m76v674439vkiipgg67ajeetb','Phoenix','43,31,18,45,11','opened','2026-07-30 16:49:47'),(145,'1m76v674439vkiipgg67ajeetb','Phoenix','43,27,19,29,7','opened','2026-07-30 16:50:02'),(146,'1m76v674439vkiipgg67ajeetb','Phoenix','35,43,23,40,12','opened','2026-07-30 16:50:50'),(147,'1m76v674439vkiipgg67ajeetb','Phoenix','31,39,44,40,9','opened','2026-07-30 16:51:01'),(148,'1m76v674439vkiipgg67ajeetb','Phoenix','39,31,35,44,8','opened','2026-07-30 16:51:11'),(149,'1m76v674439vkiipgg67ajeetb','Phoenix','27,36,28,40,15','opened','2026-07-30 16:52:15'),(150,'1m76v674439vkiipgg67ajeetb','Phoenix','43,18,39,40,14','opened','2026-07-30 16:52:28'),(151,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','18,35,23,37,16','opened','2026-08-01 21:28:27'),(152,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','31,39,40,45,12','opened','2026-08-02 07:51:25'),(153,'72dcrsobno0vqf3ag5ldh7noqp','testUser','43,27,18,44,26','opened','2026-08-02 19:51:12'),(154,'72dcrsobno0vqf3ag5ldh7noqp','testUser','39,27,43,36,23','opened','2026-08-02 19:52:37'),(155,'72dcrsobno0vqf3ag5ldh7noqp','testUser','27,39,31,20,29','opened','2026-08-02 19:55:05'),(156,'72dcrsobno0vqf3ag5ldh7noqp','testUser','35,32,44,28,45','opened','2026-08-03 08:41:10'),(157,'72dcrsobno0vqf3ag5ldh7noqp','Anonymous','43,27,22,29,42','opened','2026-08-08 21:07:00'),(158,'e06ij2dum7n2j637dflpji6315','Anonymous','43,31,22,44,30','opened','2026-08-23 18:15:38'),(159,'e06ij2dum7n2j637dflpji6315','Anonymous','22,23,32,33,37','opened','2026-08-23 18:17:28'),(160,'72dcrsobno0vqf3ag5ldh7noqp','Anonymous','18,43,27,32,46','opened','2026-08-23 18:18:59'),(161,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','27,43,19,41,45','opened','2026-08-23 18:23:38'),(162,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','27,31,40,19,44','opened','2026-08-23 18:25:40'),(163,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','22,31,27,43,45','opened','2026-08-23 18:26:09'),(164,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','22,43,44,36,38','opened','2026-08-23 18:27:30'),(165,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','18,22,31,44,36','opened','2026-08-23 18:28:43'),(166,'72dcrsobno0vqf3ag5ldh7noqp','testMelca','18,31,27,22,40','opened','2026-08-23 18:29:37'),(167,'f9cdc1765654fb973ea25ac1e24de7d5','testUser','27,35,28,20,13','opened','2026-09-19 17:21:57');
/*!40000 ALTER TABLE `chards_packs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_rarities`
--

DROP TABLE IF EXISTS `chards_rarities`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_rarities` (
  `id` tinyint unsigned NOT NULL AUTO_INCREMENT,
  `slug` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `label` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `border_url` varchar(2048) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `drop_weight` smallint unsigned NOT NULL DEFAULT '100',
  `sort_order` tinyint unsigned NOT NULL DEFAULT '0',
  `color_hex` char(7) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_rarity_slug` (`slug`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_rarities`
--

LOCK TABLES `chards_rarities` WRITE;
/*!40000 ALTER TABLE `chards_rarities` DISABLE KEYS */;
INSERT INTO `chards_rarities` VALUES (1,'common','Common','/media/Borders/Common.png',600,1,'#9E9E9E','2026-05-01 18:30:57','2026-09-19 16:45:31'),(2,'uncommon','Uncommon','/media/Borders/Uncommon.png',220,2,'#4CAF50','2026-05-01 18:30:57','2026-09-19 16:46:13'),(3,'magic','Magic','/media/Borders/Magic.png',100,3,'#2196F3','2026-05-01 18:30:57','2026-09-19 16:46:30'),(4,'rare','Rare','/media/Borders/Rare.png',50,4,'#9C27B0','2026-05-01 18:30:57','2026-09-19 16:46:40'),(5,'legendary','Legendary','/media/Borders/Legendary.png',24,5,'#FF9800','2026-05-01 18:30:57','2026-09-19 16:46:58'),(6,'ancient','Ancient','/media/Borders/Ancient.png',5,6,'#F44336','2026-05-01 18:30:57','2026-09-19 16:47:13'),(7,'mythic','Mythic','/media/Borders/Mythic.png',1,7,'#FFD700','2026-05-01 18:30:57','2026-09-19 16:47:27'),(8,'story','Story','/media/Borders/Story.png',0,0,NULL,'2026-05-04 20:22:20','2026-09-19 16:47:38');
/*!40000 ALTER TABLE `chards_rarities` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_remember_tokens`
--

DROP TABLE IF EXISTS `chards_remember_tokens`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_remember_tokens` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `user_id` int unsigned NOT NULL,
  `token_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `expires_at` timestamp NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_token_hash` (`token_hash`),
  KEY `idx_token_user` (`user_id`),
  CONSTRAINT `fk_remember_user` FOREIGN KEY (`user_id`) REFERENCES `chards_users` (`id`) ON DELETE CASCADE ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=60 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_remember_tokens`
--

LOCK TABLES `chards_remember_tokens` WRITE;
/*!40000 ALTER TABLE `chards_remember_tokens` DISABLE KEYS */;
INSERT INTO `chards_remember_tokens` VALUES (13,13,'89294e1944357d38986f1273766711c67f428e737465dc156431622892e6e50e','2026-08-24 22:33:12','2026-07-25 22:33:12'),(14,13,'b20bdd67bdc85ae97f29c261c284f6bddefcae7668dc53df667d1d02f562e5df','2026-08-26 06:44:31','2026-07-27 06:44:31'),(15,13,'62fa22b655d8468b5c687cb76b4186a97d493ee1ea0d6cf3927d48ce9a91eeac','2026-08-26 06:45:29','2026-07-27 06:45:29'),(16,13,'7a43788bbb28b0030cdd404b7d6fafe4fc9d0cc0fb3af4c3aee6011477bed9b2','2026-08-27 21:16:42','2026-07-27 06:46:29'),(17,13,'f303e219306b1bbc07b53d6cb0333517d62c5f749bf8c28e3cad8f561eb2ec44','2026-08-26 14:51:02','2026-07-27 14:51:02'),(18,13,'162a4520ef54950454c59cc7ff078388812ca12c84deb16c1c09749df8ed425b','2026-08-26 15:00:37','2026-07-27 15:00:37'),(19,13,'eacc039a73c3a4cf1362e2b173f97a6e96c0b4dd10c558ae92c587872456ab29','2026-08-26 15:59:21','2026-07-27 15:12:18'),(20,13,'64f93f14cd976b2cbe314cff104aea60e9c602b2ae84ac59c5ac9e00d70f250d','2026-08-26 15:59:57','2026-07-27 15:59:57'),(21,13,'c685c4391f260996d196ccde505008d4c1fcc7a3b7b8f22518aa33bd2d083e75','2026-08-26 16:00:38','2026-07-27 16:00:38'),(22,13,'43b471613a59b38a8d86c8521d831f3f9e68ddce718e1750aad0eab35ce0a9cd','2026-08-26 16:04:35','2026-07-27 16:04:35'),(23,13,'27f39f51be391f0d1ecfbf5be33e5241573fc81939eff7b3b958abc3d48bf672','2026-08-26 18:16:04','2026-07-27 16:05:10'),(24,13,'1bbdc6bd93cfa118ae53d5dbf165d3ee5e0f767cc4c5c13d4fec65847848e096','2026-08-26 18:16:29','2026-07-27 18:16:29'),(25,13,'e4efceca1e3e2c621adc663df9730d760482be1ed3540275d5c9949e3efecec7','2026-08-26 18:43:55','2026-07-27 18:18:29'),(26,13,'47a946b453c032dc96b06f703e7ffbf2610031d191ac3a512c1936720cf4f504','2026-08-26 18:44:15','2026-07-27 18:44:15'),(27,13,'67d9c296924983ab4799fea76b1da26e20e8a71100bdb93eed214f9fb9135d33','2026-08-26 18:45:12','2026-07-27 18:45:12'),(28,13,'4166ebbf04cc22350da138ef78a26cbc324d8a3d1497fddce5aabc1b2a853993','2026-08-26 18:45:38','2026-07-27 18:45:38'),(29,13,'5f088298498d753d76a0bc9eee810dba5a07287ebe5098a9586dbebb9fbab922','2026-08-26 18:48:21','2026-07-27 18:48:21'),(30,13,'20447e7f7e5e206826c0eb605f2fea5930074180984900252b27d9f1da41a877','2026-08-26 18:48:42','2026-07-27 18:48:42'),(31,13,'7977e1b7ff3cafd721565b1366c38c72fa33ead239077a5d68a9620ebbfd840b','2026-08-26 18:51:01','2026-07-27 18:51:01'),(32,13,'88650fcf75af9f3ad6e807d8bb41582505aa46dbbdba933445daa397d2fccf27','2026-08-26 18:53:07','2026-07-27 18:53:07'),(33,13,'13165300fd7d2ea7fa03b9894330392d43686dc00a03fa30483fcbe9f0061be9','2026-08-26 18:54:21','2026-07-27 18:54:21'),(34,13,'9063e4535a4b13d67332a0c3e448ddaf86fabfe7692df769452ca93cdb8cbd85','2026-08-26 18:56:21','2026-07-27 18:56:21'),(35,13,'8f3f67da18dc0995857373edf3274099bf31a97ba1b9455f0c575d3ca2817373','2026-08-26 19:00:03','2026-07-27 19:00:03'),(36,13,'e4b860d39c3124a274aabc27dbd606f05f4506a7f01b352ab0dfd6e0d4ce2d24','2026-08-26 19:05:33','2026-07-27 19:05:33'),(37,13,'a9af7d99c96be4635e4487128938122c307394eddb4e0cfc8377f559d12a20a0','2026-08-26 19:09:43','2026-07-27 19:09:43'),(38,13,'f9f788618a3c1b9d382e54852f0d61fbe45d0bda551db6cb0b6d942831d7c30f','2026-08-26 19:15:43','2026-07-27 19:15:43'),(39,13,'9b081aaff6e47829d16cf98f6cf201e2b97733ab8f080e1326590106ca065e0d','2026-08-26 19:17:23','2026-07-27 19:17:23'),(40,13,'774a980b33451fdb5e02b4f8c24389d107e2d593c2473a40b361b30607423169','2026-08-26 19:58:15','2026-07-27 19:20:15'),(41,13,'3a02fb3ba170e0f59c40c9cbe76ff9d58a60a586327e38f5cf2f46e61394974a','2026-08-28 19:26:45','2026-07-27 19:58:33'),(44,13,'7d40ea9c173752c03faf22d7afdcd3715353726b0fca3c6c4ab030ed4edb5460','2026-08-29 13:39:36','2026-07-28 21:17:07'),(45,15,'a1898fb527241cd3de21b174bc9fb5cd7115d236cbe81aac388cbb0ecf58a61c','2026-09-16 12:19:22','2026-07-30 16:44:34'),(46,13,'c1d95f363160dfc8a1ebb7d5ffe0d029186df673b04a391a6c000d318606c794','2026-09-07 21:13:57','2026-07-30 19:45:36'),(49,13,'7497e7050a5d04066201b0fdafa805ab0751ac776bd9ae96ac05f7d214a5bc24','2026-09-22 18:20:57','2026-08-23 18:20:57'),(50,13,'db2ff102d54ffdacb0c8ff755ed1313e2d9ff01cae23de6ae9efaa2a826a6438','2026-09-22 18:21:10','2026-08-23 18:21:10'),(51,1,'97838543058bce77cb8432d97c3f646cc69789f07c5c80b0651b1edbc0b223da','2026-09-22 18:21:18','2026-08-23 18:21:18'),(52,1,'8a2ce011022b3ac385d9a5413def6db72096f4d50efd522d2e9ba6e53ea76541','2026-09-22 18:21:30','2026-08-23 18:21:30'),(53,1,'bf40454f1be872da28c9626407e652bb857bcfabcaf498edc0c2e5459c3408c2','2026-09-22 18:21:57','2026-08-23 18:21:57'),(55,1,'f4337d72cd4528b6e2cbff197267ef87f8ebc77fe32686234672cd1dca99aa9e','2026-10-18 11:44:53','2026-09-18 11:44:53'),(56,1,'bc0d35a1771366171fb408e78c3fb1d0a91acc35a130657dcf1fd59849b43238','2026-10-18 16:26:23','2026-09-18 16:26:23'),(57,1,'848cc0fc7c31f441c057d3d5693202e1c5c9c96db155a9c54dd6be7579426cdc','2026-10-18 16:56:12','2026-09-18 16:56:12'),(58,1,'2fabce6ed50c58e7814f6e0699badc432a4f3f530e62b5603773abf126275358','2026-10-19 15:36:36','2026-09-19 15:36:36'),(59,1,'cc61593faa5469ebbc71058aaaf6f08a1354a0170b446c44d2883cca6f0f427f','2026-10-19 16:36:53','2026-09-19 16:36:53');
/*!40000 ALTER TABLE `chards_remember_tokens` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `chards_story_finds`
--

DROP TABLE IF EXISTS `chards_story_finds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_story_finds` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `card_id` int unsigned NOT NULL,
  `finder_name` varchar(128) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `finder_user_id` int unsigned DEFAULT NULL,
  `finder_ip` varchar(45) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `found_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_story_find_card` (`card_id`),
  KEY `idx_story_find_user` (`finder_user_id`),
  CONSTRAINT `fk_story_find_card` FOREIGN KEY (`card_id`) REFERENCES `chards_cards` (`id`) ON DELETE RESTRICT ON UPDATE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=215 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_story_finds`
--

LOCK TABLES `chards_story_finds` WRITE;
/*!40000 ALTER TABLE `chards_story_finds` DISABLE KEYS */;
INSERT INTO `chards_story_finds` VALUES (213,12,'testMelca',NULL,NULL,'2026-08-02 07:51:24'),(214,13,'testUser',NULL,NULL,'2026-09-19 17:21:57');
/*!40000 ALTER TABLE `chards_story_finds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Temporary view structure for view `chards_story_page`
--

DROP TABLE IF EXISTS `chards_story_page`;
/*!50001 DROP VIEW IF EXISTS `chards_story_page`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `chards_story_page` AS SELECT 
 1 AS `card_id`,
 1 AS `chapter_id`,
 1 AS `chapter_name`,
 1 AS `panel_page`,
 1 AS `panel_number`,
 1 AS `card_name`,
 1 AS `image_url`,
 1 AS `is_available`,
 1 AS `finder_name`,
 1 AS `finder_user_id`,
 1 AS `found_at`,
 1 AS `is_found`*/;
SET character_set_client = @saved_cs_client;

--
-- Temporary view structure for view `chards_user_pack_status`
--

DROP TABLE IF EXISTS `chards_user_pack_status`;
/*!50001 DROP VIEW IF EXISTS `chards_user_pack_status`*/;
SET @saved_cs_client     = @@character_set_client;
/*!50503 SET character_set_client = utf8mb4 */;
/*!50001 CREATE VIEW `chards_user_pack_status` AS SELECT 
 1 AS `user_id`,
 1 AS `username`,
 1 AS `last_free_pack_at`,
 1 AS `next_free_pack_at`,
 1 AS `free_pack_available`*/;
SET character_set_client = @saved_cs_client;

--
-- Table structure for table `chards_users`
--

DROP TABLE IF EXISTS `chards_users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `chards_users` (
  `id` int unsigned NOT NULL AUTO_INCREMENT,
  `username` varchar(32) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `password_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci NOT NULL,
  `pin_hash` varchar(255) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `is_active` tinyint(1) NOT NULL DEFAULT '0',
  `confirm_token` varchar(64) CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT NULL,
  `confirm_token_expiry` timestamp NULL DEFAULT NULL,
  `email_verified` tinyint(1) NOT NULL DEFAULT '0',
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_username` (`username`),
  UNIQUE KEY `uq_email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `chards_users`
--

LOCK TABLES `chards_users` WRITE;
/*!40000 ALTER TABLE `chards_users` DISABLE KEYS */;
INSERT INTO `chards_users` VALUES (1,'testUser','$2y$10$c3OKqc/eUY3DAhJXhcUmYeuoNUAuOjzyETKZZKgL/qMsGvvM3A5rC','test@test.test',NULL,'2026-05-18 20:20:09','2026-07-25 10:16:21',1,NULL,NULL,1),(2,'testCard','$2y$10$asVIG/vCY/mwkyPp3RZ4H.P0ZbjIGGqZhbEE6QkrdBWxatN8xbE6W','niphrentil@gmail.com',NULL,'2026-05-21 06:35:41','2026-07-25 10:16:21',1,NULL,NULL,1),(13,'testMelca','$2y$10$p7XdidR8ow90eFweIEZFbeimNqDIrouQYIOfETikJgkRdEfPT3mkS','melcalaniye@gmail.com',NULL,'2026-07-25 15:30:53','2026-07-25 15:34:20',1,NULL,NULL,1),(14,'testNiph','$2y$10$t46R2/gdxAPNxvtep7Z1NeRfvb8MIbLpbX3iYzUKQzqpAL5LV2ZV2','niphrentil@hotmail.com',NULL,'2026-07-25 22:40:09','2026-07-25 22:40:09',1,'7d50edc8a607179dde7d5d47fc9541493311c0e5b797800f5d5f8fb86f5ef78b','2026-07-26 22:40:09',0),(15,'Phoenix','$2y$10$4DuzjI65//axugskBYvloeUQySAC537xuEcWJx4Ks/6p/ALXa.ZZW','ph03nix.ag@gmail.com',NULL,'2026-07-30 16:43:08','2026-07-30 16:43:31',1,NULL,NULL,1),(16,'Konstantinos_','$2y$10$YczFMnvEZtZBn5YllFt6weGUxlyG4074zPiq3XSMYEEMlDTAasZFe','gringform1@gmail.com',NULL,'2026-08-01 11:19:25','2026-08-01 11:21:26',1,NULL,NULL,1);
/*!40000 ALTER TABLE `chards_users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Final view structure for view `chards_cards_full`
--

/*!50001 DROP VIEW IF EXISTS `chards_cards_full`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`nf_user`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `chards_cards_full` AS select `c`.`id` AS `id`,`c`.`name` AS `name`,`c`.`description` AS `description`,`c`.`flavour_text` AS `flavour_text`,`c`.`card_type` AS `card_type`,`c`.`chapter_id` AS `chapter_id`,`c`.`card_number` AS `card_number`,`c`.`panel_page` AS `panel_page`,`c`.`panel_number` AS `panel_number`,`c`.`rarity_id` AS `rarity_id`,`c`.`is_unique` AS `is_unique`,`c`.`print_run` AS `print_run`,`c`.`image_url` AS `image_url`,`c`.`artist` AS `artist`,`c`.`event_id` AS `event_id`,`c`.`enters_packs_at` AS `enters_packs_at`,`c`.`is_available` AS `is_available`,`c`.`is_active` AS `is_active`,`c`.`created_at` AS `created_at`,`c`.`updated_at` AS `updated_at`,`c`.`base_card_id` AS `base_card_id`,`c`.`upgrade_cost` AS `upgrade_cost`,`r`.`slug` AS `rarity_slug`,`r`.`label` AS `rarity_label`,`r`.`border_url` AS `rarity_border_url`,`r`.`drop_weight` AS `rarity_drop_weight`,`r`.`sort_order` AS `rarity_sort_order`,`r`.`color_hex` AS `rarity_color_hex`,`ch`.`name` AS `chapter_name`,`ch`.`slug` AS `chapter_slug` from ((`chards_cards` `c` join `chards_rarities` `r` on((`r`.`id` = `c`.`rarity_id`))) join `chards_chapters` `ch` on((`ch`.`id` = `c`.`chapter_id`))) where (`c`.`is_active` = 1) */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `chards_story_page`
--

/*!50001 DROP VIEW IF EXISTS `chards_story_page`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`nf_user`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `chards_story_page` AS select `c`.`id` AS `card_id`,`c`.`chapter_id` AS `chapter_id`,`ch`.`name` AS `chapter_name`,`c`.`panel_page` AS `panel_page`,`c`.`panel_number` AS `panel_number`,`c`.`name` AS `card_name`,`c`.`image_url` AS `image_url`,`c`.`is_available` AS `is_available`,`sf`.`finder_name` AS `finder_name`,`sf`.`finder_user_id` AS `finder_user_id`,`sf`.`found_at` AS `found_at`,(case when (`sf`.`id` is not null) then 1 else 0 end) AS `is_found` from ((`chards_cards` `c` join `chards_chapters` `ch` on((`ch`.`id` = `c`.`chapter_id`))) left join `chards_story_finds` `sf` on((`sf`.`card_id` = `c`.`id`))) where ((`c`.`card_type` = 'story') and (`c`.`is_active` = 1)) order by `c`.`chapter_id`,`c`.`panel_page`,`c`.`panel_number` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;

--
-- Final view structure for view `chards_user_pack_status`
--

/*!50001 DROP VIEW IF EXISTS `chards_user_pack_status`*/;
/*!50001 SET @saved_cs_client          = @@character_set_client */;
/*!50001 SET @saved_cs_results         = @@character_set_results */;
/*!50001 SET @saved_col_connection     = @@collation_connection */;
/*!50001 SET character_set_client      = utf8mb4 */;
/*!50001 SET character_set_results     = utf8mb4 */;
/*!50001 SET collation_connection      = utf8mb4_0900_ai_ci */;
/*!50001 CREATE ALGORITHM=UNDEFINED */
/*!50013 DEFINER=`nf_user`@`%` SQL SECURITY DEFINER */
/*!50001 VIEW `chards_user_pack_status` AS select `u`.`id` AS `user_id`,`u`.`username` AS `username`,max((case when (`pl`.`fragmenta_id` is null) then `pl`.`opened_at` end)) AS `last_free_pack_at`,(max((case when (`pl`.`fragmenta_id` is null) then `pl`.`opened_at` end)) + interval 1440 minute) AS `next_free_pack_at`,(case when ((max((case when (`pl`.`fragmenta_id` is null) then `pl`.`opened_at` end)) is null) or (max((case when (`pl`.`fragmenta_id` is null) then `pl`.`opened_at` end)) < (now() - interval 1440 minute))) then 1 else 0 end) AS `free_pack_available` from (`chards_users` `u` left join `chards_pack_log` `pl` on((`pl`.`user_id` = `u`.`id`))) group by `u`.`id`,`u`.`username` */;
/*!50001 SET character_set_client      = @saved_cs_client */;
/*!50001 SET character_set_results     = @saved_cs_results */;
/*!50001 SET collation_connection      = @saved_col_connection */;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-09-19 17:33:28
