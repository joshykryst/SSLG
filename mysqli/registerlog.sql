-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 26, 2025 at 01:24 PM
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
-- Database: `registerlog`
--

-- --------------------------------------------------------

--
-- Table structure for table `table_user`
--

CREATE TABLE `table_user` (
  `User_ID` int(11) NOT NULL,
  `Username` text NOT NULL,
  `FirstName` text NOT NULL,
  `LastName` text NOT NULL,
  `Birthday` date NOT NULL,
  `Gender` text NOT NULL,
  `Email` text NOT NULL,
  `Password` text NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=latin1 COLLATE=latin1_swedish_ci;

--
-- Dumping data for table `table_user`
--

INSERT INTO `table_user` (`User_ID`, `Username`, `FirstName`, `LastName`, `Birthday`, `Gender`, `Email`, `Password`) VALUES
(8, 'Eoskie', 'Eos', 'Aguilar', '2007-09-25', 'Male', 'Eosagilar@gmail.com', 'eoskie'),
(9, 'alainah', 'alainah kacey', 'ocampo', '2025-02-06', 'Female', 'alainahkaye@gmail.com', 'alainahkaye'),
(10, 'Keenie', 'Keen', 'Canlas', '2007-03-14', 'Male', 'Keencanlas@gmail.com', 'keeni'),
(11, 'Keeners', 'Keen', 'Canlas', '2025-02-06', 'Male', 'Keen@gmail.com', 'keenie');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `table_user`
--
ALTER TABLE `table_user`
  ADD PRIMARY KEY (`User_ID`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `table_user`
--
ALTER TABLE `table_user`
  MODIFY `User_ID` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=12;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
