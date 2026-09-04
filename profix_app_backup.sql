-- Database dump for `lily_app`
-- Generated for Namecheap cPanel Deployment
-- Date: 2026-09-02 15:10:02

SET FOREIGN_KEY_CHECKS=0;
SET SQL_MODE = 'NO_AUTO_VALUE_ON_ZERO';
START TRANSACTION;
SET time_zone = '+00:00';

-- --------------------------------------------------------
-- Table structure for `app_activity_log`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_activity_log`;
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

-- --------------------------------------------------------
-- Table structure for `app_attendance`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_attendance`;
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
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_attendance`

INSERT INTO `app_attendance` (`id`, `project_id`, `category_id`, `worker_id`, `work_date`, `daily_rate`, `attendance_multiplier`, `earned`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('9', '14', NULL, '3', '2026-05-05', '1100.00', '1.0', '1100.00', '', '1', '1', '2026-08-28 17:09:35', '2026-08-28 20:29:59', '0'),
('10', '14', NULL, '3', '2026-05-06', '1100.00', '1.0', '1100.00', '', '1', '1', '2026-08-28 17:09:35', '2026-08-28 20:30:04', '0'),
('11', '14', NULL, '3', '2026-05-07', '1100.00', '1.0', '1100.00', '', '0', '1', '2026-08-28 17:09:35', '2026-08-28 17:09:35', '0'),
('12', '14', NULL, '2', '2099-12-10', '1500.00', '1.5', '2250.00', '', '1', '1', '2026-08-28 17:51:32', '2026-08-28 17:52:07', '0'),
('13', '14', NULL, '2', '2099-12-02', '1200.00', '1.0', '1200.00', '', '1', '1', '2026-08-28 17:51:32', '2026-08-28 17:52:07', '0'),
('14', '14', NULL, '2', '2099-12-03', '1200.00', '1.0', '1200.00', '', '1', '1', '2026-08-28 17:51:32', '2026-08-28 17:52:07', '0'),
('15', '14', NULL, '4', '2026-05-17', '1100.00', '1.0', '1100.00', '', '0', '1', '2026-08-28 17:59:02', '2026-08-28 17:59:02', '0'),
('16', '11', NULL, '5', '2026-08-28', '1200.00', '1.0', '1200.00', '', '0', '1', '2026-08-28 18:02:12', '2026-08-28 18:02:12', '0'),
('17', '14', NULL, '4', '2099-01-05', '700.00', '1.0', '700.00', NULL, '1', '1', '2026-08-28 18:29:47', '2026-08-28 18:31:20', '0'),
('18', '14', NULL, '5', '2099-01-05', '600.00', '1.0', '600.00', NULL, '1', '1', '2026-08-28 18:29:47', '2026-08-28 18:31:20', '0'),
('19', '14', NULL, '4', '2099-01-06', '700.00', '1.0', '700.00', NULL, '1', '1', '2026-08-28 18:29:47', '2026-08-28 18:31:20', '0'),
('20', '14', NULL, '8', '2026-08-28', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 18:46:29', '2026-08-28 18:46:29', '0'),
('21', '11', NULL, '8', '2026-08-20', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 18:46:46', '2026-08-28 18:46:46', '0'),
('22', '11', NULL, '8', '2026-08-21', '1000.00', '1.0', '1000.00', '', '1', '1', '2026-08-28 18:46:46', '2026-08-29 18:18:09', '0'),
('23', '11', NULL, '8', '2026-08-22', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 18:46:46', '2026-08-28 18:46:46', '0'),
('24', '11', NULL, '8', '2026-08-23', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 18:47:01', '2026-08-28 18:47:01', '0'),
('25', '11', NULL, '4', '2026-08-14', '1200.00', '1.0', '1200.00', '', '0', '1', '2026-08-28 19:34:55', '2026-08-28 19:34:55', '0'),
('26', '11', NULL, '8', '2026-08-01', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('27', '11', NULL, '8', '2026-08-02', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('28', '11', NULL, '8', '2026-08-03', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('29', '11', NULL, '8', '2026-08-04', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('30', '11', NULL, '8', '2026-08-05', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('31', '11', NULL, '8', '2026-08-06', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('32', '11', NULL, '8', '2026-08-07', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('33', '11', NULL, '8', '2026-08-08', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('34', '11', NULL, '8', '2026-08-09', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('35', '11', NULL, '8', '2026-08-10', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('36', '11', NULL, '8', '2026-08-11', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('37', '11', NULL, '8', '2026-08-12', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('38', '11', NULL, '8', '2026-08-13', '1000.00', '1.0', '1000.00', '', '0', '1', '2026-08-28 20:12:11', '2026-08-28 20:12:11', '0'),
('39', '11', NULL, '8', '2026-08-14', '1000.00', '1.0', '1000.00', '', '1', '1', '2026-08-28 20:12:11', '2026-08-29 18:18:32', '0'),
('40', '11', NULL, '8', '2026-08-15', '1000.00', '1.0', '1000.00', '', '1', '1', '2026-08-28 20:12:11', '2026-08-29 18:18:22', '0');

-- --------------------------------------------------------
-- Table structure for `app_categories`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_categories`;
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
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_categories`

INSERT INTO `app_categories` (`id`, `name`, `billing_type`, `is_default`, `is_active`, `created_at`, `updated_at`, `synced`, `sort_order`) VALUES
('1', 'Board & Wood', 'purchase_contractor', '1', '1', '2026-08-18 12:01:29', '2026-08-18 12:01:29', '0', '0'),
('2', 'Paint', 'attendance', '1', '1', '2026-08-18 12:01:29', '2026-08-18 12:01:29', '0', '0'),
('3', 'Electrical & Sanitary', 'attendance', '1', '1', '2026-08-18 12:01:29', '2026-08-18 12:01:29', '0', '0'),
('4', 'Thai & Glass', 'purchase_contractor', '1', '1', '2026-08-18 12:01:29', '2026-08-18 12:01:29', '0', '0'),
('5', 'Supply', 'purchase_only', '1', '1', '2026-08-18 12:01:29', '2026-08-18 12:01:29', '0', '0');

-- --------------------------------------------------------
-- Table structure for `app_client_payments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_client_payments`;
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

-- Dumping data for table `app_client_payments`

INSERT INTO `app_client_payments` (`id`, `project_id`, `amount`, `payment_date`, `payment_method`, `receipt_photo_path`, `who_received`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('4', '14', '5000.00', '2026-07-02', 'Cash', NULL, NULL, '44', '0', '1', '2026-08-28 17:25:24', '2026-08-28 17:25:24', '0');

-- --------------------------------------------------------
-- Table structure for `app_contractor_advances`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_contractor_advances`;
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
) ENGINE=InnoDB AUTO_INCREMENT=11 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_contractor_advances`

INSERT INTO `app_contractor_advances` (`id`, `project_id`, `contractor_id`, `category_id`, `amount`, `payment_date`, `payment_method`, `who_paid`, `who_received`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('5', '14', '1', NULL, '5000.00', '2026-08-28', 'Cash', 'admin', '', '', '0', '1', '2026-08-28 17:26:03', '2026-08-28 17:26:03', '0'),
('6', '11', '5', NULL, '500.00', '2026-08-28', 'Cash', 'admin', 'bab', '', '0', '1', '2026-08-28 18:24:33', '2026-08-28 18:24:33', '0'),
('7', '14', '5', NULL, '1000.00', '2099-01-06', 'Cash', 'admin', NULL, NULL, '1', '1', '2026-08-28 18:29:47', '2026-08-28 18:31:20', '0'),
('8', '14', '5', NULL, '1000.00', '2026-08-28', 'Cash', 'admin', 'babu', '', '0', '1', '2026-08-28 18:45:28', '2026-08-28 18:45:28', '0'),
('9', '11', '1', NULL, '1000.00', '2026-08-28', 'Cash', 'admin', '', '', '0', '1', '2026-08-28 18:51:36', '2026-08-28 18:51:36', '0'),
('10', '11', '1', NULL, '50000.00', '2026-08-28', 'Cash', 'admin', 'musa', '', '0', '1', '2026-08-28 20:58:36', '2026-08-28 20:58:36', '0');

-- --------------------------------------------------------
-- Table structure for `app_contractor_bills`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_contractor_bills`;
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

