<?php
session_start();
require_once('../config/db_connection.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['action']) && isset($_POST['pendaftaran_id'])) {
        $pendaftaranId = $_POST['pendaftaran_id'];
        $action = $_POST['action'];
        $catatan = $_POST['catatan'] ?? '';

        try {
            $conn->begin_transaction();

            if ($action === 'verify') {
                $regQuery = $conn->prepare("
                    SELECT p.ID_Jadwal, p.Waktu_Daftar 
                    FROM Pendaftaran p 
                    JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
                    JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                    WHERE p.ID_Pendaftaran = ?
                ");
                $regQuery->bind_param("i", $pendaftaranId);
                $regQuery->execute();
                $regDetails = $regQuery->get_result()->fetch_assoc();

                if (!$regDetails) {
                    throw new Exception("Pendaftaran tidak ditemukan atau bukan pendaftaran online");
                }

               
                $queueQuery = $conn->prepare("
                    SELECT COALESCE(MAX(No_Antrian), 0) + 1 as next_queue 
                    FROM Pendaftaran 
                    WHERE ID_Jadwal = ? 
                    AND DATE(Waktu_Daftar) = DATE(?)
                    AND Verifikasi = 'Terverifikasi'
                ");
                $queueQuery->bind_param("is", $regDetails['ID_Jadwal'], $regDetails['Waktu_Daftar']);
                $queueQuery->execute();
                $nextQueue = $queueQuery->get_result()->fetch_assoc()['next_queue'];

             
                $updateStmt = $conn->prepare("
                    UPDATE Pendaftaran 
                    SET Verifikasi = 'Terverifikasi',
                        Waktu_Verifikasi = NOW(),
                        Catatan_Verifikasi = ?,
                        No_Antrian = ?
                    WHERE ID_Pendaftaran = ?
                ");
                $updateStmt->bind_param("sii", $catatan, $nextQueue, $pendaftaranId);

                if ($updateStmt->execute()) {
                    $conn->commit();
                    $message = "Pendaftaran berhasil diverifikasi";
                    $messageType = "success";
                } else {
                    throw new Exception("Gagal memperbarui status pendaftaran");
                }

            } else if ($action === 'reject') {
                
                $updateQuotaStmt = $conn->prepare("
                    UPDATE Jadwal_Dokter jd
                    JOIN Pendaftaran p ON jd.ID_Jadwal = p.ID_Jadwal
                    SET jd.Kuota_Online = jd.Kuota_Online + 1
                    WHERE p.ID_Pendaftaran = ?
                ");
                $updateQuotaStmt->bind_param("i", $pendaftaranId);

                if (!$updateQuotaStmt->execute()) {
                    throw new Exception("Gagal memperbarui kuota jadwal");
                }

              
                $updateStmt = $conn->prepare("
                    UPDATE Pendaftaran 
                    SET Verifikasi = 'Ditolak',
                        Waktu_Verifikasi = NOW(),
                        Catatan_Verifikasi = ?,
                        Status = 'Batal'
                    WHERE ID_Pendaftaran = ?
                ");
                $updateStmt->bind_param("si", $catatan, $pendaftaranId);

                if ($updateStmt->execute()) {
                    $conn->commit();
                    $message = "Pendaftaran berhasil ditolak";
                    $messageType = "success";
                } else {
                    throw new Exception("Gagal memperbarui status pendaftaran");
                }
            }

        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}


$query = "
    SELECT 
        p.ID_Pendaftaran,
        p.Waktu_Daftar,
        p.Verifikasi,
        p.Catatan_Verifikasi,
        pas.Nama as nama_pasien,
        pas.Nomor_Rekam_Medis,
        d.Nama as nama_dokter,
        d.Spesialis,
        jd.Hari,
        jd.Jam_Mulai,
        jd.Jam_Selesai,
        jd.Kuota_Online
    FROM Pendaftaran p
    JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
    JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    WHERE p.Verifikasi = 'Belum Diverifikasi'
    AND p.Tipe_Pendaftaran = 'online'
    AND DATE(p.Waktu_Daftar) = CURDATE()
    ORDER BY p.Waktu_Daftar ASC";

$pendingRegistrations = $conn->query($query);
if (!$pendingRegistrations) {
    die("Error executing query: " . $conn->error);
}
$pendingRegistrations = $pendingRegistrations->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verifikasi Pendaftaran Online - Admin</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>

<body class="bg-gray-100">
    <!-- Sidebar -->
    <aside class="w-64 bg-blue-800 text-white fixed h-full">
        <div class="p-4">
            <h1 class="text-xl font-bold mb-8">Poliklinik X</h1>
            <nav class="space-y-2">
                <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-home"></i>
                    <span>Dashboard</span>
                </a>
                <a href="manage_doctors.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-user-md"></i>
                    <span>Kelola Dokter</span>
                </a>
                <a href="manage_schedules.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-calendar-alt"></i>
                    <span>Kelola Jadwal</span>
                </a>
                <a href="manage_patients.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-users"></i>
                    <span>Kelola Pasien</span>
                </a>
                <a href="pendaftaran_offline.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-notes-medical"></i>
                    <span>Pendaftaran Pemeriksaan</span>
                </a>
                <a href="pendaftaran_ulang.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                    <i class="fas fa-globe"></i>
                    <span>Pendaftaran Ulang</span>
                </a>
                <a href="manage_nurses.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-user-nurse"></i>
                    <span>Kelola Perawat</span>
                </a>
                <a href="manage_payments.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                    <i class="fas fa-money-bill-wave"></i>
                    <span>Kelola Pembayaran</span>
                </a>
            </nav>
        </div>
        <div class="absolute bottom-0 w-64 p-4 bg-blue-900">
            <div class="flex items-center space-x-3 mb-4">
                <i class="fas fa-user-shield text-2xl"></i>
                <div>
                    <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                    <p class="text-sm text-gray-300">Administrator</p>
                </div>
            </div>
            <a href="logout.php" class="flex items-center space-x-3 p-2 rounded hover:bg-blue-800 text-red-300">
                <i class="fas fa-sign-out-alt"></i>
                <span>Logout</span>
            </a>
        </div>
    </aside>

    <!-- Main Content -->
    <div class="ml-64 p-8">
        <div class="max-w-6xl mx-auto">
            <h1 class="text-2xl font-bold mb-6">Verifikasi Pendaftaran Online</h1>

            <?php if ($message): ?>
                <div
                    class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                    <?php echo $message; ?>
                </div>
            <?php endif; ?>

            <?php if (empty($pendingRegistrations)): ?>
                <div class="bg-white rounded-lg shadow p-6">
                    <p class="text-gray-500">Tidak ada pendaftaran online yang menunggu verifikasi</p>
                </div>
            <?php else: ?>
                <div class="grid gap-6">
                    <?php foreach ($pendingRegistrations as $reg): ?>
                        <div class="bg-white rounded-lg shadow p-6">
                            <div class="grid grid-cols-2 gap-4 mb-4">
                                <div>
                                    <h2 class="text-lg font-semibold"><?php echo htmlspecialchars($reg['nama_pasien']); ?></h2>
                                    <p class="text-sm text-gray-600">No. RM:
                                        <?php echo htmlspecialchars($reg['Nomor_Rekam_Medis']); ?></p>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-gray-600">
                                        Tanggal Daftar: <?php echo date('d/m/Y H:i', strtotime($reg['Waktu_Daftar'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div class="mb-4">
                                <p class="font-medium">Detail Pemeriksaan:</p>
                                <p class="text-sm text-gray-600">
                                    Dokter: <?php echo htmlspecialchars($reg['nama_dokter']); ?>
                                    (<?php echo htmlspecialchars($reg['Spesialis']); ?>)
                                </p>
                                <p class="text-sm text-gray-600">
                                    Jadwal: <?php echo htmlspecialchars($reg['Hari']); ?>,
                                    <?php echo htmlspecialchars($reg['Jam_Mulai']); ?> -
                                    <?php echo htmlspecialchars($reg['Jam_Selesai']); ?>
                                </p>
                            </div>

                            <?php if ($reg['Verifikasi'] !== 'Ditolak'): ?>
                                <form method="POST" class="space-y-4">
                                    <input type="hidden" name="pendaftaran_id" value="<?php echo $reg['ID_Pendaftaran']; ?>">

                                    <div>
                                        <label class="block text-sm font-medium text-gray-700">Catatan Verifikasi</label>
                                        <textarea name="catatan" rows="2"
                                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"></textarea>
                                    </div>

                                    <div class="flex space-x-4">
                                        <button type="submit" name="action" value="verify"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500">
                                            Verifikasi
                                        </button>
                                        <button type="submit" name="action" value="reject"
                                            class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500">
                                            Tolak
                                        </button>
                                    </div>
                                </form>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</body>

</html>