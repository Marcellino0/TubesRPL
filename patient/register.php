<?php
session_start();
require_once(__DIR__ . '/../config/db_connection.php');
require_once(__DIR__ . '/../includes/messages.php');

if(isset($_POST['register'])) {
    $nama = trim($_POST['nama']);
    $nik = trim($_POST['nik']);
    $tanggal_lahir = $_POST['tanggal_lahir'];
    $jenis_kelamin = $_POST['jenis_kelamin'];
    $alamat = trim($_POST['alamat']);
    $email = trim($_POST['email']);
    $no_hp = trim($_POST['no_hp']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $confirm_password = $_POST['confirm_password'];

    // Basic validation
    $errors = [];
    
    if(empty($nama) || empty($nik) || empty($tanggal_lahir) || empty($jenis_kelamin) || 
       empty($alamat) || empty($email) || empty($no_hp) || empty($username) || empty($password)) {
        $errors[] = "Semua field harus diisi";
    }

    if(strlen($nik) !== 16 || !is_numeric($nik)) {
        $errors[] = "NIK harus 16 digit angka";
    }

    if(!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = "Format email tidak valid";
    }

    if($password !== $confirm_password) {
        $errors[] = "Password dan konfirmasi password tidak cocok";
    }

    if(strlen($password) < 8) {
        $errors[] = "Password minimal 8 karakter";
    }

    // Check if username already exists
    $stmt = $conn->prepare("SELECT Username FROM Pasien WHERE Username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $errors[] = "Username sudah digunakan";
    }
    $stmt->close();

    // Check if NIK already exists
    $stmt = $conn->prepare("SELECT NIK FROM Pasien WHERE NIK = ?");
    $stmt->bind_param("s", $nik);
    $stmt->execute();
    if($stmt->get_result()->num_rows > 0) {
        $errors[] = "NIK sudah terdaftar";
    }
    $stmt->close();

    if(empty($errors)) {
        // Generate Nomor Rekam Medis (Format: RM-YYYY-XXXXX)
        $year = date('Y');
        $stmt = $conn->prepare("SELECT MAX(CAST(SUBSTRING_INDEX(Nomor_Rekam_Medis, '-', -1) AS UNSIGNED)) as max_num 
                               FROM Pasien WHERE Nomor_Rekam_Medis LIKE ?");
        $pattern = "RM-$year-%";
        $stmt->bind_param("s", $pattern);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $next_num = str_pad(($row['max_num'] + 1), 5, '0', STR_PAD_LEFT);
        $nomor_rekam_medis = "RM-$year-$next_num";
        
        // Calculate age
        $birthDate = new DateTime($tanggal_lahir);
        $today = new DateTime('today');
        $umur = $birthDate->diff($today)->y;

        // Hash password
        $hashed_password = password_hash($password, PASSWORD_DEFAULT);

        // Insert new patient
        $stmt = $conn->prepare("INSERT INTO Pasien (Nama, NIK, Tanggal_Lahir, Jenis_Kelamin, 
                               Alamat, Email, No_HP, Username, Password, Nomor_Rekam_Medis, Umur) 
                               VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
        
        $stmt->bind_param("ssssssssssi", $nama, $nik, $tanggal_lahir, $jenis_kelamin, 
                         $alamat, $email, $no_hp, $username, $hashed_password, 
                         $nomor_rekam_medis, $umur);

        if($stmt->execute()) {
            $_SESSION['success_message'] = "Registrasi berhasil! Nomor Rekam Medis Anda: " . $nomor_rekam_medis;
            header("Location: ../index.php");
            exit();
        } else {
            $errors[] = "Terjadi kesalahan saat mendaftar. Silakan coba lagi.";
        }
        $stmt->close();
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Registrasi Pasien - Poliklinik X</title>
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
            max-width: 800px;
            margin: 0 auto;
        }

        .registration-box {
            background: white;
            padding: 40px;
            border-radius: 15px;
            box-shadow: 0 10px 25px rgba(0,0,0,0.1);
        }

        .registration-header {
            text-align: center;
            margin-bottom: 30px;
        }

        .registration-header h1 {
            color: #2c3e50;
            font-size: 2.5em;
            margin-bottom: 10px;
        }

        .registration-header p {
            color: #7f8c8d;
            font-size: 1.1em;
        }

        .form-grid {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 20px;
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group.full-width {
            grid-column: 1 / -1;
        }

        .form-group label {
            display: block;
            margin-bottom: 8px;
            color: #2c3e50;
            font-weight: 500;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 12px;
            border: 2px solid #e0e0e0;
            border-radius: 8px;
            font-size: 16px;
            transition: border-color 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
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

        .error-message {
            background-color: #e74c3c;
            color: white;
            padding: 15px;
            border-radius: 8px;
            margin-bottom: 20px;
        }

        .back-link {
            display: block;
            text-align: center;
            margin-top: 20px;
            color: #3498db;
            text-decoration: none;
        }

        .back-link:hover {
            text-decoration: underline;
        }

        @media (max-width: 768px) {
            .form-grid {
                grid-template-columns: 1fr;
            }
            
            .registration-box {
                padding: 20px;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="registration-box">
            <div class="registration-header">
                <h1>Registrasi Pasien</h1>
                <p>Poliklinik X - Sistem Informasi Manajemen Poliklinik</p>
            </div>

            <?php if(!empty($errors)): ?>
                <div class="error-message">
                    <ul style="margin-left: 20px;">
                        <?php foreach($errors as $error): ?>
                            <li><?php echo htmlspecialchars($error); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>

            <form action="" method="POST" class="registration-form">
                <div class="form-grid">
                    <div class="form-group">
                        <label for="nama">Nama Lengkap:</label>
                        <input type="text" id="nama" name="nama" required 
                               value="<?php echo isset($_POST['nama']) ? htmlspecialchars($_POST['nama']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="nik">NIK:</label>
                        <input type="text" id="nik" name="nik" required maxlength="16" 
                               value="<?php echo isset($_POST['nik']) ? htmlspecialchars($_POST['nik']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="tanggal_lahir">Tanggal Lahir:</label>
                        <input type="date" id="tanggal_lahir" name="tanggal_lahir" required 
                               value="<?php echo isset($_POST['tanggal_lahir']) ? htmlspecialchars($_POST['tanggal_lahir']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="jenis_kelamin">Jenis Kelamin:</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kelamin</option>
                            <option value="Laki-laki" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Laki-laki') ? 'selected' : ''; ?>>Laki-laki</option>
                            <option value="Perempuan" <?php echo (isset($_POST['jenis_kelamin']) && $_POST['jenis_kelamin'] == 'Perempuan') ? 'selected' : ''; ?>>Perempuan</option>
                        </select>
                    </div>

                    <div class="form-group">
                        <label for="email">Email:</label>
                        <input type="email" id="email" name="email" required 
                               value="<?php echo isset($_POST['email']) ? htmlspecialchars($_POST['email']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="no_hp">Nomor HP:</label>
                        <input type="tel" id="no_hp" name="no_hp" required 
                               value="<?php echo isset($_POST['no_hp']) ? htmlspecialchars($_POST['no_hp']) : ''; ?>">
                    </div>

                    <div class="form-group full-width">
                        <label for="alamat">Alamat:</label>
                        <textarea id="alamat" name="alamat" rows="3" required><?php echo isset($_POST['alamat']) ? htmlspecialchars($_POST['alamat']) : ''; ?></textarea>
                    </div>

                    <div class="form-group">
                        <label for="username">Username:</label>
                        <input type="text" id="username" name="username" required 
                               value="<?php echo isset($_POST['username']) ? htmlspecialchars($_POST['username']) : ''; ?>">
                    </div>

                    <div class="form-group">
                        <label for="password">Password:</label>
                        <input type="password" id="password" name="password" required>
                    </div>

                    <div class="form-group">
                        <label for="confirm_password">Konfirmasi Password:</label>
                        <input type="password" id="confirm_password" name="confirm_password" required>
                    </div>

                    <div class="form-group full-width">
                        <button type="submit" name="register" class="btn">Daftar</button>
                    </div>
                </div>
            </form>

            <a href="../login.php" class="back-link">Kembali ke Halaman Login</a>
        </div>
    </div>
</body>
</html>