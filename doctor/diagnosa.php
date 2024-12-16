<?php
session_start();
require_once('../config/db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: ../login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];
$today = date('Y-m-d');


$search = isset($_GET['search']) ? $_GET['search'] : '';


$where_clause = '';
if($search) {
    $where_clause = "AND (pas.Nama LIKE ? OR pas.Nomor_Rekam_Medis LIKE ?)";
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Pasien - Poliklinik X</title>
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
                    <a href="doctor_dashboard.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-home"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="diagnosa.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
            <div class="mb-6">
                <form action="" method="GET" class="w-full">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari nama pasien atau nomor rekam medis..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="w-full p-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                </form>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Riwayat Pemeriksaan</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Pasien
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Rekam Medis
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Diagnosa
                                    </th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Resep
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                $sql = "SELECT pm.Waktu_Periksa, pas.Nama, pas.Nomor_Rekam_Medis, 
                                        pm.Diagnosa, r.Resep_Obat
                                       FROM Pemeriksaan pm
                                       JOIN Pendaftaran p ON pm.ID_Pendaftaran = p.ID_Pendaftaran
                                       JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                                       LEFT JOIN Resep r ON pm.ID_Pemeriksaan = r.ID_Pemeriksaan
                                       WHERE pm.ID_Dokter = ? $where_clause
                                       ORDER BY pm.Waktu_Periksa DESC";
                                
                                $stmt = $conn->prepare($sql);
                                
                                if($search) {
                                    $search_param = "%$search%";
                                    $stmt->bind_param("iss", $dokter_id, $search_param, $search_param);
                                } else {
                                    $stmt->bind_param("i", $dokter_id);
                                }
                                
                                $stmt->execute();
                                $result = $stmt->get_result();
                                
                                if($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) {
                                        ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo date('d/m/Y H:i', strtotime($row['Waktu_Periksa'])); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['Nama']); ?>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                <?php echo htmlspecialchars($row['Nomor_Rekam_Medis']); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php echo nl2br(htmlspecialchars($row['Diagnosa'])); ?>
                                            </td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php echo nl2br(htmlspecialchars($row['Resep_Obat'] ?? '-')); ?>
                                            </td>
                                        </tr>
                                        <?php
                                    }
                                } else {
                                    ?>
                                    <tr>
                                        <td colspan="5" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data pemeriksaan
                                        </td>
                                    </tr>
                                    <?php
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
    
        <?php if(!$search) { ?>
        setTimeout(function() {
            location.reload();
        }, 30000);
        <?php } ?>
    </script>
</body>
</html>