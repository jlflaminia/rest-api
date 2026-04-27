-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2026 at 05:25 AM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `aclcapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(120) NOT NULL,
  `full_name` varchar(120) NOT NULL,
  `avatar` longtext DEFAULT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` varchar(30) NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `username`, `email`, `full_name`, `avatar`, `password_hash`, `role`, `created_at`, `updated_at`) VALUES
(1, 'jlflaminia', 'jlflaminia1@gmail.com', 'JL FLAMINIA', NULL, 'jlflaminia', 'admin', '2026-04-27 02:37:47', '2026-04-27 02:37:47'),
(2, 'admin', 'admin@aclc.edu', 'Administrator', NULL, '$2y$10$4QsO5ZBaqPjwfKqnlnsntuQiI/JX3Ixlub.BcQ85B04qqnyLx5m2u', 'admin', '2026-04-27 02:46:33', '2026-04-27 02:57:30');

-- --------------------------------------------------------

--
-- Table structure for table `api_tokens`
--

CREATE TABLE `api_tokens` (
  `id` int(10) UNSIGNED NOT NULL,
  `user_id` int(11) NOT NULL,
  `token` varchar(64) NOT NULL,
  `expires_at` datetime NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `api_tokens`
--

INSERT INTO `api_tokens` (`id`, `user_id`, `token`, `expires_at`, `created_at`) VALUES
(1, 2, '4b4acd1af6df3d8dd6cb5a0691185542593eb9481ce7bd589b982a1a35af1ca6', '2026-04-28 10:57:47', '2026-04-27 02:57:47'),
(2, 2, 'bb69c7cb8dbb86c6f353db0c30ae8c281d73d03785e3241949b01b5392feb55c', '2026-04-28 10:59:58', '2026-04-27 02:59:58'),
(3, 2, '2df77c3317b8d31dc58f0d76e6682819725be3065329e0dd38ed7c77cd17dd9b', '2026-04-28 11:20:17', '2026-04-27 03:20:17');

-- --------------------------------------------------------

--
-- Table structure for table `bse_students`
--

CREATE TABLE `bse_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `program` varchar(150) NOT NULL DEFAULT '',
  `year_level` varchar(50) NOT NULL DEFAULT '',
  `gmail` varchar(150) NOT NULL DEFAULT '',
  `downpayment_date` datetime DEFAULT NULL,
  `prelim_date` datetime DEFAULT NULL,
  `midterm_date` datetime DEFAULT NULL,
  `prefinal_date` datetime DEFAULT NULL,
  `final_date` datetime DEFAULT NULL,
  `total_balance_date` datetime DEFAULT NULL,
  `downpayment_paid_amount` decimal(12,2) DEFAULT NULL,
  `prelim_paid_amount` decimal(12,2) DEFAULT NULL,
  `midterm_paid_amount` decimal(12,2) DEFAULT NULL,
  `prefinal_paid_amount` decimal(12,2) DEFAULT NULL,
  `final_paid_amount` decimal(12,2) DEFAULT NULL,
  `total_balance_paid_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `bse_students`
--

INSERT INTO `bse_students` (`id`, `student_id`, `name`, `program`, `year_level`, `gmail`, `downpayment_date`, `prelim_date`, `midterm_date`, `prefinal_date`, `final_date`, `total_balance_date`, `downpayment_paid_amount`, `prelim_paid_amount`, `midterm_paid_amount`, `prefinal_paid_amount`, `final_paid_amount`, `total_balance_paid_amount`, `created_at`, `updated_at`) VALUES
(1, 'BSE-2024-001', 'JL Flaminia', 'BSE', '1st Year', 'jlflaminia@aclc.edu', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 03:02:24', '2026-04-27 03:02:24'),
(3, 'BSE-2024-002', 'Mac Mac', 'BSE', '3st Year', 'macmac@gmail.com', NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, NULL, '2026-04-27 03:23:21', '2026-04-27 03:23:21');

-- --------------------------------------------------------

--
-- Table structure for table `bsis_students`
--

CREATE TABLE `bsis_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL,
  `program` varchar(150) NOT NULL DEFAULT '',
  `year_level` varchar(50) NOT NULL DEFAULT '',
  `gmail` varchar(150) NOT NULL DEFAULT '',
  `downpayment_date` datetime DEFAULT NULL,
  `prelim_date` datetime DEFAULT NULL,
  `midterm_date` datetime DEFAULT NULL,
  `prefinal_date` datetime DEFAULT NULL,
  `final_date` datetime DEFAULT NULL,
  `total_balance_date` datetime DEFAULT NULL,
  `downpayment_paid_amount` decimal(12,2) DEFAULT NULL,
  `prelim_paid_amount` decimal(12,2) DEFAULT NULL,
  `midterm_paid_amount` decimal(12,2) DEFAULT NULL,
  `prefinal_paid_amount` decimal(12,2) DEFAULT NULL,
  `final_paid_amount` decimal(12,2) DEFAULT NULL,
  `total_balance_paid_amount` decimal(12,2) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `removed_students`
--

CREATE TABLE `removed_students` (
  `id` int(10) UNSIGNED NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `name` varchar(150) NOT NULL DEFAULT '',
  `program` varchar(150) NOT NULL DEFAULT '',
  `year_level` varchar(50) NOT NULL DEFAULT '',
  `gmail` varchar(150) NOT NULL DEFAULT '',
  `removed_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_admin_username` (`username`),
  ADD UNIQUE KEY `uniq_admin_email` (`email`);

--
-- Indexes for table `api_tokens`
--
ALTER TABLE `api_tokens`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_token` (`token`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_expires_at` (`expires_at`);

--
-- Indexes for table `bse_students`
--
ALTER TABLE `bse_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_bse_student_id` (`student_id`);

--
-- Indexes for table `bsis_students`
--
ALTER TABLE `bsis_students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `uniq_bsis_student_id` (`student_id`);

--
-- Indexes for table `removed_students`
--
ALTER TABLE `removed_students`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_removed_student_id` (`student_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `api_tokens`
--
ALTER TABLE `api_tokens`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bse_students`
--
ALTER TABLE `bse_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `bsis_students`
--
ALTER TABLE `bsis_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `removed_students`
--
ALTER TABLE `removed_students`
  MODIFY `id` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
