<?php
require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

// Memeriksa apakah parameter 'spesialis' ada di URL query string
if (isset($_GET['spesialis'])) {
    $spesialis = $_GET['spesialis']; // Menyimpan nilai spesialis dari query string

    // Menyiapkan query untuk mengambil ID Dokter dan Nama dari tabel Dokter berdasarkan spesialisasi
    $stmt = $conn->prepare("SELECT ID_Dokter, Nama FROM Dokter WHERE Spesialis = ?");
    $stmt->bind_param("s", $spesialis); // Mengikat parameter spesialis ke query
    $stmt->execute(); // Mengeksekusi query
    $result = $stmt->get_result(); // Mengambil hasil eksekusi query

    $doctors = $result->fetch_all(MYSQLI_ASSOC); // Menyimpan hasil query dalam bentuk array asosiatif
    
    header('Content-Type: application/json'); // Menentukan tipe konten sebagai JSON
    echo json_encode($doctors); // Mengirimkan data dokter dalam format JSON

} else {
    http_response_code(400); // Mengirim status kode 400 (Bad Request) jika parameter spesialis tidak ditemukan
    echo json_encode(['error' => 'Missing specialization parameter']); // Mengirimkan pesan error dalam format JSON
}
