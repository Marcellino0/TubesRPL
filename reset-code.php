<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');
require_once(__DIR__ . '/includes/messages.php');

$errorMessage = "";
$successMessage = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_GET['email'];  
    $inputCode = $_POST['reset_code'];

    
    $storedOtp = $_SESSION['otp'];

    if ($inputCode == $storedOtp) {
        
        $successMessage = "Kode reset benar. Silakan masukkan password baru.";

        
        header("Location: new-password.php?email=" . urlencode($email));
        exit(); 
    } else {
        $errorMessage = "Kode reset salah. Silakan coba lagi.";
    }
}


$otpMessage = isset($_SESSION['otp']) ? "OTP Anda adalah: " . $_SESSION['otp'] : "";
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password</title>
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

        .otp-notification {
            background-color: #4CAF50;
            color: white;
            padding: 10px;
            text-align: center;
            font-size: 1.2em;
            border-radius: 5px;
            margin-top: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Masukkan Kode Reset</h1>
                <p>Silakan masukkan kode reset yang telah muncul dilayar anda.</p>
            </div>

            <!-- Form Start -->
            <form action="reset-code.php?email=<?php echo $_GET['email']; ?>" method="POST">
                <div class="form-group">
                    <label for="reset_code">Kode Reset:</label>
                    <input type="text" name="reset_code" id="reset_code" placeholder="Masukkan kode reset" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Lanjutkan</button>
                </div>
            </form>
            <!-- Form End -->

            <!-- Display Error or Success Message -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message"><?php echo $errorMessage; ?></div>
            <?php endif; ?>

            <?php if (!empty($successMessage)): ?>
                <div class="success-message"><?php echo $successMessage; ?></div>
                <!-- New password input form -->
                <form action="reset-code.php?email=<?php echo $_GET['email']; ?>" method="POST">
                    <label for="new_password">Password Baru:</label>
                    <input type="password" name="new_password" id="new_password" required>
                    <button type="submit">Reset Password</button>
                </form>
            <?php endif; ?>

            <!-- OTP Notification (from session) -->
            <?php if (!empty($otpMessage)): ?>
                <div class="otp-notification">
                    <?php echo $otpMessage; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>
</html>
