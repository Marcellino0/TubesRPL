<?php
// Start the session
session_start(); // Memulai sesi agar bisa mengakses dan mengelola data sesi yang ada

// Clear all session variables
$_SESSION = array(); // Mengosongkan semua variabel sesi yang ada, menghapus data pengguna yang tersimpan

// Destroy the session cookie
if (isset($_COOKIE[session_name()])) { 
    // Memeriksa apakah ada cookie sesi yang terkait
    setcookie(session_name(), '', time()-3600, '/'); // Menghapus cookie sesi dengan mengatur waktu kedaluwarsa satu jam yang lalu
}

// Destroy the session
session_destroy(); // Menghancurkan sesi saat ini, menghapus sesi dari server

// Redirect to login page
header("Location: ../index.php"); // Mengarahkan pengguna kembali ke halaman login
exit(); // Menjaga agar script tidak dieksekusi lebih lanjut setelah redirect
?>
