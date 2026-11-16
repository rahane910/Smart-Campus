-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost:3306
-- Generation Time: Apr 19, 2026 at 09:45 PM
-- Server version: 10.4.32-MariaDB
-- PHP Version: 8.0.30

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `smart_campus`
--

-- --------------------------------------------------------

--
-- Table structure for table `admins`
--

CREATE TABLE `admins` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `admins`
--

INSERT INTO `admins` (`id`, `name`, `email`, `password`, `created_at`) VALUES
(1, NULL, 'admin@gmail.com', 'admin@123', '2026-04-19 18:47:08');

-- --------------------------------------------------------

--
-- Table structure for table `attendance`
--

CREATE TABLE `attendance` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) NOT NULL,
  `subject` varchar(100) NOT NULL,
  `status` varchar(20) NOT NULL,
  `date` date NOT NULL,
  `time` time NOT NULL,
  `attendance_percent` float DEFAULT 0,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `attendance`
--

INSERT INTO `attendance` (`id`, `student_id`, `subject`, `status`, `date`, `time`, `attendance_percent`, `created_at`) VALUES
(1, 'STUD12345', 'General', 'Present', '2026-04-20', '00:00:21', 0, '2026-04-19 18:30:21');

-- --------------------------------------------------------

--
-- Table structure for table `complaints`
--

