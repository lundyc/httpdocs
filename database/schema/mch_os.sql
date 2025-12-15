/*M!999999\- enable the sandbox mode */ 
-- MariaDB dump 10.19  Distrib 10.11.13-MariaDB, for debian-linux-gnu (x86_64)
--
-- Host: localhost    Database: mch_os
-- ------------------------------------------------------
-- Server version	10.11.13-MariaDB-0ubuntu0.24.04.1

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
-- Table structure for table `audit_logs`
--

DROP TABLE IF EXISTS `audit_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL AUTO_INCREMENT,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=82 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `audit_logs`
--

LOCK TABLES `audit_logs` WRITE;
/*!40000 ALTER TABLE `audit_logs` DISABLE KEYS */;
INSERT INTO `audit_logs` VALUES
(1,1,'User logged in','::1','2025-10-04 04:29:57'),
(2,1,'User logged in','::1','2025-10-04 04:36:53'),
(3,1,'User logged in','::1','2025-10-04 04:37:14'),
(4,1,'User logged in','::1','2025-10-04 04:37:25'),
(5,1,'User logged in','::1','2025-10-04 05:08:59'),
(6,1,'User logged in','::1','2025-10-04 05:09:07'),
(7,1,'User logged in','::1','2025-10-04 05:14:18'),
(8,1,'User logged in','::1','2025-10-04 05:14:37'),
(9,1,'User logged in','::1','2025-10-04 05:21:30'),
(10,1,'User logged in','::1','2025-10-04 05:26:32'),
(11,1,'User logged in','::1','2025-10-04 05:27:35'),
(12,1,'User logged in','::1','2025-10-04 05:27:36'),
(13,1,'User logged in','::1','2025-10-04 05:53:22'),
(14,1,'User logged in','::1','2025-10-04 06:53:45'),
(15,1,'User logged in','::1','2025-10-04 06:58:22'),
(16,1,'User logged in','::1','2025-10-04 07:01:30'),
(17,1,'User logged in','::1','2025-10-04 07:01:33'),
(18,1,'User logged in','::1','2025-10-04 07:03:47'),
(19,1,'User logged in','::1','2025-10-04 07:03:51'),
(20,1,'User logged in','::1','2025-10-04 07:04:30'),
(21,1,'User logged in (debug)','::1','2025-10-04 07:04:44'),
(22,1,'User logged in','::1','2025-10-04 07:11:01'),
(23,1,'User logged in','::1','2025-10-04 07:11:10'),
(24,1,'User logged in','::1','2025-10-04 07:11:13'),
(25,1,'User logged in','::1','2025-10-04 07:11:46'),
(26,1,'User logged in','::1','2025-10-04 07:12:28'),
(27,1,'User logged in','::1','2025-10-04 07:18:10'),
(28,1,'User logged in','::1','2025-10-04 07:18:19'),
(29,1,'User logged in','::1','2025-10-04 07:18:20'),
(30,1,'User logged in','::1','2025-10-04 07:22:57'),
(31,1,'User logged in','::1','2025-10-04 07:26:13'),
(32,1,'User logged in','::1','2025-10-04 07:34:05'),
(33,1,'User logged in','::1','2025-10-04 07:36:14'),
(34,1,'User logged in','::1','2025-10-04 07:36:44'),
(35,1,'User logged in','::1','2025-10-04 07:47:55'),
(36,1,'User logged in','::1','2025-10-04 07:48:10'),
(37,1,'User logged in','::1','2025-10-04 07:51:00'),
(38,1,'Created news post: first test','::1','2025-10-04 08:54:10'),
(39,1,'User logged in','::1','2025-10-04 09:27:00'),
(40,1,'User logged in','::1','2025-10-07 07:25:03'),
(41,1,'User logged in','::1','2025-10-07 07:26:26'),
(42,1,'User logged in','::1','2025-10-07 07:26:29'),
(43,1,'User logged in','::1','2025-10-07 07:33:21'),
(44,1,'User logged in','::1','2025-10-07 07:33:52'),
(45,1,'User logged in','::1','2025-10-07 07:33:54'),
(46,1,'User logged in','::1','2025-10-07 07:33:56'),
(47,1,'User logged in','::1','2025-10-07 07:40:42'),
(48,1,'User logged in','::1','2025-10-07 07:41:09'),
(49,1,'User logged in','::1','2025-10-07 07:43:25'),
(50,1,'User logged in','::1','2025-10-07 07:48:59'),
(51,1,'User logged in','::1','2025-10-07 07:49:10'),
(52,1,'User logged in','::1','2025-10-07 07:52:02'),
(53,1,'User logged in','::1','2025-10-07 07:52:35'),
(54,1,'User logged in','::1','2025-10-07 08:00:38'),
(55,1,'User logged in','::1','2025-10-07 08:02:17'),
(56,1,'User logged in','::1','2025-10-10 12:16:14'),
(57,NULL,'Failed login for colin@lundy.me.uk','82.132.187.203','2025-10-13 16:11:32'),
(58,NULL,'Failed login for colin@lundy.me.uk','82.132.187.203','2025-10-13 16:24:02'),
(59,NULL,'Failed login for colin@lundy.me.uk','82.132.187.203','2025-10-13 16:24:13'),
(60,NULL,'Failed login for colin@lundy.me.uk','82.132.187.203','2025-10-13 16:25:09'),
(61,1,'User logged in','82.132.187.203','2025-10-13 16:28:07'),
(62,1,'User logged in','82.132.187.203','2025-10-13 16:30:32'),
(63,1,'User logged in','82.132.187.203','2025-10-13 17:21:22'),
(64,1,'User logged in','82.132.187.203','2025-10-13 17:21:38'),
(65,1,'User logged in','82.132.187.203','2025-10-13 17:23:45'),
(66,1,'User logged in','82.132.187.203','2025-10-13 17:28:55'),
(67,1,'User logged in','82.132.187.203','2025-10-13 17:29:15'),
(68,1,'User logged in','86.172.104.77','2025-10-14 12:09:59'),
(69,1,'User logged in','86.172.104.77','2025-10-14 12:47:15'),
(70,1,'User logged in','86.172.104.77','2025-10-14 12:51:30'),
(71,1,'User logged in','86.172.104.77','2025-10-14 12:54:30'),
(72,1,'Opened POS session for location ID 1 with float 21','86.172.104.77','2025-10-14 14:11:53'),
(73,1,'Recorded sale: 1 x Pint = £4.5 (cash)','86.172.104.77','2025-10-14 14:32:03'),
(74,1,'Recorded sale: 1 x Half Pint = £2.5 (cash)','86.172.104.77','2025-10-14 14:32:06'),
(75,1,'Recorded sale: 1 x adsfasdf = £23 (cash)','86.172.104.77','2025-10-14 14:32:31'),
(76,1,'Recorded sale: 1 x Pint = £4.5 (cash)','86.172.104.77','2025-10-14 14:32:48'),
(77,1,'Recorded sale: 1 x Half Pint = £2.5 (cash)','86.172.104.77','2025-10-14 14:32:50'),
(78,1,'User logged in','86.154.59.103','2025-10-14 19:04:58'),
(79,1,'User logged in','31.94.32.15','2025-10-20 07:16:00'),
(80,NULL,'Failed login for colin@lundy.me.uk','86.183.15.226','2025-11-18 19:42:57'),
(81,NULL,'Failed login for colin@lundy.me.uk','86.183.15.226','2025-11-18 20:15:38');
/*!40000 ALTER TABLE `audit_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `competitions`
--

