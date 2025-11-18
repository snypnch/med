-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Sep 27, 2025 at 03:16 AM
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
-- Database: `med`
--

-- --------------------------------------------------------

--
-- Table structure for table `activity_logs`
--

CREATE TABLE `activity_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `action` varchar(50) NOT NULL,
  `details` text DEFAULT NULL,
  `ip_address` varchar(50) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `activity_logs`
--

INSERT INTO `activity_logs` (`id`, `user_id`, `action`, `details`, `ip_address`, `created_at`) VALUES
(1, 2, 'login', 'User logged in', '::1', '2025-09-12 16:02:06'),
(2, 2, 'logout', 'User logged out', '::1', '2025-09-12 16:03:39'),
(3, 2, 'login', 'User logged in', '::1', '2025-09-13 02:20:41'),
(4, 2, 'logout', 'User logged out', '::1', '2025-09-13 02:21:17'),
(5, 8, 'login', 'User logged in', '::1', '2025-09-16 13:49:53'),
(6, 8, 'logout', 'User logged out', '::1', '2025-09-16 13:53:05'),
(7, 13, 'login', 'User logged in', '::1', '2025-09-16 14:03:20'),
(8, 13, 'logout', 'User logged out', '::1', '2025-09-16 14:13:52'),
(9, 14, 'login', 'User logged in', '::1', '2025-09-26 13:48:15'),
(10, 14, 'add_medication', 'Added medication: Alaxan FR', '::1', '2025-09-26 13:48:59'),
(11, 14, 'edit_medication', 'Updated medication ID: 4', '::1', '2025-09-26 13:50:44'),
(12, 14, 'edit_pharmacy', 'Updated pharmacy ID: 3', '::1', '2025-09-26 13:50:52'),
(13, 14, 'login', 'User logged in', '::1', '2025-09-27 00:30:29'),
(14, 14, 'update_price', 'Updated existing price for medication #4 at pharmacy #3', '::1', '2025-09-27 00:39:25'),
(15, 14, 'edit_price', 'Updated price ID: 15', '::1', '2025-09-27 00:39:35');

-- --------------------------------------------------------

--
-- Table structure for table `favorites`
--

CREATE TABLE `favorites` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `medications`
--

CREATE TABLE `medications` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `generic_name` varchar(255) DEFAULT NULL,
  `dosage` varchar(100) NOT NULL,
  `form` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `medications`
--

INSERT INTO `medications` (`id`, `name`, `generic_name`, `dosage`, `form`, `description`, `created_at`, `updated_at`) VALUES
(1, 'Paracetamol', 'Acetaminophen', '500mg', 'Tablet', 'Used to treat mild to moderate pain and reduce fever', '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(2, 'Biogesic', 'Paracetamol', '500mg', 'Tablet', 'Brand name for Paracetamol', '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(3, 'Amoxicillin', 'Amoxicillin', '500mg', 'Capsule', 'Antibiotic used to treat bacterial infections', '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(4, 'Alaxan FR', 'Ibuprofen + Paracetamol', '200mg/325mg', 'Tablet', 'Pain reliever and anti-inflammatory', '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(5, 'Alaxan FR', 'alaxan', '500', 'Tablet', 'asdasd', '2025-09-26 13:48:59', '2025-09-26 13:48:59');

-- --------------------------------------------------------

--
-- Table structure for table `medication_prices`
--

CREATE TABLE `medication_prices` (
  `id` int(11) NOT NULL,
  `medication_id` int(11) NOT NULL,
  `pharmacy_id` int(11) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp(),
  `status` enum('available','out_of_stock') NOT NULL DEFAULT 'available'
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `medication_prices`
--

INSERT INTO `medication_prices` (`id`, `medication_id`, `pharmacy_id`, `price`, `created_at`, `updated_at`, `status`) VALUES
(1, 1, 1, 15.00, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(2, 1, 2, 12.50, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(3, 1, 3, 10.00, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(4, 1, 4, 14.75, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(5, 2, 1, 22.50, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(6, 2, 2, 20.00, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(7, 2, 3, 18.75, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(8, 2, 4, 21.25, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(9, 3, 1, 45.00, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(10, 3, 2, 42.50, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(11, 3, 3, 40.00, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(12, 3, 4, 44.75, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(13, 4, 1, 35.25, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(14, 4, 2, 32.50, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available'),
(15, 4, 3, 12.00, '2025-09-12 15:46:53', '2025-09-27 00:39:35', 'out_of_stock'),
(16, 4, 4, 33.75, '2025-09-12 15:46:53', '2025-09-12 15:46:53', 'available');

-- --------------------------------------------------------

--
-- Table structure for table `pharmacies`
--

CREATE TABLE `pharmacies` (
  `id` int(11) NOT NULL,
  `name` varchar(255) NOT NULL,
  `address` text NOT NULL,
  `contact` varchar(100) DEFAULT NULL,
  `latitude` decimal(10,8) DEFAULT NULL,
  `longitude` decimal(11,8) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `pharmacies`
--

INSERT INTO `pharmacies` (`id`, `name`, `address`, `contact`, `latitude`, `longitude`, `created_at`, `updated_at`) VALUES
(1, 'Rose Pharmacy', 'Downtown Mandaue, Cebu', '032-123-4567', 10.32120000, 123.93350000, '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(2, 'Mercury Drug', 'Mandaue City Public Market', '032-234-5678', 10.32870000, 123.94250000, '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(3, 'Generics Pharmacy', 'Banilad, Mandaue City', '032-345-6789', 10.33540000, 123.92100000, '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(4, 'Watsons Pharmacy', 'Pacific Mall, Mandaue City', '032-456-7890', 10.31760000, 123.93000000, '2025-09-12 15:46:53', '2025-09-12 15:46:53');

-- --------------------------------------------------------

--
-- Table structure for table `regular_users`
--

CREATE TABLE `regular_users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `first_name` varchar(50) DEFAULT NULL,
  `last_name` varchar(50) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `regular_users`
