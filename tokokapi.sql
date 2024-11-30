-- phpMyAdmin SQL Dump
-- version 5.2.1
-- https://www.phpmyadmin.net/
--
-- Host: 127.0.0.1
-- Generation Time: Nov 30, 2024 at 09:59 AM
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
-- Database: `tokokapi`
--

-- --------------------------------------------------------

--
-- Table structure for table `pelanggan`
--

CREATE TABLE `pelanggan` (
  `Nama` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `No_HP` varchar(15) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Email` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Alamat` text CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Kecamatan` enum('Andir','Astana Anyar','Antapani','Arcamanik','Babakan CIparay','Bandung Kidul','Bandung Kulon','Bandung Wetan','Batununggal','Bojongloa Kaler','Bojongloa Kidul','Buah Batu','Cibeunying Kaler','Cibeunying Kidul','Cibiru','Cicendo','Cidadap','Cinambo','Coblong','GedeBage','KiaraCondong','Lengkong','Mandalajati','Pangyileukan','Rancasari','Regol','Sukajadi','Sukasari','Sumur Bandung','Ujung Berung') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Kelurahan` enum('Ciroyom','Garuda','Kebon Jeruk','Maleber','Dungus Cariang','Cempaka','Karasak','Nyengseret','Panjunan','Pelindung Hewan','Cisaranten Bina Harapan','Antapani Kidul','Antapani Tengah','Antapani Wetan','Sukamiskin','Cisaranten Kulon','Cisantren Endah','Cisantren Kidul','Cijawura','Derwati','Manjahlega','Margasari','Margahayu Tengah','Ciateul','Balonggede','Paledang','Ancol','Karang Anyar','Dago','Pasteur','Cipedes','Sukabungah','Sukarasa','Isola','Geger Kalong','SariJadi','Babakan Ciamis','Merdeka','Kebon Pisang','Braga','Padasuka','Pasanggrahan','Cigending','Cijawura') CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Username` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Password` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

-- --------------------------------------------------------

--
-- Table structure for table `pemilik`
--

CREATE TABLE `pemilik` (
  `Id_Pemilik` int(11) NOT NULL,
  `Nama_Pemilik` varchar(100) CHARACTER SET utf8 COLLATE utf8_general_ci NOT NULL,
  `Username_Pemilik` varchar(50) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL,
  `Password_Pemilik` varchar(255) CHARACTER SET utf8 COLLATE utf8_general_ci DEFAULT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `pemilik`
--

INSERT INTO `pemilik` (`Id_Pemilik`, `Nama_Pemilik`, `Username_Pemilik`, `Password_Pemilik`) VALUES
(1, 'Audric Aurelius', 'audric', '12345678');

-- --------------------------------------------------------

--
-- Table structure for table `produk`
--

CREATE TABLE `produk` (
  `Id_Produk` int(11) NOT NULL,
  `Nama_Produk` varchar(255) NOT NULL,
  `Warna_Produk` varchar(50) NOT NULL,
  `Harga_Produk` decimal(10,2) NOT NULL,
  `Material_Produk` varchar(255) NOT NULL,
  `Stok_Produk` int(11) NOT NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_general_ci;

--
-- Dumping data for table `produk`
--

INSERT INTO `produk` (`Id_Produk`, `Nama_Produk`, `Warna_Produk`, `Harga_Produk`, `Material_Produk`, `Stok_Produk`) VALUES
(1, 'Kursi', 'biru', 700000.00, 'kayu', 11),
(2, 'Meja', 'coklat', 200000.00, 'kayu', 10);

--
-- Indexes for dumped tables
--

--
-- Indexes for table `pelanggan`
--
ALTER TABLE `pelanggan`
  ADD PRIMARY KEY (`Nama`),
  ADD KEY `Username` (`Username`);

--
-- Indexes for table `pemilik`
--
ALTER TABLE `pemilik`
  ADD PRIMARY KEY (`Id_Pemilik`) USING BTREE,
  ADD KEY `Username` (`Nama_Pemilik`) USING BTREE;

--
-- Indexes for table `produk`
--
ALTER TABLE `produk`
  ADD PRIMARY KEY (`Id_Produk`) USING BTREE,
  ADD KEY `Id_Produk` (`Nama_Produk`) USING BTREE;
COMMIT;

/*!40101 SET CHARACTER_SET_CLIENT=@OLD_CHARACTER_SET_CLIENT */;
/*!40101 SET CHARACTER_SET_RESULTS=@OLD_CHARACTER_SET_RESULTS */;
/*!40101 SET COLLATION_CONNECTION=@OLD_COLLATION_CONNECTION */;
