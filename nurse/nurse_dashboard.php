<?php
session_start();
require_once('db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'perawat') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$perawat_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Perawat - Poliklinik X</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="nurse_dashboard.php">Dashboard</a></li>
            <li><a href="waiting_patients.php">Pasien Menunggu</a></li>
            <li><a href="patient_vital_signs.php">Catat Tanda Vital</a></li>
            <li><a href="medical_records.php">Rekam Medis</a></li>
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?></h1>
        
        <div class="dashboard-stats">
            <!-- Daftar Pasien Hari Ini -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Pasien Hari Ini</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Antrian</th>
                                    <th>Nama Pasien</th>
                                    <th>No. RM</th>
                                    <th>Dokter</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $today = date('Y-m-d');
                                $sql = "SELECT p.No_Antrian, pas.Nama as nama_pasien, 
                                       pas.Nomor_Rekam_Medis, d.Nama as nama_dokter,
                                       p.Status, p.ID_Pendaftaran, pas.ID_Pasien
                                       FROM Pendaftaran p
                                       JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                                       JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                                       JOIN Dokter d ON j.ID_Dokter = d.ID_Dokter
                                       WHERE CONVERT(date, p.Waktu_Daftar) = '$today'
                                       ORDER BY p.No_Antrian";
                                
                                $result = sqlsrv_query($conn, $sql);
                                
                                while($row = sqlsrv_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['No_Antrian'] . "</td>";
                                    echo "<td>" . $row['nama_pasien'] . "</td>";
                                    echo "<td>" . $row['Nomor_Rekam_Medis'] . "</td>";
                                    echo "<td>" . $row['nama_dokter'] . "</td>";
                                    echo "<td>" . $row['Status'] . "</td>";
                                    echo "<td>";
                                    
                                    // Check if vital signs already recorded
                                    $check_sql = "SELECT TOP 1 ID_Rekam 
                                                FROM Rekam_Medis 
                                                WHERE ID_Pasien = " . $row['ID_Pasien'] . "
                                                AND CONVERT(date, Tanggal) = '$today'";
                                    $check_result = sqlsrv_query($conn, $check_sql);
                                    $has_vitals = sqlsrv_has_rows($check_result);
                                    
                                    if(!$has_vitals && $row['Status'] == 'Menunggu') {
                                        echo "<a href='record_vital_signs.php?id=" . $row['ID_Pendaftaran'] . "' 
                                              class='btn btn-primary'>Catat Vital</a>";
                                    } else {
                                        echo "Sudah Dicatat";
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
    </div>

    <script>
        // Auto refresh setiap 30 detik
        setTimeout(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>

<?php
closeDB($conn);
?>