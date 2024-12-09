<?php
require_once('../config/db_connection.php');

if(!isset($_GET['spesialis'])) {
    http_response_code(400);
    exit('Missing spesialis parameter');
}

try {
    // Get available schedules for today with remaining quota
    $query = "
        SELECT 
            j.ID_Jadwal,
            d.Nama as Nama_Dokter,
            j.Hari,
            j.Jam_Mulai,
            j.Jam_Selesai,
            j.Kuota_Offline,
            (SELECT COUNT(*) 
             FROM Pendaftaran p 
             WHERE p.ID_Jadwal = j.ID_Jadwal 
             AND DATE(p.Waktu_Daftar) = CURDATE()) as used_quota,
            (j.Kuota_Offline - (SELECT COUNT(*) 
                               FROM Pendaftaran p 
                               WHERE p.ID_Jadwal = j.ID_Jadwal 
                               AND DATE(p.Waktu_Daftar) = CURDATE())) as available_quota
        FROM Jadwal_Dokter j
        JOIN Dokter d ON j.ID_Dokter = d.ID_Dokter
        WHERE d.Spesialis = ? 
        AND j.Hari = DAYNAME(CURDATE())
        AND j.Status = 'Aktif'
        HAVING available_quota > 0
        ORDER BY j.Jam_Mulai";

    $stmt = $conn->prepare($query);
    $stmt->bind_param("s", $_GET['spesialis']);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        // Format times for display
        $row['Jam_Mulai'] = date('H:i', strtotime($row['Jam_Mulai']));
        $row['Jam_Selesai'] = date('H:i', strtotime($row['Jam_Selesai']));
        $schedules[] = $row;
    }
    
    header('Content-Type: application/json');
    echo json_encode($schedules);

} catch(Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => $e->getMessage()]);
}
