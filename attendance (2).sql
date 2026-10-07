-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Oct 06, 2026 at 02:32 PM
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
-- Database: `attendance`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance_logs`
--

CREATE TABLE `attendance_logs` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `clock_in` datetime NOT NULL,
  `clock_out` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance_logs`
--

INSERT INTO `attendance_logs` (`id`, `employee_id`, `clock_in`, `clock_out`) VALUES
(5, 1, '2026-10-02 11:46:46', '2026-10-02 11:56:40'),
(6, 2, '2026-10-02 11:46:51', '2026-10-02 11:56:55'),
(7, 1, '2026-10-02 11:57:59', '2026-10-02 12:33:15'),
(8, 2, '2026-10-02 11:58:06', '2026-10-02 12:33:18'),
(9, 3, '2026-10-02 11:59:53', '2026-10-02 12:33:20'),
(10, 2, '2026-10-02 18:24:26', '2026-10-06 09:05:18'),
(11, 3, '2026-10-04 07:26:55', '2026-10-06 09:05:21'),
(12, 1, '2026-10-06 08:11:04', '2026-10-06 09:05:15'),
(13, 1, '2026-10-06 09:06:52', '2026-10-06 10:15:36'),
(14, 2, '2026-10-06 09:07:02', '2026-10-06 09:07:44'),
(15, 3, '2026-10-06 09:07:05', '2026-10-06 10:15:46'),
(16, 2, '2026-10-06 10:15:42', '2026-10-06 10:15:44'),
(17, 1, '2026-10-06 18:25:04', NULL),
(18, 2, '2026-10-06 18:25:49', NULL),
(19, 3, '2026-10-06 18:25:50', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `audit_logs`
--

CREATE TABLE `audit_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `action` varchar(100) DEFAULT NULL,
  `details` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `audit_logs`
--

INSERT INTO `audit_logs` (`id`, `user_id`, `action`, `details`, `created_at`) VALUES
(1, 1, 'update_rate', 'Ralph Harvey Oliva: 50.00 -> 60', '2026-10-02 04:06:41'),
(2, 1, 'set_status', 'Ethan Pogi Masarap Silva: active -> inactive', '2026-10-02 04:06:49'),
(3, 1, 'set_status', 'Ethan Pogi Masarap Silva: inactive -> active', '2026-10-02 04:07:00'),
(4, 1, 'set_status', 'Ethan Pogi Masarap Silva: active -> inactive', '2026-10-02 04:18:24'),
(5, 1, 'set_status', 'Ethan Pogi Masarap Silva: inactive -> active', '2026-10-02 04:18:28'),
(6, 1, 'add_deduction', 'Ralph Harvey Oliva: -20.00 (sick leave)', '2026-10-02 04:24:09'),
(7, 1, 'add_deduction', 'Ralph Harvey Oliva: -20.00 (bastga)', '2026-10-02 04:24:51'),
(8, 1, 'set_status', 'Ralph Harvey Oliva: active -> inactive', '2026-10-03 23:24:57'),
(9, 1, 'set_status', 'Ralph Harvey Oliva: inactive -> active', '2026-10-03 23:25:07'),
(10, 1, 'add_deduction', 'Paul Tenorio: -5.00 (bastga)', '2026-10-06 02:12:53');

-- --------------------------------------------------------

--
-- Table structure for table `deductions`
--

CREATE TABLE `deductions` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `reason` varchar(255) DEFAULT NULL,
  `applied_by` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `deductions`
--

INSERT INTO `deductions` (`id`, `employee_id`, `amount`, `reason`, `applied_by`, `created_at`) VALUES
(1, 1, 20.00, 'sick leave', 1, '2026-10-02 04:24:09'),
(2, 1, 20.00, 'bastga', 1, '2026-10-02 04:24:51'),
(3, 2, 5.00, 'bastga', 1, '2026-10-06 02:12:53');

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `fingerprint_id` int(11) DEFAULT NULL,
  `hourly_rate` decimal(10,2) NOT NULL DEFAULT 0.00,
  `status` enum('active','inactive') DEFAULT 'active'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `name`, `fingerprint_id`, `hourly_rate`, `status`) VALUES
(1, 'Ralph Harvey Oliva', 1, 60.00, 'active'),
(2, 'Paul Tenorio', 2, 50.00, 'active'),
(3, 'Ethan Pogi Masarap Silva', 3, 50.00, 'active');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password_hash` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL,
  `employee_id` int(11) DEFAULT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password_hash`, `role`, `employee_id`, `is_active`, `created_at`) VALUES
(1, 'admin', '$2y$10$n0T6v8xaQEoenRDD9941uOgDvjcyIvQn1sGPyXyw3pIV3DNJv1LpO', 'admin', NULL, 1, '2026-10-02 03:11:19');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `audit_logs`
--
ALTER TABLE `audit_logs`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `deductions`
--
ALTER TABLE `deductions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`),
  ADD KEY `applied_by` (`applied_by`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `fingerprint_id` (`fingerprint_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD KEY `employee_id` (`employee_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `audit_logs`
--
ALTER TABLE `audit_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `deductions`
--
ALTER TABLE `deductions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance_logs`
--
ALTER TABLE `attendance_logs`
  ADD CONSTRAINT `attendance_logs_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);

--
-- Constraints for table `deductions`
--
ALTER TABLE `deductions`
  ADD CONSTRAINT `deductions_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`),
  ADD CONSTRAINT `deductions_ibfk_2` FOREIGN KEY (`applied_by`) REFERENCES `users` (`id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`id`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
