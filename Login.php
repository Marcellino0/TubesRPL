<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');
require_once(__DIR__ . '/includes/messages.php');

function getActiveUserSession() {
    if (isset($_SESSION['user_id']) && isset($_SESSION['user_type'])) {
        return [
            'user_id' => $_SESSION['user_id'],
            'user_type' => $_SESSION['user_type']
        ];
    }
    return null;
}

function validateUserSession($userId, $userType, $conn) {
    $columnName = '';
    $table = '';
    
    switch ($userType) {
        case 'admin':
            $table = 'Administrator';
            $columnName = 'ID_Admin';
            break;
        case 'dokter':
            $table = 'Dokter';
            $columnName = 'ID_Dokter';
            break;
        case 'perawat':
            $table = 'Perawat';
            $columnName = 'ID_Perawat';
            break;
        case 'pasien':
            $table = 'Pasien';
            $columnName = 'ID_Pasien';
            break;
        default:
            return false;
    }
    
    $query = "SELECT $columnName FROM $table WHERE $columnName = ? LIMIT 1";
    try {
        $stmt = $conn->prepare($query);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $result = $stmt->get_result();
        return $result->num_rows > 0;
    } catch (Exception $e) {
        error_log("Database error: " . $e->getMessage());
        return false;
    }
}


$activeSession = getActiveUserSession();

if ($activeSession) {
    
    if (validateUserSession($activeSession['user_id'], $activeSession['user_type'], $conn)) {
        
        switch ($activeSession['user_type']) {
            case 'admin':
                header("Location: admin/admin_dashboard.php");
                break;
            case 'dokter':
                header("Location: doctor/doctor_dashboard.php");
                break;
            case 'perawat':
                header("Location: nurse/nurse_dashboard.php");
                break;
            case 'pasien':
                header("Location: patient/patient_dashboard.php");
                break;
        }
        exit();
    } else {
        session_destroy();
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Poliklinik X</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }

        .container {
            width: 100%;
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .login-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .login-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .login-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .login-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus {
            border-color: #3498db;
            outline: none;
        }

        .password-input {
            position: relative;
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #666;
            cursor: pointer;
            font-size: 14px;
            padding: 5px;
        }

        .btn {
            width: 100%;
            padding: 14px;
            background: #3498db;
            color: white;
            border: none;
            border-radius: 8px;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        .btn:hover {
            background: #2980b9;
        }

        .form-links {
            text-align: center;
            margin-top: 20px;
        }

        .form-links a {
            color: #3498db;
            text-decoration: none;
            font-size: 0.95em;
        }

        .form-links a:hover {
            text-decoration: underline;
        }

        .separator {
            margin: 0 10px;
            color: #bdc3c7;
        }

        .info-box {
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
        }

        .info-box h3 {
            color: #2c3e50;
            margin-bottom: 20px;
            text-align: center;
            font-size: 1.5em;
        }

        .info-content {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 25px;
        }

        .info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            transition: transform 0.3s ease;
        }

        .info-item:hover {
            transform: translateY(-5px);
        }

        .info-item h4 {
            color: #2c3e50;
            margin-bottom: 15px;
            font-size: 1.2em;
        }

        .info-item p,
        .info-item ul {
            color: #666;
            line-height: 1.6;
        }

        .info-item ul {
            padding-left: 20px;
            margin-top: 10px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 8px;
            color: white;
        }

        .alert-success {
            background-color: #2ecc71;
        }

        .alert-error {
            background-color: #e74c3c;
        }

        @media (max-width: 768px) {
            .container {
                padding: 10px;
            }

            .login-box,
            .info-box {
                padding: 20px;
            }

            .info-content {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Poliklinik X</h1>
                <p>Sistem Informasi Manajemen Poliklinik</p>
            </div>

            <?php displayMessage(); ?>

            <form action="" method="POST" class="login-form">
                <div class="form-group">
                    <label for="user_type">Login Sebagai:</label>
                    <select name="user_type" id="user_type" required onchange="updateFormAction(this.value)">
                        <option value="pasien">Pasien</option>
                        <option value="dokter">Dokter</option>
                        <option value="admin">Administrator</option>
                        <option value="perawat">Perawat</option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required autocomplete="username">
                </div>

                <div class="form-group">
                    <label for="password">Password:</label>
                    <div class="password-input">
                        <input type="password" id="password" name="password" required autocomplete="current-password">
                        <button type="button" class="toggle-password" onclick="togglePassword()">Show</button>
                    </div>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Login</button>
                </div>

                <div class="form-links" id="patient-links">
                    <a href="forgot-password.php">Lupa Password?</a>
                </div>
            </form>
        </div>

        
    </div>

    <script>
        function togglePassword() {
            const passwordInput = document.getElementById('password');
            const toggleBtn = document.querySelector('.toggle-password');
            
            if (passwordInput.type === 'password') {
                passwordInput.type = 'text';
                toggleBtn.textContent = 'Hide';
            } else {
                passwordInput.type = 'password';
                toggleBtn.textContent = 'Show';
            }
        }

        function updateFormAction(userType) {
            const form = document.querySelector('.login-form');
            const patientLinks = document.getElementById('patient-links');
            
            switch(userType) {
    case 'perawat':
        form.action = 'nurse/process_login.php';
        break;
    case 'admin':
        form.action = 'admin/process_login.php';
        break;
    case 'dokter':
        form.action = 'doctor/process_login.php';
        break;
    case 'pasien':
        form.action = 'patient/process_login.php';
        break;
}
            
            patientLinks.style.display = userType === 'pasien' ? 'block' : 'none';
        }

        document.addEventListener('DOMContentLoaded', function() {
            const userType = document.getElementById('user_type');
            updateFormAction(userType.value);
        });
    </script>
</body>
</html>