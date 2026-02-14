-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Waktu pembuatan: 27 Jan 2026 pada 14.31
-- Versi server: 10.4.32-MariaDB
-- Versi PHP: 8.2.12

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `camping`
--

-- --------------------------------------------------------

--
-- Struktur dari tabel `bookings`
--

CREATE TABLE `bookings` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `booking_date` date NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `quantity` int(11) DEFAULT 1,
  `total_price` decimal(10,2) NOT NULL,
  `status` enum('pending','confirmed','completed','cancelled') DEFAULT 'pending',
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `bookings`
--

INSERT INTO `bookings` (`id`, `user_id`, `item_id`, `booking_date`, `start_date`, `end_date`, `quantity`, `total_price`, `status`, `notes`, `created_at`, `updated_at`) VALUES
(1, 2, 1, '2025-11-26', '2025-11-26', '2025-11-29', 1, 600000.00, 'cancelled', 'coba coba', '2025-11-26 03:54:08', '2025-11-26 04:01:46'),
(2, 2, 2, '2025-11-26', '2025-11-27', '2025-12-02', 1, 300000.00, 'cancelled', '', '2025-11-26 03:54:40', '2025-11-26 03:55:07'),
(3, 2, 1, '2025-11-26', '2025-11-28', '2025-12-06', 1, 1350000.00, 'completed', '', '2025-11-26 04:33:26', '2025-11-26 04:38:03'),
(4, 2, 2, '2025-11-26', '2025-11-28', '2025-11-30', 1, 150000.00, 'cancelled', '', '2025-11-26 04:35:29', '2025-11-26 04:40:49'),
(5, 2, 1, '2025-11-26', '2025-11-27', '2025-11-29', 2, 900000.00, 'cancelled', 'coba', '2025-11-26 04:40:05', '2025-11-26 04:45:31'),
(6, 2, 1, '2025-11-26', '2025-11-29', '2025-12-02', 3, 1800000.00, 'completed', '', '2025-11-26 04:45:51', '2025-11-28 11:29:56'),
(7, 3, 7, '2025-11-28', '2025-11-28', '2025-12-01', 1, 1000000.00, 'confirmed', 'booking ', '2025-11-28 12:08:20', '2025-11-28 14:40:45'),
(8, 3, 11, '2025-11-28', '2025-11-28', '2025-12-01', 1, 360000.00, 'pending', '', '2025-11-28 12:09:40', '2025-11-28 12:09:40'),
(9, 3, 9, '2025-11-28', '2025-11-29', '2025-11-30', 1, 90000.00, 'pending', '', '2025-11-28 15:24:39', '2025-11-28 15:24:39'),
(10, 3, 2, '2025-11-28', '2025-11-29', '2025-12-03', 3, 750000.00, 'confirmed', '', '2025-11-28 15:44:48', '2026-01-19 08:57:50'),
(11, 2, 8, '2026-01-19', '2026-01-21', '2026-01-29', 1, 360000.00, 'pending', '', '2026-01-19 08:58:27', '2026-01-19 08:58:27'),
(12, 2, 8, '2026-01-19', '2026-01-21', '2026-01-29', 1, 360000.00, 'pending', '', '2026-01-19 09:05:39', '2026-01-19 09:05:39'),
(13, 2, 8, '2026-01-19', '2026-01-21', '2026-01-29', 1, 360000.00, 'pending', '', '2026-01-19 09:11:49', '2026-01-19 09:11:49'),
(14, 5, 9, '2026-01-19', '2026-01-20', '2026-01-28', 1, 405000.00, 'pending', '', '2026-01-19 10:12:00', '2026-01-19 10:12:00'),
(15, 5, 9, '2026-01-19', '2026-01-20', '2026-01-29', 1, 450000.00, 'confirmed', '', '2026-01-19 10:24:50', '2026-01-20 02:58:49'),
(16, 5, 9, '2026-01-20', '2026-01-21', '2026-01-30', 1, 450000.00, 'confirmed', '', '2026-01-20 00:31:28', '2026-01-20 02:58:46'),
(17, 5, 11, '2026-01-26', '2026-01-29', '2026-01-31', 1, 270000.00, 'cancelled', '', '2026-01-26 06:34:40', '2026-01-26 06:36:50'),
(18, 5, 8, '2026-01-26', '2026-01-27', '2026-01-30', 1, 160000.00, 'pending', '', '2026-01-26 06:34:40', '2026-01-26 06:34:40'),
(19, 5, 10, '2026-01-26', '2026-01-30', '2026-02-06', 1, 960000.00, 'pending', '', '2026-01-26 06:37:51', '2026-01-26 06:37:51'),
(20, 5, 9, '2026-01-26', '2026-01-27', '2026-01-31', 1, 225000.00, 'pending', '', '2026-01-26 06:37:51', '2026-01-26 06:37:51'),
(21, 5, 9, '2026-01-26', '2026-01-31', '2026-02-26', 1, 1215000.00, 'pending', '', '2026-01-26 06:38:42', '2026-01-26 06:38:42'),
(22, 5, 8, '2026-01-26', '2026-01-30', '2026-01-31', 1, 80000.00, 'pending', '', '2026-01-26 06:38:42', '2026-01-26 06:38:42'),
(23, 5, 7, '2026-01-26', '2026-01-29', '2026-02-06', 1, 2250000.00, 'cancelled', '', '2026-01-26 06:46:43', '2026-01-26 08:15:14'),
(24, 5, 8, '2026-01-26', '2026-01-28', '2026-02-04', 1, 320000.00, 'pending', '', '2026-01-26 06:46:43', '2026-01-26 06:46:43'),
(25, 5, 9, '2026-01-26', '2026-01-27', '2026-01-29', 1, 135000.00, 'cancelled', '', '2026-01-26 08:15:53', '2026-01-26 08:19:31'),
(26, 5, 7, '2026-01-26', '2026-01-27', '2026-01-29', 1, 750000.00, 'pending', '', '2026-01-26 08:15:53', '2026-01-26 08:15:53'),
(27, 5, 9, '2026-01-26', '2026-01-27', '2026-01-28', 1, 90000.00, 'cancelled', '', '2026-01-26 08:19:59', '2026-01-26 08:20:21'),
(28, 5, 8, '2026-01-26', '2026-01-27', '2026-01-28', 1, 80000.00, 'pending', '', '2026-01-26 08:19:59', '2026-01-26 08:19:59'),
(29, 5, 9, '2026-01-26', '2026-01-27', '2026-01-29', 1, 135000.00, 'pending', '', '2026-01-26 08:26:45', '2026-01-26 08:26:45'),
(30, 5, 9, '2026-01-27', '2026-01-27', '2026-01-29', 1, 135000.00, 'pending', '', '2026-01-27 00:38:14', '2026-01-27 00:38:14'),
(31, 5, 8, '2026-01-27', '2026-01-28', '2026-01-30', 1, 120000.00, 'pending', '', '2026-01-27 00:46:51', '2026-01-27 00:46:51'),
(32, 5, 7, '2026-01-27', '2026-01-29', '2026-01-31', 1, 750000.00, 'pending', '', '2026-01-27 00:48:43', '2026-01-27 00:48:43'),
(33, 5, 8, '2026-01-27', '2026-01-30', '2026-01-31', 1, 80000.00, 'pending', '', '2026-01-27 00:50:08', '2026-01-27 00:50:08'),
(34, 5, 8, '2026-01-27', '2026-01-28', '2026-01-31', 1, 160000.00, 'pending', '', '2026-01-27 01:39:05', '2026-01-27 01:39:05'),
(35, 5, 7, '2026-01-27', '2026-01-28', '2026-01-31', 1, 1000000.00, 'pending', '', '2026-01-27 01:39:05', '2026-01-27 01:39:05'),
(36, 5, 12, '2026-01-27', '2026-01-28', '2026-01-31', 1, 280000.00, 'pending', '', '2026-01-27 02:45:46', '2026-01-27 02:45:46'),
(37, 5, 9, '2026-01-27', '2026-01-28', '2026-01-30', 1, 135000.00, 'pending', '', '2026-01-27 02:48:45', '2026-01-27 02:48:45'),
(38, 5, 16, '2026-01-27', '2026-01-28', '2026-01-31', 1, 260000.00, 'pending', '', '2026-01-27 02:51:05', '2026-01-27 02:51:05'),
(39, 5, 11, '2026-01-27', '2026-01-28', '2026-01-31', 1, 360000.00, 'pending', '', '2026-01-27 02:51:05', '2026-01-27 02:51:05'),
(40, 5, 10, '2026-01-27', '2026-01-28', '2026-01-31', 1, 570000.00, 'pending', 'Multiple items - see booking_items for details', '2026-01-27 03:00:08', '2026-01-27 03:00:08'),
(41, 5, 17, '2026-01-27', '2026-01-28', '2026-01-31', 1, 215000.00, 'pending', 'Multiple items - see booking_items for details', '2026-01-27 03:12:08', '2026-01-27 03:12:08'),
(42, 5, 16, '2026-01-27', '2026-01-28', '2026-01-31', 1, 305000.00, 'pending', 'Multiple items - see booking_items for details', '2026-01-27 03:50:07', '2026-01-27 03:50:07'),
(43, 5, 11, '2026-01-27', '2026-01-28', '2026-01-31', 1, 270000.00, 'confirmed', 'Multiple items - see booking_items for details', '2026-01-27 03:56:27', '2026-01-27 04:05:05'),
(46, 5, 11, '2026-01-27', '2026-01-28', '2026-01-31', 1, 360000.00, 'confirmed', 'ok', '2026-01-27 08:20:19', '2026-01-27 08:20:19'),
(47, 5, 15, '2026-01-27', '2026-01-28', '2026-01-30', 1, 165000.00, 'confirmed', 'ok', '2026-01-27 08:21:58', '2026-01-27 08:21:59'),
(48, 5, 11, '2026-01-27', '2026-01-28', '2026-02-06', 1, 900000.00, 'confirmed', '', '2026-01-27 08:27:09', '2026-01-27 08:27:09'),
(49, 3, 7, '2026-01-27', '2026-01-28', '2026-01-30', 1, 750000.00, 'confirmed', '', '2026-01-27 08:27:56', '2026-01-27 08:27:56'),
(50, 5, 2, '2026-01-27', '2026-01-28', '2026-01-30', 1, 150000.00, 'confirmed', '', '2026-01-27 09:01:40', '2026-01-27 09:01:40'),
(51, 5, 17, '2026-01-27', '2026-01-28', '2026-01-31', 1, 100000.00, 'confirmed', '', '2026-01-27 13:04:42', '2026-01-27 13:04:42'),
(52, 3, 17, '2026-01-27', '2026-01-28', '2026-01-31', 2, 220000.00, 'completed', '', '2026-01-27 13:05:28', '2026-01-27 13:06:24'),
(53, 5, 15, '2026-01-27', '2026-01-28', '2026-01-31', 1, 360000.00, 'completed', 'Multiple items - see booking_items for details', '2026-01-27 13:09:26', '2026-01-27 13:10:47');

