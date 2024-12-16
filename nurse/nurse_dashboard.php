<?php
session_start();
require_once('../config/db_connection.php');


if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: ../index.php");
    exit();
}

// Fungsi untuk mengambil daftar pasien yang masih menunggu pemeriksaan hari ini
function getPendingExaminations($conn)
{
    $sql = "SELECT p.ID_Pendaftaran, ps.ID_Pasien, ps.Nama as nama_pasien, ps.Nomor_Rekam_Medis, 
            d.Nama as nama_dokter, p.No_Antrian, p.Waktu_Daftar, p.Status,
            rm.ID_Rekam as rekam_exists, rm.Tekanan_Darah, rm.Tinggi_Badan, 
            rm.Berat_Badan, rm.Suhu, rm.Riwayat_Penyakit,
            DATE_FORMAT(p.Waktu_Daftar, '%d/%m/%Y') as Tanggal_Daftar
            FROM pendaftaran p
            JOIN pasien ps ON p.ID_Pasien = ps.ID_Pasien
            JOIN jadwal_dokter j ON p.ID_Jadwal = j.ID_Jadwal
            JOIN dokter d ON j.ID_Dokter = d.ID_Dokter
            LEFT JOIN rekam_medis rm ON ps.ID_Pasien = rm.ID_Pasien 
            AND DATE(rm.Tanggal) = DATE(p.Waktu_Daftar)
            WHERE p.Status = 'Menunggu'
            AND DATE(p.Waktu_Daftar) = CURDATE()
            AND p.Verifikasi = 'Terverifikasi'
            AND p.No_Antrian > 0
            ORDER BY p.No_Antrian ASC";

    $result = $conn->query($sql);
    return $result;
}

// Proses form untuk memperbarui rekam medis
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['update_record'])) {
    $id_rekam = $_POST['id_rekam'];
    $tekanan_darah = $_POST['tekanan_darah'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $suhu = $_POST['suhu'];
    $riwayat = $_POST['riwayat_penyakit'];

    // Update rekam medis
    $sql_update = "UPDATE rekam_medis SET 
                   Tekanan_Darah = ?, 
                   Tinggi_Badan = ?, 
                   Berat_Badan = ?, 
                   Suhu = ?, 
                   Riwayat_Penyakit = ?
                   WHERE ID_Rekam = ?";

    $stmt = $conn->prepare($sql_update);
    $stmt->bind_param(
        "sdddsi",
        $tekanan_darah,
        $tinggi_badan,
        $berat_badan,
        $suhu,
        $riwayat,
        $id_rekam
    );

    if ($stmt->execute()) {
        // Handle new file uploads if any
        if (!empty($_FILES['dokumen']['name'][0])) {
            $id_pasien = $_POST['id_pasien'];
            $upload_dir = __DIR__ . '/uploads/' . $id_pasien . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
            }

            $uploaded_files = $_FILES['dokumen'];
            $file_count = count($uploaded_files['name']);
            $upload_success = true;

            for ($i = 0; $i < $file_count; $i++) {
                // Process new file uploads similarly to the original code
                $file_name = basename($uploaded_files['name'][$i]);
                $file_tmp = $uploaded_files['tmp_name'][$i];
                $file_error = $uploaded_files['error'][$i];
                $jenis_dokumen = $_POST['jenis_dokumen'][$i];
                $keterangan = $_POST['keterangan_dokumen'][$i];

                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_types)) {
                    $_SESSION['error'] = "Format file tidak valid untuk $file_name.";
                    continue;
                }

                if ($uploaded_files['size'][$i] > 5242880) {
                    $_SESSION['error'] = "File $file_name melebihi ukuran maksimum 5MB.";
                    continue;
                }

                $new_file_name = uniqid('doc_') . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
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
        }
        $_SESSION['success'] = "Rekam medis berhasil diperbarui.";
    } else {
        $_SESSION['error'] = "Gagal memperbarui rekam medis: " . $conn->error;
    }

    header("Location: nurse_dashboard.php");
    exit();
}

