-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 192.168.0.2
-- Generation Time: Jul 03, 2024 at 09:15 AM
-- Server version: 10.11.2-MariaDB-1:10.11.2+maria~ubu2204
-- PHP Version: 8.1.16

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `adsb`
--
CREATE DATABASE IF NOT EXISTS `adsb` DEFAULT CHARACTER SET utf8mb4 COLLATE utf8mb4_general_ci;
USE `adsb`;

-- --------------------------------------------------------

--
-- Table structure for table `aircraft`
--

CREATE TABLE `aircraft` (
  `id` int(11) NOT NULL,
  `type` text DEFAULT NULL,
  `icao_type` text DEFAULT NULL,
  `manufacturer` text DEFAULT NULL,
  `mode_s` text DEFAULT NULL,
  `registration` text NOT NULL,
  `registered_owner_country_iso_name` text DEFAULT NULL,
  `registered_owner_country_name` text DEFAULT NULL,
  `registered_owner_operator_flag_code` text DEFAULT NULL,
  `registered_owner` text DEFAULT NULL,
  `url_photo` text DEFAULT NULL,
  `url_photo_thumbnail` text DEFAULT NULL,
  `seen` int(11) NOT NULL,
  `first_seen` datetime NOT NULL,
  `resolved` varchar(3) NOT NULL DEFAULT 'Yes'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `airports`
--

CREATE TABLE `airports` (
  `id` int(11) NOT NULL,
  `country_iso_name` varchar(3) NOT NULL,
  `country_name` varchar(16) NOT NULL,
  `elevation` int(11) NOT NULL,
  `iata_code` varchar(4) NOT NULL,
  `icao_code` varchar(4) NOT NULL,
  `latitude` text NOT NULL,
  `longitude` text NOT NULL,
  `municipality` text NOT NULL,
  `name` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `flights`
--

CREATE TABLE `flights` (
  `id` int(11) NOT NULL,
  `message_date` datetime NOT NULL,
  `now` varchar(100) DEFAULT NULL,
  `hex` varchar(100) DEFAULT NULL,
  `flight` varchar(100) DEFAULT NULL,
  `reg` varchar(20) DEFAULT NULL,
  `route` varchar(10) DEFAULT NULL,
  `src` varchar(100) DEFAULT NULL,
  `dst` varchar(100) DEFAULT NULL,
  `src_country` varchar(3) DEFAULT NULL,
  `dst_country` varchar(3) DEFAULT NULL,
  `distance` decimal(5,2) NOT NULL DEFAULT 0.00,
  `shortest_distance` decimal(5,2) NOT NULL DEFAULT 0.00,
  `largest_distance` decimal(5,2) NOT NULL DEFAULT 0.00,
  `altitude` varchar(100) DEFAULT NULL,
  `lowest` int(5) NOT NULL DEFAULT 0,
  `highest` int(5) NOT NULL DEFAULT 0,
  `lat` varchar(100) DEFAULT NULL,
  `lon` varchar(100) DEFAULT NULL,
  `track` varchar(100) DEFAULT NULL,
  `speed` decimal(6,2) NOT NULL DEFAULT 0.00,
  `slowest` decimal(6,2) NOT NULL DEFAULT 0.00,
  `fastest` decimal(6,2) NOT NULL DEFAULT 0.00,
  `vert_rate` varchar(100) DEFAULT NULL,
  `flags` varchar(16) DEFAULT NULL,
  `ws` varchar(16) DEFAULT NULL,
  `ws_low` varchar(16) DEFAULT NULL,
  `ws_high` varchar(16) DEFAULT NULL,
  `oat` varchar(16) DEFAULT NULL,
  `tat` varchar(16) DEFAULT NULL,
  `roll` varchar(16) DEFAULT NULL,
  `roll_left` varchar(16) DEFAULT NULL,
  `roll_right` varchar(16) DEFAULT NULL,
  `category` varchar(100) DEFAULT NULL,
  `squawk` varchar(100) DEFAULT NULL,
  `messages` varchar(100) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `aircraft`
--
ALTER TABLE `aircraft`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `airports`
--
ALTER TABLE `airports`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `flights`
--
ALTER TABLE `flights`
  ADD PRIMARY KEY (`id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `aircraft`
--
ALTER TABLE `aircraft`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `airports`
--
ALTER TABLE `airports`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `flights`
--
ALTER TABLE `flights`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
