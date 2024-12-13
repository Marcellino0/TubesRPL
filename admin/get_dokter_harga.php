<?php
require_once('../config/db_connection.php');

if (isset($_GET['dokter_id'])) {
    $dokterId = $_GET['dokter_id'];
    $stmt = $conn->prepare("SELECT Harga_Dokter FROM Dokter WHERE ID_Dokter = ?");
    $stmt->bind_param("i", $dokterId);
    $stmt->execute();
    $result = $stmt->get_result();
    $dokter = $result->fetch_assoc();

    if ($dokter) {
        echo json_encode(['harga' => $dokter['Harga_Dokter']]);
    } else {
        echo json_encode(['harga' => 0]);
    }
}
?>
