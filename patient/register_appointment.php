<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: login.php");
    exit();
}
// Fetch the logged-in patient's name
$patientName = "Pasien";
if (isset($_SESSION['user_id']) && $_SESSION['user_type'] === 'pasien') {
    $stmt = $conn->prepare("SELECT Nama FROM Pasien WHERE ID_Pasien = ?");
    $stmt->bind_param("i", $_SESSION['user_id']);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    $patientName = $result['Nama'] ?? "Pasien";
}

$patientId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Fetch specializations dynamically
$specQuery = "SELECT DISTINCT Spesialis FROM dokter";
$specResult = $conn->query($specQuery);
$specializations = [];
while ($row = $specResult->fetch_assoc()) {
    $specializations[] = $row;
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $doctorId = $_POST['doctor'] ?? '';
    $scheduleId = $_POST['schedule'] ?? '';
    $registrationDate = $_POST['registration_date'];


    
    // Validate inputs
    if (empty($doctorId) || empty($scheduleId)) {
        $message = "Semua field harus diisi!";
        $messageType = "error";
    } else {
        // Start transaction for data integrity
        $conn->begin_transaction();
        
        try {
            // Get schedule details
            $scheduleQuery = $conn->prepare("
                SELECT Hari, ID_Dokter 
                FROM Jadwal_Dokter 
                WHERE ID_Jadwal = ?
            ");
            $scheduleQuery->bind_param("i", $scheduleId);
            $scheduleQuery->execute();
            $scheduleDetails = $scheduleQuery->get_result()->fetch_assoc();

            // Check for existing registration on the same day with the same doctor
            $existingRegQuery = $conn->prepare("
                SELECT p.ID_Pendaftaran, d.Nama AS Nama_Dokter
                FROM Pendaftaran p
                JOIN Jadwal_Dokter jd ON p.ID_Jadwal = jd.ID_Jadwal
                JOIN dokter d ON jd.ID_Dokter = d.ID_Dokter
                WHERE p.ID_Pasien = ? 
                AND jd.ID_Dokter = ?
                AND jd.Hari = ?
                AND DATE(p.Waktu_Daftar) = ?
            ");
            $existingRegQuery->bind_param("iiss", 
                $patientId, 
                $scheduleDetails['ID_Dokter'], 
                $scheduleDetails['Hari'], 
                $registrationDate
            );
            $existingRegQuery->execute();
            $existingReg = $existingRegQuery->get_result();
            
            // If patient already has a registration for this doctor on this day, prevent new registration
            if ($existingReg->num_rows > 0) {
                $existingRegDetails = $existingReg->fetch_assoc();
                throw new Exception("Anda sudah terdaftar pada hari {$scheduleDetails['Hari']} dengan dokter {$existingRegDetails['Nama_Dokter']}.");
            }

            // Check schedule availability and quota
            $scheduleCheck = $conn->prepare("
                SELECT 
                    jd.Kuota_Online, 
                    jd.Max_Pasien,
                    jd.Hari,
                    COALESCE((
                        SELECT COUNT(*) 
                        FROM Pendaftaran 
                        WHERE ID_Jadwal = ? 
                        AND DATE(Waktu_Daftar) = ?
                    ), 0) as used_quota
                FROM Jadwal_Dokter jd
                WHERE jd.ID_Jadwal = ?
                FOR UPDATE
            ");
            
            $scheduleCheck->bind_param("sss", $scheduleId, $registrationDate, $scheduleId);
            $scheduleCheck->execute();
            $scheduleInfo = $scheduleCheck->get_result()->fetch_assoc();
            
            // Check quota availability
            if ($scheduleInfo['used_quota'] >= $scheduleInfo['Kuota_Online']) {
                throw new Exception("Kuota pendaftaran untuk jadwal ini sudah penuh!");
            }
            
            // Get next queue number
            $queueQuery = $conn->prepare("
                SELECT COALESCE(MAX(No_Antrian), 0) + 1 as next_queue 
                FROM Pendaftaran 
                WHERE ID_Jadwal = ? 
                AND DATE(Waktu_Daftar) = ?
            ");
            $queueQuery->bind_param("ss", $scheduleId, $registrationDate);
            $queueQuery->execute();
            $nextQueue = $queueQuery->get_result()->fetch_assoc()['next_queue'];
            
            // Insert registration
            $registerQuery = $conn->prepare("
                INSERT INTO Pendaftaran 
                (ID_Pasien, ID_Jadwal, Waktu_Daftar, No_Antrian, Status) 
                VALUES (?, ?, ?, ?, 'Menunggu')
            ");
            $registerQuery->bind_param("iisi", 
                $patientId, 
                $scheduleId, 
                $registrationDate, 
                $nextQueue
            );
            
            if (!$registerQuery->execute()) {
                throw new Exception("Gagal melakukan pendaftaran: " . $conn->error);
            }

            // Update the quota in Jadwal_Dokter (if needed)
            $updateQuotaQuery = $conn->prepare("
                UPDATE Jadwal_Dokter 
                SET Kuota_Online = Kuota_Online - 1 
                WHERE ID_Jadwal = ? 
                AND Kuota_Online > 0
            ");
            $updateQuotaQuery->bind_param("i", $scheduleId);
            
            if (!$updateQuotaQuery->execute()) {
                throw new Exception("Gagal mengupdate kuota: " . $conn->error);
            }
            
            $conn->commit();
            $message = "Pendaftaran berhasil! Nomor antrian Anda: " . $nextQueue;
            $messageType = "success";
            
        } catch (Exception $e) {
            $conn->rollback();
            $message = "Error: " . $e->getMessage();
            $messageType = "error";
        }
    }
}

?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Dokter - Poliklinik X</title>
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
                    <a href="register_appointment.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
        <main class="ml-64 flex-1 p-6">
            <div class="max-w-3xl mx-auto">
                <div class="bg-white rounded-lg shadow-lg p-6">
                    <h1 class="text-2xl font-bold mb-6">Pendaftaran Dokter</h1>
                    
                    <!-- Display messages -->
                    <?php if ($message): ?>
                        <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                            <?php echo $message; ?>
                        </div>
                    <?php endif; ?>

                    <!-- Registration Form -->
                    <form method="POST" class="space-y-6" id="registrationForm">
                        <!-- Specialization Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pilih Spesialis</label>
                            <select id="specialization" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500">
                                <option value="">Pilih Spesialis</option>
                                <?php foreach ($specializations as $spec): ?>
                                    <option value="<?php echo htmlspecialchars($spec['Spesialis']); ?>">
                                        <?php echo htmlspecialchars($spec['Spesialis']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- Doctor Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pilih Dokter</label>
                            <select name="doctor" id="doctor" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Pilih Dokter Terlebih Dahulu</option>
                            </select>
                        </div>

                        <!-- Date Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pilih Tanggal Pendaftaran</label>
                            <input 
                                type="date" 
                                name="registration_date" 
                                id="registration_date" 
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" 
                                required
                            >
                        </div>

                        <!-- Schedule Selection -->
                        <div>
                            <label class="block text-sm font-medium text-gray-700">Pilih Jadwal</label>
                            <select name="schedule" id="schedule" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-500 focus:ring-blue-500" required>
                                <option value="">Pilih Jadwal</option>
                            </select>
                        </div>

                        <div class="flex items-center justify-between space-x-4">
                            <a href="patient_dashboard.php" class="inline-flex justify-center py-2 px-4 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Kembali
                            </a>
                            <button type="submit" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-blue-600 hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500">
                                Daftar
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</body>
</html>
