-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 25, 2024 at 12:39 PM
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
-- Database: `scheduling_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `courses`
--

CREATE TABLE `courses` (
  `id` int(11) NOT NULL,
  `course_code` varchar(50) NOT NULL,
  `course_name` varchar(255) NOT NULL,
  `units` int(11) NOT NULL,
  `academic_year` varchar(10) DEFAULT NULL,
  `semester` varchar(50) NOT NULL,
  `is_lecture` tinyint(1) DEFAULT 1,
  `is_lab` tinyint(1) DEFAULT 0,
  `lab_hours` int(11) DEFAULT 0,
  `lecture_hours` int(11) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `courses`
--

INSERT INTO `courses` (`id`, `course_code`, `course_name`, `units`, `academic_year`, `semester`, `is_lecture`, `is_lab`, `lab_hours`, `lecture_hours`) VALUES
(1, 'INTE1041', 'Computer Graphics Programming', 3, '4', 'First Semester', 1, 1, 3, 2),
(3, 'INTE1050', 'Game Development', 3, '4', 'First Semester', 1, 1, 3, 2),
(4, 'INSY1005', 'Information Assurance and Security', 3, '4', 'First Semester', 1, 1, 3, 2),
(5, 'INTE1030', 'IT Capstone Project 2', 3, '4', 'First Semester', 1, 1, 3, 2),
(6, 'INTE1013', 'IT Service Management', 3, '4', 'First Semester', 1, 1, 3, 2),
(7, 'INTE1030', 'Network Technology 2', 3, '4', 'First Semester', 1, 1, 3, 2),
(9, 'STIC1007', 'Euthenics 2', 1, '4', 'First Semester', 1, 0, 0, 2),
(10, 'INSY1011', 'Advanced Database Systems', 3, '3', 'Second Semester', 1, 1, 3, 2),
(11, 'INTE1056', 'Advanced Systems Integration and Architecture', 3, '3', 'First Semester', 1, 1, 3, 2),
(12, 'CITE1008', 'Application Development and Emerging Technologies', 3, '3', 'First Semester', 1, 1, 3, 2),
(13, 'GEDC1010', 'Art Appreciation', 3, '3', 'Second Semester', 1, 0, 0, 2),
(14, 'CITE1003', 'Computer Programming 1', 3, '3', 'First Semester', 1, 1, 3, 2),
(15, 'CITE1006', 'Computer Programming 2', 3, '3', 'Second Semester', 1, 1, 3, 2);

-- --------------------------------------------------------

--
-- Table structure for table `course_components`
--

CREATE TABLE `course_components` (
  `component_id` int(11) NOT NULL,
  `course_id` int(11) DEFAULT NULL,
  `component_type` enum('Lecture','Laboratory') DEFAULT NULL,
  `hours` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `course_components`
--

INSERT INTO `course_components` (`component_id`, `course_id`, `component_type`, `hours`) VALUES
(9, 1, 'Lecture', 2),
(10, 1, 'Laboratory', 3),
(11, 3, 'Lecture', 2),
(12, 3, 'Laboratory', 3),
(13, 4, 'Lecture', 2),
(14, 4, 'Laboratory', 3),
(15, 6, 'Lecture', 2),
(16, 6, 'Laboratory', 3),
(17, 9, 'Lecture', 2),
(18, 10, 'Lecture', 2),
(19, 10, 'Laboratory', 3),
(20, 11, 'Lecture', 2),
(21, 11, 'Laboratory', 3),
(22, 12, 'Lecture', 2),
(23, 12, 'Laboratory', 3),
(24, 13, 'Lecture', 2),
(25, 14, 'Lecture', 2),
(26, 14, 'Laboratory', 3),
(27, 15, 'Lecture', 2),
(28, 15, 'Laboratory', 3);

-- --------------------------------------------------------

--
-- Table structure for table `faculty`
--

CREATE TABLE `faculty` (
  `id` int(30) NOT NULL,
  `id_no` varchar(100) NOT NULL,
  `firstname` varchar(100) NOT NULL,
  `middlename` varchar(100) NOT NULL,
  `lastname` varchar(100) NOT NULL,
  `full_name` varchar(255) DEFAULT NULL,
  `contact` varchar(100) NOT NULL,
  `gender` varchar(100) NOT NULL,
  `address` text NOT NULL,
  `email` varchar(200) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty`
