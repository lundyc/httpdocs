-- phpMyAdmin SQL Dump
-- version 5.2.2
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Oct 14, 2025 at 04:03 PM
-- Server version: 10.11.13-MariaDB-0ubuntu0.24.04.1
-- PHP Version: 8.4.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `mch_os`
--

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` bigint(20) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(255) NOT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `ip_address`, `created_at`) VALUES
(1, 1, 'User logged in', '::1', '2025-10-04 04:29:57'),
(2, 1, 'User logged in', '::1', '2025-10-04 04:36:53'),
(3, 1, 'User logged in', '::1', '2025-10-04 04:37:14'),
(4, 1, 'User logged in', '::1', '2025-10-04 04:37:25'),
(5, 1, 'User logged in', '::1', '2025-10-04 05:08:59'),
(6, 1, 'User logged in', '::1', '2025-10-04 05:09:07'),
(7, 1, 'User logged in', '::1', '2025-10-04 05:14:18'),
(8, 1, 'User logged in', '::1', '2025-10-04 05:14:37'),
(9, 1, 'User logged in', '::1', '2025-10-04 05:21:30'),
(10, 1, 'User logged in', '::1', '2025-10-04 05:26:32'),
(11, 1, 'User logged in', '::1', '2025-10-04 05:27:35'),
(12, 1, 'User logged in', '::1', '2025-10-04 05:27:36'),
(13, 1, 'User logged in', '::1', '2025-10-04 05:53:22'),
(14, 1, 'User logged in', '::1', '2025-10-04 06:53:45'),
(15, 1, 'User logged in', '::1', '2025-10-04 06:58:22'),
(16, 1, 'User logged in', '::1', '2025-10-04 07:01:30'),
(17, 1, 'User logged in', '::1', '2025-10-04 07:01:33'),
(18, 1, 'User logged in', '::1', '2025-10-04 07:03:47'),
(19, 1, 'User logged in', '::1', '2025-10-04 07:03:51'),
(20, 1, 'User logged in', '::1', '2025-10-04 07:04:30'),
(21, 1, 'User logged in (debug)', '::1', '2025-10-04 07:04:44'),
(22, 1, 'User logged in', '::1', '2025-10-04 07:11:01'),
(23, 1, 'User logged in', '::1', '2025-10-04 07:11:10'),
(24, 1, 'User logged in', '::1', '2025-10-04 07:11:13'),
(25, 1, 'User logged in', '::1', '2025-10-04 07:11:46'),
(26, 1, 'User logged in', '::1', '2025-10-04 07:12:28'),
(27, 1, 'User logged in', '::1', '2025-10-04 07:18:10'),
(28, 1, 'User logged in', '::1', '2025-10-04 07:18:19'),
(29, 1, 'User logged in', '::1', '2025-10-04 07:18:20'),
(30, 1, 'User logged in', '::1', '2025-10-04 07:22:57'),
(31, 1, 'User logged in', '::1', '2025-10-04 07:26:13'),
(32, 1, 'User logged in', '::1', '2025-10-04 07:34:05'),
(33, 1, 'User logged in', '::1', '2025-10-04 07:36:14'),
(34, 1, 'User logged in', '::1', '2025-10-04 07:36:44'),
(35, 1, 'User logged in', '::1', '2025-10-04 07:47:55'),
(36, 1, 'User logged in', '::1', '2025-10-04 07:48:10'),
(37, 1, 'User logged in', '::1', '2025-10-04 07:51:00'),
(38, 1, 'Created news post: first test', '::1', '2025-10-04 08:54:10'),
(39, 1, 'User logged in', '::1', '2025-10-04 09:27:00'),
(40, 1, 'User logged in', '::1', '2025-10-07 07:25:03'),
(41, 1, 'User logged in', '::1', '2025-10-07 07:26:26'),
(42, 1, 'User logged in', '::1', '2025-10-07 07:26:29'),
(43, 1, 'User logged in', '::1', '2025-10-07 07:33:21'),
(44, 1, 'User logged in', '::1', '2025-10-07 07:33:52'),
(45, 1, 'User logged in', '::1', '2025-10-07 07:33:54'),
(46, 1, 'User logged in', '::1', '2025-10-07 07:33:56'),
(47, 1, 'User logged in', '::1', '2025-10-07 07:40:42'),
(48, 1, 'User logged in', '::1', '2025-10-07 07:41:09'),
(49, 1, 'User logged in', '::1', '2025-10-07 07:43:25'),
(50, 1, 'User logged in', '::1', '2025-10-07 07:48:59'),
(51, 1, 'User logged in', '::1', '2025-10-07 07:49:10'),
(52, 1, 'User logged in', '::1', '2025-10-07 07:52:02'),
(53, 1, 'User logged in', '::1', '2025-10-07 07:52:35'),
(54, 1, 'User logged in', '::1', '2025-10-07 08:00:38'),
(55, 1, 'User logged in', '::1', '2025-10-07 08:02:17'),
(56, 1, 'User logged in', '::1', '2025-10-10 12:16:14'),
(57, NULL, 'Failed login for colin@lundy.me.uk', '82.132.187.203', '2025-10-13 16:11:32'),
(58, NULL, 'Failed login for colin@lundy.me.uk', '82.132.187.203', '2025-10-13 16:24:02'),
(59, NULL, 'Failed login for colin@lundy.me.uk', '82.132.187.203', '2025-10-13 16:24:13'),
(60, NULL, 'Failed login for colin@lundy.me.uk', '82.132.187.203', '2025-10-13 16:25:09'),
(61, 1, 'User logged in', '82.132.187.203', '2025-10-13 16:28:07'),
(62, 1, 'User logged in', '82.132.187.203', '2025-10-13 16:30:32'),
(63, 1, 'User logged in', '82.132.187.203', '2025-10-13 17:21:22'),
(64, 1, 'User logged in', '82.132.187.203', '2025-10-13 17:21:38'),
(65, 1, 'User logged in', '82.132.187.203', '2025-10-13 17:23:45'),
(66, 1, 'User logged in', '82.132.187.203', '2025-10-13 17:28:55'),
(67, 1, 'User logged in', '82.132.187.203', '2025-10-13 17:29:15'),
(68, 1, 'User logged in', '86.172.104.77', '2025-10-14 12:09:59'),
(69, 1, 'User logged in', '86.172.104.77', '2025-10-14 12:47:15'),
(70, 1, 'User logged in', '86.172.104.77', '2025-10-14 12:51:30'),
(71, 1, 'User logged in', '86.172.104.77', '2025-10-14 12:54:30'),
(72, 1, 'Opened POS session for location ID 1 with float 21', '86.172.104.77', '2025-10-14 14:11:53'),
(73, 1, 'Recorded sale: 1 x Pint = £4.5 (cash)', '86.172.104.77', '2025-10-14 14:32:03'),
(74, 1, 'Recorded sale: 1 x Half Pint = £2.5 (cash)', '86.172.104.77', '2025-10-14 14:32:06'),
(75, 1, 'Recorded sale: 1 x adsfasdf = £23 (cash)', '86.172.104.77', '2025-10-14 14:32:31'),
(76, 1, 'Recorded sale: 1 x Pint = £4.5 (cash)', '86.172.104.77', '2025-10-14 14:32:48'),
(77, 1, 'Recorded sale: 1 x Half Pint = £2.5 (cash)', '86.172.104.77', '2025-10-14 14:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `competitions`
--

CREATE TABLE `competitions` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `level` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `competitions`
--

INSERT INTO `competitions` (`id`, `name`, `level`, `description`, `created_at`) VALUES
(1, 'West of Scotland Premier Division', 'League', 'Senior league competition', '2025-10-07 12:04:39'),
(2, 'Scottish Cup', 'Cup', 'National knockout cup', '2025-10-07 12:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `fixtures`
--

CREATE TABLE `fixtures` (
  `id` int(11) NOT NULL,
  `season_id` int(11) NOT NULL,
  `competition_id` int(11) NOT NULL,
  `venue_id` int(11) NOT NULL,
  `home_team_id` int(11) NOT NULL,
  `away_team_id` int(11) NOT NULL,
  `match_date` date NOT NULL,
  `match_time` time DEFAULT NULL,
  `home_score` int(11) DEFAULT NULL,
  `away_score` int(11) DEFAULT NULL,
  `status` enum('scheduled','played','postponed','cancelled') DEFAULT 'scheduled',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `fixtures`
--

INSERT INTO `fixtures` (`id`, `season_id`, `competition_id`, `venue_id`, `home_team_id`, `away_team_id`, `match_date`, `match_time`, `home_score`, `away_score`, `status`, `created_at`) VALUES
(1, 1, 1, 1, 1, 2, '2025-07-15', '19:30:00', 2, 1, 'played', '2025-10-07 12:07:28'),
(2, 1, 1, 2, 2, 1, '2025-07-22', '15:00:00', 1, 2, 'played', '2025-10-07 12:07:28'),
(3, 1, 2, 3, 3, 1, '2025-08-05', '14:00:00', 2, 0, 'played', '2025-10-07 12:07:28'),
(4, 1, 1, 1, 1, 5, '2025-08-12', '19:30:00', 3, 0, 'played', '2025-10-07 12:07:28'),
(5, 1, 1, 2, 5, 1, '2025-08-19', '15:00:00', 1, 22, 'played', '2025-10-07 12:07:28'),
(6, 1, 2, 3, 6, 1, '2025-09-02', '14:00:00', 1, 12, 'played', '2025-10-07 12:07:28'),
(7, 2, 1, 1, 1, 3, '2025-04-20', '19:00:00', 2, 2, 'played', '2025-10-07 12:07:28'),
(8, 2, 2, 2, 2, 1, '2025-05-10', '14:00:00', 1, 2, 'played', '2025-10-07 12:07:28'),
(9, 2, 1, 3, 1, 5, '2025-05-17', '15:00:00', 2, 2, 'played', '2025-10-07 12:07:28'),
(10, 2, 2, 1, 1, 6, '2025-06-01', '16:00:00', NULL, NULL, 'scheduled', '2025-10-07 12:07:28');

-- --------------------------------------------------------

--
-- Table structure for table `invites`
--

CREATE TABLE `invites` (
  `id` int(11) NOT NULL,
  `email` varchar(150) NOT NULL,
  `role_id` int(11) NOT NULL,
  `code` varchar(64) NOT NULL,
  `status` enum('pending','used','expired') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `expires_at` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `news`
--

CREATE TABLE `news` (
  `id` int(11) NOT NULL,
  `title` varchar(255) NOT NULL,
  `category` enum('Match Report','Club News','Community') DEFAULT 'Club News',
  `content` text NOT NULL,
  `featured_image` varchar(255) DEFAULT NULL,
  `created_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `news`