// Handle form submission for new medical record
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['submit_record'])) {
    $id_pasien = $_POST['id_pasien'];
    $tekanan_darah = $_POST['tekanan_darah'];
    $tinggi_badan = $_POST['tinggi_badan'];
    $berat_badan = $_POST['berat_badan'];
    $suhu = $_POST['suhu'];
    $riwayat = $_POST['riwayat_penyakit'];

    // Insert new rekam medis
    $sql_insert = "INSERT INTO rekam_medis (ID_Pasien, Tekanan_Darah, Tinggi_Badan, 
                   Berat_Badan, Suhu, Riwayat_Penyakit, Tanggal) 
                   VALUES (?, ?, ?, ?, ?, ?, CURDATE())";

    $stmt = $conn->prepare($sql_insert);
    $stmt->bind_param(
        "isddds",
        $id_pasien,
        $tekanan_darah,
        $tinggi_badan,
        $berat_badan,
        $suhu,
        $riwayat
    );

    if ($stmt->execute()) {
        $id_rekam = $conn->insert_id;

        // Handle file uploads
        if (!empty($_FILES['dokumen']['name'][0])) {
            $upload_dir = __DIR__ . '/uploads/' . $id_pasien . '/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0777, true);
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

                $allowed_types = ['pdf', 'jpg', 'jpeg', 'png'];
                $file_ext = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

                if (!in_array($file_ext, $allowed_types)) {
                    $_SESSION['error'] = "Format file tidak valid untuk $file_name.";
                    continue;
                }

                if ($uploaded_files['size'][$i] > 5242880) {
                    $_SESSION['error'] = "File $file_name melebihi ukuran maksimum 5MB.";
                    continue;
                }

                $new_file_name = uniqid('doc_') . '.' . $file_ext;
                $file_path = $upload_dir . $new_file_name;

                if (move_uploaded_file($file_tmp, $file_path)) {
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
        }
        $_SESSION['success'] = "Data rekam medis berhasil disimpan.";
    } else {
        $_SESSION['error'] = "Gagal menyimpan rekam medis: " . $conn->error;
    }

    header("Location: nurse_dashboard.php");
    exit();
}

