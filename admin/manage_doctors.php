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
                $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
                $stmt = $conn->prepare("UPDATE Dokter SET Nama = ?, Spesialis = ?, Username = ?, Password = ? WHERE ID_Dokter = ?");
                $stmt->bind_param("ssssi", $nama, $spesialis, $username, $hashedPassword, $dokterId);
            } else {
                $stmt = $conn->prepare("UPDATE Dokter SET Nama = ?, Spesialis = ?, Username = ? WHERE ID_Dokter = ?");
                $stmt->bind_param("sssi", $nama, $spesialis, $username, $dokterId);
            }
        } else {
            // Tambah dokter baru
            $hashedPassword = password_hash($password, PASSWORD_BCRYPT);
            $stmt = $conn->prepare("INSERT INTO Dokter (Nama, Spesialis, Username, Password) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("ssss", $nama, $spesialis, $username, $hashedPassword);
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
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_doctors.php">Kelola Dokter</a></li>
            <li><a href="manage_schedules.php">Kelola Jadwal</a></li>
            <li><a href="manage_patients.php">Kelola Pasien</a></li>
            <li><a href="manage_nurses.php">Kelola Perawat</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
    <?php if ($message): ?>
        <div class="message <?= $messageType; ?>"><?= $message; ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-header">
            <h2>Kelola Dokter</h2>
        </div>
        <div class="card-body">
            <form action="" method="POST">
                <input type="hidden" name="dokter_id" id="dokter_id">
                <div>
                    <label for="nama">Nama Dokter:</label>
                    <input type="text" id="nama" name="nama" required>
                </div>
                <div>
                    <label for="spesialis">Spesialis:</label>
                    <select id="spesialis" name="spesialis" required>
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
                    <label for="username">Username:</label>
                    <input type="text" id="username" name="username" required>
                </div>
                <div>
                    <label for="password">Password:</label>
                    <input type="password" id="password" name="password">

                </div>
                <button type="submit" class="btn btn-primary">Simpan</button>
            </form>

            <h3>Daftar Dokter</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nama</th>
                        <th>Spesialis</th>
                        <th>Username</th>
                        <th>Total Jadwal</th>
                        <th>Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php while ($row = $dokterQuery->fetch_assoc()): ?>
                        <tr>
                            <td><?= $row['Nama']; ?></td>
                            <td><?= $row['Spesialis']; ?></td>
                            <td><?= $row['Username']; ?></td>
                            <td><?= $row['total_jadwal']; ?></td>
                            <td>
                                <button class="btn btn-warning" onclick="editDokter(<?= htmlspecialchars(json_encode($row)); ?>)">Edit</button>
                                <br></br>
                                <button class="btn btn-danger" onclick="confirmDelete(<?= $row['ID_Dokter']; ?>)">Hapus</button>
                                
                            </td>
                        </tr>
                    <?php endwhile; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<script>
    function editDokter(dokter) {
        document.getElementById('dokter_id').value = dokter.ID_Dokter;
        document.getElementById('nama').value = dokter.Nama;
        document.getElementById('spesialis').value = dokter.Spesialis;
        document.getElementById('username').value = dokter.Username;
    }

    function confirmDelete(dokterId) {
        if (confirm("Yakin ingin menghapus dokter ini?")) {
            window.location.href = "?action=delete&id=" + dokterId;
        }
    }
</script>