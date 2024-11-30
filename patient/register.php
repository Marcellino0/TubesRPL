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
    <title>Registrasi</title>
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
                <h1>Registrasi</h1>
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
                        <label for="jenis_kelamin">Kelurahan:</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                        <option value="">Pilih Kelurahan</option>
        <option value="Ciroyom" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Ciroyom') ? 'selected' : ''; ?>>Ciroyom</option>
        <option value="Garuda" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Garuda') ? 'selected' : ''; ?>>Garuda</option>
        <option value="Kebon Jeruk" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Kebon Jeruk') ? 'selected' : ''; ?>>Kebon Jeruk</option>
        <option value="Maleber" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Maleber') ? 'selected' : ''; ?>>Maleber</option>
        <option value="Dunguscariang" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Dunguscariang') ? 'selected' : ''; ?>>Dunguscariang</option>
        <option value="Cempaka" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Cempaka') ? 'selected' : ''; ?>>Cempaka</option>
        <option value="Karasak" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Karasak') ? 'selected' : ''; ?>>Karasak</option>
        <option value="Nyengseret" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Nyengseret') ? 'selected' : ''; ?>>Nyengseret</option>
        <option value="Panjunan" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Panjunan') ? 'selected' : ''; ?>>Panjunan</option>
        <option value="Pelindung Hewan" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Pelindung Hewan') ? 'selected' : ''; ?>>Pelindung Hewan</option>
        <option value="Cisaranten Bina Harapan" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Cisaranten Bina Harapan') ? 'selected' : ''; ?>>Cisaranten Bina Harapan</option>
        <option value="Antapani Kidul" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Antapani Kidul') ? 'selected' : ''; ?>>Antapani Kidul</option>
        <option value="Antapani Tengah" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Antapani Tengah') ? 'selected' : ''; ?>>Antapani Tengah</option>
        <option value="Antapani Wetan" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Antapani Wetan') ? 'selected' : ''; ?>>Antapani Wetan</option>
        <option value="Sukamiskin" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Sukamiskin') ? 'selected' : ''; ?>>Sukamiskin</option>
        <option value="Cisaranten Kulon" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Cisaranten Kulon') ? 'selected' : ''; ?>>Cisaranten Kulon</option>
        <option value="Cisaranten" <?php echo (isset($_POST['kelurahan']) && $_POST['kelurahan'] == 'Cisaranten') ? 'selected' : ''; ?>>Cisaranten</option>
                        </select>
                    </div>


                    <div class="form-group">
                        <label for="jenis_kelamin">Kecamatan:</label>
                        <select id="jenis_kelamin" name="jenis_kelamin" required>
                            <option value="">Pilih Jenis Kecamatan</option>
                            <option value="Andir" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Andir') ? 'selected' : ''; ?>>Andir</option>
        <option value="Astanaanyar" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Astanaanyar') ? 'selected' : ''; ?>>Astanaanyar</option>
        <option value="Antapani" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Antapani') ? 'selected' : ''; ?>>Antapani</option>
        <option value="Arcamanik" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Arcamanik') ? 'selected' : ''; ?>>Arcamanik</option>
        <option value="Babakan Ciparay" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Babakan Ciparay') ? 'selected' : ''; ?>>Babakan Ciparay</option>
        <option value="Bandung Kidul" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Bandung Kidul') ? 'selected' : ''; ?>>Bandung Kidul</option>
        <option value="Bandung Kulon" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Bandung Kulon') ? 'selected' : ''; ?>>Bandung Kulon</option>
        <option value="Bandung Wetan" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Bandung Wetan') ? 'selected' : ''; ?>>Bandung Wetan</option>
        <option value="Batununggal" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Batununggal') ? 'selected' : ''; ?>>Batununggal</option>
        <option value="Bojongloa Kaler" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Bojongloa Kaler') ? 'selected' : ''; ?>>Bojongloa Kaler</option>
        <option value="Bojongloa Kidul" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Bojongloa Kidul') ? 'selected' : ''; ?>>Bojongloa Kidul</option>
        <option value="Buahbatu" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Buahbatu') ? 'selected' : ''; ?>>Buahbatu</option>
        <option value="Cibeunying Kaler" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cibeunying Kaler') ? 'selected' : ''; ?>>Cibeunying Kaler</option>
        <option value="Cibeunying Kidul" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cibeunying Kidul') ? 'selected' : ''; ?>>Cibeunying Kidul</option>
        <option value="Cibiru" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cibiru') ? 'selected' : ''; ?>>Cibiru</option>
        <option value="Cicendo" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cicendo') ? 'selected' : ''; ?>>Cicendo</option>
        <option value="Cidadap" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cidadap') ? 'selected' : ''; ?>>Cidadap</option>
        <option value="Cinambo" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Cinambo') ? 'selected' : ''; ?>>Cinambo</option>
        <option value="Coblong" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Coblong') ? 'selected' : ''; ?>>Coblong</option>
        <option value="Gedebage" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Gedebage') ? 'selected' : ''; ?>>Gedebage</option>
        <option value="Kiaracondong" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Kiaracondong') ? 'selected' : ''; ?>>Kiaracondong</option>
        <option value="Lengkong" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Lengkong') ? 'selected' : ''; ?>>Lengkong</option>
        <option value="Mandalajati" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Mandalajati') ? 'selected' : ''; ?>>Mandalajati</option>
        <option value="Panyileukan" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Panyileukan') ? 'selected' : ''; ?>>Panyileukan</option>
        <option value="Rancasari" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Rancasari') ? 'selected' : ''; ?>>Rancasari</option>
        <option value="Regol" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Regol') ? 'selected' : ''; ?>>Regol</option>
        <option value="Sukajadi" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Sukajadi') ? 'selected' : ''; ?>>Sukajadi</option>
        <option value="Sukasari" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Sukasari') ? 'selected' : ''; ?>>Sukasari</option>
        <option value="Sumur Bandung" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Sumur Bandung') ? 'selected' : ''; ?>>Sumur Bandung</option>
        <option value="Ujung Berung" <?php echo (isset($_POST['kecamatan']) && $_POST['kecamatan'] == 'Ujung Berung') ? 'selected' : ''; ?>>Ujung Berung</option>
                        </select>
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

            <a href="../index.php" class="back-link">Kembali ke Halaman Login</a>
        </div>
    </div>
</body>
</html>