<?php
session_start();
require_once('../config/db_connection.php');

// Initialize response message variables
$successMsg = '';
$errorMsg = '';

// Helper function to get all doctors
function getDoctors($conn) {
    $sql = "SELECT ID_Dokter, Nama, Spesialis FROM dokter ORDER BY Nama";
    $result = $conn->query($sql);
    return $result->fetch_all(MYSQLI_ASSOC);
}

// Helper function to get schedule by ID
function getScheduleById($conn, $id) {
    $stmt = $conn->prepare("SELECT * FROM jadwal_dokter WHERE ID_Jadwal = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    return $result->fetch_assoc();
}

// Handle Add/Edit Schedule
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['action'])) {
        if ($_POST['action'] == 'add') {
            $stmt = $conn->prepare("INSERT INTO jadwal_dokter (ID_Dokter, Jam_Mulai, Jam_Selesai, Kuota, Max_Pasien, Hari, Status, Keterangan) VALUES (?, ?, ?, ?, ?, ?, ?, ?)");
            $stmt->bind_param("issiisss", $_POST['dokter'], $_POST['jam_mulai'], $_POST['jam_selesai'], $_POST['kuota'], $_POST['max_pasien'], $_POST['hari'], $_POST['status'], $_POST['keterangan']);

            if ($stmt->execute()) {
                $successMsg = "Jadwal berhasil ditambahkan";
            } else {
                $errorMsg = "Error: " . $stmt->error;
            }
        } else if ($_POST['action'] == 'edit') {
            $stmt = $conn->prepare("UPDATE jadwal_dokter SET ID_Dokter=?, Jam_Mulai=?, Jam_Selesai=?, Kuota=?, Max_Pasien=?, Hari=?, Status=?, Keterangan=? WHERE ID_Jadwal=?");
            $stmt->bind_param("issiisssi", $_POST['dokter'], $_POST['jam_mulai'], $_POST['jam_selesai'], $_POST['kuota'], $_POST['max_pasien'], $_POST['hari'], $_POST['status'], $_POST['keterangan'], $_POST['id_jadwal']);

            if ($stmt->execute()) {
                $successMsg = "Jadwal berhasil diperbarui";
            } else {
                $errorMsg = "Error: " . $stmt->error;
            }
        }
    }
}

// Handle Delete Schedule
if (isset($_GET['delete'])) {
    $stmt = $conn->prepare("DELETE FROM jadwal_dokter WHERE ID_Jadwal = ?");
    $stmt->bind_param("i", $_GET['delete']);

    if ($stmt->execute()) {
        $successMsg = "Jadwal berhasil dihapus";
    } else {
        $errorMsg = "Error: " . $stmt->error;
    }
}

