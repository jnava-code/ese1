-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Mar 05, 2025 at 03:20 PM
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
-- Database: `esetech`
--

-- --------------------------------------------------------

--
-- Table structure for table `admin`
--

CREATE TABLE `admin` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `user_type` int(11) NOT NULL DEFAULT 1 COMMENT '1=admin, 2=staff, 3=super admin',
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '1=active, 2=inactive',
  `first_name` varchar(255) DEFAULT NULL,
  `middle_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(255) DEFAULT NULL,
  `suffix` varchar(32) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `birthday` varchar(50) DEFAULT NULL,
  `position` varchar(50) DEFAULT NULL,
  `is_archived` tinyint(1) NOT NULL DEFAULT 0 COMMENT '0=not archived, 1=archived'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `user_type`, `status`, `first_name`, `middle_name`, `last_name`, `suffix`, `email`, `contact_number`, `birthday`, `position`, `is_archived`) VALUES
(4, 'admin', '$2y$10$30kJpHW0K6n2GqVM7YMBwuyjtIvCWThJmCGhyObUOpEtR3VPhr.GC', 1, 1, 'John', NULL, 'mark', NULL, 'dccsamaniego@bpsu.edu.ph', '909233422131', NULL, NULL, 0),
(5, 'superadmin', '$2y$10$30kJpHW0K6n2GqVM7YMBwuyjtIvCWThJmCGhyObUOpEtR3VPhr.GC', 3, 1, 'Super', NULL, 'Admin', NULL, NULL, NULL, NULL, NULL, 0),
(8, 'Akali132', '$2y$10$BhJAcTbFz60cDbIPImFhpe/VI4N8KzBp/EKlEC6I5creuG66SzV5.', 1, 1, 'Marrk', NULL, 'Caguiea1', NULL, 'markcaguia@gmail.com', '09093556323', NULL, NULL, 0),
(9, 'aya13', '$2y$10$WTUlvSq.Gi5kx/z0ZhCk6e5dlp.43NWd2/.753TiDfDoSjlqh2DaK', 1, 1, 'Jeremiah', NULL, 'Nava', NULL, 'aiahnava5@gmail.com', '09155434721', NULL, NULL, 0),
(13, 'aya', '$2y$10$kwLh0OacIB9bzg1CgsJkr./CY/o0NhvcwhM36yYa85pYWYkxuxU42', 1, 1, 'Jeremiah', 'Garay', 'Nava', '', 'aiahnava5@gmail.com', '09155434721', '2025-02-28', 'Computer Programmer I', 0),
(16, 'Maricris', '$2y$10$Yi.dvf4b/rls7tN8qOgtGukw1vF0Ctu8cu.UoQCYSISi5m9K2keZu', 1, 1, 'Maricris', 'Aucena', 'Mallari', '', 'mallarimaricris121902@gmail.com', '09455878782', '2000-12-19', 'Admin', 0),
(17, 'Rachelle', '$2y$10$ozYciTS25ONJJTw7YSG6LeSA.jALSnM90btHNnGSiDc8k8h6plkjK', 1, 1, 'Rachelle Anne', 'Gabieta', 'Roquero', '', 'mallarimaricris121902@gmail.com', '09455878781', '2000-01-01', 'Admin', 0),
(18, 'Daenuelle', '$2y$10$f8VYYWXYjR1ZseSGu2yx3OSCbPHlKOAXSAX2OZHcBKQG0QDg3uVlu', 1, 1, 'Daenuelle Christian', 'Capulong', 'Samaniego', '', 'mallarimaricris121902@gmail.com', '09455878783', '2000-02-02', 'Admin', 0),
(19, 'Keano', '$2y$10$8M/77W9CeafFJJyjcPvYb.D9WzM98t.I6xkYOfER7IePWQFstsd9u', 1, 1, 'Keano', 'Cruz', 'Penaloza', '', 'mallarimaricris121902@gmail.com', '09455878784', '1990-02-02', 'Admin', 0);

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `attendance_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `date` date NOT NULL,
  `clock_in_time` time DEFAULT NULL,
  `clock_out_time` time DEFAULT NULL,
  `total_hours` text DEFAULT NULL,
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `date`, `clock_in_time`, `clock_out_time`, `total_hours`, `status`) VALUES
(1, 24001, '2024-08-07', '07:53:02', '05:31:28', '8.02', 'Present'),
(2, 24001, '2024-08-08', '07:35:09', '05:35:20', '8.09', 'Present'),
(3, 24001, '2024-08-09', '07:45:12', '05:45:32', '8.26', 'Present'),
(4, 24001, '2024-08-12', '07:47:06', '06:47:27', '9.29', 'Over Time'),
(5, 24001, '2024-08-13', '07:48:52', '08:49:00', '11.32', 'Over Time'),
(6, 24001, '2024-08-14', '07:49:41', '10:50:12', '13.34', 'Over Time'),
(7, 24001, '2024-08-15', '07:50:30', '07:50:43', '10.35', 'Over Time'),
(8, 24001, '2024-08-16', '07:51:06', '07:01:53', '9.53', 'Over Time'),
(9, 24001, '2024-08-19', '07:45:59', '05:46:52', '8.28', 'Present'),
(10, 24001, '2025-03-05', '07:54:12', '05:50:44', '8.35', 'Overtime');

