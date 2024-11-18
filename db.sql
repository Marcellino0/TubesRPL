-- Create Database
CREATE DATABASE IF NOT EXISTS PoliklinikX;
USE PoliklinikX;

-- Create Tables
CREATE TABLE Administrator (
    ID_Admin INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Nama VARCHAR(100) NOT NULL
);

CREATE TABLE Dokter (
    ID_Dokter INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Nama VARCHAR(100) NOT NULL,
    Spesialis VARCHAR(50) NOT NULL,
    Jadwal_Praktik VARCHAR(255)
);

CREATE TABLE Perawat (
    ID_Perawat INT PRIMARY KEY AUTO_INCREMENT,
    Username VARCHAR(50) NOT NULL UNIQUE,
    Password VARCHAR(255) NOT NULL,
    Nama VARCHAR(100) NOT NULL
);

CREATE TABLE Jadwal_Dokter (
    ID_Jadwal INT PRIMARY KEY AUTO_INCREMENT,
    ID_Dokter INT,
    Jam_Mulai TIME NOT NULL,
    Jam_Selesai TIME NOT NULL,
    Kuota INT NOT NULL,
    Hari VARCHAR(20) NOT NULL,
    FOREIGN KEY (ID_Dokter) REFERENCES Dokter(ID_Dokter)
);

CREATE TABLE Pasien (
    ID_Pasien INT PRIMARY KEY AUTO_INCREMENT,
    Nama VARCHAR(100) NOT NULL,
    Tanggal_Lahir DATE NOT NULL,
    Username VARCHAR(50) UNIQUE,
    Password VARCHAR(255),
    Email VARCHAR(100),
    Nomor_Rekam_Medis VARCHAR(20) UNIQUE NOT NULL,
    Umur INT
);

ALTER TABLE Pasien 
ADD COLUMN Alamat TEXT AFTER Email,
ADD COLUMN Jenis_Kelamin ENUM('Laki-laki', 'Perempuan') AFTER Tanggal_Lahir,
ADD COLUMN No_HP VARCHAR(15) AFTER Email,
ADD COLUMN NIK VARCHAR(16) AFTER Nama;

CREATE TABLE Pendaftaran (
    ID_Pendaftaran INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pasien INT,
    ID_Jadwal INT,
    Waktu_Daftar DATETIME NOT NULL,
    No_Antrian INT NOT NULL,
    Status VARCHAR(20) DEFAULT 'Menunggu',
    FOREIGN KEY (ID_Pasien) REFERENCES Pasien(ID_Pasien),
    FOREIGN KEY (ID_Jadwal) REFERENCES Jadwal_Dokter(ID_Jadwal)
);
ALTER TABLE Pendaftaran ADD INDEX idx_status_waktu (Status, Waktu_Daftar);

CREATE TABLE Rekam_Medis (
    ID_Rekam INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pasien INT,
    Tekanan_Darah VARCHAR(20),
    Tinggi_Badan DECIMAL(5,2),
    Berat_Badan DECIMAL(5,2),
    Suhu DECIMAL(4,1),
    Riwayat_Penyakit LONGTEXT,
    Tanggal DATE NOT NULL,
    FOREIGN KEY (ID_Pasien) REFERENCES Pasien(ID_Pasien)
);

CREATE TABLE Pemeriksaan (
    ID_Pemeriksaan INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pendaftaran INT,
    ID_Dokter INT,
    Waktu_Periksa DATETIME NOT NULL,
    Diagnosa LONGTEXT NOT NULL,
    FOREIGN KEY (ID_Pendaftaran) REFERENCES Pendaftaran(ID_Pendaftaran),
    FOREIGN KEY (ID_Dokter) REFERENCES Dokter(ID_Dokter)
);

CREATE TABLE Resep (
    ID_Resep INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pemeriksaan INT,
    Resep_Obat LONGTEXT NOT NULL,
    Tanggal DATE NOT NULL,
    FOREIGN KEY (ID_Pemeriksaan) REFERENCES Pemeriksaan(ID_Pemeriksaan)
);

CREATE TABLE Pembayaran (
    ID_Pembayaran INT PRIMARY KEY AUTO_INCREMENT,
    ID_Pendaftaran INT,
    Tanggal DATETIME NOT NULL,
    Jumlah DECIMAL(10,2) NOT NULL,
    Metode VARCHAR(50) NOT NULL,
    Status VARCHAR(20) DEFAULT 'Belum Lunas',
    FOREIGN KEY (ID_Pendaftaran) REFERENCES Pendaftaran(ID_Pendaftaran)
);
ALTER TABLE Pembayaran ADD COLUMN Keterangan TEXT AFTER Status;

-- Create Indexes
CREATE INDEX IX_Pasien_Username ON Pasien(Username);
CREATE INDEX IX_Dokter_Spesialis ON Dokter(Spesialis);
CREATE INDEX IX_Pendaftaran_Tanggal ON Pendaftaran(Waktu_Daftar);
CREATE INDEX IX_Pembayaran_Status ON Pembayaran(Status);

-- Insert Sample Data
INSERT INTO Administrator (Username, Password, Nama)
VALUES ('admin', SHA2('admin123', 256), 'Administrator Utama');

INSERT INTO Dokter (Username, Password, Nama, Spesialis, Jadwal_Praktik)
VALUES 
('dr.anton', SHA2('dokter123', 256), 'Dr. Anton', 'Penyakit Dalam', 'Senin-Jumat'),
('dr.budi', SHA2('dokter123', 256), 'Dr. Budi', 'Paru-paru', 'Senin-Kamis'),
('dr.citra', SHA2('dokter123', 256), 'Dr. Citra', 'Mata', 'Selasa-Jumat');