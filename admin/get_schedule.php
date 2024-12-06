<?php
require_once('../config/db_connection.php');

header('Content-Type: application/json');

if (!isset($_GET['doctor_id'])) {
    http_response_code(400);
    echo json_encode(['error' => 'Doctor ID is required']);
    exit;
}

$doctorId = $_GET['doctor_id'];
$registrationDay = $_GET['registration_day'] ?? 'today';

try {
    $query = "
    SELECT 
        jd.*,
        (SELECT COUNT(*) 
         FROM Pendaftaran 
         WHERE ID_Jadwal = jd.ID_Jadwal 
         AND DATE(Waktu_Daftar) = CURDATE()) as used_quota_offline_today
    FROM Jadwal_Dokter jd
    WHERE jd.ID_Dokter = ? 
    AND jd.Status = 'Aktif'
    ORDER BY 
        FIELD(jd.Hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu'),
        jd.Jam_Mulai
";
    
    $stmt = $conn->prepare($query);
    $stmt->bind_param("i", $doctorId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $schedules = [];
    while ($row = $result->fetch_assoc()) {
        // Format times for display
        $row['Jam_Mulai'] = date('H:i', strtotime($row['Jam_Mulai']));
        $row['Jam_Selesai'] = date('H:i', strtotime($row['Jam_Selesai']));
        $schedules[] = $row;
    }
    
    echo json_encode($schedules);
    
} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);
}
?>