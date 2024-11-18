<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: login.php");
    exit();
}

if(!isset($_GET['id'])) {
    header("Location: my_appointments.php");
    exit();
}

$conn = connectDB();
$pembayaran_id = intval($_GET['id']);
$pasien_id = $_SESSION['user_id'];

// Get payment details
$sql = "SELECT pb.*, p.ID_Pasien, p.No_Antrian, 
        pas.Nama as nama_pasien, pas.Nomor_Rekam_Medis,
        d.Nama as nama_dokter, d.Spesialis,
        pem.Diagnosa, r.Resep_Obat
        FROM Pembayaran pb
        JOIN Pendaftaran p ON pb.ID_Pendaftaran = p.ID_Pendaftaran
        JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
        JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
        JOIN Dokter d ON j.ID_Dokter = d.ID_Dokter
        LEFT JOIN Pemeriksaan pem ON p.ID_Pendaftaran = pem.ID_Pendaftaran
        LEFT JOIN Resep r ON pem.ID_Pemeriksaan = r.ID_Pemeriksaan
        WHERE pb.ID_Pembayaran = $pembayaran_id 
        AND p.ID_Pasien = $pasien_id";

$result = sqlsrv_query($conn, $sql);
$payment = sqlsrv_fetch_array($result);

if(!$payment || $payment['Status'] == 'Lunas') {
    $_SESSION['error'] = "Pembayaran tidak ditemukan atau sudah lunas";
    header("Location: my_appointments.php");
    exit();
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="patient_dashboard.php">Dashboard</a></li>
            <li><a href="my_appointments.php">Kembali ke Riwayat</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php if(isset($_SESSION['error'])): ?>
            <div class="alert alert-danger">
                <?php 
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header">
                <h2>Detail Pembayaran</h2>
            </div>
            <div class="card-body">
                <div class="payment-info">
                    <div class="info-section">
                        <h3>Informasi Pasien</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Nama:</label>
                                <span><?php echo $payment['nama_pasien']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>No. RM:</label>
                                <span><?php echo $payment['Nomor_Rekam_Medis']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>No. Antrian:</label>
                                <span><?php echo $payment['No_Antrian']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Detail Pemeriksaan</h3>
                        <div class="info-grid">
                            <div class="info-item">
                                <label>Dokter:</label>
                                <span><?php echo $payment['nama_dokter']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Spesialis:</label>
                                <span><?php echo $payment['Spesialis']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Diagnosa:</label>
                                <span><?php echo $payment['Diagnosa']; ?></span>
                            </div>
                            <div class="info-item">
                                <label>Resep:</label>
                                <span><?php echo $payment['Resep_Obat']; ?></span>
                            </div>
                        </div>
                    </div>

                    <div class="info-section">
                        <h3>Rincian Biaya</h3>
                        <div class="payment-details">
                            <div class="payment-item">
                                <span class="item-name">Biaya Konsultasi</span>
                                <span class="item-price">Rp <?php echo number_format($payment['Jumlah'], 0, ',', '.'); ?></span>
                            </div>
                        </div>
                    </div>

                    <form action="process_payment.php" method="POST" class="payment-form">
                        <input type="hidden" name="pembayaran_id" value="<?php echo $pembayaran_id; ?>">
                        
                        <div class="form-group">
                            <label for="metode">Metode Pembayaran:</label>
                            <select id="metode" name="metode" required>
                                <option value="">Pilih Metode Pembayaran</option>
                                <option value="Transfer Bank">Transfer Bank</option>
                                <option value="Kartu Debit">Kartu Debit</option>
                                <option value="Kartu Kredit">Kartu Kredit</option>
                                <option value="Tunai">Tunai</option>
                            </select>
                        </div>

                        <div id="bankDetails" class="form-group" style="display: none;">
                            <label for="bank">Bank:</label>
                            <select id="bank" name="bank">
                                <option value="">Pilih Bank</option>
                                <option value="BCA">BCA</option>
                                <option value="Mandiri">Mandiri</option>
                                <option value="BNI">BNI</option>
                                <option value="BRI">BRI</option>
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary">Proses Pembayaran</button>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.getElementById('metode').addEventListener('change', function() {
            const bankDetails = document.getElementById('bankDetails');
            if(this.value === 'Transfer Bank') {
                bankDetails.style.display = 'block';
                document.getElementById('bank').required = true;
            } else {
                bankDetails.style.display = 'none';
                document.getElementById('bank').required = false;
            }
        });
    </script>
</body>
</html>

<?php
closeDB($conn);
?>