--

INSERT INTO `faculty` (`id`, `id_no`, `firstname`, `middlename`, `lastname`, `full_name`, `contact`, `gender`, `address`, `email`) VALUES
(4, '', 'Edmar Ian', '', 'Waje', 'Edmar Ian Waje', '', 'Male', 'San Francisco 2nd, Minalin', 'lnclot2721@gmail.com'),
(5, '', 'John Patrick', '', 'Garcia', 'John Patrick Garcia', '', 'Male', 'West Magnolia Boulevard Burbank CA', 'garcia@gmail.com'),
(6, '', 'Nathanael', '', 'Serrano', 'Nathanael Serrano', '', 'Male', '', 'serrano@gmail.com');

--
-- Triggers `faculty`
--
DELIMITER $$
CREATE TRIGGER `before_faculty_insert` BEFORE INSERT ON `faculty` FOR EACH ROW BEGIN
    SET NEW.full_name = CONCAT(NEW.firstname, CHAR(32), NEW.lastname);
END
$$
DELIMITER ;
DELIMITER $$
CREATE TRIGGER `before_faculty_update` BEFORE UPDATE ON `faculty` FOR EACH ROW BEGIN
    SET NEW.full_name = CONCAT(NEW.firstname, CHAR(32), NEW.lastname);
END
$$
DELIMITER ;

-- --------------------------------------------------------

--
-- Table structure for table `faculty_courses`
--

CREATE TABLE `faculty_courses` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `faculty_courses`
--

INSERT INTO `faculty_courses` (`id`, `course_id`, `faculty_id`) VALUES
(28, 4, 5),
(29, 5, 5),
(30, 6, 6),
(31, 7, 6),
(32, 1, 4),
(33, 3, 4);

-- --------------------------------------------------------

--
-- Table structure for table `generated_reports`
--

