-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 17, 2024 at 05:04 PM
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
(1, 'admin', '240be518fabd2724ddb6f04eeb1da5967448d7e831c08c8fa822809f74c720a9', 'Administrator Utama');

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
(1, 'dr.anton', 'b3959dee9b178b030c2b8373da55a04ab3adb318edeb178953ff8b77301a360a', 'Dr. Anton', 'Penyakit Dalam', 'Senin-Jumat'),
(2, 'dr.budi', 'b3959dee9b178b030c2b8373da55a04ab3adb318edeb178953ff8b77301a360a', 'Dr. Budi', 'Paru-paru', 'Senin-Kamis'),
(3, 'dr.citra', 'b3959dee9b178b030c2b8373da55a04ab3adb318edeb178953ff8b77301a360a', 'Dr. Citra', 'Mata', 'Selasa-Jumat');

-- --------------------------------------------------------

--
-- Table structure for table `jadwal_dokter`
--

CREATE TABLE `jadwal_dokter` (
  `ID_Jadwal` int(11) NOT NULL,
  `ID_Dokter` int(11) DEFAULT NULL,
  `Jam_Mulai` time NOT NULL,
  `Jam_Selesai` time NOT NULL,
  `Kuota` int(11) NOT NULL,
  `Max_Pasien` int(11) NOT NULL DEFAULT 20,
  `Hari` varchar(20) NOT NULL,
  `Status` enum('Aktif','Tidak Aktif') DEFAULT 'Aktif',
  `Keterangan` text DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `jadwal_dokter`
--

INSERT INTO `jadwal_dokter` (`ID_Jadwal`, `ID_Dokter`, `Jam_Mulai`, `Jam_Selesai`, `Kuota`, `Max_Pasien`, `Hari`, `Status`, `Keterangan`) VALUES
(1, 1, '08:00:00', '12:00:00', 20, 20, 'Senin', 'Aktif', NULL),
(2, 1, '08:00:00', '12:00:00', 20, 20, 'Rabu', 'Aktif', NULL),
(3, 1, '13:00:00', '17:00:00', 20, 20, 'Jumat', 'Aktif', NULL),
(4, 2, '08:00:00', '14:00:00', 15, 15, 'Selasa', 'Aktif', NULL),
(5, 2, '08:00:00', '14:00:00', 15, 15, 'Kamis', 'Aktif', NULL),
(6, 3, '13:00:00', '17:00:00', 25, 25, 'Selasa', 'Aktif', NULL),
(7, 3, '13:00:00', '17:00:00', 25, 25, 'Jumat', 'Aktif', NULL);

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
  `Umur` int(11) DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pasien`
--

INSERT INTO `pasien` (`ID_Pasien`, `Nama`, `NIK`, `Tanggal_Lahir`, `Jenis_Kelamin`, `Username`, `Password`, `Email`, `No_HP`, `Alamat`, `Nomor_Rekam_Medis`, `Umur`) VALUES
(1, 'Marcell', '1234561918941892', '2003-12-05', 'Laki-laki', 'Marcell', '$2y$10$Fd1u3NFwYxYUrYZ/yV5uceMZSH5BQLvWLqknqKFwdDhcRUuYWhrAO', 'asdas@gmail.com', '12312321', '321123312', 'RM-2024-00001', 20),
(2, 'sava', '1234561918941896', '2003-12-10', 'Laki-laki', NULL, '$2y$10$91Ic5mU0.GUBJQI4InuAWOEx2/FgisbRFDeUomTea9tBYorN.kOtC', 'asdas@gmail.com', '213231123312123', '321231123', 'RM-2024-00002', 20),
(3, 'Army', '1234561918941893', '2003-12-12', 'Perempuan', 'Army', '$2y$10$dqaIe0BEFbG7boljKRYFB.PrNGW2BK3rrYjvgGmz2eRBLuNz/vh7y', 'asdas@gmail.com', '123', 'kopo', 'RM-2024-00003', 20),
(4, 'sava', '1234561918941815', '1555-12-05', 'Laki-laki', 'sava', '$2y$10$pvJ03E4.rjLgmSeAB23K.eRc7gbjzt1WRWY0VM6Pqk5K5HZYd7bmW', 'asdas@gmail.com', '213231123312123', 'sa', 'RM-2024-00004', 468);

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
(4, 1, 1, '2024-01-15 08:30:00', 'Flu dan Batuk Ringan\nTekanan darah normal\nPerlu istirahat cukup'),
(5, 2, 2, '2024-02-15 14:30:00', 'Demam\nRadang tenggorokan\nPerlu antibiotik'),
(6, 3, 3, '2024-03-14 08:30:00', 'Gastritis\nAsam lambung tinggi\nPerlu pengaturan pola makan');

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
  `Status` varchar(20) DEFAULT 'Menunggu'
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pendaftaran`
--

INSERT INTO `pendaftaran` (`ID_Pendaftaran`, `ID_Pasien`, `ID_Jadwal`, `Waktu_Daftar`, `No_Antrian`, `Status`) VALUES
(1, 1, 1, '2024-01-15 08:00:00', 1, 'Selesai'),
(2, 1, 2, '2024-02-15 14:00:00', 1, 'Selesai'),
(3, 2, 3, '2024-03-14 08:00:00', 1, 'Selesai');

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

-- --------------------------------------------------------

--
-- Stand-in structure for view `v_jadwal_dokter`
-- (See below for the actual view)
--
CREATE TABLE `v_jadwal_dokter` (
`ID_Jadwal` int(11)
,`ID_Dokter` int(11)
,`nama_dokter` varchar(100)
,`Spesialis` varchar(50)
,`Hari` varchar(20)
,`Jam_Mulai` time
,`Jam_Selesai` time
,`Kuota` int(11)
,`Max_Pasien` int(11)
,`Status` enum('Aktif','Tidak Aktif')
,`Keterangan` text
,`jumlah_pasien_hari_ini` bigint(21)
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
-- Indexes for table `dokter`
--
ALTER TABLE `dokter`
  ADD PRIMARY KEY (`ID_Dokter`),
  ADD UNIQUE KEY `Username` (`Username`),
  ADD KEY `IX_Dokter_Spesialis` (`Spesialis`);

--
-- Indexes for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  ADD PRIMARY KEY (`ID_Jadwal`),
  ADD KEY `ID_Dokter` (`ID_Dokter`);

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
-- AUTO_INCREMENT for table `dokter`
--
ALTER TABLE `dokter`
  MODIFY `ID_Dokter` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `jadwal_dokter`
--
ALTER TABLE `jadwal_dokter`
  MODIFY `ID_Jadwal` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=8;

--
-- AUTO_INCREMENT for table `pasien`
--
ALTER TABLE `pasien`
  MODIFY `ID_Pasien` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=5;

--
-- AUTO_INCREMENT for table `pembayaran`
--
ALTER TABLE `pembayaran`
  MODIFY `ID_Pembayaran` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `pemeriksaan`
--
ALTER TABLE `pemeriksaan`
  MODIFY `ID_Pemeriksaan` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=7;

--
-- AUTO_INCREMENT for table `pendaftaran`
--
ALTER TABLE `pendaftaran`
  MODIFY `ID_Pendaftaran` int(11) NOT NULL AUTO_INCREMENT, AUTO_INCREMENT=4;

--
-- AUTO_INCREMENT for table `perawat`
--
ALTER TABLE `perawat`
  MODIFY `ID_Perawat` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `rekam_medis`
--
ALTER TABLE `rekam_medis`
  MODIFY `ID_Rekam` int(11) NOT NULL AUTO_INCREMENT;

--
-- AUTO_INCREMENT for table `resep`
--
ALTER TABLE `resep`
  MODIFY `ID_Resep` int(11) NOT NULL AUTO_INCREMENT;

--
-- Constraints for dumped tables
--

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
