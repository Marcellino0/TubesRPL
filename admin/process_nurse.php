<?php
// Memulai sesi pengguna
session_start();

// Menyertakan koneksi ke database
require_once('db_connection.php');

// Memeriksa apakah pengguna yang sedang login adalah admin, jika tidak, arahkan ke halaman login
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Menghubungkan ke database
$conn = connectDB();

// Memproses permintaan POST (untuk menambah atau mengupdate data perawat)
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil data dari form dan membersihkannya
    $nama = sanitize($_POST['nama']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $action = $_POST['action'];

    // Memulai transaksi SQL
    sqlsrv_begin_transaction($conn);

    try {
        // Memeriksa apakah username sudah ada (kecuali untuk perawat yang sedang diperbarui)
        $perawat_id = isset($_POST['perawat_id']) ? intval($_POST['perawat_id']) : 0;
        $sql = "SELECT ID_Perawat FROM Perawat 
                WHERE Username = '$username' 
                AND ID_Perawat != $perawat_id"; // Mengabaikan perawat yang sedang diperbarui
        
        // Menjalankan query untuk memeriksa username
        $result = sqlsrv_query($conn, $sql);
        
        // Jika username sudah ada, lemparkan exception
        if(sqlsrv_has_rows($result)) {
            throw new Exception("Username sudah digunakan");
        }

        // Proses untuk menambah data perawat baru
        if($action == 'add') {
            // Memeriksa apakah password diisi
            if(empty($password)) {
                throw new Exception("Password harus diisi untuk perawat baru");
            }

            // Melakukan hash pada password
            $hashed_password = hash('sha256', $password);

            // Menyimpan data perawat baru
            $sql = "INSERT INTO Perawat (Nama, Username, Password)
                    VALUES ('$nama', '$username', '$hashed_password')";
            
            // Pesan sukses untuk penambahan
            $message = "Data perawat berhasil ditambahkan";
        } else {
            // Proses untuk memperbarui data perawat yang sudah ada
            $sql = "UPDATE Perawat SET 
                    Nama = '$nama',
                    Username = '$username'";
            
            // Hanya update password jika ada password baru yang dimasukkan
            if(!empty($password)) {
                $hashed_password = hash('sha256', $password);
                $sql .= ", Password = '$hashed_password'";
            }
            
            // Menentukan perawat yang akan diperbarui
            $sql .= " WHERE ID_Perawat = $perawat_id";
            
            // Pesan sukses untuk pembaruan
            $message = "Data perawat berhasil diupdate";
        }

        // Menjalankan query untuk menyimpan data
        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Gagal menyimpan data perawat");
        }

        // Jika semua berhasil, commit transaksi
        sqlsrv_commit($conn);
        $_SESSION['success'] = $message;

    } catch(Exception $e) {
        // Jika terjadi kesalahan, rollback transaksi
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    // Memproses permintaan GET untuk menghapus perawat
    $perawat_id = intval($_GET['id']);

    // Memulai transaksi SQL
    sqlsrv_begin_transaction($conn);

    try {
        // Memeriksa apakah perawat memiliki rekam medis
        $sql = "SELECT COUNT(*) as rekam_medis 
                FROM Rekam_Medis 
                WHERE ID_Perawat = $perawat_id";
        
        // Menjalankan query untuk memeriksa rekam medis perawat
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        // Jika perawat memiliki rekam medis, tidak bisa dihapus
        if($row['rekam_medis'] > 0) {
            throw new Exception("Tidak dapat menghapus perawat yang memiliki riwayat pemeriksaan");
        }

        // Menghapus data perawat jika tidak ada rekam medis
        $sql = "DELETE FROM Perawat WHERE ID_Perawat = $perawat_id";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Gagal menghapus data perawat");
        }

        // Jika semua berhasil, commit transaksi
        sqlsrv_commit($conn);
        $_SESSION['success'] = "Data perawat berhasil dihapus";

    } catch(Exception $e) {
        // Jika terjadi kesalahan, rollback transaksi
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();
    }
}

// Menutup koneksi ke database
closeDB($conn);

// Mengarahkan kembali ke halaman daftar perawat
header("Location: manage_nurses.php");
exit();
?>
