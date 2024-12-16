<?php
session_start(); // Memulai sesi untuk menyimpan data pengguna yang telah login

require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

// Memeriksa apakah metode request adalah POST (untuk menangani form login)
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = connectDB(); // Menghubungkan ke database menggunakan fungsi connectDB
    $username = sanitize($_POST['username']); // Menyaring input username
    $password = sanitize($_POST['password']); // Menyaring input password
    $user_type = $_POST['user_type']; // Mendapatkan jenis pengguna (admin, dokter, perawat, pasien)

    // Meng-hash password dengan algoritma SHA-256
    $hashed_password = hash('sha256', $password);

    // Menentukan tabel dan halaman tujuan berdasarkan jenis pengguna
    switch ($user_type) {
        case 'admin':
            $table = 'Administrator'; // Tabel untuk admin
            $redirect = 'admin_dashboard.php'; // Redirect ke dashboard admin
            break;
        case 'dokter':
            $table = 'Dokter'; // Tabel untuk dokter
            $redirect = 'doctor_dashboard.php'; // Redirect ke dashboard dokter
            break;
        case 'perawat':
            $table = 'Perawat'; // Tabel untuk perawat
            $redirect = 'nurse_dashboard.php'; // Redirect ke dashboard perawat
            break;
        case 'pasien':
            $table = 'Pasien'; // Tabel untuk pasien
            $redirect = 'patient_dashboard.php'; // Redirect ke dashboard pasien
            break;
        default:
            $_SESSION['error'] = "Tipe pengguna tidak valid"; // Menampilkan error jika tipe pengguna tidak valid
            header("Location: login.php"); // Mengarahkan kembali ke halaman login
            exit();
    }

    // Query untuk memeriksa kredensial pengguna berdasarkan tipe pengguna
    $sql = "SELECT ID_" . ucfirst($user_type) . " as user_id, Nama 
            FROM $table 
            WHERE Username = '$username' AND Password = '$hashed_password'";

    $result = sqlsrv_query($conn, $sql); // Menjalankan query untuk memeriksa kredensial

    if ($result === false) {
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login"; // Menampilkan error jika terjadi kesalahan query
        header("Location: login.php"); // Mengarahkan kembali ke halaman login
        exit();
    }

    // Memeriksa apakah pengguna ditemukan di database
    if ($row = sqlsrv_fetch_array($result)) {
        // Jika ditemukan, set variabel sesi
        $_SESSION['user_id'] = $row['user_id']; // Menyimpan ID pengguna dalam sesi
        $_SESSION['username'] = $username; // Menyimpan username dalam sesi
        $_SESSION['nama'] = $row['Nama']; // Menyimpan nama pengguna dalam sesi
        $_SESSION['user_type'] = $user_type; // Menyimpan tipe pengguna dalam sesi

        // Mengarahkan pengguna ke dashboard yang sesuai berdasarkan tipe pengguna
        header("Location: $redirect");
        exit();
    } else {
        $_SESSION['error'] = "Username atau password salah"; // Menampilkan error jika kredensial salah
        header("Location: login.php"); // Mengarahkan kembali ke halaman login
        exit();
    }

    closeDB($conn); // Menutup koneksi database
} else {
    header("Location: login.php"); // Jika bukan metode POST, arahkan kembali ke halaman login
    exit();
}
?>