-- --------------------------------------------------------
-- Table structure for `app_contractors`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_contractors`;
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

-- Dumping data for table `app_contractors`

INSERT INTO `app_contractors` (`id`, `name`, `phone`, `address`, `trade`, `notes`, `is_active`, `created_at`, `updated_at`, `synced`) VALUES
('1', 'Musa Seikh', '01311389641', '', 'Carpenter', '', '1', '2026-08-19 14:33:36', '2026-08-26 12:33:20', '0'),
('2', 'Shakhawat Hossain', '01879218041', NULL, 'Thai Glass', '', '1', '2026-08-19 17:31:51', '2026-08-19 18:42:35', '0'),
('3', 'Arif', '01714376116', NULL, 'Painter', '', '1', '2026-08-19 18:41:46', '2026-08-19 18:41:46', '0'),
('4', 'Nuruzzaman', '01725117354', 'Jahaj Building - Uttar Badda', 'Paint', '', '0', '2026-08-27 02:05:44', '2026-08-28 18:42:08', '0'),
('5', 'Afsar', '01818628034', 'Uttar Badda ORG: FENI', 'Electritian', '', '1', '2026-08-28 18:00:33', '2026-08-28 18:43:04', '0');

-- --------------------------------------------------------
-- Table structure for `app_expenses`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_expenses`;
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

