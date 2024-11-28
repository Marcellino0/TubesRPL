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

// Existing form submission code remains the same...

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
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                // Reset pointer after num_rows check
                                $pendingExaminations->data_seek(0);
                                while ($row = $pendingExaminations->fetch_assoc()): ?>
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
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if (!$row['rekam_exists']): ?>
                                                <button type="button"
                                                    class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-sm"
                                                    data-bs-toggle="modal"
                                                    data-bs-target="#recordModal<?php echo $row['ID_Pendaftaran']; ?>">
                                                    Input Pemeriksaan
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
                                <textarea name="keterangan_dokumen[]" class="form-control" rows="2"></textarea>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Tutup</button>
                            <button type="submit" name="submit_record" class="btn btn-primary">Simpan</button>
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