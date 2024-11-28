<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as doctor
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: ../login.php");
    exit();
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: doctor_dashboard.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];
$id_pendaftaran = $_POST['id_pendaftaran'];
$diagnosa = $_POST['diagnosa'];
$resep = $_POST['resep'];

try {
    // Start transaction
    $conn->begin_transaction();

    // Insert into Pemeriksaan
    $sql_pemeriksaan = "INSERT INTO Pemeriksaan (ID_Pendaftaran, ID_Dokter, Waktu_Periksa, Diagnosa) 
                        VALUES (?, ?, NOW(), ?)";
    $stmt = $conn->prepare($sql_pemeriksaan);
    $stmt->bind_param("iis", $id_pendaftaran, $dokter_id, $diagnosa);
    $stmt->execute();
    
    $id_pemeriksaan = $conn->insert_id;

    // Insert into Resep
    $sql_resep = "INSERT INTO Resep (ID_Pemeriksaan, Resep_Obat, Tanggal) 
                  VALUES (?, ?, CURDATE())";
    $stmt = $conn->prepare($sql_resep);
    $stmt->bind_param("is", $id_pemeriksaan, $resep);
    $stmt->execute();

    // Update status pendaftaran
    $sql_update = "UPDATE Pendaftaran SET Status = 'Selesai' WHERE ID_Pendaftaran = ?";
    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param("i", $id_pendaftaran);
    $stmt->execute();

    // Commit transaction
    $conn->commit();

    // Set success message
    $_SESSION['success_message'] = "Pemeriksaan berhasil disimpan";
    header("Location: doctor_dashboard.php");
    exit();

} catch (Exception $e) {
    // Rollback transaction on error
    $conn->rollback();
    
    // Set error message
    $_SESSION['error_message'] = "Terjadi kesalahan: " . $e->getMessage();
    header("Location: examine_patient.php?id=" . $id_pendaftaran);
    exit();
}
?>