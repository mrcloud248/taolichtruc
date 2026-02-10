-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Feb 10, 2026 at 12:43 PM
-- Server version: 11.4.7-MariaDB-cll-lve-log
-- PHP Version: 8.2.21

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `hhgclkaohosting_taolich`
--

-- --------------------------------------------------------

--
-- Table structure for table `rules`
--

CREATE TABLE `rules` (
  `id` int(11) NOT NULL,
  `rule_name` varchar(100) NOT NULL,
  `rule_type` varchar(50) NOT NULL,
  `rule_value` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `description` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `rules`
--

INSERT INTO `rules` (`id`, `rule_name`, `rule_type`, `rule_value`, `is_active`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Số ca liên tiếp tối đa', 'max_consecutive_shifts', '2', 1, 'Không được trực quá 2 ca liên tiếp', '2026-02-10 08:14:31', '2026-02-10 08:14:31'),
(2, 'Thời gian nghỉ tối thiểu', 'min_rest_hours', '24', 1, 'Nghỉ ít nhất 24 giờ giữa các ca', '2026-02-10 08:14:31', '2026-02-10 08:14:31'),
(3, 'Phân bổ đều theo tháng', 'fair_distribution', '1', 1, 'Đảm bảo số ca gần bằng nhau cho mỗi nhân viên', '2026-02-10 08:14:31', '2026-02-10 08:14:31'),
(4, 'Tránh ca cuối tuần liên tiếp', 'avoid_consecutive_weekends', '1', 1, 'Không trực cuối tuần 2 tuần liên tiếp', '2026-02-10 08:14:31', '2026-02-10 08:14:31');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `schedule_type` enum('daily','weekly') NOT NULL,
  `month` int(11) NOT NULL,
  `year` int(11) NOT NULL,
  `status` enum('draft','published','archived') DEFAULT 'draft',
  `generated_at` datetime DEFAULT current_timestamp(),
  `generated_by` varchar(100) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `schedule_type`, `month`, `year`, `status`, `generated_at`, `generated_by`, `notes`, `created_at`) VALUES
(12, 'daily', 2, 2026, 'draft', '2026-02-10 16:22:56', 'Admin', NULL, '2026-02-10 16:22:56'),
(13, 'weekly', 2, 2026, 'draft', '2026-02-10 16:24:17', 'Admin', 'Lịch tuần cho 6 nhân viên, 6 tuần', '2026-02-10 16:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_history`
--

CREATE TABLE `schedule_history` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `changed_by` varchar(100) DEFAULT NULL,
  `change_details` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule_history`
--

INSERT INTO `schedule_history` (`id`, `schedule_id`, `action`, `changed_by`, `change_details`, `created_at`) VALUES
(3, 12, 'created', 'Admin', 'Tạo lịch trực ngày tự động', '2026-02-10 16:22:56'),
(4, 13, 'created', 'Admin', 'Tạo lịch trực tuần tự động', '2026-02-10 16:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `schedule_shifts`
--

CREATE TABLE `schedule_shifts` (
  `id` int(11) NOT NULL,
  `schedule_id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `shift_date` date NOT NULL,
  `shift_type` enum('WEEKDAY_EVENING','SUNDAY_MORNING','SUNDAY_EVENING','SATURDAY_MORNING') NOT NULL,
  `start_time` time NOT NULL,
  `end_time` time NOT NULL,
  `is_manual_override` tinyint(1) DEFAULT 0,
  `notes` text DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `schedule_shifts`
--

INSERT INTO `schedule_shifts` (`id`, `schedule_id`, `staff_id`, `shift_date`, `shift_type`, `start_time`, `end_time`, `is_manual_override`, `notes`, `created_at`) VALUES
(224, 12, 5, '2026-02-01', 'SUNDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:22:56'),
(225, 12, 8, '2026-02-01', 'SUNDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(226, 12, 6, '2026-02-02', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(227, 12, 1, '2026-02-03', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(228, 12, 6, '2026-02-04', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(229, 12, 4, '2026-02-05', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(230, 12, 6, '2026-02-06', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(231, 12, 1, '2026-02-07', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(232, 12, 3, '2026-02-08', 'SUNDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:22:56'),
(233, 12, 8, '2026-02-08', 'SUNDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(234, 12, 5, '2026-02-09', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(235, 12, 3, '2026-02-10', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(236, 12, 4, '2026-02-11', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(237, 12, 8, '2026-02-12', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(238, 12, 4, '2026-02-13', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(239, 12, 1, '2026-02-14', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(240, 12, 6, '2026-02-15', 'SUNDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:22:56'),
(241, 12, 4, '2026-02-15', 'SUNDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(242, 12, 8, '2026-02-16', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(243, 12, 1, '2026-02-17', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(244, 12, 3, '2026-02-18', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(245, 12, 5, '2026-02-19', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(246, 12, 1, '2026-02-20', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(247, 12, 5, '2026-02-21', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(248, 12, 6, '2026-02-22', 'SUNDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:22:56'),
(249, 12, 6, '2026-02-22', 'SUNDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(250, 12, 3, '2026-02-23', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(251, 12, 4, '2026-02-24', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(252, 12, 1, '2026-02-25', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(253, 12, 5, '2026-02-26', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(254, 12, 4, '2026-02-27', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(255, 12, 3, '2026-02-28', 'WEEKDAY_EVENING', '17:00:00', '23:30:00', 0, NULL, '2026-02-10 16:22:56'),
(256, 13, 8, '2026-02-14', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17'),
(257, 13, 1, '2026-02-21', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17'),
(258, 13, 6, '2026-02-28', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17'),
(259, 13, 5, '2026-03-07', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17'),
(260, 13, 4, '2026-03-14', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17'),
(261, 13, 3, '2026-03-21', 'SATURDAY_MORNING', '08:00:00', '17:00:00', 0, NULL, '2026-02-10 16:24:17');

-- --------------------------------------------------------

--
-- Table structure for table `staff`
--

CREATE TABLE `staff` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `is_active` tinyint(1) DEFAULT 1,
  `max_shifts_per_week` int(11) DEFAULT 5,
  `max_shifts_per_month` int(11) DEFAULT 20,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `staff`
--

INSERT INTO `staff` (`id`, `name`, `is_active`, `max_shifts_per_week`, `max_shifts_per_month`, `created_at`, `updated_at`) VALUES
(1, 'Minhnv', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:32'),
(3, 'Linhnb', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:19'),
(4, 'Ngapt', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:40'),
(5, 'c Hà', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:12'),
(6, 'a Pắc', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:06'),
(8, 'Vietpd', 1, 5, 20, '2026-02-10 08:14:31', '2026-02-10 16:00:26'),
(11, 'Hòabd', 1, 5, 20, '2026-02-10 16:28:52', '2026-02-10 16:28:52');

-- --------------------------------------------------------

--
-- Table structure for table `staff_constraints`
--

CREATE TABLE `staff_constraints` (
  `id` int(11) NOT NULL,
  `staff_id` int(11) NOT NULL,
  `constraint_type` enum('day_off','avoid_shift','prefer_shift') NOT NULL,
  `constraint_value` varchar(50) NOT NULL,
  `start_date` date DEFAULT NULL,
  `end_date` date DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) NOT NULL,
  `role` enum('admin','user') DEFAULT 'user',
  `is_active` tinyint(1) DEFAULT 1,
  `last_login` datetime DEFAULT NULL,
  `created_at` datetime DEFAULT current_timestamp(),
  `updated_at` datetime DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `full_name`, `role`, `is_active`, `last_login`, `created_at`, `updated_at`) VALUES
(1, 'admin', '$2y$10$yV4Ci3QUHDN6JjpeYWhngOuPInepmabkSCIWwG2cdyKsO3rxS2E86', 'Administrator', 'admin', 1, '2026-02-10 16:49:35', '2026-02-10 16:44:23', '2026-02-10 16:49:35');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `rules`
--
ALTER TABLE `rules`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_schedule_date` (`year`,`month`,`schedule_type`);

--
-- Indexes for table `schedule_history`
--
ALTER TABLE `schedule_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`);

--
-- Indexes for table `schedule_shifts`
--
ALTER TABLE `schedule_shifts`
  ADD PRIMARY KEY (`id`),
  ADD KEY `schedule_id` (`schedule_id`),
  ADD KEY `staff_id` (`staff_id`),
  ADD KEY `idx_shift_date` (`shift_date`);

--
-- Indexes for table `staff`
--
ALTER TABLE `staff`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `staff_constraints`
--
ALTER TABLE `staff_constraints`
  ADD PRIMARY KEY (`id`),
  ADD KEY `staff_id` (`staff_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `rules`
--
ALTER TABLE `rules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `schedule_history`
--
ALTER TABLE `schedule_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `schedule_shifts`
--
ALTER TABLE `schedule_shifts`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=262;

--
-- AUTO_INCREMENT for table `staff`
--
ALTER TABLE `staff`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;

--
-- AUTO_INCREMENT for table `staff_constraints`
--
ALTER TABLE `staff_constraints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `schedule_history`
--
ALTER TABLE `schedule_history`
  ADD CONSTRAINT `fk_history_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `schedule_shifts`
--
ALTER TABLE `schedule_shifts`
  ADD CONSTRAINT `fk_shifts_schedule` FOREIGN KEY (`schedule_id`) REFERENCES `schedules` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_shifts_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `staff_constraints`
--
ALTER TABLE `staff_constraints`
  ADD CONSTRAINT `fk_constraints_staff` FOREIGN KEY (`staff_id`) REFERENCES `staff` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
