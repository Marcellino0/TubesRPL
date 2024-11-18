<?php
session_start();
require_once('../config/db_connection.php');

// Get all specialists
$query_specialists = "SELECT DISTINCT Spesialis FROM Dokter ORDER BY Spesialis";
$specialists = $conn->query($query_specialists)->fetch_all(MYSQLI_ASSOC);

// Get search parameters
$search_doctor = isset($_GET['search_doctor']) ? $_GET['search_doctor'] : '';
$selected_specialist = isset($_GET['specialist']) ? $_GET['specialist'] : '';

// Build the query
$query = "SELECT 
    d.Nama as nama_dokter,
    d.Spesialis,
    GROUP_CONCAT(
        CONCAT(
            jd.Hari, 
            ' (', 
            TIME_FORMAT(jd.Jam_Mulai, '%H:%i'),
            ' - ',
            TIME_FORMAT(jd.Jam_Selesai, '%H:%i'),
            ') - ',
            CASE 
                WHEN (SELECT COUNT(*) FROM Pendaftaran p 
                      WHERE p.ID_Jadwal = jd.ID_Jadwal 
                      AND DATE(p.Waktu_Daftar) = CURDATE()) >= jd.Kuota 
                THEN 'Kuota Penuh'
                ELSE CONCAT('Tersedia ', 
                          jd.Kuota - (SELECT COUNT(*) FROM Pendaftaran p 
                                     WHERE p.ID_Jadwal = jd.ID_Jadwal 
                                     AND DATE(p.Waktu_Daftar) = CURDATE()),
                          ' slot')
            END
        ) SEPARATOR '\n'
    ) as jadwal_detail
FROM Dokter d
LEFT JOIN Jadwal_Dokter jd ON d.ID_Dokter = jd.ID_Dokter
WHERE jd.Status = 'Aktif'";

if (!empty($search_doctor)) {
    $query .= " AND d.Nama LIKE ?";
}
if (!empty($selected_specialist)) {
    $query .= " AND d.Spesialis = ?";
}

$query .= " GROUP BY d.ID_Dokter, d.Nama, d.Spesialis ORDER BY d.Nama";

$stmt = $conn->prepare($query);

// Bind parameters if they exist
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
$schedules = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
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
                                        <?php 
                                        $jadwal_array = explode("\n", $schedule['jadwal_detail']);
                                        foreach ($jadwal_array as $jadwal): 
                                        ?>
                                            <p class="text-sm text-gray-600">
                                                <?php echo htmlspecialchars($jadwal); ?>
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