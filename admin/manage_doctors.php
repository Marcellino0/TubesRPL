<?php
session_start();
require_once('../config/db_connection.php');

// Cek apakah user sudah login dan memiliki tipe 'admin'
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Dokter - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_doctors.php">Kelola Dokter</a></li>
            <li><a href="manage_schedules.php">Kelola Jadwal</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php displayMessage(); ?>

        <div class="card">
            <div class="card-header">
                <h2>Kelola Data Dokter</h2>
            </div>
            <div class="card-body">
                <!-- Form Tambah/Edit Dokter -->
                <form action="process_doctor.php" method="POST" class="doctor-form">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nama">Nama Dokter:</label>
                                <input type="text" id="nama" name="nama" required>
                            </div>

                            <div class="form-group">
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
                        </div>

                        <div class="form-col">
                            <div class="form-group">
                                <label for="username">Username:</label>
                                <input type="text" id="username" name="username" required>
                            </div>

                            <div class="form-group">
                                <label for="password">Password:</label>
                                <input type="password" id="password" name="password">
                                <small class="hint">Kosongkan jika tidak ingin mengubah password</small>
                            </div>
                        </div>
                    </div>

                    <input type="hidden" name="dokter_id" id="dokter_id">
                    <button type="submit" name="action" value="add" class="btn btn-primary">Tambah Dokter</button>
                </form>

                <!-- Tabel Dokter -->
                <div class="table-container mt-4">
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
                            <?php
                            $sql = "SELECT d.*, 
                                   (SELECT COUNT(*) FROM Jadwal_Dokter j 
                                    WHERE j.ID_Dokter = d.ID_Dokter) as total_jadwal
                                   FROM Dokter d
                                   ORDER BY d.Nama";
                            
                            $result = sqlsrv_query($conn, $sql);
                            
                            while($row = sqlsrv_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['Nama'] . "</td>";
                                echo "<td>" . $row['Spesialis'] . "</td>";
                                echo "<td>" . $row['Username'] . "</td>";
                                echo "<td>" . $row['total_jadwal'] . "</td>";
                                echo "<td>";
                                echo "<button onclick='editDokter(" . json_encode($row) . ")' 
                                      class='btn btn-warning btn-sm'>Edit</button> ";
                                
                                if($row['total_jadwal'] == 0) {
                                    echo "<a href='process_doctor.php?action=delete&id=" . $row['ID_Dokter'] . "' 
                                          class='btn btn-danger btn-sm' 
                                          onclick='return confirm(\"Yakin ingin menghapus dokter ini?\")'>Hapus</a>";
                                }
                                
                                echo "</td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <script>
        function editDokter(dokter) {
            document.getElementById('nama').value = dokter.Nama;
            document.getElementById('spesialis').value = dokter.Spesialis;
            document.getElementById('username').value = dokter.Username;
            document.getElementById('password').value = '';
            document.getElementById('dokter_id').value = dokter.ID_Dokter;
            
            // Change form button
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Update Dokter';
            submitBtn.value = 'update';
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const action = document.querySelector('button[type="submit"]').value;
            const password = document.getElementById('password').value;
            
            if(action == 'add' && !password) {
                e.preventDefault();
                alert('Password harus diisi untuk dokter baru');
            }
        });
    </script>
</body>
</html>

<?php
closeDB($conn);
?>