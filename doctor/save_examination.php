<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectDB();
    $dokter_id = $_SESSION['user_id'];
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $diagnosa = sanitize($_POST['diagnosa']);
    $resep = sanitize($_POST['resep']);
    $waktu_periksa = date('Y-m-d H:i:s');

    // Begin transaction
    sqlsrv_begin_transaction($conn);
    try {
        // Insert pemeriksaan
        $sql = "INSERT INTO Pemeriksaan (ID_Pendaftaran, ID_Dokter, Waktu_Periksa, Diagnosa)
                VALUES ($pendaftaran_id, $dokter_id, '$waktu_periksa', '$diagnosa')";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Error saving examination");
        }

        // Get the ID_Pemeriksaan
        $sql = "SELECT SCOPE_IDENTITY() as ID_Pemeriksaan";
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        $pemeriksaan_id = $row['ID_Pemeriksaan'];

        // Insert resep
        $sql = "INSERT INTO Resep (ID_Pemeriksaan, Resep_Obat, Tanggal)
                VALUES ($pemeriksaan_id, '$resep', '$waktu_periksa')";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Error saving prescription");
        }

        // Update status pendaftaran
        $sql = "UPDATE Pendaftaran SET Status = 'Selesai' WHERE ID_Pendaftaran = $pendaftaran_id";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Error updating registration status");
        }

        // Create pembayaran record
        $sql = "INSERT INTO Pembayaran (ID_Pendaftaran, Tanggal, Jumlah, Metode, Status)
                VALUES ($pendaftaran_id, '$waktu_periksa', 0, 'Belum Dibayar', 'Belum Lunas')";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Error creating payment record");
        }

        // Commit transaction
        sqlsrv_commit($conn);
        
        $_SESSION['success'] = "Pemeriksaan berhasil disimpan";
        header("Location: doctor_dashboard.php");
        exit();

    } catch(Exception $e) {
        // Rollback transaction on error
        sqlsrv_rollback($conn);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: examine_patient.php?id=" . $pendaftaran_id);
        exit();
    }

    closeDB($conn);
} else {
    header("Location: doctor_dashboard.php");
    exit();
}
?>