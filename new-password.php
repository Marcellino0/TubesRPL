<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');

$errorMessage = "";
$successMessage = "";


if (isset($_GET['email'])) {
    $email = $_GET['email'];

 
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $newPassword = $_POST['new_password'];
        $confirmPassword = $_POST['confirm_password'];

  
        if (strlen($newPassword) < 8) {
            $errorMessage = "Password harus lebih dari 8 karakter.";
        } elseif ($newPassword !== $confirmPassword) {
            $errorMessage = "Password baru dan konfirmasi password tidak cocok.";
        } else {
            
            $updatePasswordQuery = "UPDATE pasien SET Password = ? WHERE Email = ?";
            $stmt = $conn->prepare($updatePasswordQuery);
            $stmt->bind_param('ss', password_hash($newPassword, PASSWORD_DEFAULT), $email);
            $stmt->execute();

           
            header("Location: password-changed.php");
            exit();
        }
    }
} else {
    $errorMessage = "Email tidak valid.";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
    <style>
        /* Keep the existing styles for consistency */
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
            max-width: 450px;
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

        .form-group input {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus {
            border-color: #3498db;
            outline: none;
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

        .error-message {
            color: #e74c3c;
            text-align: center;
            font-size: 1em;
            margin-top: 15px;
        }

        .success-message {
            color: #2ecc71;
            text-align: center;
            font-size: 1em;
            margin-top: 15px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Masukkan Password Baru</h1>
                <p>Silakan masukkan password baru Anda dan konfirmasi password.</p>
            </div>

            <!-- Form Start -->
            <form action="new-password.php?email=<?php echo urlencode($email); ?>" method="POST">
                <div class="form-group">
                    <label for="new_password">Password Baru:</label>
                    <input type="password" name="new_password" id="new_password" placeholder="Masukkan password baru" required>
                </div>

                <div class="form-group">
                    <label for="confirm_password">Konfirmasi Password:</label>
                    <input type="password" name="confirm_password" id="confirm_password" placeholder="Konfirmasi password baru" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Reset Password</button>
                </div>
            </form>
            <!-- Form End -->

            <!-- Display Error or Success Message -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
            <?php endif; ?>

            <!-- Links -->
            <div class="form-links">
                <a href="login.php">Kembali ke halaman login</a>
            </div>
        </div>
    </div>
</body>
</html>
