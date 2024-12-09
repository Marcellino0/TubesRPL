<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle Add Payment
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $namaPasien = isset($_POST['nama_pasien']) ? $_POST['nama_pasien'] : '';
    $jumlah = $_POST['jumlah'];
    $metode = $_POST['metode'];
    $status = isset($_POST['status']) ? 'Lunas' : 'Belum Lunas';

    // Get ID_Pendaftaran based on nama pasien
    $stmt = $conn->prepare("
        SELECT pd.ID_Pendaftaran
        FROM Pendaftaran pd
        JOIN Pasien pa ON pd.ID_Pasien = pa.ID_Pasien
        JOIN Pemeriksaan pm ON pd.ID_Pendaftaran = pm.ID_Pendaftaran
        WHERE pa.Nama = ?
        ORDER BY pd.ID_Pendaftaran DESC
        LIMIT 1
    ");
    $stmt->bind_param("s", $namaPasien);
    $stmt->execute();
    $result = $stmt->get_result();
    $pendaftaran = $result->fetch_assoc();

    if ($pendaftaran) {
        $pendaftaranId = $pendaftaran['ID_Pendaftaran'];

        // Insert payment data into the database
        $stmt = $conn->prepare("INSERT INTO Pembayaran (ID_Pendaftaran, Tanggal, Jumlah, Metode, Status) VALUES (?, NOW(), ?, ?, ?)");
        $stmt->bind_param("idss", $pendaftaranId, $jumlah, $metode, $status);

        if ($stmt->execute()) {
            $message = "Pembayaran berhasil ditambahkan.";
            $messageType = "success";
        } else {
            $message = "Terjadi kesalahan: " . $conn->error;
            $messageType = "error";
        }
    } else {
        $message = "Data pendaftaran tidak ditemukan untuk pasien yang dipilih.";
        $messageType = "error";
    }
}

// Handle Edit Payment
if (isset($_POST['edit_payment'])) {
    $pembayaranId = $_POST['payment_id'];
    $jumlah = $_POST['jumlah'];
    $metode = $_POST['metode'];
    $status = isset($_POST['status']) ? 'Lunas' : 'Belum Lunas';

    $stmt = $conn->prepare("UPDATE Pembayaran SET Jumlah = ?, Metode = ?, Status = ? WHERE ID_Pembayaran = ?");
    $stmt->bind_param("dssi", $jumlah, $metode, $status, $pembayaranId);

    if ($stmt->execute()) {
        $message = "Pembayaran berhasil diperbarui.";
        $messageType = "success";
    } else {
        $message = "Terjadi kesalahan: " . $conn->error;
        $messageType = "error";
    }
}

// Handle Delete Payment
if (isset($_GET['delete'])) {
    $pembayaranId = $_GET['delete'];

    $stmt = $conn->prepare("DELETE FROM Pembayaran WHERE ID_Pembayaran = ?");
    $stmt->bind_param("i", $pembayaranId);

    if ($stmt->execute()) {
        $message = "Pembayaran berhasil dihapus.";
        $messageType = "success";
    } else {
        $message = "Terjadi kesalahan: " . $conn->error;
        $messageType = "error";
    }
}

