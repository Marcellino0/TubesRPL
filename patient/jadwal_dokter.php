<?php
session_start();
require_once('../config/db_connection.php');
// Fetch the logged-in patient's name
$patientName = "Pasien";
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'pasien') {
    $stmt = $conn->prepare("SELECT Nama FROM Pasien WHERE ID_Pasien = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $patientName = $result['Nama'] ?? "Pasien";
}
// Mengambil semua data spesialis dari database
$query_specialists = "SELECT DISTINCT Spesialis FROM Dokter ORDER BY Spesialis";
$specialists = $conn->query($query_specialists)->fetch_all(MYSQLI_ASSOC);


$search_doctor = isset($_GET['search_doctor']) ? $_GET['search_doctor'] : '';
$selected_specialist = isset($_GET['specialist']) ? $_GET['specialist'] : '';

// Membuat query untuk mengambil data dokter dan jadwalnya
$query = "SELECT 
    d.ID_Dokter,
    d.Nama as nama_dokter,
    d.Spesialis,
    jd.Hari,
    jd.Jam_Mulai,
    jd.Jam_Selesai,
    jd.Kuota_Online,
    jd.Status
FROM dokter d
LEFT JOIN jadwal_dokter jd ON d.ID_Dokter = jd.ID_Dokter
WHERE jd.Status = 'Aktif'";

if (!empty($search_doctor)) {
    $query .= " AND d.Nama LIKE ?";
}
if (!empty($selected_specialist)) {
    $query .= " AND d.Spesialis = ?";
}
// Mengurutkan hasil berdasarkan nama dan hari
$query .= " ORDER BY d.Nama, FIELD(jd.Hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";

$stmt = $conn->prepare($query);

if (!empty($search_doctor) && !empty($selected_specialist)) {
    $search_param = "%$search_doctor%";
    $stmt->bind_param("ss", $search_param, $selected_specialist);
} elseif (!empty($search_doctor)) {
    $search_param = "%$search_doctor%";
    $stmt->bind_param("s", $search_param);
} elseif (!empty($selected_specialist)) {
    $stmt->bind_param("s", $selected_specialist);
}

$stmt->execute();
$result = $stmt->get_result();

// Memproses hasil query untuk mengelompokkan jadwal berdasarkan dokter
$schedules = [];
while ($row = $result->fetch_assoc()) {
    $doctorId = $row['ID_Dokter'];
    if (!isset($schedules[$doctorId])) {
        $schedules[$doctorId] = [
            'nama_dokter' => $row['nama_dokter'],
            'Spesialis' => $row['Spesialis'],
            'jadwal' => []
        ];
    }
    if ($row['Hari']) {  // Only add if there's a schedule
        $schedules[$doctorId]['jadwal'][] = [
            'hari' => $row['Hari'],
            'jam_mulai' => $row['Jam_Mulai'],
            'jam_selesai' => $row['Jam_Selesai'],
            'kuota_online' => $row['Kuota_Online']
        ];
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jadwal Dokter - Poliklinik X</title>
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
                    <a href="jadwal_dokter.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Jadwal Dokter</span>
                    </a>
                    <a href="register_appointment.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-plus-circle"></i>
                        <span>Buat Janji</span>
                    </a>
                    <a href="medical_history.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-file-medical"></i>
                        <span>Hasil Pemeriksaan</span>
                    </a>
                    <a href="payment.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-receipt"></i>
                        <span>Pembayaran</span>
                    </a>
                </nav>
            </div>
            <div class="absolute bottom-0 w-64 p-4 bg-blue-900">
                <div class="flex items-center space-x-3 mb-4">
                    <i class="fas fa-user-circle text-2xl"></i>
                    <div>
                        <p class="font-medium"><?php echo htmlspecialchars($patientName); ?></p>
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
        <div class="flex-1 ml-64 bg-gray-100 py-8">
            <div class="max-w-7xl mx-auto px-4">

                <!-- Search and Filter Section -->
                <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                    <h2 class="text-2xl font-bold mb-4">Cari Jadwal Dokter</h2>
                    <form method="GET" class="space-y-4 md:space-y-0 md:flex md:space-x-4">
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Nama Dokter</label>
                            <input 
                                type="text" 
                                name="search_doctor" 
                                value="<?php echo htmlspecialchars($search_doctor); ?>"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500"
                                placeholder="Cari nama dokter...">
                        </div>
                        <div class="flex-1">
                            <label class="block text-sm font-medium text-gray-700">Spesialis</label>
                            <select 
                                name="specialist" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Semua Spesialis</option>
                                <?php foreach ($specialists as $specialist): ?>
                                    <option 
                                        value="<?php echo htmlspecialchars($specialist['Spesialis']); ?>"
                                        <?php echo $selected_specialist === $specialist['Spesialis'] ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($specialist['Spesialis']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="flex items-end">
                            <button 
                                type="submit"
                                class="w-full md:w-auto px-6 py-2 border border-transparent rounded-md shadow-sm text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Cari
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Search Results Section -->
                <h3 class="text-lg font-semibold mb-4">Hasil Pencarian</h3>

                <div class="bg-white shadow rounded-lg p-6">
                    <?php if (empty($schedules)): ?>
                        <div class="text-center text-gray-500">
                            Tidak ada jadwal dokter yang ditemukan
                        </div>
                    <?php else: ?>
                        <div class="divide-y divide-gray-200">
                            <?php foreach ($schedules as $schedule): ?>
                                <div class="py-4">
                                    <div class="flex justify-between items-start">
                                        <div>
                                            <h4 class="text-lg font-medium text-gray-900">
                                                <?php echo htmlspecialchars($schedule['nama_dokter']); ?>
                                            </h4>
                                            <p class="text-sm text-gray-600">
                                                Spesialis <?php echo htmlspecialchars($schedule['Spesialis']); ?>
                                            </p>
                                            <div class="mt-2 space-y-1">
                                                <?php foreach ($schedule['jadwal'] as $jadwal): ?>
                                                    <p class="text-sm text-gray-600">
                                                        <?php 
                                                        echo htmlspecialchars(sprintf(
                                                            "%s (%s - %s) - Kuota Online: %d pasien",
                                                            $jadwal['hari'],
                                                            date('H:i', strtotime($jadwal['jam_mulai'])),
                                                            date('H:i', strtotime($jadwal['jam_selesai'])),
                                                            $jadwal['kuota_online']
                                                        ));
                                                        ?>
                                                    </p>
                                                <?php endforeach; ?>
                                            </div>
                                        </div>
                                        <?php if (isset($_SESSION['user_type']) && $_SESSION['user_type'] === 'pasien'): ?>
                                            <a href="register_appointment.php?doctor=<?php echo urlencode($schedule['nama_dokter']); ?>" 
                                               class="inline-flex items-center px-4 py-2 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                                Buat Janji
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