-- --------------------------------------------------------

--
-- Struktur dari tabel `booking_items`
--

CREATE TABLE `booking_items` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `price_per_day` decimal(10,2) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `subtotal` decimal(10,2) NOT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `booking_items`
--

INSERT INTO `booking_items` (`id`, `booking_id`, `item_id`, `quantity`, `price_per_day`, `start_date`, `end_date`, `subtotal`, `notes`, `created_at`) VALUES
(1, 40, 10, 1, 120000.00, '2026-01-28', '2026-01-31', 360000.00, '', '2026-01-27 03:00:08'),
(2, 40, 12, 1, 70000.00, '2026-01-28', '2026-01-31', 210000.00, '', '2026-01-27 03:00:08'),
(3, 41, 17, 1, 25000.00, '2026-01-28', '2026-01-31', 75000.00, '', '2026-01-27 03:12:08'),
(4, 41, 12, 1, 70000.00, '2026-01-28', '2026-01-30', 140000.00, '', '2026-01-27 03:12:08'),
(5, 42, 16, 1, 65000.00, '2026-01-28', '2026-01-31', 195000.00, '', '2026-01-27 03:50:07'),
(6, 42, 15, 1, 55000.00, '2026-01-28', '2026-01-30', 110000.00, '', '2026-01-27 03:50:07'),
(7, 43, 11, 1, 90000.00, '2026-01-28', '2026-01-31', 270000.00, '', '2026-01-27 03:56:27'),
(9, 46, 11, 1, 90000.00, '0000-00-00', '0000-00-00', 360000.00, NULL, '2026-01-27 08:20:19'),
(10, 47, 15, 1, 55000.00, '0000-00-00', '0000-00-00', 165000.00, NULL, '2026-01-27 08:21:59'),
(11, 48, 11, 1, 90000.00, '0000-00-00', '0000-00-00', 900000.00, NULL, '2026-01-27 08:27:09'),
(12, 49, 7, 1, 250000.00, '0000-00-00', '0000-00-00', 750000.00, NULL, '2026-01-27 08:27:56'),
(13, 50, 2, 1, 50000.00, '0000-00-00', '0000-00-00', 150000.00, NULL, '2026-01-27 09:01:40'),
(14, 51, 17, 1, 25000.00, '0000-00-00', '0000-00-00', 100000.00, NULL, '2026-01-27 13:04:42'),
(15, 52, 17, 1, 25000.00, '0000-00-00', '0000-00-00', 100000.00, NULL, '2026-01-27 13:05:28'),
(16, 52, 18, 1, 30000.00, '0000-00-00', '0000-00-00', 120000.00, NULL, '2026-01-27 13:05:28'),
(17, 53, 15, 1, 55000.00, '2026-01-28', '2026-01-31', 165000.00, '', '2026-01-27 13:09:26'),
(18, 53, 16, 1, 65000.00, '2026-01-28', '2026-01-31', 195000.00, '', '2026-01-27 13:09:26');

