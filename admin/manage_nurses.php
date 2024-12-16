<?php
session_start(); // Memulai sesi untuk mengakses variabel sesi
require_once('../config/db_connection.php'); // Memuat koneksi database

// Cek apakah user sudah login dan memiliki tipe 'admin'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php"); // Jika bukan admin atau belum login, redirect ke halaman login
    exit(); // Menghentikan eksekusi lebih lanjut
}


$message = ''; // Menyimpan pesan yang akan ditampilkan kepada pengguna
$messageType = ''; // Menyimpan tipe pesan (success/error)
$editData = null; // Inisialisasi $editData sebagai null, digunakan untuk menampung data perawat yang diedit


if (isset($_GET['action']) && $_GET['action'] === 'edit' && isset($_GET['id'])) {
    $perawatId = $_GET['id']; // Ambil ID perawat dari query string
    $stmt = $conn->prepare("SELECT * FROM Perawat WHERE ID_Perawat = ?"); // Query untuk mengambil data perawat berdasarkan ID
    $stmt->bind_param("i", $perawatId); // Binding parameter ID perawat
    $stmt->execute(); // Eksekusi query
    $result = $stmt->get_result(); // Ambil hasil query
    $editData = $result->fetch_assoc(); // Ambil data perawat untuk ditampilkan di form
}


if ($_SERVER['REQUEST_METHOD'] === 'POST') { // Cek jika request method adalah POST (submit form)
    $perawatId = $_POST['perawat_id'] ?? null; // Ambil ID perawat jika ada
    $nama = $_POST['nama']; // Ambil nama perawat
    $username = $_POST['username']; // Ambil username perawat
    $password = $_POST['password']; // Ambil password perawat

    // Validasi inputan
    if (empty($nama) || empty($username)) {
        $message = "Semua field kecuali password harus diisi!";
        $messageType = "error";
    } else {
        if ($perawatId) {
            // Update data perawat jika ID perawat ada
            if (!empty($password)) {
                $stmt = $conn->prepare("UPDATE Perawat SET Nama = ?, Username = ?, Password = ? WHERE ID_Perawat = ?");
                $stmt->bind_param("sssi", $nama, $username, $password, $perawatId); // Binding parameter untuk update
            } else {
                $stmt = $conn->prepare("UPDATE Perawat SET Nama = ?, Username = ? WHERE ID_Perawat = ?");
                $stmt->bind_param("ssi", $nama, $username, $perawatId); // Update tanpa password
            }
        } else {
            // Tambah data perawat baru jika ID tidak ada
            $stmt = $conn->prepare("INSERT INTO Perawat (Nama, Username, Password) VALUES (?, ?, ?)");
            $stmt->bind_param("sss", $nama, $username, $password); // Binding parameter untuk insert
        }

        if ($stmt->execute()) {
            $message = $perawatId ? "Data perawat berhasil diperbarui." : "Perawat baru berhasil ditambahkan.";
            $messageType = "success";
            $editData = null; // Reset data edit setelah berhasil
            header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message) . "&type=" . urlencode($messageType));
            exit(); // Redirect untuk mencegah pengiriman ulang form
        } else {
            $message = "Terjadi kesalahan: " . $conn->error;
            $messageType = "error";
        }
    }
}


if (isset($_GET['action']) && $_GET['action'] === 'delete' && isset($_GET['id'])) {
    $perawatId = $_GET['id']; // Ambil ID perawat dari query string
    $stmt = $conn->prepare("DELETE FROM Perawat WHERE ID_Perawat = ?");
    $stmt->bind_param("i", $perawatId); // Binding parameter ID perawat untuk menghapus

    if ($stmt->execute()) {
        $message = "Perawat berhasil dihapus.";
        $messageType = "success";
    } else {
        $message = "Gagal menghapus perawat: " . $conn->error;
        $messageType = "error";
    }
    
    header("Location: " . $_SERVER['PHP_SELF'] . "?message=" . urlencode($message) . "&type=" . urlencode($messageType));
    exit(); // Redirect setelah operasi hapus untuk menghindari pengiriman ulang
}


