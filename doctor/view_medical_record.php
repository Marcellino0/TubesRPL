<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$dokter_id = $_SESSION['user_id'];
$pasien_id = $_GET['id'];


$sql = "SELECT * FROM Pasien WHERE ID_Pasien = $pasien_id";
$result = sqlsrv_query($conn, $sql);
$patient = sqlsrv_fetch_array($result);


$sql = "SELECT TOP 1 * FROM Rekam_Medis 
        WHERE ID_Pasien = $pasien_id 
        ORDER BY Tanggal DESC";
$result = sqlsrv_query($conn, $sql);
$vital_signs = sqlsrv_fetch_array($result);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Detail Rekam Medis - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="doctor_dashboard.php">Dashboard</a></li>
            <li><a href="medical_records.php">Kembali ke Rekam Medis</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Detail Rekam Medis Pasien</h2>
            </div>
            <div class="card-body">
                <!-- Informasi Pasien -->
                <div class="patient-info">
                    <h3>Informasi Pasien</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Nama:</label>
                            <span><?php echo $patient['Nama']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>No. RM:</label>
                            <span><?php echo $patient['Nomor_Rekam_Medis']; ?></span>
                        </div>
                        <div class="info-item">
                            <label>Tanggal Lahir:</label>
                            <span><?php echo $patient['Tanggal_Lahir']->format('d-m-Y'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Umur:</label>
                            <span><?php echo $patient['Umur']; ?> tahun</span>
                        </div>
                    </div>
                </div>

                <!-- Tanda Vital Terakhir -->
                <?php if($vital_signs): ?>
                <div class="vital-signs">
                    <h3>Tanda Vital Terakhir (<?php echo $vital_signs['Tanggal']->format('d-m-Y'); ?>)</h3>
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Tekanan Darah:</label>
                            <span><?php echo $vital_signs['Tekanan_Darah']; ?> mmHg</span>
                        </div>
                        <div class="info-item">
                            <label>Tinggi Badan:</label>
                            <span><?php echo $vital_signs['Tinggi_Badan']; ?> cm</span>
                        </div>
                        <div class="info-item">
                            <label>Berat Badan:</label>
                            <span><?php echo $vital_signs['Berat_Badan']; ?> kg</span>
                        </div>
                        <div class="info-item">
                            <label>Suhu:</label>
                            <span><?php echo $vital_signs['Suhu']; ?> °C</span>
                        </div>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Riwayat Kunjungan -->
                <div class="visit-history">
                    <h3>Riwayat Kunjungan</h3>
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Tanggal</th>
                                    <th>Dokter</th>
                                    <th>Diagnosa</th>
                                    <th>Resep</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $sql = "SELECT pem.Waktu_Periksa, d.Nama as nama_dokter, 
                                       pem.Diagnosa, r.Resep_Obat
                                       FROM Pemeriksaan pem
                                       JOIN Pendaftaran p ON pem.ID_Pendaftaran = p.ID_Pendaftaran
                                       JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
                                       JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
                                       LEFT JOIN Resep r ON pem.ID_Pemeriksaan = r.ID_Pemeriksaan
                                       WHERE p.ID_Pasien = $pasien_id
                                       ORDER BY pem.Waktu_Periksa DESC";
                                
                                $result = sqlsrv_query($conn, $sql);
                                
                                while($row = sqlsrv_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['Waktu_Periksa']->format('d-m-Y H:i') . "</td>";
                                    echo "<td>" . $row['nama_dokter'] . "</td>";
                                    echo "<td>" . $row['Diagnosa'] . "</td>";
                                    echo "<td>" . $row['Resep_Obat'] . "</td>";
                                    echo "</tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Riwayat Penyakit -->
                <div class="disease-history">
                    <h3>Riwayat Penyakit</h3>
                    <p><?php echo $vital_signs['Riwayat_Penyakit'] ?? 'Tidak ada riwayat penyakit tercatat'; ?></p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
closeDB($conn);
?>