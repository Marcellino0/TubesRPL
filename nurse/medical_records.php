<?php
session_start();
require_once('../config/db_connection.php');

// Memastikan bahwa hanya perawat yang dapat mengakses halaman ini
if (!isset($_SESSION['user_id']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: ../index.php"); // Redirect ke halaman login jika bukan perawat
    exit();
}

// Mengambil seluruh data rekam medis dari database, termasuk informasi pasien
$sql = "SELECT rm.ID_Rekam, rm.ID_Pasien, rm.Tekanan_Darah, rm.Tinggi_Badan, rm.Berat_Badan, rm.Suhu, 
        rm.Riwayat_Penyakit, rm.Tanggal, p.Nama AS Nama_Pasien
        FROM rekam_medis rm
        JOIN pasien p ON rm.ID_Pasien = p.ID_Pasien
        ORDER BY rm.Tanggal DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis - Poliklinik X</title>
    <!-- Menggunakan Tailwind CSS dan Font Awesome untuk desain -->
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="flex min-h-screen">
        <!-- Sidebar untuk navigasi menu perawat -->
        <aside class="w-64 bg-blue-800 text-white fixed h-full">
            <div class="p-4">
                <h1 class="text-xl font-bold mb-8">Poliklinik X</h1>
                <nav class="space-y-2">
                    <a href="nurse_dashboard.php"
                        class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700 text-white hover:text-white">
                        <i class="fas fa-home text-white"></i>
                        <span>Dashboard</span>
                    </a>
                    <a href="medical_records.php"
                        class="flex items-center space-x-3 p-3 rounded bg-blue-900 text-white">
                        <i class="fas fa-file-medical text-white"></i>
                        <span>Rekam Medis</span>
                    </a>
                </nav>
            </div>
            <!-- Bagian bawah sidebar menampilkan nama perawat dan opsi logout -->
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

        <!-- Konten utama -->
        <main class="flex-1 ml-64 p-8">
            <!-- Bagian daftar rekam medis -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Daftar Rekam Medis</h2>
                        <!-- Input pencarian pasien -->
                        <div class="flex space-x-2">
                            <input type="text" id="searchInput" placeholder="Cari pasien..." 
                                class="px-3 py-2 border rounded-md text-sm focus:outline-none focus:ring-2 focus:ring-blue-500">
                        </div>
                    </div>
                    <div class="overflow-x-auto">
                        <!-- Tabel daftar rekam medis -->
                        <table class="min-w-full divide-y divide-gray-200" id="recordTable">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tekanan Darah</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tinggi Badan (cm)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Berat Badan (kg)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Suhu (°C)</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Riwayat Penyakit</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Dokumen</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if ($result->num_rows > 0): ?>
                                    <?php while ($row = $result->fetch_assoc()): ?>
                                        <tr>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Nama_Pasien']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Tekanan_Darah']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Tinggi_Badan']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Berat_Badan']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Suhu']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900"><?php echo htmlspecialchars($row['Riwayat_Penyakit']); ?></td>
                                            <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900"><?php echo htmlspecialchars($row['Tanggal']); ?></td>
                                            <td class="px-6 py-4 text-sm text-gray-900">
                                                <?php
                                                // Query dokumen medis terkait pasien
                                                $id_pasien = $row['ID_Pasien'];
                                                $sql_docs = "SELECT Nama_File, Jenis_Dokumen, Path_File 
                                                             FROM dokumen_medis 
                                                             WHERE ID_Pasien = ?";
                                                $stmt_docs = $conn->prepare($sql_docs);
                                                $stmt_docs->bind_param("i", $id_pasien);
                                                $stmt_docs->execute();
                                                $result_docs = $stmt_docs->get_result();

                                                if ($result_docs->num_rows > 0):
                                                ?>
                                                    <div class="space-y-1">
                                                        <?php while ($doc = $result_docs->fetch_assoc()): ?>
                                                            <a href="<?php echo htmlspecialchars($doc['Path_File']); ?>" 
                                                               target="_blank" 
                                                               class="text-blue-600 hover:text-blue-800 hover:underline block">
                                                                <?php echo htmlspecialchars($doc['Jenis_Dokumen'] . ': ' . $doc['Nama_File']); ?>
                                                            </a>
                                                        <?php endwhile; ?>
                                                    </div>
                                                <?php else: ?>
                                                    <span class="text-gray-500">Tidak ada dokumen</span>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="px-6 py-4 text-center text-gray-500">Tidak ada data rekam medis.</td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        // Fungsi pencarian data pasien di tabel
        document.getElementById('searchInput').addEventListener('keyup', function() {
            const searchValue = this.value.toLowerCase();
            const table = document.getElementById('recordTable');
            const rows = table.getElementsByTagName('tbody')[0].getElementsByTagName('tr');

            for (let row of rows) {
                const patientName = row.getElementsByTagName('td')[0].textContent.toLowerCase();
                row.style.display = patientName.includes(searchValue) ? '' : 'none';
            }
        });
    </script>
</body>
</html>
