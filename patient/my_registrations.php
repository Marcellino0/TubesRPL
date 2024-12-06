<?php
session_start();
require_once('../config/db_connection.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: index.php");
    exit();
}

$patientId = $_SESSION['user_id'];

// Fetch patient data for sidebar
$stmt = $conn->prepare("SELECT Nama FROM Pasien WHERE ID_Pasien = ?");
$stmt->bind_param("i", $patientId);
$stmt->execute();
$patientData = $stmt->get_result()->fetch_assoc();

// Rest of the database queries remain the same...
$stmt = $conn->prepare("
    SELECT 
        p.ID_Pendaftaran,
        p.No_Antrian,
        p.Waktu_Daftar,
        p.Status,
        p.Bukti_Reservasi,
        d.Nama as nama_dokter,
        d.Spesialis,
        jd.Hari,
        jd.Jam_Mulai,
        jd.Jam_Selesai,
        pem.ID_Pemeriksaan,
        pem.Waktu_Periksa,
        pem.Diagnosa,
        pb.Status as status_pembayaran,
        pb.Jumlah as biaya
    FROM Pendaftaran p
    JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    LEFT JOIN Pemeriksaan pem ON p.ID_Pendaftaran = pem.ID_Pendaftaran
    LEFT JOIN Pembayaran pb ON p.ID_Pendaftaran = pb.ID_Pendaftaran
    WHERE p.ID_Pasien = ?
    ORDER BY p.Waktu_Daftar DESC
");

$stmt->bind_param("i", $patientId);
$stmt->execute();
$registrations = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Riwayat Pendaftaran - Poliklinik X</title>
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
                    <a href="patient_dashboard.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="jadwal_dokter.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal Dokter</span>
                    </a>
                    <a href="register_appointment.php"
                        class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Janji</span>
                    </a>
                    <a href="medical_history.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-file-medical"></i>
                        <span>Hasil Pemeriksaan</span>
                    </a>
                    <a href="payment_history.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-receipt"></i>
                        <span>Pembayaran</span>
                    </a>
                </nav>
            </div>
            <!-- User info at bottom of sidebar -->
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
            <div class="mb-6">
                <h1 class="text-2xl font-bold mb-2">Riwayat Pendaftaran</h1>
                <p class="text-gray-600">Daftar semua pendaftaran pemeriksaan Anda</p>
            </div>

            <?php if (empty($registrations)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-500">Belum ada riwayat pendaftaran</p>
                </div>
            <?php else: ?>
                <div class="space-y-6">
                    <?php foreach ($registrations as $reg): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="flex justify-between items-start mb-4">
                                <div>
                                    <h2 class="text-xl font-semibold">
                                        <?php echo htmlspecialchars($reg['nama_dokter']); ?>
                                        <span class="text-sm text-gray-600">
                                            (<?php echo htmlspecialchars($reg['Spesialis']); ?>)
                                        </span>
                                    </h2>
                                    <p class="text-gray-600">
                                        <?php echo htmlspecialchars($reg['Hari']); ?>,
                                        <?php echo htmlspecialchars($reg['Jam_Mulai']); ?> -
                                        <?php echo htmlspecialchars($reg['Jam_Selesai']); ?>
                                    </p>
                                </div>
                                <div class="text-right">
                                    <span class="px-3 py-1 rounded text-sm font-medium
                                        <?php
                                        switch ($reg['Status']) {
                                            case 'Menunggu':
                                                echo 'bg-yellow-100 text-yellow-800';
                                                break;
                                            case 'Selesai':
                                                echo 'bg-green-100 text-green-800';
                                                break;
                                            case 'Batal':
                                                echo 'bg-red-100 text-red-800';
                                                break;
                                            default:
                                                echo 'bg-gray-100 text-gray-800';
                                        }
                                        ?>">
                                        <?php echo htmlspecialchars($reg['Status']); ?>
                                    </span>
                                </div>
                            </div>

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                                <div>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">No. Antrian:</span>
                                        <?php echo htmlspecialchars($reg['No_Antrian']); ?>
                                    </p>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Waktu Pendaftaran:</span>
                                        <?php echo date('d/m/Y H:i', strtotime($reg['Waktu_Daftar'])); ?>
                                    </p>
                                </div>

                                <?php if ($reg['biaya']): ?>
                                    <div>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Biaya:</span>
                                            Rp <?php echo number_format($reg['biaya'], 0, ',', '.'); ?>
                                        </p>
                                        <p class="text-sm text-gray-600">
                                            <span class="font-medium">Status Pembayaran:</span>
                                            <span
                                                class="<?php echo $reg['status_pembayaran'] === 'Lunas' ? 'text-green-600' : 'text-red-600'; ?>">
                                                <?php echo htmlspecialchars($reg['status_pembayaran']); ?>
                                            </span>
                                        </p>
                                    </div>
                                <?php endif; ?>
                            </div>

                            

                            <?php if ($reg['ID_Pemeriksaan']): ?>
                                <div class="mt-4 pt-4 border-t">
                                    <h3 class="font-medium mb-2">Hasil Pemeriksaan:</h3>
                                    <p class="text-sm text-gray-600">
                                        <span class="font-medium">Waktu Periksa:</span>
                                        <?php echo date('d/m/Y H:i', strtotime($reg['Waktu_Periksa'])); ?>
                                    </p>
                                    <p class="text-sm text-gray-600 mt-2">
                                        <span class="font-medium">Diagnosa:</span><br>
                                        <?php echo nl2br(htmlspecialchars($reg['Diagnosa'])); ?>
                                    </p>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </main>
    </div>
</body>

</html>