-- phpMyAdmin SQL Dump
-- version 5.1.0
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: May 15, 2026 at 07:39 AM
-- Server version: 10.4.18-MariaDB
-- PHP Version: 7.3.28

SET SQL_MODE = "NO_AUTO_VALUE_ON_ZERO";
START TRANSACTION;
SET time_zone = "+00:00";


/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!40101 SET NAMES utf8mb4 */;

--
-- Database: `cer_flow_db`
--

-- --------------------------------------------------------

--
-- Table structure for table `cer_maps`
--

CREATE TABLE `cer_maps` (
  `id` int(11) NOT NULL,
  `title` varchar(100) NOT NULL,
  `allow_feedback` tinyint(1) NOT NULL DEFAULT 1,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `cer_maps`
--

INSERT INTO `cer_maps` (`id`, `title`, `allow_feedback`, `created_at`) VALUES
(1, 'Dampak Deforestasi pada Keanekaragaman Hayati', 0, '2026-05-09 05:36:43'),
(2, 'Pemanasan Global dan Efek Rumah Kaca', 1, '2026-05-09 05:36:43'),
(3, 'Aduduh', 1, '2026-05-13 05:53:33');

-- --------------------------------------------------------

--
-- Table structure for table `scores`
--

CREATE TABLE `scores` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `map_id` int(11) DEFAULT NULL,
  `session_id` int(11) DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT current_timestamp(),
  `map_data` longtext DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `scores`
--

INSERT INTO `scores` (`id`, `user_id`, `map_id`, `session_id`, `score`, `submitted_at`, `map_data`) VALUES
(9, 2, 3, 24, '66.67', '2026-05-13 10:15:26', '{\"triplets\":[{\"id\":\"21\",\"map_id\":\"3\",\"claim\":\"aaaa\",\"evidence\":\"du\",\"reasoning\":\"duuuuhhhh\"},{\"id\":\"22\",\"map_id\":\"3\",\"claim\":\"aaaa\",\"evidence\":\"asdadasdsad\",\"reasoning\":\"safsdfasafsafafa\"},{\"id\":\"23\",\"map_id\":\"3\",\"claim\":\"llll\",\"evidence\":\"iiii\",\"reasoning\":\"kkk\"}],\"colOrder\":{\"claim\":[\"c-22\",\"c-21\",\"c-23\"],\"evidence\":[\"e-21\",\"e-23\",\"e-22\"],\"reasoning\":[\"r-21\",\"r-22\",\"r-23\"]},\"connections\":[{\"from\":\"e-21\",\"to\":\"r-21\"},{\"from\":\"c-21\",\"to\":\"e-21\"},{\"from\":\"c-22\",\"to\":\"e-22\"},{\"from\":\"e-23\",\"to\":\"r-22\"},{\"from\":\"c-23\",\"to\":\"e-23\"}]}'),
(10, 2, 2, 25, '20.00', '2026-05-13 10:32:11', '{\"triplets\":[{\"id\":\"14\",\"map_id\":\"2\",\"claim\":\"Suhu rata-rata bumi terus meningkat secara signifikan.\",\"evidence\":\"Data pengamatan satelit menunjukkan rekor suhu terpanas terjadi dalam satu dekade terakhir.\",\"reasoning\":\"Gas rumah kaca memerangkap energi panas dari matahari di atmosfer bumi yang seharusnya dipantulkan kembali ke luar angkasa.\"},{\"id\":\"15\",\"map_id\":\"2\",\"claim\":\"Lapisan es di kutub mencair jauh lebih cepat dari sebelumnya.\",\"evidence\":\"Volume lapisan es di Kutub Utara tercatat berkurang sekitar 13% setiap dekade.\",\"reasoning\":\"Peningkatan suhu global mempercepat titik lebur bongkahan es di area sekitar kutub.\"},{\"id\":\"16\",\"map_id\":\"2\",\"claim\":\"Permukaan air laut global terus mengalami kenaikan.\",\"evidence\":\"Pengukuran satelit mencatat kenaikan permukaan laut rata-rata 3.3 milimeter per tahun.\",\"reasoning\":\"Pencairan es di daratan luas (seperti Greenland) secara konsisten menambah volume total air di lautan.\"},{\"id\":\"17\",\"map_id\":\"2\",\"claim\":\"Cuaca ekstrem menjadi jauh lebih sering terjadi belakangan ini.\",\"evidence\":\"Laporan pengamat iklim menyebutkan frekuensi badai dan kekeringan panjang meningkat 2x lipat.\",\"reasoning\":\"Suhu yang lebih tinggi meningkatkan laju penguapan air, sehingga mengacaukan siklus hidrologi alami.\"},{\"id\":\"18\",\"map_id\":\"2\",\"claim\":\"Aktivitas manusia adalah penyebab utama tingginya gas rumah kaca.\",\"evidence\":\"Konsentrasi CO2 di atmosfer mencapai 420 ppm, level tertinggi dalam sejarah umat manusia.\",\"reasoning\":\"Pembakaran bahan bakar fosil (batu bara, minyak) oleh manusia melepaskan karbon yang sebelumnya tersimpan di dalam bumi.\"}],\"colOrder\":{\"claim\":[\"c-16\",\"c-18\",\"c-15\",\"c-14\",\"c-17\"],\"evidence\":[\"e-14\",\"e-16\",\"e-15\",\"e-17\",\"e-18\"],\"reasoning\":[\"r-16\",\"r-17\",\"r-18\",\"r-15\",\"r-14\"]},\"connections\":[{\"from\":\"c-16\",\"to\":\"e-14\"},{\"from\":\"e-16\",\"to\":\"r-16\"},{\"from\":\"c-15\",\"to\":\"e-15\"}]}');

-- --------------------------------------------------------

--
-- Table structure for table `settings`
--

CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text DEFAULT NULL,
  `updated_at` timestamp NOT NULL DEFAULT current_timestamp() ON UPDATE current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

-- --------------------------------------------------------

--
-- Table structure for table `triplets`
--

CREATE TABLE `triplets` (
  `id` int(11) NOT NULL,
  `map_id` int(11) DEFAULT NULL,
  `claim` text DEFAULT NULL,
  `evidence` text DEFAULT NULL,
  `reasoning` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `triplets`
--

INSERT INTO `triplets` (`id`, `map_id`, `claim`, `evidence`, `reasoning`) VALUES
(14, 2, 'Suhu rata-rata bumi terus meningkat secara signifikan.', 'Data pengamatan satelit menunjukkan rekor suhu terpanas terjadi dalam satu dekade terakhir.', 'Gas rumah kaca memerangkap energi panas dari matahari di atmosfer bumi yang seharusnya dipantulkan kembali ke luar angkasa.'),
(15, 2, 'Lapisan es di kutub mencair jauh lebih cepat dari sebelumnya.', 'Volume lapisan es di Kutub Utara tercatat berkurang sekitar 13% setiap dekade.', 'Peningkatan suhu global mempercepat titik lebur bongkahan es di area sekitar kutub.'),
(16, 2, 'Permukaan air laut global terus mengalami kenaikan.', 'Pengukuran satelit mencatat kenaikan permukaan laut rata-rata 3.3 milimeter per tahun.', 'Pencairan es di daratan luas (seperti Greenland) secara konsisten menambah volume total air di lautan.'),
(17, 2, 'Cuaca ekstrem menjadi jauh lebih sering terjadi belakangan ini.', 'Laporan pengamat iklim menyebutkan frekuensi badai dan kekeringan panjang meningkat 2x lipat.', 'Suhu yang lebih tinggi meningkatkan laju penguapan air, sehingga mengacaukan siklus hidrologi alami.'),
(18, 2, 'Aktivitas manusia adalah penyebab utama tingginya gas rumah kaca.', 'Konsentrasi CO2 di atmosfer mencapai 420 ppm, level tertinggi dalam sejarah umat manusia.', 'Pembakaran bahan bakar fosil (batu bara, minyak) oleh manusia melepaskan karbon yang sebelumnya tersimpan di dalam bumi.'),
(21, 3, 'aaaa', 'du', 'duuuuhhhh'),
(22, 3, 'aaaa', 'asdadasdsad', 'safsdfasafsafafa'),
(23, 3, 'llll', 'iiii', 'kkk'),
(24, 1, 'Deforestasi mengurangi populasi burung.', 'Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.', 'Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.'),
(25, 1, 'Suhu udara lokal meningkat akibat penebangan hutan.', 'Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.', 'Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.'),
(26, 1, 'Kualitas air sungai di sekitar hutan memburuk.', 'Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.', 'Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.');

-- --------------------------------------------------------

--
-- Table structure for table `users`
--

CREATE TABLE `users` (
  `id` int(11) NOT NULL,
  `username` varchar(50) NOT NULL,
  `namalengkap` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guru','siswa') NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `users`
--

INSERT INTO `users` (`id`, `username`, `namalengkap`, `password`, `role`) VALUES
(1, 'admin', 'Administrator', '$2y$10$2p7GZJpvIqCGg/feguG6/OkdDZReaUTeY1cRTUoKnRzSC2R65DjJe', 'guru'),
(2, 'siswa', 'Siswa Percobaan', '$2y$10$8J1C.I9wG/6Re2UzzGOHcO6hf0y2.4PszLEXAtaMVQqiI442x0QEK', 'siswa'),
(3, 'siswa1', 'Siswa 1', '$2y$10$dK3Brtg8eAEUGaUWoZIJeujS/BNXRtHY4.ie.1EaX8Clil7wDlkjq', 'siswa'),
(4, 'siswa2', 'Siswa 2', '$2y$10$8tTAbZJt40YvPSyvzzCjD.VaKHXgy/iuZTxQGaKhVXS1mYIoQYcD.', 'siswa'),
(5, 'siswa3', 'Siswa 3', '$2y$10$Y56xgFJYSl.3OgUSqAcY1OLVfxbhSpN0gs5plIw12qytYZ3.uScWq', 'siswa');

-- --------------------------------------------------------

--
-- Table structure for table `user_logs`
--

CREATE TABLE `user_logs` (
  `id` int(11) NOT NULL,
  `session_id` int(11) DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `action_data` text DEFAULT NULL,
  `created_at` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_logs`
--

INSERT INTO `user_logs` (`id`, `session_id`, `action_type`, `action_data`, `created_at`) VALUES
(1, 2, 'connect', '{\"from\":\"c-1\",\"to\":\"e-1\"}', '2026-05-09 05:41:17'),
(2, 2, 'connect', '{\"from\":\"e-1\",\"to\":\"r-1\"}', '2026-05-09 05:41:24'),
(3, 2, 'connect', '{\"from\":\"c-2\",\"to\":\"e-2\"}', '2026-05-09 05:41:26'),
(4, 2, 'connect', '{\"from\":\"e-2\",\"to\":\"r-2\"}', '2026-05-09 05:41:27'),
(5, 2, 'auto_arrange', '[]', '2026-05-09 05:43:01'),
(6, 1, 'connect', '{\"from\":\"e-1\",\"to\":\"r-0\"}', '2026-05-09 05:43:33'),
(7, 2, 'auto_arrange', '[]', '2026-05-09 11:18:12'),
(8, 2, 'move', '{\"card\":\"r-1\",\"from\":0,\"to\":2}', '2026-05-09 11:18:15'),
(9, 2, 'auto_arrange', '[]', '2026-05-09 11:18:16'),
(10, 3, 'connect', '{\"from\":\"e-1\",\"to\":\"r-0\"}', '2026-05-09 11:18:26'),
(11, 3, 'connect', '{\"from\":\"e-2\",\"to\":\"r-2\"}', '2026-05-09 11:18:27'),
(12, 3, 'connect', '{\"from\":\"c-2\",\"to\":\"e-2\"}', '2026-05-09 11:18:29'),
(13, 4, 'move', '{\"card\":\"r-0\",\"from\":0,\"to\":1}', '2026-05-09 11:19:00'),
(14, 4, 'connect', '{\"from\":\"e-1\",\"to\":\"r-0\"}', '2026-05-09 11:19:04'),
(15, 4, 'auto_arrange', '[]', '2026-05-09 11:19:05'),
(16, 5, 'connect', '{\"from\":\"e-0\",\"to\":\"r-2\"}', '2026-05-09 11:19:20'),
(17, 5, 'connect', '{\"from\":\"e-1\",\"to\":\"r-0\"}', '2026-05-09 11:19:21'),
(18, 5, 'move', '{\"card\":\"r-0\",\"from\":3,\"to\":2}', '2026-05-09 11:19:22'),
(19, 5, 'auto_arrange', '[]', '2026-05-09 11:19:23'),
(20, 5, 'connect', '{\"from\":\"c-2\",\"to\":\"e-0\"}', '2026-05-09 11:19:25'),
(21, 5, 'auto_arrange', '[]', '2026-05-09 11:19:26'),
(22, 5, 'move', '{\"card\":\"e-0\",\"from\":0,\"to\":2}', '2026-05-09 11:19:28'),
(23, 5, 'auto_arrange', '[]', '2026-05-09 11:19:29'),
(24, 6, 'connect', '{\"from\":\"c-3\",\"to\":\"e-0\"}', '2026-05-09 16:03:15'),
(25, 7, 'connect', '{\"from\":\"c-2\",\"to\":\"e-2\"}', '2026-05-09 16:09:26'),
(26, 7, 'connect', '{\"from\":\"e-2\",\"to\":\"r-2\"}', '2026-05-09 16:09:39'),
(27, 7, 'connect', '{\"from\":\"c-1\",\"to\":\"e-1\"}', '2026-05-09 16:09:48'),
(28, 7, 'connect', '{\"from\":\"e-1\",\"to\":\"r-1\"}', '2026-05-09 16:09:57'),
(29, 9, 'connect', '{\"from\":\"c-3\",\"to\":\"e-4\"}', '2026-05-09 16:24:52'),
(30, 9, 'disconnect', '{\"from\":\"c-3\",\"to\":\"e-4\"}', '2026-05-09 16:25:15'),
(31, 9, 'connect', '{\"from\":\"c-3\",\"to\":\"e-0\"}', '2026-05-09 16:25:15'),
(32, 11, 'connect', '{\"from\":\"c-1\",\"to\":\"e-1\"}', '2026-05-09 16:25:35'),
(33, 11, 'connect', '{\"from\":\"e-1\",\"to\":\"r-1\"}', '2026-05-09 16:25:43'),
(34, 11, 'connect', '{\"from\":\"c-0\",\"to\":\"e-2\"}', '2026-05-09 16:25:49'),
(35, 11, 'connect', '{\"from\":\"e-2\",\"to\":\"r-0\"}', '2026-05-09 16:25:56'),
(36, 12, 'move', '{\"card\":\"r-1\",\"from\":0,\"to\":1}', '2026-05-09 17:08:35'),
(37, 12, 'move', '{\"card\":\"r-0\",\"from\":0,\"to\":1}', '2026-05-09 17:08:37'),
(38, 12, 'move', '{\"card\":\"r-2\",\"from\":2,\"to\":1}', '2026-05-09 17:08:39'),
(39, 17, 'connect', '{\"from\":\"e-2\",\"to\":\"r-1\"}', '2026-05-09 17:23:02'),
(40, 18, 'connect', '{\"from\":\"c-3\",\"to\":\"e-0\"}', '2026-05-09 17:27:48'),
(41, 21, 'connect', '{\"from\":\"c-0\",\"to\":\"e-1\"}', '2026-05-13 09:33:19'),
(42, 21, 'connect', '{\"from\":\"e-1\",\"to\":\"r-1\"}', '2026-05-13 09:33:21'),
(43, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:33:22'),
(44, 21, 'disconnect', '{\"from\":\"c-0\",\"to\":\"e-1\"}', '2026-05-13 09:33:29'),
(45, 21, 'connect', '{\"from\":\"c-0\",\"to\":\"e-2\"}', '2026-05-13 09:33:29'),
(46, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:33:30'),
(47, 21, 'disconnect', '{\"from\":\"c-0\",\"to\":\"e-2\"}', '2026-05-13 09:33:35'),
(48, 21, 'connect', '{\"from\":\"c-0\",\"to\":\"e-0\"}', '2026-05-13 09:33:35'),
(49, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:33:38'),
(50, 21, 'connect', '{\"from\":\"e-0\",\"to\":\"r-0\"}', '2026-05-13 09:33:44'),
(51, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:33:45'),
(52, 21, 'connect', '{\"from\":\"e-2\",\"to\":\"r-2\"}', '2026-05-13 09:33:50'),
(53, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:33:50'),
(54, 21, 'disconnect', '{\"from\":\"e-2\",\"to\":\"r-2\"}', '2026-05-13 09:33:53'),
(55, 21, 'connect', '{\"from\":\"c-1\",\"to\":\"e-2\"}', '2026-05-13 09:34:00'),
(56, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:34:02'),
(57, 21, 'connect', '{\"from\":\"c-2\",\"to\":\"e-1\"}', '2026-05-13 09:34:07'),
(58, 21, 'feedback', '{\"active\":true}', '2026-05-13 09:34:08'),
(59, 22, 'connect', '{\"from\":\"c-3\",\"to\":\"e-3\"}', '2026-05-13 09:46:56'),
(60, 22, 'connect', '{\"from\":\"c-1\",\"to\":\"e-1\"}', '2026-05-13 09:46:58'),
(61, 22, 'move', '{\"card\":\"e-3\",\"from\":0,\"to\":3}', '2026-05-13 09:46:59'),
(62, 22, 'connect', '{\"from\":\"c-2\",\"to\":\"e-2\"}', '2026-05-13 09:47:02'),
(63, 22, 'connect', '{\"from\":\"e-1\",\"to\":\"r-3\"}', '2026-05-13 09:47:04'),
(64, 22, 'connect', '{\"from\":\"e-3\",\"to\":\"r-0\"}', '2026-05-13 09:47:06'),
(65, 22, 'feedback', '{\"active\":true}', '2026-05-13 09:47:06'),
(66, 23, 'connect', '{\"from\":\"c-21\",\"to\":\"e-21\"}', '2026-05-13 09:57:32'),
(67, 23, 'connect', '{\"from\":\"e-21\",\"to\":\"r-21\"}', '2026-05-13 09:57:33'),
(68, 23, 'feedback', '{\"active\":true}', '2026-05-13 09:57:34'),
(69, 23, 'connect', '{\"from\":\"c-23\",\"to\":\"e-22\"}', '2026-05-13 09:57:37'),
(70, 23, 'feedback', '{\"active\":true}', '2026-05-13 09:57:38'),
(71, 23, 'connect', '{\"from\":\"e-23\",\"to\":\"r-23\"}', '2026-05-13 09:57:40'),
(72, 23, 'feedback', '{\"active\":true}', '2026-05-13 09:57:41'),
(73, 23, 'connect', '{\"from\":\"e-22\",\"to\":\"r-23\"}', '2026-05-13 09:57:46'),
(74, 23, 'disconnect', '{\"from\":\"e-23\",\"to\":\"r-23\"}', '2026-05-13 09:57:46'),
(75, 23, 'feedback', '{\"active\":true}', '2026-05-13 09:57:47'),
(76, 24, 'connect', '{\"from\":\"c-22\",\"to\":\"e-22\"}', '2026-05-13 10:14:48'),
(77, 24, 'connect', '{\"from\":\"c-22\",\"to\":\"e-21\"}', '2026-05-13 10:14:56'),
(78, 24, 'disconnect', '{\"from\":\"c-22\",\"to\":\"e-22\"}', '2026-05-13 10:14:56'),
(79, 24, 'connect', '{\"from\":\"e-21\",\"to\":\"r-21\"}', '2026-05-13 10:14:58'),
(80, 24, 'feedback', '{\"active\":true}', '2026-05-13 10:14:59'),
(81, 24, 'disconnect', '{\"from\":\"c-22\",\"to\":\"e-21\"}', '2026-05-13 10:15:08'),
(82, 24, 'connect', '{\"from\":\"c-21\",\"to\":\"e-21\"}', '2026-05-13 10:15:08'),
(83, 24, 'feedback', '{\"active\":true}', '2026-05-13 10:15:10'),
(84, 24, 'connect', '{\"from\":\"c-22\",\"to\":\"e-22\"}', '2026-05-13 10:15:12'),
(85, 24, 'connect', '{\"from\":\"e-23\",\"to\":\"r-23\"}', '2026-05-13 10:15:15'),
(86, 24, 'feedback', '{\"active\":true}', '2026-05-13 10:15:16'),
(87, 24, 'disconnect', '{\"from\":\"e-23\",\"to\":\"r-23\"}', '2026-05-13 10:15:18'),
(88, 24, 'connect', '{\"from\":\"e-23\",\"to\":\"r-22\"}', '2026-05-13 10:15:18'),
(89, 24, 'feedback', '{\"active\":true}', '2026-05-13 10:15:19'),
(90, 24, 'connect', '{\"from\":\"c-23\",\"to\":\"e-23\"}', '2026-05-13 10:15:22'),
(91, 24, 'feedback', '{\"active\":true}', '2026-05-13 10:15:23'),
(92, 25, 'connect', '{\"from\":\"c-16\",\"to\":\"e-14\"}', '2026-05-13 10:31:33'),
(93, 25, 'connect', '{\"from\":\"e-16\",\"to\":\"r-16\"}', '2026-05-13 10:31:40'),
(94, 25, 'auto_arrange', '[]', '2026-05-13 10:31:43'),
(95, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:31:45'),
(96, 25, 'connect', '{\"from\":\"c-18\",\"to\":\"e-15\"}', '2026-05-13 10:31:48'),
(97, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:31:49'),
(98, 25, 'connect', '{\"from\":\"c-15\",\"to\":\"e-18\"}', '2026-05-13 10:31:52'),
(99, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:31:53'),
(100, 25, 'connect', '{\"from\":\"c-15\",\"to\":\"e-16\"}', '2026-05-13 10:31:56'),
(101, 25, 'disconnect', '{\"from\":\"c-15\",\"to\":\"e-18\"}', '2026-05-13 10:31:56'),
(102, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:31:57'),
(103, 25, 'connect', '{\"from\":\"c-15\",\"to\":\"e-17\"}', '2026-05-13 10:31:59'),
(104, 25, 'disconnect', '{\"from\":\"c-15\",\"to\":\"e-16\"}', '2026-05-13 10:31:59'),
(105, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:32:00'),
(106, 25, 'disconnect', '{\"from\":\"c-18\",\"to\":\"e-15\"}', '2026-05-13 10:32:03'),
(107, 25, 'disconnect', '{\"from\":\"c-15\",\"to\":\"e-17\"}', '2026-05-13 10:32:03'),
(108, 25, 'connect', '{\"from\":\"c-15\",\"to\":\"e-15\"}', '2026-05-13 10:32:03'),
(109, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:32:04'),
(110, 25, 'move', '{\"card\":\"r-16\",\"from\":0,\"to\":1}', '2026-05-13 10:32:14'),
(111, 25, 'move', '{\"card\":\"r-16\",\"from\":1,\"to\":2}', '2026-05-13 10:32:15'),
(112, 25, 'auto_arrange', '[]', '2026-05-13 10:32:16'),
(113, 25, 'move', '{\"card\":\"e-15\",\"from\":1,\"to\":0}', '2026-05-13 10:32:22'),
(114, 25, 'move', '{\"card\":\"e-14\",\"from\":1,\"to\":3}', '2026-05-13 10:32:23'),
(115, 25, 'move', '{\"card\":\"e-18\",\"from\":4,\"to\":1}', '2026-05-13 10:32:24'),
(116, 25, 'auto_arrange', '[]', '2026-05-13 10:32:25'),
(117, 25, 'feedback', '{\"active\":true}', '2026-05-13 10:32:34');

-- --------------------------------------------------------

--
-- Table structure for table `user_sessions`
--

CREATE TABLE `user_sessions` (
  `id` int(11) NOT NULL,
  `user_id` int(11) DEFAULT NULL,
  `map_id` int(11) DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT current_timestamp(),
  `is_submitted` tinyint(1) DEFAULT 0
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;

--
-- Dumping data for table `user_sessions`
--

INSERT INTO `user_sessions` (`id`, `user_id`, `map_id`, `start_time`, `is_submitted`) VALUES
(1, 2, 2, '2026-05-09 05:37:17', 1),
(2, 2, 2, '2026-05-09 05:39:44', 0),
(3, 2, 1, '2026-05-09 11:18:24', 0),
(4, 2, 1, '2026-05-09 11:18:58', 0),
(5, 2, 2, '2026-05-09 11:19:17', 1),
(6, 2, 2, '2026-05-09 16:03:07', 1),
(7, 2, 1, '2026-05-09 16:09:19', 1),
(8, 2, 1, '2026-05-09 16:10:20', 0),
(9, 2, 2, '2026-05-09 16:21:36', 0),
(10, 2, 2, '2026-05-09 16:25:28', 0),
(11, 2, 1, '2026-05-09 16:25:30', 1),
(12, 2, 1, '2026-05-09 17:08:34', 0),
(13, 2, 1, '2026-05-09 17:09:17', 0),
(14, 2, 1, '2026-05-09 17:09:38', 0),
(15, 2, 1, '2026-05-09 17:10:03', 0),
(16, 2, 2, '2026-05-09 17:21:35', 0),
(17, 2, 1, '2026-05-09 17:22:46', 0),
(18, 2, 2, '2026-05-09 17:27:41', 0),
(19, 2, 2, '2026-05-12 01:39:07', 0),
(20, 2, 1, '2026-05-12 01:39:11', 0),
(21, 2, 3, '2026-05-13 09:33:06', 1),
(22, 2, 2, '2026-05-13 09:46:53', 1),
(23, 2, 3, '2026-05-13 09:57:24', 1),
(24, 2, 3, '2026-05-13 10:14:44', 1),
(25, 2, 2, '2026-05-13 10:31:31', 1);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `cer_maps`
--
ALTER TABLE `cer_maps`
  ADD PRIMARY KEY (`id`);

--
-- Indexes for table `scores`
--
ALTER TABLE `scores`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `map_id` (`map_id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `settings`
--
ALTER TABLE `settings`
  ADD PRIMARY KEY (`setting_key`);

--
-- Indexes for table `triplets`
--
ALTER TABLE `triplets`
  ADD PRIMARY KEY (`id`),
  ADD KEY `map_id` (`map_id`);

--
-- Indexes for table `users`
--
ALTER TABLE `users`
  ADD PRIMARY KEY (`id`),
  ADD UNIQUE KEY `username` (`username`);

--
-- Indexes for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD PRIMARY KEY (`id`),
  ADD KEY `session_id` (`session_id`);

--
-- Indexes for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD PRIMARY KEY (`id`),
  ADD KEY `user_id` (`user_id`),
  ADD KEY `map_id` (`map_id`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `cer_maps`
--
ALTER TABLE `cer_maps`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `scores`
--
ALTER TABLE `scores`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `triplets`
--
ALTER TABLE `triplets`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=27;

--
-- AUTO_INCREMENT for table `users`
--
ALTER TABLE `users`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `user_logs`
--
ALTER TABLE `user_logs`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=118;

--
-- AUTO_INCREMENT for table `user_sessions`
--
ALTER TABLE `user_sessions`
  MODIFY `id` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=26;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `scores`
--
ALTER TABLE `scores`
  ADD CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `triplets`
--
ALTER TABLE `triplets`
  ADD CONSTRAINT `triplets_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_logs`
--
ALTER TABLE `user_logs`
  ADD CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE CASCADE;

--
-- Constraints for table `user_sessions`
--
ALTER TABLE `user_sessions`
  ADD CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  ADD CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