// Retrieve payment data from the database
$paymentQuery = $conn->query("
    SELECT p.ID_Pembayaran, pa.Nama AS Nama_Pasien, d.Nama AS Nama_Dokter, d.Spesialis, p.Tanggal, p.Jumlah, p.Metode, p.Status
    FROM Pembayaran p
    JOIN Pendaftaran pd ON p.ID_Pendaftaran = pd.ID_Pendaftaran
    JOIN Pasien pa ON pd.ID_Pasien = pa.ID_Pasien
    JOIN Jadwal_Dokter jd ON pd.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    ORDER BY p.Tanggal DESC
");
?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pembayaran - Poliklinik X</title>
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
                    <a href="pendaftaran_offline.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-notes-medical"></i>
                        <span>Pendaftaran Pemeriksaan</span>
                    </a>
                    <a href="manage_nurses.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-user-nurse"></i>
                        <span>Kelola Perawat</span>
                    </a>
                    <a href="manage_payments.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
            <!-- Payment Management Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Kelola Pembayaran</h2>

                    <!-- Form Tambah Pembayaran -->
                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <div>
                            <label for="nama_pasien" class="block text-sm font-medium text-gray-700">Nama Pasien</label>
                            <select id="nama_pasien" name="nama_pasien" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <option value="">Pilih Pasien</option>
                                <?php
                                $pasienQuery = $conn->query("
                                    SELECT pa.Nama
                                    FROM Pasien pa
                                    JOIN Pendaftaran pd ON pa.ID_Pasien = pd.ID_Pasien
                                    JOIN Pemeriksaan pm ON pd.ID_Pendaftaran = pm.ID_Pendaftaran
                                    GROUP BY pa.Nama
                                ");
                                while ($pasien = $pasienQuery->fetch_assoc()) {
                                    echo "<option value='" . $pasien['Nama'] . "'>" . $pasien['Nama'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label for="jumlah" class="block text-sm font-medium text-gray-700">Jumlah</label>
                            <input type="number" id="jumlah" name="jumlah" step="0.01" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        </div>

                        <div>
                            <label for="metode" class="block text-sm font-medium text-gray-700">Metode Pembayaran</label>
                            <select id="metode" name="metode" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <option value="">Pilih Metode Pembayaran</option>
                                <?php
                                $metodeQuery = $conn->query("SELECT * FROM Metode_Pembayaran WHERE Status = 'Aktif'");
                                while ($metode = $metodeQuery->fetch_assoc()) {
                                    echo "<option value='" . $metode['Nama_Metode'] . "'>" . $metode['Nama_Metode'] . "</option>";
                                }
                                ?>
                            </select>
                        </div>

                        <div>
                            <label class="inline-flex items-center">
                                <input type="checkbox" name="status" class="form-checkbox h-5 w-5 text-blue-600">
                                <span class="ml-2 text-gray-700">Pembayaran Lunas</span>
                            </label>
                        </div>

                        <div class="col-span-full">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Simpan
                            </button>
                        </div>
                    </form>

                    <!-- Daftar Pembayaran -->
                    <h3 class="text-lg font-semibold mb-4">Daftar Pembayaran</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Pasien</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama Dokter</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Spesialis</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Tanggal</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Jumlah</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Metode</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Status</th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $paymentQuery->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Nama_Pasien']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Nama_Dokter']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Spesialis']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Tanggal']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Jumlah']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Metode']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Status']); ?></td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                            <a href="#" onclick="openModal('edit', <?= $row['ID_Pembayaran']; ?>)"
                                                class="text-blue-600 hover:text-blue-900 mr-3">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <a href="?delete=<?= $row['ID_Pembayaran']; ?>"
                                                onclick="return confirm('Apakah Anda yakin ingin menghapus pembayaran ini?')"
                                                class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
    <!-- Edit Payment Modal -->
    <div id="editModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="closeModal('edit')">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Edit Pembayaran</h3>

                    <form id="editForm" method="POST" action="" class="space-y-4">
                        <input type="hidden" name="edit_payment" value="1">
                        <input type="hidden" name="payment_id" id="edit_payment_id">

                        <div>
                            <label class="block text-gray-700 text-sm font-bold mb-2">Jumlah</label>
                            <input type="number" name="jumlah" id="edit_jumlah" step="0.01" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                        </div>
                        <div>
                        <label class="block text-gray-700 text-sm font-bold mb-2">Metode Pembayaran</label>
<select name="metode" id="edit_metode" required
    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
    <option value="">Pilih Metode Pembayaran</option>
    <?php
    $metodeQuery = $conn->query("SELECT * FROM Metode_Pembayaran WHERE Status = 'Aktif'");
    while ($metode = $metodeQuery->fetch_assoc()) {
        echo "<option value='" . $metode['Nama_Metode'] . "'>" . $metode['Nama_Metode'] . "</option>";
    }
    ?>
</select>
</div>

<div>
<label class="inline-flex items-center">
    <input type="checkbox" name="status" id="edit_status" class="form-checkbox h-5 w-5 text-blue-600">
    <span class="ml-2 text-gray-700">Pembayaran Lunas</span>
</label>
</div>

<div class="mt-6 flex justify-end space-x-3">
<button type="button" onclick="closeModal('edit')"
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
function openModal(type, id = null) {
    if (type === 'edit') {
        document.getElementById('editModal').classList.remove('hidden');
        if (id) {
            document.getElementById('edit_payment_id').value = id;
            // Fetch payment data and populate the form
            fetch(`?id=${id}`)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('edit_jumlah').value = data.Jumlah;
                    document.getElementById('edit_metode').value = data.Metode;
                    document.getElementById('edit_status').checked = data.Status === 'Lunas';
                });
        }
    }
}

function closeModal(type) {
    if (type === 'edit') {
        document.getElementById('editModal').classList.add('hidden');
    }
}

function confirmDelete() {
    return confirm('Apakah Anda yakin ingin menghapus data pembayaran ini?');
}

// Close modal when clicking outside
window.onclick = function(event) {
    if (event.target.classList.contains('fixed')) {
        event.target.classList.add('hidden');
    }
}
</script>
</body>
</html>