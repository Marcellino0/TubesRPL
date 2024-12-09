-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Dec 09, 2024 at 03:38 PM
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
(10, 'dr.abc', '1234', 'dr.abc', 'Umum', 'Senin-Jumat');

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
(23, 48, 'doc_6753268de3612.pdf', 'Hasil Lab', '', '2024-12-06 23:30:05', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/48/doc_6753268de3612.pdf'),
(24, 48, 'doc_6754786dad5a1.pdf', 'Hasil Lab', '', '2024-12-07 23:31:41', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/48/doc_6754786dad5a1.pdf'),
(25, 49, 'doc_6754797cca948.pdf', 'Hasil Lab', '', '2024-12-07 23:36:12', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/49/doc_6754797cca948.pdf'),
(26, 48, 'doc_67571e33a0d52.pdf', 'Hasil Lab', '', '2024-12-09 23:43:31', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/48/doc_67571e33a0d52.pdf'),
(27, 48, 'doc_67571e7e74526.pdf', 'Hasil Lab', '', '2024-12-09 23:44:46', 'C:\\xampp\\htdocs\\TubesRPL\\nurse/uploads/48/doc_67571e7e74526.pdf');

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
(1, 1, '08:00:00', '12:00:00', 0, 6, 30, 'Senin', 'Aktif', ''),
(2, 1, '08:00:00', '12:00:00', 18, 20, 20, 'Rabu', 'Aktif', ''),
(3, 1, '13:00:00', '17:00:00', 19, 20, 20, 'Jumat', 'Aktif', ''),
(4, 2, '08:00:00', '14:00:00', 13, 15, 15, 'Selasa', 'Aktif', ''),
(5, 2, '08:00:00', '14:00:00', 12, 14, 15, 'Kamis', 'Aktif', ''),
(6, 3, '13:00:00', '17:00:00', 21, 24, 25, 'Selasa', 'Aktif', ''),
(7, 3, '13:00:00', '17:00:00', 25, 21, 50, 'Jumat', 'Aktif', ''),
(8, 4, '08:00:00', '12:00:00', 17, 20, 20, 'Senin', 'Aktif', ''),
(9, 6, '09:00:00', '11:00:00', 24, 15, 40, 'Jumat', 'Aktif', ''),
(12, 6, '13:00:00', '14:00:00', 38, 39, 40, 'Rabu', 'Aktif', ''),
(13, 10, '19:00:00', '21:41:00', 30, 30, 30, 'Kamis', 'Aktif', ''),
(14, 6, '12:00:00', '19:00:00', 19, 14, 20, 'Sabtu', 'Aktif', ''),
(16, 10, '12:22:00', '15:22:00', 9, 8, 20, 'Senin', 'Aktif', ''),
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
  `reset_token_expiry` datetime DEFAULT NULL,
  `Registration_Type` enum('online','offline') NOT NULL DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`ID_Pasien`, `Nama`, `NIK`, `Tanggal_Lahir`, `Jenis_Kelamin`, `Username`, `Password`, `Email`, `No_HP`, `Alamat`, `Nomor_Rekam_Medis`, `Umur`, `reset_token`, `reset_token_expiry`, `Registration_Type`) VALUES
(48, 'Kenzhi', '0396816043106916', '2024-12-06', 'Laki-laki', 'user', '$2y$10$HU731YVzyfTnAcE4.Gdax.3lNpxiMUTKxzODhbvLmFRWlKtt7Uqm2', 'kenzhi@gmail.com', '12345678', 'Kopo', 'RM-2024-00001', 0, NULL, NULL, 'offline'),
(49, 'Kiko', '0396816043106915', '2024-12-07', 'Laki-laki', NULL, NULL, NULL, NULL, NULL, 'RMOffline-2024-00001', 0, NULL, NULL, 'offline'),
(50, 'Kenzhu', '4567854645489454', '2003-07-26', 'Laki-laki', 'Kenzhu', '$2y$10$CYPNk9aYZDhxLDIB.kbCAOPXxZ8kAfgvZGZCjJp6iklg.fjcvzfAG', 'kenzhu@gmail.com', '083811888826', 'Jl.Mekar Lestari no.11', 'RM-2024-00002', 21, NULL, NULL, 'offline'),
(51, 'Maulana', '4775675688768678', '2024-12-01', 'Laki-laki', 'maulana', '$2y$10$XWLldXHXUCpW9Qyesu2Hn.V6.pkq3dhVjA2POBJHMH13QvrLC1qHO', 'maulana@gmail.com', '08142343245', 'Sukajadi no 13', 'RM-2024-00003', 0, NULL, NULL, 'offline'),
(52, 'Jonathan', '2142353465467564', '2024-12-01', 'Laki-laki', 'Jonathan', '$2y$10$wF.iO2i87lAkQmn7XB1I..cBcaCnpge6U0tpVijWi6/6HZHJ5bWUK', 'Jonathan@gmail.com', '081346458347', 'TKI 2', 'RM-2024-00004', 0, NULL, NULL, 'offline');

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

--
-- Dumping data for table `pembayaran`
--

INSERT INTO `pembayaran` (`ID_Pembayaran`, `ID_Pendaftaran`, `Tanggal`, `Jumlah`, `Metode`, `Status`) VALUES
(6, 137, '2024-12-06 21:26:21', 5444.00, 'QRIS', 'Lunas'),
(7, NULL, '2024-12-06 21:28:04', 35000.00, 'QRIS', 'Lunas'),
(8, 139, '2024-12-07 23:34:23', 100000.00, 'QRIS', 'Belum Lunas'),
(9, 142, '2024-12-06 23:45:54', 55555.00, 'Bank Transfer', 'Lunas'),
(10, 142, '2024-12-06 23:46:01', 66666.00, 'QRIS', 'Lunas');

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
(65, 137, 3, '2024-12-06 18:15:32', 'aaa'),
(66, 138, 3, '2024-12-06 23:30:32', 'bbb'),
(67, 139, 6, '2024-12-07 23:33:47', 'sdasda'),
(68, 140, 6, '2024-12-07 23:36:26', 'abc'),
(69, 141, 4, '2024-12-09 23:44:00', 'abc'),
(70, 142, 1, '2024-12-09 23:45:20', 'abc'),
(71, 144, 1, '2024-12-09 20:02:03', '123');

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
  `Bukti_Reservasi` varchar(255) DEFAULT NULL,
  `Verifikasi` enum('Belum Diverifikasi','Terverifikasi','Ditolak') DEFAULT 'Belum Diverifikasi',
  `Waktu_Verifikasi` datetime DEFAULT NULL,
  `Catatan_Verifikasi` text DEFAULT NULL,
  `Tipe_Pendaftaran` enum('online','offline') NOT NULL DEFAULT 'offline'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`ID_Pendaftaran`, `ID_Pasien`, `ID_Jadwal`, `Waktu_Daftar`, `No_Antrian`, `Status`, `Bukti_Reservasi`, `Verifikasi`, `Waktu_Verifikasi`, `Catatan_Verifikasi`, `Tipe_Pendaftaran`) VALUES
(137, 48, 7, '2024-12-06 00:00:00', 1, 'Selesai', NULL, 'Belum Diverifikasi', NULL, NULL, 'offline'),
(138, 48, 7, '2024-12-06 16:57:55', 2, 'Selesai', 'REG20241206002', 'Belum Diverifikasi', NULL, NULL, 'offline'),
(139, 48, 14, '2024-12-07 00:00:00', 1, 'Selesai', NULL, 'Belum Diverifikasi', NULL, NULL, 'offline'),
(140, 49, 14, '2024-12-07 23:35:04', 2, 'Selesai', 'REG20241207002', 'Belum Diverifikasi', NULL, NULL, 'offline'),
(141, 48, 8, '2024-12-09 00:00:00', 1, 'Selesai', NULL, 'Belum Diverifikasi', NULL, NULL, 'offline'),
(142, 48, 1, '2024-12-09 00:00:00', 1, 'Selesai', NULL, 'Belum Diverifikasi', NULL, NULL, 'offline'),
(144, 50, 1, '2024-12-09 00:00:00', 1, 'Selesai', NULL, 'Terverifikasi', '2024-12-09 19:56:52', '', 'offline'),
(145, 50, 16, '2024-12-09 00:00:00', 1, 'Menunggu', NULL, 'Terverifikasi', '2024-12-09 20:16:51', '', 'offline'),
(146, 48, 16, '2024-12-09 00:00:00', 2, 'Menunggu', NULL, 'Terverifikasi', '2024-12-09 20:26:36', '', 'offline'),
(147, 50, 8, '2024-12-09 00:00:00', 0, 'Menunggu', NULL, 'Belum Diverifikasi', NULL, NULL, 'offline'),
(148, 51, 8, '2024-12-09 00:00:00', 1, 'Menunggu', NULL, 'Terverifikasi', '2024-12-09 20:45:01', '', 'offline'),
(149, 51, 16, '2024-12-09 20:49:44', 3, 'Menunggu', 'REG20241209003', 'Belum Diverifikasi', NULL, NULL, 'offline'),
(154, 52, 16, '2024-12-09 21:37:47', 3, 'Menunggu', 'REG20241209003', 'Terverifikasi', NULL, NULL, 'offline');

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
(3, 'perawat', '1234', 'Perawat'),
(4, 'perawat1', '1234', 'Perawat2');

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
(32, 48, '130/50', 200.00, 45.00, 34.0, 'Sakit', '2024-12-06'),
(33, 48, '200/10', 200.00, 50.00, 50.0, 'sss', '2024-12-07'),
(34, 49, '120/90', 200.00, 200.00, 60.0, 'abc', '2024-12-07'),
(35, 48, '130/60', 200.00, 50.00, 40.0, '                                abc                            ', '2024-12-09');

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
(59, 65, 'aaaa', '2024-12-06'),
(60, 66, 'bbb', '2024-12-06'),
(61, 67, 'dasasd', '2024-12-07'),
(62, 68, 'abc', '2024-12-07'),
(63, 69, 'abc', '2024-12-09'),
(64, 70, 'abc', '2024-12-09'),
(65, 71, '123', '2024-12-09');

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
  ADD UNIQUE KEY `unique_nik` (`NIK`),
  ADD KEY `IX_Pasien_Username` (`Username`),
  ADD KEY `idx_registration_type` (`Registration_Type`),
  ADD KEY `idx_nomor_rm` (`Nomor_Rekam_Medis`);

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
  MODIFY `ID_Dokumen` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=28;

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
  MODIFY `ID_Pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=53;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `ID_Pembayaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=13;

--
-- AUTO_INCREMENT for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  MODIFY `ID_Pemeriksaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=72;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `ID_Pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=155;

--
-- AUTO_INCREMENT for table `perawat`
--
ALTER TABLE `perawat`
  MODIFY `ID_Perawat` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `ID_Rekam` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=36;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `ID_Resep` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=66;

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