// Handle messages from redirects
if (isset($_GET['message']) && isset($_GET['type'])) {
    $message = $_GET['message']; // Ambil pesan dari query string
    $messageType = $_GET['type']; // Ambil tipe pesan (success/error)
}


// Ambil daftar perawat
$perawatQuery = $conn->query("SELECT * FROM Perawat ORDER BY Nama");
?>
<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Perawat - Poliklinik X</title>
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
                    <a href="pendaftaran_ulang.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
                        <i class="fas fa-globe"></i>
                        <span>Pendaftaran Ulang</span>
                    </a>
                    <a href="manage_nurses.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
        <?php if ($message): ?>
            <div class="mb-4 p-4 <?php echo $messageType === 'success' ? 'bg-green-100 text-green-800' : 'bg-red-100 text-red-800'; ?> rounded-md">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <!-- Perawat Management Card -->
        <div class="bg-white rounded-lg shadow">
            <div class="p-6">
                <h2 class="text-xl font-bold mb-4">Kelola Perawat</h2>

                <!-- Form Tambah/Edit Perawat -->
                <form action="" method="POST" class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-6">
                    <input type="hidden" name="perawat_id" value="<?php echo $editData ? htmlspecialchars($editData['ID_Perawat']) : ''; ?>">

                    <div>
                        <label for="nama" class="block text-sm font-medium text-gray-700">Nama Perawat</label>
                        <input type="text" id="nama" name="nama" required
                            value="<?php echo $editData ? htmlspecialchars($editData['Nama']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                    </div>

                    <div>
                        <label for="username" class="block text-sm font-medium text-gray-700">Username</label>
                        <input type="text" id="username" name="username" required
                            value="<?php echo $editData ? htmlspecialchars($editData['Username']) : ''; ?>"
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                    </div>

                    <div class="col-span-2">
                        <label for="password" class="block text-sm font-medium text-gray-700">Password</label>
                        <input type="password" id="password" name="password"
                            <?php echo !$editData ? 'required' : ''; ?>
                            class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-blue-300 focus:ring focus:ring-blue-200">
                        <?php if ($editData): ?>
                            <p class="mt-1 text-sm text-gray-500">Kosongkan jika tidak ingin mengubah password</p>
                        <?php endif; ?>
                    </div>

                    <div class="col-span-full">
                        <button type="submit"
                            class="w-full bg-blue-600 text-white py-2 px-4 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500">
                            <?php echo $editData ? 'Update' : 'Simpan'; ?>
                        </button>
                    </div>
                </form>

                    <!-- Daftar Perawat -->
                    <h3 class="text-lg font-semibold mb-4">Daftar Perawat</h3>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Nama
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Username
                                    </th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                        Aksi
                                    </th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php while ($row = $perawatQuery->fetch_assoc()): ?>
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Nama']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            <?= htmlspecialchars($row['Username']); ?>
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-3 flex">
                                            <a href="?action=edit&id=<?= $row['ID_Perawat']; ?>" 
                                               class="text-blue-600 hover:text-blue-900">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                            <button onclick="confirmDelete(<?= $row['ID_Perawat']; ?>)"
                                                    class="text-red-600 hover:text-red-900">
                                                <i class="fas fa-trash"></i>
                                            </button>
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
        function editPerawat(perawat) {
            document.getElementById('perawat_id').value = perawat.ID_Perawat;
            document.getElementById('nama').value = perawat.Nama;
            document.getElementById('username').value = perawat.Username;
            document.getElementById('password').value = '';
        }

        function confirmDelete(perawatId) {
            if (confirm("Yakin ingin menghapus perawat ini?")) {
                window.location.href = `?action=delete&id=${perawatId}`;
            }
        }
    </script>
</body>

</html>