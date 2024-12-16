<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $nama = sanitize($_POST['nama']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $action = $_POST['action'];


    sqlsrv_begin_transaction($conn);

    try {
       
        $perawat_id = isset($_POST['perawat_id']) ? intval($_POST['perawat_id']) : 0;
        $sql = "SELECT ID_Perawat FROM Perawat 
                WHERE Username = '$username' 
                AND ID_Perawat != $perawat_id";
        
        $result = sqlsrv_query($conn, $sql);
        
        if(sqlsrv_has_rows($result)) {
            throw new Exception("Username sudah digunakan");
        }

        if($action == 'add') {
            if(empty($password)) {
                throw new Exception("Password harus diisi untuk perawat baru");
            }

            // Hash password
            $hashed_password = hash('sha256', $password);

          
            $sql = "INSERT INTO Perawat (Nama, Username, Password)
                    VALUES ('$nama', '$username', '$hashed_password')";
            
            $message = "Data perawat berhasil ditambahkan";
        } else {
           
            $sql = "UPDATE Perawat SET 
                    Nama = '$nama',
                    Username = '$username'";
            
         
            if(!empty($password)) {
                $hashed_password = hash('sha256', $password);
                $sql .= ", Password = '$hashed_password'";
            }
            
            $sql .= " WHERE ID_Perawat = $perawat_id";
            
            $message = "Data perawat berhasil diupdate";
        }

        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Gagal menyimpan data perawat");
        }

 
        sqlsrv_commit($conn);
        $_SESSION['success'] = $message;

    } catch(Exception $e) {
    
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $perawat_id = intval($_GET['id']);

 
    sqlsrv_begin_transaction($conn);

    try {
    
        $sql = "SELECT COUNT(*) as rekam_medis 
                FROM Rekam_Medis 
                WHERE ID_Perawat = $perawat_id";
        
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        if($row['rekam_medis'] > 0) {
            throw new Exception("Tidak dapat menghapus perawat yang memiliki riwayat pemeriksaan");
        }

      
        $sql = "DELETE FROM Perawat WHERE ID_Perawat = $perawat_id";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Gagal menghapus data perawat");
        }

        sqlsrv_commit($conn);
        $_SESSION['success'] = "Data perawat berhasil dihapus";

    } catch(Exception $e) {
    
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}

closeDB($conn);
header("Location: manage_nurses.php");
exit();
?>