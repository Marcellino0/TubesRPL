<?php
// Memulai sesi pengguna
session_start();

// Menyertakan koneksi ke database
require_once('db_connection.php');

// Memeriksa apakah pengguna sudah login sebagai admin
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Jika bukan admin, arahkan pengguna ke halaman login
    header("Location: login.php");
    exit();
}

// Menghubungkan ke database
$conn = connectDB();

// Jika permintaan adalah POST (untuk menambah atau memperbarui dokter)
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Menyaring data input pengguna untuk mencegah injeksi SQL
    $nama = sanitize($_POST['nama']);
    $spesialis = sanitize($_POST['spesialis']);
    $username = sanitize($_POST['username']);
    $password = $_POST['password'];
    $action = $_POST['action'];  // Aksi (add atau update)

    // Memulai transaksi database
    sqlsrv_begin_transaction($conn);

    try {
        // Mengecek apakah username sudah digunakan (kecuali untuk update dokter yang sama)
        $dokter_id = isset($_POST['dokter_id']) ? intval($_POST['dokter_id']) : 0;
        $sql = "SELECT ID_Dokter FROM Dokter 
                WHERE Username = '$username' 
                AND ID_Dokter != $dokter_id";
        
        $result = sqlsrv_query($conn, $sql);
        
        // Jika username sudah ada, lemparkan exception
        if(sqlsrv_has_rows($result)) {
            throw new Exception("Username sudah digunakan");
        }

        // Jika aksi adalah 'add' (menambah dokter baru)
        if($action == 'add') {
            // Pastikan password diisi saat menambah dokter baru
            if(empty($password)) {
                throw new Exception("Password harus diisi untuk dokter baru");
            }

            // Meng-hash password sebelum disimpan
            $hashed_password = hash('sha256', $password);

            // Menyisipkan dokter baru ke dalam tabel Dokter
            $sql = "INSERT INTO Dokter (Nama, Spesialis, Username, Password)
                    VALUES ('$nama', '$spesialis', '$username', '$hashed_password')";
            
            $message = "Data dokter berhasil ditambahkan"; // Pesan sukses
        } else {
            // Jika aksi adalah 'update' (memperbarui data dokter)
            $sql = "UPDATE Dokter SET 
                    Nama = '$nama',
                    Spesialis = '$spesialis',
                    Username = '$username'";
            
            // Hanya update password jika ada password baru yang diinput
            if(!empty($password)) {
                $hashed_password = hash('sha256', $password);
                $sql .= ", Password = '$hashed_password'";
            }
            
            // Menyelesaikan query update dengan menambahkan kondisi WHERE untuk ID_Dokter
            $sql .= " WHERE ID_Dokter = $dokter_id";
            
            $message = "Data dokter berhasil diupdate"; // Pesan sukses
        }

        // Eksekusi query
        $result = sqlsrv_query($conn, $sql);
        if($result === false) {
            throw new Exception("Gagal menyimpan data dokter");
        }

        // Commit transaksi jika berhasil
        sqlsrv_commit($conn);
        $_SESSION['success'] = $message;

    } catch(Exception $e) {
        // Rollback transaksi jika terjadi error
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();  // Simpan pesan error ke sesi
    }

} elseif($_SERVER['REQUEST_METHOD'] == 'GET' && isset($_GET['action']) && $_GET['action'] == 'delete') {
    // Jika permintaan adalah GET untuk menghapus dokter
    $dokter_id = intval($_GET['id']);  // Ambil ID dokter yang ingin dihapus

    // Memulai transaksi untuk penghapusan
    sqlsrv_begin_transaction($conn);

    try {
        // Memeriksa apakah dokter memiliki jadwal yang masih aktif
        $sql = "SELECT COUNT(*) as jadwal FROM Jadwal_Dokter WHERE ID_Dokter = $dokter_id";
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        // Jika dokter memiliki jadwal, batalkan penghapusan
        if($row['jadwal'] > 0) {
            throw new Exception("Tidak dapat menghapus dokter yang masih memiliki jadwal");
        }

        // Memeriksa apakah dokter memiliki pemeriksaan yang sudah dilakukan
        $sql = "SELECT COUNT(*) as pemeriksaan 
                FROM Pemeriksaan pem
                JOIN Pendaftaran p ON pem.ID_Pendaftaran = p.ID_Pendaftaran
                JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                WHERE j.ID_Dokter = $dokter_id";
        
        $result = sqlsrv_query($conn, $sql);
        $row = sqlsrv_fetch_array($result);
        
        // Jika dokter memiliki riwayat pemeriksaan, batalkan penghapusan
        if($row['pemeriksaan'] > 0) {
            throw new Exception("Tidak dapat menghapus dokter yang memiliki riwayat pemeriksaan");
        }

        // Menghapus dokter dari tabel Dokter
        $sql = "DELETE FROM Dokter WHERE ID_Dokter = $dokter_id";
        $result = sqlsrv_query($conn, $sql);
        
        if($result === false) {
            throw new Exception("Gagal menghapus data dokter");
        }

        // Commit transaksi jika berhasil
        sqlsrv_commit($conn);
        $_SESSION['success'] = "Data dokter berhasil dihapus";

    } catch(Exception $e) {
        // Rollback transaksi jika terjadi error
        sqlsrv_rollback($conn);
        $_SESSION['error'] = $e->getMessage();  // Simpan pesan error ke sesi
    }
}

// Menutup koneksi ke database
closeDB($conn);

// Mengarahkan kembali ke halaman manajemen dokter setelah operasi selesai
header("Location: manage_doctors.php");
exit();
?>
