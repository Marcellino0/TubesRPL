<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as doctor
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Dokter - Poliklinik X</title>
    <style>
        /* Style CSS sama seperti sebelumnya */
        body {
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
            background-color: #f4f4f4;
        }

        .nav {
            background-color: #333;
            color: white;
            padding: 1rem;
        }

        .nav ul {
            list-style-type: none;
            padding: 0;
            margin: 0;
            display: flex;
            justify-content: flex-start;
            gap: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
        }

        .nav a:hover {
            background-color: #555;
            border-radius: 4px;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .dashboard-stats {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
            overflow: hidden;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
        }

        .card-title {
            margin: 0;
            font-size: 1.2rem;
        }

        .card-body {
            padding: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 12px;
            text-align: left;
            border-bottom: 1px solid #ddd;
        }

        th {
            background-color: #f8f9fa;
        }

        .btn {
            padding: 6px 12px;
            border-radius: 4px;
            text-decoration: none;
            display: inline-block;
            font-size: 14px;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
        }

        .table-container {
            overflow-x: auto;
        }
    </style>
</head>
<body>
<nav class="nav">
    <ul>
        <li><a href="./doctor_dashboard.php">Dashboard</a></li>
        <li><a href="./patient_queue.php">Antrian Pasien</a></li>
        <li><a href="./medical_records.php">Rekam Medis</a></li>
        <li><a href="./logout.php">Logout</a></li>
    </ul>
</nav>

    <div class="container">
        <h1>Selamat Datang, <?php echo $_SESSION['nama']; ?></h1>
        
        <div class="dashboard-stats">
            <!-- Antrian Pasien Hari Ini -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Antrian Pasien Hari Ini</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>No. Antrian</th>
                                    <th>Nama Pasien</th>
                                    <th>No. Rekam Medis</th>
                                    <th>Status</th>
                                    <th>Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                $today = date('Y-m-d');
                                $sql = "SELECT p.No_Antrian, pas.Nama, pas.Nomor_Rekam_Medis, p.Status, p.ID_Pendaftaran
                                       FROM Pendaftaran p
                                       JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                                       JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                                       WHERE j.ID_Dokter = $dokter_id 
                                       AND DATE(p.Waktu_Daftar) = '$today'
                                       ORDER BY p.No_Antrian";
                                
                                $result = mysqli_query($conn, $sql);
                                
                                if (!$result) {
                                    die("Error: " . mysqli_error($conn));
                                }
                                
                                while($row = mysqli_fetch_array($result)) {
                                    echo "<tr>";
                                    echo "<td>" . $row['No_Antrian'] . "</td>";
                                    echo "<td>" . $row['Nama'] . "</td>";
                                    echo "<td>" . $row['Nomor_Rekam_Medis'] . "</td>";
                                    echo "<td>" . $row['Status'] . "</td>";
                                    echo "<td>";
                                    if($row['Status'] == 'Menunggu') {
                                        echo "<a href='examine_patient.php?id=" . $row['ID_Pendaftaran'] . "' 
                                              class='btn btn-primary'>Periksa</a>";
                                    } else if($row['Status'] == 'Diperiksa') {
                                        echo "<a href='continue_examination.php?id=" . $row['ID_Pendaftaran'] . "' 
                                              class='btn btn-warning'>Lanjutkan</a>";
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

            <!-- Jadwal Praktik -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jadwal Praktik Hari Ini</h3>
                </div>
                <div class="card-body">
                    <?php
                    $day = date('l'); // Get current day name
                    $sql = "SELECT Jam_Mulai, Jam_Selesai, Kuota,
                           (SELECT COUNT(*) FROM Pendaftaran p 
                            WHERE p.ID_Jadwal = j.ID_Jadwal 
                            AND DATE(p.Waktu_Daftar) = '$today') as total_pasien
                           FROM Jadwal_Dokter j
                           WHERE ID_Dokter = $dokter_id AND Hari = '$day'";
                    
                    $result = mysqli_query($conn, $sql);
                    
                    if (!$result) {
                        die("Error: " . mysqli_error($conn));
                    }
                    
                    if($jadwal = mysqli_fetch_array($result)) {
                        echo "<p>Jam Praktik: " . $jadwal['Jam_Mulai'] . " - " . 
                             $jadwal['Jam_Selesai'] . "</p>";
                        echo "<p>Total Pasien: " . $jadwal['total_pasien'] . " / " . $jadwal['Kuota'] . "</p>";
                    } else {
                        echo "<p>Tidak ada jadwal praktik hari ini</p>";
                    }
                    ?>
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