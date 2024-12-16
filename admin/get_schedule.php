<?php
require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

header('Content-Type: application/json'); // Mengatur header respons untuk tipe konten JSON

// Memeriksa apakah parameter 'doctor_id' ada dalam query string URL
if (!isset($_GET['doctor_id'])) {
    // Jika tidak ada, mengembalikan error 400 (Bad Request) dengan pesan 'Doctor ID is required'
    http_response_code(400);
    echo json_encode(['error' => 'Doctor ID is required']);
    exit; // Menghentikan eksekusi skrip lebih lanjut
}

// Mengambil parameter doctor_id dari query string
$doctorId = $_GET['doctor_id'];

// Menyediakan nilai default untuk registration_day, yaitu 'today' jika tidak ada
$registrationDay = $_GET['registration_day'] ?? 'today';

try {
    // Query untuk mengambil jadwal dokter dan kuota yang digunakan (offline dan online) berdasarkan doctor_id
    $query = "
    SELECT 
        jd.*, 
        (SELECT COUNT(*) FROM Pendaftaran 
         WHERE ID_Jadwal = jd.ID_Jadwal 
         AND DATE(Waktu_Daftar) = CURDATE()
         AND Verifikasi = 'Terverifikasi') as used_quota_total,
        (SELECT COUNT(*) FROM Pendaftaran p
         JOIN Pasien ps ON p.ID_Pasien = ps.ID_Pasien 
         WHERE p.ID_Jadwal = jd.ID_Jadwal 
         AND DATE(p.Waktu_Daftar) = CURDATE()
         AND ps.Registration_Type = 'offline'
         AND p.Verifikasi = 'Terverifikasi') as used_quota_offline_today,
        (SELECT COUNT(*) FROM Pendaftaran p
         JOIN Pasien ps ON p.ID_Pasien = ps.ID_Pasien 
         WHERE p.ID_Jadwal = jd.ID_Jadwal 
         AND DATE(p.Waktu_Daftar) = CURDATE()
         AND ps.Registration_Type = 'online'
         AND p.Verifikasi = 'Terverifikasi') as used_quota_online_today
    FROM Jadwal_Dokter jd 
    WHERE jd.ID_Dokter = ?
    AND jd.Status = 'Aktif'";  // Mengambil jadwal dokter yang aktif berdasarkan doctor_id

    // Menyiapkan dan mengeksekusi query dengan parameter doctor_id
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId); // Mengikat parameter doctor_id sebagai integer
    $stmt->execute(); // Menjalankan query
    $result = $stmt->get_result(); // Mengambil hasil query

    $schedules = []; // Menyimpan hasil jadwal dalam array
    while ($row = $result->fetch_assoc()) {
        // Format waktu (Jam Mulai dan Jam Selesai) agar sesuai dengan format 'H:i'
        $row['Jam_Mulai'] = date('H:i', strtotime($row['Jam_Mulai']));
        $row['Jam_Selesai'] = date('H:i', strtotime($row['Jam_Selesai']));
        $schedules[] = $row; // Menambahkan data jadwal ke dalam array schedules
    }

    // Mengembalikan hasil jadwal dalam format JSON
    echo json_encode($schedules);

} catch (Exception $e) {
    // Jika terjadi kesalahan dalam eksekusi query, mengembalikan error 500 (Internal Server Error)
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>
