<?php
session_start();
require_once('../config/db_connection.php');

// Cek apakah user sudah login dan memiliki tipe 'admin'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$message = '';
$messageType = '';

// Handle form submission untuk Tambah/Edit Dokter
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $dokterId = $_POST['dokter_id'] ?? null;
    $nama = $_POST['nama'];
    $spesialis = $_POST['spesialis'];
    $username = $_POST['username'];
    $password = $_POST['password'];

    if (empty($nama) || empty($spesialis) || empty($username)) {
        $message = "Semua field kecuali password harus diisi!";
        $messageType = "error";
    } else {
        if ($dokterId) {
            // Update data dokter
            if (!empty($password)) {
                // Simpan password tanpa hashing
                $stmt = $conn->prepare("UPDATE Dokter SET Nama = ?, Spesialis = ?, Username = ?, Password = ? WHERE ID_Dokter = ?");
                $stmt->bind_param("ssssi", $nama, $spesialis, $username, $password, $dokterId);
            } else {
                $stmt = $conn->prepare("UPDATE Dokter SET Nama = ?, Spesialis = ?, Username = ? WHERE ID_Dokter = ?");
                $stmt->bind_param("sssi", $nama, $spesialis, $username, $dokterId);
            }
        } else {
            // Tambah dokter baru
            $stmt = $conn->prepare("INSERT INTO Dokter (Nama, Spesialis, Username, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $spesialis, $username, $password);
        }

        if ($stmt->execute()) {
            $message = $dokterId ? "Data dokter berhasil diperbarui." : "Dokter baru berhasil ditambahkan.";
            $messageType = "success";
        } else {
            $message = "Terjadi kesalahan: " . $conn->error;
            $messageType = "error";
        }
    }
}

// Hapus dokter
if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $dokterId = $_GET['id'];
    $stmt = $conn->prepare("DELETE FROM Dokter WHERE ID_Dokter = ?");
    $stmt->bind_param("i", $dokterId);

    if ($stmt->execute()) {
        $message = "Dokter berhasil dihapus.";
        $messageType = "success";
    } else {
        $message = "Gagal menghapus dokter: " . $conn->error;
        $messageType = "error";
    }
}

// Ambil daftar dokter
$dokterQuery = $conn->query("
    SELECT d.*, 
           (SELECT COUNT(*) FROM Jadwal_Dokter j WHERE j.ID_Dokter = d.ID_Dokter) AS total_jadwal
    FROM Dokter d
    ORDER BY d.Nama
");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - Poliklinik X</title>
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
                    <a href="manage_doctors.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
        <!-- Main Content -->
        <main class="flex-1 ml-64 p-8">
            <?php if ($message): ?>
                <div
                    class="mb-4 p-4 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> rounded-md">
                    <?php echo htmlspecialchars($message); ?>
                </div>
            <?php endif; ?>

            <!-- Dokter Management Card -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <h2 class="text-xl font-bold mb-4">Kelola Dokter</h2>

                    <!-- Form Tambah/Edit Dokter -->
                    <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                        <input type="hidden" name="dokter_id" id="dokter_id">

                        <div>
                            <label for="nama" class="block text-sm font-medium text-gray-700">Nama Dokter</label>
                            <input type="text" id="nama" name="nama" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        </div>

                        <div>
                            <label for="spesialis" class="block text-sm font-medium text-gray-700">Spesialis</label>
                            <select id="spesialis" name="spesialis" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                                <option value="">Pilih Spesialis</option>
                                <option value="Umum">Umum</option>
                                <option value="Penyakit Dalam">Penyakit Dalam</option>
                                <option value="Anak">Anak</option>
                                <option value="Bedah">Bedah</option>
                                <option value="Kandungan">Kandungan</option>
                                <option value="Mata">Mata</option>
                                <option value="THT">THT</option>
                                <option value="Kulit">Kulit</option>
                                <option value="Saraf">Saraf</option>
                                <option value="Gigi">Gigi</option>
                            </select>
                        </div>

                        <div>
                            <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                            <input type="text" id="username" name="username" required
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        </div>

                        <div>
                            <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                            <input type="password" id="password" name="password"
                                class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                            <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                        </div>

                        <div class="col-span-full">
                            <button type="submit"
                                class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Simpan
                            </button>
                        </div>
                    </form>

                    <!-- Daftar Dokter -->
                    <h3 class="text-lg font-semibold mb-4">Daftar Dokter</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Spesialis
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Username
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Total Jadwal
                                    </th>
                                    <th
                                        class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $dokterQuery->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Nama']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Spesialis']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Username']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['total_jadwal']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3 flex">
                                            <a href="#" onclick="editDokter(<?= htmlspecialchars(json_encode($row)); ?>)"
                                                class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <form method="POST" action="" class="inline" onsubmit="return confirmDelete()">
                                                <input type="hidden" name="dokter_id" value="<?= $row['ID_Dokter']; ?>">
                                                <button type="submit" name="delete_doctor"
                                                    class="text-red-600 hover:text-red-900">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </form>
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

    <script>
        function editDokter(dokter) {
            document.getElementById('dokter_id').value = dokter.ID_Dokter;
            document.getElementById('nama').value = dokter.Nama;
            document.getElementById('spesialis').value = dokter.Spesialis;
            document.getElementById('username').value = dokter.Username;
            document.getElementById('password').value = '';
        }

        function confirmDelete(dokterId) {
            if (confirm("Yakin ingin menghapus dokter ini?")) {
                window.location.href = "?action=delete&id=" + dokterId;
            }
        }
    </script>
</body>

</html>