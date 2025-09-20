-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 21, 2025 at 01:48 AM
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
-- Database: spstickets
--

-- --------------------------------------------------------

--
-- Table structure for table assignments
--

CREATE TABLE assignments (
  assignment_ticket int(11) NOT NULL,
  assignment_user int(11) NOT NULL,
  assignment_creation datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table autoassigns
--

CREATE TABLE autoassigns (
  autoassign_user int(11) NOT NULL,
  autoassign_category int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table categories
--

CREATE TABLE categories (
  category_id int(11) NOT NULL,
  category_name varchar(255) NOT NULL DEFAULT 'Nová kategorie'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table priorities
--

CREATE TABLE priorities (
  priority_id int(11) NOT NULL,
  priority_name varchar(255) NOT NULL DEFAULT 'Nová priorita',
  priority_weight int(11) NOT NULL DEFAULT 0,
  priority_color varchar(255) NOT NULL DEFAULT 'gray'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table rooms
--

CREATE TABLE rooms (
  room_id int(11) NOT NULL,
  room_name varchar(255) NOT NULL DEFAULT 'Nová místnost'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table sessions
--

CREATE TABLE sessions (
  session_id int(11) NOT NULL,
  session_user int(11) NOT NULL,
  session_address varchar(255) NOT NULL,
  session_creation datetime NOT NULL DEFAULT current_timestamp(),
  session_last_use datetime NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table teachers
--

CREATE TABLE teachers (
  teacher_id int(11) NOT NULL,
  teacher_code varchar(6) NOT NULL,
  teacher_name varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table tickets
--

CREATE TABLE tickets (
  ticket_id int(11) NOT NULL,
  ticket_creation datetime NOT NULL DEFAULT current_timestamp(),
  ticket_origin int(11) DEFAULT NULL,
  ticket_category int(11) DEFAULT NULL,
  ticket_room int(11) DEFAULT NULL,
  ticket_priority int(11) DEFAULT NULL,
  ticket_deadline date DEFAULT NULL,
  ticket_description text NOT NULL,
  ticket_is_open tinyint(1) NOT NULL DEFAULT 1
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table users
--

CREATE TABLE users (
  user_id int(11) NOT NULL,
  user_username varchar(255) NOT NULL,
  user_password varchar(255) NOT NULL,
  user_change_password tinyint(1) NOT NULL DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

-- --------------------------------------------------------

--
-- Table structure for table works
--

CREATE TABLE works (
  work_id int(11) NOT NULL,
  work_ticket int(11) NOT NULL,
  work_user int(11) DEFAULT NULL,
  work_minutes int(11) NOT NULL DEFAULT 0,
  work_description text NOT NULL DEFAULT ''
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_czech_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table assignments
--
ALTER TABLE assignments
  ADD PRIMARY KEY (assignment_ticket,assignment_user),
  ADD KEY assignments_ibfk_user (assignment_user);

--
-- Indexes for table autoassigns
--
ALTER TABLE autoassigns
  ADD PRIMARY KEY (autoassign_user,autoassign_category),
  ADD KEY autoassigns_ibfk_category (autoassign_category);

--
-- Indexes for table categories
--
ALTER TABLE categories
  ADD PRIMARY KEY (category_id);

--
-- Indexes for table priorities
--
ALTER TABLE priorities
  ADD PRIMARY KEY (priority_id);

--
-- Indexes for table rooms
--
ALTER TABLE rooms
  ADD PRIMARY KEY (room_id);

--
-- Indexes for table sessions
--
ALTER TABLE sessions
  ADD PRIMARY KEY (session_id),
  ADD KEY sessions_ibfk_user (session_user);

--
-- Indexes for table teachers
--
ALTER TABLE teachers
  ADD PRIMARY KEY (teacher_id),
  ADD UNIQUE KEY teacher_code (teacher_code);

--
-- Indexes for table tickets
--
ALTER TABLE tickets
  ADD PRIMARY KEY (ticket_id),
  ADD KEY tickets_ibfk_category (ticket_category),
  ADD KEY tickets_ibfk_origin (ticket_origin),
  ADD KEY tickets_ibfk_priority (ticket_priority),
  ADD KEY tickets_ibfk_room (ticket_room);

--
-- Indexes for table users
--
ALTER TABLE users
  ADD PRIMARY KEY (user_id),
  ADD UNIQUE KEY user_username (user_username);

--
-- Indexes for table works
--
ALTER TABLE works
  ADD PRIMARY KEY (work_id),
  ADD KEY works_ibfk_ticket (work_ticket),
  ADD KEY works_ibfk_user (work_user);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table categories
--
ALTER TABLE categories
  MODIFY category_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table priorities
--
ALTER TABLE priorities
  MODIFY priority_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table rooms
--
ALTER TABLE rooms
  MODIFY room_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table sessions
--
ALTER TABLE sessions
  MODIFY session_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table teachers
--
ALTER TABLE teachers
  MODIFY teacher_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table tickets
--
ALTER TABLE tickets
  MODIFY ticket_id int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table works
--
ALTER TABLE works
  MODIFY work_id int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

--
-- Constraints for table assignments
--
ALTER TABLE assignments
  ADD CONSTRAINT assignments_ibfk_ticket FOREIGN KEY (assignment_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT assignments_ibfk_user FOREIGN KEY (assignment_user) REFERENCES `users` (user_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table autoassigns
--
ALTER TABLE autoassigns
  ADD CONSTRAINT autoassigns_ibfk_category FOREIGN KEY (autoassign_category) REFERENCES categories (category_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT autoassigns_ibfk_user FOREIGN KEY (autoassign_user) REFERENCES `users` (user_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table sessions
--
ALTER TABLE sessions
  ADD CONSTRAINT sessions_ibfk_user FOREIGN KEY (session_user) REFERENCES `users` (user_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table tickets
--
ALTER TABLE tickets
  ADD CONSTRAINT tickets_ibfk_category FOREIGN KEY (ticket_category) REFERENCES categories (category_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT tickets_ibfk_origin FOREIGN KEY (ticket_origin) REFERENCES teachers (teacher_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT tickets_ibfk_priority FOREIGN KEY (ticket_priority) REFERENCES priorities (priority_id) ON DELETE SET NULL ON UPDATE CASCADE,
  ADD CONSTRAINT tickets_ibfk_room FOREIGN KEY (ticket_room) REFERENCES rooms (room_id) ON DELETE SET NULL ON UPDATE CASCADE;

--
-- Constraints for table users
--
ALTER TABLE users
  ADD CONSTRAINT users_ibfk_teacher FOREIGN KEY (user_id) REFERENCES teachers (teacher_id) ON DELETE CASCADE ON UPDATE CASCADE;

--
-- Constraints for table works
--
ALTER TABLE works
  ADD CONSTRAINT works_ibfk_ticket FOREIGN KEY (work_ticket) REFERENCES tickets (ticket_id) ON DELETE CASCADE ON UPDATE CASCADE,
  ADD CONSTRAINT works_ibfk_user FOREIGN KEY (work_user) REFERENCES `users` (user_id) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