--

INSERT INTO `news` (`id`, `title`, `category`, `content`, `featured_image`, `created_by`, `created_at`) VALUES
(1, 'first test', 'Club News', 'Hello World', NULL, 1, '2025-10-04 08:54:10');

-- --------------------------------------------------------

--
-- Table structure for table `pos_locations`
--

CREATE TABLE `pos_locations` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_locations`
--

INSERT INTO `pos_locations` (`id`, `name`, `description`, `created_at`) VALUES
(1, 'Bar', 'Main bar counter', '2025-10-07 11:23:58'),
(2, 'Kiosk', 'Food and snacks kiosk', '2025-10-07 11:23:58'),
(3, 'Merch', 'Merchandise stand', '2025-10-07 11:23:58'),
(4, 'Gate', 'Gate entry / ticket sales', '2025-10-07 11:23:58');

-- --------------------------------------------------------

--
-- Table structure for table `pos_refunds`
--

CREATE TABLE `pos_refunds` (
  `id` int(11) NOT NULL,
  `sale_id` int(11) DEFAULT NULL,
  `refunded_by` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `refund_amount` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pos_sales`
--

CREATE TABLE `pos_sales` (
  `id` int(11) NOT NULL,
  `session_id` int(11) NOT NULL,
  `item_name` varchar(150) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price` decimal(10,2) NOT NULL,
  `payment_method` enum('cash','card') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_sales`
--

INSERT INTO `pos_sales` (`id`, `session_id`, `item_name`, `quantity`, `price`, `payment_method`, `created_at`) VALUES
(1, 1, 'Pint', 1, 4.50, 'cash', '2025-10-14 14:32:03'),
(2, 1, 'Half Pint', 1, 2.50, 'cash', '2025-10-14 14:32:06'),
(3, 1, 'adsfasdf', 1, 23.00, 'cash', '2025-10-14 14:32:31'),
(4, 1, 'Pint', 1, 4.50, 'cash', '2025-10-14 14:32:48'),
(5, 1, 'Half Pint', 1, 2.50, 'cash', '2025-10-14 14:32:50');

-- --------------------------------------------------------

--
-- Table structure for table `pos_sessions`
--

CREATE TABLE `pos_sessions` (
  `id` int(11) NOT NULL,
  `location_id` int(11) NOT NULL,
  `opened_by` int(11) NOT NULL,
  `closed_by` int(11) DEFAULT NULL,
  `start_float` decimal(10,2) NOT NULL,
  `end_float` decimal(10,2) DEFAULT NULL,
  `variance` decimal(10,2) DEFAULT 0.00,
  `status` enum('open','closed') DEFAULT 'open',
  `opened_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `closed_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pos_sessions`
--

INSERT INTO `pos_sessions` (`id`, `location_id`, `opened_by`, `closed_by`, `start_float`, `end_float`, `variance`, `status`, `opened_at`, `closed_at`) VALUES
(1, 1, 1, NULL, 21.00, NULL, 0.00, 'open', '2025-10-14 14:11:53', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `roles`
--

CREATE TABLE `roles` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `roles`
--

INSERT INTO `roles` (`id`, `name`, `created_at`) VALUES
(1, 'Super Admin', '2025-10-04 04:12:17'),
(2, 'Admin', '2025-10-04 04:12:17'),
(3, 'Manager', '2025-10-04 04:12:17'),
(4, 'Volunteer', '2025-10-04 04:12:17'),
(5, 'Coach', '2025-10-07 11:21:10'),
(6, 'Player', '2025-10-07 11:21:10'),
(7, 'Treasurer', '2025-10-07 11:21:10'),
(8, 'Committee', '2025-10-07 11:21:10');

-- --------------------------------------------------------

--
-- Table structure for table `seasons`
--

CREATE TABLE `seasons` (
  `id` int(11) NOT NULL,
  `name` varchar(50) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `status` enum('active','archived','upcoming') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `seasons`
--

INSERT INTO `seasons` (`id`, `name`, `start_date`, `end_date`, `status`, `created_at`) VALUES
(1, '2025/26', '2025-07-01', '2026-06-30', 'active', '2025-10-07 11:38:59'),
(2, '2024/25 (temp)', '2024-07-01', '2025-06-30', 'archived', '2025-10-13 13:32:45'),
(3, '2024/25', '2024-07-01', '2025-06-30', 'archived', '2025-10-07 12:07:28');

-- --------------------------------------------------------

--
-- Table structure for table `season_tickets`
--

CREATE TABLE `season_tickets` (
  `id` int(11) NOT NULL,
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
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `sponsors`
--

CREATE TABLE `sponsors` (
  `id` int(11) NOT NULL,
  `name` varchar(255) DEFAULT NULL,
  `company_name` varchar(150) NOT NULL,
  `contact_name` varchar(100) DEFAULT NULL,
  `contact_email` varchar(150) DEFAULT NULL,
  `tier` enum('Main','Partner','Supporter') DEFAULT 'Supporter',
  `logo` varchar(255) DEFAULT NULL,
  `website` varchar(255) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_items`
--

CREATE TABLE `stock_items` (
  `id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `category` varchar(100) DEFAULT NULL,
  `supplier` varchar(150) DEFAULT NULL,
  `unit_cost` decimal(10,2) DEFAULT NULL,
  `unit_price` decimal(10,2) DEFAULT NULL,
  `quantity` int(11) DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `stock_movements`
--

CREATE TABLE `stock_movements` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `movement_type` enum('delivery','sale','wastage','donation') NOT NULL,
  `quantity` int(11) NOT NULL,
  `reference` varchar(100) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `svfc_sessions`
--

CREATE TABLE `svfc_sessions` (
  `id` varchar(64) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `role_id` int(11) DEFAULT NULL,
  `data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `last_activity` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `expires_at` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `svfc_sessions`
--

INSERT INTO `svfc_sessions` (`id`, `user_id`, `role_id`, `data`, `created_at`, `last_activity`, `expires_at`) VALUES
('0059a9c1ae8eb8df9abd87adcb547964', NULL, NULL, NULL, '2025-10-13 14:37:20', '2025-10-13 14:37:20', '2025-10-14 14:37:20'),
('08d6e11dbab7164cdf815480b6c45163', NULL, NULL, NULL, '2025-10-13 14:09:14', '2025-10-13 14:09:14', '2025-10-14 14:09:14'),
('0f35c33916a2b1d275b5df1ee2ee6eed', NULL, NULL, NULL, '2025-10-13 14:32:28', '2025-10-13 14:32:28', '2025-10-14 14:32:28'),
('1d82541aafc99728206576f25620bcf0', NULL, NULL, NULL, '2025-10-13 14:08:00', '2025-10-13 14:08:00', '2025-10-14 14:08:00'),
('32db3b605af4193baeb9a05f4f9bfb51', NULL, NULL, NULL, '2025-10-13 14:32:06', '2025-10-13 14:32:06', '2025-10-14 14:32:06'),
('5638c8a136ef46dd13864801970f181b', NULL, NULL, NULL, '2025-10-13 14:32:01', '2025-10-13 14:32:01', '2025-10-14 14:32:01'),
('8e6a99b026902f1d9c314a99a2ba05dd', NULL, NULL, NULL, '2025-10-13 14:37:18', '2025-10-13 14:37:18', '2025-10-14 14:37:18'),
('914a1853a05f623264acdb2912167836', NULL, NULL, NULL, '2025-10-13 14:32:28', '2025-10-13 14:32:28', '2025-10-14 14:32:28'),
('9d51fb2ec9334146d07b4f7ccf435544', NULL, NULL, NULL, '2025-10-13 14:09:10', '2025-10-13 14:09:10', '2025-10-14 14:09:10'),
('a73426882a414b528365b624319db672', NULL, NULL, NULL, '2025-10-13 14:12:41', '2025-10-13 14:16:32', '2025-10-14 14:12:41'),
('aa49002fe2f530375d08a7c39dfc2020', NULL, NULL, NULL, '2025-10-13 14:09:25', '2025-10-13 14:09:25', '2025-10-14 14:09:25'),
('bac632f0ba7057146163f0e801344054', NULL, NULL, NULL, '2025-10-13 14:32:06', '2025-10-13 14:32:06', '2025-10-14 14:32:06'),
('c112d4fc3cea7d879d9cb47e0ca59e51', NULL, NULL, NULL, '2025-10-13 14:44:23', '2025-10-13 14:44:24', '2025-10-14 14:44:23'),
('c4a01f1e38d10ca7f9e68aaef05989b8', NULL, NULL, NULL, '2025-10-13 14:32:06', '2025-10-13 14:32:06', '2025-10-14 14:32:06'),
('cdde8fe8443cc8a5d030bfb7a635787c', NULL, NULL, NULL, '2025-10-13 14:32:00', '2025-10-13 14:32:00', '2025-10-14 14:32:00'),
('dea964f9595dcd65bad244012efd39b6', NULL, NULL, NULL, '2025-10-13 14:09:08', '2025-10-13 14:09:08', '2025-10-14 14:09:08');

-- --------------------------------------------------------

--
-- Table structure for table `teams`
--

CREATE TABLE `teams` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `short_name` varchar(50) DEFAULT NULL,
  `logo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `teams`
--

INSERT INTO `teams` (`id`, `name`, `short_name`, `logo`, `created_at`) VALUES
(1, 'Saltcoats Victoria FC', 'SVFC', NULL, '2025-10-07 12:04:39'),
(2, 'Gateside Rovers', 'ROV', NULL, '2025-10-07 12:04:39'),
(3, 'Port Glasgow United', 'PGU', NULL, '2025-10-07 12:04:39'),
(4, 'Ayr Town', 'AYR', NULL, '2025-10-07 12:04:39'),
(5, 'Kilmarnock Colts', 'KIL', NULL, '2025-10-07 12:04:39'),
(6, 'Greenock Athletic', 'GRK', NULL, '2025-10-07 12:04:39');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `role_id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(150) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT NULL ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `role_id`, `name`, `email`, `password_hash`, `status`, `created_at`, `updated_at`) VALUES
(1, 1, 'Colin Lundy', 'colin@lundy.me.uk', '$2y$12$8z.RlRQ8IgCrzeryfepPjuXKNSZLiUIG5B5dUpU3zkPUVzxjG2pUO', 'active', '2025-10-04 04:12:17', '2025-10-14 12:47:01');

-- --------------------------------------------------------

--
-- Table structure for table `venues`
--

CREATE TABLE `venues` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` varchar(255) DEFAULT NULL,
  `city` varchar(100) DEFAULT NULL,
  `postcode` varchar(20) DEFAULT NULL,
  `capacity` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `venues`
--

INSERT INTO `venues` (`id`, `name`, `address`, `city`, `postcode`, `capacity`, `created_at`) VALUES
(1, 'Victoria Park', '31 Jack’s Road', 'Saltcoats', 'KA21 5SH', 1000, '2025-10-07 12:04:39'),
(2, 'Rovers Stadium', '12 Riverside Drive', 'Gateside', 'KA11 4JJ', 1200, '2025-10-07 12:04:39'),
(3, 'Greenock Arena', '100 Main Street', 'Greenock', 'PA15 1AB', 1500, '2025-10-07 12:04:39');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `competitions`
--
ALTER TABLE `competitions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_competitions_name` (`name`);

--
-- Indexes for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD PRIMARY KEY (`id`),
  ADD KEY `season_id` (`season_id`),
  ADD KEY `competition_id` (`competition_id`),
  ADD KEY `venue_id` (`venue_id`),
  ADD KEY `home_team_id` (`home_team_id`),
  ADD KEY `away_team_id` (`away_team_id`);

--
-- Indexes for table `invites`
--
ALTER TABLE `invites`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `code` (`code`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `news`
--
ALTER TABLE `news`
  ADD PRIMARY KEY (`id`),
  ADD KEY `created_by` (`created_by`);

--
-- Indexes for table `pos_locations`
--
ALTER TABLE `pos_locations`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `pos_refunds`
--
ALTER TABLE `pos_refunds`
  ADD PRIMARY KEY (`id`),
  ADD KEY `sale_id` (`sale_id`),
  ADD KEY `refunded_by` (`refunded_by`),
  ADD KEY `fk_pos_refunds_session` (`session_id`);

--
-- Indexes for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `pos_sessions`
--
ALTER TABLE `pos_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `location_id` (`location_id`),
  ADD KEY `opened_by` (`opened_by`),
  ADD KEY `fk_pos_sessions_closed_by` (`closed_by`);

--
-- Indexes for table `roles`
--
ALTER TABLE `roles`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `seasons`
--
ALTER TABLE `seasons`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_seasons_name` (`name`);

--
-- Indexes for table `season_tickets`
--
ALTER TABLE `season_tickets`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `seat_number` (`seat_number`),
  ADD KEY `idx_season_tickets_season` (`season`),
  ADD KEY `idx_season_tickets_status` (`status`),
  ADD KEY `idx_season_tickets_email` (`email`);

--
-- Indexes for table `sponsors`
--
ALTER TABLE `sponsors`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_items`
--
ALTER TABLE `stock_items`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indexes for table `svfc_sessions`
--
ALTER TABLE `svfc_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `expires_at` (`expires_at`);

--
-- Indexes for table `teams`
--
ALTER TABLE `teams`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_teams_name` (`name`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `role_id` (`role_id`);

--
-- Indexes for table `venues`
--
ALTER TABLE `venues`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uq_venues_name` (`name`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` bigint(20) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=78;

--
-- AUTO_INCREMENT for table `competitions`
--
ALTER TABLE `competitions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `fixtures`
--
ALTER TABLE `fixtures`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `invites`
--
ALTER TABLE `invites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `news`
--
ALTER TABLE `news`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `pos_locations`
--
ALTER TABLE `pos_locations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pos_refunds`
--
ALTER TABLE `pos_refunds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pos_sales`
--
ALTER TABLE `pos_sales`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pos_sessions`
--
ALTER TABLE `pos_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `roles`
--
ALTER TABLE `roles`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=9;

--
-- AUTO_INCREMENT for table `seasons`
--
ALTER TABLE `seasons`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `season_tickets`
--
ALTER TABLE `season_tickets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `sponsors`
--
ALTER TABLE `sponsors`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_items`
--
ALTER TABLE `stock_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `stock_movements`
--
ALTER TABLE `stock_movements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `teams`
--
ALTER TABLE `teams`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `venues`
--
ALTER TABLE `venues`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD CONSTRAINT `audit_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `fixtures`
--
ALTER TABLE `fixtures`
  ADD CONSTRAINT `fixtures_ibfk_1` FOREIGN KEY (`season_id`) REFERENCES `seasons` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_2` FOREIGN KEY (`competition_id`) REFERENCES `competitions` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_3` FOREIGN KEY (`venue_id`) REFERENCES `venues` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_4` FOREIGN KEY (`home_team_id`) REFERENCES `teams` (`id`),
  ADD CONSTRAINT `fixtures_ibfk_5` FOREIGN KEY (`away_team_id`) REFERENCES `teams` (`id`);

--
-- Constraints for table `invites`
--
ALTER TABLE `invites`
  ADD CONSTRAINT `invites_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`);

--
-- Constraints for table `news`
--
ALTER TABLE `news`
  ADD CONSTRAINT `news_ibfk_1` FOREIGN KEY (`created_by`) REFERENCES `users` (`id`) ON DELETE SET NULL;

--
-- Constraints for table `pos_refunds`
--
ALTER TABLE `pos_refunds`
  ADD CONSTRAINT `fk_pos_refunds_session` FOREIGN KEY (`session_id`) REFERENCES `pos_sessions` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pos_refunds_ibfk_1` FOREIGN KEY (`sale_id`) REFERENCES `pos_sales` (`id`) ON DELETE SET NULL,
  ADD CONSTRAINT `pos_refunds_ibfk_2` FOREIGN KEY (`refunded_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `pos_sales`
--
ALTER TABLE `pos_sales`
  ADD CONSTRAINT `pos_sales_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `pos_sessions` (`id`);

--
-- Constraints for table `pos_sessions`
--
ALTER TABLE `pos_sessions`
  ADD CONSTRAINT `fk_pos_sessions_closed_by` FOREIGN KEY (`closed_by`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `pos_sessions_ibfk_1` FOREIGN KEY (`location_id`) REFERENCES `pos_locations` (`id`),
  ADD CONSTRAINT `pos_sessions_ibfk_2` FOREIGN KEY (`opened_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `stock_movements`
--
ALTER TABLE `stock_movements`
  ADD CONSTRAINT `stock_movements_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `stock_items` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`role_id`) REFERENCES `roles` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