-- Dumping data for table `app_expenses`

INSERT INTO `app_expenses` (`id`, `project_id`, `category`, `description`, `vendor`, `amount`, `paid`, `payment_method`, `expense_date`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('1', '14', 'Transport', 'updated', 'V2', '1600.00', '1600.00', 'bKash', '2026-08-30', 'x', '1', '1', '2026-08-29 21:05:35', '2026-08-29 21:06:25', '0'),
('2', '14', 'Transport', 'UI harness expense', 'Harness Vendor', '2200.00', '2200.00', 'Cash', '2026-08-29', 'created by verify25', '1', '1', '2026-08-29 21:07:29', '2026-08-29 21:13:50', '0');

-- --------------------------------------------------------
-- Table structure for `app_glass_advances`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_glass_advances`;
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

-- --------------------------------------------------------
-- Table structure for `app_items_master`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_items_master`;
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

-- --------------------------------------------------------
-- Table structure for `app_notifications`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_notifications`;
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

-- --------------------------------------------------------
-- Table structure for `app_printouts`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_printouts`;
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

-- --------------------------------------------------------
-- Table structure for `app_project_contractors`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_project_contractors`;
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
) ENGINE=InnoDB AUTO_INCREMENT=13 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_project_contractors`

INSERT INTO `app_project_contractors` (`id`, `project_id`, `contractor_id`, `category_id`, `start_date`, `notes`, `created_at`, `updated_at`, `synced`) VALUES
('8', '14', '3', NULL, NULL, NULL, '2026-08-28 17:19:42', '2026-08-28 17:19:42', '0'),
('9', '14', '1', NULL, NULL, NULL, '2026-08-28 17:19:48', '2026-08-28 17:19:48', '0'),
('10', '11', '5', NULL, NULL, NULL, '2026-08-28 18:20:01', '2026-08-28 18:20:01', '0'),
('11', '11', '3', NULL, NULL, NULL, '2026-08-28 18:50:29', '2026-08-28 18:50:29', '0'),
('12', '11', '1', NULL, NULL, NULL, '2026-08-28 18:50:49', '2026-08-28 18:50:49', '0');

-- --------------------------------------------------------
-- Table structure for `app_project_images`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_project_images`;
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

-- Dumping data for table `app_project_images`

INSERT INTO `app_project_images` (`id`, `project_id`, `image_path`, `caption`, `is_primary`, `sort_order`, `created_at`) VALUES
('5', '11', 'uploads/projects/proj_11_1787913890_6604c4d7.jpg', NULL, '1', '0', '2026-08-28 16:44:50'),
('12', '14', 'uploads/projects/proj_14_1787917173_91f04709.jpg', NULL, '1', '0', '2026-08-28 17:39:33');

-- --------------------------------------------------------
-- Table structure for `app_projects`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_projects`;
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
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_projects`

INSERT INTO `app_projects` (`id`, `name`, `address`, `client_name`, `client_phone`, `client_email`, `client_address`, `project_type`, `status`, `estimated_budget`, `start_date`, `end_date`, `project_image`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('11', 'Shiddeshsori', 'Siddheswari Kali Mandir Ground, 11, Near Mouchak market, Shiddheswari Ln, Dhaka 1217', 'Arpan', '018888888888', '', '', 'Residential', 'Ongoing', '0.00', '2026-08-28', NULL, 'uploads/projects/proj_11_1787913890_6604c4d7.jpg', '', '0', '1', '2026-08-28 16:40:03', '2026-08-28 16:44:50', '0'),
('14', 'Gendaria', 'dfds', 'dsfdgd', '', 'fdgf', '', 'Residential', 'Ongoing', '0.00', '2026-08-28', NULL, 'uploads/projects/proj_14_1787917173_91f04709.jpg', '', '0', '1', '2026-08-28 17:09:35', '2026-08-28 17:39:33', '0');

-- --------------------------------------------------------
-- Table structure for `app_purchases`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_purchases`;
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

-- --------------------------------------------------------
-- Table structure for `app_schedules`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_schedules`;
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

-- Dumping data for table `app_schedules`

INSERT INTO `app_schedules` (`id`, `project_id`, `schedule_date`, `category`, `description`, `is_done`, `created_by`, `created_at`, `updated_at`, `is_deleted`) VALUES
('1', NULL, '2026-08-26', 'Board', 'dsfdsfdsdfd\r\ndfdslfdsl', '1', '1', '2026-08-26 12:31:11', '2026-08-26 12:31:59', '0'),
('2', NULL, '2026-05-27', 'Board', 'dsfdsfdsdfd\r\ndfd\r\ndfsf slfdsl', '0', '1', '2026-08-26 12:31:53', '2026-08-26 12:31:53', '0'),
('3', NULL, '2026-09-27', 'Board', '15pc particle', '0', '1', '2026-08-27 00:35:19', '2026-08-27 00:35:19', '0'),
('4', NULL, '2026-09-28', 'Board', '15pc particle', '0', '1', '2026-08-27 00:35:43', '2026-08-27 00:35:43', '0'),
('5', NULL, '2026-08-05', NULL, 'board kinbo pvc', '0', '1', '2026-08-27 02:25:58', '2026-08-27 02:25:58', '0'),
('9', NULL, '2026-08-30', NULL, 'vuisuiit', '0', '1', '2026-08-28 16:55:06', '2026-08-28 16:55:06', '0');

-- --------------------------------------------------------
-- Table structure for `app_settings`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_settings`;
CREATE TABLE `app_settings` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  PRIMARY KEY (`id`),
  UNIQUE KEY `setting_key` (`setting_key`)
) ENGINE=InnoDB AUTO_INCREMENT=46 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_settings`

INSERT INTO `app_settings` (`id`, `setting_key`, `setting_value`, `updated_at`) VALUES
('1', 'company_name', 'Lily Interiors - লিলি ইন্টেরিয়র\'স', '2026-08-29 18:01:14'),
('2', 'company_address', '36 Bir Uttam C.R Dutta Road, Hatirpool, Dhaka-1205', '2026-08-19 18:44:34'),
('3', 'company_phone', '+88 02 44612456, +88 01734182694', '2026-08-19 18:44:34'),
('4', 'company_email', '', '2026-08-18 12:01:30'),
('5', 'pdf_margin_top', '30', '2026-08-18 12:01:30'),
('6', 'pdf_margin_bottom', '30', '2026-08-18 12:01:30'),
('7', 'session_timeout', '7200', '2026-08-18 12:01:30'),
('8', 'default_language', 'en', '2026-08-19 17:14:28'),
('9', 'default_theme', 'light', '2026-08-18 12:01:30'),
('13', 'currency_symbol', 'Tk.', '2026-08-24 12:18:18'),
('25', 'company_whatsapp', '', '2026-08-19 20:18:55'),
('26', 'company_facebook', '', '2026-08-19 20:18:55'),
('27', 'company_website', '', '2026-08-19 20:18:55'),
('29', 'app_version', '1.0.0', '2026-08-19 20:18:55'),
('30', 'text_language', 'both', '2026-08-19 20:18:55'),
('33', 'pdf_margin_left', '15', '2026-08-24 12:18:04'),
('34', 'pdf_margin_right', '15', '2026-08-24 12:18:04'),
('35', 'print_language', 'en', '2026-08-24 12:18:04');

-- --------------------------------------------------------
-- Table structure for `app_supply_purchases`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_supply_purchases`;
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
) ENGINE=InnoDB AUTO_INCREMENT=15 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_supply_purchases`

INSERT INTO `app_supply_purchases` (`id`, `project_id`, `contractor_id`, `item_name`, `supply_category`, `category_id`, `board_type`, `board_thickness`, `board_size`, `color_finish`, `size`, `quantity`, `unit`, `rate`, `total`, `supplier`, `purchase_date`, `notes`, `purchased_by`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('12', '14', NULL, 'Partex', 'Board & Wood', NULL, NULL, NULL, NULL, NULL, NULL, '5.00', 'pcs', '1500.00', '7500.00', NULL, '2026-08-28', NULL, 'admin', '0', '1', '2026-08-28 17:36:54', '2026-08-28 17:36:54', '0'),
('13', '14', NULL, 'Melamine', 'Board & Wood', NULL, NULL, NULL, NULL, NULL, NULL, '5.00', 'pcs', '2400.00', '12000.00', NULL, '2026-08-28', NULL, 'admin', '0', '1', '2026-08-28 17:37:22', '2026-08-28 17:37:22', '0'),
('14', '14', NULL, 'UPvc pipe', 'Electrical & Sanitary', NULL, NULL, NULL, NULL, NULL, NULL, '10.00', 'ft', '40.00', '400.00', NULL, '2026-08-29', NULL, 'admin', '0', '1', '2026-08-29 18:29:23', '2026-08-29 18:29:23', '0');

-- --------------------------------------------------------
-- Table structure for `app_thai_glass_bills`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_thai_glass_bills`;
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