--

INSERT INTO `regular_users` (`id`, `username`, `password`, `email`, `first_name`, `last_name`, `created_at`, `updated_at`) VALUES
(1, 'user', '$2y$10$ba4r/sgP.j61cBFjgINRa.RcPLEHEwfOj1emds.TqPowQrku.RUHO', 'user@medcompare.ph', 'Demo', 'User', '2025-09-12 15:46:53', '2025-09-12 15:46:53'),
(2, 'jonelreyes290', '$2y$10$u.UIbBatgtaqLZIgMCvpcuZVJdUKl9FUMImj9qqy2nfBca1x3akBi', 'example@gmail.com', 'neljonel', 'aviso', '2025-09-12 15:57:46', '2025-09-12 15:57:46'),
(3, 'jonelreyes123', '$2y$10$xrk41VBsk0S.Brdaf2nnX.BAg3.H98gn.9aXIeWbeZ/J4gZ4oWq5G', 'exampl44e@gmail.com', 'neljonel', 'aviso', '2025-09-12 16:01:03', '2025-09-12 16:01:03'),
(4, 'johnneil12311', '$2y$10$ZtUMj5AsilGd16DlHlZ97.bUyE3Mch.uUfsgtwMDW7L7SIZrEdM4S', 'example123a@gmail.com', 'neljonel', 'damaso', '2025-09-12 16:15:18', '2025-09-12 16:15:18');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `password` varchar(255) NOT NULL,
  `email` varchar(100) NOT NULL,
  `role` enum('admin') NOT NULL DEFAULT 'admin',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `password`, `email`, `role`, `created_at`, `updated_at`) VALUES
(14, 'admin', '$2y$10$lBPLLpp8vbsRVpVepNhk6OabEjrEoZA7eTCoy3YFcNmAtSyRLTNSW', 'mmedcompare@gmail.com', 'admin', '2025-09-26 13:47:47', '2025-09-27 01:16:08');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `activity_logs`
--
ALTER TABLE `activity_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `favorites`
--
ALTER TABLE `favorites`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `medication_id` (`medication_id`);

--
-- Indexes for table `medications`
--
ALTER TABLE `medications`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `medication_prices`
--
ALTER TABLE `medication_prices`
  ADD PRIMARY KEY (`id`),
  ADD KEY `medication_id` (`medication_id`),
  ADD KEY `pharmacy_id` (`pharmacy_id`);

--
-- Indexes for table `pharmacies`
--
ALTER TABLE `pharmacies`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `regular_users`
--
ALTER TABLE `regular_users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `activity_logs`
--
ALTER TABLE `activity_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- AUTO_INCREMENT for table `favorites`
--
ALTER TABLE `favorites`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `medications`
--
ALTER TABLE `medications`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `medication_prices`
--
ALTER TABLE `medication_prices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=17;

--
-- AUTO_INCREMENT for table `pharmacies`
--
ALTER TABLE `pharmacies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `regular_users`
--
ALTER TABLE `regular_users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=15;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `favorites`
--
ALTER TABLE `favorites`
  ADD CONSTRAINT `favorites_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `regular_users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `favorites_ibfk_2` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `medication_prices`
--
ALTER TABLE `medication_prices`
  ADD CONSTRAINT `medication_prices_ibfk_1` FOREIGN KEY (`medication_id`) REFERENCES `medications` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `medication_prices_ibfk_2` FOREIGN KEY (`pharmacy_id`) REFERENCES `pharmacies` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
