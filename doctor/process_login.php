<?php
session_start();
require_once('../config/db_connection.php');

// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password as it needs to match exactly
    $user_type = $_POST['user_type'];
    
    // Verify user type is dokter
    if($user_type !== 'dokter') {
        $_SESSION['error'] = "Tipe pengguna tidak valid";
        header("Location: ../index.php");
        exit();
    }

    try {
        // Prepare and execute query to fetch doctor data with all relevant fields
        $sql = "SELECT ID_Dokter, Username, Password, Nama, Spesialis, Jadwal_Praktik 
                FROM dokter 
                WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            // For this example, assuming simple password comparison
            // In production, you should use password_verify() with hashed passwords
            if ($password === $row['Password']) {
                // Set session variables including specialist and schedule info
                $_SESSION['user_id'] = $row['ID_Dokter'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['spesialis'] = $row['Spesialis'];
                $_SESSION['jadwal_praktik'] = $row['Jadwal_Praktik'];
                $_SESSION['user_type'] = 'dokter';
                
                // Redirect to doctor dashboard
                header("Location: doctor_dashboard.php");
                exit();
            } else {
                $_SESSION['error'] = "Username atau password salah";
                header("Location: ../index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Username atau password salah";
            header("Location: ../index.php");
            exit();
        }
    } catch(Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login: " . $e->getMessage();
        header("Location: ../index.php");
        exit();
    }

} else {
    // If not POST request, redirect to login page
    header("Location: ../index.php");
    exit();
}
?>