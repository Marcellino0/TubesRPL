<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in and is a patient
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: login.php");
    exit();
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
    
    // Validate inputs
    if (empty($doctorId) || empty($scheduleId)) {
        $message = "Semua field harus diisi!";
        $messageType = "error";
    } else {
        // Start transaction for data integrity
        $conn->begin_transaction();
        
        try {
            // Use today's date for registration
            $registrationDate = date('Y-m-d');
            
            // Check schedule availability and quota
            $scheduleCheck = $conn->prepare("
                SELECT 
                    jd.Kuota, 
                    jd.Max_Pasien,
                    jd.Hari,
                    (SELECT COUNT(*) 
                     FROM Pendaftaran 
                     WHERE ID_Jadwal = ? 
                     AND DATE(Waktu_Daftar) = DATE(?)) as used_quota
                FROM Jadwal_Dokter jd
                WHERE jd.ID_Jadwal = ?
            ");
            
            $scheduleCheck->bind_param("sss", $scheduleId, $registrationDate, $scheduleId);
            $scheduleCheck->execute();
            $scheduleInfo = $scheduleCheck->get_result()->fetch_assoc();
            
            // Check quota availability
            if ($scheduleInfo['used_quota'] >= $scheduleInfo['Kuota']) {
                throw new Exception("Kuota pendaftaran untuk jadwal ini sudah penuh!");
            }
            
            // Get next queue number
            $queueQuery = $conn->prepare("
                SELECT COALESCE(MAX(No_Antrian), 0) + 1 as next_queue 
                FROM Pendaftaran 
                WHERE ID_Jadwal = ? 
                AND DATE(Waktu_Daftar) = DATE(?)
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
</head>
<body class="bg-gray-100">
    <div class="min-h-screen p-6">
        <div class="max-w-3xl mx-auto">
            <div class="bg-white rounded-lg shadow-lg p-6">
                <h1 class="text-2xl font-bold mb-6">Pendaftaran Dokter</h1>
                
                <?php if ($message): ?>
                    <div class="mb-4 p-4 rounded <?php echo $messageType === 'success' ? 'bg-green-100 text-green-700' : 'bg-red-100 text-red-700'; ?>">
                        <?php echo $message; ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="space-y-6">
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
    </div>

    <script>
    document.getElementById('specialization').addEventListener('change', async function() {
        const spesialis = this.value;
        const doctorSelect = document.getElementById('doctor');
        const scheduleSelect = document.getElementById('schedule');
        
        // Reset selections
        doctorSelect.innerHTML = '<option value="">Pilih Dokter</option>';
        scheduleSelect.innerHTML = '<option value="">Pilih Jadwal</option>';
        
        if (spesialis) {
            try {
                const response = await fetch(`get_doctors.php?spesialis=${encodeURIComponent(spesialis)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const doctors = await response.json();
                doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.ID_Dokter;
                    option.textContent = doctor.Nama;
                    doctorSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error fetching doctors:', error);
                alert('Terjadi kesalahan saat mengambil data dokter');
            }
        }
    });

    document.getElementById('doctor').addEventListener('change', async function() {
        loadSchedules();
    });

    async function loadSchedules() {
        const doctorId = document.getElementById('doctor').value;
        const scheduleSelect = document.getElementById('schedule');
        
        // Reset schedule selection
        scheduleSelect.innerHTML = '<option value="">Pilih Jadwal</option>';
        
        if (doctorId) {
            try {
                const response = await fetch(`get_schedule.php?doctor_id=${encodeURIComponent(doctorId)}`);
                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }
                const schedules = await response.json();
                schedules.forEach(schedule => {
                    const option = document.createElement('option');
                    option.value = schedule.ID_Jadwal;
                    const quotaInfo = schedule.Kuota - schedule.used_quota_today;
                    option.textContent = `${schedule.Hari} - ${schedule.Jam_Mulai} - ${schedule.Jam_Selesai} (Sisa Kuota: ${quotaInfo})`;
                    option.disabled = quotaInfo <= 0;
                    scheduleSelect.appendChild(option);
                });
            } catch (error) {
                console.error('Error fetching schedules:', error);
                alert('Terjadi kesalahan saat mengambil jadwal dokter');
            }
        }
    }
    </script>
</body>
</html>