// Get all schedules with doctor information
$schedules = $conn->query("
    SELECT jd.*, d.Nama as nama_dokter, d.Spesialis 
    FROM jadwal_dokter jd 
    JOIN dokter d ON jd.ID_Dokter = d.ID_Dokter 
    ORDER BY jd.Hari, jd.Jam_Mulai
");

$doctors = getDoctors($conn);
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Jadwal Dokter - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <style>
        /* Custom styles for the schedule modal */
        #scheduleModal .modal-dialog {
            max-width: 400px;
        }
        #scheduleModal .form-group {
            margin-bottom: 0.5rem;
        }
        #scheduleModal label {
            font-size: 0.875rem;
            margin-bottom: 0.25rem;
        }
        #scheduleModal input,
        #scheduleModal select,
        #scheduleModal textarea {
            font-size: 0.875rem;
            padding: 0.25rem 0.5rem;
        }
        #scheduleModal .btn {
            font-size: 0.875rem;
            padding: 0.25rem 0.75rem;
        }
    </style>
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
                    <a href="manage_schedules.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-calendar-alt"></i>
                        <span>Kelola Jadwal</span>
                    </a>
                    <a href="manage_patients.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-users"></i>
                        <span>Kelola Pasien</span>
                    </a>
                    <a href="manage_nurses.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-user-nurse"></i>
                        <span>Kelola Perawat</span>
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
            <div class="bg-white rounded-lg shadow p-6">
                <div class="flex justify-between items-center mb-6">
                    <h2 class="text-2xl font-bold text-gray-800">Kelola Jadwal Dokter</h2>
                    <button type="button" class="bg-blue-600 hover:bg-blue-700 text-white px-4 py-2 rounded"
                        onclick="toggleModal('scheduleModal')">
                        <i class="fas fa-plus mr-2"></i>Tambah Jadwal Baru
                    </button>
                </div>

                <?php if ($successMsg): ?>
                    <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php echo htmlspecialchars($successMsg); ?>
                    </div>
                <?php endif; ?>

                <?php if ($errorMsg): ?>
                    <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                        <?php echo htmlspecialchars($errorMsg); ?>
                    </div>
                <?php endif; ?>

                <div class="overflow-x-auto">
                    <table class="min-w-full bg-white">
                        <thead class="bg-gray-100 text-gray-600">
                            <tr>
                                <th class="px-6 py-3 text-left">Dokter</th>
                                <th class="px-6 py-3 text-left">Spesialis</th>
                                <th class="px-6 py-3 text-left">Hari</th>
                                <th class="px-6 py-3 text-left">Jam Mulai</th>
                                <th class="px-6 py-3 text-left">Jam Selesai</th>
                                <th class="px-6 py-3 text-left">Kuota</th>
                                <th class="px-6 py-3 text-left">Maks Pasien</th>
                                <th class="px-6 py-3 text-left">Status</th>
                                <th class="px-6 py-3 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-200">
                            <?php while ($row = $schedules->fetch_assoc()): ?>
                                <tr>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['nama_dokter']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Spesialis']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Hari']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Jam_Mulai']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Jam_Selesai']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Kuota']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Max_Pasien']); ?></td>
                                    <td class="px-6 py-4"><?php echo htmlspecialchars($row['Status']); ?></td>
                                    <td class="px-6 py-4 text-center">
                                        <button class="text-blue-600 hover:text-blue-900 mr-2 edit-btn"
                                            onclick="editSchedule(<?php echo htmlspecialchars(json_encode($row)); ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <a href="?delete=<?php echo $row['ID_Jadwal']; ?>"
                                            class="text-red-600 hover:text-red-900"
                                            onclick="return confirm('Apakah Anda yakin ingin menghapus jadwal ini?')">
                                            <i class="fas fa-trash"></i>
                                        </a>
                                    </td>
                                </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Schedule Modal -->
            <div class="fixed z-10 inset-0 overflow-y-auto hidden" id="scheduleModal" aria-labelledby="modal-title" role="dialog" aria-modal="true">
                <div class="flex items-end justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
                    <div class="fixed inset-0 bg-gray-500 bg-opacity-75 transition-opacity" aria-hidden="true"></div>
                    <span class="hidden sm:inline-block sm:align-middle sm:h-screen" aria-hidden="true">&#8203;</span>
                    <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                        <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                            <div class="sm:flex sm:items-start">
                                <div class="mt-3 text-center sm:mt-0 sm:ml-4 sm:text-left w-full">
                                    <h3 class="text-lg leading-6 font-medium text-gray-900" id="modal-title">
                                        Tambah/Edit Jadwal
                                    </h3>
                                    <div class="mt-2">
                                        <form id="scheduleForm" method="POST" class="space-y-3">
                                            <input type="hidden" name="action" value="add">
                                            <input type="hidden" name="id_jadwal" id="id_jadwal">

                                            <div>
                                                <label for="dokter" class="block text-sm font-medium text-gray-700">Dokter</label>
                                                <select name="dokter" id="dokter" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                    <option value="">Pilih Dokter</option>
                                                    <?php foreach ($doctors as $doctor): ?>
                                                        <option value="<?php echo $doctor['ID_Dokter']; ?>">
                                                            <?php echo htmlspecialchars($doctor['Nama'] . ' - ' . $doctor['Spesialis']); ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                            </div>

                                            <div>
                                                <label for="hari" class="block text-sm font-medium text-gray-700">Hari</label>
                                                <select name="hari" id="hari" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                    <option value="Senin">Senin</option>
                                                    <option value="Selasa">Selasa</option>
                                                    <option value="Rabu">Rabu</option>
                                                    <option value="Kamis">Kamis</option>
                                                    <option value="Jumat">Jumat</option>
                                                    <option value="Sabtu">Sabtu</option>
                                                </select>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="jam_mulai" class="block text-sm font-medium text-gray-700">Jam Mulai</label>
                                                    <input type="time" name="jam_mulai" id="jam_mulai" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="jam_selesai" class="block text-sm font-medium text-gray-700">Jam Selesai</label>
                                                    <input type="time" name="jam_selesai" id="jam_selesai" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <div class="grid grid-cols-2 gap-4">
                                                <div>
                                                    <label for="kuota" class="block text-sm font-medium text-gray-700">Kuota</label>
                                                    <input type="number" name="kuota" id="kuota" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>

                                                <div>
                                                    <label for="max_pasien" class="block text-sm font-medium text-gray-700">Maks Pasien</label>
                                                    <input type="number" name="max_pasien" id="max_pasien" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                </div>
                                            </div>

                                            <div>
                                                <label for="status" class="block text-sm font-medium text-gray-700">Status</label>
                                                <select name="status" id="status" required class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm">
                                                    <option value="Aktif">Aktif</option>
                                                    <option value="Tidak Aktif">Tidak Aktif</option>
                                                </select>
                                            </div>

                                            <div>
                                                <label for="keterangan" class="block text-sm font-medium text-gray-700">Catatan</label>
                                                <textarea name="keterangan" id="keterangan" rows="3" class="mt-1 block w-full py-2 px-3 border border-gray-300 bg-white rounded-md shadow-sm focus:outline-none focus:ring-blue-500 focus:border-blue-500 sm:text-sm"></textarea>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                            <button type="submit" form="scheduleForm" class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-blue-600 text-base font-medium text-white hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:ml-3 sm:w-auto sm:text-sm">
                                Simpan Jadwal
                            </button>
                            <button type="button" onclick="toggleModal('scheduleModal')" class="mt-3 w-full inline-flex justify-center rounded-md border border-gray-300 shadow-sm px-4 py-2 bg-white text-base font-medium text-gray-700 hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 sm:mt-0 sm:ml-3 sm:w-auto sm:text-sm">
                                Batal
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <script>
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle('hidden');
        }

        function editSchedule(scheduleData) {
            const form = document.getElementById('scheduleForm');
            form.querySelector('[name="action"]').value = 'edit';
            form.querySelector('#id_jadwal').value = scheduleData.ID_Jadwal;
            form.querySelector('#dokter').value = scheduleData.ID_Dokter;
            form.querySelector('#hari').value = scheduleData.Hari;
            form.querySelector('#jam_mulai').value = scheduleData.Jam_Mulai;
            form.querySelector('#jam_selesai').value = scheduleData.Jam_Selesai;
            form.querySelector('#kuota').value = scheduleData.Kuota;
            form.querySelector('#max_pasien').value = scheduleData.Max_Pasien;
            form.querySelector('#status').value = scheduleData.Status;
            form.querySelector('#keterangan').value = scheduleData.Keterangan;

            toggleModal('scheduleModal');
        }

        // Reset form when adding new schedule
        document.querySelector('[onclick="toggleModal(\'scheduleModal\')"]').addEventListener('click', function() {
            const form = document.getElementById('scheduleForm');
            form.reset();
            form.querySelector('[name="action"]').value = 'add';
            form.querySelector('#id_jadwal').value = '';
        });
    </script>
</body>
</html>