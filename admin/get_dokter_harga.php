<?php
require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

// Memeriksa apakah parameter 'dokter_id' ada dalam query string URL
if (isset($_GET['dokter_id'])) {
    $dokterId = $_GET['dokter_id']; // Menyimpan nilai ID Dokter dari parameter query string

    // Menyiapkan query untuk mengambil Harga_Dokter berdasarkan ID Dokter yang diberikan
    $stmt = $conn->prepare("SELECT Harga_Dokter FROM Dokter WHERE ID_Dokter = ?");
    $stmt->bind_param("i", $dokterId); // Mengikat parameter ID Dokter ke query sebagai integer
    $stmt->execute(); // Mengeksekusi query
    $result = $stmt->get_result(); // Mengambil hasil query

    $dokter = $result->fetch_assoc(); // Mengambil hasil pertama sebagai array asosiatif

    if ($dokter) { 
        // Jika data dokter ditemukan, mengirimkan harga dokter dalam format JSON
        echo json_encode(['harga' => $dokter['Harga_Dokter']]);
    } else {
        // Jika dokter dengan ID tersebut tidak ditemukan, mengirimkan harga 0 dalam format JSON
        echo json_encode(['harga' => 0]);
    }
}
?>
