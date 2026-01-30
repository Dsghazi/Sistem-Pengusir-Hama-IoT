-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: localhost
-- Generation Time: Jan 30, 2026 at 04:28 AM
-- Server version: 10.3.39-MariaDB-0ubuntu0.20.04.2-log
-- PHP Version: 8.3.17

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `ghazi_riwayat_iot`
--

-- --------------------------------------------------------

--
-- Table structure for table `log_debug`
--

CREATE TABLE `log_debug` (
  `id` int(11) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `tipe` varchar(20) NOT NULL,
  `pesan` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `log_debug`
--

INSERT INTO `log_debug` (`id`, `waktu`, `tipe`, `pesan`) VALUES
(2, '2026-01-28 17:36:03', 'FATAL', 'DFPlayer Not Responding'),
(3, '2026-01-28 17:37:05', 'FATAL', 'DFPlayer Not Responding'),
(4, '2026-01-28 17:38:02', 'FATAL', 'DFPlayer Not Responding'),
(5, '2026-01-28 17:39:02', 'FATAL', 'DFPlayer Not Responding'),
(6, '2026-01-28 17:40:03', 'FATAL', 'DFPlayer Not Responding'),
(7, '2026-01-28 17:41:05', 'FATAL', 'DFPlayer Not Responding'),
(8, '2026-01-28 18:00:46', 'WARNING', 'DFPlayer Unresponsive'),
(9, '2026-01-28 18:04:56', 'WARNING', 'DFPlayer Unresponsive'),
(10, '2026-01-28 18:05:06', 'WARNING', 'DFPlayer Unresponsive'),
(11, '2026-01-28 18:05:30', 'WARNING', 'DFPlayer Unresponsive'),
(12, '2026-01-28 18:17:24', 'WARNING', 'Low WiFi Signal: -91dBm'),
(13, '2026-01-28 18:22:28', 'WARNING', 'Low WiFi Signal: -91dBm');

-- --------------------------------------------------------

--
-- Table structure for table `riwayat_alat`
--

CREATE TABLE `riwayat_alat` (
  `id` int(11) NOT NULL,
  `waktu` timestamp NOT NULL DEFAULT current_timestamp(),
  `status` varchar(10) NOT NULL,
  `sumber` varchar(50) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `riwayat_alat`
--

INSERT INTO `riwayat_alat` (`id`, `waktu`, `status`, `sumber`) VALUES
(10, '2026-01-25 15:38:03', 'OFF', 'AUTO JADWAL-1'),
(14, '2026-01-25 15:47:14', 'OFF', 'WEB'),
(17, '2026-01-25 16:50:06', 'OFF', 'WEB'),
(18, '2026-01-25 16:50:39', 'ON', 'WEB'),
(28, '2026-01-25 18:29:58', 'OFF', 'WEB'),
(29, '2026-01-25 18:30:03', 'ON', 'AUTO JADWAL-1'),
(34, '2026-01-25 18:46:03', 'OFF', 'AUTO JADWAL-2'),
(35, '2026-01-25 19:00:03', 'ON', 'AUTO JADWAL-1'),
(36, '2026-01-25 20:00:03', 'OFF', 'AUTO JADWAL-1'),
(37, '2026-01-27 06:20:19', 'ON', 'WEB'),
(38, '2026-01-27 06:20:22', 'OFF', 'AUTO JADWAL-1'),
(39, '2026-01-27 06:20:48', 'ON', 'WEB'),
(40, '2026-01-27 10:28:45', 'ON', 'WEB'),
(41, '2026-01-27 10:31:36', 'OFF', 'WEB'),
(42, '2026-01-28 06:38:48', 'ON', 'WEB'),
(43, '2026-01-28 06:40:56', 'OFF', 'WEB'),
(44, '2026-01-28 06:41:06', 'ON', 'WEB'),
(45, '2026-01-28 07:47:20', 'OFF', 'AUTO JADWAL-2'),
(46, '2026-01-28 07:48:03', 'ON', 'AUTO JADWAL-3'),
(47, '2026-01-28 07:49:03', 'OFF', 'AUTO JADWAL-3'),
(48, '2026-01-28 17:34:09', 'ON', 'WEB'),
(49, '2026-01-28 18:06:16', 'ON', 'WEB'),
(50, '2026-01-28 18:30:08', 'ON', 'WEB');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `log_debug`
--
ALTER TABLE `log_debug`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `riwayat_alat`
--
ALTER TABLE `riwayat_alat`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `log_debug`
--
ALTER TABLE `log_debug`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `riwayat_alat`
--
ALTER TABLE `riwayat_alat`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=51;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
