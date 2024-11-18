<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: login.php");
    exit();
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectDB();
    
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $pasien_id = $_POST['pasien_id'];
    $tekanan_darah = sanitize($_POST['tekanan_darah']);
    $tinggi_badan = floatval($_POST['tinggi_badan']);
    $berat_badan = floatval($_POST['berat_badan']);
    $suhu = floatval($_POST['suhu']);
    $riwayat_penyakit = sanitize($_POST['riwayat_penyakit']);
    $keluhan = sanitize($_POST['keluhan']);
    $tanggal = date('Y-m-d H:i:s');

    // Begin transaction
    sqlsrv_begin_transaction($conn);
    
    try {
        // Insert rekam medis
        $sql = "INSERT INTO Rekam_Medis (ID_Pasien, Tekanan_Darah, Tinggi_Badan, 
                Berat_Badan, Suhu, Riwayat_Penyakit, Tanggal)
                VALUES ($pasien_id, '$tekanan_darah', $tinggi_badan, 
                $berat_badan, $suhu, '$riwayat_penyakit', '$tanggal')";
        
        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Error saving medical record");
        }

        // Update status pendaftaran
        $sql = "UPDATE Pendaftaran SET Status = 'Diperiksa' 
                WHERE ID_Pendaftaran = $pendaftaran_id";
        
        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Error updating registration status");
        }

        // Commit transaction
        sqlsrv_commit($conn);
        
        $_SESSION['success'] = "Tanda vital berhasil disimpan";
        header("Location: nurse_dashboard.php");
        exit();

    } catch(Exception $e) {
        // Rollback transaction on error
        sqlsrv_rollback($conn);
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
        header("Location: record_vital_signs.php?id=" . $pendaftaran_id);
        exit();
    }

    closeDB($conn);
} else {
    header("Location: nurse_dashboard.php");
    exit();
}
?>