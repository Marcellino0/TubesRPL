<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$dokter_id = $_SESSION['user_id'];
$pendaftaran_id = $_GET['id'];

// Get patient information
$sql = "SELECT p.*, pas.Nama as nama_pasien, pas.Nomor_Rekam_Medis, 
        rm.Tekanan_Darah, rm.Tinggi_Badan, rm.Berat_Badan, rm.Suhu, rm.Riwayat_Penyakit
        FROM Pendaftaran p
        JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
        LEFT JOIN Rekam_Medis rm ON pas.ID_Pasien = rm.ID_Pasien
        WHERE p.ID_Pendaftaran = $pendaftaran_id";

$result = sqlsrv_query($conn, $sql);
$patient = sqlsrv_fetch_array($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pemeriksaan Pasien - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="doctor_dashboard.php">Dashboard</a></li>
            <li><a href="patient_queue.php">Antrian Pasien</a></li>
            <li><a href="medical_records.php">Rekam Medis</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Pemeriksaan Pasien</h2>
            </div>
            <div class="card-body">
                <div class="patient-info">
                    <h3>Informasi Pasien:</h3>
                    <p>Nama: <?php echo $patient['nama_pasien']; ?></p>
                    <p>No. Rekam Medis: <?php echo $patient['Nomor_Rekam_Medis']; ?></p>
                    <p>Tekanan Darah: <?php echo $patient['Tekanan_Darah']; ?></p>
                    <p>Tinggi Badan: <?php echo $patient['Tinggi_Badan']; ?> cm</p>
                    <p>Berat Badan: <?php echo $patient['Berat_Badan']; ?> kg</p>
                    <p>Suhu: <?php echo $patient['Suhu']; ?> °C</p>
                    <p>Riwayat Penyakit: <?php echo $patient['Riwayat_Penyakit']; ?></p>
                </div>

                <form action="save_examination.php" method="POST" class="examination-form">
                    <input type="hidden" name="pendaftaran_id" value="<?php echo $pendaftaran_id; ?>">
                    
                    <div class="form-group">
                        <label for="diagnosa">Diagnosa:</label>
                        <textarea id="diagnosa" name="diagnosa" rows="4" required></textarea>
                    </div>

                    <div class="form-group">
                        <label for="resep">Resep Obat:</label>
                        <textarea id="resep" name="resep" rows="4" required></textarea>
                    </div>

                    <button type="submit" class="btn btn-primary">Simpan & Selesai</button>
                    <button type="button" class="btn btn-secondary" onclick="history.back()">Kembali</button>
                </form>
            </div>
        </div>
    </div>
</body>
</html>

<?php
closeDB($conn);
?>