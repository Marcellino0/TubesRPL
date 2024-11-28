<?php
session_start();
require_once('../config/db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: ../login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];
$today = date('Y-m-d');
$day = date('l');

// Get doctor stats
$stmt = $conn->prepare("SELECT COUNT(*) as total FROM Pendaftaran p 
                       JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal 
                       WHERE j.ID_Dokter = ? AND DATE(p.Waktu_Daftar) = ?");
$stmt->bind_param("is", $dokter_id, $today);
$stmt->execute();
$total_result = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT COUNT(*) as waiting FROM Pendaftaran p 
                       JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal 
                       WHERE j.ID_Dokter = ? AND DATE(p.Waktu_Daftar) = ? 
                       AND p.Status = 'Menunggu'");
$stmt->bind_param("is", $dokter_id, $today);
$stmt->execute();
$waiting_result = $stmt->get_result()->fetch_assoc();

$stmt = $conn->prepare("SELECT Kuota, 
                       (SELECT COUNT(*) FROM Pendaftaran p 
                        WHERE p.ID_Jadwal = j.ID_Jadwal 
                        AND DATE(p.Waktu_Daftar) = ?) as used_quota 
                       FROM Jadwal_Dokter j 
                       WHERE ID_Dokter = ? AND Hari = ?");
$stmt->bind_param("sis", $today, $dokter_id, $day);
$stmt->execute();
$quota_result = $stmt->get_result()->fetch_assoc();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Poliklinik X</title>
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
                    <a href="doctor_dashboard.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="medical_records.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-file-medical"></i>
                        <span>Rekam Medis</span>
                    </a>
                    <a href="diagnosa.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-users"></i>
                        <span>Mencatat Diagnosa</span>
                    </a>
                   
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 bg-blue-900">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-user-md text-2xl"></i>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($_SESSION['nama']); ?></p>
                        <p class="text-sm text-gray-300">Dokter</p>
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
            <!-- Quick Stats -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8">
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-blue-100 p-3 rounded-full">
                            <i class="fas fa-clipboard-list text-blue-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Total Antrian Hari Ini</p>
                            <p class="text-2xl font-bold"><?php echo $total_result['total']; ?></p>
                        </div>
                    </div>
                </div>
                
                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-yellow-100 p-3 rounded-full">
                            <i class="fas fa-user-clock text-yellow-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Pasien Menunggu</p>
                            <p class="text-2xl font-bold"><?php echo $waiting_result['waiting']; ?></p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow p-6">
                    <div class="flex items-center space-x-4">
                        <div class="bg-green-100 p-3 rounded-full">
                            <i class="fas fa-ticket-alt text-green-600"></i>
                        </div>
                        <div>
                            <p class="text-gray-500 text-sm">Sisa Kuota</p>
                            <p class="text-2xl font-bold">
                                <?php 
                                if ($quota_result) {
                                    echo ($quota_result['Kuota'] - $quota_result['used_quota']);
                                } else {
                                    echo "0";
                                }
                                ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Patient Queue -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Antrian Pasien Hari Ini</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Antrian
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Pasien
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Rekam Medis
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status
                                    </th>
                                    
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $sql = "SELECT p.No_Antrian, pas.Nama, pas.Nomor_Rekam_Medis, p.Status, p.ID_Pendaftaran
                                       FROM Pendaftaran p
                                       JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                                       JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                                       WHERE j.ID_Dokter = ? AND DATE(p.Waktu_Daftar) = ?
                                       ORDER BY p.No_Antrian";
                                
                                $stmt = $conn->prepare($sql);
                                $stmt->bind_param("is", $dokter_id, $today);
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                while($row = $result->fetch_assoc()): 
                                    $status_class = match($row['Status']) {
                                        'Menunggu' => 'bg-yellow-100 text-yellow-800',
                                        'Diperiksa' => 'bg-blue-100 text-blue-800',
                                        'Selesai' => 'bg-green-100 text-green-800',
                                        default => ''
                                    };
                                ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            <?php echo htmlspecialchars($row['No_Antrian']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Nama']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Nomor_Rekam_Medis']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full <?php echo $status_class; ?>">
                                                <?php echo htmlspecialchars($row['Status']); ?>
                                            </span>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <?php if($row['Status'] == 'Menunggu'): ?>
                                                <a href="examine_patient.php?id=<?php echo $row['ID_Pendaftaran']; ?>" 
                                                   class="text-white bg-blue-600 hover:bg-blue-700 px-4 py-2 rounded-md text-sm">
                                                    Periksa
                                                </a>
                                            <?php elseif($row['Status'] == 'Diperiksa'): ?>
                                                <a href="continue_examination.php?id=<?php echo $row['ID_Pendaftaran']; ?>" 
                                                   class="text-gray-700 bg-gray-200 hover:bg-gray-300 px-4 py-2 rounded-md text-sm">
                                                    Lanjutkan
                                                </a>
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

    <script>
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>