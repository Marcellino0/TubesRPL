<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');

// Function to sanitize input
function sanitize($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

if($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = sanitize($_POST['username']);
    $password = $_POST['password']; // Don't sanitize password
    $user_type = $_POST['user_type'];
    
    // Determine table based on user type
    switch($user_type) {
        case 'admin':
            $table = 'Administrator';
            $id_field = 'ID_Admin';
            $redirect = 'admin/admin_dashboard.php';
            break;
        case 'dokter':
            $table = 'Dokter';
            $id_field = 'ID_Dokter';
            $redirect = 'doctor/doctor_dashboard.php';
            break;
        case 'perawat':
            $table = 'Perawat';
            $id_field = 'ID_Perawat';
            $redirect = 'nurse/nurse_dashboard.php';
            break;
        case 'pasien':
            $table = 'Pasien';
            $id_field = 'ID_Pasien';
            $redirect = 'patient/patient_dashboard.php';
            break;
        default:
            $_SESSION['error'] = "Tipe pengguna tidak valid";
            header("Location: index.php");
            exit();
    }

    try {
        // Prepare and execute query using mysqli
        $sql = "SELECT $id_field as user_id, Nama, Password FROM $table WHERE Username = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("s", $username);
        $stmt->execute();
        $result = $stmt->get_result();
        
        if($row = $result->fetch_assoc()) {
            // Verify password
            if (password_verify($password, $row['Password'])) {
                // Set session variables
                $_SESSION['user_id'] = $row['user_id'];
                $_SESSION['username'] = $username;
                $_SESSION['nama'] = $row['Nama'];
                $_SESSION['user_type'] = $user_type;
                
                // Redirect to appropriate dashboard
                header("Location: $redirect");
                exit();
            } else {
                $_SESSION['error'] = "Username atau password salah";
                header("Location: index.php");
                exit();
            }
        } else {
            $_SESSION['error'] = "Username atau password salah";
            header("Location: index.php");
            exit();
        }
    } catch(Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan dalam proses login: " . $e->getMessage();
        header("Location: index.php");
        exit();
    }

    // Close connection
    $conn->close();
} else {
    header("Location: index.php");
    exit();
}
?>