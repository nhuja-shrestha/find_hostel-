-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Feb 22, 2026 at 05:30 AM
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
-- Database: `hostel_finder1`
--

-- --------------------------------------------------------

--
-- Table structure for table `contact_messages`
--

CREATE TABLE `contact_messages` (
  `id` int(11) NOT NULL,
  `name` varchar(100) DEFAULT NULL,
  `email` varchar(100) DEFAULT NULL,
  `subject` varchar(200) DEFAULT NULL,
  `message` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `contact_messages`
--

INSERT INTO `contact_messages` (`id`, `name`, `email`, `subject`, `message`, `created_at`) VALUES
(1, 'Nhuja shrestha', 'nhujashrestha121212@gmail.com', 'dai hostel ko bare', 'dai hostel ta ramro lagyo paisa chai kati ho', '2026-02-16 05:22:51');

-- --------------------------------------------------------

--
-- Table structure for table `hostels`
--

CREATE TABLE `hostels` (
  `hostel_id` int(11) NOT NULL,
  `owner_id` int(11) NOT NULL,
  `name` varchar(150) NOT NULL,
  `location` varchar(200) NOT NULL,
  `price` decimal(10,2) NOT NULL,
  `rooms` int(11) NOT NULL,
  `room_type` varchar(50) DEFAULT NULL,
  `description` text DEFAULT NULL,
  `facilities` text DEFAULT NULL,
  `status` enum('pending','active','inactive') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostels`
--

INSERT INTO `hostels` (`hostel_id`, `owner_id`, `name`, `location`, `price`, `rooms`, `room_type`, `description`, `facilities`, `status`, `created_at`) VALUES
(8, 11, 'New moon girls hostel', 'katunje, bhaktapur', 6000.00, 4, '0', 'Our hostel offers fully furnished rooms, hygienic meals, high-speed Wi-Fi, clean bathrooms, 24/7 security, and regular housekeeping to ensure a safe and comfortable living environment for students.', 'furnished rooms, clean bathrooms, regular water and electricity supply, nutritious meals, Wi-Fi, study areas, security services like CCTV and guards, laundry facilities, and recreational spaces for students.', 'active', '2026-02-16 02:39:13'),
(10, 11, 'Sunshine Boys Hostel ', 'Kumaripati, Patan', 7000.00, 5, '0', 'Sunshine Boys Hostel offers a safe, clean, and comfortable stay with essential facilities like WiFi, electricity, water, meals, and security. It provides a peaceful, homely environment that supports students’ comfort, discipline, and success.', 'Accommodation,  Security,  Electricity,  Water,  WiFi', 'active', '2026-02-16 08:20:29'),
(11, 15, 'Mount Everest Girls Hostel', 'New Baneshwor, Kathmandu', 9500.00, 8, '0', 'Description: A welcoming girls’ hostel with strong security and utilities, plus hot water and sanitary support services. Great choice for focused students who value comfort and safety.', 'Accommodation, Electricity, Water, WiFi, Security Guards, Sanitary Support', 'active', '2026-02-17 20:02:36'),
(13, 15, 'Universal Boys Hostel', 'Putalisadak, Kathmandu', 9800.00, 8, 'Double', 'Well-located and clean boys’ hostel with strong WiFi and a dedicated study room. A good balance of security and convenience near colleges and public transport.', 'Accommodation, Security, Electricity, Water, WiFi, Study Room', 'active', '2026-02-17 20:08:29');

-- --------------------------------------------------------

--
-- Table structure for table `hostel_images`
--

CREATE TABLE `hostel_images` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `image_path` varchar(255) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `hostel_images`
--

INSERT INTO `hostel_images` (`id`, `hostel_id`, `image_path`) VALUES
(5, 6, '1764346230_0_room2.jpeg'),
(6, 7, '1764346270_0_room3.webp'),
(7, 8, '1771209553_0_1764420480_0_mt.jpg'),
(16, 10, '1771356747_sagarmatha.jpg.jpeg'),
(17, 10, '1771356765_hakus2.jpg.jpeg'),
(18, 10, '1771356781_sunshine1.jpg'),
(19, 8, '1771357433_mt1.jpg.jpeg'),
(28, 11, '1771358647_peace.jpg (1).jpeg'),
(30, 11, '1771358749_peace1.jpg.jpeg'),
(31, 11, '1771358762_peace2.jpg.jpeg'),
(32, 13, '1771358909_0_hakus_jpg.jpeg'),
(33, 13, '1771358909_1_download__2__jpg.jpeg'),
(34, 13, '1771358909_2_download_jpg.jpeg');

-- --------------------------------------------------------

--
-- Table structure for table `inquiries`
--

CREATE TABLE `inquiries` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `message` text DEFAULT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiries`
--

INSERT INTO `inquiries` (`id`, `user_id`, `hostel_id`, `message`, `status`, `created_at`) VALUES
(18, 14, 11, 'Hello, I’m interested in booking a room at your hostel. Could you please let me know if there are any available rooms for the upcoming month?', 'pending', '2026-02-17 20:16:04'),
(19, 14, 8, 'Hi, I’m interested in staying at your hostel. I have a few requests regarding room placement and amenities. Can you let me know if that’s possible?', 'approved', '2026-02-17 20:16:56');

-- --------------------------------------------------------

--
-- Table structure for table `inquiry_replies`
--

CREATE TABLE `inquiry_replies` (
  `id` int(11) NOT NULL,
  `inquiry_id` int(11) NOT NULL,
  `sender` enum('student','owner') NOT NULL,
  `sender_id` int(11) NOT NULL,
  `message` text NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `inquiry_replies`
--

INSERT INTO `inquiry_replies` (`id`, `inquiry_id`, `sender`, `sender_id`, `message`, `created_at`) VALUES
(4, 19, 'owner', 0, 'Hello, thank you for your interest in our hostel. We’d be happy to accommodate your requests as much as possible. Could you please specify your requirements regarding room placement and amenities so we can check availability and confirm?', '2026-02-17 20:18:10');

-- --------------------------------------------------------

--
-- Table structure for table `reservations`
--

CREATE TABLE `reservations` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `status` enum('pending','approved','rejected') DEFAULT 'pending',
  `reserved_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `reservations`
--

INSERT INTO `reservations` (`id`, `user_id`, `hostel_id`, `status`, `reserved_at`) VALUES
(1, 14, 11, 'approved', '2026-02-22 03:03:16'),
(2, 14, 13, 'rejected', '2026-02-22 03:25:29'),
(5, 14, 10, 'approved', '2026-02-22 04:21:52'),
(6, 14, 8, 'approved', '2026-02-22 04:23:23');

-- --------------------------------------------------------

--
-- Table structure for table `reviews`
--

CREATE TABLE `reviews` (
  `id` int(11) NOT NULL,
  `hostel_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `rating` int(11) NOT NULL CHECK (`rating` between 1 and 5),
  `comment` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `email` varchar(100) NOT NULL,
  `phone` varchar(20) DEFAULT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('user','owner','student') NOT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `id_type` varchar(20) DEFAULT NULL,
  `verification_status` enum('pending','verified','rejected') DEFAULT 'pending',
  `id_document_front` varchar(255) DEFAULT NULL,
  `id_document_back` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `name`, `email`, `phone`, `password`, `role`, `created_at`, `id_type`, `verification_status`, `id_document_front`, `id_document_back`) VALUES
(11, 'alisha khiuju', 'alisha@gmail.com', NULL, 'alisha@123', 'owner', '2026-02-16 02:32:57', NULL, 'pending', NULL, NULL),
(14, 'unika', 'unika@gmail.com', NULL, 'Unika@123', 'student', '2026-02-16 03:54:30', 'citizenship', 'pending', 'citizenship_front_699294f6a616b.jpg', 'citizenship_back_699294f6a6170.jpg'),
(15, 'Niva Shrestha ', 'niva@gmail.com', NULL, 'niva@123', 'owner', '2026-02-17 19:56:35', 'citizenship', 'pending', 'citizenship_front_6994c7f308002.jpg', 'citizenship_back_6994c7f308006.jpg');

--
-- Indexes for dumped tables
--

--
-- Indexes for table `contact_messages`
--
ALTER TABLE `contact_messages`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `hostels`
--
ALTER TABLE `hostels`
  ADD PRIMARY KEY (`hostel_id`),
  ADD KEY `owner_id` (`owner_id`);

--
-- Indexes for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  ADD PRIMARY KEY (`id`),
  ADD KEY `inquiry_id` (`inquiry_id`);

--
-- Indexes for table `reservations`
--
ALTER TABLE `reservations`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `hostel_id` (`hostel_id`);

--
-- Indexes for table `reviews`
--
ALTER TABLE `reviews`
  ADD PRIMARY KEY (`id`),
  ADD KEY `hostel_id` (`hostel_id`),
  ADD KEY `user_id` (`user_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `email` (`email`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `contact_messages`
--
ALTER TABLE `contact_messages`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `hostels`
--
ALTER TABLE `hostels`
  MODIFY `hostel_id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT for table `hostel_images`
--
ALTER TABLE `hostel_images`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=35;

--
-- AUTO_INCREMENT for table `inquiries`
--
ALTER TABLE `inquiries`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=20;

--
-- AUTO_INCREMENT for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `reservations`
--
ALTER TABLE `reservations`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `reviews`
--
ALTER TABLE `reviews`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=16;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `hostels`
--
ALTER TABLE `hostels`
  ADD CONSTRAINT `hostels_ibfk_1` FOREIGN KEY (`owner_id`) REFERENCES `users` (`id`);

--
-- Constraints for table `hostel_images`
--
ALTER TABLE `hostel_images`
  ADD CONSTRAINT `hostel_images_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `inquiries`
--
ALTER TABLE `inquiries`
  ADD CONSTRAINT `inquiries_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `inquiries_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `inquiry_replies`
--
ALTER TABLE `inquiry_replies`
  ADD CONSTRAINT `inquiry_replies_ibfk_1` FOREIGN KEY (`inquiry_id`) REFERENCES `inquiries` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `reservations`
--
ALTER TABLE `reservations`
  ADD CONSTRAINT `reservations_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`),
  ADD CONSTRAINT `reservations_ibfk_2` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`);

--
-- Constraints for table `reviews`
--
ALTER TABLE `reviews`
  ADD CONSTRAINT `reviews_ibfk_1` FOREIGN KEY (`hostel_id`) REFERENCES `hostels` (`hostel_id`) ON DELETE CASCADE,
  ADD CONSTRAINT `reviews_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
