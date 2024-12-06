<?php
session_start();

// Database connection configuration
function connectDB()
{
    $host = 'localhost';
    $dbname = 'PoliklinikX';
    $username = 'root';  // Replace with your MySQL username
    $password = '';      // Replace with your MySQL password

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch (PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }
}

// Check if user is logged in as admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    header("Location: login.php");
    exit();
}

$conn = connectDB();
$today = date('Y-m-d');


?>

<!DOCTYPE html>
<html lang="id">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Admin - Poliklinik X</title>
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
                    <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
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
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Today's Statistics -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Statistik Hari Ini</h2>
                        </div>
                        <?php
                        try {
                            // Count total appointments today
                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Pendaftaran WHERE DATE(Waktu_Daftar) = :today");
                            $stmt->bindParam(':today', $today);
                            $stmt->execute();
                            $total_appointments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];

                            // Count completed payments today
                            $stmt = $conn->prepare("SELECT COUNT(*) as total FROM Pembayaran WHERE DATE(Tanggal) = :today AND Status = 'Lunas'");
                            $stmt->bindParam(':today', $today);
                            $stmt->execute();
                            $completed_payments = $stmt->fetch(PDO::FETCH_ASSOC)['total'];
                            ?>
                            <div class="grid grid-cols-2 gap-4">
                                <div class="bg-blue-100 p-4 rounded-lg">
                                    <h3 class="text-sm text-gray-600">Total Pendaftaran</h3>
                                    <p class="text-2xl font-bold text-blue-800"><?php echo $total_appointments; ?></p>
                                </div>
                                <div class="bg-green-100 p-4 rounded-lg">
                                    <h3 class="text-sm text-gray-600">Pembayaran Selesai</h3>
                                    <p class="text-2xl font-bold text-green-800"><?php echo $completed_payments; ?></p>
                                </div>
                            </div>
                            <?php
                        } catch (PDOException $e) {
                            echo "<p class='text-red-500'>Error: " . $e->getMessage() . "</p>";
                        }
                        ?>
                    </div>
                </div>

                <!-- Doctors on Duty -->
                <div class="bg-white rounded-lg shadow">
                    <div class="p-6">
                        <div class="flex justify-between items-center mb-4">
                            <h2 class="text-xl font-bold">Daftar Dokter Praktek Hari Ini</h2>
                        </div>
                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead>
                                    <tr>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Nama</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Spesialis</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Jam Praktek</th>
                                        <th
                                            class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                            Sisa Kuota</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    <?php
                                    try {
                                        // Konversi nama hari ke bahasa Indonesia
                                        $dayNames = [
                                            'Sunday' => 'Minggu',
                                            'Monday' => 'Senin',
                                            'Tuesday' => 'Selasa',
                                            'Wednesday' => 'Rabu',
                                            'Thursday' => 'Kamis',
                                            'Friday' => 'Jumat',
                                            'Saturday' => 'Sabtu'
                                        ];
                                        
                                        $day = $dayNames[date('l')]; // Konversi hari ini ke bahasa Indonesia
                                    
                                        $sql = "SELECT 
                                                d.Nama, 
                                                d.Spesialis, 
                                                j.Jam_Mulai, 
                                                j.Jam_Selesai, 
                                                j.Kuota_Offline,
                                                j.Kuota_Online,
                                                (SELECT COUNT(*) 
                                                 FROM Pendaftaran p 
                                                 WHERE p.ID_Jadwal = j.ID_Jadwal 
                                                 AND DATE(p.Waktu_Daftar) = :today) as used_quota
                                               FROM Dokter d
                                               JOIN Jadwal_Dokter j ON d.ID_Dokter = j.ID_Dokter
                                               WHERE j.Hari = :day
                                               AND j.Status = 'Aktif'";
                                    
                                        // Debug: Print values
                                        echo "<!-- Current Day: " . $day . " -->"; // For debugging
                                        
                                        $stmt = $conn->prepare($sql);
                                        $stmt->bindParam(':today', $today);
                                        $stmt->bindParam(':day', $day);
                                        $stmt->execute();
                                        
                                        if($stmt->rowCount() === 0) {
                                            echo "<tr><td colspan='4' class='px-6 py-4 text-center text-gray-500'>Tidak ada dokter yang praktek hari ini</td></tr>";
                                        } else {
                                            while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                                $total_kuota = $row['Kuota_Offline'] + $row['Kuota_Online'];
                                                $sisa_kuota = $total_kuota - $row['used_quota'];
                                                $quota_color = $sisa_kuota <= 2 ? 'text-red-600' : 'text-green-600';
                                                
                                                echo "<tr>";
                                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($row['Nama']) . "</td>";
                                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Spesialis']) . "</td>";
                                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . 
                                                     date('H:i', strtotime($row['Jam_Mulai'])) . " - " . 
                                                     date('H:i', strtotime($row['Jam_Selesai'])) . "</td>";
                                                echo "<td class='px-6 py-4 whitespace-nowrap text-sm " . $quota_color . "'>" . $sisa_kuota . "</td>";
                                                echo "</tr>";
                                            }
                                        }
                                    
                                        // Debug: Print query and parameters
                                        echo "<!-- SQL: " . $sql . " -->";
                                        echo "<!-- Parameters: today=" . $today . ", day=" . $day . " -->";
                                        
                                    } catch(PDOException $e) {
                                        echo "<tr><td colspan='4' class='text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
                                    }catch (PDOException $e) {
                                        echo "<tr><td colspan='4' class='text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
                                    }
                                    ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</body>

</html>

<?php
$conn = null; // Close connection
?>