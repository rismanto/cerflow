-- MySQL dump 10.13  Distrib 8.0.46, for Linux (x86_64)
--
-- Host: localhost    Database: cer_flow_db
-- ------------------------------------------------------
-- Server version	8.0.46

/*!40101 SET @OLD_CHARACTER_SET_CLIENT=@@CHARACTER_SET_CLIENT */;
/*!40101 SET @OLD_CHARACTER_SET_RESULTS=@@CHARACTER_SET_RESULTS */;
/*!40101 SET @OLD_COLLATION_CONNECTION=@@COLLATION_CONNECTION */;
/*!50503 SET NAMES utf8mb4 */;
/*!40103 SET @OLD_TIME_ZONE=@@TIME_ZONE */;
/*!40103 SET TIME_ZONE='+00:00' */;
/*!40014 SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0 */;
/*!40014 SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0 */;
/*!40101 SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='NO_AUTO_VALUE_ON_ZERO' */;
/*!40111 SET @OLD_SQL_NOTES=@@SQL_NOTES, SQL_NOTES=0 */;

--
-- Table structure for table `cer_maps`
--

DROP TABLE IF EXISTS `cer_maps`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `cer_maps` (
  `id` int NOT NULL AUTO_INCREMENT,
  `title` varchar(100) NOT NULL,
  `allow_feedback` tinyint(1) NOT NULL DEFAULT '1',
  `reading_text` text,
  `allow_reading` tinyint(1) NOT NULL DEFAULT '1',
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=5 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `cer_maps`
--

LOCK TABLES `cer_maps` WRITE;
/*!40000 ALTER TABLE `cer_maps` DISABLE KEYS */;
INSERT INTO `cer_maps` VALUES (1,'Dampak Deforestasi pada Keanekaragaman Hayati',0,'Deforestasi mengurangi populasi burung.	Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.	Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.	\nEdit\nHapus\nSuhu udara lokal meningkat akibat penebangan hutan.	Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.	Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.	\nEdit\nHapus\nKualitas air sungai di sekitar hutan memburuk.	Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.	Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.',1,'2026-05-09 05:36:43'),(2,'Pemanasan Global dan Efek Rumah Kaca',1,'Lorem ipsum dolor.\n\nAdipiscing elit.\n\nCons.\n\nelit\n\nbanana.\n\nopen.\n\napple.',1,'2026-05-09 05:36:43'),(3,'Test Materi',1,'',1,'2026-05-13 05:53:33'),(4,'Efek Cahaya Terhadap Fotosintesis',1,'Cahaya matahari memiliki peran krusial dalam proses fotosintesis pada tumbuhan hijau. Berdasarkan pengamatan eksperimental, peningkatan intensitas cahaya terbukti mempercepat laju pembentukan gelembung oksigen pada tanaman air Hydrilla verticillata. Hal ini dikarenakan energi dari foton cahaya digunakan untuk memecah molekul air dalam reaksi terang, sehingga semakin banyak cahaya yang diserap, semakin banyak energi yang tersedia untuk memacu reaksi biokimia tersebut.',1,'2026-05-16 01:30:37');
/*!40000 ALTER TABLE `cer_maps` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `scores`
--

DROP TABLE IF EXISTS `scores`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `scores` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `map_id` int DEFAULT NULL,
  `session_id` int DEFAULT NULL,
  `score` decimal(5,2) DEFAULT NULL,
  `submitted_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `map_data` longtext,
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `map_id` (`map_id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `scores_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE,
  CONSTRAINT `scores_ibfk_3` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=16 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `scores`
--

LOCK TABLES `scores` WRITE;
/*!40000 ALTER TABLE `scores` DISABLE KEYS */;
INSERT INTO `scores` VALUES (9,2,3,24,66.67,'2026-05-13 10:15:26','{\"triplets\":[{\"id\":\"21\",\"map_id\":\"3\",\"claim\":\"aaaa\",\"evidence\":\"du\",\"reasoning\":\"duuuuhhhh\"},{\"id\":\"22\",\"map_id\":\"3\",\"claim\":\"aaaa\",\"evidence\":\"asdadasdsad\",\"reasoning\":\"safsdfasafsafafa\"},{\"id\":\"23\",\"map_id\":\"3\",\"claim\":\"llll\",\"evidence\":\"iiii\",\"reasoning\":\"kkk\"}],\"colOrder\":{\"claim\":[\"c-22\",\"c-21\",\"c-23\"],\"evidence\":[\"e-21\",\"e-23\",\"e-22\"],\"reasoning\":[\"r-21\",\"r-22\",\"r-23\"]},\"connections\":[{\"from\":\"e-21\",\"to\":\"r-21\"},{\"from\":\"c-21\",\"to\":\"e-21\"},{\"from\":\"c-22\",\"to\":\"e-22\"},{\"from\":\"e-23\",\"to\":\"r-22\"},{\"from\":\"c-23\",\"to\":\"e-23\"}]}'),(10,2,2,25,20.00,'2026-05-13 10:32:11','{\"triplets\":[{\"id\":\"14\",\"map_id\":\"2\",\"claim\":\"Suhu rata-rata bumi terus meningkat secara signifikan.\",\"evidence\":\"Data pengamatan satelit menunjukkan rekor suhu terpanas terjadi dalam satu dekade terakhir.\",\"reasoning\":\"Gas rumah kaca memerangkap energi panas dari matahari di atmosfer bumi yang seharusnya dipantulkan kembali ke luar angkasa.\"},{\"id\":\"15\",\"map_id\":\"2\",\"claim\":\"Lapisan es di kutub mencair jauh lebih cepat dari sebelumnya.\",\"evidence\":\"Volume lapisan es di Kutub Utara tercatat berkurang sekitar 13% setiap dekade.\",\"reasoning\":\"Peningkatan suhu global mempercepat titik lebur bongkahan es di area sekitar kutub.\"},{\"id\":\"16\",\"map_id\":\"2\",\"claim\":\"Permukaan air laut global terus mengalami kenaikan.\",\"evidence\":\"Pengukuran satelit mencatat kenaikan permukaan laut rata-rata 3.3 milimeter per tahun.\",\"reasoning\":\"Pencairan es di daratan luas (seperti Greenland) secara konsisten menambah volume total air di lautan.\"},{\"id\":\"17\",\"map_id\":\"2\",\"claim\":\"Cuaca ekstrem menjadi jauh lebih sering terjadi belakangan ini.\",\"evidence\":\"Laporan pengamat iklim menyebutkan frekuensi badai dan kekeringan panjang meningkat 2x lipat.\",\"reasoning\":\"Suhu yang lebih tinggi meningkatkan laju penguapan air, sehingga mengacaukan siklus hidrologi alami.\"},{\"id\":\"18\",\"map_id\":\"2\",\"claim\":\"Aktivitas manusia adalah penyebab utama tingginya gas rumah kaca.\",\"evidence\":\"Konsentrasi CO2 di atmosfer mencapai 420 ppm, level tertinggi dalam sejarah umat manusia.\",\"reasoning\":\"Pembakaran bahan bakar fosil (batu bara, minyak) oleh manusia melepaskan karbon yang sebelumnya tersimpan di dalam bumi.\"}],\"colOrder\":{\"claim\":[\"c-16\",\"c-18\",\"c-15\",\"c-14\",\"c-17\"],\"evidence\":[\"e-14\",\"e-16\",\"e-15\",\"e-17\",\"e-18\"],\"reasoning\":[\"r-16\",\"r-17\",\"r-18\",\"r-15\",\"r-14\"]},\"connections\":[{\"from\":\"c-16\",\"to\":\"e-14\"},{\"from\":\"e-16\",\"to\":\"r-16\"},{\"from\":\"c-15\",\"to\":\"e-15\"}]}'),(11,3,1,35,50.00,'2026-05-15 22:48:23','{\"triplets\":[{\"id\":24,\"map_id\":1,\"claim\":\"Deforestasi mengurangi populasi burung.\",\"evidence\":\"Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.\",\"reasoning\":\"Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.\"},{\"id\":25,\"map_id\":1,\"claim\":\"Suhu udara lokal meningkat akibat penebangan hutan.\",\"evidence\":\"Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.\",\"reasoning\":\"Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.\"},{\"id\":26,\"map_id\":1,\"claim\":\"Kualitas air sungai di sekitar hutan memburuk.\",\"evidence\":\"Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.\",\"reasoning\":\"Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.\"}],\"colOrder\":{\"claim\":[\"c-25\",\"c-26\",\"c-24\"],\"evidence\":[\"e-25\",\"e-24\",\"e-26\"],\"reasoning\":[\"r-26\",\"r-25\",\"r-24\"]},\"connections\":[{\"from\":\"e-26\",\"to\":\"r-24\"},{\"from\":\"e-25\",\"to\":\"r-25\"},{\"from\":\"c-25\",\"to\":\"e-25\"},{\"from\":\"c-26\",\"to\":\"e-26\"}]}'),(12,3,1,36,0.00,'2026-05-16 00:29:27','{\"triplets\":[{\"id\":24,\"map_id\":1,\"claim\":\"Deforestasi mengurangi populasi burung.\",\"evidence\":\"Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.\",\"reasoning\":\"Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.\"},{\"id\":25,\"map_id\":1,\"claim\":\"Suhu udara lokal meningkat akibat penebangan hutan.\",\"evidence\":\"Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.\",\"reasoning\":\"Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.\"},{\"id\":26,\"map_id\":1,\"claim\":\"Kualitas air sungai di sekitar hutan memburuk.\",\"evidence\":\"Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.\",\"reasoning\":\"Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.\"}],\"colOrder\":{\"claim\":[\"c-26\",\"c-24\",\"c-25\"],\"evidence\":[\"e-25\",\"e-24\",\"e-26\"],\"reasoning\":[\"r-25\",\"r-26\",\"r-24\"]},\"connections\":[{\"from\":\"c-26\",\"to\":\"e-25\"}]}'),(13,3,1,37,16.67,'2026-05-16 00:30:15','{\"triplets\":[{\"id\":24,\"map_id\":1,\"claim\":\"Deforestasi mengurangi populasi burung.\",\"evidence\":\"Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.\",\"reasoning\":\"Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.\"},{\"id\":25,\"map_id\":1,\"claim\":\"Suhu udara lokal meningkat akibat penebangan hutan.\",\"evidence\":\"Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.\",\"reasoning\":\"Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.\"},{\"id\":26,\"map_id\":1,\"claim\":\"Kualitas air sungai di sekitar hutan memburuk.\",\"evidence\":\"Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.\",\"reasoning\":\"Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.\"}],\"colOrder\":{\"claim\":[\"c-24\",\"c-25\",\"c-26\"],\"evidence\":[\"e-25\",\"e-26\",\"e-24\"],\"reasoning\":[\"r-24\",\"r-26\",\"r-25\"]},\"connections\":[{\"from\":\"e-25\",\"to\":\"r-25\"}]}'),(14,3,1,38,0.00,'2026-05-16 00:30:56','{\"triplets\":[{\"id\":24,\"map_id\":1,\"claim\":\"Deforestasi mengurangi populasi burung.\",\"evidence\":\"Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.\",\"reasoning\":\"Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.\"},{\"id\":25,\"map_id\":1,\"claim\":\"Suhu udara lokal meningkat akibat penebangan hutan.\",\"evidence\":\"Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.\",\"reasoning\":\"Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.\"},{\"id\":26,\"map_id\":1,\"claim\":\"Kualitas air sungai di sekitar hutan memburuk.\",\"evidence\":\"Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.\",\"reasoning\":\"Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.\"}],\"colOrder\":{\"claim\":[\"c-24\",\"c-25\",\"c-26\"],\"evidence\":[\"e-25\",\"e-26\",\"e-24\"],\"reasoning\":[\"r-25\",\"r-24\",\"r-26\"]},\"connections\":[{\"from\":\"e-25\",\"to\":\"r-26\"}]}'),(15,3,1,39,16.67,'2026-05-16 00:33:20','{\"triplets\":[{\"id\":24,\"map_id\":1,\"claim\":\"Deforestasi mengurangi populasi burung.\",\"evidence\":\"Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.\",\"reasoning\":\"Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.\"},{\"id\":25,\"map_id\":1,\"claim\":\"Suhu udara lokal meningkat akibat penebangan hutan.\",\"evidence\":\"Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.\",\"reasoning\":\"Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.\"},{\"id\":26,\"map_id\":1,\"claim\":\"Kualitas air sungai di sekitar hutan memburuk.\",\"evidence\":\"Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.\",\"reasoning\":\"Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.\"}],\"colOrder\":{\"claim\":[\"c-25\",\"c-24\",\"c-26\"],\"evidence\":[\"e-25\",\"e-26\",\"e-24\"],\"reasoning\":[\"r-25\",\"r-26\",\"r-24\"]},\"connections\":[{\"from\":\"e-25\",\"to\":\"r-25\"},{\"from\":\"c-25\",\"to\":\"e-24\"}]}');
/*!40000 ALTER TABLE `scores` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `settings`
--

DROP TABLE IF EXISTS `settings`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `settings` (
  `setting_key` varchar(50) NOT NULL,
  `setting_value` text,
  `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`setting_key`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `settings`
--

LOCK TABLES `settings` WRITE;
/*!40000 ALTER TABLE `settings` DISABLE KEYS */;
INSERT INTO `settings` VALUES ('gemini_api_key','AIzaSyBmZ4SEPK-_Z2Uh0zA64oqaVUC4m0ypCBA','2026-05-16 01:14:59');
/*!40000 ALTER TABLE `settings` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `triplets`
--

DROP TABLE IF EXISTS `triplets`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `triplets` (
  `id` int NOT NULL AUTO_INCREMENT,
  `map_id` int DEFAULT NULL,
  `claim` text,
  `evidence` text,
  `reasoning` text,
  PRIMARY KEY (`id`),
  KEY `map_id` (`map_id`),
  CONSTRAINT `triplets_ibfk_1` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=28 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `triplets`
--

LOCK TABLES `triplets` WRITE;
/*!40000 ALTER TABLE `triplets` DISABLE KEYS */;
INSERT INTO `triplets` VALUES (14,2,'Suhu rata-rata bumi terus meningkat secara signifikan.','Data pengamatan satelit menunjukkan rekor suhu terpanas terjadi dalam satu dekade terakhir.','Gas rumah kaca memerangkap energi panas dari matahari di atmosfer bumi yang seharusnya dipantulkan kembali ke luar angkasa.'),(15,2,'Lapisan es di kutub mencair jauh lebih cepat dari sebelumnya.','Volume lapisan es di Kutub Utara tercatat berkurang sekitar 13% setiap dekade.','Peningkatan suhu global mempercepat titik lebur bongkahan es di area sekitar kutub.'),(16,2,'Permukaan air laut global terus mengalami kenaikan.','Pengukuran satelit mencatat kenaikan permukaan laut rata-rata 3.3 milimeter per tahun.','Pencairan es di daratan luas (seperti Greenland) secara konsisten menambah volume total air di lautan.'),(17,2,'Cuaca ekstrem menjadi jauh lebih sering terjadi belakangan ini.','Laporan pengamat iklim menyebutkan frekuensi badai dan kekeringan panjang meningkat 2x lipat.','Suhu yang lebih tinggi meningkatkan laju penguapan air, sehingga mengacaukan siklus hidrologi alami.'),(18,2,'Aktivitas manusia adalah penyebab utama tingginya gas rumah kaca.','Konsentrasi CO2 di atmosfer mencapai 420 ppm, level tertinggi dalam sejarah umat manusia.','Pembakaran bahan bakar fosil (batu bara, minyak) oleh manusia melepaskan karbon yang sebelumnya tersimpan di dalam bumi.'),(21,3,'aaaa','du','duuuuhhhh'),(22,3,'aaaa','asdadasdsad','safsdfasafsafafa'),(23,3,'llll','iiii','kkk'),(24,1,'Deforestasi mengurangi populasi burung.','Jumlah spesies burung di area hutan yang ditebang turun 40% dalam waktu satu tahun.','Burung kehilangan habitat untuk bersarang dan sumber makanan utamanya akibat pohon-pohon yang ditebang.'),(25,1,'Suhu udara lokal meningkat akibat penebangan hutan.','Data termometer menunjukkan kenaikan rata-rata 2??C di area yang baru saja ditebang.','Pohon menyerap panas dan memberikan keteduhan. Tanpa kanopi pohon, sinar matahari langsung memanaskan tanah.'),(26,1,'Kualitas air sungai di sekitar hutan memburuk.','Tingkat kekeruhan air sungai meningkat 3x lipat terutama saat hujan lebat.','Akar pohon berfungsi menahan tanah. Tanpa pohon, tanah mudah tergerus erosi dan masuk ke aliran sungai.'),(27,4,'Cahaya matahari memiliki peran krusial dalam proses fotosintesis pada tumbuhan hijau.','Peningkatan intensitas cahaya terbukti mempercepat laju pembentukan gelembung oksigen pada tanaman air Hydrilla verticillata.','Energi dari foton cahaya digunakan untuk memecah molekul air dalam reaksi terang, sehingga semakin banyak cahaya yang diserap, semakin banyak energi yang tersedia untuk memacu reaksi biokimia tersebut.');
/*!40000 ALTER TABLE `triplets` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_logs`
--

DROP TABLE IF EXISTS `user_logs`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_logs` (
  `id` int NOT NULL AUTO_INCREMENT,
  `session_id` int DEFAULT NULL,
  `action_type` varchar(50) DEFAULT NULL,
  `action_data` text,
  `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `session_id` (`session_id`),
  CONSTRAINT `user_logs_ibfk_1` FOREIGN KEY (`session_id`) REFERENCES `user_sessions` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=153 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_logs`
--

LOCK TABLES `user_logs` WRITE;
/*!40000 ALTER TABLE `user_logs` DISABLE KEYS */;
INSERT INTO `user_logs` VALUES (1,2,'connect','{\"from\":\"c-1\",\"to\":\"e-1\"}','2026-05-09 05:41:17'),(2,2,'connect','{\"from\":\"e-1\",\"to\":\"r-1\"}','2026-05-09 05:41:24'),(3,2,'connect','{\"from\":\"c-2\",\"to\":\"e-2\"}','2026-05-09 05:41:26'),(4,2,'connect','{\"from\":\"e-2\",\"to\":\"r-2\"}','2026-05-09 05:41:27'),(5,2,'auto_arrange','[]','2026-05-09 05:43:01'),(6,1,'connect','{\"from\":\"e-1\",\"to\":\"r-0\"}','2026-05-09 05:43:33'),(7,2,'auto_arrange','[]','2026-05-09 11:18:12'),(8,2,'move','{\"card\":\"r-1\",\"from\":0,\"to\":2}','2026-05-09 11:18:15'),(9,2,'auto_arrange','[]','2026-05-09 11:18:16'),(10,3,'connect','{\"from\":\"e-1\",\"to\":\"r-0\"}','2026-05-09 11:18:26'),(11,3,'connect','{\"from\":\"e-2\",\"to\":\"r-2\"}','2026-05-09 11:18:27'),(12,3,'connect','{\"from\":\"c-2\",\"to\":\"e-2\"}','2026-05-09 11:18:29'),(13,4,'move','{\"card\":\"r-0\",\"from\":0,\"to\":1}','2026-05-09 11:19:00'),(14,4,'connect','{\"from\":\"e-1\",\"to\":\"r-0\"}','2026-05-09 11:19:04'),(15,4,'auto_arrange','[]','2026-05-09 11:19:05'),(16,5,'connect','{\"from\":\"e-0\",\"to\":\"r-2\"}','2026-05-09 11:19:20'),(17,5,'connect','{\"from\":\"e-1\",\"to\":\"r-0\"}','2026-05-09 11:19:21'),(18,5,'move','{\"card\":\"r-0\",\"from\":3,\"to\":2}','2026-05-09 11:19:22'),(19,5,'auto_arrange','[]','2026-05-09 11:19:23'),(20,5,'connect','{\"from\":\"c-2\",\"to\":\"e-0\"}','2026-05-09 11:19:25'),(21,5,'auto_arrange','[]','2026-05-09 11:19:26'),(22,5,'move','{\"card\":\"e-0\",\"from\":0,\"to\":2}','2026-05-09 11:19:28'),(23,5,'auto_arrange','[]','2026-05-09 11:19:29'),(24,6,'connect','{\"from\":\"c-3\",\"to\":\"e-0\"}','2026-05-09 16:03:15'),(25,7,'connect','{\"from\":\"c-2\",\"to\":\"e-2\"}','2026-05-09 16:09:26'),(26,7,'connect','{\"from\":\"e-2\",\"to\":\"r-2\"}','2026-05-09 16:09:39'),(27,7,'connect','{\"from\":\"c-1\",\"to\":\"e-1\"}','2026-05-09 16:09:48'),(28,7,'connect','{\"from\":\"e-1\",\"to\":\"r-1\"}','2026-05-09 16:09:57'),(29,9,'connect','{\"from\":\"c-3\",\"to\":\"e-4\"}','2026-05-09 16:24:52'),(30,9,'disconnect','{\"from\":\"c-3\",\"to\":\"e-4\"}','2026-05-09 16:25:15'),(31,9,'connect','{\"from\":\"c-3\",\"to\":\"e-0\"}','2026-05-09 16:25:15'),(32,11,'connect','{\"from\":\"c-1\",\"to\":\"e-1\"}','2026-05-09 16:25:35'),(33,11,'connect','{\"from\":\"e-1\",\"to\":\"r-1\"}','2026-05-09 16:25:43'),(34,11,'connect','{\"from\":\"c-0\",\"to\":\"e-2\"}','2026-05-09 16:25:49'),(35,11,'connect','{\"from\":\"e-2\",\"to\":\"r-0\"}','2026-05-09 16:25:56'),(36,12,'move','{\"card\":\"r-1\",\"from\":0,\"to\":1}','2026-05-09 17:08:35'),(37,12,'move','{\"card\":\"r-0\",\"from\":0,\"to\":1}','2026-05-09 17:08:37'),(38,12,'move','{\"card\":\"r-2\",\"from\":2,\"to\":1}','2026-05-09 17:08:39'),(39,17,'connect','{\"from\":\"e-2\",\"to\":\"r-1\"}','2026-05-09 17:23:02'),(40,18,'connect','{\"from\":\"c-3\",\"to\":\"e-0\"}','2026-05-09 17:27:48'),(41,21,'connect','{\"from\":\"c-0\",\"to\":\"e-1\"}','2026-05-13 09:33:19'),(42,21,'connect','{\"from\":\"e-1\",\"to\":\"r-1\"}','2026-05-13 09:33:21'),(43,21,'feedback','{\"active\":true}','2026-05-13 09:33:22'),(44,21,'disconnect','{\"from\":\"c-0\",\"to\":\"e-1\"}','2026-05-13 09:33:29'),(45,21,'connect','{\"from\":\"c-0\",\"to\":\"e-2\"}','2026-05-13 09:33:29'),(46,21,'feedback','{\"active\":true}','2026-05-13 09:33:30'),(47,21,'disconnect','{\"from\":\"c-0\",\"to\":\"e-2\"}','2026-05-13 09:33:35'),(48,21,'connect','{\"from\":\"c-0\",\"to\":\"e-0\"}','2026-05-13 09:33:35'),(49,21,'feedback','{\"active\":true}','2026-05-13 09:33:38'),(50,21,'connect','{\"from\":\"e-0\",\"to\":\"r-0\"}','2026-05-13 09:33:44'),(51,21,'feedback','{\"active\":true}','2026-05-13 09:33:45'),(52,21,'connect','{\"from\":\"e-2\",\"to\":\"r-2\"}','2026-05-13 09:33:50'),(53,21,'feedback','{\"active\":true}','2026-05-13 09:33:50'),(54,21,'disconnect','{\"from\":\"e-2\",\"to\":\"r-2\"}','2026-05-13 09:33:53'),(55,21,'connect','{\"from\":\"c-1\",\"to\":\"e-2\"}','2026-05-13 09:34:00'),(56,21,'feedback','{\"active\":true}','2026-05-13 09:34:02'),(57,21,'connect','{\"from\":\"c-2\",\"to\":\"e-1\"}','2026-05-13 09:34:07'),(58,21,'feedback','{\"active\":true}','2026-05-13 09:34:08'),(59,22,'connect','{\"from\":\"c-3\",\"to\":\"e-3\"}','2026-05-13 09:46:56'),(60,22,'connect','{\"from\":\"c-1\",\"to\":\"e-1\"}','2026-05-13 09:46:58'),(61,22,'move','{\"card\":\"e-3\",\"from\":0,\"to\":3}','2026-05-13 09:46:59'),(62,22,'connect','{\"from\":\"c-2\",\"to\":\"e-2\"}','2026-05-13 09:47:02'),(63,22,'connect','{\"from\":\"e-1\",\"to\":\"r-3\"}','2026-05-13 09:47:04'),(64,22,'connect','{\"from\":\"e-3\",\"to\":\"r-0\"}','2026-05-13 09:47:06'),(65,22,'feedback','{\"active\":true}','2026-05-13 09:47:06'),(66,23,'connect','{\"from\":\"c-21\",\"to\":\"e-21\"}','2026-05-13 09:57:32'),(67,23,'connect','{\"from\":\"e-21\",\"to\":\"r-21\"}','2026-05-13 09:57:33'),(68,23,'feedback','{\"active\":true}','2026-05-13 09:57:34'),(69,23,'connect','{\"from\":\"c-23\",\"to\":\"e-22\"}','2026-05-13 09:57:37'),(70,23,'feedback','{\"active\":true}','2026-05-13 09:57:38'),(71,23,'connect','{\"from\":\"e-23\",\"to\":\"r-23\"}','2026-05-13 09:57:40'),(72,23,'feedback','{\"active\":true}','2026-05-13 09:57:41'),(73,23,'connect','{\"from\":\"e-22\",\"to\":\"r-23\"}','2026-05-13 09:57:46'),(74,23,'disconnect','{\"from\":\"e-23\",\"to\":\"r-23\"}','2026-05-13 09:57:46'),(75,23,'feedback','{\"active\":true}','2026-05-13 09:57:47'),(76,24,'connect','{\"from\":\"c-22\",\"to\":\"e-22\"}','2026-05-13 10:14:48'),(77,24,'connect','{\"from\":\"c-22\",\"to\":\"e-21\"}','2026-05-13 10:14:56'),(78,24,'disconnect','{\"from\":\"c-22\",\"to\":\"e-22\"}','2026-05-13 10:14:56'),(79,24,'connect','{\"from\":\"e-21\",\"to\":\"r-21\"}','2026-05-13 10:14:58'),(80,24,'feedback','{\"active\":true}','2026-05-13 10:14:59'),(81,24,'disconnect','{\"from\":\"c-22\",\"to\":\"e-21\"}','2026-05-13 10:15:08'),(82,24,'connect','{\"from\":\"c-21\",\"to\":\"e-21\"}','2026-05-13 10:15:08'),(83,24,'feedback','{\"active\":true}','2026-05-13 10:15:10'),(84,24,'connect','{\"from\":\"c-22\",\"to\":\"e-22\"}','2026-05-13 10:15:12'),(85,24,'connect','{\"from\":\"e-23\",\"to\":\"r-23\"}','2026-05-13 10:15:15'),(86,24,'feedback','{\"active\":true}','2026-05-13 10:15:16'),(87,24,'disconnect','{\"from\":\"e-23\",\"to\":\"r-23\"}','2026-05-13 10:15:18'),(88,24,'connect','{\"from\":\"e-23\",\"to\":\"r-22\"}','2026-05-13 10:15:18'),(89,24,'feedback','{\"active\":true}','2026-05-13 10:15:19'),(90,24,'connect','{\"from\":\"c-23\",\"to\":\"e-23\"}','2026-05-13 10:15:22'),(91,24,'feedback','{\"active\":true}','2026-05-13 10:15:23'),(92,25,'connect','{\"from\":\"c-16\",\"to\":\"e-14\"}','2026-05-13 10:31:33'),(93,25,'connect','{\"from\":\"e-16\",\"to\":\"r-16\"}','2026-05-13 10:31:40'),(94,25,'auto_arrange','[]','2026-05-13 10:31:43'),(95,25,'feedback','{\"active\":true}','2026-05-13 10:31:45'),(96,25,'connect','{\"from\":\"c-18\",\"to\":\"e-15\"}','2026-05-13 10:31:48'),(97,25,'feedback','{\"active\":true}','2026-05-13 10:31:49'),(98,25,'connect','{\"from\":\"c-15\",\"to\":\"e-18\"}','2026-05-13 10:31:52'),(99,25,'feedback','{\"active\":true}','2026-05-13 10:31:53'),(100,25,'connect','{\"from\":\"c-15\",\"to\":\"e-16\"}','2026-05-13 10:31:56'),(101,25,'disconnect','{\"from\":\"c-15\",\"to\":\"e-18\"}','2026-05-13 10:31:56'),(102,25,'feedback','{\"active\":true}','2026-05-13 10:31:57'),(103,25,'connect','{\"from\":\"c-15\",\"to\":\"e-17\"}','2026-05-13 10:31:59'),(104,25,'disconnect','{\"from\":\"c-15\",\"to\":\"e-16\"}','2026-05-13 10:31:59'),(105,25,'feedback','{\"active\":true}','2026-05-13 10:32:00'),(106,25,'disconnect','{\"from\":\"c-18\",\"to\":\"e-15\"}','2026-05-13 10:32:03'),(107,25,'disconnect','{\"from\":\"c-15\",\"to\":\"e-17\"}','2026-05-13 10:32:03'),(108,25,'connect','{\"from\":\"c-15\",\"to\":\"e-15\"}','2026-05-13 10:32:03'),(109,25,'feedback','{\"active\":true}','2026-05-13 10:32:04'),(110,25,'move','{\"card\":\"r-16\",\"from\":0,\"to\":1}','2026-05-13 10:32:14'),(111,25,'move','{\"card\":\"r-16\",\"from\":1,\"to\":2}','2026-05-13 10:32:15'),(112,25,'auto_arrange','[]','2026-05-13 10:32:16'),(113,25,'move','{\"card\":\"e-15\",\"from\":1,\"to\":0}','2026-05-13 10:32:22'),(114,25,'move','{\"card\":\"e-14\",\"from\":1,\"to\":3}','2026-05-13 10:32:23'),(115,25,'move','{\"card\":\"e-18\",\"from\":4,\"to\":1}','2026-05-13 10:32:24'),(116,25,'auto_arrange','[]','2026-05-13 10:32:25'),(117,25,'feedback','{\"active\":true}','2026-05-13 10:32:34'),(118,27,'view_reading','[]','2026-05-15 11:18:48'),(119,31,'view_reading','[]','2026-05-15 11:45:22'),(120,31,'view_reading','[]','2026-05-15 11:45:39'),(121,31,'view_reading','[]','2026-05-15 11:45:40'),(122,32,'view_reading','[]','2026-05-15 11:46:58'),(123,32,'view_reading','[]','2026-05-15 11:46:59'),(124,32,'view_reading','[]','2026-05-15 11:47:02'),(125,32,'view_reading','[]','2026-05-15 11:48:10'),(126,33,'view_reading','[]','2026-05-15 11:49:25'),(127,33,'view_reading','[]','2026-05-15 11:49:30'),(128,33,'view_reading','[]','2026-05-15 11:50:54'),(129,33,'view_reading','[]','2026-05-15 11:52:29'),(130,33,'view_reading','[]','2026-05-15 11:52:56'),(131,33,'view_reading','[]','2026-05-15 11:53:09'),(132,33,'view_reading','[]','2026-05-15 11:53:12'),(133,33,'view_reading','[]','2026-05-15 11:56:31'),(134,34,'view_reading','[]','2026-05-15 11:59:37'),(135,35,'view_reading','[]','2026-05-15 22:46:52'),(136,35,'connect','{\"from\":\"e-26\",\"to\":\"r-24\"}','2026-05-15 22:47:41'),(137,35,'move','{\"card\":\"r-24\",\"from\":1,\"to\":2}','2026-05-15 22:47:46'),(138,35,'move','{\"card\":\"r-25\",\"from\":0,\"to\":1}','2026-05-15 22:47:55'),(139,35,'connect','{\"from\":\"e-25\",\"to\":\"r-25\"}','2026-05-15 22:48:14'),(140,35,'connect','{\"from\":\"c-25\",\"to\":\"e-25\"}','2026-05-15 22:48:17'),(141,35,'connect','{\"from\":\"c-26\",\"to\":\"e-26\"}','2026-05-15 22:48:19'),(142,36,'connect','{\"from\":\"c-26\",\"to\":\"e-25\"}','2026-05-16 00:29:20'),(143,36,'auto_arrange','[]','2026-05-16 00:29:22'),(144,37,'connect','{\"from\":\"e-25\",\"to\":\"r-25\"}','2026-05-16 00:30:09'),(145,38,'connect','{\"from\":\"e-25\",\"to\":\"r-26\"}','2026-05-16 00:30:53'),(146,39,'connect','{\"from\":\"e-25\",\"to\":\"r-25\"}','2026-05-16 00:33:15'),(147,39,'connect','{\"from\":\"c-25\",\"to\":\"e-24\"}','2026-05-16 00:33:17'),(148,39,'feedback','{\"active\":true}','2026-05-16 00:33:36'),(149,39,'feedback','{\"active\":true}','2026-05-16 00:33:38'),(150,39,'auto_arrange','[]','2026-05-16 00:33:42'),(151,39,'view_reading','[]','2026-05-16 00:33:44'),(152,40,'view_reading','[]','2026-05-16 00:34:06');
/*!40000 ALTER TABLE `user_logs` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `user_sessions`
--

DROP TABLE IF EXISTS `user_sessions`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `user_sessions` (
  `id` int NOT NULL AUTO_INCREMENT,
  `user_id` int DEFAULT NULL,
  `map_id` int DEFAULT NULL,
  `start_time` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
  `is_submitted` tinyint(1) DEFAULT '0',
  PRIMARY KEY (`id`),
  KEY `user_id` (`user_id`),
  KEY `map_id` (`map_id`),
  CONSTRAINT `user_sessions_ibfk_1` FOREIGN KEY (`user_id`) REFERENCES `users` (`id`) ON DELETE CASCADE,
  CONSTRAINT `user_sessions_ibfk_2` FOREIGN KEY (`map_id`) REFERENCES `cer_maps` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB AUTO_INCREMENT=41 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `user_sessions`
--

LOCK TABLES `user_sessions` WRITE;
/*!40000 ALTER TABLE `user_sessions` DISABLE KEYS */;
INSERT INTO `user_sessions` VALUES (1,2,2,'2026-05-09 05:37:17',1),(2,2,2,'2026-05-09 05:39:44',0),(3,2,1,'2026-05-09 11:18:24',0),(4,2,1,'2026-05-09 11:18:58',0),(5,2,2,'2026-05-09 11:19:17',1),(6,2,2,'2026-05-09 16:03:07',1),(7,2,1,'2026-05-09 16:09:19',1),(8,2,1,'2026-05-09 16:10:20',0),(9,2,2,'2026-05-09 16:21:36',0),(10,2,2,'2026-05-09 16:25:28',0),(11,2,1,'2026-05-09 16:25:30',1),(12,2,1,'2026-05-09 17:08:34',0),(13,2,1,'2026-05-09 17:09:17',0),(14,2,1,'2026-05-09 17:09:38',0),(15,2,1,'2026-05-09 17:10:03',0),(16,2,2,'2026-05-09 17:21:35',0),(17,2,1,'2026-05-09 17:22:46',0),(18,2,2,'2026-05-09 17:27:41',0),(19,2,2,'2026-05-12 01:39:07',0),(20,2,1,'2026-05-12 01:39:11',0),(21,2,3,'2026-05-13 09:33:06',1),(22,2,2,'2026-05-13 09:46:53',1),(23,2,3,'2026-05-13 09:57:24',1),(24,2,3,'2026-05-13 10:14:44',1),(25,2,2,'2026-05-13 10:31:31',1),(26,3,2,'2026-05-15 10:36:42',0),(27,3,2,'2026-05-15 11:18:45',0),(28,3,1,'2026-05-15 11:19:09',0),(29,3,2,'2026-05-15 11:19:15',0),(30,3,1,'2026-05-15 11:45:18',0),(31,3,2,'2026-05-15 11:45:21',0),(32,3,2,'2026-05-15 11:46:57',0),(33,3,2,'2026-05-15 11:49:24',0),(34,3,2,'2026-05-15 11:59:35',0),(35,3,1,'2026-05-15 22:46:41',1),(36,3,1,'2026-05-15 22:49:07',1),(37,3,1,'2026-05-16 00:30:06',1),(38,3,1,'2026-05-16 00:30:50',1),(39,3,1,'2026-05-16 00:33:13',1),(40,3,2,'2026-05-16 00:34:04',0);
/*!40000 ALTER TABLE `user_sessions` ENABLE KEYS */;
UNLOCK TABLES;

--
-- Table structure for table `users`
--

DROP TABLE IF EXISTS `users`;
/*!40101 SET @saved_cs_client     = @@character_set_client */;
/*!50503 SET character_set_client = utf8mb4 */;
CREATE TABLE `users` (
  `id` int NOT NULL AUTO_INCREMENT,
  `username` varchar(50) NOT NULL,
  `namalengkap` varchar(100) NOT NULL,
  `password` varchar(255) NOT NULL,
  `role` enum('guru','siswa') NOT NULL,
  `gemini_api_key` text DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`)
) ENGINE=InnoDB AUTO_INCREMENT=6 DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_0900_ai_ci;
/*!40101 SET character_set_client = @saved_cs_client */;

--
-- Dumping data for table `users`
--

LOCK TABLES `users` WRITE;
/*!40000 ALTER TABLE `users` DISABLE KEYS */;
INSERT INTO `users` VALUES (1,'admin','Administrator','$2y$10$2p7GZJpvIqCGg/feguG6/OkdDZReaUTeY1cRTUoKnRzSC2R65DjJe','guru',NULL),(2,'siswa','Siswa Percobaan','$2y$10$8J1C.I9wG/6Re2UzzGOHcO6hf0y2.4PszLEXAtaMVQqiI442x0QEK','siswa',NULL),(3,'siswa1','Siswa 1','$2y$10$dK3Brtg8eAEUGaUWoZIJeujS/BNXRtHY4.ie.1EaX8Clil7wDlkjq','siswa',NULL),(4,'siswa2','Siswa 2','$2y$10$8tTAbZJt40YvPSyvzzCjD.VaKHXgy/iuZTxQGaKhVXS1mYIoQYcD.','siswa',NULL),(5,'siswa3','Siswa 3','$2y$10$Y56xgFJYSl.3OgUSqAcY1OLVfxbhSpN0gs5plIw12qytYZ3.uScWq','siswa',NULL);
/*!40000 ALTER TABLE `users` ENABLE KEYS */;
UNLOCK TABLES;
/*!40103 SET TIME_ZONE=@OLD_TIME_ZONE */;

/*!40101 SET SQL_MODE=@OLD_SQL_MODE */;
/*!40014 SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS */;
/*!40014 SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS */;
/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
/*!40111 SET SQL_NOTES=@OLD_SQL_NOTES */;

-- Dump completed on 2026-05-16  1:31:39
