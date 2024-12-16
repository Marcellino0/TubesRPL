<?php
session_start();
require_once(__DIR__ . '/config/db_connection.php');
require_once(__DIR__ . '/includes/messages.php');


$email = '';
$error = '';
$success = false;


if ($_SERVER['REQUEST_METHOD'] === 'POST') {

    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Format email tidak valid.";
    } elseif (empty($email)) {
        $error = "Silakan masukkan alamat email Anda.";
    } else {
        try {
            $query = "SELECT ID_Pasien, Nama, Email FROM Pasien WHERE Email = ? LIMIT 1";
            $stmt = $conn->prepare($query);
            
            if (!$stmt) {
                throw new Exception("Gagal mempersiapkan query: " . $conn->error);
            }
            
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();
            
            if ($result->num_rows > 0) {
                $patient = $result->fetch_assoc();
                
                $reset_token = bin2hex(random_bytes(32));
                $token_expiry = date('Y-m-d H:i:s', strtotime('+1 hour'));
                
                
                $update_query = "UPDATE Pasien SET 
                    reset_token = ?, 
                    reset_token_expiry = ? 
                    WHERE Email = ?";
                $update_stmt = $conn->prepare($update_query);
                
                if (!$update_stmt) {
                    throw new Exception("Gagal mempersiapkan update query: " . $conn->error);
                }
                
                $update_stmt->bind_param("sss", $reset_token, $token_expiry, $email);
                
                if ($update_stmt->execute()) {
                   
                    $reset_link = "https://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']) . "/reset_password.php?token=" . $reset_token;
                    
                    $_SESSION['message'] = "Link reset password telah dikirim ke " . htmlspecialchars($email) . ". Berlaku selama 1 jam.";
                    
                    $success = true;
                    
                    error_log("Password reset requested for email: " . $email);
                } else {
                    $error = "Gagal memperbarui token. Silakan coba lagi.";
                }
                
                
                $update_stmt->close();
            } else {
                $error = "Email tidak terdaftar dalam sistem.";
            }
            
            
            $stmt->close();
        } catch (Exception $e) {
            
            error_log("Database error: " . $e->getMessage());
            $error = "Terjadi kesalahan sistem. Silakan hubungi administrator.";
        }
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
            max-width: 1200px;
            margin: 0 auto;
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .forgot-password-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0, 0, 0, 0.1);
            width: 100%;
            max-width: 450px;
            margin: 0 auto;
        }

        .forgot-password-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .forgot-password-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .forgot-password-header p {
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

            .forgot-password-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="forgot-password-box">
            <div class="forgot-password-header">
                <h1>Lupa Password</h1>
                <p>Masukkan email untuk mereset password Anda</p>
            </div>

            <?php 
            if (!empty($error)): ?>
                <div class="alert alert-error">
                    <?php echo htmlspecialchars($error); ?>
                </div>
            <?php endif; ?>

            <?php 
            if ($success): ?>
                <div class="alert alert-success">
                    <?php 
                    echo isset($_SESSION['message']) ? htmlspecialchars($_SESSION['message']) : 'Link reset password telah dikirim.'; 
                    
                    unset($_SESSION['message']); 
                    ?>
                </div>
            <?php else: ?>
                <form action="" method="POST">
                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required autocomplete="email" 
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="Masukkan email terdaftar">
                    </div>

                    <div class="form-group">
                        <button type="submit" class="btn">Kirim Link Reset Password</button>
                    </div>
                </form>
            <?php endif; ?>

            <div class="form-links">
                <a href="index.php">Kembali ke Halaman Login</a>
            </div>
        </div>
    </div>
</body>
</html>