-- --------------------------------------------------------

--
-- Table structure for table `attrition_forecasting`
--

CREATE TABLE `attrition_forecasting` (
  `forecast_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `prediction_date` date NOT NULL,
  `attrition_probability` decimal(5,2) NOT NULL,
  `factors` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`factors`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attrition_forecasting`
--

INSERT INTO `attrition_forecasting` (`forecast_id`, `employee_id`, `prediction_date`, `attrition_probability`, `factors`) VALUES
(7, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(8, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(9, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(10, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(11, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(12, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(13, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(14, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(15, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0.5,\"performance_score\":0,\"years_of_service\":0.6603}'),
(16, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(17, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(18, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(19, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(20, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(21, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(22, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(23, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(24, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}'),
(25, 24001, '2025-03-05', 0.99, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.6603}'),
(26, 25002, '2025-03-05', 1.00, '{\"attendance_score\":0,\"satisfaction_score\":0,\"performance_score\":0,\"years_of_service\":0.0822}');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `dept_name` varchar(50) NOT NULL,
  `colors` varchar(32) NOT NULL,
  `is_archived` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `dept_name`, `colors`, `is_archived`) VALUES
(2, 'Chemical', '#ff8787', 0),
(3, 'Procurement', '#f783ac', 0),
(4, 'Sales', '#da77f2', 0),
(5, 'Sales & Marketing', '#9775fa', 0),
(6, 'Technical', '#748ffc', 0),
(7, 'Technical Sales', '#4dabf7', 0),
(14, 'Admin', '#3bc9db', 0);

-- --------------------------------------------------------

--
-- Table structure for table `educational_backgrounds`
--

CREATE TABLE `educational_backgrounds` (
  `id` int(11) NOT NULL,
  `description` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `employees`
--

CREATE TABLE `employees` (
  `id` int(11) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `first_name` varchar(255) NOT NULL,
  `middle_name` varchar(255) NOT NULL,
  `suffix` varchar(255) NOT NULL,
  `gender` varchar(32) NOT NULL,
  `email` varchar(100) NOT NULL,
  `position` varchar(255) NOT NULL,
  `hire_date` date NOT NULL,
  `department` varchar(255) NOT NULL,
  `employment_status` varchar(255) NOT NULL,
  `employee_id` int(20) NOT NULL,
  `user_type` int(11) NOT NULL DEFAULT 2 COMMENT '1=admin, 2=staff, 3=super admin',
  `password` varchar(255) NOT NULL,
  `e_status` int(11) NOT NULL DEFAULT 1,
  `date_of_birth` date DEFAULT NULL,
  `age` varchar(32) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL,
  `perma_address` text DEFAULT NULL,
  `civil_status` varchar(10) DEFAULT NULL,
  `sss_number` varchar(15) DEFAULT NULL,
  `philhealth_number` varchar(15) DEFAULT NULL,
  `pagibig_number` varchar(15) DEFAULT NULL,
  `tin_number` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(50) DEFAULT NULL,
  `emergency_contact_number` varchar(15) DEFAULT NULL,
  `educational_background` enum('Technical-Vocational Program graduate','College graduate','Master''s degree graduate','Doctorate degree graduate') DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `sick_leave` int(11) NOT NULL DEFAULT 0,
  `vacation_leave` int(11) NOT NULL DEFAULT 0,
  `maternity_leave` int(11) NOT NULL DEFAULT 0,
  `paternity_leave` int(11) NOT NULL DEFAULT 0,
  `sick_availed` int(11) NOT NULL,
  `vacation_availed` int(11) NOT NULL,
  `maternity_availed` int(11) NOT NULL,
  `paternity_availed` int(11) NOT NULL,
  `medical` varchar(32) DEFAULT NULL,
  `tor` varchar(32) DEFAULT NULL,
  `nbi_clearance` varchar(32) DEFAULT NULL,
  `resume` varchar(32) DEFAULT NULL,
  `prc` varchar(32) DEFAULT NULL,
  `others` varchar(32) DEFAULT NULL,
  `medical_type` varchar(32) DEFAULT NULL,
  `tor_type` varchar(32) DEFAULT NULL,
  `police_type` varchar(32) DEFAULT NULL,
  `resume_type` varchar(32) DEFAULT NULL,
  `prc_type` varchar(32) DEFAULT NULL,
  `others_type` varchar(32) DEFAULT NULL,
  `is_archived` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `last_name`, `first_name`, `middle_name`, `suffix`, `gender`, `email`, `position`, `hire_date`, `department`, `employment_status`, `employee_id`, `user_type`, `password`, `e_status`, `date_of_birth`, `age`, `contact_number`, `perma_address`, `civil_status`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `emergency_contact_name`, `emergency_contact_number`, `educational_background`, `skills`, `username`, `sick_leave`, `vacation_leave`, `maternity_leave`, `paternity_leave`, `sick_availed`, `vacation_availed`, `maternity_availed`, `paternity_availed`, `medical`, `tor`, `nbi_clearance`, `resume`, `prc`, `others`, `medical_type`, `tor_type`, `police_type`, `resume_type`, `prc_type`, `others_type`, `is_archived`) VALUES
(3, 'Sanggalang', 'Jeremy', 'Zulueta', '', 'Male', 'mallarimaricris121902@gmail.com', 'Programmer', '2024-07-07', 'Technical', 'Regular', 24001, 2, '$2y$10$zFdibl3zYs8tO7bxLjZkaO/46QuGj2S9bHGrSLdWHKTxH4bT3x48q', 1, '1999-06-06', '25', '09093476284', '0006 Santa Rosa, Pilar, Bataan', 'Married', '00-0000000-8', '00-000000000-8', '0000-0000-0008', '000-000-000-008', 'Regine Velasquez', '09595623785', 'College graduate', 'Coder', 'Jeremy', 12, 12, 0, 7, 5, 0, 0, 0, 'Jeremy_Sanggalang_67c74f61bf4f5.', 'Jeremy_Sanggalang_67c74f61bf79c.', 'Jeremy_Sanggalang_67c74f61bf96c.', 'Jeremy_Sanggalang_67c74f61c0de7.', 'Jeremy_Sanggalang_67c74f61c0f48.', 'Jeremy_Sanggalang_67c74f61c1098.', 'application/pdf', 'image/png', 'image/jpeg', 'image/png', 'image/png', 'image/jpeg', 0),
(4, 'Margallo', 'Justine', 'Gabi', '', 'Male', 'mallarimaricris121902@gmail.com', 'Photographer', '2025-02-03', 'Sales & Marketing', 'Probationary', 25002, 2, '$2y$10$DoiJodwzjmQbKmgqDTnQOefbzYoi8yOg3Rw2AsaAv4KHOoi57UQVu', 1, '1998-07-07', '26', '09993344667', '0007 Orion, Bataan', 'Single', '00-0000000-9', '00-000000000-9', '0000-0000-0009', '000-000-000-009', 'Ogie Alcasid', '09621853430', 'Master\'s degree graduate', 'Photogenic', 'Justine', 12, 12, 0, 7, 0, 0, 0, 0, 'Justine_Margallo_67c7503231f39.p', 'Justine_Margallo_67c75032321bb.p', 'Justine_Margallo_67c7503232350.j', 'Justine_Margallo_67c75032324dc.p', 'Justine_Margallo_67c7503232651.j', 'Justine_Margallo_67c75032327bd.j', 'application/pdf', 'image/png', 'image/jpeg', 'image/png', 'image/jpeg', 'image/jpeg', 0);

-- --------------------------------------------------------

--
-- Table structure for table `e_recommendations`
--

CREATE TABLE `e_recommendations` (
  `recommendation_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `recommendation_type` enum('Promotion','Demotion','Retrenchment') NOT NULL,
  `reason` text DEFAULT NULL,
  `effective_date` date DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `job_satisfaction_form_status`
--

CREATE TABLE `job_satisfaction_form_status` (
  `status_id` int(11) NOT NULL,
  `status` enum('Open','Closed') NOT NULL DEFAULT 'Closed'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_satisfaction_form_status`
--

INSERT INTO `job_satisfaction_form_status` (`status_id`, `status`) VALUES
(1, 'Open');

-- --------------------------------------------------------

--
-- Table structure for table `job_satisfaction_surveys`
--

CREATE TABLE `job_satisfaction_surveys` (
  `survey_id` int(11) NOT NULL,
  `employee_id` int(20) NOT NULL,
  `survey_date` date NOT NULL,
  `questions` longtext NOT NULL CHECK (json_valid(`questions`)),
  `overall_rating` decimal(5,2) NOT NULL,
  `rating_description` varchar(50) GENERATED ALWAYS AS (case when `overall_rating` between 4.00 and 5.00 then 'Very Satisfied' when `overall_rating` between 3.00 and 3.99 then 'Neutral' when `overall_rating` between 1.00 and 2.99 then 'Very Dissatisfied' else 'Undefined' end) VIRTUAL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `job_satisfaction_surveys`
--

INSERT INTO `job_satisfaction_surveys` (`survey_id`, `employee_id`, `survey_date`, `questions`, `overall_rating`) VALUES
(6, 25001, '2025-02-22', '{\"clarity_of_responsibilities\":\"5\",\"work_environment\":\"5\",\"work_life_balance\":\"5\",\"manager_support\":\"5\",\"team_collaboration\":\"5\",\"compensation\":\"5\",\"career_growth\":\"5\"}', 5.00);

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Sick','Vacation','Paternity','Maternity') NOT NULL,
  `file_date` varchar(32) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `number_of_days` varchar(32) NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `monthly_attendance`
--

CREATE TABLE `monthly_attendance` (
  `monthly_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `month_year` date NOT NULL,
  `days_present` int(11) NOT NULL DEFAULT 0,
  `days_absent` int(11) NOT NULL DEFAULT 0,
  `days_late` int(11) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `performance_criteria`
--

CREATE TABLE `performance_criteria` (
  `id` int(11) NOT NULL,
  `description` varchar(150) NOT NULL,
  `is_archived` tinyint(1) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_criteria`
--

INSERT INTO `performance_criteria` (`id`, `description`, `is_archived`) VALUES
(1, 'Attitude', 0),
(2, 'Performance', 0),
(3, 'try', 0);

-- --------------------------------------------------------

--
-- Table structure for table `performance_evaluations`
--

CREATE TABLE `performance_evaluations` (
  `id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `evaluation_date` date NOT NULL,
  `criteria` text NOT NULL,
  `comments` text DEFAULT NULL,
  `remarks` text DEFAULT NULL,
  `overall_score` decimal(5,2) NOT NULL,
  `status` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `performance_evaluations`
--

INSERT INTO `performance_evaluations` (`id`, `employee_id`, `admin_id`, `evaluation_date`, `criteria`, `comments`, `remarks`, `overall_score`, `status`) VALUES
(1, 25001, 1, '2025-02-27', '{\"Attitude\":5,\"Performance\":5,\"try\":5}', 'Good job', 'Very Effective', 5.00, 'Completed');

-- --------------------------------------------------------

--
-- Table structure for table `reports`
--

CREATE TABLE `reports` (
  `report_id` int(11) NOT NULL,
  `report_type` varchar(50) NOT NULL,
  `generated_date` date NOT NULL,
  `data` longtext CHARACTER SET utf8mb4 COLLATE utf8mb4_bin NOT NULL CHECK (json_valid(`data`))
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admin`
--
ALTER TABLE `admin`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`attendance_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `attrition_forecasting`
--
ALTER TABLE `attrition_forecasting`
  ADD PRIMARY KEY (`forecast_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `employees`
--
ALTER TABLE `employees`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `employee_id_unique` (`employee_id`),
  ADD UNIQUE KEY `e_username` (`username`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `e_recommendations`
--
ALTER TABLE `e_recommendations`
  ADD PRIMARY KEY (`recommendation_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `job_satisfaction_form_status`
--
ALTER TABLE `job_satisfaction_form_status`
  ADD PRIMARY KEY (`status_id`);

--
-- Indexes for table `job_satisfaction_surveys`
--
ALTER TABLE `job_satisfaction_surveys`
  ADD PRIMARY KEY (`survey_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `leave_applications`
--
ALTER TABLE `leave_applications`
  ADD PRIMARY KEY (`leave_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `monthly_attendance`
--
ALTER TABLE `monthly_attendance`
  ADD PRIMARY KEY (`monthly_id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `performance_criteria`
--
ALTER TABLE `performance_criteria`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `performance_evaluations`
--
ALTER TABLE `performance_evaluations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `employee_id` (`employee_id`);

--
-- Indexes for table `reports`
--
ALTER TABLE `reports`
  ADD PRIMARY KEY (`report_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `attrition_forecasting`
--
ALTER TABLE `attrition_forecasting`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `e_recommendations`
--
ALTER TABLE `e_recommendations`
  MODIFY `recommendation_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `job_satisfaction_form_status`
--
ALTER TABLE `job_satisfaction_form_status`
  MODIFY `status_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `job_satisfaction_surveys`
--
ALTER TABLE `job_satisfaction_surveys`
  MODIFY `survey_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `monthly_attendance`
--
ALTER TABLE `monthly_attendance`
  MODIFY `monthly_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance_criteria`
--
ALTER TABLE `performance_criteria`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `performance_evaluations`
--
ALTER TABLE `performance_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `job_satisfaction_surveys`
--
ALTER TABLE `job_satisfaction_surveys`
  ADD CONSTRAINT `job_satisfaction_surveys_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
