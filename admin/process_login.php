<?php
// Memulai sesi pengguna
session_start();

// Menyertakan koneksi ke database
require_once('../config/db_connection.php');

// Fungsi untuk membersihkan (sanitize) input dari pengguna
function sanitize($data) {
    $data = trim($data);          // Menghapus spasi di awal dan akhir string
    $data = stripslashes($data);  // Menghapus karakter backslash
    $data = htmlspecialchars($data); // Mengonversi karakter khusus menjadi entitas HTML
    return $data;
}

// Memeriksa apakah request adalah POST
if($_SERVER['REQUEST_METHOD'] == 'POST') {
    // Mengambil input dari form dan membersihkannya
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Password tidak disanitasi karena harus cocok persis
    $user_type = $_POST['user_type'];
    
    // Memeriksa apakah tipe pengguna adalah 'admin'
    if($user_type !== 'admin') {
        // Jika tipe pengguna tidak valid, arahkan ke halaman login dengan pesan error
        $_SESSION['error'] = "Tipe pengguna tidak valid";
        header("Location: ../index.php");
        exit();
    }

    try {
        // Persiapkan dan eksekusi query untuk mencari data admin berdasarkan username
        $sql = "SELECT ID_Admin, Username, Password, Nama 
                FROM administrator 
                WHERE Username = ?";
        // Menyiapkan statement SQL
        $stmt = $conn->prepare($sql);
        // Mengikat parameter username ke query
        $stmt->bind_param("s", $username);
        // Menjalankan query
        $stmt->execute();
        // Mendapatkan hasil query
        $result = $stmt->get_result();
        
        // Memeriksa apakah ada baris yang ditemukan
        if($row = $result->fetch_assoc()) {
            // Memeriksa apakah password yang dimasukkan cocok dengan password di database
            // Catatan: Di produksi, Anda harus menggunakan password_verify() untuk membandingkan password yang di-hash
            if ($password === $row['Password']) {
                // Jika password cocok, set session variables
                $_SESSION['user_id'] = $row['ID_Admin'];
                $_SESSION['username'] = $row['Username'];
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['user_type'] = 'admin';
                
                // Redirect ke dashboard admin
                header("Location: admin_dashboard.php");
                exit();
            } else {
                // Jika password salah, arahkan kembali ke halaman login dengan pesan error
                $_SESSION['error'] = "Username atau password salah";
                header("Location: ../index.php");
                exit();
            }
        } else {
            // Jika username tidak ditemukan di database, arahkan kembali ke halaman login
            $_SESSION['error'] = "Username atau password salah";
            header("Location: ../index.php");
            exit();
        }
    } catch(Exception $e) {
        // Menangani kesalahan jika terjadi dalam proses login
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login: " . $e->getMessage();
        header("Location: ../index.php");
        exit();
    }

} else {
    // Jika bukan request POST, arahkan kembali ke halaman login
    header("Location: ../index.php");
    exit();
}
?>
