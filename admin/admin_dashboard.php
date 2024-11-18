<?php
session_start();

// Database connection configuration
function connectDB() {
    $host = 'localhost';
    $dbname = 'PoliklinikX';
    $username = 'root';  // Ganti dengan username MySQL Anda
    $password = '';      // Ganti dengan password MySQL Anda

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Check if user is logged in as admin
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
    <title>Dashboard Admin - Poliklinik X</title>
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
        <h1>Selamat Datang, <?php echo htmlspecialchars($_SESSION['nama']); ?></h1>
        
        <div class="dashboard-stats">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Statistik Hari Ini</h3>
                </div>
                <div class="card-body">
                    <?php
                    try {
                        // Get today's statistics
                        $today = date('Y-m-d');
                        
                        // Count total appointments today
                        $sql = "SELECT COUNT(*) as total FROM Pendaftaran 
                               WHERE DATE(Waktu_Daftar) = :today";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':today', $today);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $total_appointments = $row['total'];
                        
                        // Count completed payments today
                        $sql = "SELECT COUNT(*) as total FROM Pembayaran 
                               WHERE DATE(Tanggal) = :today AND Status = 'Lunas'";
                        $stmt = $conn->prepare($sql);
                        $stmt->bindParam(':today', $today);
                        $stmt->execute();
                        $row = $stmt->fetch(PDO::FETCH_ASSOC);
                        $completed_payments = $row['total'];
                    ?>
                    
                    <p>Total Pendaftaran: <?php echo $total_appointments; ?></p>
                    <p>Pembayaran Selesai: <?php echo $completed_payments; ?></p>
                    
                    <?php
                    } catch(PDOException $e) {
                        echo "<p>Error: " . $e->getMessage() . "</p>";
                    }
                    ?>
                </div>
            </div>
            
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Daftar Dokter Praktek Hari Ini</h3>
                </div>
                <div class="card-body">
                    <div class="table-container">
                        <table>
                            <thead>
                                <tr>
                                    <th>Nama Dokter</th>
                                    <th>Spesialis</th>
                                    <th>Jam Praktek</th>
                                    <th>Sisa Kuota</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php
                                try {
                                    $day = date('l'); // Get current day name
                                    $sql = "SELECT 
                                            d.Nama, 
                                            d.Spesialis, 
                                            j.Jam_Mulai, 
                                            j.Jam_Selesai, 
                                            j.Kuota,
                                            (SELECT COUNT(*) 
                                             FROM Pendaftaran p 
                                             WHERE p.ID_Jadwal = j.ID_Jadwal 
                                             AND DATE(p.Waktu_Daftar) = :today) as used_quota
                                           FROM Dokter d
                                           JOIN Jadwal_Dokter j ON d.ID_Dokter = j.ID_Dokter
                                           WHERE j.Hari = :day";
                                    
                                    $stmt = $conn->prepare($sql);
                                    $stmt->bindParam(':today', $today);
                                    $stmt->bindParam(':day', $day);
                                    $stmt->execute();
                                    
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        $sisa_kuota = $row['Kuota'] - $row['used_quota'];
                                        echo "<tr>";
                                        echo "<td>" . htmlspecialchars($row['Nama']) . "</td>";
                                        echo "<td>" . htmlspecialchars($row['Spesialis']) . "</td>";
                                        echo "<td>" . date('H:i', strtotime($row['Jam_Mulai'])) . " - " . 
                                             date('H:i', strtotime($row['Jam_Selesai'])) . "</td>";
                                        echo "<td>" . $sisa_kuota . "</td>";
                                        echo "</tr>";
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='4'>Error: " . $e->getMessage() . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>

<?php
$conn = null; // Close connection
?>