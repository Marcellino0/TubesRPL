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
    
    
    if($user_type !== 'admin') {
        $_SESSION['error'] = "Tipe pengguna tidak valid";
        header("Location: ../index.php");
        exit();
    }

    try {
       
        $sql = "SELECT ID_Admin, Username, Password, Nama 
                FROM administrator 
                WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
           
            if ($password === $row['Password']) {
              
                $_SESSION['user_id'] = $row['ID_Admin'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['user_type'] = 'admin';
                
               
                header("Location: admin_dashboard.php");
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