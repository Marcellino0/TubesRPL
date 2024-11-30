<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as doctor
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: ../login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];

// Initialize search variables
$search = isset($_GET['search']) ? $_GET['search'] : '';
$date_filter = isset($_GET['date_filter']) ? $_GET['date_filter'] : '';
$search_param = "%".$search."%";

$sql = "SELECT rm.*, pas.Nama, pas.Nomor_Rekam_Medis, 
        pm.Waktu_Periksa, pm.Diagnosa, r.Resep_Obat
        FROM Rekam_Medis rm
        JOIN Pasien pas ON rm.ID_Pasien = pas.ID_Pasien
        LEFT JOIN Pendaftaran p ON pas.ID_Pasien = p.ID_Pasien
        LEFT JOIN Pemeriksaan pm ON p.ID_Pendaftaran = pm.ID_Pendaftaran
        LEFT JOIN Resep r ON pm.ID_Pemeriksaan = r.ID_Pemeriksaan
        WHERE rm.Tanggal = CURDATE()";

if($search) {
    $sql .= " AND (pas.Nama LIKE ? OR pas.Nomor_Rekam_Medis LIKE ?)";
}

if($date_filter) {
    $sql .= " AND rm.Tanggal = ?";
}

$sql .= " ORDER BY rm.Tanggal DESC, rm.ID_Rekam DESC";

$stmt = $conn->prepare($sql);

if($search && $date_filter) {
    $stmt->bind_param("sss", $search_param, $search_param, $date_filter);
} elseif($search) {
    $stmt->bind_param("ss", $search_param, $search_param);
} elseif($date_filter) {
    $stmt->bind_param("s", $date_filter);
} else {
    $stmt->execute();
}

$result = $stmt->get_result();
if($result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Display the medical record data
        echo "ID_Rekam: " . $row['ID_Rekam'] . "<br>";
        echo "Nama Pasien: " . $row['Nama'] . "<br>";
        echo "Nomor Rekam Medis: " . $row['Nomor_Rekam_Medis'] . "<br>";
        echo "Waktu Pemeriksaan: " . $row['Waktu_Periksa'] . "<br>";
        echo "Diagnosa: " . $row['Diagnosa'] . "<br>";
        echo "Resep Obat: " . $row['Resep_Obat'] . "<br>";
        echo "Tekanan Darah: " . $row['Tekanan_Darah'] . "<br>";
        echo "Tinggi Badan: " . $row['Tinggi_Badan'] . " cm<br>";
        echo "Berat Badan: " . $row['Berat_Badan'] . " kg<br>";
        echo "Suhu: " . $row['Suhu'] . "°C<br>";
        echo "Riwayat Penyakit: " . $row['Riwayat_Penyakit'] . "<br>";
        echo "Tanggal: " . $row['Tanggal'] . "<br><br>";
    }
} 

// Modified query to correctly show all medical records
$sql = "SELECT rm.*, pas.Nama, pas.Nomor_Rekam_Medis,
        pm.Waktu_Periksa, pm.Diagnosa, r.Resep_Obat
        FROM Rekam_Medis rm
        JOIN Pasien pas ON rm.ID_Pasien = pas.ID_Pasien
        LEFT JOIN Pendaftaran p ON pas.ID_Pasien = p.ID_Pasien
        LEFT JOIN Pemeriksaan pm ON p.ID_Pendaftaran = pm.ID_Pendaftaran
        LEFT JOIN Resep r ON pm.ID_Pemeriksaan = r.ID_Pemeriksaan
        WHERE rm.Tanggal = CURDATE()";

if($search) {
    $sql .= " AND (pas.Nama LIKE ? OR pas.Nomor_Rekam_Medis LIKE ?)";
}

if($date_filter) {
    $sql .= " AND rm.Tanggal = ?";
}

$sql .= " ORDER BY rm.Tanggal DESC, rm.ID_Rekam DESC";

$stmt = $conn->prepare($sql);

// Bind parameters based on filters
if($search && $date_filter) {
    $stmt->bind_param("sss", $search_param, $search_param, $date_filter);
} elseif($search) {
    $stmt->bind_param("ss", $search_param, $search_param);
} elseif($date_filter) {
    $stmt->bind_param("s", $date_filter);
} else {
    $stmt->execute();
}


?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis - Poliklinik X</title>
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
                    <a href="medical_records.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
            <div class="mb-6">
                <form action="" method="GET" class="flex gap-4">
                    <input type="text" 
                           name="search" 
                           placeholder="Cari nama pasien atau nomor rekam medis..." 
                           value="<?php echo htmlspecialchars($search); ?>"
                           class="flex-1 p-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    <input type="date" 
                           name="date_filter" 
                           value="<?php echo htmlspecialchars($date_filter); ?>"
                           class="p-3 rounded-lg border border-gray-300 focus:border-blue-500 focus:ring focus:ring-blue-200 transition">
                    <button type="submit" class="px-6 py-3 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition">
                        <i class="fas fa-search mr-2"></i>Filter
                    </button>
                </form>
            </div>

            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-xl font-bold">Rekam Medis Pasien</h2>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama Pasien</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Rekam Medis</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Diagnosa</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Resep Obat</th>
                                    <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Vital Signs</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php if($result->num_rows > 0) {
                                    while($row = $result->fetch_assoc()) { ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo date('d/m/Y', strtotime($row['Tanggal'])); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Nama']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?php echo htmlspecialchars($row['Nomor_Rekam_Medis']); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php echo nl2br(htmlspecialchars($row['Diagnosa'] ?? 'Belum ada diagnosa')); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <?php echo nl2br(htmlspecialchars($row['Resep_Obat'] ?? 'Belum ada resep')); ?>
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-900">
                                            <div class="space-y-1">
                                                <p><span class="font-medium">Tekanan Darah:</span> <?php echo htmlspecialchars($row['Tekanan_Darah']); ?></p>
                                                <p><span class="font-medium">Tinggi:</span> <?php echo htmlspecialchars($row['Tinggi_Badan']); ?> cm</p>
                                                <p><span class="font-medium">Berat:</span> <?php echo htmlspecialchars($row['Berat_Badan']); ?> kg</p>
                                                <p><span class="font-medium">Suhu:</span> <?php echo htmlspecialchars($row['Suhu']); ?>°C</p>
                                                <p><span class="font-medium">Riwayat:</span> <?php echo htmlspecialchars($row['Riwayat_Penyakit']); ?></p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php }
                                } else { ?>
                                    <tr>
                                        <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">
                                            Tidak ada data rekam medis yang ditemukan
                                        </td>
                                    </tr>
                                <?php } ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>
</html>