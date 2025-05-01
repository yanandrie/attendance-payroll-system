-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 27, 2025 at 07:48 AM
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
-- Database: `payroll_system`
--

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `time_in` datetime DEFAULT NULL,
  `time_out` datetime DEFAULT NULL,
  `work_hours` decimal(5,2) DEFAULT 0.00,
  `date` date NOT NULL,
  `status` enum('present','late','absent','on leave') NOT NULL DEFAULT 'absent',
  `overtime_hours` decimal(5,2) DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `emp_id`, `time_in`, `time_out`, `work_hours`, `date`, `status`, `overtime_hours`) VALUES
(6, 4, '2025-03-18 10:01:45', '2025-03-18 14:54:09', 4.87, '2025-03-18', 'late', 0.00),
(7, 5, '2025-03-18 15:03:33', '2025-03-18 15:31:57', 0.47, '2025-03-18', 'late', 0.00),
(8, 6, '2025-03-18 15:09:00', '2025-03-18 18:38:20', 3.49, '2025-03-18', 'late', 1.64),
(9, 5, '2025-03-19 07:09:09', '2025-03-19 18:06:07', 10.95, '2025-03-19', 'present', 0.00),
(11, 6, '2025-03-19 07:16:51', '2025-03-19 18:08:55', 10.87, '2025-03-19', 'present', 0.00),
(12, 4, '2025-03-19 08:16:01', '2025-03-19 18:09:36', 9.89, '2025-03-19', 'late', 0.00),
(14, 9, '2025-03-19 15:38:34', NULL, 0.00, '2025-03-19', 'late', 0.00),
(15, 9, '2025-03-20 15:59:54', '2025-03-20 15:59:59', 0.00, '2025-03-20', 'late', 0.00),
(16, 5, '2025-03-20 16:01:03', NULL, 0.00, '2025-03-20', 'late', 0.00),
(17, 4, '2025-03-20 16:54:16', NULL, 0.00, '2025-03-20', 'late', 0.00),
(18, 6, '2025-03-20 16:55:23', NULL, 0.00, '2025-03-20', 'late', 0.00),
(21, 11, '2025-03-21 06:51:24', NULL, 0.00, '2025-03-21', 'present', 0.00),
(22, 4, '2025-03-21 07:09:52', NULL, 0.00, '2025-03-21', 'present', 0.00),
(23, 5, '2025-03-21 07:22:17', NULL, 0.00, '2025-03-21', 'present', 0.00),
(24, 12, '2025-03-21 15:24:32', NULL, 0.00, '2025-03-21', 'late', 0.00),
(25, 5, '2025-03-26 14:39:43', NULL, 0.00, '2025-03-26', 'late', 0.00),
(26, 13, '2025-03-26 14:42:19', NULL, 0.00, '2025-03-26', 'late', 0.00),
(27, 14, '2025-03-26 14:45:52', NULL, 0.00, '2025-03-26', 'late', 0.00),
(28, 15, '2025-03-26 14:51:57', NULL, 0.00, '2025-03-26', 'late', 0.00),
(29, 16, '2025-03-26 15:06:19', NULL, 0.00, '2025-03-26', 'late', 0.00),
(30, 5, '2025-03-28 09:04:20', NULL, 0.00, '2025-03-28', 'present', 0.00),
(31, 17, '2025-04-20 21:21:05', '2025-04-20 21:21:17', 0.00, '2025-04-20', 'late', 0.00),
(32, 5, '2025-04-20 21:23:10', NULL, 0.00, '2025-04-20', 'late', 0.00),
(33, 5, '2025-04-21 07:39:22', '2025-04-21 07:45:00', 0.00, '2025-04-21', 'present', 0.00);

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `emp_id` int(11) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `phone` varchar(15) NOT NULL,
  `address` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `position` varchar(50) NOT NULL,
  `salary` decimal(10,2) NOT NULL DEFAULT 0.00
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`emp_id`, `full_name`, `phone`, `address`, `created_at`, `position`, `salary`) VALUES
(1, 'Administrator', '09123456789', 'Company HQ', '2025-03-16 08:02:41', 'Administrator', 60000.00),
(4, 'Test User 2', '09273516755', 'example', '2025-03-16 14:44:10', 'IT Specialist', 42000.00),
(5, 'Geo Marcito', '09364718923', 'Barangay Binubuhan', '2025-03-18 07:02:59', 'Civil Engineer', 45000.00),
(6, 'Mike Anderzon', '09737892911', 'Barangay Unknown', '2025-03-18 07:08:43', 'Firefighter', 28000.00),
(9, 'april joy flores', '09887654677', 'Barangay Gomez Pontevedra', '2025-03-19 07:31:01', 'Police Officer', 30000.00),
(10, 'Johny Martinez', '09875738744', 'Barangay Dulao Bago City', '2025-03-19 07:41:27', 'IT Specialist', 42000.00),
(11, 'Test 3', '09773652877', 'Barangay Unknown', '2025-03-20 22:42:56', 'Social Worker', 29000.00),
(12, 'Reinster May Ochida', '09756765123', 'bacolod', '2025-03-21 07:23:53', 'Social Worker', 50000.00),
(13, 'Roselle Martizano', '09647382799', 'Barangay Dulao Bago City Negros Occidental', '2025-03-26 06:41:41', 'Civil Engineer', 100000.00),
(14, 'Keith', '09661963314', 'Dimasalang rd Taangub', '2025-03-26 06:45:34', 'IT Specialist', 25000.00),
(15, 'raj', '09060619729', 'bago city', '2025-03-26 06:51:30', 'IT Specialist', 120000.00),
(16, 'millan keith', '09060619729', 'bago', '2025-03-26 07:06:07', 'Government Accountant', 500000.00),
(17, 'Testing Only', '+639776018922', 'Binubuhan, Bago City, Negros Occidental, Philippines', '2025-04-20 13:20:35', 'Public School Teacher', 35000.00);

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `leave_type` varchar(50) DEFAULT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reason` text DEFAULT NULL,
  `requested_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `overtime_requests`
