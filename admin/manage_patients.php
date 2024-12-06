<?php
session_start();

// Database connection configuration
function connectDB()
{
    $host = 'localhost';
    $dbname = 'poliklinikx'; // sesuaikan dengan nama database Anda
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();

// Handle Delete Action
if (isset($_POST['delete_patient'])) {
    try {
        $nik = $_POST['nik'];
        $stmt = $conn->prepare("DELETE FROM Pasien WHERE NIK = ?");
        $stmt->execute([$nik]);
        $_SESSION['success_message'] = "Pasien berhasil dihapus";
        header("Location: manage_patients.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal menghapus pasien: " . $e->getMessage();
    }
}

// Fetch patient data for editing
$edit_data = null;
if (isset($_GET['edit']) && !empty($_GET['edit'])) {
    try {
        $stmt = $conn->prepare("SELECT * FROM Pasien WHERE NIK = ?");
        $stmt->execute([$_GET['edit']]);
        $edit_data = $stmt->fetch(PDO::FETCH_ASSOC);
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Error fetching patient data: " . $e->getMessage();
    }
}
// Handle Edit Action
if (isset($_POST['edit_patient'])) {
    try {
        $stmt = $conn->prepare("UPDATE Pasien SET Nama = ?, Tanggal_Lahir = ?, Jenis_Kelamin = ? WHERE NIK = ?");
        $stmt->execute([
            $_POST['nama'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $_POST['nik']
        ]);
        $_SESSION['success_message'] = "Data pasien berhasil diperbarui";
        header("Location: manage_patients.php");
        exit();
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Gagal memperbarui data pasien: " . $e->getMessage();
    }
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Aktifkan error reporting untuk debugging
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Handle Add Patient Action
if (isset($_POST['add_patient'])) {
    try {

        // Validasi input
        if (empty($_POST['nik']) || empty($_POST['nama']) || empty($_POST['tanggal_lahir']) || empty($_POST['jenis_kelamin'])) {
            throw new Exception("Semua field harus diisi");
        }

        if (strlen($_POST['nik']) !== 16 || !is_numeric($_POST['nik'])) {
            throw new Exception("NIK harus 16 digit angka");
        }

        // Check if NIK already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM Pasien WHERE NIK = ?");
        $stmt->execute([$_POST['nik']]);
        if ($stmt->fetchColumn() > 0) {
            throw new Exception("NIK sudah terdaftar");
        }

        // Generate nomor rekam medis
        $year = date('Y');
        $stmt = $conn->query("SELECT MAX(CAST(SUBSTRING_INDEX(Nomor_Rekam_Medis, '-', -1) AS UNSIGNED)) as max_num FROM Pasien WHERE Nomor_Rekam_Medis LIKE 'RMOffline-$year-%'");
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        $next_num = ($result['max_num'] ?? 0) + 1;
        $nomor_rm = sprintf("RMOffline-%s-%05d", $year, $next_num);

        // Calculate age
        $birthDate = new DateTime($_POST['tanggal_lahir']);
        $today = new DateTime();
        $age = $today->diff($birthDate)->y;

        // Insert data
        $stmt = $conn->prepare("INSERT INTO Pasien (NIK, Nama, Tanggal_Lahir, Jenis_Kelamin, Nomor_Rekam_Medis, Umur) VALUES (?, ?, ?, ?, ?, ?)");

        $success = $stmt->execute([
            $_POST['nik'],
            $_POST['nama'],
            $_POST['tanggal_lahir'],
            $_POST['jenis_kelamin'],
            $nomor_rm,
            $age
        ]);

        if ($success) {
            $_SESSION['success_message'] = "Pasien berhasil ditambahkan dengan Nomor RM: " . $nomor_rm;
            header("Location: manage_patients.php");
            exit();
        } else {
            throw new Exception("Gagal menyimpan data ke database");
        }
    } catch (PDOException $e) {
        $_SESSION['error_message'] = "Database Error: " . $e->getMessage();
    } catch (Exception $e) {
        $_SESSION['error_message'] = $e->getMessage();
    }
}

// Tampilkan pesan error/success
if (isset($_SESSION['error_message'])) {
    echo "<div class='bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative' role='alert'>";
    echo $_SESSION['error_message'];
    echo "</div>";
    unset($_SESSION['error_message']);
}

if (isset($_SESSION['success_message'])) {
    echo "<div class='bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative' role='alert'>";
    echo $_SESSION['success_message'];
    echo "</div>";
    unset($_SESSION['success_message']);
}
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pasien - Poliklinik X</title>
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
                    <a href="manage_patients.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-users"></i>
                        <span>Kelola Pasien</span>
                    </a>
                    <a href="pendaftaran_offline.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-notes-medical"></i>
                        <span>Pendaftaran Pemeriksaan</span>
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
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Kelola Pasien</h1>
                <button onclick="openModal('add')"
                    class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Pasien</span>
                </button>
            </div>

            <!-- Patient List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead>
                                <tr>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        NIK</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        No. Rekam Medis</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal Lahir</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jenis Kelamin</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT * FROM Pasien ORDER BY Nama");
                                    while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['NIK']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($row['Nama']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Nomor_Rekam_Medis']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Tanggal_Lahir']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Jenis_Kelamin']) . "</td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>
                                            <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>
                                                Aktif
                                            </span>
                                          </td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>
                                            <button onclick='openModal(\"edit\", " . json_encode($row) . ")' class='text-blue-600 hover:text-blue-900 mr-3'>
                                                <i class='fas fa-edit'></i>
                                            </button>
                                            <form method='POST' action='' class='inline' onsubmit='return confirmDelete()'>
                                                <input type='hidden' name='nik' value='" . htmlspecialchars($row['NIK']) . "'>
                                                <button type='submit' name='delete_patient' class='text-red-600 hover:text-red-900'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                            </form>
                                          </td>";
                                        echo "</tr>";
                                    }
                                } catch (PDOException $e) {
                                    echo "<tr><td colspan='7' class='text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>

    </div>
    <!-- Edit Patient Modal -->
    <div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="closeModal('edit')">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Data Pasien</h3>

                    <form id="editForm" method="POST" action="" class="space-y-4">
                        <input type="hidden" name="edit_patient" value="1">
                        <input type="hidden" name="nik" id="edit_nik">
                        
                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" id="edit_nama" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" id="edit_tanggal_lahir" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" id="edit_jenis_kelamin" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="closeModal('edit')"
                                class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
    <!-- Patient Modal -->
    <div id="patientModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="closeModal('add')">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div
                class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Pasien Baru</h3>

                    <form method="POST" action="" class="space-y-4">
                        <input type="hidden" name="add_patient" value="1">

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">NIK</label>
                            <input type="text" name="nik" required maxlength="16"
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                            <input type="text" name="nama" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                            <input type="date" name="tanggal_lahir" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin</label>
                            <select name="jenis_kelamin" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                <option value="">Pilih Jenis Kelamin</option>
                                <option value="Laki-laki">Laki-laki</option>
                                <option value="Perempuan">Perempuan</option>
                            </select>
                        </div>

                        <div class="mt-6 flex justify-end space-x-3">
                            <button type="button" onclick="closeModal('add')"
                                class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                Batal
                            </button>
                            <button type="submit" class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>


    <script>
        function openModal(type, data = null) {
            if (type === 'edit') {
                document.getElementById('editModal').classList.remove('hidden');
                // Populate edit form
                document.getElementById('edit_nik').value = data.NIK;
                document.getElementById('edit_nama').value = data.Nama;
                document.getElementById('edit_tanggal_lahir').value = data.Tanggal_Lahir;
                document.getElementById('edit_jenis_kelamin').value = data.Jenis_Kelamin;
            } else {
                document.getElementById('patientModal').classList.remove('hidden');
            }
        }


        function closeModal(type) {
            if (type === 'edit') {
                document.getElementById('editModal').classList.add('hidden');
            } else {
                document.getElementById('patientModal').classList.add('hidden');
            }
        }

        function confirmDelete() {
            return confirm('Apakah Anda yakin ingin menghapus data pasien ini?');
        }


        function editPatient(nik) {
            // Implement edit functionality
            alert('Edit patient with NIK: ' + nik);
        }

        function deletePatient(nik) {
            if (confirm('Are you sure you want to delete this patient?')) {
                // Implement delete functionality
                alert('Delete patient with NIK: ' + nik);
            }
        }
    </script>
</body>

</html>

<?php
$conn = null; // Close connection
?>