DROP TABLE IF EXISTS `competitions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `competitions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `level` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_competitions_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `competitions`
--

LOCK TABLES `competitions` WRITE;
/*!40000 ALTER TABLE `competitions` DISABLE KEYS */;
INSERT INTO `competitions` VALUES
(1,'West of Scotland Premier Division','League','Senior league competition','2025-10-07 12:04:39'),
(2,'Scottish Cup','Cup','National knockout cup','2025-10-07 12:04:39');
/*!40000 ALTER TABLE `competitions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `fixtures`
--

DROP TABLE IF EXISTS `fixtures`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `fixtures` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `season_id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `home_team_id` int(11) NOT NULL,
  `away_team_id` int(11) NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `home_score` int(11) DEFAULT NULL,
  `away_score` int(11) DEFAULT NULL,
  `status` enum('scheduled','played','postponed','cancelled') DEFAULT 'scheduled',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `season_id` (`season_id`),
  KEY `competition_id` (`competition_id`),
  KEY `venue_id` (`venue_id`),
  KEY `home_team_id` (`home_team_id`),
  KEY `away_team_id` (`away_team_id`),
  CONSTRAINT `fixtures_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  CONSTRAINT `fixtures_ibfk_2` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  CONSTRAINT `fixtures_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  CONSTRAINT `fixtures_ibfk_4` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`),
  CONSTRAINT `fixtures_ibfk_5` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `fixtures`
--

LOCK TABLES `fixtures` WRITE;
/*!40000 ALTER TABLE `fixtures` DISABLE KEYS */;
INSERT INTO `fixtures` VALUES
(1,1,1,1,1,2,'2025-07-15','19:30:00','2025-10-20 10:34:42',2,1,'played',NULL,'2025-10-07 12:07:28'),
(2,1,1,2,2,1,'2025-07-22','15:00:00','2025-10-20 10:34:42',1,2,'played',NULL,'2025-10-07 12:07:28'),
(3,1,2,3,3,1,'2025-08-05','14:00:00','2025-10-20 10:34:42',2,0,'played',NULL,'2025-10-07 12:07:28'),
(4,1,1,1,1,5,'2025-08-12','19:30:00','2025-10-20 10:34:42',3,0,'played',NULL,'2025-10-07 12:07:28'),
(5,1,1,2,5,1,'2025-08-19','15:00:00','2025-10-20 10:34:42',1,22,'played',NULL,'2025-10-07 12:07:28'),
(6,1,2,3,6,1,'2025-09-02','14:00:00','2025-10-20 10:34:48',1,12,'played','','2025-10-07 12:07:28'),
(7,2,1,1,1,3,'2025-04-20','19:00:00','2025-10-20 10:34:42',2,2,'played',NULL,'2025-10-07 12:07:28'),
(8,2,2,2,2,1,'2025-05-10','14:00:00','2025-10-20 10:34:42',1,2,'played',NULL,'2025-10-07 12:07:28'),
(9,2,1,3,1,5,'2025-05-17','15:00:00','2025-10-20 10:34:42',2,2,'played',NULL,'2025-10-07 12:07:28'),
(10,2,2,1,1,6,'2025-06-01','16:00:00','2025-10-20 10:34:42',NULL,NULL,'scheduled',NULL,'2025-10-07 12:07:28');
/*!40000 ALTER TABLE `fixtures` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `invites`
--

DROP TABLE IF EXISTS `invites`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `invites` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `email` varchar(150) NOT NULL,
  `role_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `status` enum('pending','used','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `code` (`code`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `invites_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `invites`
--

LOCK TABLES `invites` WRITE;
/*!40000 ALTER TABLE `invites` DISABLE KEYS */;
/*!40000 ALTER TABLE `invites` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_discipline`
--

DROP TABLE IF EXISTS `match_discipline`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_discipline` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `card_type` enum('yellow','red') NOT NULL DEFAULT 'yellow',
  `minute` tinyint(3) unsigned DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `idx_match_discipline_fixture` (`fixture_id`),
  KEY `idx_match_discipline_player` (`player_id`),
  CONSTRAINT `fk_match_discipline_fixture` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `fk_match_discipline_player` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_discipline`
--

LOCK TABLES `match_discipline` WRITE;
/*!40000 ALTER TABLE `match_discipline` DISABLE KEYS */;
INSERT INTO `match_discipline` VALUES
(1,6,11,'yellow',13,'2025-10-20 16:30:25','2025-10-20 16:30:25');
/*!40000 ALTER TABLE `match_discipline` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_lineups`
--

DROP TABLE IF EXISTS `match_lineups`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_lineups` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `shirt_number` int(3) DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `is_substitute` tinyint(1) DEFAULT 0,
  `minutes_played` int(11) DEFAULT NULL,
  `player_replaced_id` int(11) DEFAULT NULL,
  `captain` tinyint(1) DEFAULT 0,
  `goals` int(11) DEFAULT 0,
  `assists` int(11) DEFAULT 0,
  `yellow_cards` int(11) DEFAULT 0,
  `red_cards` int(11) DEFAULT 0,
  PRIMARY KEY (`id`),
  KEY `fixture_id` (`fixture_id`),
  KEY `player_id` (`player_id`),
  KEY `position_id` (`position_id`),
  KEY `player_replaced_id` (`player_replaced_id`),
  CONSTRAINT `match_lineups_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_lineups_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_lineups_ibfk_3` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `match_lineups_ibfk_4` FOREIGN KEY (`player_replaced_id`) REFERENCES `players` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=162 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_lineups`
--

LOCK TABLES `match_lineups` WRITE;
/*!40000 ALTER TABLE `match_lineups` DISABLE KEYS */;
INSERT INTO `match_lineups` VALUES
(150,6,12,1,1,0,NULL,NULL,0,0,0,0,0),
(151,6,9,2,NULL,0,NULL,NULL,0,0,0,0,0),
(152,6,7,3,NULL,0,NULL,NULL,1,0,0,0,0),
(153,6,11,4,NULL,0,NULL,NULL,0,0,0,0,0),
(154,6,8,5,NULL,0,NULL,NULL,0,0,0,0,0),
(155,6,10,6,NULL,0,NULL,NULL,0,0,0,0,0),
(156,6,15,7,NULL,0,NULL,NULL,0,0,0,0,0),
(157,6,1,8,NULL,0,NULL,NULL,0,0,0,0,0),
(158,6,4,9,NULL,0,NULL,NULL,0,0,0,0,0),
(159,6,17,10,NULL,0,NULL,NULL,0,0,0,0,0),
(160,6,13,11,21,1,16,12,0,0,0,0,0),
(161,6,5,12,NULL,0,NULL,NULL,0,0,0,0,0);
/*!40000 ALTER TABLE `match_lineups` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_notes`
--

DROP TABLE IF EXISTS `match_notes`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_notes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `note_type` enum('tactical','postmatch','general') DEFAULT 'general',
  `content` text NOT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fixture_id` (`fixture_id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `match_notes_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_notes_ibfk_2` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_notes`
--

LOCK TABLES `match_notes` WRITE;
/*!40000 ALTER TABLE `match_notes` DISABLE KEYS */;
INSERT INTO `match_notes` VALUES
(1,6,'general','sdfsdf',1,'2025-10-20 15:11:55');
/*!40000 ALTER TABLE `match_notes` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_ratings`
--

DROP TABLE IF EXISTS `match_ratings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_ratings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `rating` decimal(3,1) DEFAULT NULL,
  `comment` text DEFAULT NULL,
  `rated_by` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fixture_id` (`fixture_id`),
  KEY `player_id` (`player_id`),
  KEY `rated_by` (`rated_by`),
  CONSTRAINT `match_ratings_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_ratings_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `match_ratings_ibfk_3` FOREIGN KEY (`rated_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_ratings`
--

LOCK TABLES `match_ratings` WRITE;
/*!40000 ALTER TABLE `match_ratings` DISABLE KEYS */;
/*!40000 ALTER TABLE `match_ratings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `match_stats`
--

DROP TABLE IF EXISTS `match_stats`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `match_stats` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `fixture_id` int(11) NOT NULL,
  `team_id` int(11) DEFAULT NULL,
  `possession` tinyint(4) DEFAULT NULL,
  `shots_total` int(11) DEFAULT NULL,
  `shots_on_target` int(11) DEFAULT NULL,
  `corners` int(11) DEFAULT NULL,
  `freekicks` int(11) DEFAULT NULL,
  `fouls` int(11) DEFAULT NULL,
  `offsides` int(11) DEFAULT NULL,
  `yellow_cards` int(11) DEFAULT NULL,
  `red_cards` int(11) DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fixture_id` (`fixture_id`),
  CONSTRAINT `match_stats_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=3 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `match_stats`
--

LOCK TABLES `match_stats` WRITE;
/*!40000 ALTER TABLE `match_stats` DISABLE KEYS */;
INSERT INTO `match_stats` VALUES
(1,6,6,50,10,8,5,0,200,1,NULL,NULL,'2025-10-20 11:25:17','2025-10-20 11:25:17'),
(2,6,1,50,15,2,6,5,10,0,NULL,NULL,'2025-10-20 11:25:17','2025-10-20 11:25:17');
/*!40000 ALTER TABLE `match_stats` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `news`
--

DROP TABLE IF EXISTS `news`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `news` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `category` enum('Match Report','Club News','Community') DEFAULT 'Club News',
  `content` text NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `created_by` (`created_by`),
  CONSTRAINT `news_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `news`
--

LOCK TABLES `news` WRITE;
/*!40000 ALTER TABLE `news` DISABLE KEYS */;
INSERT INTO `news` VALUES
(1,'first test','Club News','Hello World',NULL,1,'2025-10-04 08:54:10');
/*!40000 ALTER TABLE `news` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `players`
--

DROP TABLE IF EXISTS `players`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `players` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `team_id` int(11) DEFAULT NULL,
  `name` varchar(150) NOT NULL,
  `dob` date DEFAULT NULL,
  `position_id` int(11) DEFAULT NULL,
  `shirt_number` int(11) DEFAULT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `team_id` (`team_id`),
  KEY `position_id` (`position_id`),
  CONSTRAINT `players_ibfk_1` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL,
  CONSTRAINT `players_ibfk_2` FOREIGN KEY (`position_id`) REFERENCES `positions` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB AUTO_INCREMENT=19 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `players`
--

LOCK TABLES `players` WRITE;
/*!40000 ALTER TABLE `players` DISABLE KEYS */;
INSERT INTO `players` VALUES
(1,1,'Calum Robertson',NULL,NULL,1,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(2,1,'Cameron McIntyre',NULL,NULL,2,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(3,1,'Stewart Morgan',NULL,NULL,3,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(4,1,'Jamie Stirling',NULL,NULL,4,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(5,1,'Ross Agnew',NULL,NULL,5,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(6,1,'Andrew McIntyre',NULL,NULL,6,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(7,1,'Aaron Tait',NULL,NULL,7,'active','2025-10-20 11:32:17','2025-10-20 11:57:49'),
(8,1,'Rudi Johnston',NULL,NULL,8,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(9,1,'Aaron Robertson',NULL,NULL,9,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(10,1,'Euan Anderson',NULL,NULL,10,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(11,1,'Adam Kamara',NULL,NULL,11,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(12,1,'Aaron Hussey',NULL,1,19,'active','2025-10-20 11:32:17','2025-10-20 12:07:06'),
(13,1,'Adam Love',NULL,NULL,12,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(14,1,'Rian Eaglesham',NULL,NULL,14,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(15,1,'David Sawyers',NULL,NULL,15,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(16,1,'Ryan Ritchie',NULL,NULL,16,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(17,1,'Jack Cousar',NULL,NULL,17,'active','2025-10-20 11:32:17','2025-10-20 11:32:17'),
(18,1,'Reiss Love',NULL,NULL,18,'active','2025-10-20 11:32:17','2025-10-20 11:32:17');
/*!40000 ALTER TABLE `players` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_locations`
--

DROP TABLE IF EXISTS `pos_locations`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_locations` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_locations`
--

LOCK TABLES `pos_locations` WRITE;
/*!40000 ALTER TABLE `pos_locations` DISABLE KEYS */;
INSERT INTO `pos_locations` VALUES
(1,'Bar','Main bar counter','2025-10-07 11:23:58'),
(2,'Kiosk','Food and snacks kiosk','2025-10-07 11:23:58'),
(3,'Merch','Merchandise stand','2025-10-07 11:23:58'),
(4,'Gate','Gate entry / ticket sales','2025-10-07 11:23:58');
/*!40000 ALTER TABLE `pos_locations` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_refunds`
--

DROP TABLE IF EXISTS `pos_refunds`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_refunds` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `sale_id` int(11) DEFAULT NULL,
  `refunded_by` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `sale_id` (`sale_id`),
  KEY `refunded_by` (`refunded_by`),
  KEY `fk_pos_refunds_session` (`session_id`),
  CONSTRAINT `fk_pos_refunds_session` FOREIGN KEY (`session_id`) REFERENCES `pos_sessions` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_refunds_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `pos_sales` (`id`) ON DELETE SET NULL,
  CONSTRAINT `pos_refunds_ibfk_2` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_refunds`
--

LOCK TABLES `pos_refunds` WRITE;
/*!40000 ALTER TABLE `pos_refunds` DISABLE KEYS */;
/*!40000 ALTER TABLE `pos_refunds` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_sales`
--

DROP TABLE IF EXISTS `pos_sales`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_sales` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `session_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `pos_sales_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `pos_sessions` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_sales`
--

LOCK TABLES `pos_sales` WRITE;
/*!40000 ALTER TABLE `pos_sales` DISABLE KEYS */;
INSERT INTO `pos_sales` VALUES
(1,1,'Pint',1,4.50,'cash','2025-10-14 14:32:03'),
(2,1,'Half Pint',1,2.50,'cash','2025-10-14 14:32:06'),
(3,1,'adsfasdf',1,23.00,'cash','2025-10-14 14:32:31'),
(4,1,'Pint',1,4.50,'cash','2025-10-14 14:32:48'),
(5,1,'Half Pint',1,2.50,'cash','2025-10-14 14:32:50');
/*!40000 ALTER TABLE `pos_sales` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `pos_sessions`
--

DROP TABLE IF EXISTS `pos_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `pos_sessions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `location_id` int(11) NOT NULL,
  `opened_by` int(11) NOT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `start_float` decimal(10,2) NOT NULL,
  `end_float` decimal(10,2) DEFAULT NULL,
  `variance` decimal(10,2) DEFAULT 0.00,
  `status` enum('open','closed') DEFAULT 'open',
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `location_id` (`location_id`),
  KEY `opened_by` (`opened_by`),
  KEY `fk_pos_sessions_closed_by` (`closed_by`),
  CONSTRAINT `fk_pos_sessions_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`),
  CONSTRAINT `pos_sessions_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `pos_locations` (`id`),
  CONSTRAINT `pos_sessions_ibfk_2` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `pos_sessions`
--

LOCK TABLES `pos_sessions` WRITE;
/*!40000 ALTER TABLE `pos_sessions` DISABLE KEYS */;
INSERT INTO `pos_sessions` VALUES
(1,1,1,NULL,21.00,NULL,0.00,'open','2025-10-14 14:11:53',NULL);
/*!40000 ALTER TABLE `pos_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `positions`
--

DROP TABLE IF EXISTS `positions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `positions` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `code` varchar(10) NOT NULL,
  `short_label` varchar(10) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `category` enum('Goalkeeper','Defence','Midfield','Attack') DEFAULT NULL,
  `display_order` int(11) DEFAULT 0,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=30 DEFAULT CHARSET=utf8mb3 COLLATE=utf8mb3_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `positions`
--

LOCK TABLES `positions` WRITE;
/*!40000 ALTER TABLE `positions` DISABLE KEYS */;
INSERT INTO `positions` VALUES
(1,'010GK','GK','Goalkeeper','Goalkeeper',1),
(2,'020RB','RB','Right Back','Defence',2),
(3,'030LB','LB','Left Back','Defence',3),
(4,'040SW','SW','Sweeper','Defence',4),
(5,'050SWR','SWR','Sweeper Right','Defence',5),
(6,'060SWL','SWL','Sweeper Left','Defence',6),
(7,'070CB','CB','Centre Back','Defence',7),
(8,'080CBR','CBR','Centre Back Right','Defence',8),
(9,'090CBL','CBL','Centre Back Left','Defence',9),
(10,'100RWB','RWB','Right Wing Back','Defence',10),
(11,'110LWB','LWB','Left Wing Back','Defence',11),
(12,'120DCM','DCM','Defensive Centre Midfield','Midfield',12),
(13,'130DCMR','DCMR','Defensive Centre Midfield Right','Midfield',13),
(14,'140DCML','DCML','Defensive Centre Midfield Left','Midfield',14),
(15,'150CM','CM','Centre Midfield','Midfield',15),
(16,'160CMR','CMR','Centre Midfield Right','Midfield',16),
(17,'170CML','CML','Centre Midfield Left','Midfield',17),
(18,'180RM','RM','Right Midfield','Midfield',18),
(19,'190LM','LM','Left Midfield','Midfield',19),
(20,'200ACM','ACM','Attacking Centre Midfield','Midfield',20),
(21,'210ACMR','ACMR','Attacking Centre Midfield Right','Midfield',21),
(22,'220ACML','ACML','Attacking Centre Midfield Left','Midfield',22),
(23,'230AMR','AMR','Attacking Right Midfield','Midfield',23),
(24,'240AML','AML','Attacking Left Midfield','Midfield',24),
(25,'250RW','RW','Right Winger','Attack',25),
(26,'260LW','LW','Left Winger','Attack',26),
(27,'270CFR','CFR','Centre Forward Right','Attack',27),
(28,'280CFL','CFL','Centre Forward Left','Attack',28),
(29,'290CF','CF','Centre Forward','Attack',29);
/*!40000 ALTER TABLE `positions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `roles`
--

DROP TABLE IF EXISTS `roles`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `roles` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `roles`
--

LOCK TABLES `roles` WRITE;
/*!40000 ALTER TABLE `roles` DISABLE KEYS */;
INSERT INTO `roles` VALUES
(1,'Super Admin','2025-10-04 04:12:17'),
(2,'Admin','2025-10-04 04:12:17'),
(3,'Manager','2025-10-04 04:12:17'),
(4,'Volunteer','2025-10-04 04:12:17'),
(5,'Coach','2025-10-07 11:21:10'),
(6,'Player','2025-10-07 11:21:10'),
(7,'Treasurer','2025-10-07 11:21:10'),
(8,'Committee','2025-10-07 11:21:10');
/*!40000 ALTER TABLE `roles` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `season_tickets`
--

DROP TABLE IF EXISTS `season_tickets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `season_tickets` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `holder_name` varchar(255) NOT NULL,
  `email` varchar(255) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `seat_number` varchar(10) DEFAULT NULL,
  `season` varchar(20) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `status` enum('active','expired','suspended') DEFAULT 'active',
  `purchase_date` date NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `seat_number` (`seat_number`),
  KEY `idx_season_tickets_season` (`season`),
  KEY `idx_season_tickets_status` (`status`),
  KEY `idx_season_tickets_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `season_tickets`
--

LOCK TABLES `season_tickets` WRITE;
/*!40000 ALTER TABLE `season_tickets` DISABLE KEYS */;
/*!40000 ALTER TABLE `season_tickets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `seasons`
--

DROP TABLE IF EXISTS `seasons`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `seasons` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','archived','upcoming') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_seasons_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=4 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `seasons`
--

LOCK TABLES `seasons` WRITE;
/*!40000 ALTER TABLE `seasons` DISABLE KEYS */;
INSERT INTO `seasons` VALUES
(1,'2025/26','2025-07-01','2026-06-30','active','2025-10-07 11:38:59'),
(2,'2024/25 (temp)','2024-07-01','2025-06-30','archived','2025-10-13 13:32:45'),
(3,'2024/25','2024-07-01','2025-06-30','archived','2025-10-07 12:07:28');
/*!40000 ALTER TABLE `seasons` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `sponsors`
--

DROP TABLE IF EXISTS `sponsors`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `tier` enum('Main','Partner','Supporter') DEFAULT 'Supporter',
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `sponsors`
--

LOCK TABLES `sponsors` WRITE;
/*!40000 ALTER TABLE `sponsors` DISABLE KEYS */;
/*!40000 ALTER TABLE `sponsors` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_items`
--

DROP TABLE IF EXISTS `stock_items`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_items`
--

LOCK TABLES `stock_items` WRITE;
/*!40000 ALTER TABLE `stock_items` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_items` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `stock_movements`
--

DROP TABLE IF EXISTS `stock_movements`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('delivery','sale','wastage','donation') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `item_id` (`item_id`),
  CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `stock_movements`
--

LOCK TABLES `stock_movements` WRITE;
/*!40000 ALTER TABLE `stock_movements` DISABLE KEYS */;
/*!40000 ALTER TABLE `stock_movements` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `svfc_sessions`
--

DROP TABLE IF EXISTS `svfc_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `svfc_sessions` (
  `id` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `expires_at` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `svfc_sessions`
--

LOCK TABLES `svfc_sessions` WRITE;
/*!40000 ALTER TABLE `svfc_sessions` DISABLE KEYS */;
INSERT INTO `svfc_sessions` VALUES
('0059a9c1ae8eb8df9abd87adcb547964',NULL,NULL,NULL,'2025-10-13 14:37:20','2025-10-13 14:37:20','2025-10-14 14:37:20'),
('08d6e11dbab7164cdf815480b6c45163',NULL,NULL,NULL,'2025-10-13 14:09:14','2025-10-13 14:09:14','2025-10-14 14:09:14'),
('0f35c33916a2b1d275b5df1ee2ee6eed',NULL,NULL,NULL,'2025-10-13 14:32:28','2025-10-13 14:32:28','2025-10-14 14:32:28'),
('1d82541aafc99728206576f25620bcf0',NULL,NULL,NULL,'2025-10-13 14:08:00','2025-10-13 14:08:00','2025-10-14 14:08:00'),
('32db3b605af4193baeb9a05f4f9bfb51',NULL,NULL,NULL,'2025-10-13 14:32:06','2025-10-13 14:32:06','2025-10-14 14:32:06'),
('5638c8a136ef46dd13864801970f181b',NULL,NULL,NULL,'2025-10-13 14:32:01','2025-10-13 14:32:01','2025-10-14 14:32:01'),
('8e6a99b026902f1d9c314a99a2ba05dd',NULL,NULL,NULL,'2025-10-13 14:37:18','2025-10-13 14:37:18','2025-10-14 14:37:18'),
('914a1853a05f623264acdb2912167836',NULL,NULL,NULL,'2025-10-13 14:32:28','2025-10-13 14:32:28','2025-10-14 14:32:28'),
('9d51fb2ec9334146d07b4f7ccf435544',NULL,NULL,NULL,'2025-10-13 14:09:10','2025-10-13 14:09:10','2025-10-14 14:09:10'),
('a73426882a414b528365b624319db672',NULL,NULL,NULL,'2025-10-13 14:12:41','2025-10-13 14:16:32','2025-10-14 14:12:41'),
('aa49002fe2f530375d08a7c39dfc2020',NULL,NULL,NULL,'2025-10-13 14:09:25','2025-10-13 14:09:25','2025-10-14 14:09:25'),
('bac632f0ba7057146163f0e801344054',NULL,NULL,NULL,'2025-10-13 14:32:06','2025-10-13 14:32:06','2025-10-14 14:32:06'),
('c112d4fc3cea7d879d9cb47e0ca59e51',NULL,NULL,NULL,'2025-10-13 14:44:23','2025-10-13 14:44:24','2025-10-14 14:44:23'),
('c4a01f1e38d10ca7f9e68aaef05989b8',NULL,NULL,NULL,'2025-10-13 14:32:06','2025-10-13 14:32:06','2025-10-14 14:32:06'),
('cdde8fe8443cc8a5d030bfb7a635787c',NULL,NULL,NULL,'2025-10-13 14:32:00','2025-10-13 14:32:00','2025-10-14 14:32:00'),
('dea964f9595dcd65bad244012efd39b6',NULL,NULL,NULL,'2025-10-13 14:09:08','2025-10-13 14:09:08','2025-10-14 14:09:08');
/*!40000 ALTER TABLE `svfc_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `teams`
--

DROP TABLE IF EXISTS `teams`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `teams` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_teams_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `teams`
--

LOCK TABLES `teams` WRITE;
/*!40000 ALTER TABLE `teams` DISABLE KEYS */;
INSERT INTO `teams` VALUES
(1,'Saltcoats Victoria FC','SVFC',NULL,'2025-10-07 12:04:39'),
(2,'Gateside Rovers','ROV',NULL,'2025-10-07 12:04:39'),
(3,'Port Glasgow United','PGU',NULL,'2025-10-07 12:04:39'),
(4,'Ayr Town','AYR',NULL,'2025-10-07 12:04:39'),
(5,'Kilmarnock Colts','KIL',NULL,'2025-10-07 12:04:39'),
(6,'Greenock Athletic','GRK',NULL,'2025-10-07 12:04:39');
/*!40000 ALTER TABLE `teams` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `role_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `email` (`email`),
  KEY `role_id` (`role_id`),
  CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES
(1,1,'Colin Lundy','colin@lundy.me.uk','$2y$12$8z.RlRQ8IgCrzeryfepPjuXKNSZLiUIG5B5dUpU3zkPUVzxjG2pUO','active','2025-10-04 04:12:17','2025-10-14 12:47:01');
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `venues`
--

DROP TABLE IF EXISTS `venues`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `venues` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `uq_venues_name` (`name`)
) ENGINE=InnoDB AUTO_INCREMENT=7 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `venues`
--

LOCK TABLES `venues` WRITE;
/*!40000 ALTER TABLE `venues` DISABLE KEYS */;
INSERT INTO `venues` VALUES
(1,'Victoria Park','31 Jack’s Road','Saltcoats','KA21 5SH',1000,'2025-10-07 12:04:39'),
(2,'Rovers Stadium','12 Riverside Drive','Gateside','KA11 4JJ',1200,'2025-10-07 12:04:39'),
(3,'Greenock Arena','100 Main Street','Greenock','PA15 1AB',1500,'2025-10-07 12:04:39');
/*!40000 ALTER TABLE `venues` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veo_clip_tags`
--

DROP TABLE IF EXISTS `veo_clip_tags`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `veo_clip_tags` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `clip_id` int(11) NOT NULL,
  `player_id` int(11) NOT NULL,
  `tagged_by` int(11) DEFAULT NULL,
  `tagged_at` timestamp NULL DEFAULT current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `clip_id` (`clip_id`),
  KEY `player_id` (`player_id`),
  KEY `tagged_by` (`tagged_by`),
  CONSTRAINT `veo_clip_tags_ibfk_1` FOREIGN KEY (`clip_id`) REFERENCES `veo_clips` (`id`) ON DELETE CASCADE,
  CONSTRAINT `veo_clip_tags_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE CASCADE,
  CONSTRAINT `veo_clip_tags_ibfk_3` FOREIGN KEY (`tagged_by`) REFERENCES `users` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veo_clip_tags`
--

LOCK TABLES `veo_clip_tags` WRITE;
/*!40000 ALTER TABLE `veo_clip_tags` DISABLE KEYS */;
/*!40000 ALTER TABLE `veo_clip_tags` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veo_clips`
--

DROP TABLE IF EXISTS `veo_clips`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `veo_clips` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veo_clip_id` varchar(100) DEFAULT NULL,
  `veo_match_id` varchar(100) DEFAULT NULL,
  `match_id` int(11) DEFAULT NULL,
  `player_id` int(11) DEFAULT NULL,
  `event_type` varchar(100) DEFAULT NULL,
  `timestamp_start` float DEFAULT NULL,
  `timestamp_end` float DEFAULT NULL,
  `video_url` text DEFAULT NULL,
  `thumbnail_url` text DEFAULT NULL,
  `visibility` enum('private','team','public') DEFAULT 'team',
  `coach_comment` text DEFAULT NULL,
  `source` enum('veo','manual') DEFAULT 'veo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `match_id` (`match_id`),
  KEY `player_id` (`player_id`),
  CONSTRAINT `veo_clips_ibfk_1` FOREIGN KEY (`match_id`) REFERENCES `veo_matches` (`id`) ON DELETE CASCADE,
  CONSTRAINT `veo_clips_ibfk_2` FOREIGN KEY (`player_id`) REFERENCES `players` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veo_clips`
--

LOCK TABLES `veo_clips` WRITE;
/*!40000 ALTER TABLE `veo_clips` DISABLE KEYS */;
/*!40000 ALTER TABLE `veo_clips` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veo_matches`
--

DROP TABLE IF EXISTS `veo_matches`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `veo_matches` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `veo_match_id` varchar(100) NOT NULL,
  `fixture_id` int(11) DEFAULT NULL,
  `team_id` int(11) DEFAULT NULL,
  `opponent_name` varchar(255) DEFAULT NULL,
  `title` varchar(255) DEFAULT NULL,
  `date_played` date DEFAULT NULL,
  `video_url` text DEFAULT NULL,
  `thumbnail_url` text DEFAULT NULL,
  `duration_seconds` int(11) DEFAULT NULL,
  `status` enum('pending','analysed','published') DEFAULT 'pending',
  `source` enum('veo','manual') DEFAULT 'veo',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  KEY `fixture_id` (`fixture_id`),
  KEY `team_id` (`team_id`),
  CONSTRAINT `veo_matches_ibfk_1` FOREIGN KEY (`fixture_id`) REFERENCES `fixtures` (`id`) ON DELETE SET NULL,
  CONSTRAINT `veo_matches_ibfk_2` FOREIGN KEY (`team_id`) REFERENCES `teams` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veo_matches`
--

LOCK TABLES `veo_matches` WRITE;
/*!40000 ALTER TABLE `veo_matches` DISABLE KEYS */;
/*!40000 ALTER TABLE `veo_matches` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `veo_settings`
--

DROP TABLE IF EXISTS `veo_settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!40101 SET character_set_client = utf8mb4 */;
CREATE TABLE `veo_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `org_id` varchar(100) NOT NULL,
  `api_key` varchar(255) NOT NULL,
  `last_sync` timestamp NULL DEFAULT NULL,
  `sync_status` enum('idle','running','error') DEFAULT 'idle',
  `notes` text DEFAULT NULL,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `veo_settings`
--

LOCK TABLES `veo_settings` WRITE;
/*!40000 ALTER TABLE `veo_settings` DISABLE KEYS */;
/*!40000 ALTER TABLE `veo_settings` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2025-12-15  8:24:44