--

CREATE TABLE `overtime_requests` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `date` date DEFAULT NULL,
  `requested_hours` decimal(5,2) DEFAULT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `payroll`
--

CREATE TABLE `payroll` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `cutoff_date` date NOT NULL,
  `days_worked` int(11) DEFAULT 0,
  `work_hours` decimal(5,2) DEFAULT 0.00,
  `overtime_hours` decimal(5,2) DEFAULT 0.00,
  `gross_salary` decimal(10,2) NOT NULL,
  `deductions` decimal(10,2) NOT NULL,
  `net_salary` decimal(10,2) NOT NULL,
  `generated_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `positions`
--

CREATE TABLE `positions` (
  `id` int(11) NOT NULL,
  `position_name` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `salary_rates`
--

CREATE TABLE `salary_rates` (
  `id` int(11) NOT NULL,
  `position` varchar(255) NOT NULL,
  `salary` decimal(10,2) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `shifts`
--

CREATE TABLE `shifts` (
  `id` int(11) NOT NULL,
  `emp_id` int(11) DEFAULT NULL,
  `shift_date` date DEFAULT NULL,
  `start_time` time DEFAULT NULL,
  `end_time` time DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `user_id` int(11) NOT NULL,
  `emp_id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('admin','employee') NOT NULL DEFAULT 'employee',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`user_id`, `emp_id`, `username`, `password`, `role`, `created_at`) VALUES
