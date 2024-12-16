<?php
session_start(); // Memulai sesi untuk menyimpan data pengguna

require_once('../config/db_connection.php'); // Mengimpor file koneksi ke database

// Mengambil semua spesialisasi dokter yang tersedia untuk dropdown atau filter
$query_specialists = "SELECT DISTINCT Spesialis FROM Dokter ORDER BY Spesialis"; 
$specialists = $conn->query($query_specialists)->fetch_all(MYSQLI_ASSOC); // Menyimpan hasil query dalam array asosiasi

// Mendapatkan parameter pencarian dari URL (jika ada)
$search_doctor = isset($_GET['search_doctor']) ? $_GET['search_doctor'] : ''; // Nama dokter untuk pencarian
$selected_specialist = isset($_GET['specialist']) ? $_GET['specialist'] : ''; // Spesialis yang dipilih untuk filter

// Membuat query dasar untuk mengambil data dokter dan jadwal dokter
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
WHERE jd.Status = 'Aktif'"; // Query untuk mengambil data dokter yang memiliki jadwal aktif

// Menambahkan filter pencarian nama dokter jika ada
if (!empty($search_doctor)) {
    $query .= " AND d.Nama LIKE ?"; // Mencocokkan nama dokter dengan string pencarian
}

// Menambahkan filter berdasarkan spesialisasi jika ada
if (!empty($selected_specialist)) {
    $query .= " AND d.Spesialis = ?"; // Memfilter berdasarkan spesialisasi yang dipilih
}

// Menyusun urutan berdasarkan nama dokter dan hari dalam minggu
$query .= " ORDER BY d.Nama, FIELD(jd.Hari, 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu', 'Minggu')";

$stmt = $conn->prepare($query); // Menyiapkan query untuk dieksekusi

// Mengikat parameter pencarian jika ada
if (!empty($search_doctor) && !empty($selected_specialist)) {
    $search_param = "%$search_doctor%"; // Menambahkan wildcard untuk pencarian nama dokter
    $stmt->bind_param("ss", $search_param, $selected_specialist); // Mengikat dua parameter (nama dokter dan spesialis)
} elseif (!empty($search_doctor)) {
    $search_param = "%$search_doctor%"; // Menambahkan wildcard untuk pencarian nama dokter
    $stmt->bind_param("s", $search_param); // Mengikat parameter hanya untuk nama dokter
} elseif (!empty($selected_specialist)) {
    $stmt->bind_param("s", $selected_specialist); // Mengikat parameter hanya untuk spesialisasi
}

$stmt->execute(); // Menjalankan query
$result = $stmt->get_result(); // Mendapatkan hasil dari eksekusi query

// Memproses hasil untuk mengelompokkan jadwal berdasarkan dokter
$schedules = []; // Menyimpan hasil jadwal dalam array
while ($row = $result->fetch_assoc()) { // Iterasi melalui setiap baris hasil
    $doctorId = $row['ID_Dokter']; // Mendapatkan ID dokter
    if (!isset($schedules[$doctorId])) { // Jika dokter belum ada dalam array, tambahkan
        $schedules[$doctorId] = [
            'nama_dokter' => $row['nama_dokter'],
            'Spesialis' => $row['Spesialis'],
            'jadwal' => [] // Membuat array kosong untuk jadwal dokter
        ];
    }
    if ($row['Hari']) {  // Menambahkan jadwal jika ada
        $schedules[$doctorId]['jadwal'][] = [
            'hari' => $row['Hari'], // Hari jadwal
            'jam_mulai' => $row['Jam_Mulai'], // Jam mulai
            'jam_selesai' => $row['Jam_Selesai'], // Jam selesai
            'kuota_online' => $row['Kuota_Online'] // Kuota online
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
</head>
<body class="bg-gray-100">
    <div class="max-w-7xl mx-auto px-4 py-8">
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

        <!-- Schedule List -->
        <div class="bg-white rounded-lg shadow-md">
            <div class="px-6 py-4 border-b border-gray-200">
                <h3 class="text-lg font-semibold">Hasil Pencarian</h3>
            </div>
            <?php if (empty($schedules)): ?>
                <div class="p-6 text-center text-gray-500">
                    Tidak ada jadwal dokter yang ditemukan
                </div>
            <?php else: ?>
                <div class="divide-y divide-gray-200">
                    <?php foreach ($schedules as $schedule): ?>
                        <div class="p-6">
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
</body>
</html>