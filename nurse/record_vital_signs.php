<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$pendaftaran_id = $_GET['id'];

// Get patient information
$sql = "SELECT p.*, pas.Nama as nama_pasien, pas.Nomor_Rekam_Medis, 
        pas.ID_Pasien, d.Nama as nama_dokter
        FROM Pendaftaran p
        JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
        JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
        JOIN Dokter d ON j.ID_Dokter = d.ID_Dokter
        WHERE p.ID_Pendaftaran = $pendaftaran_id";

$result = sqlsrv_query($conn, $sql);
$patient = sqlsrv_fetch_array($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Catat Tanda Vital - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="nurse_dashboard.php">Dashboard</a></li>
            <li><a href="waiting_patients.php">Pasien Menunggu</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Catat Tanda Vital Pasien</h2>
            </div>
            <div class="card-body">
                <div class="patient-info">
                    <h3>Informasi Pasien:</h3>
                    <p>Nama: <?php echo $patient['nama_pasien']; ?></p>
                    <p>No. Rekam Medis: <?php echo $patient['Nomor_Rekam_Medis']; ?></p>
                    <p>Dokter: <?php echo $patient['nama_dokter']; ?></p>
                    <p>No. Antrian: <?php echo $patient['No_Antrian']; ?></p>
                </div>

                <form action="save_vital_signs.php" method="POST" class="vital-signs-form">
                    <input type="hidden" name="pendaftaran_id" value="<?php echo $pendaftaran_id; ?>">
                    <input type="hidden" name="pasien_id" value="<?php echo $patient['ID_Pasien']; ?>">
                    
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="tekanan_darah">Tekanan Darah (mmHg):</label>
                                <input type="text" id="tekanan_darah" name="tekanan_darah" 
                                       placeholder="mis. 120/80" required>
                            </div>

                            <div class="form-group">
                                <label for="tinggi_badan">Tinggi Badan (cm):</label>
                                <input type="number" id="tinggi_badan" name="tinggi_badan" 
                                       step="0.1" required>
                            </div>
                        </div>
                        
                        <div class="form-col">
                            <div class="form-group">
                                <label for="berat_badan">Berat Badan (kg):</label>
                                <input type="number" id="berat_badan" name="berat_badan" 
                                       step="0.1" required>
                            </div>

                            <div class="form-group">
                                <label for="suhu">Suhu (°C):</label>
                                <input type="number" id="suhu" name="suhu" step="0.1" required>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label for="riwayat_penyakit">Riwayat Penyakit:</label>
                        <textarea id="riwayat_penyakit" name="riwayat_penyakit" rows="4"></textarea>
                    </div>

                    <div class="form-group">
                        <label for="keluhan">Keluhan Saat Ini:</label>
                        <textarea id="keluhan" name="keluhan" rows="4" required></textarea>
                    </div>

                    <div class="button-group">
                        <button type="submit" class="btn btn-primary">Simpan</button>
                        <button type="button" class="btn btn-secondary" 
                                onclick="window.location.href='nurse_dashboard.php'">Batal</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        // Validasi format tekanan darah
        document.getElementById('tekanan_darah').addEventListener('blur', function() {
            const value = this.value;
            const pattern = /^\d{2,3}\/\d{2,3}$/;
            
            if(!pattern.test(value)) {
                alert('Format tekanan darah tidak valid. Gunakan format "120/80"');
                this.value = '';
            }
        });

        // Validasi range nilai
        document.querySelector('form').addEventListener('submit', function(e) {
            const suhu = parseFloat(document.getElementById('suhu').value);
            const tinggi = parseFloat(document.getElementById('tinggi_badan').value);
            const berat = parseFloat(document.getElementById('berat_badan').value);
            
            if(suhu < 35 || suhu > 42) {
                e.preventDefault();
                alert('Suhu harus berada di antara 35-42°C');
            }
            
            if(tinggi < 30 || tinggi > 250) {
                e.preventDefault();
                alert('Tinggi badan harus berada di antara 30-250 cm');
            }
            
            if(berat < 0 || berat > 300) {
                e.preventDefault();
                alert('Berat badan harus berada di antara 0-300 kg');
            }
        });
    </script>
</body>
</html>

<?php
closeDB($conn);
?>