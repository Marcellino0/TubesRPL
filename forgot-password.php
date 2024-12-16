<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');
require_once(__DIR__ . '/includes/messages.php');


function generateOtp() {
    return mt_rand(100000, 999999); 
}


$errorMessage = "";


if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $email = $_POST['email'];

    
    $query = "SELECT * FROM pasien WHERE Email = ?";  
    $stmt = $conn->prepare($query);
    $stmt->bind_param('s', $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows > 0) {
        
        $user = $result->fetch_assoc();

        
        $otp = generateOtp();

        
        $updateQuery = "UPDATE pasien SET code = ? WHERE Email = ?";
        $stmt = $conn->prepare($updateQuery);
        $stmt->bind_param('ss', $otp, $email);
        $stmt->execute();

        
        $_SESSION['otp'] = $otp;  

        
        header("Location: reset-code.php?email=$email");
        exit();
    } else {
        
        $errorMessage = "Email tidak terdaftar. Silakan daftar terlebih dahulu.";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password - Poliklinik X</title>
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
    </style>
</head>
<body>
    <div class="container">
        <div class="login-box">
            <div class="login-header">
                <h1>Lupa Password</h1>
            </div>

            <!-- Form Start -->
            <form action="forgot-password.php" method="POST">
                <div class="form-group">
                    <label for="email">Masukkan Email Anda:</label>
                    <input type="email" name="email" id="email" placeholder="Masukkan email Anda" required>
                </div>

                <div class="form-group">
                    <button type="submit" class="btn">Lanjutkan</button>
                </div>
            </form>
            <!-- Form End -->

            <!-- Display Error Message -->
            <?php if (!empty($errorMessage)): ?>
                <div class="error-message">
                    <?php echo $errorMessage; ?>
                </div>
            <?php endif; ?>

            <!-- Links -->
            <div class="form-links">
                <a href="login.php">Kembali ke halaman login</a>
            </div>
        </div>
    </div>
</body>
</html>
