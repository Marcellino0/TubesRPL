<?php
session_start();
require_once('../config/db_connection.php');

// Check if nurse is logged in
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: ../index.php");
    exit();
}

// Function to get pending examinations
function getPendingExaminations($conn)
{
    $sql = "SELECT p.ID_Pendaftaran, ps.ID_Pasien, ps.Nama as nama_pasien, ps.Nomor_Rekam_Medis, 
            d.Nama as nama_dokter, p.No_Antrian, p.Waktu_Daftar, p.Status,
            rm.ID_Rekam as rekam_exists
            FROM pendaftaran p
            JOIN pasien ps ON p.ID_Pasien = ps.ID_Pasien
            JOIN jadwal_dokter j ON p.ID_Jadwal = j.ID_Jadwal
            JOIN dokter d ON j.ID_Dokter = d.ID_Dokter
            LEFT JOIN rekam_medis rm ON ps.ID_Pasien = rm.ID_Pasien 
            AND DATE(rm.Tanggal) = DATE(p.Waktu_Daftar)
            WHERE p.Status = 'Menunggu'
            ORDER BY p.Waktu_Daftar ASC";

    $result = $conn->query($sql);
    return $result;
}
// Handle form submission for medical record and file upload
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_record'])) {
    $id_pasien = $_POST['id_pasien'];
    $tekanan_darah = $_POST['tekanan_darah'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $suhu = $_POST['suhu'];
    $riwayat = $_POST['riwayat_penyakit'];
    $tanggal = date('Y-m-d');

    // Insert rekam medis
    $sql_rekam_medis = "INSERT INTO rekam_medis (ID_Pasien, Tekanan_Darah, Tinggi_Badan, Berat_Badan, 
                        Suhu, Riwayat_Penyakit, Tanggal) 
                        VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($sql_rekam_medis);
    $stmt->bind_param(
        "isdddss",
        $id_pasien,
        $tekanan_darah,
        $tinggi_badan,
        $berat_badan,
        $suhu,
        $riwayat,
        $tanggal
    );

    if ($stmt->execute()) {
        $rekam_medis_id = $stmt->insert_id; // ID Rekam Medis yang baru ditambahkan

        // Handle file uploads jika ada
        if (!empty($_FILES['dokumen']['name'][0])) {
            $upload_dir = __DIR__ . '/uploads/' . $id_pasien . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true); // Buat folder jika belum ada
            }

            $uploaded_files = $_FILES['dokumen'];
            $file_count = count($uploaded_files['name']);
            $upload_success = true;

            for ($i = 0; $i < $file_count; $i++) {
                $file_name = basename($uploaded_files['name'][$i]);
                $file_tmp = $uploaded_files['tmp_name'][$i];
                $file_error = $uploaded_files['error'][$i];
                $jenis_dokumen = $_POST['jenis_dokumen'][$i];
                $keterangan = $_POST['keterangan_dokumen'][$i];

                // Validasi file
                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_types)) {
                    $_SESSION['error'] = "Format file tidak valid untuk $file_name.";
                    continue;
                }

                if ($uploaded_files['size'][$i] > 5242880) { // Maksimal 5MB
                    $_SESSION['error'] = "File $file_name melebihi ukuran maksimum 5MB.";
                    continue;
                }

                // Pindahkan file ke folder tujuan
                $new_file_name = uniqid('doc_') . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
                    // Insert ke tabel dokumen_medis
                    $sql_dokumen = "INSERT INTO dokumen_medis (ID_Pasien, Nama_File, Jenis_Dokumen, 
                                    Keterangan, Tanggal_Upload, Path_File) 
                                    VALUES (?, ?, ?, ?, NOW(), ?)";
                    $stmt_dokumen = $conn->prepare($sql_dokumen);
                    $stmt_dokumen->bind_param(
                        "issss",
                        $id_pasien,
                        $new_file_name,
                        $jenis_dokumen,
                        $keterangan,
                        $file_path
                    );

                    if (!$stmt_dokumen->execute()) {
                        $upload_success = false;
                        error_log("Error inserting file to database: " . $stmt_dokumen->error);
                    }
                } else {
                    $upload_success = false;
                    error_log("Failed to move uploaded file: " . $file_name);
                }
            }

            // Tampilkan pesan sukses atau error
            if ($upload_success) {
                $_SESSION['success'] = "Rekam medis dan file dokumen berhasil ditambahkan.";
            } else {
                $_SESSION['error'] = "Rekam medis berhasil, tetapi beberapa file gagal diunggah.";
            }
        } else {
            $_SESSION['success'] = "Rekam medis berhasil ditambahkan tanpa dokumen.";
        }
    } else {
        $_SESSION['error'] = "Gagal menambahkan rekam medis: " . $conn->error;
    }

    // Redirect ke dashboard
    header("Location: nurse_dashboard.php");
    exit();
}


