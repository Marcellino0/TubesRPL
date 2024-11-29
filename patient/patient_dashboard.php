<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: index.php");
    exit();
}

// Get patient data
$patientId = $_SESSION['user_id'];
$stmt = $conn->prepare("
    SELECT p.*, 
           COUNT(DISTINCT pend.ID_Pendaftaran) as total_kunjungan,
           COUNT(DISTINCT CASE WHEN pend.Status = 'Menunggu' THEN pend.ID_Pendaftaran END) as pending_appointments
    FROM Pasien p
    LEFT JOIN Pendaftaran pend ON p.ID_Pasien = pend.ID_Pasien
    WHERE p.ID_Pasien = ?
    GROUP BY p.ID_Pasien
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$patientData = $stmt->get_result()->fetch_assoc();

// Modify exam results query to include more information
$stmt = $conn->prepare("
    SELECT 
        p.ID_Pemeriksaan,
        p.Waktu_Periksa,
        p.Diagnosa,
        d.Nama as nama_dokter,
        d.Spesialis,
        r.Resep_Obat,
        rm.Tekanan_Darah,
        rm.Tinggi_Badan,
        rm.Berat_Badan,
        rm.Suhu,
        pend.ID_Pendaftaran,
        pend.No_Antrian
    FROM Pemeriksaan p
    JOIN Dokter d ON p.ID_Dokter = d.ID_Dokter
    JOIN Pendaftaran pend ON p.ID_Pendaftaran = pend.ID_Pendaftaran
    LEFT JOIN Resep r ON p.ID_Pemeriksaan = r.ID_Pemeriksaan
    LEFT JOIN Rekam_Medis rm ON pend.ID_Pasien = rm.ID_Pasien 
        AND DATE(p.Waktu_Periksa) = rm.Tanggal
    WHERE pend.ID_Pasien = ?
    ORDER BY p.Waktu_Periksa DESC
    LIMIT 5
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$examResults = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Add a new query to fetch registration details with reservation proof
$stmt = $conn->prepare("
    SELECT 
        p.ID_Pendaftaran,
        d.Nama as nama_dokter,
        d.Spesialis,
        jd.Hari,
        jd.Jam_Mulai,
        jd.Jam_Selesai,
        p.No_Antrian,
        p.Waktu_Daftar,
        p.Bukti_Reservasi,
        p.Status
    FROM Pendaftaran p
    JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    WHERE p.ID_Pasien = ? 
      AND p.Status = 'Menunggu'
    ORDER BY p.Waktu_Daftar DESC
    LIMIT 3
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$activeRegistrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);


// Get payment history
$stmt = $conn->prepare("
    SELECT 
        pb.*,
        d.Nama as nama_dokter,
        d.Spesialis
    FROM Pembayaran pb
    JOIN Pendaftaran pend ON pb.ID_Pendaftaran = pend.ID_Pendaftaran
    JOIN Jadwal_Dokter jd ON pend.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    WHERE pend.ID_Pasien = ?
    ORDER BY pb.Tanggal DESC
    LIMIT 5
");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$paymentHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get available doctors count and specialties count for quick stats
$query_stats = "
    SELECT 
        COUNT(DISTINCT d.ID_Dokter) as total_doctors,
        COUNT(DISTINCT d.Spesialis) as total_specialties
    FROM Dokter d
    JOIN Jadwal_Dokter jd ON d.ID_Dokter = jd.ID_Dokter
    WHERE jd.Status = 'Aktif'";
$stats = $conn->query($query_stats)->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Pasien - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar -->
        <aside class="w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">Poliklinik X</h1>
                <nav class="space-y-2">
                    <a href="#dashboard" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="jadwal_dokter.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal Dokter</span>
                    </a>
                    <a href="register_appointment.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Janji</span>
                    </a>
                    <a href="medical_history.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-file-medical"></i>
                        <span>Riwayat Medis</span>
                    </a>
                    <a href="payment_history.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-receipt"></i>
                        <span>Pembayaran</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 bg-blue-900">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-user-circle text-2xl"></i>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($patientData['Nama']); ?></p>
                        <p class="text-sm text-gray-300">Pasien</p>
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
            <!-- Recent Medical Examination Results -->
            <div class="bg-white rounded-lg shadow mb-8">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Hasil Pemeriksaan Terakhir</h2>
                        <a href="medical_history.php" class="text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                    </div>
                    <?php if (empty($examResults)): ?>
                        <p class="text-gray-500">Belum ada riwayat pemeriksaan</p>
                    <?php else: ?>
                        <div class="space-y-6">
                            <?php foreach ($examResults as $exam): ?>
                                <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                                    <div class="flex justify-between items-start mb-2">
                                        <div>
                                            <h3 class="font-semibold text-lg">
                                                <?php echo htmlspecialchars($exam['nama_dokter']); ?>
                                                <span class="text-sm text-gray-600">
                                                    (<?php echo htmlspecialchars($exam['Spesialis']); ?>)
                                                </span>
                                            </h3>
                                            <p class="text-sm text-gray-600">
                                                <?php echo date('d F Y, H:i', strtotime($exam['Waktu_Periksa'])); ?>
                                            </p>
                                        </div>
                                        <!-- <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                            Pemeriksaan #<?php echo $exam['ID_Pemeriksaan']; ?>
                                        </span> -->
                                    </div>

                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2">Data Vital:</h4>
                                            <div class="grid grid-cols-2 gap-2 text-sm">
                                                <?php if ($exam['Tekanan_Darah']): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-heartbeat text-red-500 mr-2"></i>
                                                        <span>TD: <?php echo htmlspecialchars($exam['Tekanan_Darah']); ?></span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($exam['Suhu']): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-thermometer-half text-orange-500 mr-2"></i>
                                                        <span>Suhu: <?php echo htmlspecialchars($exam['Suhu']); ?>°C</span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($exam['Tinggi_Badan']): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-ruler-vertical text-blue-500 mr-2"></i>
                                                        <span>TB: <?php echo htmlspecialchars($exam['Tinggi_Badan']); ?> cm</span>
                                                    </div>
                                                <?php endif; ?>
                                                <?php if ($exam['Berat_Badan']): ?>
                                                    <div class="flex items-center">
                                                        <i class="fas fa-weight text-green-500 mr-2"></i>
                                                        <span>BB: <?php echo htmlspecialchars($exam['Berat_Badan']); ?> kg</span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                        
                                        <div>
                                            <h4 class="font-medium text-gray-700 mb-2">Diagnosa:</h4>
                                            <p class="text-sm text-gray-600">
                                                <?php echo nl2br(htmlspecialchars($exam['Diagnosa'])); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <?php if ($exam['Resep_Obat']): ?>
                                    <div class="mt-4 border-t pt-4">
                                        <h4 class="font-medium text-gray-700 mb-2">Resep Obat:</h4>
                                        <p class="text-sm text-gray-600">
                                            <?php echo nl2br(htmlspecialchars($exam['Resep_Obat'])); ?>
                                        </p>
                                    </div>
                                    <?php endif; ?>

                                    
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>

            </div>

            <!-- Payment History -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Riwayat Pembayaran</h2>
                        <a href="payment_history.php" class="text-blue-600 hover:text-blue-800">Lihat Semua →</a>
                    </div>
                    <?php if (empty($paymentHistory)): ?>
                        <p class="text-gray-500">Belum ada riwayat pembayaran</p>
                    <?php else: ?>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Tanggal
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Dokter
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jumlah
                                        </th>
                                        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Status
                                        </th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php foreach ($paymentHistory as $payment): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('d/m/Y', strtotime($payment['Tanggal'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                              <?php echo htmlspecialchars($payment['nama_dokter']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                Rp <?php echo number_format($payment['Jumlah'], 0, ',', '.'); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full 
                                                    <?php echo $payment['Status'] === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?>">
                                                    <?php echo htmlspecialchars($payment['Status']); ?>
                                                </span>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="bg-white rounded-lg shadow mt-8">
    <div class="p-6">
        <div class="flex justify-between items-center mb-4">
            <h2 class="text-xl font-bold">Pendaftaran Aktif</h2>
            <a href="my_registrations.php" class="text-blue-600 hover:text-blue-800">Lihat Semua →</a>
        </div>
        <?php if (empty($activeRegistrations)): ?>
            <p class="text-gray-500">Tidak ada pendaftaran aktif</p>
        <?php else: ?>
            <div class="space-y-4">
                <?php foreach ($activeRegistrations as $registration): ?>
                    <div class="bg-gray-50 rounded-lg p-4 border border-gray-200">
                        <div class="flex justify-between items-start mb-2">
                            <div>
                                <h3 class="font-semibold text-lg">
                                    Dr. <?php echo htmlspecialchars($registration['nama_dokter']); ?>
                                    <span class="text-sm text-gray-600">
                                        (<?php echo htmlspecialchars($registration['Spesialis']); ?>)
                                    </span>
                                </h3>
                                <p class="text-sm text-gray-600">
                                    <?php echo htmlspecialchars($registration['Hari']); ?>, 
                                    <?php echo htmlspecialchars($registration['Jam_Mulai']); ?> - 
                                    <?php echo htmlspecialchars($registration['Jam_Selesai']); ?>
                                </p>
                            </div>
                            <span class="bg-blue-100 text-blue-800 text-xs font-medium px-2.5 py-0.5 rounded">
                                No. Antrian: <?php echo htmlspecialchars($registration['No_Antrian']); ?>
                            </span>
                        </div>

                        <?php if ($registration['Bukti_Reservasi']): ?>
                            <div class="mt-4 border-t pt-4">
                                <h4 class="font-medium text-gray-700 mb-2">Bukti Reservasi:</h4>
                                <div class="flex items-center space-x-4">
                                    <?php 
                                    $filePath = "../uploads/bukti_reservasi/" . htmlspecialchars($registration['Bukti_Reservasi']);
                                    $fileExtension = strtolower(pathinfo($filePath, PATHINFO_EXTENSION));
                                    ?>
                                    <?php if (in_array($fileExtension, ['jpg', 'jpeg', 'png'])): ?>
                                        <img src="<?php echo $filePath; ?>" alt="Bukti Reservasi" class="h-24 w-auto rounded-lg object-cover">
                                    <?php elseif ($fileExtension === 'pdf'): ?>
                                        <i class="fas fa-file-pdf text-4xl text-red-500"></i>
                                    <?php endif; ?>
                                    <a href="<?php echo $filePath; ?>" target="_blank" class="text-blue-600 hover:text-blue-800 underline">
                                        Lihat Bukti Reservasi
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>
    </div>
</div>
        </main>
    </div>
</body>
</html>