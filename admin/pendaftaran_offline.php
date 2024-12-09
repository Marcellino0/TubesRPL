<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

// Inside the form submission handler
if (isset($_POST['register_offline'])) {
    try {
        // Existing input validation...
        if (empty($_POST['nomor_rm']) || empty($_POST['id_jadwal'])) {
            throw new Exception("Semua field harus diisi");
        }
        // Get patient registration type
        $stmt = $conn->prepare("SELECT ID_Pasien, Registration_Type FROM Pasien WHERE Nomor_Rekam_Medis = ?");
        $stmt->bind_param("s", $_POST['nomor_rm']);
        $stmt->execute();
        $result = $stmt->get_result();
        $patient = $result->fetch_assoc();

        if (!$patient) {
            throw new Exception("Nomor Rekam Medis tidak ditemukan");
        }

        // Get latest queue number for today and schedule
        $stmt = $conn->prepare("
            SELECT MAX(No_Antrian) as last_number 
            FROM Pendaftaran 
            WHERE ID_Jadwal = ? 
            AND DATE(Waktu_Daftar) = CURDATE()
            AND Verifikasi = 'Terverifikasi'");
        $stmt->bind_param("i", $_POST['id_jadwal']);
        $stmt->execute();
        $result = $stmt->get_result();
        $row = $result->fetch_assoc();
        $no_antrian = ($row['last_number'] ?? 0) + 1;

        // Update quota based on registration type
        if ($patient['Registration_Type'] === 'offline') {
            $quotaField = 'Kuota_Offline';
            $verificationStatus = 'Terverifikasi';

            $stmt = $conn->prepare("
                UPDATE Jadwal_Dokter 
                SET Kuota_Offline = Kuota_Offline - 1
                WHERE ID_Jadwal = ?
            ");
        } else {
            $quotaField = 'Kuota_Online';
            $verificationStatus = 'Belum Diverifikasi';
            $no_antrian = 0; // Will be assigned after verification

            $stmt = $conn->prepare("
                UPDATE Jadwal_Dokter 
                SET Kuota_Online = Kuota_Online - 1
                WHERE ID_Jadwal = ?
            ");
        }
        $stmt->bind_param("i", $_POST['id_jadwal']);
        $stmt->execute();

        // Generate registration proof
        $bukti_reservasi = 'REG' . date('Ymd') . sprintf('%03d', $no_antrian);

        // Insert registration with appropriate verification status
        $stmt = $conn->prepare("
                INSERT INTO Pendaftaran 
                (ID_Pasien, ID_Jadwal, Waktu_Daftar, No_Antrian, Status, Bukti_Reservasi, Verifikasi, Tipe_Pendaftaran) 
                VALUES (?, ?, NOW(), ?, 'Menunggu', ?, 'Terverifikasi', 'offline')");

        $stmt->bind_param(
            "iiis",
            $patient['ID_Pasien'],
            $_POST['id_jadwal'],
            $no_antrian,
            $bukti_reservasi
        );

        if (!$stmt->execute()) {
            throw new Exception("Error executing query: " . $stmt->error);
        }

        $successMsg = $patient['Registration_Type'] === 'offline'
            ? "Pendaftaran berhasil dengan nomor antrian: " . $no_antrian . " dan bukti reservasi: " . $bukti_reservasi
            : "Pendaftaran berhasil. Mohon tunggu verifikasi admin untuk mendapatkan nomor antrian.";

        $_SESSION['success_message'] = $successMsg;
        header("Location: pendaftaran_offline.php");
        exit();

    } catch (Exception $e) {
        $_SESSION['error_message'] = "Gagal melakukan pendaftaran: " . $e->getMessage();
        header("Location: pendaftaran_offline.php");
        exit();
    }
}

?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pendaftaran Offline - Poliklinik X</title>
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
                    <a href="pendaftaran_offline.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-notes-medical"></i>
                        <span>Pendaftaran Pemeriksaan</span>
                    </a>
                    <a href="pendaftaran_ulang.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
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
        <main class="flex-1 ml-64 p-8">
            <div class="max-w-4xl mx-auto">
                <h1 class="text-2xl font-bold mb-6">Pendaftaran Pemeriksaan Offline</h1>

                <?php if (isset($_SESSION['error_message'])): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $_SESSION['error_message'];
                        unset($_SESSION['error_message']); ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($_SESSION['success_message'])): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                        <?php echo $_SESSION['success_message'];
                        unset($_SESSION['success_message']); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" class="bg-white shadow-md rounded px-8 pt-6 pb-8 mb-4">
                    <input type="hidden" name="register_offline" value="1">

                    <div class="mb-4 relative">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Nomor Rekam Medis</label>
                        <input type="text" name="nomor_rm"
                            class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 leading-tight focus:outline-none focus:shadow-outline"
                            required>
                    </div>

                    <div class="mb-4 relative">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Spesialisasi</label>
                        <div class="relative">
                            <select id="spesialisasi"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline pr-8"
                                required>
                                <option value="">Pilih Spesialisasi</option>
                                <?php
                                $query = "SELECT DISTINCT Spesialis FROM Dokter ORDER BY Spesialis";
                                $result = $conn->query($query);
                                while ($row = $result->fetch_assoc()) {
                                    echo "<option value='" . htmlspecialchars($row['Spesialis']) . "'>" . htmlspecialchars($row['Spesialis']) . "</option>";
                                }
                                ?>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 relative">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Dokter</label>
                        <div class="relative">
                            <select id="dokter"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline pr-8"
                                required>
                                <option value="">Pilih Dokter</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="mb-4 relative">
                        <label class="block text-gray-700 text-sm font-bold mb-2">Jadwal Tersedia</label>
                        <div class="relative">
                            <select name="id_jadwal" id="jadwal"
                                class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 mb-3 leading-tight focus:outline-none focus:shadow-outline pr-8"
                                required>
                                <option value="">Pilih Jadwal</option>
                            </select>
                            <div
                                class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-2 text-gray-700">
                                <i class="fas fa-chevron-down"></i>
                            </div>
                        </div>
                    </div>

                    <div class="flex items-center justify-center">
                        <button type="submit"
                            class="bg-blue-600 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                            Daftar
                        </button>
                    </div>
                </form>
            </div>
        </main>
    </div>

    <script>
        document.getElementById('spesialisasi').addEventListener('change', function () {
            const spesialis = this.value;
            const dokterSelect = document.getElementById('dokter');
            const jadwalSelect = document.getElementById('jadwal');

            // Reset dokter dan jadwal
            dokterSelect.innerHTML = '<option value="">Pilih Dokter</option>';
            jadwalSelect.innerHTML = '<option value="">Pilih Jadwal</option>';

            if (!spesialis) return;

            // Fetch doctors
            fetch(`get_doctors.php?spesialis=${encodeURIComponent(spesialis)}`)
                .then(response => response.json())
                .then(doctors => {
                    doctors.forEach(doctor => {
                        const option = document.createElement('option');
                        option.value = doctor.ID_Dokter;
                        option.textContent = doctor.Nama;
                        dokterSelect.appendChild(option);
                    });
                })
                .catch(error => console.error('Error:', error));
        });

        document.getElementById('dokter').addEventListener('change', function () {
            const doctorId = this.value;
            const jadwalSelect = document.getElementById('jadwal');

            // Reset jadwal
            jadwalSelect.innerHTML = '<option value="">Pilih Jadwal</option>';

            if (!doctorId) return;

            // Fetch schedules
            fetch(`get_schedule.php?doctor_id=${doctorId}&registration_day=today`)
                .then(response => response.json())
                .then(schedules => {
                    schedules.forEach(schedule => {
                        // Hitung sisa kuota offline
                        const availableQuota = schedule.Kuota_Offline - schedule.used_quota_offline_today;

                        if (availableQuota > 0 && schedule.Hari === getCurrentDay()) {
                            const option = document.createElement('option');
                            option.value = schedule.ID_Jadwal;
                            option.textContent = `${schedule.Jam_Mulai}-${schedule.Jam_Selesai}`;
                            jadwalSelect.appendChild(option);
                        }
                    });
                })
                .catch(error => console.error('Error:', error));
        });
        function getCurrentDay() {
            const days = ['Minggu', 'Senin', 'Selasa', 'Rabu', 'Kamis', 'Jumat', 'Sabtu'];
            const date = new Date();
            return days[date.getDay()];
        }
    </script>
</body>

</html>