CREATE TABLE `generated_reports` (
  `report_id` int(11) NOT NULL,
  `program_id` varchar(10) NOT NULL,
  `academic_year` int(4) NOT NULL,
  `semester` varchar(50) NOT NULL,
  `year` int(4) NOT NULL,
  `reference_number` varchar(255) NOT NULL,
  `total_units` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `section_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `generated_reports`
--

INSERT INTO `generated_reports` (`report_id`, `program_id`, `academic_year`, `semester`, `year`, `reference_number`, `total_units`, `created_at`, `section_id`) VALUES
(42, 'BSIT', 4, 'First Semester', 2024, 'BSIT-4A-24', 16, '2024-11-21 19:07:07', 94),
(43, 'BSIT', 4, 'First Semester', 2024, 'BSIT-4B-24', 16, '2024-11-21 22:54:27', 95);

-- --------------------------------------------------------

--
-- Table structure for table `programs`
--

CREATE TABLE `programs` (
  `program_id` varchar(10) NOT NULL,
  `program_name` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `programs`
--

INSERT INTO `programs` (`program_id`, `program_name`) VALUES
('BSCE', 'Bachelor of Science in Computer Engineering'),
('BSCS', 'Bachelor of Science in Computer Science'),
('BSHM', 'Bachelor of Science in Hospitality Management'),
('BSIS', 'Bachelor of Science in Information Systems'),
('BSIT', 'Bachelor of Science in Information Technology');

-- --------------------------------------------------------

--
-- Table structure for table `program_courses`
--

CREATE TABLE `program_courses` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `program_id` varchar(10) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `program_courses`
--

INSERT INTO `program_courses` (`id`, `course_id`, `program_id`) VALUES
(8, 5, 'BSIT'),
(10, 7, 'BSIT'),
(18, 1, 'BSCE'),
(19, 1, 'BSIT'),
(21, 3, 'BSIT'),
(22, 4, 'BSIT'),
(23, 6, 'BSIT'),
(26, 9, 'BSIT'),
(27, 9, 'BSCE'),
(28, 10, 'BSIT'),
(29, 11, 'BSIT'),
(30, 12, 'BSIT'),
(31, 13, 'BSIT'),
(32, 14, 'BSIT'),
(33, 15, 'BSIT');

-- --------------------------------------------------------

--
-- Table structure for table `rooms`
--

CREATE TABLE `rooms` (
  `room_id` varchar(10) NOT NULL,
  `room_name` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rooms`
--

INSERT INTO `rooms` (`room_id`, `room_name`) VALUES
('305', 'Room 305'),
('402', 'Room 402'),
('410', 'Room 410'),
('CL1', 'Computer Lab 1'),
('CL2', 'Computer Lab 2'),
('CL3', 'Computer Lab 3'),
('CL4', 'Computer Lab 4');

-- --------------------------------------------------------

--
-- Table structure for table `schedules`
--

CREATE TABLE `schedules` (
  `id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL,
  `section_id` int(11) NOT NULL,
  `faculty_id` int(11) NOT NULL,
  `room_id` varchar(10) NOT NULL,
  `start_time` varchar(8) DEFAULT NULL,
  `end_time` varchar(8) DEFAULT NULL,
  `day_of_week` enum('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday') NOT NULL,
  `semester` enum('1st Term','2nd Term') NOT NULL,
  `academic_year` varchar(10) NOT NULL,
  `component_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `schedules`
--

INSERT INTO `schedules` (`id`, `course_id`, `section_id`, `faculty_id`, `room_id`, `start_time`, `end_time`, `day_of_week`, `semester`, `academic_year`, `component_id`) VALUES
(929, 1, 94, 4, '305', '10:00', '12:00', 'Monday', '', '4', 9),
(930, 1, 94, 4, 'CL1', '12:00', '15:00', 'Monday', '', '4', 10),
(931, 3, 94, 4, '305', '10:00', '12:00', 'Friday', '', '4', 11),
(932, 3, 94, 4, 'CL1', '12:00', '15:00', 'Friday', '', '4', 12),
(933, 4, 94, 5, 'CL3', '09:00', '11:00', 'Monday', '', '4', 13),
(934, 4, 94, 5, 'CL1', '11:00', '14:00', 'Thursday', '', '4', 14),
(935, 6, 94, 6, '305', '10:00', '12:00', 'Wednesday', '', '4', 15),
(936, 6, 94, 6, 'CL1', '12:00', '15:00', 'Wednesday', '', '4', 16);

-- --------------------------------------------------------

--
-- Table structure for table `sections`
--

CREATE TABLE `sections` (
  `section_id` int(11) NOT NULL,
  `section_name` varchar(50) NOT NULL,
  `program_id` varchar(10) NOT NULL,
  `academic_year` int(11) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `sections`
--

INSERT INTO `sections` (`section_id`, `section_name`, `program_id`, `academic_year`, `created_at`, `updated_at`) VALUES
(94, 'BSIT-4A', 'BSIT', 4, '2024-11-21 19:07:04', '2024-11-21 19:07:04'),
(95, 'BSIT-4B', 'BSIT', 4, '2024-11-21 22:54:25', '2024-11-21 22:54:25');

-- --------------------------------------------------------

--
-- Table structure for table `section_courses`
--

CREATE TABLE `section_courses` (
  `section_id` int(11) NOT NULL,
  `course_id` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `section_courses`
--

INSERT INTO `section_courses` (`section_id`, `course_id`) VALUES
(94, 1),
(94, 3),
(94, 4),
(94, 5),
(94, 6),
(94, 7),
(94, 9),
(95, 1),
(95, 3),
(95, 4),
(95, 5),
(95, 6),
(95, 7),
(95, 9);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(30) NOT NULL,
  `name` text NOT NULL,
  `username` varchar(200) NOT NULL,
  `password` text NOT NULL,
  `type` tinyint(1) NOT NULL DEFAULT 3 COMMENT '1=Admin,2=Staff, 3= subscriber',
  `role` enum('admin','program_head','faculty','student') NOT NULL DEFAULT 'faculty',
  `program_id` varchar(255) DEFAULT NULL,
  `faculty_id` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `username`, `password`, `type`, `role`, `program_id`, `faculty_id`) VALUES
(1, 'Administrator', 'admin', '0192023a7bbd73250516f069df18b500', 1, 'admin', NULL, NULL),
(8, 'Edmar Ian Waje', 'programhead1', '12579401b5348241690c338132f4613a', 3, 'program_head', 'BSIT', 4),
(9, 'John Patrick Garcia', 'faculty1', '85b954cf9565b9c54add85f09281a50b', 3, 'faculty', 'BSIT', 5),
(10, 'Nathanael Serrano', 'faculty2', '85b954cf9565b9c54add85f09281a50b', 3, 'faculty', 'BSIT', 6);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `courses`
--
ALTER TABLE `courses`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `course_components`
--
ALTER TABLE `course_components`
  ADD PRIMARY KEY (`component_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `faculty`
--
ALTER TABLE `faculty`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `faculty_courses`
--
ALTER TABLE `faculty_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `course_id` (`course_id`),
  ADD KEY `faculty_id` (`faculty_id`);

--
-- Indexes for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD PRIMARY KEY (`report_id`),
  ADD KEY `program_id` (`program_id`),
  ADD KEY `section_id` (`section_id`);

--
-- Indexes for table `programs`
--
ALTER TABLE `programs`
  ADD PRIMARY KEY (`program_id`);

--
-- Indexes for table `program_courses`
--
ALTER TABLE `program_courses`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_program_courses_course` (`course_id`),
  ADD KEY `fk_program_courses_program` (`program_id`);

--
-- Indexes for table `rooms`
--
ALTER TABLE `rooms`
  ADD PRIMARY KEY (`room_id`);

--
-- Indexes for table `schedules`
--
ALTER TABLE `schedules`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_course` (`course_id`),
  ADD KEY `fk_section` (`section_id`),
  ADD KEY `fk_faculty` (`faculty_id`),
  ADD KEY `fk_room` (`room_id`),
  ADD KEY `component_id` (`component_id`);

--
-- Indexes for table `sections`
--
ALTER TABLE `sections`
  ADD PRIMARY KEY (`section_id`),
  ADD UNIQUE KEY `section_name` (`section_name`),
  ADD KEY `sections_ibfk_1` (`program_id`);

--
-- Indexes for table `section_courses`
--
ALTER TABLE `section_courses`
  ADD PRIMARY KEY (`section_id`,`course_id`),
  ADD KEY `course_id` (`course_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD KEY `fk_users_program` (`program_id`),
  ADD KEY `fk_faculty_user` (`faculty_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `courses`
--
ALTER TABLE `courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `course_components`
--
ALTER TABLE `course_components`
  MODIFY `component_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=29;

--
-- AUTO_INCREMENT for table `faculty`
--
ALTER TABLE `faculty`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `faculty_courses`
--
ALTER TABLE `faculty_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `generated_reports`
--
ALTER TABLE `generated_reports`
  MODIFY `report_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=44;

--
-- AUTO_INCREMENT for table `program_courses`
--
ALTER TABLE `program_courses`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=34;

--
-- AUTO_INCREMENT for table `schedules`
--
ALTER TABLE `schedules`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=945;

--
-- AUTO_INCREMENT for table `sections`
--
ALTER TABLE `sections`
  MODIFY `section_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=96;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(30) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `course_components`
--
ALTER TABLE `course_components`
  ADD CONSTRAINT `course_components_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `faculty_courses`
--
ALTER TABLE `faculty_courses`
  ADD CONSTRAINT `faculty_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `faculty_courses_ibfk_2` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `generated_reports`
--
ALTER TABLE `generated_reports`
  ADD CONSTRAINT `generated_reports_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `generated_reports_ibfk_2` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`);

--
-- Constraints for table `program_courses`
--
ALTER TABLE `program_courses`
  ADD CONSTRAINT `fk_program_courses_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_program_courses_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `program_courses_ibfk_1` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`),
  ADD CONSTRAINT `program_courses_ibfk_2` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `schedules`
--
ALTER TABLE `schedules`
  ADD CONSTRAINT `fk_course` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_faculty` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_room` FOREIGN KEY (`room_id`) REFERENCES `rooms` (`room_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `fk_section` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `schedules_ibfk_1` FOREIGN KEY (`component_id`) REFERENCES `course_components` (`component_id`);

--
-- Constraints for table `sections`
--
ALTER TABLE `sections`
  ADD CONSTRAINT `sections_ibfk_1` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`);

--
-- Constraints for table `section_courses`
--
ALTER TABLE `section_courses`
  ADD CONSTRAINT `section_courses_ibfk_1` FOREIGN KEY (`section_id`) REFERENCES `sections` (`section_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `section_courses_ibfk_2` FOREIGN KEY (`course_id`) REFERENCES `courses` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_faculty_user` FOREIGN KEY (`faculty_id`) REFERENCES `faculty` (`id`) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT `fk_users_program` FOREIGN KEY (`program_id`) REFERENCES `programs` (`program_id`) ON DELETE SET NULL;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
