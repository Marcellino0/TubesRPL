<?php
session_start();
require_once('../config/db_connection.php');

if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

// Koneksi database sudah ada dari require db_connection.php
$dokter_id = $_SESSION['user_id'];

// Function sanitize untuk mencegah SQL injection
function sanitize($str) {
    global $conn;
    return mysqli_real_escape_string($conn, htmlspecialchars(trim($str)));
}

// Get patient list or search specific patient
$search = isset($_GET['search']) ? sanitize($_GET['search']) : '';
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis Pasien - Poliklinik X</title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <nav class="nav">
        <ul>
            <li><a href="doctor_dashboard.php">Dashboard</a></li>
            <li><a href="patient_queue.php">Antrian Pasien</a></li>
            <li><a href="medical_records.php">Rekam Medis</a></li>
            <!-- <li><a href="doctor_schedule.php">Jadwal Praktik</a></li> -->
            <li><a href="logout.php">Logout</a></li>
        </ul>
    </nav>

    <div class="container">
        <div class="card">
            <div class="card-header">
                <h2>Rekam Medis Pasien</h2>
                <form method="GET" class="search-form">
                    <div class="form-group">
                        <input type="text" name="search" placeholder="Cari pasien (Nama/No.RM)" value="<?php echo $search; ?>">
                        <button type="submit" class="btn btn-primary">Cari</button>
                    </div>
                </form>
            </div>
            <div class="card-body">
                <div class="table-container">
                    <table>
                        <thead>
                            <tr>
                                <th>No. RM</th>
                                <th>Nama Pasien</th>
                                <th>Tanggal Lahir</th>
                                <th>Riwayat Kunjungan</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT DISTINCT p.ID_Pasien, p.Nomor_Rekam_Medis, p.Nama, p.Tanggal_Lahir,
                                   (SELECT COUNT(*) FROM Pendaftaran pd 
                                    JOIN Jadwal_Dokter jd ON pd.ID_Jadwal = jd.ID_Jadwal
                                    WHERE pd.ID_Pasien = p.ID_Pasien 
                                    AND jd.ID_Dokter = ?) as total_visits
                                   FROM Pasien p
                                   JOIN Pendaftaran pd ON p.ID_Pasien = pd.ID_Pasien
                                   JOIN Jadwal_Dokter jd ON pd.ID_Jadwal = jd.ID_Jadwal
                                   WHERE jd.ID_Dokter = ?";
                            
                            if($search != '') {
                                $sql .= " AND (p.Nama LIKE ? OR p.Nomor_Rekam_Medis LIKE ?)";
                            }
                            
                            $sql .= " ORDER BY p.Nama";
                            
                            $stmt = $conn->prepare($sql);
                            
                            if($search != '') {
                                $search_param = "%$search%";
                                $stmt->bind_param("iiss", $dokter_id, $dokter_id, $search_param, $search_param);
                            } else {
                                $stmt->bind_param("ii", $dokter_id, $dokter_id);
                            }
                            
                            $stmt->execute();
                            $result = $stmt->get_result();
                            
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                echo "<td>" . $row['Nomor_Rekam_Medis'] . "</td>";
                                echo "<td>" . $row['Nama'] . "</td>";
                                echo "<td>" . date('d-m-Y', strtotime($row['Tanggal_Lahir'])) . "</td>";
                                echo "<td>" . $row['total_visits'] . " kali</td>";
                                echo "<td>
                                        <a href='view_medical_record.php?id=" . $row['ID_Pasien'] . "' 
                                           class='btn btn-info'>Lihat Detail</a>
                                     </td>";
                                echo "</tr>";
                            }
                            
                            $stmt->close();
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
// Tidak perlu closeDB() karena koneksi ditangani oleh db_connection.php
?>