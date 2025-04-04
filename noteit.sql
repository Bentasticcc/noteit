-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Apr 05, 2025 at 12:12 AM
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
-- Database: `noteit`
--

-- --------------------------------------------------------

--
-- Table structure for table `notes`
--

CREATE TABLE `notes` (
  `id` int(11) NOT NULL,
  `title` varchar(255) DEFAULT NULL,
  `content` text DEFAULT NULL,
  `date` date DEFAULT NULL,
  `dot` varchar(20) DEFAULT NULL,
  `archived` tinyint(1) DEFAULT 0,
  `deleted_at` datetime DEFAULT NULL,
  `favorite` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `notes`
--

INSERT INTO `notes` (`id`, `title`, `content`, `date`, `dot`, `archived`, `deleted_at`, `favorite`) VALUES
(1, 'Notes 1', 'This is a sample note.', '2024-03-03', 'orange', 0, NULL, 0),
(2, 'Notes 1', 'Another sample note.', '2024-03-03', '', 0, NULL, 0),
(3, 'Notes 1', 'More notes.', '2024-03-03', '', 0, NULL, 0),
(4, 'Notes 2', 'Some other notes.', '2024-03-03', '', 0, NULL, 0),
(5, 'Notes 2', 'More sample text.', '2024-03-03', '', 0, NULL, 0),
(6, 'Notes 2', 'Final sample note.', '2024-03-03', 'orange', 0, NULL, 0),
(7, 'Notes 3', 'Note three content.', '2024-03-03', '', 0, NULL, 0),
(8, 'Notes 3', 'Another Note 3.', '2024-03-03', 'orange', 0, NULL, 0),
(9, 'Notes 3', 'Last Note 3.', '2024-03-03', '', 0, NULL, 0),
(17, 'TEST', 'TEST', '2025-04-05', '', 0, NULL, 0);

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`) VALUES
(7, 'test', 'test@gmail.com', '$2y$10$9f8X7/M0WREajtg9/UDGgeXnjTr.LIUGsn3TFxeRu.XpegEH782tS');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `notes`
--
ALTER TABLE `notes`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `notes`
--
ALTER TABLE `notes`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
