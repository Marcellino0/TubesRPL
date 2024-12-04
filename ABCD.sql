-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 04, 2024 at 09:56 AM
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
-- Database: `poliklinikx`
--

-- --------------------------------------------------------

--
-- Table structure for table `administrator`
--

CREATE TABLE `administrator` (
  `ID_Admin` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `administrator`
--

INSERT INTO `administrator` (`ID_Admin`, `Username`, `Password`, `Nama`) VALUES
(1, 'admin', '1234', 'Administrator Utama');

-- --------------------------------------------------------

--
-- Table structure for table `detail_pembayaran`
--

CREATE TABLE `detail_pembayaran` (
  `ID_Detail` int(11) NOT NULL,
  `ID_Pembayaran` int(11) DEFAULT NULL,
  `ID_Metode` int(11) DEFAULT NULL,
  `Nomor_Referensi` varchar(100) DEFAULT NULL,
  `Bukti_Pembayaran` varchar(255) DEFAULT NULL,
  `Status_Verifikasi` enum('Pending','Verified','Rejected') DEFAULT 'Pending',
  `Waktu_Verifikasi` timestamp NULL DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `dokter`
--

CREATE TABLE `dokter` (
  `ID_Dokter` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `Spesialis` varchar(50) NOT NULL,
  `Jadwal_Praktik` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokter`
--

INSERT INTO `dokter` (`ID_Dokter`, `Username`, `Password`, `Nama`, `Spesialis`, `Jadwal_Praktik`) VALUES
(1, 'dr.anton', '1234', 'dr. Anton', 'Umum', ''),
(2, 'dr.budi', '1234', 'dr. Budi', 'Paru-paru', ''),
(3, 'dr.citra', '1234', 'dr. Citra', 'Mata', ''),
(4, 'dr.ken', '1234', 'dr. Ken', 'THT', ''),
(5, 'dr.wilson', '1234', 'dr. Wilson', 'Umum', ''),
(6, 'dr.ayu', '1234', 'dr. Ayu', 'Umum', ''),
(10, 'dr.abc', '$2y$10$ob248QyJwHSRvgYWbx.6WeoHWKS3.5cAKWFPGqb9oTYzjUVWs8st6', 'dr.abc', 'Umum', 'Senin-Jumat');

-- --------------------------------------------------------

--
-- Table structure for table `dokumen_medis`
--

CREATE TABLE `dokumen_medis` (
  `ID_Dokumen` int(11) NOT NULL,
  `ID_Pasien` int(11) NOT NULL,
  `Nama_File` varchar(255) NOT NULL,
  `Jenis_Dokumen` varchar(50) NOT NULL,
  `Keterangan` text DEFAULT NULL,
  `Tanggal_Upload` datetime NOT NULL,
  `Path_File` varchar(500) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `dokumen_medis`
--

INSERT INTO `dokumen_medis` (`ID_Dokumen`, `ID_Pasien`, `Nama_File`, `Jenis_Dokumen`, `Keterangan`, `Tanggal_Upload`, `Path_File`) VALUES
(13, 1, 'doc_674bbead66668.pdf', 'Resep', '123', '2024-12-01 08:41:01', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bbead66668.pdf'),
(14, 1, 'doc_674bc83d195df.pdf', 'Rujukan', '.', '2024-12-01 09:21:49', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bc83d195df.pdf'),
(15, 1, 'doc_674bc8867ae44.pdf', 'Resep', '', '2024-12-01 09:23:02', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bc8867ae44.pdf'),
(16, 1, 'doc_674bc898def5f.pdf', 'Hasil Lab', '312', '2024-12-01 09:23:20', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bc898def5f.pdf'),
(17, 1, 'doc_674bd0d9cfe92.pdf', 'Hasil Lab', '31', '2024-12-01 09:58:33', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bd0d9cfe92.pdf'),
(18, 1, 'doc_674bf450d3a3f.pdf', 'Hasil Lab', '.', '2024-12-01 12:29:52', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674bf450d3a3f.pdf'),
(19, 1, 'doc_674eaaa3b9105.pdf', 'Hasil Lab', '..', '2024-12-03 13:52:19', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674eaaa3b9105.pdf'),
(20, 1, 'doc_674e8504901cf.pdf', 'Rujukan', '', '2024-12-03 11:11:48', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674e8504901cf.pdf'),
(21, 6, 'doc_674f3ab69ae0c.pdf', 'Hasil Lab', '', '2024-12-04 00:07:02', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/6/doc_674f3ab69ae0c.pdf'),
(22, 1, 'doc_674fd4d9a5f2e.pdf', 'Hasil Lab', '-', '2024-12-04 11:04:41', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/1/doc_674fd4d9a5f2e.pdf');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_dokter`
--

CREATE TABLE `jadwal_dokter` (
  `ID_Jadwal` int(11) NOT NULL,
  `ID_Dokter` int(11) DEFAULT NULL,
  `Jam_Mulai` time NOT NULL,
  `Jam_Selesai` time NOT NULL,
  `Kuota_Online` int(11) DEFAULT NULL,
  `Kuota_Offline` int(11) DEFAULT NULL,
  `Max_Pasien` int(11) NOT NULL DEFAULT 20,
  `Hari` varchar(20) NOT NULL,
  `Status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif',
  `Keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_dokter`
--

INSERT INTO `jadwal_dokter` (`ID_Jadwal`, `ID_Dokter`, `Jam_Mulai`, `Jam_Selesai`, `Kuota_Online`, `Kuota_Offline`, `Max_Pasien`, `Hari`, `Status`, `Keterangan`) VALUES
(1, 1, '08:00:00', '12:00:00', 19, 8, 30, 'Senin', 'Aktif', ''),
(2, 1, '08:00:00', '12:00:00', 18, 20, 20, 'Rabu', 'Aktif', ''),
(3, 1, '13:00:00', '17:00:00', 19, 20, 20, 'Jumat', 'Aktif', ''),
(4, 2, '08:00:00', '14:00:00', 13, 15, 15, 'Selasa', 'Aktif', ''),
(5, 2, '08:00:00', '14:00:00', 12, 15, 15, 'Kamis', 'Aktif', ''),
(6, 3, '13:00:00', '17:00:00', 21, 25, 25, 'Selasa', 'Aktif', ''),
(7, 3, '13:00:00', '17:00:00', 24, 17, 50, 'Jumat', 'Aktif', ''),
(8, 4, '08:00:00', '12:00:00', 20, 20, 20, 'Senin', 'Aktif', ''),
(9, 6, '09:00:00', '11:00:00', 24, 15, 40, 'Jumat', 'Aktif', ''),
(12, 6, '13:00:00', '14:00:00', 38, 40, 40, 'Rabu', 'Aktif', ''),
(13, 10, '19:00:00', '21:41:00', 30, 30, 30, 'Kamis', 'Aktif', ''),
(14, 6, '12:00:00', '19:00:00', 20, 15, 20, 'Sabtu', 'Aktif', ''),
(16, 10, '12:22:00', '15:22:00', 10, 10, 20, 'Senin', 'Aktif', ''),
(17, 4, '12:25:00', '13:00:00', 20, 15, 40, 'Jumat', 'Aktif', '');

-- --------------------------------------------------------

--
-- Table structure for table `metode_pembayaran`
--

CREATE TABLE `metode_pembayaran` (
  `ID_Metode` int(11) NOT NULL,
  `Nama_Metode` varchar(50) NOT NULL,
  `Status` enum('Aktif','Nonaktif') DEFAULT 'Aktif',
  `Created_At` timestamp NOT NULL DEFAULT current_timestamp()
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `metode_pembayaran`
--

INSERT INTO `metode_pembayaran` (`ID_Metode`, `Nama_Metode`, `Status`, `Created_At`) VALUES
(1, 'QRIS', 'Aktif', '2024-11-29 18:26:16'),
(2, 'Bank Transfer', 'Aktif', '2024-11-29 18:26:16'),
(3, 'Cash', 'Aktif', '2024-11-29 18:26:16'),
(4, 'Debit Card', 'Aktif', '2024-11-29 18:26:16'),
(5, 'Credit Card', 'Aktif', '2024-11-29 18:26:16');

-- --------------------------------------------------------

--
-- Table structure for table `pasien`
--

CREATE TABLE `pasien` (
  `ID_Pasien` int(11) NOT NULL,
  `Nama` varchar(100) NOT NULL,
  `NIK` varchar(16) DEFAULT NULL,
  `Tanggal_Lahir` date NOT NULL,
  `Jenis_Kelamin` enum('Laki-laki','Perempuan') DEFAULT NULL,
  `Username` varchar(50) DEFAULT NULL,
  `Password` varchar(255) DEFAULT NULL,
  `Email` varchar(100) DEFAULT NULL,
  `No_HP` varchar(15) DEFAULT NULL,
  `Alamat` text DEFAULT NULL,
  `Nomor_Rekam_Medis` varchar(20) NOT NULL,
  `Umur` int(11) DEFAULT NULL,
  `reset_token` varchar(64) DEFAULT NULL,
  `reset_token_expiry` datetime DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`ID_Pasien`, `Nama`, `NIK`, `Tanggal_Lahir`, `Jenis_Kelamin`, `Username`, `Password`, `Email`, `No_HP`, `Alamat`, `Nomor_Rekam_Medis`, `Umur`, `reset_token`, `reset_token_expiry`) VALUES
(1, 'Marcell', '1234561918941892', '2003-12-05', 'Laki-laki', 'Marcell', '$2y$10$Fd1u3NFwYxYUrYZ/yV5uceMZSH5BQLvWLqknqKFwdDhcRUuYWhrAO', 'asdas@gmail.com', '12312321', '321123312', 'RM-2024-00001', 20, 'bf59f86de4a8c172b8b5bf4221bad72d9a21f209c715e59c7782be88bfaf4f55', '2024-12-01 18:16:58'),
(2, 'sava', '1234561918941896', '2003-12-10', 'Laki-laki', NULL, '$2y$10$91Ic5mU0.GUBJQI4InuAWOEx2/FgisbRFDeUomTea9tBYorN.kOtC', 'asdas@gmail.com', '213231123312123', '321231123', 'RM-2024-00002', 20, 'bf59f86de4a8c172b8b5bf4221bad72d9a21f209c715e59c7782be88bfaf4f55', '2024-12-01 18:16:58'),
(3, 'Army', '1234561918941893', '2003-12-12', 'Perempuan', 'Army', '$2y$10$dqaIe0BEFbG7boljKRYFB.PrNGW2BK3rrYjvgGmz2eRBLuNz/vh7y', 'asdas@gmail.com', '123', 'kopo', 'RM-2024-00003', 20, 'bf59f86de4a8c172b8b5bf4221bad72d9a21f209c715e59c7782be88bfaf4f55', '2024-12-01 18:16:58'),
(5, 'Anto', '1234561913212521', '2003-12-10', 'Laki-laki', 'user', '$2y$10$Smx3t4aP6N72oOy2pB/GOOSbYWco1Fl6iHe0lmzPK09AlR3xxk9na', 'asdas@gmail.com', '213231123312123', 'Kopo', 'RM-2024-00005', 20, 'bf59f86de4a8c172b8b5bf4221bad72d9a21f209c715e59c7782be88bfaf4f55', '2024-12-01 18:16:58'),
(6, 'Anto', '1234561918941545', '2024-11-29', 'Laki-laki', 'user1', '$2y$10$5YJJBrqL7exTsRonF6xRe.1VNfNPLWCZT8Jnyuj42gJU4t7idoCKC', 'asdas@gmail.com', '213231123312123', 'koop', 'RM-2024-00006', 0, 'bf59f86de4a8c172b8b5bf4221bad72d9a21f209c715e59c7782be88bfaf4f55', '2024-12-01 18:16:58'),
(9, 'Antoo', '123456191894151', '2024-12-03', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0003', 0, NULL, NULL),
(10, 'Perawat', '1234561918551892', '2024-12-03', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0004', 0, NULL, NULL),
(11, 'Antooo', '1234231312123231', '2024-12-09', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0005', 0, NULL, NULL),
(12, 'dr. Anton', '1231231231231231', '2024-06-11', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0006', 0, NULL, NULL),
(13, 'Kikooo', '1010101010101010', '2024-12-09', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0007', 0, NULL, NULL),
(14, 'Andrew', '12123123123123', '2024-12-09', 'Perempuan', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0008', 0, NULL, NULL),
(15, 'Perawat', '1234561918941892', '2024-12-04', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0009', 0, NULL, NULL),
(16, 'Perawat', '123123123123123', '2024-09-16', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RM-2024-0010', 0, NULL, NULL),
(20, 'Suti', '0396816043106917', '2003-10-15', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00001', NULL, NULL, NULL),
(21, 'TONO', '0396816043106916', '2024-12-10', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00002', NULL, NULL, NULL),
(22, 'KENZHII', '1234561918945155', '2024-12-11', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00003', NULL, NULL, NULL),
(23, 'Aaron', '3132213213131313', '2024-12-09', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00004', NULL, NULL, NULL),
(24, 'dr. Anton', '1222222222222222', '2024-12-09', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00005', NULL, NULL, NULL),
(25, 'Kiko', '1111111111111111', '2024-12-03', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00006', NULL, NULL, NULL),
(26, 'Kiko', '3333333333333333', '2024-12-04', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00007', NULL, NULL, NULL),
(27, 'hokik', '4444444444444444', '2024-12-04', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00008', NULL, NULL, NULL),
(28, 'bcd', '1234561914444444', '2024-12-04', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00009', NULL, NULL, NULL),
(29, 'Antooo', '1234565555555555', '2024-12-04', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00010', NULL, NULL, NULL),
(30, 'SAVA', '1341414141414142', '2024-12-16', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00011', NULL, NULL, NULL);

-- --------------------------------------------------------

--
-- Table structure for table `pembayaran`
--

CREATE TABLE `pembayaran` (
  `ID_Pembayaran` int(11) NOT NULL,
  `ID_Pendaftaran` int(11) DEFAULT NULL,
  `Tanggal` datetime NOT NULL,
  `Jumlah` decimal(10,2) NOT NULL,
  `Metode` varchar(50) NOT NULL,
  `Status` varchar(20) DEFAULT 'Belum Lunas'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemeriksaan`
--

CREATE TABLE `pemeriksaan` (
  `ID_Pemeriksaan` int(11) NOT NULL,
  `ID_Pendaftaran` int(11) DEFAULT NULL,
  `ID_Dokter` int(11) DEFAULT NULL,
  `Waktu_Periksa` datetime NOT NULL,
  `Diagnosa` longtext NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemeriksaan`
--

INSERT INTO `pemeriksaan` (`ID_Pemeriksaan`, `ID_Pendaftaran`, `ID_Dokter`, `Waktu_Periksa`, `Diagnosa`) VALUES
(55, 89, 3, '2024-12-03 11:15:20', 'ABCD'),
(56, 90, 2, '2024-12-03 11:15:38', 'CDE'),
(57, 103, 1, '2024-12-04 06:35:18', 'Pusing'),
(58, 105, 3, '2024-12-04 06:43:50', 'abc'),
(59, 106, 3, '2024-12-06 06:46:58', 'ABC'),
(60, 104, 6, '2024-12-06 06:56:37', 'ABC'),
(61, 107, 1, '2024-12-04 11:06:02', 'Paru paru'),
(62, 108, 6, '2024-12-04 11:06:32', 'adsasaddsa'),
(63, 109, 3, '2024-12-06 11:11:44', 'ABUABBBUBUUUUUUUUuu'),
(64, 111, 3, '2024-12-13 11:19:06', 'SAKIT PERUT');

-- --------------------------------------------------------

--
-- Table structure for table `pendaftaran`
--

CREATE TABLE `pendaftaran` (
  `ID_Pendaftaran` int(11) NOT NULL,
  `ID_Pasien` int(11) DEFAULT NULL,
  `ID_Jadwal` int(11) DEFAULT NULL,
  `Waktu_Daftar` datetime NOT NULL,
  `No_Antrian` int(11) NOT NULL,
  `Status` varchar(20) DEFAULT 'Menunggu',
  `Bukti_Reservasi` varchar(255) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`ID_Pendaftaran`, `ID_Pasien`, `ID_Jadwal`, `Waktu_Daftar`, `No_Antrian`, `Status`, `Bukti_Reservasi`) VALUES
(89, 1, 6, '2024-12-03 00:00:00', 1, 'Selesai', NULL),
(90, 1, 4, '2024-12-03 00:00:00', 1, 'Selesai', NULL),
(103, 6, 2, '2024-12-04 00:00:00', 1, 'Selesai', NULL),
(104, 6, 9, '2024-12-06 00:00:00', 1, 'Selesai', NULL),
(105, 20, 7, '2024-12-04 00:42:15', 1, 'Selesai', 'REG20241204001'),
(106, 21, 7, '2024-12-06 00:46:28', 1, 'Selesai', 'REG20241204001'),
(107, 1, 2, '2024-12-04 00:00:00', 2, 'Selesai', NULL),
(108, 1, 12, '2024-12-04 00:00:00', 1, 'Selesai', NULL),
(109, 21, 7, '2024-12-06 05:09:44', 2, 'Selesai', 'REG20241204002'),
(110, 1, 5, '2024-12-12 00:00:00', 1, 'Menunggu', NULL),
(111, 22, 7, '2024-12-13 05:15:40', 1, 'Selesai', 'REG20241211001'),
(112, 23, 1, '2024-12-09 06:10:54', 1, 'Menunggu', 'REG20241204001'),
(113, 23, 1, '2024-12-09 06:10:54', 2, 'Menunggu', 'REG20241204002'),
(114, 24, 1, '2024-12-09 06:11:59', 3, 'Menunggu', 'REG20241204003'),
(115, 24, 1, '2024-12-09 06:11:59', 4, 'Menunggu', 'REG20241204004'),
(116, 25, 1, '2024-12-04 06:15:18', 1, 'Menunggu', 'REG20241204001'),
(117, 26, 1, '2024-12-08 06:41:48', 1, 'Menunggu', 'REG20241204001'),
(118, 26, 1, '2024-12-08 06:41:48', 2, 'Menunggu', 'REG20241204002'),
(119, 27, 1, '2024-12-16 06:52:43', 1, 'Menunggu', 'REG20241204001'),
(120, 27, 1, '2024-12-16 06:52:43', 2, 'Menunggu', 'REG20241204002'),
(121, 28, 1, '2024-12-09 07:02:08', 5, 'Menunggu', 'REG20241204005'),
(122, 28, 1, '2024-12-09 07:02:08', 6, 'Menunggu', 'REG20241204006'),
(123, 29, 1, '2024-12-09 07:17:12', 7, 'Menunggu', 'REG20241204007'),
(124, 1, 6, '2024-12-24 00:00:00', 1, 'Menunggu', NULL),
(125, 30, 1, '2024-12-30 07:24:36', 1, 'Menunggu', 'REG20241204001'),
(126, 1, 5, '2024-12-05 00:00:00', 1, 'Menunggu', NULL);

-- --------------------------------------------------------

--
-- Table structure for table `perawat`
--

CREATE TABLE `perawat` (
  `ID_Perawat` int(11) NOT NULL,
  `Username` varchar(50) NOT NULL,
  `Password` varchar(255) NOT NULL,
  `Nama` varchar(100) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `perawat`
--

INSERT INTO `perawat` (`ID_Perawat`, `Username`, `Password`, `Nama`) VALUES
(3, 'perawat', '1234', 'Perawat');

-- --------------------------------------------------------

--
-- Table structure for table `rekam_medis`
--

CREATE TABLE `rekam_medis` (
  `ID_Rekam` int(11) NOT NULL,
  `ID_Pasien` int(11) DEFAULT NULL,
  `Tekanan_Darah` varchar(20) DEFAULT NULL,
  `Tinggi_Badan` decimal(5,2) DEFAULT NULL,
  `Berat_Badan` decimal(5,2) DEFAULT NULL,
  `Suhu` decimal(4,1) DEFAULT NULL,
  `Riwayat_Penyakit` longtext DEFAULT NULL,
  `Tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `rekam_medis`
--

INSERT INTO `rekam_medis` (`ID_Rekam`, `ID_Pasien`, `Tekanan_Darah`, `Tinggi_Badan`, `Berat_Badan`, `Suhu`, `Riwayat_Penyakit`, `Tanggal`) VALUES
(29, 1, '130/50', 200.00, 200.00, 50.0, '       PARU PARU                 ', '2024-12-03'),
(30, 6, '125/23', 174.00, 54.00, 34.0, '                                Pusing                            ', '2024-12-04'),
(31, 1, '200/10', 173.00, 75.00, 39.0, 'Sakit Badan', '2024-12-04');

-- --------------------------------------------------------

--
-- Table structure for table `resep`
--

CREATE TABLE `resep` (
  `ID_Resep` int(11) NOT NULL,
  `ID_Pemeriksaan` int(11) DEFAULT NULL,
  `Resep_Obat` longtext NOT NULL,
  `Tanggal` date NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `resep`
--

INSERT INTO `resep` (`ID_Resep`, `ID_Pemeriksaan`, `Resep_Obat`, `Tanggal`) VALUES
(49, 55, 'ABCD', '2024-12-03'),
(50, 56, 'CDE', '2024-12-03'),
(51, 57, 'Pusing', '2024-12-04'),
(52, 58, 'abc', '2024-12-04'),
(53, 59, 'ABC', '2024-12-06'),
(54, 60, 'ABC', '2024-12-06'),
(55, 61, 'abc\r\n', '2024-12-04'),
(56, 62, 'dasdasads', '2024-12-04'),
(57, 63, 'sadaddaaaaaaaaaaaaaaaaaaaaaaaaa', '2024-12-06'),
(58, 64, 'ABC\r\n', '2024-12-13');

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_jadwal_dokter`
-- (See below for the actual view)
--
CREATE TABLE `v_jadwal_dokter` (
);

-- --------------------------------------------------------

--
-- Structure for view `v_jadwal_dokter`
--
DROP TABLE IF EXISTS `v_jadwal_dokter`;

CREATE ALGORITHM=UNDEFINED DEFINER=`root`@`localhost` SQL SECURITY DEFINER VIEW `v_jadwal_dokter`  AS SELECT `jd`.`ID_Jadwal` AS `ID_Jadwal`, `d`.`ID_Dokter` AS `ID_Dokter`, `d`.`Nama` AS `nama_dokter`, `d`.`Spesialis` AS `Spesialis`, `jd`.`Hari` AS `Hari`, `jd`.`Jam_Mulai` AS `Jam_Mulai`, `jd`.`Jam_Selesai` AS `Jam_Selesai`, `jd`.`Kuota` AS `Kuota`, `jd`.`Max_Pasien` AS `Max_Pasien`, `jd`.`Status` AS `Status`, `jd`.`Keterangan` AS `Keterangan`, (select count(0) from `pendaftaran` `p` where `p`.`ID_Jadwal` = `jd`.`ID_Jadwal` and cast(`p`.`Waktu_Daftar` as date) = curdate()) AS `jumlah_pasien_hari_ini` FROM (`jadwal_dokter` `jd` join `dokter` `d` on(`jd`.`ID_Dokter` = `d`.`ID_Dokter`)) ;

--
-- Indexes for dumped tables
--

--
-- Indexes for table `administrator`
--
ALTER TABLE `administrator`
  ADD PRIMARY KEY (`ID_Admin`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `detail_pembayaran`
--
ALTER TABLE `detail_pembayaran`
  ADD PRIMARY KEY (`ID_Detail`),
  ADD KEY `ID_Pembayaran` (`ID_Pembayaran`),
  ADD KEY `ID_Metode` (`ID_Metode`);

--
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`ID_Dokter`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `IX_Dokter_Spesialis` (`Spesialis`);

--
-- Indexes for table `dokumen_medis`
--
ALTER TABLE `dokumen_medis`
  ADD PRIMARY KEY (`ID_Dokumen`),
  ADD KEY `ID_Pasien` (`ID_Pasien`);

--
-- Indexes for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD PRIMARY KEY (`ID_Jadwal`),
  ADD KEY `ID_Dokter` (`ID_Dokter`);

--
-- Indexes for table `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  ADD PRIMARY KEY (`ID_Metode`);

--
-- Indexes for table `pasien`
--
ALTER TABLE `pasien`
  ADD PRIMARY KEY (`ID_Pasien`),
  ADD UNIQUE KEY `Nomor_Rekam_Medis` (`Nomor_Rekam_Medis`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `IX_Pasien_Username` (`Username`);

--
-- Indexes for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD PRIMARY KEY (`ID_Pembayaran`),
  ADD KEY `ID_Pendaftaran` (`ID_Pendaftaran`),
  ADD KEY `IX_Pembayaran_Status` (`Status`);

--
-- Indexes for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  ADD PRIMARY KEY (`ID_Pemeriksaan`),
  ADD KEY `ID_Pendaftaran` (`ID_Pendaftaran`),
  ADD KEY `ID_Dokter` (`ID_Dokter`);

--
-- Indexes for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD PRIMARY KEY (`ID_Pendaftaran`),
  ADD KEY `ID_Pasien` (`ID_Pasien`),
  ADD KEY `ID_Jadwal` (`ID_Jadwal`),
  ADD KEY `IX_Pendaftaran_Tanggal` (`Waktu_Daftar`);

--
-- Indexes for table `perawat`
--
ALTER TABLE `perawat`
  ADD PRIMARY KEY (`ID_Perawat`),
  ADD UNIQUE KEY `Username` (`Username`);

--
-- Indexes for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD PRIMARY KEY (`ID_Rekam`),
  ADD KEY `ID_Pasien` (`ID_Pasien`);

--
-- Indexes for table `resep`
--
ALTER TABLE `resep`
  ADD PRIMARY KEY (`ID_Resep`),
  ADD KEY `ID_Pemeriksaan` (`ID_Pemeriksaan`);

--
-- AUTO_INCREMENT for dumped tables
--

--
-- AUTO_INCREMENT for table `administrator`
--
ALTER TABLE `administrator`
  MODIFY `ID_Admin` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=2;

--
-- AUTO_INCREMENT for table `detail_pembayaran`
--
ALTER TABLE `detail_pembayaran`
  MODIFY `ID_Detail` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `ID_Dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=11;

--
-- AUTO_INCREMENT for table `dokumen_medis`
--
ALTER TABLE `dokumen_medis`
  MODIFY `ID_Dokumen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=23;

--
-- AUTO_INCREMENT for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  MODIFY `ID_Jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=18;

--
-- AUTO_INCREMENT for table `metode_pembayaran`
--
ALTER TABLE `metode_pembayaran`
  MODIFY `ID_Metode` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=6;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `ID_Pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=31;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `ID_Pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  MODIFY `ID_Pemeriksaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=65;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `ID_Pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=127;

--
-- AUTO_INCREMENT for table `perawat`
--
ALTER TABLE `perawat`
  MODIFY `ID_Perawat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `ID_Rekam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=32;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `ID_Resep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=59;

--
-- Constraints for dumped tables
--

--
-- Constraints for table `detail_pembayaran`
--
ALTER TABLE `detail_pembayaran`
  ADD CONSTRAINT `detail_pembayaran_ibfk_1` FOREIGN KEY (`ID_Pembayaran`) REFERENCES `pembayaran` (`ID_Pembayaran`),
  ADD CONSTRAINT `detail_pembayaran_ibfk_2` FOREIGN KEY (`ID_Metode`) REFERENCES `metode_pembayaran` (`ID_Metode`);

--
-- Constraints for table `dokumen_medis`
--
ALTER TABLE `dokumen_medis`
  ADD CONSTRAINT `dokumen_medis_ibfk_1` FOREIGN KEY (`ID_Pasien`) REFERENCES `pasien` (`ID_Pasien`);

--
-- Constraints for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD CONSTRAINT `jadwal_dokter_ibfk_1` FOREIGN KEY (`ID_Dokter`) REFERENCES `dokter` (`ID_Dokter`);

--
-- Constraints for table `pembayaran`
--
ALTER TABLE `pembayaran`
  ADD CONSTRAINT `pembayaran_ibfk_1` FOREIGN KEY (`ID_Pendaftaran`) REFERENCES `pendaftaran` (`ID_Pendaftaran`);

--
-- Constraints for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  ADD CONSTRAINT `pemeriksaan_ibfk_1` FOREIGN KEY (`ID_Pendaftaran`) REFERENCES `pendaftaran` (`ID_Pendaftaran`),
  ADD CONSTRAINT `pemeriksaan_ibfk_2` FOREIGN KEY (`ID_Dokter`) REFERENCES `dokter` (`ID_Dokter`);

--
-- Constraints for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  ADD CONSTRAINT `pendaftaran_ibfk_1` FOREIGN KEY (`ID_Pasien`) REFERENCES `pasien` (`ID_Pasien`),
  ADD CONSTRAINT `pendaftaran_ibfk_2` FOREIGN KEY (`ID_Jadwal`) REFERENCES `jadwal_dokter` (`ID_Jadwal`);

--
-- Constraints for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  ADD CONSTRAINT `rekam_medis_ibfk_1` FOREIGN KEY (`ID_Pasien`) REFERENCES `pasien` (`ID_Pasien`);

--
-- Constraints for table `resep`
--
ALTER TABLE `resep`
  ADD CONSTRAINT `resep_ibfk_1` FOREIGN KEY (`ID_Pemeriksaan`) REFERENCES `pemeriksaan` (`ID_Pemeriksaan`);
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