-- --------------------------------------------------------

--
-- Struktur dari tabel `cart`
--

CREATE TABLE `cart` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `start_date` date NOT NULL,
  `end_date` date NOT NULL,
  `quantity` int(11) NOT NULL DEFAULT 1,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores shopping cart items for users';

-- --------------------------------------------------------

--
-- Struktur dari tabel `inventory_history`
--

CREATE TABLE `inventory_history` (
  `id` int(11) NOT NULL,
  `item_id` int(11) NOT NULL,
  `quantity_before` int(11) DEFAULT NULL,
  `quantity_after` int(11) DEFAULT NULL,
  `action` varchar(50) DEFAULT NULL,
  `reference_id` int(11) DEFAULT NULL,
  `notes` text DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `invoices`
--

CREATE TABLE `invoices` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `invoice_number` varchar(50) DEFAULT NULL,
  `user_id` int(11) NOT NULL,
  `subtotal` decimal(10,2) DEFAULT NULL,
  `tax` decimal(10,2) DEFAULT 0.00,
  `discount` decimal(10,2) DEFAULT 0.00,
  `total` decimal(10,2) NOT NULL,
  `status` enum('draft','sent','paid','cancelled') DEFAULT 'draft',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- --------------------------------------------------------

--
-- Struktur dari tabel `items`
--

CREATE TABLE `items` (
  `id` int(11) NOT NULL,
  `name` varchar(100) NOT NULL,
  `description` text DEFAULT NULL,
  `category` varchar(50) DEFAULT NULL,
  `price_per_day` decimal(10,2) NOT NULL,
  `quantity_available` int(11) DEFAULT 1,
  `quantity_total` int(11) DEFAULT 1,
  `image_url` varchar(255) DEFAULT NULL,
  `status` enum('available','unavailable') DEFAULT 'available',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `items`
--

INSERT INTO `items` (`id`, `name`, `description`, `category`, `price_per_day`, `quantity_available`, `quantity_total`, `image_url`, `status`, `created_at`, `updated_at`) VALUES
(1, 'Tenda Dome 4 Orang', 'Tenda berkualitas tinggi dengan kapasitas 4 orang, Tahan air', 'Tenda', 150000.00, 2, 5, 'assets/uploads/69269619725bd_1764136473.jpeg', 'available', '2025-11-26 03:53:48', '2025-11-26 10:31:42'),
(2, 'Sleeping Bag Premium', 'Sleeping bag premium dengan temperature rating hingga -10°C', 'Perlengkapan Tidur', 50000.00, 6, 10, 'assets/uploads/692696dc5bfd7_1764136668.jpg', 'available', '2025-11-26 03:53:48', '2026-01-27 09:01:40'),
(3, 'Matras Camping', 'Matras anti air dengan ketebalan 10cm, nyaman digunakan', 'Perlengkapan Tidur', 30000.00, 8, 8, 'assets/uploads/6926970b2ac82_1764136715.jpg', 'available', '2025-11-26 03:53:48', '2025-11-26 05:58:35'),
(4, 'Daypack 50L', 'Tas daypack berkapasitas 50 liter untuk hiking', 'Tas & Ransel', 75000.00, 6, 6, 'assets/uploads/6926972e80c09_1764136750.jpg', 'available', '2025-11-26 03:53:48', '2025-11-26 05:59:10'),
(5, 'Kompor Camping', 'Kompor portable untuk memasak di camping', 'Peralatan Masak', 40000.00, 4, 4, 'assets/uploads/6926976a36a5b_1764136810.jpg', 'available', '2025-11-26 03:53:48', '2025-11-26 06:00:10'),
(6, 'Lampu LED Camping', 'Lampu LED rechargeable dengan 3 mode cahaya', 'Penerangan', 60000.00, 7, 7, 'assets/uploads/6926979948fa7_1764136857.jpg', 'available', '2025-11-26 03:53:48', '2025-11-26 06:00:57'),
(7, 'Tenda Tunnel 6 Orang', 'Tenda besar berbentuk tunnel untuk kelompok hingga 6 orang, tahan angin dan hujan', 'Tenda', 250000.00, 1, 5, 'assets/uploads/692986bfe0069_1764329151.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 08:27:56'),
(8, 'Sleeping Bag Musim Panas', 'Sleeping bag ringan untuk suhu hangat, ideal untuk kemping musim kemarau', 'Perlengkapan Tidur', 40000.00, 2, 12, 'assets/uploads/692986ce5ec07_1764329166.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 01:39:05'),
(9, 'Matras Inflatable', 'Matras kempa yang bisa dikempiskan dan dikemas kecil, mudah dibawa', 'Perlengkapan Tidur', 45000.00, 3, 10, 'assets/uploads/692986eba8ca0_1764329195.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 02:48:45'),
(10, 'Tas Carrier 70L', 'Tas carrier besar dengan sistem sirkulasi udara, kapasitas 70 liter untuk ekspedisi', 'Tas & Ransel', 120000.00, 3, 4, 'assets/uploads/692986f7c3b0f_1764329207.jpg', 'available', '2025-11-28 11:18:05', '2026-01-26 06:37:51'),
(11, 'Set Peralatan Masak Outdoor', 'Set lengkap peralatan masak portable: panci, wajan, mangkuk, dan sendok', 'Peralatan Masak', 90000.00, 1, 5, 'assets/uploads/69298704eb6ba_1764329220.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 08:27:09'),
(12, 'Lentera Gantung Solar', 'Lentera gantung berbahan tahan lama dengan panel surya untuk pengisian daya', 'Penerangan', 70000.00, 5, 6, 'assets/uploads/6929871d630c0_1764329245.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 02:45:46'),
(13, 'Tenda Ultralight 2 Orang', 'Tenda ringan dan mudah dipasang untuk dua orang, ideal untuk backpacking', 'Tenda', 180000.00, 4, 4, 'assets/uploads/6929872bc5d0e_1764329259.jpg', 'available', '2025-11-28 11:18:05', '2025-11-28 11:27:39'),
(14, 'Kantong Tidur Anak', 'Sleeping bag lucu dan hangat untuk anak-anak usia 5–10 tahun', 'Perlengkapan Tidur', 35000.00, 8, 8, 'assets/uploads/692987391e635_1764329273.jpg', 'available', '2025-11-28 11:18:05', '2025-11-28 11:27:53'),
(15, 'Hydration Bladder 3L', 'Tempat air isi ulang kapasitas 3 liter untuk tas hiking', 'Aksesoris Hiking', 55000.00, 7, 9, 'assets/uploads/692987468e2e6_1764329286.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 13:09:46'),
(16, 'Senter Headlamp LED', 'Senter kepala dengan cahaya terang dan baterai tahan lama', 'Penerangan', 65000.00, 9, 10, 'assets/uploads/692987524b169_1764329298.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 02:51:05'),
(17, 'Pompa Inflator Manual', 'Pompa portable untuk mengisi matras atau peralatan kemping lainnya', 'Aksesoris Hiking', 25000.00, 5, 7, 'assets/uploads/69298761017b8_1764329313.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 13:05:28'),
(18, 'Fire Starter Kit', 'Alat pembuat api darurat yang tahan air dan angin', 'Survival', 30000.00, 14, 15, 'assets/uploads/6929876e5d182_1764329326.jpg', 'available', '2025-11-28 11:18:05', '2026-01-27 13:05:28'),
(19, 'Water Filter Portable', 'Filter air portable untuk menyaring air sungai atau danau agar aman diminum', 'Survival', 200000.00, 2, 2, 'assets/uploads/6929877ad4443_1764329338.jpg', 'available', '2025-11-28 11:18:05', '2025-11-28 11:28:58'),
(20, 'Tikar Camping Lipat', 'Tikar lipat anti air dan tahan lama untuk duduk atau alas tenda', 'Aksesoris Hiking', 20000.00, 20, 20, 'assets/uploads/69298786d054c_1764329350.jpg', 'available', '2025-11-28 11:18:05', '2025-11-28 11:29:10'),
(21, 'Cooking Stove Gas Mini', 'Kompor gas mini dengan desain ringkas dan efisien', 'Peralatan Masak', 50000.00, 6, 6, 'assets/uploads/69298798c8e2a_1764329368.jpg', 'available', '2025-11-28 11:18:05', '2025-11-28 11:29:28');

-- --------------------------------------------------------

--
-- Struktur dari tabel `ktp_verification_logs`
--

CREATE TABLE `ktp_verification_logs` (
  `id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `admin_id` int(11) NOT NULL,
  `action` enum('approved','rejected') NOT NULL,
  `notes` text DEFAULT NULL,
  `ktp_file` varchar(255) DEFAULT NULL,
  `ip_address` varchar(45) DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci COMMENT='Log of all KTP verification actions';

-- --------------------------------------------------------

--
-- Struktur dari tabel `payments`
--

CREATE TABLE `payments` (
  `id` int(11) NOT NULL,
  `booking_id` int(11) NOT NULL,
  `user_id` int(11) NOT NULL,
  `amount` decimal(10,2) NOT NULL,
  `payment_method` varchar(50) DEFAULT NULL,
  `transaction_id` varchar(100) DEFAULT NULL,
  `status` enum('pending','completed','failed') DEFAULT 'pending',
  `payment_date` timestamp NULL DEFAULT current_timestamp(),
  `created_at` timestamp NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci COMMENT='Stores payment information for rentals';

--
-- Dumping data untuk tabel `payments`
--

INSERT INTO `payments` (`id`, `booking_id`, `user_id`, `amount`, `payment_method`, `transaction_id`, `status`, `payment_date`, `created_at`) VALUES
(1, 1, 2, 600000.00, 'cash', 'TRX-20251126105415-2792', 'failed', '2025-11-26 03:54:15', '2025-11-26 03:54:15'),
(2, 2, 2, 300000.00, 'e_wallet', 'TRX-20251126105448-6484', 'failed', '2025-11-26 03:54:48', '2025-11-26 03:54:48'),
(3, 3, 2, 1350000.00, 'cash', 'TRX-20251126113333-9572', 'completed', '2025-11-26 04:33:33', '2025-11-26 04:33:33'),
(4, 4, 2, 150000.00, 'cash', 'TRX-20251126113710-3468', 'failed', '2025-11-26 04:37:10', '2025-11-26 04:37:10'),
(5, 5, 2, 900000.00, 'e_wallet', 'TRX-20251126114013-6916', 'failed', '2025-11-26 04:40:13', '2025-11-26 04:40:13'),
(6, 6, 2, 1800000.00, 'e_wallet', 'TRX-20251126114556-7548', 'completed', '2025-11-26 04:45:56', '2025-11-26 04:45:56'),
(7, 7, 3, 1000000.00, 'e_wallet', 'TRX-20251128190831-8587', 'completed', '2025-11-28 12:08:31', '2025-11-28 12:08:31'),
(8, 8, 3, 360000.00, 'e_wallet', 'TRX-20251128190948-9719', 'pending', '2025-11-28 12:09:48', '2025-11-28 12:09:48'),
(9, 9, 3, 90000.00, 'e_wallet', 'TRX-20251128222556-7048', 'pending', '2025-11-28 15:25:56', '2025-11-28 15:25:56'),
(10, 10, 3, 750000.00, 'e_wallet', 'TRX-20251128224456-7602', 'completed', '2025-11-28 15:44:56', '2025-11-28 15:44:56'),
(11, 15, 5, 450000.00, 'bank_transfer', 'TRX-20260119173205-3713', 'completed', '2026-01-19 10:32:05', '2026-01-19 10:32:05'),
(12, 16, 5, 450000.00, 'cash', 'TRX-20260120095511-3398', 'completed', '2026-01-20 02:55:11', '2026-01-20 02:55:11'),
(13, 17, 5, 270000.00, 'cash', 'TRX-20260126133447-3297', 'completed', '2026-01-26 06:34:47', '2026-01-26 06:34:47'),
(14, 31, 5, 120000.00, 'bank_transfer', 'TRX-20260127074722-5848', 'pending', '2026-01-27 00:47:22', '2026-01-27 00:47:22'),
(15, 32, 5, 750000.00, 'cash', 'TRX-20260127074902-4331', 'pending', '2026-01-27 00:49:02', '2026-01-27 00:49:02'),
(16, 18, 5, 160000.00, 'cash', 'TRX-20260127081817-2816', 'pending', '2026-01-27 01:18:17', '2026-01-27 01:18:17'),
(17, 34, 5, 160000.00, 'cash', 'TRX-20260127084041-5298', 'pending', '2026-01-27 01:40:41', '2026-01-27 01:40:41'),
(18, 40, 5, 570000.00, 'cash', 'TRX-20260127100135-2221', 'pending', '2026-01-27 03:01:35', '2026-01-27 03:01:35'),
(19, 41, 5, 215000.00, 'cash', 'TRX-20260127101226-2045', 'pending', '2026-01-27 03:12:26', '2026-01-27 03:12:26'),
(20, 42, 5, 305000.00, 'e_wallet', 'TRX-20260127105222-8781', 'pending', '2026-01-27 03:52:22', '2026-01-27 03:52:22'),
(21, 43, 5, 270000.00, 'e_wallet', 'TRX-20260127110505-9941', 'completed', '2026-01-27 04:05:05', '2026-01-27 04:05:05'),
(23, 53, 5, 360000.00, 'bank_transfer', 'TRX-20260127200946-9943', 'completed', '2026-01-27 13:09:46', '2026-01-27 13:09:46');

-- --------------------------------------------------------

--
-- Struktur dari tabel `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `email` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `full_name` varchar(100) DEFAULT NULL,
  `phone` varchar(15) DEFAULT NULL,
  `ktp_number` varchar(16) DEFAULT NULL,
  `ktp_image` varchar(255) DEFAULT NULL,
  `ktp_uploaded_at` datetime DEFAULT NULL,
  `ktp_verified` tinyint(1) DEFAULT 0,
  `ktp_verified_by` int(11) DEFAULT NULL,
  `ktp_verification_notes` text DEFAULT NULL COMMENT 'Admin notes on verification',
  `ktp_verified_at` datetime DEFAULT NULL,
  `address` text DEFAULT NULL,
  `role` enum('user','admin') DEFAULT 'user',
  `status` enum('active','inactive') DEFAULT 'active',
  `created_at` timestamp NULL DEFAULT current_timestamp(),
  `updated_at` timestamp NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

--
-- Dumping data untuk tabel `users`
--

INSERT INTO `users` (`id`, `username`, `email`, `password`, `full_name`, `phone`, `ktp_number`, `ktp_image`, `ktp_uploaded_at`, `ktp_verified`, `ktp_verified_by`, `ktp_verification_notes`, `ktp_verified_at`, `address`, `role`, `status`, `created_at`, `updated_at`) VALUES
(1, 'admin', 'admin@campingrental.com', '$2a$12$B8aq6zZ95ulGrgdp7BQc2.VxY7Cxn/6bL2xj6TK2TwYkEY1/t4G3W', 'Administrator', '085889660387', NULL, 'assets/uploads/ktp/ktp_user_1_1769520323.jpg', NULL, 0, NULL, NULL, NULL, '', 'admin', 'active', '2025-11-25 22:37:39', '2026-01-27 13:26:09'),
(2, 'ical', 'faisal@gmail.com', '$2y$10$br6wU50xuOgrYCGudcVW4eJe3Yz/9IGUwZq0OGfhTct2VU0LgKj7C', 'Faisal Fikri', '082234567890', NULL, 'assets/uploads/ktp/ktp_user_2_1769520149.jpg', NULL, 0, NULL, NULL, NULL, '', 'user', 'active', '2025-11-25 22:37:39', '2026-01-27 13:22:29'),
(3, 'zan', 'zan@gmail.com', '$2y$10$oO.wGiX.hVKN5c7VuLsebesnV8FolnHuTSsCSaUmmnLMfb2ygoBEC', 'Arya Fauzan', '085889660387', NULL, 'assets/uploads/ktp/ktp_user_3_1769520103.jpg', NULL, 0, NULL, NULL, NULL, '', 'user', 'active', '2025-11-28 12:01:51', '2026-01-27 13:21:43'),
(4, 'acaa', 'cantika@gmail.com', '$2y$10$NDtK5zpiKMfvxN3QdcEJAu2CJQFuEGSZIY9NkBm.8uHssPjLlHV9y', 'Dede Cantika', '087729521818', NULL, NULL, NULL, 0, NULL, NULL, NULL, '', 'user', 'active', '2025-11-28 12:05:59', '2026-01-27 13:23:27'),
(5, 'rena', 'abc@gmail.com', '$2y$10$IUg990rSlA/UPKh1OWvLgOW4tAOAQAHzsrW2Pp2WEqk/yzCSc6K.K', 'rena', '123', NULL, 'assets/uploads/ktp/ktp_user_5_1769519285.jpg', NULL, 0, NULL, NULL, NULL, '', 'user', 'active', '2026-01-19 10:11:23', '2026-01-27 13:08:05');

--
-- Indexes for dumped tables
--

--
-- Indeks untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_status` (`status`),
  ADD KEY `idx_start_date` (`start_date`);

--
-- Indeks untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `booking_id` (`booking_id`),
  ADD KEY `item_id` (`item_id`);

--
-- Indeks untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_dates` (`start_date`,`end_date`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_item_id` (`item_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD UNIQUE KEY `invoice_number` (`invoice_number`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `items`
--
ALTER TABLE `items`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_category` (`category`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `ktp_verification_logs`
--
ALTER TABLE `ktp_verification_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_admin_id` (`admin_id`),
  ADD KEY `idx_created_at` (`created_at`);

--
-- Indeks untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `booking_id` (`booking_id`),
  ADD UNIQUE KEY `transaction_id` (`transaction_id`),
  ADD KEY `idx_user_id` (`user_id`),
  ADD KEY `idx_status` (`status`);

--
-- Indeks untuk tabel `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`),
  ADD UNIQUE KEY `email` (`email`),
  ADD KEY `idx_username` (`username`),
  ADD KEY `idx_email` (`email`),
  ADD KEY `idx_role` (`role`),
  ADD KEY `fk_ktp_verified_by` (`ktp_verified_by`),
  ADD KEY `idx_ktp_verified` (`ktp_verified`),
  ADD KEY `idx_ktp_verified_at` (`ktp_verified_at`),
  ADD KEY `idx_ktp_image` (`ktp_image`(50));

--
-- AUTO_INCREMENT untuk tabel yang dibuang
--

--
-- AUTO_INCREMENT untuk tabel `bookings`
--
ALTER TABLE `bookings`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=54;

--
-- AUTO_INCREMENT untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=19;

--
-- AUTO_INCREMENT untuk tabel `cart`
--
ALTER TABLE `cart`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=14;

--
-- AUTO_INCREMENT untuk tabel `inventory_history`
--
ALTER TABLE `inventory_history`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `invoices`
--
ALTER TABLE `invoices`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `items`
--
ALTER TABLE `items`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT untuk tabel `ktp_verification_logs`
--
ALTER TABLE `ktp_verification_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT untuk tabel `payments`
--
ALTER TABLE `payments`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=24;

--
-- AUTO_INCREMENT untuk tabel `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- Ketidakleluasaan untuk tabel pelimpahan (Dumped Tables)
--

--
-- Ketidakleluasaan untuk tabel `bookings`
--
ALTER TABLE `bookings`
  ADD CONSTRAINT `bookings_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `bookings_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `booking_items`
--
ALTER TABLE `booking_items`
  ADD CONSTRAINT `booking_items_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `booking_items_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`);

--
-- Ketidakleluasaan untuk tabel `cart`
--
ALTER TABLE `cart`
  ADD CONSTRAINT `cart_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `cart_ibfk_2` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `inventory_history`
--
ALTER TABLE `inventory_history`
  ADD CONSTRAINT `inventory_history_ibfk_1` FOREIGN KEY (`item_id`) REFERENCES `items` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `invoices`
--
ALTER TABLE `invoices`
  ADD CONSTRAINT `invoices_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `invoices_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `ktp_verification_logs`
--
ALTER TABLE `ktp_verification_logs`
  ADD CONSTRAINT `ktp_verification_logs_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `ktp_verification_logs_ibfk_2` FOREIGN KEY (`admin_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `payments`
--
ALTER TABLE `payments`
  ADD CONSTRAINT `payments_ibfk_1` FOREIGN KEY (`booking_id`) REFERENCES `bookings` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `payments_ibfk_2` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE;

--
-- Ketidakleluasaan untuk tabel `users`
--
ALTER TABLE `users`
  ADD CONSTRAINT `fk_ktp_verified_by` FOREIGN KEY (`ktp_verified_by`) REFERENCES `users` (`id`) ON DELETE SET NULL ON UPDATE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