CREATE TABLE `complaints` (
  `id` int(11) NOT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `complaint_title` varchar(255) DEFAULT NULL,
  `complaint_category` varchar(100) DEFAULT NULL,
  `priority` varchar(20) DEFAULT NULL,
  `complaint_text` text DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `complaints`
--

INSERT INTO `complaints` (`id`, `student_id`, `complaint_title`, `complaint_category`, `priority`, `complaint_text`, `status`, `created_at`) VALUES
(1, 'FACULTY2026', NULL, NULL, NULL, 'Title: fight\nCategory: Hostel\nPriority: Medium\nDetails: not faire', 'Pending', '2026-04-19 18:33:13'),
(2, 'STUD12345', NULL, NULL, NULL, 'Title: campus not perfect\nCategory: Academic\nPriority: Medium\nDetails: not good', 'Pending', '2026-04-19 19:12:05'),
(3, 'STUD12345', NULL, NULL, NULL, 'Title: campus not perfect\nCategory: Academic\nPriority: Medium\nDetails: not good', 'Pending', '2026-04-19 19:12:13');

-- --------------------------------------------------------

--
-- Table structure for table `departments`
--

CREATE TABLE `departments` (
  `id` int(11) NOT NULL,
  `department_name` varchar(100) NOT NULL,
  `hod_name` varchar(100) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `departments`
--

INSERT INTO `departments` (`id`, `department_name`, `hod_name`, `created_at`) VALUES
(1, 'Computer Science ', 'P.S.Roham', '2026-04-19 19:03:03');

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `faculty_id` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `qualification` varchar(100) DEFAULT NULL,
  `experience` varchar(50) DEFAULT NULL,
  `joining_date` date DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `designation` varchar(100) DEFAULT NULL,
  `specialization` varchar(100) DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `name`, `email`, `password`, `faculty_id`, `department`, `qualification`, `experience`, `joining_date`, `mobile`, `designation`, `specialization`, `gender`, `dob`, `photo`, `created_at`) VALUES
(1, 'ritesh balasaheb wabale', 'ritesh1234@gmail.com', '$2y$10$PHeUIs0F6zjZpEnsPmE4YebWkvlsXatIstFAs5ZRqQSZsjFBaQyim', 'FACULTY2026', 'Computer Science', 'B.SC', '3', '2024-02-20', '9749958787', 'PROFESSOR', 'MATH', 'Male', '2000-11-11', '1776621611_ritesh.jpeg', '2026-04-19 18:00:11');

-- --------------------------------------------------------

--
-- Table structure for table `faculty_calendar`
--

CREATE TABLE `faculty_calendar` (
  `id` int(11) NOT NULL,
  `faculty_id` int(11) DEFAULT NULL,
  `event_title` varchar(255) DEFAULT NULL,
  `event_date` date DEFAULT NULL,
  `event_type` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_calendar`
--

INSERT INTO `faculty_calendar` (`id`, `faculty_id`, `event_title`, `event_date`, `event_type`, `created_at`) VALUES
(1, 0, 'ambedkar jayanti', '2026-04-14', 'Holiday', '2026-04-19 18:15:50');

-- --------------------------------------------------------

--
-- Table structure for table `leave_requests`
--

CREATE TABLE `leave_requests` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `student_id` int(11) NOT NULL,
  `reason` text DEFAULT NULL,
  `from_date` date DEFAULT NULL,
  `to_date` date DEFAULT NULL,
  `status` varchar(20) DEFAULT 'Pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `notices`
--

CREATE TABLE `notices` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `posted_by` int(11) DEFAULT NULL,
  `notice_file` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notices`
--

INSERT INTO `notices` (`id`, `title`, `content`, `posted_by`, `notice_file`, `created_at`) VALUES
(1, '', '', 1, 'NOTICE_1776624665_3467.jpeg', '2026-04-19 18:51:05');

-- --------------------------------------------------------

--
-- Table structure for table `notice_acknowledgements`
--

CREATE TABLE `notice_acknowledgements` (
  `id` int(11) NOT NULL,
  `notice_id` int(11) DEFAULT NULL,
  `student_id` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notice_acknowledgements`
--

INSERT INTO `notice_acknowledgements` (`id`, `notice_id`, `student_id`, `created_at`) VALUES
(1, 1, 0, '2026-04-19 19:11:30');

-- --------------------------------------------------------

--
-- Table structure for table `resources`
--

CREATE TABLE `resources` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `class_name` varchar(50) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `subject` varchar(100) DEFAULT NULL,
  `file_name` varchar(255) DEFAULT NULL,
  `uploaded_by` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resources`
--

INSERT INTO `resources` (`id`, `title`, `description`, `class_name`, `course`, `subject`, `file_name`, `uploaded_by`, `created_at`) VALUES
(1, 'Web tech', 'practical lab book sem-6', 'TY', 'BCS', 'Web Technology-II', '1776622943_Web Technologies-II_Data Analytics_Lab_Book.pdf', 0, '2026-04-19 18:22:23');

-- --------------------------------------------------------

--
-- Table structure for table `students`
--

CREATE TABLE `students` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `password` varchar(255) DEFAULT NULL,
  `student_id` varchar(50) DEFAULT NULL,
  `roll_no` varchar(50) DEFAULT NULL,
  `prn_no` varchar(50) DEFAULT NULL,
  `course` varchar(50) DEFAULT NULL,
  `semester` varchar(50) DEFAULT NULL,
  `department` varchar(100) DEFAULT NULL,
  `admission_year` varchar(10) DEFAULT NULL,
  `dob` date DEFAULT NULL,
  `gender` varchar(10) DEFAULT NULL,
  `mobile` varchar(15) DEFAULT NULL,
  `parent_mobile` varchar(15) DEFAULT NULL,
  `address` text DEFAULT NULL,
  `photo` varchar(255) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `class_name` varchar(50) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `students`
--

INSERT INTO `students` (`id`, `name`, `email`, `password`, `student_id`, `roll_no`, `prn_no`, `course`, `semester`, `department`, `admission_year`, `dob`, `gender`, `mobile`, `parent_mobile`, `address`, `photo`, `created_at`, `class_name`) VALUES
(1, 'Shubham Ashok Rahane', 'rahane910@gmail.com', '$2y$10$G91wkcX2CWwIO8ayqHpKvOzJelsK8j.VMOjw/HuQSJQJFUuJZgm6e', 'STUD12345', '2543069', '55521554216323', 'BCS', 'Semester 6', 'Computer Science', '', '0000-00-00', '', '7499587873', '2525252525', 'Deolane , devthan road', '1776620594_shubham.png', '2026-04-19 17:43:14', 'TY');

-- --------------------------------------------------------

--
-- Table structure for table `timetable`
--

CREATE TABLE `timetable` (
  `id` int(11) NOT NULL,
  `class_name` varchar(20) DEFAULT NULL,
  `course` varchar(100) DEFAULT NULL,
  `time` varchar(50) DEFAULT NULL,
  `monday` varchar(100) DEFAULT NULL,
  `tuesday` varchar(100) DEFAULT NULL,
  `wednesday` varchar(100) DEFAULT NULL,
  `thursday` varchar(100) DEFAULT NULL,
  `friday` varchar(100) DEFAULT NULL,
  `saturday` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `timetable`
--

INSERT INTO `timetable` (`id`, `class_name`, `course`, `time`, `monday`, `tuesday`, `wednesday`, `thursday`, `friday`, `saturday`) VALUES
(1, 'TY', 'BCS', '11:00 - 11:45 Am', 'Math', 'bio', 'chemestry', 'history', 'Geography', 'IT'),
(2, 'TY', 'BCS', '11:45 - 12:30', 'bio', 'MATH', 'history', 'CHEMISTRY', 'IT', 'Geography'),
(3, 'TY', 'BCS', '01:00 - 02:00', 'LUNCH BREAK', 'LUNCH BREAK', 'LUNCH BREAK', 'LUNCH BREAK', 'LUNCH BREAK', 'LUNCH BREAK');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `admins`
--
ALTER TABLE `admins`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `attendance`
--
ALTER TABLE `attendance`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `complaints`
--
ALTER TABLE `complaints`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `departments`
--
ALTER TABLE `departments`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `faculty_calendar`
--
ALTER TABLE `faculty_calendar`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `leave_requests`
--
ALTER TABLE `leave_requests`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notices`
--
ALTER TABLE `notices`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `notice_acknowledgements`
--
ALTER TABLE `notice_acknowledgements`
  ADD PRIMARY KEY (`id`),
  ADD KEY `notice_id` (`notice_id`);

--
-- Indexes for table `resources`
--
ALTER TABLE `resources`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `students`
--
ALTER TABLE `students`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `timetable`
--
ALTER TABLE `timetable`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `admins`
--
ALTER TABLE `admins`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `attendance`
--
ALTER TABLE `attendance`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `complaints`
--
ALTER TABLE `complaints`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `departments`
--
ALTER TABLE `departments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `faculty_calendar`
--
ALTER TABLE `faculty_calendar`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `leave_requests`
--
ALTER TABLE `leave_requests`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `notices`
--
ALTER TABLE `notices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `notice_acknowledgements`
--
ALTER TABLE `notice_acknowledgements`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `resources`
--
ALTER TABLE `resources`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `students`
--
ALTER TABLE `students`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `timetable`
--
ALTER TABLE `timetable`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `notice_acknowledgements`
--
ALTER TABLE `notice_acknowledgements`
  ADD CONSTRAINT `notice_acknowledgements_ibfk_1` FOREIGN KEY (`notice_id`) REFERENCES `notices` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
