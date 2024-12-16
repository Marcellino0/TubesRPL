<?php
require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

// Memeriksa apakah parameter 'spesialis' ada di URL query string
if (!isset($_GET['spesialis'])) {
    http_response_code(400); // Mengirim status kode 400 (Bad Request) jika parameter tidak ditemukan
    exit('Missing spesialis parameter'); // Menyudahi eksekusi dan menampilkan pesan error
}

try {
    // Query untuk mendapatkan jadwal yang tersedia untuk hari ini dan kuota yang tersisa
    $query = "
        SELECT 
            j.ID_Jadwal, -- ID jadwal
            d.Nama as Nama_Dokter, -- Nama dokter
            j.Hari, -- Hari jadwal
            j.Jam_Mulai, -- Jam mulai jadwal
            j.Jam_Selesai, -- Jam selesai jadwal
            j.Kuota_Offline, -- Kuota offline
            (SELECT COUNT(*) 
             FROM Pendaftaran p 
             WHERE p.ID_Jadwal = j.ID_Jadwal 
             AND DATE(p.Waktu_Daftar) = CURDATE()) as used_quota, -- Menghitung jumlah pendaftaran yang sudah dilakukan untuk jadwal ini hari ini
            (j.Kuota_Offline - (SELECT COUNT(*) 
                               FROM Pendaftaran p 
                               WHERE p.ID_Jadwal = j.ID_Jadwal 
                               AND DATE(p.Waktu_Daftar) = CURDATE())) as available_quota -- Menghitung kuota yang masih tersedia
        FROM Jadwal_Dokter j
        JOIN Dokter d ON j.ID_Dokter = d.ID_Dokter -- Menghubungkan jadwal dengan dokter
        WHERE d.Spesialis = ? -- Filter berdasarkan spesialisasi dokter
        AND j.Hari = DAYNAME(CURDATE()) -- Filter berdasarkan hari ini
        AND j.Status = 'Aktif' -- Hanya jadwal yang aktif
        HAVING available_quota > 0 -- Menampilkan jadwal yang masih memiliki kuota tersedia
        ORDER BY j.Jam_Mulai"; // Mengurutkan hasil berdasarkan jam mulai

    // Menyiapkan dan mengeksekusi query dengan parameter spesialis
    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_GET['spesialis']); // Mengikat parameter 'spesialis' dari query string
    $stmt->execute(); // Mengeksekusi query
    $result = $stmt->get_result(); // Mengambil hasil eksekusi query

    $schedules = []; // Array untuk menyimpan hasil jadwal
    while ($row = $result->fetch_assoc()) {
        // Format jam mulai dan jam selesai ke format 'H:i' untuk tampilan
        $row['Jam_Mulai'] = date('H:i', strtotime($row['Jam_Mulai']));
        $row['Jam_Selesai'] = date('H:i', strtotime($row['Jam_Selesai']));
        $schedules[] = $row; // Menambahkan data jadwal ke array
    }

    header('Content-Type: application/json'); // Menentukan tipe konten sebagai JSON
    echo json_encode($schedules); // Mengirimkan data jadwal dalam format JSON

} catch(Exception $e) {
    // Jika terjadi error dalam proses query atau eksekusi lainnya
    http_response_code(500); // Mengirim status kode 500 (Internal Server Error)
    echo json_encode(['error' => $e->getMessage()]); // Menampilkan pesan error dalam format JSON
}
