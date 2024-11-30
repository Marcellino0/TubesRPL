<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: index.php");
    exit();
}

$patientId = $_SESSION['user_id'];

// Get patient basic info
$stmt = $conn->prepare("SELECT Nama, Nomor_Rekam_Medis FROM Pasien WHERE ID_Pasien = ?");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$patientInfo = $stmt->get_result()->fetch_assoc();

// Pagination setup
$results_per_page = 10;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $results_per_page;

// Get total number of examinations
$stmt = $conn->prepare("
    SELECT COUNT(*) as total 
    FROM Pemeriksaan p
    JOIN Pendaftaran pend ON p.ID_Pendaftaran = pend.ID_Pendaftaran
    WHERE pend.ID_Pasien = ?
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$total_results = $stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_results / $results_per_page);

// Get examination history with pagination
$stmt = $conn->prepare("
    SELECT 
        p.ID_Pemeriksaan,
        p.Waktu_Periksa,
        p.Diagnosa,
        d.Nama as nama_dokter,
        d.Spesialis,
        r.Resep_Obat,
        pb.Status as status_pembayaran,
        pb.Jumlah as biaya_pemeriksaan
    FROM Pemeriksaan p
    JOIN Pendaftaran pend ON p.ID_Pendaftaran = pend.ID_Pendaftaran
    JOIN Dokter d ON p.ID_Dokter = d.ID_Dokter
    LEFT JOIN Resep r ON p.ID_Pemeriksaan = r.ID_Pemeriksaan
    LEFT JOIN Pembayaran pb ON pend.ID_Pendaftaran = pb.ID_Pendaftaran
    WHERE pend.ID_Pasien = ?
    ORDER BY p.Waktu_Periksa DESC
    LIMIT ? OFFSET ?
");
$stmt->bind_param("iii", $patientId, $results_per_page, $offset);
$stmt->execute();
$examinations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pemeriksaan - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <nav class="bg-blue-600 text-white shadow-lg">
        <div class="max-w-7xl mx-auto px-4">
            <div class="flex justify-between items-center py-4">
                <div class="flex items-center">
                    <h1 class="text-xl font-bold">Poliklinik X</h1>
                </div>
                <div class="flex items-center space-x-4">
                    <a href="patient_dashboard.php" class="hover:text-gray-200">
                        <i class="fas fa-home mr-2"></i>Dashboard
                    </a>
                    <span><?php echo htmlspecialchars($patientInfo['Nama']); ?></span>
                    <a href="logout.php" class="bg-red-500 hover:bg-red-600 px-4 py-2 rounded">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-7xl mx-auto px-4 py-8">
        <div class="mb-8">
            <h2 class="text-2xl font-bold text-gray-800">Riwayat Pemeriksaan</h2>
            <p class="text-gray-600">No. Rekam Medis: <?php echo htmlspecialchars($patientInfo['Nomor_Rekam_Medis']); ?></p>
        </div>

        <?php if (empty($examinations)): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500 text-center">Belum ada riwayat pemeriksaan</p>
            </div>
        <?php else: ?>
            <div class="space-y-6">
                <?php foreach ($examinations as $exam): ?>
                    <div class="bg-white rounded-lg shadow p-6">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <div class="flex items-center space-x-2">
                                    <h3 class="text-lg font-semibold">
                                        Pemeriksaan oleh <?php echo htmlspecialchars($exam['nama_dokter']); ?>
                                    </h3>
                                    <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                        <?php echo htmlspecialchars($exam['Spesialis']); ?>
                                    </span>
                                </div>
                                <p class="text-gray-600">
                                    <?php echo date('l, d F Y H:i', strtotime($exam['Waktu_Periksa'])); ?>
                                </p>
                            </div>
                            <div class="flex items-center space-x-2">
                                <span class="<?php echo $exam['status_pembayaran'] === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> text-xs font-medium px-2.5 py-0.5 rounded">
                                    <?php echo htmlspecialchars($exam['status_pembayaran']); ?>
                                </span>
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Diagnosa:</h4>
                                <p class="text-gray-600 whitespace-pre-line">
                                    <?php echo nl2br(htmlspecialchars($exam['Diagnosa'])); ?>
                                </p>
                            </div>

                            <?php if ($exam['Resep_Obat']): ?>
                            <div>
                                <h4 class="font-medium text-gray-700 mb-2">Resep Obat:</h4>
                                <p class="text-gray-600 whitespace-pre-line">
                                    <?php echo nl2br(htmlspecialchars($exam['Resep_Obat'])); ?>
                                </p>
                            </div>
                            <?php endif; ?>
                        </div>

                    </div>
                <?php endforeach; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
                <div class="mt-8 flex justify-center">
                    <nav class="relative z-0 inline-flex rounded-md shadow-sm -space-x-px" aria-label="Pagination">
                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                            <a href="?page=<?php echo $i; ?>" 
                               class="<?php echo $page === $i ? 'bg-blue-50 border-blue-500 text-blue-600' : 'bg-white border-gray-300 text-gray-500 hover:bg-gray-50'; ?> relative inline-flex items-center px-4 py-2 border text-sm font-medium">
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