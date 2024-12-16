<?php
// Memulai sesi PHP agar variabel sesi dapat digunakan di seluruh aplikasi
session_start();

// Menyertakan file konfigurasi untuk koneksi database
require_once('../config/db_connection.php');

// Fungsi untuk membersihkan input pengguna agar aman dari XSS atau injeksi
function sanitize($data) {
    $data = trim($data); // Menghapus spasi di awal dan akhir
    $data = stripslashes($data); // Menghapus backslash
    $data = htmlspecialchars($data); // Mengonversi karakter spesial menjadi entitas HTML
    return $data;
}

// Blok untuk menangani permintaan POST dari form login
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Membersihkan input username
    $username = sanitize($_POST['username']);
    // Password tidak disanitasi karena harus dicocokkan persis dengan data di database
    $password = $_POST['password'];
    // Mengambil tipe pengguna dari form
    $user_type = $_POST['user_type'];
    
    // Memeriksa apakah tipe pengguna adalah 'perawat'
    if ($user_type !== 'perawat') {
        // Jika tipe pengguna salah, set error pada sesi dan arahkan kembali ke halaman login
        $_SESSION['error'] = "Tipe pengguna tidak valid";
        header("Location: ../index.php");
        exit();
    }

    try {
        // Menyiapkan query untuk mendapatkan data perawat berdasarkan username
        $sql = "SELECT ID_Perawat as user_id, Nama, Password FROM Perawat WHERE Username = ?";
        $stmt = $conn->prepare($sql); // Menyiapkan query dengan statement terparameter
        $stmt->bind_param("s", $username); // Mengikat parameter username
        $stmt->execute(); // Menjalankan query
        $result = $stmt->get_result(); // Mendapatkan hasil query
        
        // Memeriksa apakah data perawat ditemukan
        if ($row = $result->fetch_assoc()) {
            // Membandingkan password yang dimasukkan dengan password di database
            // Dalam produksi, gunakan password_verify() untuk password yang di-hash
            if ($password === $row['Password']) {
                // Jika password cocok, set variabel sesi untuk pengguna yang berhasil login
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['user_type'] = 'perawat';
                
                // Mengarahkan ke dashboard perawat
                header("Location: nurse_dashboard.php");
                exit();
            } else {
                // Jika password salah, set error pada sesi dan arahkan kembali ke halaman login
                $_SESSION['error'] = "Username atau password salah";
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Jika username tidak ditemukan, set error pada sesi dan arahkan kembali ke halaman login
            $_SESSION['error'] = "Username atau password salah";
            header("Location: ../index.php");
            exit();
        }
    } catch (Exception $e) {
        // Menangkap kesalahan dan set error pada sesi dengan pesan kesalahan
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login: " . $e->getMessage();
        header("Location: ../index.php");
        exit();
    }

} else {
    // Jika metode request bukan POST, arahkan kembali ke halaman login
    header("Location: ../index.php");
    exit();
}
?>
