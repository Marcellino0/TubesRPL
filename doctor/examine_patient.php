<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as doctor
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: ../login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];

// Check if ID is provided
if(!isset($_GET['id'])) {
    header("Location: doctor_dashboard.php");
    exit();
}

$id_pendaftaran = $_GET['id'];

// Get patient data
$sql = "SELECT p.*, pas.Nama, pas.Nomor_Rekam_Medis, rm.Tekanan_Darah, rm.Tinggi_Badan, 
        rm.Berat_Badan, rm.Suhu, rm.Riwayat_Penyakit 
        FROM Pendaftaran p
        JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
        LEFT JOIN Rekam_Medis rm ON pas.ID_Pasien = rm.ID_Pasien
        WHERE p.ID_Pendaftaran = ? AND p.Status = 'Menunggu'";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id_pendaftaran);
$stmt->execute();
$result = $stmt->get_result();

if($result->num_rows === 0) {
    header("Location: doctor_dashboard.php");
    exit();
}

$patient = $result->fetch_assoc();

// Update status to 'Diperiksa'
$update_sql = "UPDATE Pendaftaran SET Status = 'Diperiksa' WHERE ID_Pendaftaran = ?";
$stmt = $conn->prepare($update_sql);
$stmt->bind_param("i", $id_pendaftaran);
$stmt->execute();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Pasien - Poliklinik X</title>
    <style>
        :root {
            --primary-color: #2563eb;
            --secondary-color: #1e40af;
            --background-color: #f3f4f6;
            --text-color: #1f2937;
            --border-color: #e5e7eb;
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Arial', sans-serif;
            background-color: var(--background-color);
            color: var(--text-color);
            line-height: 1.6;
        }

        .container {
            max-width: 1200px;
            margin: 2rem auto;
            padding: 0 1rem;
        }

        .card {
            background: white;
            border-radius: 0.5rem;
            box-shadow: 0 1px 3px rgba(0,0,0,0.1);
            margin-bottom: 1.5rem;
            padding: 1.5rem;
        }

        .card h2 {
            color: var(--text-color);
            margin-bottom: 1rem;
            font-size: 1.25rem;
        }

        .patient-info {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 1rem;
            margin-bottom: 1.5rem;
        }

        .info-item {
            padding: 0.5rem;
        }

        .info-label {
            font-size: 0.875rem;
            color: #666;
            margin-bottom: 0.25rem;
        }

        .info-value {
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            margin-bottom: 0.5rem;
            font-weight: 500;
        }

        textarea {
            width: 100%;
            padding: 0.75rem;
            border: 1px solid var(--border-color);
            border-radius: 0.375rem;
            resize: vertical;
            min-height: 100px;
            font-family: inherit;
        }

        .btn-container {
            display: flex;
            justify-content: flex-end;
            gap: 1rem;
            margin-top: 1.5rem;
        }

        .btn {
            padding: 0.5rem 1rem;
            border-radius: 0.375rem;
            font-size: 0.875rem;
            font-weight: 500;
            cursor: pointer;
            border: none;
            transition: background-color 0.2s;
        }

        .btn-primary {
            background-color: var(--primary-color);
            color: white;
        }

        .btn-primary:hover {
            background-color: var(--secondary-color);
        }

        .btn-secondary {
            background-color: var(--background-color);
            color: var(--text-color);
        }

        .btn-secondary:hover {
            background-color: var(--border-color);
        }

        .alert {
            padding: 1rem;
            border-radius: 0.375rem;
            margin-bottom: 1rem;
        }

        .alert-success {
            background-color: #dcfce7;
            color: #166534;
        }

        .alert-error {
            background-color: #fee2e2;
            color: #991b1b;
        }
    </style>
</head>
<body>
    <div class="container">
        <?php if(isset($_SESSION['error_message'])): ?>
            <div class="alert alert-error">
                <?php 
                echo $_SESSION['error_message'];
                unset($_SESSION['error_message']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2>Data Pasien</h2>
            <div class="patient-info">
                <div class="info-item">
                    <div class="info-label">Nama Pasien</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['Nama']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">No. Rekam Medis</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['Nomor_Rekam_Medis']); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tekanan Darah</div>
                    <div class="info-value"><?php echo htmlspecialchars($patient['Tekanan_Darah'] ?? 'Belum diukur'); ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Tinggi Badan</div>
                    <div class="info-value"><?php echo $patient['Tinggi_Badan'] ? htmlspecialchars($patient['Tinggi_Badan']).' cm' : 'Belum diukur'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Berat Badan</div>
                    <div class="info-value"><?php echo $patient['Berat_Badan'] ? htmlspecialchars($patient['Berat_Badan']).' kg' : 'Belum diukur'; ?></div>
                </div>
                <div class="info-item">
                    <div class="info-label">Suhu Badan</div>
                    <div class="info-value"><?php echo $patient['Suhu'] ? htmlspecialchars($patient['Suhu']).'°C' : 'Belum diukur'; ?></div>
                </div>
            </div>
            
            <div class="form-group">
                <label>Riwayat Penyakit</label>
                <div class="info-value"><?php echo nl2br(htmlspecialchars($patient['Riwayat_Penyakit'] ?? 'Tidak ada riwayat penyakit')); ?></div>
            </div>
        </div>

        <form action="process_examination.php" method="POST" class="card">
            <input type="hidden" name="id_pendaftaran" value="<?php echo $id_pendaftaran; ?>">
            
            <div class="form-group">
                <label for="diagnosa">Diagnosa</label>
                <textarea id="diagnosa" name="diagnosa" required 
                          placeholder="Masukkan hasil diagnosa pasien..."></textarea>
            </div>

            <div class="form-group">
                <label for="resep">Resep Obat</label>
                <textarea id="resep" name="resep" required
                          placeholder="Masukkan resep obat untuk pasien..."></textarea>
                <small style="color: #666;">
                    Format: Nama obat - Dosis - Aturan pakai<br>
                    Contoh: Paracetamol - 500mg - 3x1 sehari setelah makan
                </small>
            </div>

            <div class="btn-container">
                <a href="doctor_dashboard.php" class="btn btn-secondary">Kembali</a>
                <button type="submit" class="btn btn-primary">Simpan & Selesai</button>
            </div>
        </form>
    </div>
</body>
</html>