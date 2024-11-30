<?php
session_start();
require_once('db_connection.php');

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $email = sanitize($_POST['email']);
    $username = sanitize($_POST['username']);
    $password = hash('sha256', $_POST['password']);
    
    // Generate nomor rekam medis
    $nomor_rekam_medis = generateRekamMedis();
    
    // Hitung umur
    $birthDate = new DateTime($tanggal_lahir);
    $today = new DateTime();
    $umur = $today->diff($birthDate)->y;
    
    $conn = connectDB();
    
    // Check if username already exists
    $checkUsername = "SELECT Username FROM Pasien WHERE Username = '$username'";
    $result = sqlsrv_query($conn, $checkUsername);
        
    if(sqlsrv_has_rows($result)) {
        $_SESSION['error'] = "Username sudah digunakan. Silakan pilih username lain.";
        header("Location: register.php");
        exit();
    }
    
    // Insert new patient
    $sql = "INSERT INTO Pasien (Nama, Tanggal_Lahir, Username, Password, Email, Nomor_Rekam_Medis, Umur) 
            VALUES ('$nama', '$tanggal_lahir', '$username', '$password', '$email', '$nomor_rekam_medis', $umur)";
    
    $result = sqlsrv_query($conn, $sql);
    
    if($result === false) {
        $_SESSION['error'] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        header("Location: register.php");
        exit();
    }
    
    $_SESSION['success'] = "Pendaftaran berhasil! Nomor Rekam Medis Anda: " . $nomor_rekam_medis;
    header("Location: login.php");
    exit();
} else {
    header("Location: register.php");
    exit();
}
?>
