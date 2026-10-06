-- MySQL dump 10.13  Distrib 8.4.7, for Win64 (x86_64)
--
-- Host: localhost    Database: feedback_system
-- ------------------------------------------------------
-- Server version	8.4.7

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
-- Current Database: `feedback_system`
--

CREATE DATABASE /*!32312 IF NOT EXISTS*/ `feedback_system` /*!40100 DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci */ /*!80016 DEFAULT ENCRYPTION='N' */;

USE `feedback_system`;

--
-- Table structure for table `admins`
--

DROP TABLE IF EXISTS `admins`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `admins` (
  `admin_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`admin_id`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `admins`
--

LOCK TABLES `admins` WRITE;
/*!40000 ALTER TABLE `admins` DISABLE KEYS */;
INSERT INTO `admins` VALUES (1,'Admin','admin@mail.com','$2y$10$jahntc57f8X47qdmEDplue4Y8MLrczaoS6l348aOEv1abnTI6mfvS','2026-07-08 16:24:15');
/*!40000 ALTER TABLE `admins` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `answers`
--

DROP TABLE IF EXISTS `answers`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `answers` (
  `answer_id` int NOT NULL AUTO_INCREMENT,
  `response_id` int NOT NULL,
  `question_id` int NOT NULL,
  `answer_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`answer_id`),
  KEY `response_id` (`response_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `fk_answers_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_answers_response` FOREIGN KEY (`response_id`) REFERENCES `responses` (`response_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=636 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `answers`
--

LOCK TABLES `answers` WRITE;
/*!40000 ALTER TABLE `answers` DISABLE KEYS */;
INSERT INTO `answers` VALUES (1,36,49,'3'),(2,36,50,'No'),(3,36,51,'3'),(4,36,52,'Yes'),(5,36,53,'Good experience.'),(6,35,44,'5'),(7,35,45,'No'),(8,35,46,'4'),(9,35,47,'No'),(10,35,48,'Prices are reasonable.'),(11,34,44,'4'),(12,34,45,'No'),(13,34,46,'3'),(14,34,47,'No'),(15,34,48,'Prices are reasonable.'),(16,33,39,'4'),(17,33,40,'Beverages'),(18,33,41,'5'),(19,33,42,'Yes'),(20,33,43,'Keep up the good work.'),(21,32,39,'3'),(22,32,40,'Beverages'),(23,32,41,'5'),(24,32,42,'Yes'),(25,32,43,'Keep up the good work.'),(26,31,34,'4'),(27,31,35,'Shopping'),(28,31,36,'4'),(29,31,37,'Yes'),(30,31,38,'Good experience.'),(31,30,34,'4'),(32,30,35,'Payments'),(33,30,36,'5'),(34,30,37,'No'),(35,30,38,'I will recommend this.'),(36,29,34,'4'),(37,29,35,'Notifications'),(38,29,36,'5'),(39,29,37,'No'),(40,29,38,'Could be improved.'),(41,28,29,'4'),(42,28,30,'Mobile Phone'),(43,28,31,'3'),(44,28,32,'Yes'),(45,28,33,'Good experience.'),(46,27,29,'5'),(47,27,30,'Desktop'),(48,27,31,'5'),(49,27,32,'No'),(50,27,33,'Prices are reasonable.'),(51,26,29,'4'),(52,26,30,'Tablet'),(53,26,31,'5'),(54,26,32,'Yes'),(55,26,33,'Everything was great.'),(56,25,24,'5'),(57,25,25,'Beverages'),(58,25,26,'5'),(59,25,27,'Yes'),(60,25,28,'Everything was great.'),(61,24,24,'4'),(62,24,25,'Groceries'),(63,24,26,'5'),(64,24,27,'No'),(65,24,28,'Very satisfied.'),(66,23,24,'3'),(67,23,25,'Groceries'),(68,23,26,'5'),(69,23,27,'Yes'),(70,23,28,'Excellent service.'),(71,22,19,'5'),(72,22,20,'3'),(73,22,21,'No'),(74,22,22,'4'),(75,22,23,'I will recommend this.'),(76,21,19,'5'),(77,21,20,'4'),(78,21,21,'Yes'),(79,21,22,'3'),(80,21,23,'Good experience.'),(81,20,19,'3'),(82,20,20,'5'),(83,20,21,'No'),(84,20,22,'3'),(85,20,23,'Excellent service.'),(86,37,49,'3'),(87,37,50,'Yes'),(88,37,51,'5'),(89,37,52,'No'),(90,37,53,'Excellent service.'),(91,38,64,'5'),(92,38,65,'No'),(93,38,66,'4'),(94,38,67,'5'),(95,38,68,'I will recommend this.'),(96,39,64,'5'),(97,39,65,'No'),(98,39,66,'3'),(99,39,67,'3'),(100,39,68,'Could be improved.'),(101,40,19,'4'),(102,40,20,'3'),(103,40,21,'No'),(104,40,22,'3'),(105,40,23,'Prices are reasonable.'),(106,41,19,'3'),(107,41,20,'4'),(108,41,21,'No'),(109,41,22,'3'),(110,41,23,'Keep up the good work.'),(111,42,24,'3'),(112,42,25,'Personal Care'),(113,42,26,'4'),(114,42,27,'Yes'),(115,42,28,'Prices are reasonable.'),(116,43,24,'3'),(117,43,25,'Beverages'),(118,43,26,'3'),(119,43,27,'No'),(120,43,28,'Keep up the good work.'),(121,44,19,'3'),(122,44,20,'3'),(123,44,21,'No'),(124,44,22,'4'),(125,44,23,'Everything was great.'),(126,45,29,'4'),(127,45,30,'Mobile Phone'),(128,45,31,'3'),(129,45,32,'No'),(130,45,33,'Could be improved.'),(131,46,39,'4'),(132,46,40,'Beverages'),(133,46,41,'5'),(134,46,42,'Yes'),(135,46,43,'Prices are reasonable.'),(136,47,64,'5'),(137,47,65,'Maybe'),(138,47,66,'4'),(139,47,67,'3'),(140,47,68,'Could be improved.'),(141,48,29,'4'),(142,48,30,'Tablet'),(143,48,31,'4'),(144,48,32,'No'),(145,48,33,'I will recommend this.'),(146,49,34,'3'),(147,49,35,'Notifications'),(148,49,36,'3'),(149,49,37,'Yes'),(150,49,38,'Very satisfied.'),(151,50,39,'4'),(152,50,40,'Frozen Foods'),(153,50,41,'5'),(154,50,42,'No'),(155,50,43,'I will recommend this.'),(156,51,49,'3'),(157,51,50,'No'),(158,51,51,'3'),(159,51,52,'No'),(160,51,53,'Could be improved.'),(161,52,64,'3'),(162,52,65,'Maybe'),(163,52,66,'4'),(164,52,67,'5'),(165,52,68,'Very satisfied.'),(166,53,29,'5'),(167,53,30,'Tablet'),(168,53,31,'4'),(169,53,32,'No'),(170,53,33,'Friendly staff.'),(171,54,34,'5'),(172,54,35,'Account Management'),(173,54,36,'4'),(174,54,37,'Yes'),(175,54,38,'Friendly staff.'),(176,55,39,'3'),(177,55,40,'Beverages'),(178,55,41,'4'),(179,55,42,'No'),(180,55,43,'Keep up the good work.'),(181,56,44,'5'),(182,56,45,'No'),(183,56,46,'4'),(184,56,47,'Yes'),(185,56,48,'Prices are reasonable.'),(186,57,49,'3'),(187,57,50,'Yes'),(188,57,51,'3'),(189,57,52,'No'),(190,57,53,'Keep up the good work.'),(191,58,64,'5'),(192,58,65,'Yes'),(193,58,66,'4'),(194,58,67,'3'),(195,58,68,'Fast service.'),(196,59,44,'3'),(197,59,45,'No'),(198,59,46,'4'),(199,59,47,'No'),(200,59,48,'Friendly staff.'),(201,60,34,'5'),(202,60,35,'Notifications'),(203,60,36,'4'),(204,60,37,'No'),(205,60,38,'a cart'),(206,61,24,'3'),(207,61,25,'Groceries'),(208,61,26,'2'),(209,61,27,'Yes'),(210,61,28,'aaa'),(211,62,44,'2'),(212,62,45,'Yes'),(213,62,46,'4'),(214,62,47,'Yes'),(215,62,48,'zzz'),(234,68,23,'ssssssssss'),(235,68,22,'3'),(236,68,21,'Yes.'),(237,68,20,'3'),(238,68,19,'2'),(243,70,136,'5'),(244,70,137,'Customer Support'),(245,70,138,'4'),(246,70,139,'Weekly'),(247,70,140,'Loved it!'),(248,71,141,'5'),(249,71,142,'Yes, completely'),(250,71,143,'5'),(251,71,144,'Accessories'),(252,71,145,'Could be better.'),(253,72,146,'5'),(254,72,147,'Android'),(255,72,148,'4'),(256,72,149,'Sometimes'),(257,72,150,'Great service!'),(258,73,156,'Definitely'),(259,73,157,'1'),(260,73,158,'$10/mo'),(261,73,159,'1'),(262,73,160,'Excellent quality.'),(263,74,141,'3'),(264,74,142,'Yes, completely'),(265,74,143,'5'),(266,74,144,'Apparel'),(267,74,145,'Could be better.'),(268,75,146,'3'),(269,75,147,'Android'),(270,75,148,'1'),(271,75,149,'Sometimes'),(272,75,150,'Loved it!'),(273,76,151,'2'),(274,76,152,'Significant damage'),(275,76,153,'4'),(276,76,154,'No'),(277,76,155,'Excellent quality.'),(278,77,156,'Maybe'),(279,77,157,'5'),(280,77,158,'$20/mo'),(281,77,159,'4'),(282,77,160,'Great service!'),(283,78,136,'5'),(284,78,137,'Website'),(285,78,138,'3'),(286,78,139,'Daily'),(287,78,140,'Will recommend to friends.'),(288,79,141,'3'),(289,79,142,'Yes, completely'),(290,79,143,'3'),(291,79,144,'Home & Garden'),(292,79,145,'Very happy overall.'),(293,80,146,'2'),(294,80,147,'Other'),(295,80,148,'4'),(296,80,149,'Sometimes'),(297,80,150,'Average experience.'),(298,81,151,'5'),(299,81,152,'Significant damage'),(300,81,153,'2'),(301,81,154,'No'),(302,81,155,'Fantastic!'),(303,82,156,'Definitely'),(304,82,157,'2'),(305,82,158,'Would not pay'),(306,82,159,'5'),(307,82,160,'Needs some improvement.'),(308,83,141,'2'),(309,83,142,'No'),(310,83,143,'5'),(311,83,144,'Accessories'),(312,83,145,'Excellent quality.'),(313,84,146,'3'),(314,84,147,'Other'),(315,84,148,'1'),(316,84,149,'Rarely'),(317,84,150,'Average experience.'),(318,85,151,'3'),(319,85,152,'Minor damage'),(320,85,153,'2'),(321,85,154,'Yes'),(322,85,155,'Excellent quality.'),(323,86,156,'Not interested'),(324,86,157,'4'),(325,86,158,'$5/mo'),(326,86,159,'3'),(327,86,160,'Needs some improvement.'),(328,87,136,'3'),(329,87,137,'Website'),(330,87,138,'4'),(331,87,139,'Monthly'),(332,87,140,'Loved it!'),(333,88,141,'4'),(334,88,142,'Yes, completely'),(335,88,143,'4'),(336,88,144,'Home & Garden'),(337,88,145,'Great service!'),(338,89,146,'1'),(339,89,147,'Other'),(340,89,148,'3'),(341,89,149,'Sometimes'),(342,89,150,'Will recommend to friends.'),(343,90,151,'4'),(344,90,152,'Significant damage'),(345,90,153,'2'),(346,90,154,'No'),(347,90,155,'Not satisfied with the speed.'),(348,91,156,'Maybe'),(349,91,157,'3'),(350,91,158,'$20/mo'),(351,91,159,'5'),(352,91,160,'Fantastic!'),(353,92,141,'4'),(354,92,142,'Yes, completely'),(355,92,143,'5'),(356,92,144,'Home & Garden'),(357,92,145,'Average experience.'),(358,93,151,'4'),(359,93,152,'Minor damage'),(360,93,153,'3'),(361,93,154,'No'),(362,93,155,'Needs some improvement.'),(363,94,136,'3'),(364,94,137,'Customer Support'),(365,94,138,'2'),(366,94,139,'Monthly'),(367,94,140,'Needs some improvement.'),(368,95,156,'Not interested'),(369,95,157,'4'),(370,95,158,'Would not pay'),(371,95,159,'3'),(372,95,160,'Fantastic!'),(373,96,141,'3'),(374,96,142,'Somewhat'),(375,96,143,'4'),(376,96,144,'Electronics'),(377,96,145,'Fantastic!'),(378,97,146,'2'),(379,97,147,'Other'),(380,97,148,'4'),(381,97,149,'Rarely'),(382,97,150,'Needs some improvement.'),(383,98,151,'4'),(384,98,152,'Minor damage'),(385,98,153,'2'),(386,98,154,'No'),(387,98,155,'Great service!'),(388,99,141,'3'),(389,99,142,'Yes, completely'),(390,99,143,'1'),(391,99,144,'Accessories'),(392,99,145,'Average experience.'),(393,100,156,'Definitely'),(394,100,157,'3'),(395,100,158,'$5/mo'),(396,100,159,'3'),(397,100,160,'Average experience.'),(398,101,136,'1'),(399,101,137,'Customer Support'),(400,101,138,'3'),(401,101,139,'Daily'),(402,101,140,'Very happy overall.'),(403,102,146,'3'),(404,102,147,'Android'),(405,102,148,'1'),(406,102,149,'Sometimes'),(407,102,150,'Excellent quality.'),(408,103,151,'4'),(409,103,152,'Minor damage'),(410,103,153,'3'),(411,103,154,'Yes'),(412,103,155,'Average experience.'),(413,104,156,'Maybe'),(414,104,157,'3'),(415,104,158,'Would not pay'),(416,104,159,'3'),(417,104,160,'Not satisfied with the speed.'),(418,105,136,'5'),(419,105,137,'Website'),(420,105,138,'5'),(421,105,139,'Rarely'),(422,105,140,'Not satisfied with the speed.'),(423,106,146,'5'),(424,106,147,'Other'),(425,106,148,'5'),(426,106,149,'Rarely'),(427,106,150,'Needs some improvement.'),(428,107,151,'3'),(429,107,152,'Minor damage'),(430,107,153,'2'),(431,107,154,'No'),(432,107,155,'Great service!'),(433,108,156,'Maybe'),(434,108,157,'4'),(435,108,158,'$5/mo'),(436,108,159,'4'),(437,108,160,'Excellent quality.'),(438,109,146,'3'),(439,109,147,'Android'),(440,109,148,'3'),(441,109,149,'Rarely'),(442,109,150,'Excellent quality.'),(443,110,151,'4'),(444,110,152,'Significant damage'),(445,110,153,'3'),(446,110,154,'No'),(447,110,155,'Will recommend to friends.'),(448,111,136,'4'),(449,111,137,'Mobile App'),(450,111,138,'3'),(451,111,139,'Daily'),(452,111,140,'Not satisfied with the speed.'),(453,112,156,'Not interested'),(454,112,157,'5'),(455,112,158,'$5/mo'),(456,112,159,'3'),(457,112,160,'Great service!'),(458,113,136,'4'),(459,113,137,'Website'),(460,113,138,'5'),(461,113,139,'Rarely'),(462,113,140,'Loved it!'),(463,114,151,'1'),(464,114,152,'Significant damage'),(465,114,153,'4'),(466,114,154,'Yes'),(467,114,155,'Very happy overall.'),(468,115,156,'Maybe'),(469,115,157,'4'),(470,115,158,'$10/mo'),(471,115,159,'3'),(472,115,160,'Very happy overall.'),(473,116,136,'1'),(474,116,137,'Website'),(475,116,138,'4'),(476,116,139,'Monthly'),(477,116,140,'Very happy overall.'),(478,117,141,'4'),(479,117,142,'Yes, completely'),(480,117,143,'5'),(481,117,144,'Apparel'),(482,117,145,'Fantastic!'),(483,118,146,'5'),(484,118,147,'iOS'),(485,118,148,'3'),(486,118,149,'Sometimes'),(487,118,150,'Will recommend to friends.'),(488,119,151,'3'),(489,119,152,'Minor damage'),(490,119,153,'4'),(491,119,154,'No'),(492,119,155,'Excellent quality.'),(493,120,156,'Maybe'),(494,120,157,'3'),(495,120,158,'$10/mo'),(496,120,159,'3'),(497,120,160,'Very happy overall.'),(498,121,166,'3'),(499,121,167,'Phone'),(500,121,168,'4'),(501,121,169,'Not sure'),(502,121,170,'Pretty good.'),(503,122,161,'3'),(504,122,162,'Smartphone'),(505,122,163,'1'),(506,122,164,'Often'),(507,122,165,'Not what I expected.'),(508,123,171,'5'),(509,123,172,'Minor issues'),(510,123,173,'4'),(511,123,174,'Very difficult'),(512,123,175,'Very helpful.'),(513,124,176,'5'),(514,124,177,'Exclusive Access'),(515,124,178,'5'),(516,124,179,'Yes, definitely'),(517,124,180,'Not what I expected.'),(518,125,161,'4'),(519,125,162,'Laptop'),(520,125,163,'3'),(521,125,164,'Rarely'),(522,125,165,'Average experience.'),(523,126,161,'3'),(524,126,162,'Smartphone'),(525,126,163,'4'),(526,126,164,'Rarely'),(527,126,165,'I am satisfied overall.'),(528,127,161,'3'),(529,127,162,'Smartphone'),(530,127,163,'3'),(531,127,164,'Often'),(532,127,165,'Absolutely fantastic.'),(533,128,171,'2'),(534,128,172,'Yes, perfectly'),(535,128,173,'5'),(536,128,174,'Very easy'),(537,128,175,'I am satisfied overall.'),(538,129,176,'1'),(539,129,177,'Free Products'),(540,129,178,'5'),(541,129,179,'Maybe'),(542,129,180,'Could be much better.'),(543,130,166,'3'),(544,130,167,'Email'),(545,130,168,'2'),(546,130,169,'No'),(547,130,170,'Not what I expected.'),(548,131,161,'4'),(549,131,162,'Tablet'),(550,131,163,'3'),(551,131,164,'Rarely'),(552,131,165,'Pretty good.'),(553,132,171,'5'),(554,132,172,'Items were damaged'),(555,132,173,'1'),(556,132,174,'Very difficult'),(557,132,175,'Average experience.'),(558,133,176,'1'),(559,133,177,'Exclusive Access'),(560,133,178,'2'),(561,133,179,'No, keep it simple'),(562,133,180,'I am satisfied overall.'),(563,134,171,'4'),(564,134,172,'Yes, perfectly'),(565,134,173,'4'),(566,134,174,'Very easy'),(567,134,175,'Could be much better.'),(568,135,171,'4'),(569,135,172,'Minor issues'),(570,135,173,'5'),(571,135,174,'Moderate'),(572,135,175,'Could be much better.'),(573,136,176,'3'),(574,136,177,'Discounts'),(575,136,178,'4'),(576,136,179,'Maybe'),(577,136,180,'Absolutely fantastic.'),(578,137,166,'5'),(579,137,167,'Phone'),(580,137,168,'4'),(581,137,169,'Not sure'),(582,137,170,'Pretty good.'),(583,138,171,'5'),(584,138,172,'Yes, perfectly'),(585,138,173,'4'),(586,138,174,'Moderate'),(587,138,175,'Absolutely fantastic.'),(588,139,161,'4'),(589,139,162,'Smartphone'),(590,139,163,'3'),(591,139,164,'Often'),(592,139,165,'Average experience.'),(593,140,171,'4'),(594,140,172,'Minor issues'),(595,140,173,'3'),(596,140,174,'Very easy'),(597,140,175,'I encountered some issues.'),(598,141,161,'2'),(599,141,162,'Smartphone'),(600,141,163,'3'),(601,141,164,'Rarely'),(602,141,165,'Absolutely fantastic.'),(603,142,166,'2'),(604,142,167,'Phone'),(605,142,168,'4'),(606,142,169,'Not sure'),(607,142,170,'Not what I expected.'),(608,143,176,'5'),(609,143,177,'Discounts'),(610,143,178,'1'),(611,143,179,'No, keep it simple'),(612,143,180,'I encountered some issues.'),(613,144,161,'4'),(614,144,162,'Desktop'),(615,144,163,'2'),(616,144,164,'Rarely'),(617,144,165,'Not what I expected.'),(618,145,176,'3'),(619,145,177,'Exclusive Access'),(620,145,178,'5'),(621,145,179,'Maybe'),(622,145,180,'Pretty good.'),(633,148,195,'2'),(634,148,196,'c'),(635,148,197,'abccd');
/*!40000 ALTER TABLE `answers` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `companies`
--

DROP TABLE IF EXISTS `companies`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `companies` (
  `company_id` int NOT NULL AUTO_INCREMENT,
  `company_name` varchar(150) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`company_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `companies`
--

LOCK TABLES `companies` WRITE;
/*!40000 ALTER TABLE `companies` DISABLE KEYS */;
INSERT INTO `companies` VALUES (1,'ABC company','abc@mail.com','$2y$10$2aCyTYj0AlHb3lP3vt0VyOz7JhhmGcc6MdG/VVdh0tW5S5t8PLVYa','2026-07-08 16:32:28','active'),(2,'NovaTech Solutions','tech@mail.com','$2y$10$2aCyTYj0AlHb3lP3vt0VyOz7JhhmGcc6MdG/VVdh0tW5S5t8PLVYa','2026-07-08 16:32:28','active'),(3,'Green Foods Lanka','green@mail.com','$2y$10$2aCyTYj0AlHb3lP3vt0VyOz7JhhmGcc6MdG/VVdh0tW5S5t8PLVYa','2026-07-08 16:32:28','active'),(4,'CityCare Pharmacy','city@mail.com','$2y$10$2aCyTYj0AlHb3lP3vt0VyOz7JhhmGcc6MdG/VVdh0tW5S5t8PLVYa','2026-07-08 16:32:28','active'),(5,'abcd Company','abcd@mail.com','$2y$10$vzuX2j4KVYYlx4B3wLcmUuyJFrmNWGjN/QkP5EQkQrjTixGIcfVbm','2026-07-15 04:19:47','inactive'),(6,'Surasa productions','surasa@mail.com','$2y$10$ynJycQtGKaNRyWeV5nAIRO8AIS20j/qi7TXABU/p6tcnSMH5tO.j2','2026-07-16 07:50:52','active'),(7,'newCompany','newCompany@mail.com','$2y$10$k3tBmVStb0YjMs4X8bbb5.PT0418hzWR9lYBxhkNNWiXiPWr037SC','2026-07-19 08:10:55','active'),(8,'newCompany2','newCompany2@mail.com','$2y$10$aNNf5H3/hNsi9RO5cbivc.p3evLrU8.9zdcYkDe5x5izcKHwJgy0.','2026-07-20 16:48:29','active');
/*!40000 ALTER TABLE `companies` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `options`
--

DROP TABLE IF EXISTS `options`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `options` (
  `option_id` int NOT NULL AUTO_INCREMENT,
  `question_id` int NOT NULL,
  `option_text` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`option_id`),
  KEY `question_id` (`question_id`),
  CONSTRAINT `fk_options_question` FOREIGN KEY (`question_id`) REFERENCES `questions` (`question_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=109 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `options`
--

LOCK TABLES `options` WRITE;
/*!40000 ALTER TABLE `options` DISABLE KEYS */;
INSERT INTO `options` VALUES (1,21,'Yes.'),(2,21,'No'),(3,25,'Beverages'),(4,25,'Groceries'),(5,25,'Household Items'),(6,25,'Personal Care'),(7,27,'Yes'),(8,27,'No'),(9,30,'Desktop'),(10,30,'Laptop'),(11,30,'Mobile Phone'),(12,30,'Tablet'),(13,32,'Yes'),(14,32,'No'),(15,35,'Shopping'),(16,35,'Payments'),(17,35,'Account Management'),(18,35,'Notifications'),(19,37,'Yes'),(20,37,'No'),(21,40,'Snacks'),(22,40,'Beverages'),(23,40,'Frozen Foods'),(24,40,'Dairy Products'),(25,42,'Yes'),(26,42,'No'),(27,45,'Yes'),(28,45,'No'),(29,47,'Yes'),(30,47,'No'),(31,50,'Yes'),(32,50,'No'),(33,52,'Yes'),(34,52,'No'),(35,65,'Yes'),(36,65,'Maybe'),(37,65,'No'),(38,21,'Maybe'),(43,137,'Mobile App'),(44,137,'Website'),(45,137,'In-Store'),(46,137,'Customer Support'),(47,139,'Daily'),(48,139,'Weekly'),(49,139,'Monthly'),(50,139,'Rarely'),(51,142,'Yes, completely'),(52,142,'Somewhat'),(53,142,'No'),(54,144,'Electronics'),(55,144,'Home & Garden'),(56,144,'Apparel'),(57,144,'Accessories'),(58,147,'iOS'),(59,147,'Android'),(60,147,'Other'),(61,149,'Never'),(62,149,'Rarely'),(63,149,'Sometimes'),(64,149,'Often'),(65,152,'Yes, perfect'),(66,152,'Minor damage'),(67,152,'Significant damage'),(68,154,'Yes'),(69,154,'No'),(70,156,'Definitely'),(71,156,'Maybe'),(72,156,'Not interested'),(73,158,'$5/mo'),(74,158,'$10/mo'),(75,158,'$20/mo'),(76,158,'Would not pay'),(77,162,'Desktop'),(78,162,'Laptop'),(79,162,'Tablet'),(80,162,'Smartphone'),(81,164,'Never'),(82,164,'Rarely'),(83,164,'Often'),(84,167,'Email'),(85,167,'Phone'),(86,167,'Live Chat'),(87,167,'Social Media'),(88,169,'Yes'),(89,169,'No'),(90,169,'Not sure'),(91,172,'Yes, perfectly'),(92,172,'Minor issues'),(93,172,'Items were damaged'),(94,174,'Very easy'),(95,174,'Moderate'),(96,174,'Very difficult'),(97,177,'Discounts'),(98,177,'Free Products'),(99,177,'Exclusive Access'),(100,177,'Gift Cards'),(101,179,'Yes, definitely'),(102,179,'Maybe'),(103,179,'No, keep it simple'),(104,196,'a'),(105,196,'b'),(106,196,'c'),(107,196,'d'),(108,196,'e');
/*!40000 ALTER TABLE `options` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `questions`
--

DROP TABLE IF EXISTS `questions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `questions` (
  `question_id` int NOT NULL AUTO_INCREMENT,
  `survey_id` int NOT NULL,
  `question_text` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `question_type` enum('mcq','rating','text') COLLATE utf8mb4_unicode_ci NOT NULL,
  PRIMARY KEY (`question_id`),
  KEY `survey_id` (`survey_id`),
  CONSTRAINT `fk_questions_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=198 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `questions`
--

LOCK TABLES `questions` WRITE;
/*!40000 ALTER TABLE `questions` DISABLE KEYS */;
INSERT INTO `questions` VALUES (19,1,'How satisfied are you with your overall shopping experience?','rating'),(20,1,'How would you rate the friendliness of our staff?','rating'),(21,1,'Were the products you needed available?','mcq'),(22,1,'How clean and organized was the store?','rating'),(23,1,'Please share any suggestions to improve our supermarket services.','text'),(24,2,'How often do you find the products you are looking for.?','rating'),(25,2,'Which product category do you purchase most frequently?','mcq'),(26,2,'How satisfied are you with product variety?','rating'),(27,2,'Were out-of-stock products a problem during your visit?','mcq'),(28,2,'What products would you like us to add?','text'),(29,3,'How easy was it to navigate our website?','rating'),(30,3,'Which device did you use to access our website?','mcq'),(31,3,'How satisfied are you with the website loading speed?','rating'),(32,3,'Did you find the information you were looking for?','mcq'),(33,3,'Please share any suggestions to improve our website.','text'),(34,4,'How satisfied are you with our mobile application?','rating'),(35,4,'Which feature do you use the most?','mcq'),(36,4,'How easy is it to use the application?','rating'),(37,4,'Did you experience any technical issues?','mcq'),(38,4,'What improvements would you like to see in the app?','text'),(39,5,'How would you rate the overall quality of our products?','rating'),(40,5,'Which product do you purchase most often?','mcq'),(41,5,'Are you satisfied with the freshness of our products?','rating'),(42,5,'Would you recommend our products to others?','mcq'),(43,5,'Please provide any comments about product quality.','text'),(44,6,'How satisfied are you with our delivery service?','rating'),(45,6,'Was your order delivered on time?','mcq'),(46,6,'How would you rate the delivery staff?','rating'),(47,6,'Was your package received in good condition?','mcq'),(48,6,'How can we improve our delivery service?','text'),(49,7,'How satisfied are you with our pharmacy service?','rating'),(50,7,'Were our staff helpful and professional?','mcq'),(51,7,'How would you rate the waiting time?','rating'),(52,7,'Did you receive all the medicines you needed?','mcq'),(53,7,'Please share your feedback about our pharmacy service.','text'),(64,8,'How satisfied are you with your overall experience?','rating'),(65,8,'Would you visit us again?','mcq'),(66,8,'How likely are you to recommend us to others?','rating'),(67,8,'How would you rate our customer service?','rating'),(68,8,'Please share your suggestions for improvement.','text'),(136,26,'How would you rate your overall experience with us?','rating'),(137,26,'Which feature do you use the most?','mcq'),(138,26,'How likely are you to recommend us to a friend?','rating'),(139,26,'How often do you use our service?','mcq'),(140,26,'What can we do to improve our services in the future?','text'),(141,27,'How satisfied are you with the quality of the product?','rating'),(142,27,'Did the product meet your expectations?','mcq'),(143,27,'How would you rate the durability of the product?','rating'),(144,27,'Which product category did you purchase from?','mcq'),(145,27,'Please describe any issues you faced with the product.','text'),(146,28,'How easy is it to navigate the mobile app?','rating'),(147,28,'Which operating system are you using?','mcq'),(148,28,'How would you rate the speed and performance of the app?','rating'),(149,28,'Have you experienced any crashes?','mcq'),(150,28,'What new features would you like to see in the next update?','text'),(151,29,'How satisfied are you with the delivery time?','rating'),(152,29,'Was the package delivered in good condition?','mcq'),(153,29,'How would you rate the friendliness of the delivery staff?','rating'),(154,29,'Did you track your package online?','mcq'),(155,29,'Any additional comments about your delivery experience?','text'),(156,30,'Would you be interested in an eco-friendly product line?','mcq'),(157,30,'How important is sustainable packaging to you?','rating'),(158,30,'How much would you pay for premium support?','mcq'),(159,30,'Rate your interest in participating in beta tests.','rating'),(160,30,'What is the biggest problem our products can solve for you?','text'),(161,31,'How easy is it to find what you are looking for?','rating'),(162,31,'What device do you primarily use to browse our site?','mcq'),(163,31,'How visually appealing is our website?','rating'),(164,31,'Have you ever experienced broken links or errors?','mcq'),(165,31,'What is one feature you would add to our website?','text'),(166,32,'How satisfied are you with the resolution provided?','rating'),(167,32,'Which channel did you use to contact support?','mcq'),(168,32,'Rate the friendliness of the support representative.','rating'),(169,32,'Was your issue resolved in the first interaction?','mcq'),(170,32,'How can our support team serve you better in the future?','text'),(171,33,'How would you rate the durability of the packaging?','rating'),(172,33,'Did the packaging protect your items adequately?','mcq'),(173,33,'How eco-friendly do you consider our packaging?','rating'),(174,33,'How difficult was it to open the package?','mcq'),(175,33,'Do you have any suggestions for better packaging?','text'),(176,34,'How satisfied are you with the current rewards offered?','rating'),(177,34,'What type of rewards do you prefer?','mcq'),(178,34,'How easy is it to earn points?','rating'),(179,34,'Would you prefer a tiered loyalty system (e.g., Gold, Platinum)?','mcq'),(180,34,'What is the number one reason you participate in loyalty programs?','text'),(195,38,'w','rating'),(196,38,'e','mcq'),(197,38,'q','text');
/*!40000 ALTER TABLE `questions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `redemptions`
--

DROP TABLE IF EXISTS `redemptions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `redemptions` (
  `redemption_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `reward_id` int NOT NULL,
  `code` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `status` enum('unused','used') CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci DEFAULT 'unused',
  `redeemed_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`redemption_id`),
  KEY `user_id` (`user_id`),
  KEY `reward_id` (`reward_id`),
  CONSTRAINT `fk_redemptions_reward` FOREIGN KEY (`reward_id`) REFERENCES `rewards` (`reward_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_redemptions_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `redemptions`
--

LOCK TABLES `redemptions` WRITE;
/*!40000 ALTER TABLE `redemptions` DISABLE KEYS */;
INSERT INTO `redemptions` VALUES (1,1,1,'ABC-5OFF-1001','unused','2026-07-03 04:45:00'),(2,2,7,'GREEN-250-1002','unused','2026-07-04 05:50:00'),(3,3,2,'ABC-500-1003','unused','2026-07-05 04:10:00'),(4,4,10,'CITY-350-1004','unused','2026-07-05 20:40:00'),(5,5,4,'TECH-300-1005','unused','2026-07-06 22:00:00'),(6,6,8,'GREEN-600-1006','unused','2026-07-08 02:55:00'),(7,7,5,'TECH-700-1007','unused','2026-07-08 05:15:00'),(8,8,11,'CITY-650-1008','unused','2026-07-09 04:05:00'),(9,9,1,'ABC-5OFF-1009','unused','2026-07-09 06:20:00'),(10,10,7,'GREEN-250-1010','unused','2026-07-10 06:45:00'),(11,11,10,'CITY-350-1011','unused','2026-07-09 21:50:00'),(12,12,4,'TECH-300-1012','unused','2026-07-11 03:40:00'),(16,15,1,'6963DD95','unused','2026-07-16 09:32:58'),(17,1,7,'DA3A8B40','unused','2026-07-18 03:45:27'),(19,1,14,'0424D931','unused','2026-07-18 07:08:49'),(20,1,4,'CECA033C','unused','2026-07-18 07:22:30'),(21,1,16,'FBA25AA384','unused','2026-07-18 08:32:59'),(22,2,18,'6C050691CD','unused','2026-07-18 08:32:59'),(23,3,16,'F484AC7FB0','unused','2026-07-18 08:32:59'),(24,4,20,'02DCEBE0AA','unused','2026-07-18 08:32:59'),(25,4,16,'A65CA3C669','unused','2026-07-18 08:32:59'),(26,5,18,'9B7D17A408','unused','2026-07-18 08:32:59'),(27,6,16,'986A3F3D94','unused','2026-07-18 08:32:59'),(28,6,20,'271DC74DD0','unused','2026-07-18 08:32:59'),(29,7,20,'6E1CD5CD77','unused','2026-07-18 08:32:59'),(30,7,21,'1A6FD89551','unused','2026-07-18 08:32:59'),(31,8,17,'F14AA95D33','unused','2026-07-18 08:32:59'),(32,8,18,'208DCA18ED','unused','2026-07-18 08:32:59'),(33,9,20,'5F6D372B6C','unused','2026-07-18 08:32:59'),(34,10,21,'C53D8E3235','unused','2026-07-18 08:32:59'),(35,10,17,'0AEBBDACCF','unused','2026-07-18 08:32:59'),(36,11,21,'7F10D88F04','unused','2026-07-18 08:32:59'),(37,11,19,'B45204F133','unused','2026-07-18 08:32:59'),(38,12,19,'5393BC18B5','unused','2026-07-18 08:32:59'),(39,12,16,'949A1A69E2','unused','2026-07-18 08:32:59'),(40,13,17,'5201D360D4','unused','2026-07-18 08:32:59'),(41,14,17,'D7089DB0F4','unused','2026-07-18 08:32:59'),(42,15,20,'0BB768A685','unused','2026-07-18 08:32:59'),(43,1,10,'D13BBD06','unused','2026-07-19 09:32:38'),(44,1,18,'D22118D5','unused','2026-07-20 17:37:57'),(45,1,20,'643DB9D3','unused','2026-07-22 05:05:37');
/*!40000 ALTER TABLE `redemptions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `responses`
--

DROP TABLE IF EXISTS `responses`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `responses` (
  `response_id` int NOT NULL AUTO_INCREMENT,
  `user_id` int NOT NULL,
  `survey_id` int NOT NULL,
  `submitted_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`response_id`),
  UNIQUE KEY `uk_user_survey` (`user_id`,`survey_id`),
  KEY `user_id` (`user_id`),
  KEY `survey_id` (`survey_id`),
  CONSTRAINT `fk_responses_survey` FOREIGN KEY (`survey_id`) REFERENCES `surveys` (`survey_id`) ON DELETE RESTRICT,
  CONSTRAINT `fk_responses_user` FOREIGN KEY (`user_id`) REFERENCES `users` (`user_id`) ON DELETE RESTRICT
) ENGINE=InnoDB AUTO_INCREMENT=149 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `responses`
--

LOCK TABLES `responses` WRITE;
/*!40000 ALTER TABLE `responses` DISABLE KEYS */;
INSERT INTO `responses` VALUES (20,1,1,'2026-07-01 03:45:00'),(21,2,1,'2026-07-01 05:00:00'),(22,3,1,'2026-07-01 06:15:00'),(23,4,2,'2026-07-02 03:50:00'),(24,5,2,'2026-07-02 04:40:00'),(25,6,2,'2026-07-02 06:25:00'),(26,7,3,'2026-07-03 03:35:00'),(27,8,3,'2026-07-03 05:10:00'),(28,9,3,'2026-07-03 05:55:00'),(29,10,4,'2026-07-04 04:05:00'),(30,11,4,'2026-07-04 05:20:00'),(31,12,4,'2026-07-04 05:45:00'),(32,1,5,'2026-07-05 04:15:00'),(33,2,5,'2026-07-05 04:50:00'),(34,3,6,'2026-07-05 06:10:00'),(35,4,6,'2026-07-06 03:40:00'),(36,5,7,'2026-07-06 04:55:00'),(37,6,7,'2026-07-06 06:00:00'),(38,7,8,'2026-07-07 04:20:00'),(39,8,8,'2026-07-07 05:15:00'),(40,9,1,'2026-07-08 03:50:00'),(41,10,1,'2026-07-08 05:05:00'),(42,11,2,'2026-07-08 05:40:00'),(43,12,2,'2026-07-08 06:25:00'),(44,13,1,'2026-07-09 03:45:00'),(45,13,3,'2026-07-09 04:50:00'),(46,13,5,'2026-07-09 06:00:00'),(47,13,8,'2026-07-08 20:45:00'),(48,1,3,'2026-07-10 04:10:00'),(49,2,4,'2026-07-10 05:00:00'),(50,3,5,'2026-07-10 05:45:00'),(51,4,7,'2026-07-09 19:40:00'),(52,5,8,'2026-07-09 20:55:00'),(53,6,3,'2026-07-11 03:35:00'),(54,7,4,'2026-07-11 04:45:00'),(55,8,5,'2026-07-11 06:10:00'),(56,9,6,'2026-07-10 19:50:00'),(57,10,7,'2026-07-10 20:40:00'),(58,11,8,'2026-07-12 04:00:00'),(59,12,6,'2026-07-12 05:15:00'),(60,1,4,'2026-07-09 08:10:46'),(61,1,2,'2026-07-09 09:51:26'),(62,1,6,'2026-07-09 09:52:22'),(68,15,1,'2026-07-16 09:31:18'),(70,1,26,'2026-07-18 08:32:58'),(71,1,27,'2026-07-18 08:32:58'),(72,1,28,'2026-07-18 08:32:58'),(73,1,30,'2026-07-18 08:32:58'),(74,2,27,'2026-07-18 08:32:58'),(75,2,28,'2026-07-18 08:32:58'),(76,2,29,'2026-07-18 08:32:58'),(77,2,30,'2026-07-18 08:32:58'),(78,3,26,'2026-07-18 08:32:58'),(79,3,27,'2026-07-18 08:32:58'),(80,3,28,'2026-07-18 08:32:58'),(81,3,29,'2026-07-18 08:32:58'),(82,3,30,'2026-07-18 08:32:58'),(83,4,27,'2026-07-18 08:32:58'),(84,4,28,'2026-07-18 08:32:58'),(85,4,29,'2026-07-18 08:32:58'),(86,4,30,'2026-07-18 08:32:58'),(87,5,26,'2026-07-18 08:32:58'),(88,5,27,'2026-07-18 08:32:58'),(89,5,28,'2026-07-18 08:32:58'),(90,5,29,'2026-07-18 08:32:58'),(91,5,30,'2026-07-18 08:32:58'),(92,6,27,'2026-07-18 08:32:58'),(93,6,29,'2026-07-18 08:32:58'),(94,7,26,'2026-07-18 08:32:59'),(95,7,30,'2026-07-18 08:32:59'),(96,8,27,'2026-07-18 08:32:59'),(97,8,28,'2026-07-18 08:32:59'),(98,8,29,'2026-07-18 08:32:59'),(99,9,27,'2026-07-18 08:32:59'),(100,9,30,'2026-07-18 08:32:59'),(101,10,26,'2026-07-18 08:32:59'),(102,10,28,'2026-07-18 08:32:59'),(103,10,29,'2026-07-18 08:32:59'),(104,10,30,'2026-07-18 08:32:59'),(105,11,26,'2026-07-18 08:32:59'),(106,11,28,'2026-07-18 08:32:59'),(107,11,29,'2026-07-18 08:32:59'),(108,11,30,'2026-07-18 08:32:59'),(109,12,28,'2026-07-18 08:32:59'),(110,12,29,'2026-07-18 08:32:59'),(111,13,26,'2026-07-18 08:32:59'),(112,13,30,'2026-07-18 08:32:59'),(113,14,26,'2026-07-18 08:32:59'),(114,14,29,'2026-07-18 08:32:59'),(115,14,30,'2026-07-18 08:32:59'),(116,15,26,'2026-07-18 08:32:59'),(117,15,27,'2026-07-18 08:32:59'),(118,15,28,'2026-07-18 08:32:59'),(119,15,29,'2026-07-18 08:32:59'),(120,15,30,'2026-07-18 08:32:59'),(121,2,32,'2026-07-18 09:04:01'),(122,3,31,'2026-07-18 09:04:01'),(123,3,33,'2026-07-18 09:04:01'),(124,3,34,'2026-07-18 09:04:01'),(125,4,31,'2026-07-18 09:04:01'),(126,5,31,'2026-07-18 09:04:01'),(127,6,31,'2026-07-18 09:04:01'),(128,6,33,'2026-07-18 09:04:01'),(129,6,34,'2026-07-18 09:04:01'),(130,7,32,'2026-07-18 09:04:01'),(131,8,31,'2026-07-18 09:04:01'),(132,8,33,'2026-07-18 09:04:01'),(133,8,34,'2026-07-18 09:04:01'),(134,9,33,'2026-07-18 09:04:01'),(135,10,33,'2026-07-18 09:04:01'),(136,11,34,'2026-07-18 09:04:01'),(137,12,32,'2026-07-18 09:04:01'),(138,12,33,'2026-07-18 09:04:01'),(139,13,31,'2026-07-18 09:04:01'),(140,13,33,'2026-07-18 09:04:01'),(141,14,31,'2026-07-18 09:04:01'),(142,14,32,'2026-07-18 09:04:01'),(143,14,34,'2026-07-18 09:04:01'),(144,15,31,'2026-07-18 09:04:01'),(145,15,34,'2026-07-18 09:04:01'),(148,1,38,'2026-07-22 05:00:41');
/*!40000 ALTER TABLE `responses` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `rewards`
--

DROP TABLE IF EXISTS `rewards`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `rewards` (
  `reward_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `reward_name` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points_required` int NOT NULL,
  `status` enum('available','archived') COLLATE utf8mb4_unicode_ci DEFAULT 'available',
  PRIMARY KEY (`reward_id`),
  KEY `company_id` (`company_id`),
  KEY `idx_status` (`status`),
  CONSTRAINT `fk_rewards_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=24 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `rewards`
--

LOCK TABLES `rewards` WRITE;
/*!40000 ALTER TABLE `rewards` DISABLE KEYS */;
INSERT INTO `rewards` VALUES (1,1,'5% Discount Coupon',200,'available'),(2,1,'LKR 500 Shopping Voucher',500,'available'),(4,2,'USB Flash Drive (32GB)',300,'available'),(5,2,'Wireless Mouse',700,'available'),(6,2,'Bluetooth Speaker',1000,'available'),(7,3,'Healthy Snack Pack',250,'available'),(8,3,'Organic Food Basket',600,'available'),(9,3,'LKR 1000 Gift Voucher',900,'available'),(10,4,'Health Check Coupon',350,'available'),(11,4,'Vitamin Supplement Pack',650,'available'),(12,4,'Wellness Gift Pack',950,'archived'),(14,1,'gift vouchers',300,'available'),(16,1,'Coffee Voucher',50,'available'),(17,1,'10% Discount Coupon',100,''),(18,1,'Gift Hamper',300,'available'),(19,1,'Movie Ticket',150,''),(20,1,'Shopping Voucher',200,'available'),(21,1,'Premium Membership',250,'available'),(22,1,'tempReward1',900,'archived'),(23,1,'5% Discount Coupon2',250,'available');
/*!40000 ALTER TABLE `rewards` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `surveys`
--

DROP TABLE IF EXISTS `surveys`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `surveys` (
  `survey_id` int NOT NULL AUTO_INCREMENT,
  `company_id` int NOT NULL,
  `title` varchar(200) COLLATE utf8mb4_unicode_ci NOT NULL,
  `description` text CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci,
  `points_reward` int NOT NULL,
  `status` enum('draft','active') COLLATE utf8mb4_unicode_ci DEFAULT 'draft',
  `expiry_date` date DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`survey_id`),
  KEY `company_id` (`company_id`),
  KEY `idx_status` (`status`),
  KEY `idx_expiry` (`expiry_date`),
  CONSTRAINT `fk_surveys_company` FOREIGN KEY (`company_id`) REFERENCES `companies` (`company_id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=39 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `surveys`
--

LOCK TABLES `surveys` WRITE;
/*!40000 ALTER TABLE `surveys` DISABLE KEYS */;
INSERT INTO `surveys` VALUES (1,1,'Customer Satisfaction Survey...','Share your shopping experience at ABC Supermarket.',20,'active','2026-05-15','2026-03-12 04:30:00'),(2,1,'Product Availability Survey','Help us improve product availability in our stores.',40,'active','2026-05-15','2026-03-12 04:30:00'),(3,2,'Website User Experience Survey','Tell us about your experience using our company website.',60,'active','2026-12-31','2026-07-08 16:48:10'),(4,2,'Mobile Application Feedback','Provide feedback about our mobile application.',70,'active','2026-12-31','2026-07-08 16:48:10'),(5,3,'Product Quality Survey','Rate the quality of our food products.',55,'active','2026-12-31','2026-07-08 16:48:10'),(6,3,'Delivery Service Feedback','Help us improve our delivery service.',45,'active','2026-12-31','2026-07-08 16:48:10'),(7,4,'Pharmacy Service Evaluation','Share your experience with our pharmacy services.',50,'active','2026-12-31','2026-07-08 16:48:10'),(8,4,'Customer Experience Survey','Tell us how we can improve your overall experience.',65,'active','2026-12-31','2026-07-08 16:48:10'),(26,1,'Customer Satisfaction Survey','We want to hear about your latest experience with our services.',40,'active','2026-05-15','2026-03-12 04:30:00'),(27,1,'Product Quality Feedback','Tell us how our recent product line meets your expectations.',40,'active','2026-08-17','2026-07-18 08:32:58'),(28,1,'Mobile App Experience','Help us improve our mobile application by sharing your thoughts.',40,'active','2026-08-17','2026-07-18 08:32:58'),(29,1,'Delivery Service Feedback','Let us know how your recent delivery went.',40,'active','2026-08-17','2026-07-18 08:32:58'),(30,1,'New Product Research','We are designing new products and need your input.',40,'active','2026-08-17','2026-07-18 08:32:58'),(31,1,'Website Usability Survey','We want to understand how you use our website and how we can improve it.',40,'active','2026-09-01','2026-07-18 03:34:01'),(32,1,'Customer Support Experience','Share your thoughts on your recent interaction with our support team.',40,'active','2026-09-01','2026-07-18 03:34:01'),(33,1,'Packaging Quality Feedback','We are evaluating our packaging and want your honest feedback.',40,'active','2026-09-01','2026-07-18 03:34:01'),(34,1,'Loyalty Program Survey','Help us build a better loyalty program for our most valued customers.',40,'active','2026-09-01','2026-07-18 03:34:01'),(38,1,'aaa','aaa',20,'active','2026-07-23','2026-07-22 04:51:55');
/*!40000 ALTER TABLE `surveys` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `user_id` int NOT NULL AUTO_INCREMENT,
  `name` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `email` varchar(100) COLLATE utf8mb4_unicode_ci NOT NULL,
  `password` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `points` int DEFAULT '0',
  `created_at` timestamp NULL DEFAULT CURRENT_TIMESTAMP,
  `is_deleted` tinyint(1) NOT NULL DEFAULT '0',
  `deleted_at` datetime DEFAULT NULL,
  `status` enum('active','inactive') COLLATE utf8mb4_unicode_ci DEFAULT 'active',
  PRIMARY KEY (`user_id`),
  UNIQUE KEY `email` (`email`),
  KEY `idx_status` (`status`)
) ENGINE=InnoDB AUTO_INCREMENT=17 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'Kasun Shehan','kasun@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',1000,'2026-07-08 16:38:35',0,NULL,'active'),(2,'Nimal Fernando','nimal@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',220,'2026-07-08 16:38:35',0,NULL,'active'),(3,'Chamodi Silva','chamodi@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',850,'2026-07-08 16:38:35',0,NULL,'active'),(4,'Ashen Jayasinghe','ashen@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',160,'2026-07-08 16:38:35',0,NULL,'active'),(5,'Dilshan Kumara','dilshan@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',700,'2026-07-08 16:38:35',0,NULL,'inactive'),(6,'Tharushi Perera','tharushi@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',130,'2026-07-08 16:38:35',0,NULL,'active'),(7,'Supun Fernando','supun@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',310,'2026-07-08 16:38:35',0,NULL,'active'),(8,'Isuri Madushani','isuri@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',360,'2026-07-08 16:38:35',0,NULL,'active'),(9,'Kavindu Perera','kavindu@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',310,'2026-07-08 16:38:35',0,NULL,'active'),(10,'Shehani De Silva','shehani@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',720,'2026-07-08 16:38:35',0,NULL,'active'),(11,'Pasindu Wijesinghe','pasindu@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',95,'2026-07-08 16:38:35',0,NULL,'active'),(12,'Dinithi Fernando','dinithi@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',390,'2026-07-08 16:38:35',0,NULL,'active'),(13,'Nuwan Perera','nuwan@mail.com','$2y$10$JJr6UB8v3C2/cXWzK.nn6.PBmPmmf5bv7tTDGu6kCs6p58T.mW7Pi',670,'2026-07-08 16:38:35',0,'2026-07-08 22:08:35','inactive'),(14,'Rasika Prabodha','rasika@mail.com','$2y$10$/jnuZQ1HfLKiCmFqMckC2OfZvahvHqyvviqUuKheZxqt3rrZKj7xO',140,'2026-07-16 07:31:18',0,NULL,'active'),(15,'sajatha','sajathashehan23@gmail.com','$2y$10$3QH45awfpBCuMsfgqvVbQOeKp0rcPi/.ycWPOoJ.tvbnycyH0nk.W',140,'2026-07-16 09:28:56',0,NULL,'active'),(16,'Kakashi','kakashi@mail.com','$2y$10$hpmwaQ6CDhnUp34/.xzOiuIMF6gtw2ii5qJiB6J2QauRyJsf0GAAq',0,'2026-07-19 08:14:07',0,NULL,'active');
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

-- Dump completed on 2026-10-05 23:00:23
