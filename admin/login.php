<?php
session_start();
require_once('../config/db_connection.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectDB();
    $username = sanitize($_POST['username']);
    $password = sanitize($_POST['password']);
    $user_type = $_POST['user_type'];
    

    $hashed_password = hash('sha256', $password);
    
  
    switch($user_type) {
        case 'admin':
            $table = 'Administrator';
            $redirect = 'admin_dashboard.php';
            break;
        case 'dokter':
            $table = 'Dokter';
            $redirect = 'doctor_dashboard.php';
            break;
        case 'perawat':
            $table = 'Perawat';
            $redirect = 'nurse_dashboard.php';
            break;
        case 'pasien':
            $table = 'Pasien';
            $redirect = 'patient_dashboard.php';
            break;
        default:
            $_SESSION['error'] = "Tipe pengguna tidak valid";
            header("Location: login.php");
            exit();
    }

 
    $sql = "SELECT ID_" . ucfirst($user_type) . " as user_id, Nama 
            FROM $table 
            WHERE Username = '$username' AND Password = '$hashed_password'";

    $result = sqlsrv_query($conn, $sql);
    
    if($result === false) {
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login";
        header("Location: login.php");
        exit();
    }

    if($row = sqlsrv_fetch_array($result)) {
       
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['nama'] = $row['Nama'];
        $_SESSION['user_type'] = $user_type;
        
     
        header("Location: $redirect");
        exit();
    } else {
        $_SESSION['error'] = "Username atau password salah";
        header("Location: login.php");
        exit();
    }

    closeDB($conn);
} else {
    header("Location: login.php");
    exit();
}
?>