// Get today's pending examinations
$pendingExaminations = getPendingExaminations($conn);
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perawat - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <!-- Retain Bootstrap for modal functionality -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">Poliklinik X</h1>
                <nav class="space-y-2">
                    <a href="nurse_dashboard.php"
                        class="flex items-center space-x-3 p-3 rounded bg-blue-900 text-white">
                        <i class="fas fa-home text-white"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="medical_records.php"
                        class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700 text-white hover:text-white">
                        <i class="fas fa-file-medical text-white"></i>
                        <span>Rekam Medis</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 bg-blue-900">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-user-nurse text-2xl"></i>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                        <p class="text-sm text-gray-300">Perawat</p>
                    </div>
                </div>
                <a href="logout.php" class="flex items-center space-x-3 p-2 rounded hover:bg-blue-800 text-red-300">
                    <i class="fas fa-sign-out-alt"></i>
                    <span>Logout</span>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <!-- Patient Queue -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Daftar Pasien Hari Ini</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Antrian
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Pasien
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Rekam Medis
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Dokter
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Daftar
                                    </th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $pendingExaminations->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['No_Antrian']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['nama_pasien']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Nomor_Rekam_Medis']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['nama_dokter']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Tanggal_Daftar']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if (!$row['rekam_exists']): ?>
                                                <button type="button"
                                                    class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#recordModal<?php echo $row['ID_Pendaftaran']; ?>">
                                                    Input Pemeriksaan
                                                </button>
                                            <?php else: ?>
                                                <button type="button"
                                                    class="text-white bg-green-600 hover:bg-green-700 px-4 py-2 rounded-md text-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#updateModal<?php echo $row['ID_Pendaftaran']; ?>">
                                                    Update Pemeriksaan
                                                </button>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Keep existing modal(s) with Bootstrap styling -->
    <?php
    // Reset pointer again
    $pendingExaminations->data_seek(0);
    while ($row = $pendingExaminations->fetch_assoc()): ?>
        <div class="modal fade" id="recordModal<?php echo $row['ID_Pendaftaran']; ?>" tabindex="-1">
            <!-- Existing modal code remains unchanged -->
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Input Pemeriksaan Awal</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <!-- All existing form fields are kept exactly the same as in the original file -->
                            <input type="hidden" name="id_pasien" value="<?php echo $row['ID_Pasien']; ?>">

                            <div class="mb-3">
                                <label class="form-label">Tekanan Darah</label>
                                <input type="text" class="form-control" name="tekanan_darah" required
                                    placeholder="Contoh: 120/80">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.1" class="form-control" name="tinggi_badan" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Berat Badan (kg)</label>
                                <input type="number" step="0.1" class="form-control" name="berat_badan" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Suhu Badan (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="suhu" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keluhan/Riwayat Penyakit</label>
                                <textarea class="form-control" name="riwayat_penyakit" rows="3" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Upload Dokumen (Opsional)</label>
                                <input type="file" name="dokumen[]" class="form-control" multiple>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Dokumen</label>
                                <select name="jenis_dokumen[]" class="form-select">
                                    <option value="">Pilih Jenis</option>
                                    <option value="Hasil Lab">Hasil Lab</option>
                                    <option value="Resep">Resep</option>
                                    <option value="Rujukan">Rujukan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <!-- <div class="mb-3">
                                <label class="form-label">Keterangan</label>
                                <textarea name="keterangan_dokumen[]" class="form-control" rows="2"></textarea>
                            </div> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" name="submit_record" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        <!-- Modal untuk update pemeriksaan -->
        <div class="modal fade" id="updateModal<?php echo $row['ID_Pendaftaran']; ?>" tabindex="-1">
            <div class="modal-dialog">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">Update Pemeriksaan</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST" enctype="multipart/form-data">
                        <div class="modal-body">
                            <input type="hidden" name="id_pasien" value="<?php echo $row['ID_Pasien']; ?>">
                            <input type="hidden" name="id_rekam" value="<?php echo $row['rekam_exists']; ?>">

                            <div class="mb-3">
                                <label class="form-label">Tekanan Darah</label>
                                <input type="text" class="form-control" name="tekanan_darah"
                                    value="<?php echo htmlspecialchars($row['Tekanan_Darah'] ?? ''); ?>" required
                                    placeholder="Contoh: 120/80">
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Tinggi Badan (cm)</label>
                                <input type="number" step="0.1" class="form-control" name="tinggi_badan"
                                    value="<?php echo htmlspecialchars($row['Tinggi_Badan'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Berat Badan (kg)</label>
                                <input type="number" step="0.1" class="form-control" name="berat_badan"
                                    value="<?php echo htmlspecialchars($row['Berat_Badan'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Suhu Badan (°C)</label>
                                <input type="number" step="0.1" class="form-control" name="suhu"
                                    value="<?php echo htmlspecialchars($row['Suhu'] ?? ''); ?>" required>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Keluhan/Riwayat Penyakit</label>
                                <textarea class="form-control" name="riwayat_penyakit" rows="3" required>
                                    <?php echo htmlspecialchars($row['Riwayat_Penyakit'] ?? ''); ?>
                                </textarea>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Upload Dokumen</label>
                                <input type="file" name="dokumen[]" class="form-control" multiple>
                            </div>

                            <div class="mb-3">
                                <label class="form-label">Jenis Dokumen</label>
                                <select name="jenis_dokumen[]" class="form-select">
                                    <option value="Hasil Lab">Hasil Lab</option>
                                    <option value="Resep">Resep</option>
                                    <option value="Rujukan">Rujukan</option>
                                    <option value="Lainnya">Lainnya</option>
                                </select>
                            </div>

                            <!-- <div class="mb-3">
                            <label class="form-label">Keterangan</label>
                            <textarea name="keterangan_dokumen[]" class="form-control" rows="2"></textarea>
                        </div> -->
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" name="update_record" class="btn btn-primary">Update</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endwhile; ?>


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
</ReactProject>