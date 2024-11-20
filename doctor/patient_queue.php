<?php
session_start();
require_once('../config/db_connection.php');

// Check if user is logged in as doctor
if(!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'dokter') {
    header("Location: login.php");
    exit();
}

$dokter_id = $_SESSION['user_id'];
$msg = '';

// Handle patient status updates
if(isset($_POST['update_status'])) {
    $pendaftaran_id = $_POST['pendaftaran_id'];
    $new_status = $_POST['status'];
    
    $update_sql = "UPDATE Pendaftaran SET Status = ? WHERE ID_Pendaftaran = ?";
    $stmt = $conn->prepare($update_sql);
    $stmt->bind_param("si", $new_status, $pendaftaran_id);
    
    if($stmt->execute()) {
        $msg = "Status berhasil diperbarui";
        
        // If status is changed to 'Diperiksa', create new entry in Pemeriksaan
        if($new_status == 'Diperiksa') {
            $insert_sql = "INSERT INTO Pemeriksaan (ID_Pendaftaran, ID_Dokter, Waktu_Periksa) 
                          VALUES (?, ?, NOW())";
            $stmt = $conn->prepare($insert_sql);
            $stmt->bind_param("ii", $pendaftaran_id, $dokter_id);
            $stmt->execute();
        }
    } else {
        $msg = "Error updating status: " . $conn->error;
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Antrian Pasien - Poliklinik X</title>
    <style>
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
            gap: 20px;
        }

        .nav a {
            color: white;
            text-decoration: none;
            padding: 0.5rem 1rem;
        }

        .container {
            max-width: 1200px;
            margin: 20px auto;
            padding: 0 20px;
        }

        .alert {
            padding: 15px;
            margin-bottom: 20px;
            border: 1px solid transparent;
            border-radius: 4px;
        }

        .alert-success {
            color: #155724;
            background-color: #d4edda;
            border-color: #c3e6cb;
        }

        .card {
            background: white;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }

        .card-header {
            background-color: #007bff;
            color: white;
            padding: 1rem;
            border-radius: 8px 8px 0 0;
        }

        .card-body {
            padding: 1rem;
        }

        table {
            width: 100%;
            border-collapse: collapse;
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
            cursor: pointer;
            border: none;
        }

        .btn-primary {
            background-color: #007bff;
            color: white;
        }

        .btn-success {
            background-color: #28a745;
            color: white;
        }

        .btn-warning {
            background-color: #ffc107;
            color: black;
        }

        .patient-info {
            background-color: #f8f9fa;
            padding: 15px;
            border-radius: 4px;
            margin-bottom: 15px;
        }

        .tabs {
            display: flex;
            gap: 10px;
            margin-bottom: 20px;
        }

        .tab {
            padding: 10px 20px;
            background-color: #e9ecef;
            border-radius: 4px;
            cursor: pointer;
        }

        .tab.active {
            background-color: #007bff;
            color: white;
        }

        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
        }

        .modal-content {
            background-color: white;
            margin: 15% auto;
            padding: 20px;
            width: 70%;
            max-width: 500px;
            border-radius: 8px;
        }
    </style>
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
        <h1>Antrian Pasien</h1>
        
        <?php if($msg): ?>
        <div class="alert alert-success">
            <?php echo $msg; ?>
        </div>
        <?php endif; ?>

        <div class="tabs">
            <div class="tab active" onclick="showQueue('waiting')">Menunggu</div>
            <div class="tab" onclick="showQueue('examined')">Sedang Diperiksa</div>
            <div class="tab" onclick="showQueue('completed')">Selesai</div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title">Daftar Antrian Pasien</h3>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table>
                        <thead>
                            <tr>
                                <th>No. Antrian</th>
                                <th>Waktu Daftar</th>
                                <th>Nama Pasien</th>
                                <th>No. Rekam Medis</th>
                                <th>Status</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody id="queueTableBody">
                            <?php
                            $today = date('Y-m-d');
                            $sql = "SELECT p.ID_Pendaftaran, p.No_Antrian, p.Waktu_Daftar, 
                                   pas.Nama, pas.Nomor_Rekam_Medis, p.Status,
                                   rm.Tekanan_Darah, rm.Tinggi_Badan, rm.Berat_Badan, 
                                   rm.Suhu
                                   FROM Pendaftaran p
                                   JOIN Jadwal_Dokter j ON p.ID_Jadwal = j.ID_Jadwal
                                   JOIN Pasien pas ON p.ID_Pasien = pas.ID_Pasien
                                   LEFT JOIN Rekam_Medis rm ON pas.ID_Pasien = rm.ID_Pasien
                                   WHERE j.ID_Dokter = ? 
                                   AND DATE(p.Waktu_Daftar) = ?
                                   ORDER BY p.No_Antrian";
                            
                            $stmt = $conn->prepare($sql);
                            $stmt->bind_param("is", $dokter_id, $today);
                            $stmt->execute();
                            $result = $stmt->get_result();

                            while($row = $result->fetch_assoc()) {
                                echo "<tr class='queue-row' data-status='" . strtolower($row['Status']) . "'>";
                                echo "<td>" . $row['No_Antrian'] . "</td>";
                                echo "<td>" . date('H:i', strtotime($row['Waktu_Daftar'])) . "</td>";
                                echo "<td>" . $row['Nama'] . "</td>";
                                echo "<td>" . $row['Nomor_Rekam_Medis'] . "</td>";
                                echo "<td>" . $row['Status'] . "</td>";
                                echo "<td>";
                                
                                if($row['Status'] == 'Menunggu') {
                                    echo "<form method='POST' style='display:inline;'>";
                                    echo "<input type='hidden' name='pendaftaran_id' value='" . $row['ID_Pendaftaran'] . "'>";
                                    echo "<input type='hidden' name='status' value='Diperiksa'>";
                                    echo "<button type='submit' name='update_status' class='btn btn-primary'>Mulai Periksa</button>";
                                    echo "</form>";
                                } else if($row['Status'] == 'Diperiksa') {
                                    echo "<a href='examination.php?id=" . $row['ID_Pendaftaran'] . "' class='btn btn-warning'>Lanjutkan Pemeriksaan</a>";
                                }
                                
                                // View patient info button
                                echo " <button onclick='showPatientInfo(\"" . 
                                     $row['Nama'] . "\", \"" . 
                                     $row['Nomor_Rekam_Medis'] . "\", \"" .
                                     $row['Tekanan_Darah'] . "\", \"" .
                                     $row['Tinggi_Badan'] . "\", \"" .
                                     $row['Berat_Badan'] . "\", \"" .
                                     $row['Suhu'] . "\")' class='btn btn-success'>Info Pasien</button>";
                                
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

    <!-- Modal for patient info -->
    <div id="patientInfoModal" class="modal">
        <div class="modal-content">
            <h2>Informasi Pasien</h2>
            <div id="patientInfoContent"></div>
            <button onclick="closeModal()" class="btn btn-primary">Tutup</button>
        </div>
    </div>

    <script>
        function showQueue(status) {
            const rows = document.querySelectorAll('.queue-row');
            const tabs = document.querySelectorAll('.tab');
            
            // Update active tab
            tabs.forEach(tab => {
                tab.classList.remove('active');
                if(tab.textContent.toLowerCase().includes(status)) {
                    tab.classList.add('active');
                }
            });
            
            // Show/hide rows based on status
            rows.forEach(row => {
                if(status === 'waiting' && row.dataset.status === 'menunggu') {
                    row.style.display = '';
                } else if(status === 'examined' && row.dataset.status === 'diperiksa') {
                    row.style.display = '';
                } else if(status === 'completed' && row.dataset.status === 'selesai') {
                    row.style.display = '';
                } else {
                    row.style.display = 'none';
                }
            });
        }

        function showPatientInfo(nama, rekamMedis, tekananDarah, tinggiBadan, beratBadan, suhu) {
            const modal = document.getElementById('patientInfoModal');
            const content = document.getElementById('patientInfoContent');
            
            content.innerHTML = `
                <div class="patient-info">
                    <p><strong>Nama:</strong> ${nama}</p>
                    <p><strong>No. Rekam Medis:</strong> ${rekamMedis}</p>
                    <p><strong>Tekanan Darah:</strong> ${tekananDarah || 'Belum diukur'}</p>
                    <p><strong>Tinggi Badan:</strong> ${tinggiBadan || 'Belum diukur'} cm</p>
                    <p><strong>Berat Badan:</strong> ${beratBadan || 'Belum diukur'} kg</p>
                    <p><strong>Suhu Badan:</strong> ${suhu || 'Belum diukur'} °C</p>
                </div>
            `;
            
            modal.style.display = 'block';
        }

        function closeModal() {
            const modal = document.getElementById('patientInfoModal');
            modal.style.display = 'none';
        }

        // Close modal when clicking outside
        window.onclick = function(event) {
            const modal = document.getElementById('patientInfoModal');
            if (event.target == modal) {
                modal.style.display = 'none';
            }
        }

        // Initial queue display
        showQueue('waiting');

        // Auto refresh every 30 seconds
        setInterval(function() {
            location.reload();
        }, 30000);
    </script>
</body>
</html>