-- --------------------------------------------------------
-- Table structure for `app_users`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_users`;
CREATE TABLE `app_users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('owner','manager') NOT NULL,
  `assigned_projects` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin DEFAULT NULL CHECK (json_valid(`assigned_projects`)),
  `is_active` tinyint(4) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `synced` tinyint(4) DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB AUTO_INCREMENT=2 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_users`

INSERT INTO `app_users` (`id`, `username`, `email`, `password_hash`, `role`, `assigned_projects`, `is_active`, `last_login`, `created_at`, `updated_at`, `synced`) VALUES
('1', 'admin', 'admin@lilyinteriorsbd.com', '$2y$12$zMvd3N794MDlusDJDiQNM.VXTBDLJ36uLeq4SFLzLPYfJpYPddNxm', 'owner', NULL, '1', '2026-09-04 15:40:00', '2026-08-18 12:01:30', '2026-09-04 15:40:00', '0'),
('2', 'lilyweb', 'lilyweb@lilyinteriorsbd.com', '$2y$12$zMvd3N794MDlusDJDiQNM.VXTBDLJ36uLeq4SFLzLPYfJpYPddNxm', 'owner', NULL, '1', '2026-09-04 15:40:00', '2026-09-04 15:40:00', '2026-09-04 15:40:00', '0');

-- --------------------------------------------------------
-- Table structure for `app_worker_payments`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_worker_payments`;
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
) ENGINE=InnoDB AUTO_INCREMENT=10 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_worker_payments`

INSERT INTO `app_worker_payments` (`id`, `project_id`, `worker_id`, `category_id`, `amount`, `payment_date`, `payment_method`, `who_paid`, `who_received`, `notes`, `is_deleted`, `created_by`, `created_at`, `updated_at`, `synced`) VALUES
('6', '14', '3', NULL, '500.00', '2026-05-03', 'Cash', 'admin', '', '', '0', '1', '2026-08-28 17:09:35', '2026-08-28 17:09:35', '0'),
('7', '14', '5', NULL, '300.00', '2099-01-06', 'Cash', 'admin', NULL, NULL, '1', '1', '2026-08-28 18:29:47', '2026-08-28 18:31:20', '0'),
('8', '11', '8', NULL, '3500.00', '2026-08-28', 'Cash', 'admin', '', '', '0', '1', '2026-08-28 19:25:49', '2026-08-28 19:25:49', '0'),
('9', '11', '4', NULL, '500.00', '2026-08-14', 'Cash', 'admin', '', '', '0', '1', '2026-08-28 19:35:25', '2026-08-28 19:35:25', '0');

-- --------------------------------------------------------
-- Table structure for `app_workers`
-- --------------------------------------------------------

DROP TABLE IF EXISTS `app_workers`;
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
) ENGINE=InnoDB AUTO_INCREMENT=9 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Dumping data for table `app_workers`

INSERT INTO `app_workers` (`id`, `name`, `phone`, `address`, `trade`, `contractor_id`, `default_daily_rate`, `notes`, `is_active`, `created_at`, `updated_at`, `synced`) VALUES
('1', 'Sihab', '06655151151', '', 'paint', NULL, '800.00', '', '1', '2026-08-19 17:24:34', '2026-08-26 12:30:04', '0'),
('2', 'Sazzad', '01317388443', '', 'Painter', '3', '1100.00', '', '1', '2026-08-26 12:38:34', '2026-08-28 18:41:46', '0'),
('3', 'Worker', NULL, NULL, '', NULL, NULL, NULL, '0', '2026-08-28 17:09:35', '2026-08-28 18:41:55', '0'),
('4', 'Afsar', '01818628034', 'Uttar Badda, Origin: FENI', 'Electrician', '5', '1200.00', '', '1', '2026-08-28 17:58:09', '2026-08-28 18:40:04', '0'),
('5', 'Babu', '', '', 'Electrician', '5', '1200.00', '', '0', '2026-08-28 17:58:22', '2026-08-28 18:38:16', '0'),
('6', 'zzCrewTest', '', '', 'Electrician', '5', '500.00', '', '0', '2026-08-28 18:31:48', '2026-08-28 18:31:57', '0'),
('7', 'Babu', '', '', 'Electrician', '5', '1000.00', '', '0', '2026-08-28 18:37:59', '2026-08-28 18:38:21', '0'),
('8', 'Babu', '01737225108', 'Badda', 'Electrician', '5', '1000.00', '', '1', '2026-08-28 18:39:12', '2026-08-28 18:39:12', '0');

SET FOREIGN_KEY_CHECKS=1;
COMMIT;