// Get today's pending examinations
$pendingExaminations = getPendingExaminations($conn);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perawat - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
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
                        <a class="nav-link active" href="nurse_dashboard.php">Dashboard</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="medical_records.php">Rekam Medis</a>
                    </li>
                </ul>
                <div class="navbar-nav">
                    <li class="nav-item">
                        <span class="nav-link">Welcome, <?php echo $_SESSION['nama']; ?></span>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="logout.php">Logout</a>
                    </li>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <?php if (isset($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show">
                <?php
                echo $_SESSION['success'];
                unset($_SESSION['success']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <?php if (isset($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show">
                <?php
                echo $_SESSION['error'];
                unset($_SESSION['error']);
                ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <h2>Daftar Pasien Hari Ini</h2>
        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>No. Antrian</th>
                        <th>Nama Pasien</th>
                        <th>No. Rekam Medis</th>
                        <th>Dokter</th>
                        <th>Waktu Daftar</th>
                        <th>Status</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $pendingExaminations->fetch_assoc()): ?>
                        <tr>
                            <td><?php echo $row['No_Antrian']; ?></td>
                            <td><?php echo $row['nama_pasien']; ?></td>
                            <td><?php echo $row['Nomor_Rekam_Medis']; ?></td>
                            <td><?php echo $row['nama_dokter']; ?></td>
                            <td><?php echo date('H:i', strtotime($row['Waktu_Daftar'])); ?></td>
                            <td>
                                <?php if ($row['rekam_exists']): ?>
                                    <span class="badge bg-success">Sudah Diperiksa</span>
                                <?php else: ?>
                                    <span class="badge bg-warning">Menunggu Pemeriksaan</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if (!$row['rekam_exists']): ?>
                                    <button type="button" class="btn btn-primary btn-sm" data-bs-toggle="modal"
                                        data-bs-target="#recordModal<?php echo $row['ID_Pendaftaran']; ?>">
                                        <i class="fas fa-notes-medical"></i> Input Pemeriksaan
                                    </button>
                                <?php endif; ?>
                            </td>
                        </tr>

                        <!-- Modal for medical record input -->
                        <div class="modal fade" id="recordModal<?php echo $row['ID_Pendaftaran']; ?>" tabindex="-1">
                            <div class="modal-dialog">
                                <div class="modal-content">
                                    <div class="modal-header">
                                        <h5 class="modal-title">Input Pemeriksaan Awal</h5>
                                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                    </div>
                                    <form method="POST" enctype="multipart/form-data">
                                        <div class="modal-body">
                                            <input type="hidden" name="id_pasien" value="<?php echo $row['ID_Pasien']; ?>">

                                            <div class="mb-3">
                                                <label class="form-label">Tekanan Darah</label>
                                                <input type="text" class="form-control" name="tekanan_darah" required
                                                    placeholder="Contoh: 120/80">
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Tinggi Badan (cm)</label>
                                                <input type="number" step="0.1" class="form-control" name="tinggi_badan"
                                                    required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Berat Badan (kg)</label>
                                                <input type="number" step="0.1" class="form-control" name="berat_badan"
                                                    required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Suhu Badan (°C)</label>
                                                <input type="number" step="0.1" class="form-control" name="suhu" required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keluhan/Riwayat Penyakit</label>
                                                <textarea class="form-control" name="riwayat_penyakit" rows="3"
                                                    required></textarea>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label">Upload Dokumen</label>
                                                <input type="file" name="dokumen[]" class="form-control" multiple required>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Jenis Dokumen</label>
                                                <select name="jenis_dokumen[]" class="form-select" required>
                                                    <option value="Hasil Lab">Hasil Lab</option>
                                                    <option value="Resep">Resep</option>
                                                    <option value="Rujukan">Rujukan</option>
                                                    <option value="Lainnya">Lainnya</option>
                                                </select>
                                            </div>

                                            <div class="mb-3">
                                                <label class="form-label">Keterangan</label>
                                                <textarea name="keterangan_dokumen[]" class="form-control"
                                                    rows="2"></textarea>
                                            </div>

                        

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-secondary"
                                                data-bs-dismiss="modal">Tutup</button>
                                            <button type="submit" name="submit_record"
                                                class="btn btn-primary">Simpan</button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function addDocumentField() {
            const container = document.getElementById('documentFields');
            const newEntry = document.createElement('div');
            newEntry.className = 'document-entry mb-3';
            newEntry.innerHTML = `
        <div class="row">
            <div class="col-md-6">
                <input type="file" class="form-control" name="dokumen[]" accept=".pdf,.jpg,.jpeg,.png">
            </div>
            <div class="col-md-3">
                <select class="form-select" name="jenis_dokumen[]" required>
                    <option value="">Pilih Jenis</option>
                    <option value="Hasil Lab">Hasil Lab</option>
                    <option value="Resep">Resep</option>
                    <option value="Rujukan">Rujukan</option>
                    <option value="Lainnya">Lainnya</option>
                </select>
            </div>
            <div class="col-md-3">
                <div class="input-group">
                    <input type="text" class="form-control" name="keterangan_dokumen[]" 
                           placeholder="Keterangan">
                    <button type="button" class="btn btn-danger" onclick="removeDocumentField(this)">
                        <i class="fas fa-times"></i>
                    </button>
                </div>
            </div>
        </div>
    `;
            container.appendChild(newEntry);
        }

        function removeDocumentField(button) {
            button.closest('.document-entry').remove();
        }
    </script>
</body>

</html>