-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 12, 2024 at 09:12 AM
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
  `first_name` varchar(255) NOT NULL,
  `last_name` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `contact_number` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admin`
--

INSERT INTO `admin` (`id`, `username`, `password`, `user_type`, `status`, `first_name`, `last_name`, `email`, `contact_number`) VALUES
(1, 'Admin123', '$2y$10$NJzm1uVynBEavasjMmbkGOxhCdI3q2/ayRfToZqfhpHhEFgxvMpiW', 1, 1, 'John', 'Doe', 'admin123@gmail.com', '0978909898'),
(4, 'admin', '$2y$10$WZstvT8PqwzO/YULfZXr.e8uJoHHPGVDTQDbb8HkOuLuD2j75d4sK', 1, 1, 'John', 'mark', 'dccsamaniego@bpsu.edu.ph', '909233422131');

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
  `status` varchar(20) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`attendance_id`, `employee_id`, `date`, `clock_in_time`, `clock_out_time`, `status`) VALUES
(27, 12345, '2024-11-26', '10:16:03', '10:21:18', 'Late'),
(28, 45678, '2024-11-26', '04:16:41', '04:16:45', 'Late'),
(29, 12345, '2024-11-27', '08:25:44', '08:25:49', 'Late'),
(30, 45678, '2024-11-27', '08:25:53', '08:25:57', 'Late'),
(31, 41516, '2024-11-27', '10:18:36', '10:18:39', 'Late'),
(32, 41516, '2024-11-29', '08:11:51', '08:45:28', 'Late');

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
  `contact_number` varchar(20) DEFAULT NULL,
  `perma_address` text DEFAULT NULL,
  `civil_status` varchar(10) DEFAULT NULL,
  `sss_number` varchar(15) DEFAULT NULL,
  `philhealth_number` varchar(15) DEFAULT NULL,
  `pagibig_number` varchar(15) DEFAULT NULL,
  `tin_number` varchar(15) DEFAULT NULL,
  `emergency_contact_name` varchar(50) DEFAULT NULL,
  `emergency_contact_number` varchar(15) DEFAULT NULL,
  `educational_background` enum('High School Graduate','Vocational Graduate','College Undergraduate','College Graduate','Postgraduate') DEFAULT NULL,
  `skills` text DEFAULT NULL,
  `username` varchar(255) NOT NULL,
  `leave_credit` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `employees`
--

INSERT INTO `employees` (`id`, `last_name`, `first_name`, `middle_name`, `email`, `position`, `hire_date`, `department`, `employment_status`, `employee_id`, `user_type`, `password`, `e_status`, `date_of_birth`, `contact_number`, `perma_address`, `civil_status`, `sss_number`, `philhealth_number`, `pagibig_number`, `tin_number`, `emergency_contact_name`, `emergency_contact_number`, `educational_background`, `skills`, `username`, `leave_credit`) VALUES
(2, 'capulong', 'cristina', 'adona', 'admin123@gmail.com', 'Field Track', '2024-12-21', 'Sales', 'Active', 12345, 2, '09ca6ec0b133', 1, '2024-12-18', '09093556311', '010#/lower tundol/reformista', 'Married', '43-3453468-6', '21-432432342-2', '1246-3243-4235', '876-456-235-423', 'cristina adona capulong', '0965965965', 'High School Graduate', 'sql', 'Akali1423', 5);

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

-- --------------------------------------------------------

--
-- Table structure for table `leave_applications`
--

CREATE TABLE `leave_applications` (
  `leave_id` int(11) NOT NULL,
  `employee_id` int(11) NOT NULL,
  `leave_type` enum('Sick','Vacation','Emergency','Other') NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `reason` text DEFAULT NULL,
  `status` enum('Pending','Approved','Rejected') DEFAULT 'Pending'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `leave_applications`
--

INSERT INTO `leave_applications` (`leave_id`, `employee_id`, `leave_type`, `start_date`, `end_date`, `reason`, `status`) VALUES
(9, 41516, 'Emergency', '2024-11-30', '2024-12-07', 'diabetes', 'Pending');

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
(18, 12345, 1, '2024-12-12', '{\"job_knowledge\":\"1\",\"quality_of_work\":\"1\",\"work_ethic\":\"1\",\"communication_skills\":\"1\",\"punctuality\":\"1\",\"goals_achievements\":\"1\"}', '', 'Need Guidance', 1.00, 'Completed');

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

-- --------------------------------------------------------

--
-- Table structure for table `user`
--

CREATE TABLE `user` (
  `id` int(11) NOT NULL,
  `a_name` varchar(50) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(50) NOT NULL,
  `user_type` int(11) NOT NULL DEFAULT 3 COMMENT '1=admin, 2=staff, 3=super admin',
  `status` int(11) NOT NULL DEFAULT 1 COMMENT '1=active, 2=inactive'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `user`
--

INSERT INTO `user` (`id`, `a_name`, `username`, `password`, `user_type`, `status`) VALUES
(1, 'Super Admin', 'admin', 'admin123', 3, 1);

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
-- Indexes for table `user`
--
ALTER TABLE `user`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admin`
--
ALTER TABLE `admin`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `attendance_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=33;

--
-- AUTO_INCREMENT for table `attrition_forecasting`
--
ALTER TABLE `attrition_forecasting`
  MODIFY `forecast_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `educational_backgrounds`
--
ALTER TABLE `educational_backgrounds`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `employees`
--
ALTER TABLE `employees`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=3;

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
  MODIFY `survey_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_applications`
--
ALTER TABLE `leave_applications`
  MODIFY `leave_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=10;

--
-- AUTO_INCREMENT for table `monthly_attendance`
--
ALTER TABLE `monthly_attendance`
  MODIFY `monthly_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `performance_evaluations`
--
ALTER TABLE `performance_evaluations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=21;

--
-- AUTO_INCREMENT for table `reports`
--
ALTER TABLE `reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `user`
--
ALTER TABLE `user`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=91012;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `attrition_forecasting`
--
ALTER TABLE `attrition_forecasting`
  ADD CONSTRAINT `attrition_forecasting_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;

--
-- Constraints for table `job_satisfaction_surveys`
--
ALTER TABLE `job_satisfaction_surveys`
  ADD CONSTRAINT `job_satisfaction_surveys_ibfk_1` FOREIGN KEY (`employee_id`) REFERENCES `employees` (`employee_id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
