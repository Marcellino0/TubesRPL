<?php
session_start();
require_once('../config/db_connection.php');


function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; 
    $user_type = $_POST['user_type'];
    
   
    if($user_type !== 'dokter') {
        $_SESSION['error'] = "Tipe pengguna tidak valid";
        header("Location: ../index.php");
        exit();
    }

    try {
      
        $sql = "SELECT ID_Dokter, Username, Password, Nama, Spesialis, Jadwal_Praktik 
                FROM dokter 
                WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
          
            if ($password === $row['Password']) {
              
                $_SESSION['user_id'] = $row['ID_Dokter'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['spesialis'] = $row['Spesialis'];
                $_SESSION['jadwal_praktik'] = $row['Jadwal_Praktik'];
                $_SESSION['user_type'] = 'dokter';
                
             
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
  
    header("Location: ../index.php");
    exit();
}
?>