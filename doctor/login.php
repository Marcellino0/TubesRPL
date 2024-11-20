<?php
session_start();
require_once('../config/db_connection.php');

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectDB();
    $username = mysqli_real_escape_string($conn, $_POST['username']);
    $password = mysqli_real_escape_string($conn, $_POST['password']);
    $user_type = $_POST['user_type'];
    
    // Hash password
    $hashed_password = hash('sha256', $password);
    
    // Determine table based on user type
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

    // Check credentials
    $sql = "SELECT ID_" . ucfirst($user_type) . " as user_id, Nama 
            FROM $table 
            WHERE Username = '$username' AND Password = '$hashed_password'";

    $result = mysqli_query($conn, $sql);
    
    if(!$result) {
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login";
        header("Location: login.php");
        exit();
    }

    if($row = mysqli_fetch_assoc($result)) {
        // Set session variables
        $_SESSION['user_id'] = $row['user_id'];
        $_SESSION['username'] = $username;
        $_SESSION['nama'] = $row['Nama'];
        $_SESSION['user_type'] = $user_type;
        
        // Redirect to appropriate dashboard
        header("Location: $redirect");
        exit();
    } else {
        $_SESSION['error'] = "Username atau password salah";
        header("Location: login.php");
        exit();
    }

    mysqli_close($conn);
} else {
    header("Location: login.php");
    exit();
}
?>