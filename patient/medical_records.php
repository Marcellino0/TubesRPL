<?php
session_start();
require_once('../config/db_connection.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: index.php");
    exit();
}


$patientId = $_SESSION['user_id'];
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$recordsPerPage = 10;
$offset = ($page - 1) * $recordsPerPage;

// Menghitung total record untuk pagination
$stmt = $conn->prepare("
    SELECT COUNT(DISTINCT rm.ID_Rekam) as total 
    FROM Rekam_Medis rm 
    WHERE rm.ID_Pasien = ?
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$totalRecords = $stmt->get_result()->fetch_assoc()['total'];
$totalPages = ceil($totalRecords / $recordsPerPage);

// Mengambil detail rekam medis dengan pagination
$stmt = $conn->prepare("
    SELECT 
        rm.*,
        p.Diagnosa,
        p.Waktu_Periksa,
        r.Resep_Obat,
        d.Nama as nama_dokter,
        d.Spesialis,
        pend.No_Antrian
    FROM Rekam_Medis rm
    LEFT JOIN Pendaftaran pend ON rm.ID_Pasien = pend.ID_Pasien
    LEFT JOIN Pemeriksaan p ON pend.ID_Pendaftaran = p.ID_Pendaftaran
    LEFT JOIN Resep r ON p.ID_Pemeriksaan = r.ID_Pemeriksaan
    LEFT JOIN Dokter d ON p.ID_Dokter = d.ID_Dokter
    WHERE rm.ID_Pasien = ?
    ORDER BY rm.Tanggal DESC, p.Waktu_Periksa DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $patientId, $recordsPerPage, $offset);
$stmt->execute();
$medicalRecords = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Mengambil informasi dasar pasien
$stmt = $conn->prepare("SELECT Nama, Nomor_Rekam_Medis FROM Pasien WHERE ID_Pasien = ?");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$patientInfo = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Rekam Medis - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <!-- Navigation -->
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <a href="patient_dashboard.php" class="text-xl font-bold">Poliklinik X</a>
                </div>
                <div class="flex items-center space-x-4">
                    <span><?php echo htmlspecialchars($patientInfo['Nama']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <!-- Header Section -->
        <div class="bg-white rounded-lg shadow mb-6 p-6">
            <div class="flex justify-between items-center">
                <div>
                    <h1 class="text-2xl font-bold text-gray-800">Riwayat Rekam Medis</h1>
                    <p class="text-gray-600">No. Rekam Medis: <?php echo htmlspecialchars($patientInfo['Nomor_Rekam_Medis']); ?></p>
                </div>
                <a href="patient_dashboard.php" class="bg-blue-600 text-white px-4 py-2 rounded hover:bg-blue-700">
                    <i class="fas fa-arrow-left mr-2"></i>Kembali ke Dashboard
                </a>
            </div>
        </div>

        <!-- Medical Records List -->
        <?php if (empty($medicalRecords)): ?>
            <div class="bg-white rounded-lg shadow p-6 text-center">
                <p class="text-gray-500">Belum ada riwayat rekam medis</p>
            </div>
        <?php else: ?>
            <?php foreach ($medicalRecords as $record): ?>
                <div class="bg-white rounded-lg shadow mb-4">
                    <div class="p-6">
                        <!-- Header with date and doctor info -->
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <h2 class="text-xl font-semibold">
                                    Kunjungan: <?php echo date('d/m/Y', strtotime($record['Tanggal'])); ?>
                                </h2>
                                <?php if ($record['Waktu_Periksa']): ?>
                                    <p class="text-sm text-gray-600">
                                        Waktu Periksa: <?php echo date('H:i', strtotime($record['Waktu_Periksa'])); ?>
                                    </p>
                                <?php endif; ?>
                            </div>
                            <?php if ($record['nama_dokter']): ?>
                                <div class="text-right">
                                    <p class="font-semibold"><?php echo htmlspecialchars($record['nama_dokter']); ?></p>
                                    <p class="text-sm text-gray-600"><?php echo htmlspecialchars($record['Spesialis']); ?></p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Vital Signs -->
                        <div class="grid grid-cols-1 md:grid-cols-4 gap-4 mb-4">
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Tekanan Darah</p>
                                <p class="font-semibold"><?php echo htmlspecialchars($record['Tekanan_Darah'] ?? '-'); ?></p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Tinggi Badan</p>
                                <p class="font-semibold"><?php echo $record['Tinggi_Badan'] ? htmlspecialchars($record['Tinggi_Badan']) . ' cm' : '-'; ?></p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Berat Badan</p>
                                <p class="font-semibold"><?php echo $record['Berat_Badan'] ? htmlspecialchars($record['Berat_Badan']) . ' kg' : '-'; ?></p>
                            </div>
                            <div class="bg-blue-50 p-3 rounded">
                                <p class="text-sm text-gray-600">Suhu</p>
                                <p class="font-semibold"><?php echo $record['Suhu'] ? htmlspecialchars($record['Suhu']) . ' °C' : '-'; ?></p>
                            </div>
                        </div>

                        <!-- Diagnosis and Prescription -->
                        <?php if ($record['Diagnosa'] || $record['Resep_Obat']): ?>
                            <div class="border-t pt-4">
                                <?php if ($record['Diagnosa']): ?>
                                    <div class="mb-4">
                                        <h3 class="font-semibold mb-2">Diagnosa:</h3>
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($record['Diagnosa'])); ?></p>
                                    </div>
                                <?php endif; ?>

                                <?php if ($record['Resep_Obat']): ?>
                                    <div>
                                        <h3 class="font-semibold mb-2">Resep Obat:</h3>
                                        <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($record['Resep_Obat'])); ?></p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Medical History -->
                        <?php if ($record['Riwayat_Penyakit']): ?>
                            <div class="border-t pt-4 mt-4">
                                <h3 class="font-semibold mb-2">Riwayat Penyakit:</h3>
                                <p class="text-gray-700"><?php echo nl2br(htmlspecialchars($record['Riwayat_Penyakit'])); ?></p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>

            <!-- Pagination -->
            <?php if ($totalPages > 1): ?>
                <div class="flex justify-center mt-6">
                    <nav class="inline-flex rounded-md shadow">
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="px-3 py-2 <?php echo $page === $i ? 'bg-blue-600 text-white' : 'bg-white text-gray-700 hover:bg-gray-50'; ?> text-sm font-medium border <?php echo $i === 1 ? 'rounded-l-md' : ($i === $totalPages ? 'rounded-r-md' : ''); ?>">
                                <?php echo $i; ?>
                            </a>
                        <?php endfor; ?>
                    </nav>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</body>
</html>