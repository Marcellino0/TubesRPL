<?php
// Memulai sesi
session_start();

// Menghapus semua variabel sesi
$_SESSION = array();

// Menghancurkan cookie sesi
if (isset($_COOKIE[session_name()])) {
    setcookie(session_name(), '', time()-3600, '/'); // Mengatur waktu kedaluwarsa cookie menjadi masa lalu untuk menghapusnya
}

// Menghancurkan sesi
session_destroy();

// Mengarahkan ulang ke halaman login
header("Location: ../index.php");
exit(); // Mengakhiri eksekusi skrip agar pengalihan bekerja dengan baik
?>
