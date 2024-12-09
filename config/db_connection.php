<?php
$db_server = "localhost";
$db_user = "root";
$db_pass = "";
$db_name = "poliklinikx";  // Perhatikan case-sensitive

try {
    $conn = mysqli_connect($db_server, $db_user, $db_pass, $db_name);
    
    if (!$conn) {
        throw new Exception("Koneksi database gagal: " . mysqli_connect_error());
    }
    
    // Set charset ke utf8
    if (!mysqli_set_charset($conn, "utf8")) {
        throw new Exception("Error setting charset: " . mysqli_error($conn));
    }

} catch (Exception $e) {
    die("Error: " . $e->getMessage());
}
?>