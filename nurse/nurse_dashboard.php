<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in and is a nurse
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: ../index.php");
    exit();
}

// Function to get today's appointments
function getTodayAppointments($conn) {
    $query = "
        SELECT 
            p.ID_Pendaftaran,
            pas.Nama AS nama_pasien,
            pas.Nomor_Rekam_Medis,
            d.Nama AS nama_dokter,
            d.Spesialis,
            p.No_Antrian,
            p.Status,
            p.Waktu_Daftar,
            pas.ID_Pasien
        FROM pendaftaran p
        JOIN pasien pas ON p.ID_Pasien = pas.ID_Pasien
        JOIN jadwal_dokter j ON p.ID_Jadwal = j.ID_Jadwal
        JOIN dokter d ON j.ID_Dokter = d.ID_Dokter
        WHERE DATE(p.Waktu_Daftar) = CURDATE()
        ORDER BY p.No_Antrian ASC
    ";
    
    $result = $conn->query($query);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Handle form submission for patient vitals
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['submit_vitals'])) {
    try {
        $sql = "INSERT INTO rekam_medis (
            ID_Pasien, 
            Tekanan_Darah, 
            Tinggi_Badan, 
            Berat_Badan, 
            Suhu, 
            Riwayat_Penyakit,
            Tanggal
        ) VALUES (?, ?, ?, ?, ?, ?, CURDATE())";
        
        $stmt = $conn->prepare($sql);
        $stmt->bind_param(
            "isddds",
            $_POST['id_pasien'],
            $_POST['tekanan_darah'],
            $_POST['tinggi_badan'],
            $_POST['berat_badan'],
            $_POST['suhu'],
            $_POST['riwayat_penyakit']
        );
        
        if ($stmt->execute()) {
            $_SESSION['success'] = "Data vital pasien berhasil disimpan!";
        } else {
            $_SESSION['error'] = "Gagal menyimpan data vital pasien.";
        }
        
        header("Location: nurse_dashboard.php");
        exit();
    } catch (Exception $e) {
        $_SESSION['error'] = "Terjadi kesalahan: " . $e->getMessage();
    }
}

// Get today's appointments
$appointments = getTodayAppointments($conn);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perawat - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.min.css" rel="stylesheet">
</head>
<body>

<nav class="navbar navbar-expand-lg navbar-dark bg-primary">
    <div class="container">
        <a class="navbar-brand" href="#">Poliklinik X</a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarNav">
            <ul class="navbar-nav me-auto">
                <li class="nav-item">
                    <a class="nav-link active" href="#">Dashboard</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="#">Riwayat Pemeriksaan</a>
                </li>
            </ul>
            <span class="navbar-text me-3">
                Selamat datang, <?php echo htmlspecialchars($_SESSION['nama']); ?>
            </span>
            <a href="../logout.php" class="btn btn-light">Logout</a>
        </div>
    </div>
</nav>

<div class="container mt-4">
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['success'];
            unset($_SESSION['success']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?php 
            echo $_SESSION['error'];
            unset($_SESSION['error']);
            ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="row">
        <div class="col-md-8">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Daftar Pasien Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>No. Antrian</th>
                                    <th>Nama Pasien</th>
                                    <th>No. Rekam Medis</th>
                                    <th>Dokter</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($appointments as $appointment): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($appointment['No_Antrian']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['nama_pasien']); ?></td>
                                    <td><?php echo htmlspecialchars($appointment['Nomor_Rekam_Medis']); ?></td>
                                    <td>
                                        <?php echo htmlspecialchars($appointment['nama_dokter']); ?>
                                        <br>
                                        <small class="text-muted"><?php echo htmlspecialchars($appointment['Spesialis']); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-<?php echo $appointment['Status'] === 'Menunggu' ? 'warning' : 'success'; ?>">
                                            <?php echo htmlspecialchars($appointment['Status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <button type="button" 
                                                class="btn btn-sm btn-primary" 
                                                data-bs-toggle="modal" 
                                                data-bs-target="#vitalsModal"
                                                data-id="<?php echo $appointment['ID_Pasien']; ?>"
                                                data-name="<?php echo htmlspecialchars($appointment['nama_pasien']); ?>">
                                            Catat Vital
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-4">
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0">Statistik Hari Ini</h5>
                </div>
                <div class="card-body">
                    <div class="mb-3">
                        <h6>Total Pasien</h6>
                        <h2><?php echo count($appointments); ?></h2>
                    </div>
                    <div class="mb-3">
                        <h6>Status Pemeriksaan</h6>
                        <?php
                        $waiting = array_filter($appointments, function($a) { return $a['Status'] === 'Menunggu'; });
                        $completed = array_filter($appointments, function($a) { return $a['Status'] === 'Selesai'; });
                        ?>
                        <div class="d-flex justify-content-between">
                            <span>Menunggu</span>
                            <span class="badge bg-warning"><?php echo count($waiting); ?></span>
                        </div>
                        <div class="d-flex justify-content-between mt-2">
                            <span>Selesai</span>
                            <span class="badge bg-success"><?php echo count($completed); ?></span>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Modal for recording vital signs -->
<div class="modal fade" id="vitalsModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Catat Data Vital Pasien</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form action="nurse_dashboard.php" method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id_pasien" id="patientId">
                    
                    <div class="mb-3">
                        <label class="form-label">Nama Pasien:</label>
                        <input type="text" class="form-control" id="patientName" readonly>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tekanan Darah (mmHg):</label>
                        <input type="text" class="form-control" name="tekanan_darah" required 
                               placeholder="Contoh: 120/80">
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Tinggi Badan (cm):</label>
                        <input type="number" step="0.1" class="form-control" name="tinggi_badan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Berat Badan (kg):</label>
                        <input type="number" step="0.1" class="form-control" name="berat_badan" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Suhu Badan (°C):</label>
                        <input type="number" step="0.1" class="form-control" name="suhu" required>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Riwayat Penyakit/Keluhan:</label>
                        <textarea class="form-control" name="riwayat_penyakit" rows="3" required></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                    <button type="submit" name="submit_vitals" class="btn btn-primary">Simpan Data</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11.7.5/dist/sweetalert2.all.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Handle modal data
    const vitalsModal = document.getElementById('vitalsModal');
    if (vitalsModal) {
        vitalsModal.addEventListener('show.bs.modal', function(event) {
            const button = event.relatedTarget;
            const patientId = button.getAttribute('data-id');
            const patientName = button.getAttribute('data-name');
            
            document.getElementById('patientId').value = patientId;
            document.getElementById('patientName').value = patientName;
        });
    }
    
    // Handle alerts
    const alerts = document.querySelectorAll('.alert');
    alerts.forEach(alert => {
        setTimeout(() => {
            const bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }, 5000);
    });
});
</script>

</body>
</html>