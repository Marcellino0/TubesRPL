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
    $spesialis = sanitize($_POST['spesialis']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $action = $_POST['action'];


    sqlsrv_begin_transaction($conn);

    try {
       
        $dokter_id = isset($_POST['dokter_id']) ? intval($_POST['dokter_id']) : 0;
        $sql = "SELECT ID_Dokter FROM Dokter 
                WHERE Username = '$username' 
                AND ID_Dokter != $dokter_id";
        
        $result = sqlsrv_query($conn, $sql);
        
        if(sqlsrv_has_rows($result)) {
            throw new Exception("Username sudah digunakan");
        }

        if($action == 'add') {
            if(empty($password)) {
                throw new Exception("Password harus diisi untuk dokter baru");
            }

            // Hash password
            $hashed_password = hash('sha256', $password);

        
            $sql = "INSERT INTO Dokter (Nama, Spesialis, Username, Password)
                    VALUES ('$nama', '$spesialis', '$username', '$hashed_password')";
            
            $message = "Data dokter berhasil ditambahkan";
        } else {
         
            $sql = "UPDATE Dokter SET 
                    Nama = '$nama',
                    Spesialis = '$spesialis',
                    Username = '$username'";
            
       
            if(!empty($password)) {
                $hashed_password = hash('sha256', $password);
                $sql .= ", Password = '$hashed_password'";
            }
            
            $sql .= " WHERE ID_Dokter = $dokter_id";
            
            $message = "Data dokter berhasil diupdate";
        }

        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Gagal menyimpan data dokter");
        }

        sqlsrv_commit($conn);
        $_SESSION['success'] = $message;

    } catch(Exception $e) {
       
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    $dokter_id = intval($_GET['id']);


    sqlsrv_begin_transaction($conn);

    try {
     
        $sql = "SELECT COUNT(*) as jadwal FROM Jadwal_Dokter WHERE ID_Dokter = $dokter_id";
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        if($row['jadwal'] > 0) {
            throw new Exception("Tidak dapat menghapus dokter yang masih memiliki jadwal");
        }

      
        $sql = "SELECT COUNT(*) as pemeriksaan 
                FROM Pemeriksaan pem
                JOIN Pendaftaran p ON pem.ID_Pendaftaran = p.ID_Pendaftaran
                JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                WHERE j.ID_Dokter = $dokter_id";
        
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        if($row['pemeriksaan'] > 0) {
            throw new Exception("Tidak dapat menghapus dokter yang memiliki riwayat pemeriksaan");
        }

        // Delete doctor
        $sql = "DELETE FROM Dokter WHERE ID_Dokter = $dokter_id";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Gagal menghapus data dokter");
        }

  
        sqlsrv_commit($conn);
        $_SESSION['success'] = "Data dokter berhasil dihapus";

    } catch(Exception $e) {
      
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}

closeDB($conn);
header("Location: manage_doctors.php");
exit();
?>