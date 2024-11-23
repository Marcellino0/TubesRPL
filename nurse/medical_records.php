<?php
session_start();
require_once('../config/db_connection.php');

// Ambil seluruh data rekam medis
$sql = "SELECT rm.ID_RekamMedis, rm.ID_Pasien, rm.Tekanan_Darah, rm.Tinggi_Badan, rm.Berat_Badan, rm.Suhu, 
        rm.Riwayat_Penyakit, rm.Tanggal, p.Nama AS Nama_Pasien
        FROM rekam_medis rm
        JOIN pasien p ON rm.ID_Pasien = p.ID_Pasien
        ORDER BY rm.Tanggal DESC";
$result = $conn->query($sql);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rekam Medis</title>
    <link rel="stylesheet" href="styles.css"> <!-- Tambahkan jika ada file CSS -->
</head>
<body>
    <h1>Daftar Rekam Medis</h1>
    <?php if (isset($_SESSION['success'])): ?>
        <p class="success"><?php echo $_SESSION['success']; unset($_SESSION['success']); ?></p>
    <?php endif; ?>
    <?php if (isset($_SESSION['error'])): ?>
        <p class="error"><?php echo $_SESSION['error']; unset($_SESSION['error']); ?></p>
    <?php endif; ?>

    <table border="1" cellpadding="10" cellspacing="0">
        <thead>
            <tr>
                <th>ID Rekam Medis</th>
                <th>ID Pasien</th>
                <th>Nama Pasien</th>
                <th>Tekanan Darah</th>
                <th>Tinggi Badan (cm)</th>
                <th>Berat Badan (kg)</th>
                <th>Suhu (°C)</th>
                <th>Riwayat Penyakit</th>
                <th>Tanggal</th>
                <th>Dokumen</th>
            </tr>
        </thead>
        <tbody>
            <?php if ($result->num_rows > 0): ?>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?php echo $row['ID_RekamMedis']; ?></td>
                        <td><?php echo $row['ID_Pasien']; ?></td>
                        <td><?php echo htmlspecialchars($row['Nama_Pasien']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tekanan_Darah']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tinggi_Badan']); ?></td>
                        <td><?php echo htmlspecialchars($row['Berat_Badan']); ?></td>
                        <td><?php echo htmlspecialchars($row['Suhu']); ?></td>
                        <td><?php echo htmlspecialchars($row['Riwayat_Penyakit']); ?></td>
                        <td><?php echo htmlspecialchars($row['Tanggal']); ?></td>
                        <td>
                            <?php
                            // Ambil dokumen terkait untuk ID_Pasien
                            $id_pasien = $row['ID_Pasien'];
                            $sql_docs = "SELECT Nama_File, Jenis_Dokumen, Path_File 
                                         FROM dokumen_medis 
                                         WHERE ID_Pasien = ?";
                            $stmt_docs = $conn->prepare($sql_docs);
                            $stmt_docs->bind_param("i", $id_pasien);
                            $stmt_docs->execute();
                            $result_docs = $stmt_docs->get_result();

                            if ($result_docs->num_rows > 0):
                            ?>
                                <ul>
                                    <?php while ($doc = $result_docs->fetch_assoc()): ?>
                                        <li>
                                            <strong><?php echo htmlspecialchars($doc['Jenis_Dokumen']); ?>:</strong>
                                            <a href="<?php echo htmlspecialchars($doc['Path_File']); ?>" target="_blank">
                                                <?php echo htmlspecialchars($doc['Nama_File']); ?>
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            <?php else: ?>
                                Tidak ada dokumen
                            <?php endif; ?>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="10">Tidak ada data rekam medis.</td>
                </tr>
            <?php endif; ?>
        </tbody>
    </table>
</body>
</html>