(2, 1, 'admin', '$2y$10$7iKfVTt7w.Eghr4J0Ya3J.JNe.8Kkaj7KD/cb8rz0QI6fwrCbn.Gi', 'admin', '2025-03-16 08:02:41'),
(5, 4, 'test2', '$2y$10$HeemOoJNvkoxaysE98SqQ.5tZ8shDw4fkANf81OlHxrYC6wzW0DhG', 'employee', '2025-03-16 14:44:10'),
(6, 5, 'geo', '$2y$10$nRhinqYKdm7CkCA/uMfEt.Th5.JkXWkEEWXqOPpP92.qScK/.Nl5W', 'employee', '2025-03-18 07:02:59'),
(7, 6, 'mike', '$2y$10$XjpzTRw8WEukImhOZ22RTuj6uDvSR5YXAH8FITFrIKFojiJpZ/t8a', 'employee', '2025-03-18 07:08:43'),
(10, 9, 'joya', '$2y$10$jgPX6EUcs0tWxQwVDIxKsuTN2qKsYRCzPShFj24coUYDfAZ9rBydC', 'employee', '2025-03-19 07:31:01'),
(11, 10, 'johny', '$2y$10$Wx/u8s97qdUtJdjzlHxVX.e1gf2FtNtUN7o/5ngEhnKyL0IiXuw6u', 'employee', '2025-03-19 07:41:27'),
(12, 11, 'test3', '$2y$10$HhMb25F6EPIYVcSTrnTnauasRpNTZQ9albx3iV0aGZRvk4JxAvxfe', 'employee', '2025-03-20 22:42:56'),
(13, 12, 'rein', '$2y$10$tWY2V2DkPY3VkAyxMYs8LeIg/8XicRdzNwn6mp.4F.mEg23GFsbVy', 'employee', '2025-03-21 07:23:53'),
(14, 13, 'roselle', '$2y$10$iRou1lptWLC3SboB7kiqVOKr.1LW7GJRYWD2ZhRt3RHSZjTn9Veii', 'employee', '2025-03-26 06:41:41'),
(15, 14, 'KIT', '$2y$10$tmBaG06XYppuOR/tV4ylQ.FB2cuPA7EdW5ADPXBqjkYEN0YubYQ9e', 'employee', '2025-03-26 06:45:34'),
(16, 15, '1234gwapa', '$2y$10$ItVMW8Bzp150cLhfEx9xbeioz7u0.cGelQQY6bQ6b3OwULsHNqSiu', 'employee', '2025-03-26 06:51:30'),
(17, 16, 'millan', '$2y$10$5GKUMJ0RS9cYM/TjOuE5PO4.rvjnnNdHN8as.JCJbz.UQ7gu51UIW', 'employee', '2025-03-26 07:06:07'),
(18, 17, 'testing', '$2y$10$zBOxsbL8Hw5n717XZLwQGeoAYpXgM2lR1ZJD4fDfbpAr6uDrDmTYy', 'employee', '2025-04-20 13:20:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`emp_id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `payroll`
--
ALTER TABLE `payroll`
  ADD PRIMARY KEY (`id`),
  ADD KEY `emp_id` (`emp_id`);

--
-- Indexes for table `positions`
--
ALTER TABLE `positions`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position_name` (`position_name`);

--
-- Indexes for table `salary_rates`
--
ALTER TABLE `salary_rates`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `position` (`position`);

--
-- Indexes for table `shifts`
--
ALTER TABLE `shifts`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`user_id`),
  ADD UNIQUE KEY `emp_id` (`emp_id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `emp_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `overtime_requests`
--
ALTER TABLE `overtime_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `payroll`
--
ALTER TABLE `payroll`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `positions`
--
ALTER TABLE `positions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `salary_rates`
--
ALTER TABLE `salary_rates`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `shifts`
--
ALTER TABLE `shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `user_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attendance`
--
ALTER TABLE `attendance`
  ADD CONSTRAINT `attendance_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;

--
-- Constraints for table `payroll`
--
ALTER TABLE `payroll`
  ADD CONSTRAINT `payroll_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`);

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `users_ibfk_1` FOREIGN KEY (`emp_id`) REFERENCES `employees` (`emp_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
