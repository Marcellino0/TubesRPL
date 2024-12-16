<?php
require_once('../config/db_connection.php');

if (isset($_GET['spesialis'])) {
    $spesialis = $_GET['spesialis'];
    // Query untuk mengambil data dokter berdasarkan spesialisasi
    $stmt = $conn->prepare("SELECT ID_Dokter, Nama FROM Dokter WHERE Spesialis = ?");
    $stmt->bind_param("s", $spesialis);
    $stmt->execute();
    $result = $stmt->get_result();
    $doctors = $result->fetch_all(MYSQLI_ASSOC);
    
    header('Content-Type: application/json');
    echo json_encode($doctors);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Missing specialization parameter']);
}