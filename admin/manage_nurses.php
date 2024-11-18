<?php
session_start();
require_once('db_connection.php');
require_once('messages.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
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
    <title>Kelola Perawat - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="admin_dashboard.php">Dashboard</a></li>
            <li><a href="manage_doctors.php">Kelola Dokter</a></li>
            <li><a href="manage_nurses.php">Kelola Perawat</a></li>
            <li><a href="manage_schedules.php">Kelola Jadwal</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <?php displayMessage(); ?>

        <div class="card">
            <div class="card-header">
                <h2>Kelola Data Perawat</h2>
            </div>
            <div class="card-body">
                <!-- Form Tambah/Edit Perawat -->
                <form action="process_nurse.php" method="POST" class="nurse-form">
                    <div class="form-row">
                        <div class="form-col">
                            <div class="form-group">
                                <label for="nama">Nama Perawat:</label>
                                <input type="text" id="nama" name="nama" required>
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

                    <input type="hidden" name="perawat_id" id="perawat_id">
                    <button type="submit" name="action" value="add" class="btn btn-primary">Tambah Perawat</button>
                </form>

                <!-- Tabel Perawat -->
                <div class="table-container mt-4">
                    <h3>Daftar Perawat</h3>
                    <table>
                        <thead>
                            <tr>
                                <th>Nama</th>
                                <th>Username</th>
                                <th>Total Pemeriksaan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT p.*, 
                                   (SELECT COUNT(*) FROM Rekam_Medis rm 
                                    WHERE rm.ID_Perawat = p.ID_Perawat) as total_pemeriksaan
                                   FROM Perawat p
                                   ORDER BY p.Nama";
                            
                            $result = sqlsrv_query($conn, $sql);
                            
                            while($row = sqlsrv_fetch_array($result)) {
                                echo "<tr>";
                                echo "<td>" . $row['Nama'] . "</td>";
                                echo "<td>" . $row['Username'] . "</td>";
                                echo "<td>" . $row['total_pemeriksaan'] . "</td>";
                                echo "<td>";
                                echo "<button onclick='editPerawat(" . json_encode($row) . ")' 
                                      class='btn btn-warning btn-sm'>Edit</button> ";
                                
                                if($row['total_pemeriksaan'] == 0) {
                                    echo "<a href='process_nurse.php?action=delete&id=" . $row['ID_Perawat'] . "' 
                                          class='btn btn-danger btn-sm' 
                                          onclick='return confirm(\"Yakin ingin menghapus perawat ini?\")'>Hapus</a>";
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
        function editPerawat(perawat) {
            document.getElementById('nama').value = perawat.Nama;
            document.getElementById('username').value = perawat.Username;
            document.getElementById('password').value = '';
            document.getElementById('perawat_id').value = perawat.ID_Perawat;
            
            // Change form button
            const submitBtn = document.querySelector('button[type="submit"]');
            submitBtn.textContent = 'Update Perawat';
            submitBtn.value = 'update';
        }

        // Form validation
        document.querySelector('form').addEventListener('submit', function(e) {
            const action = document.querySelector('button[type="submit"]').value;
            const password = document.getElementById('password').value;
            
            if(action == 'add' && !password) {
                e.preventDefault();
                alert('Password harus diisi untuk perawat baru');
            }
        });
    </script>
</body>
</html>

<?php
closeDB($conn);
?>