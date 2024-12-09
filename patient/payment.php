<?php
session_start();
require_once('../config/db_connection.php');

if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'pasien') {
    header("Location: index.php");
    exit();
}

// Get pending payments
$stmt = $conn->prepare("
    SELECT 
        p.ID_Pembayaran,
        p.ID_Pendaftaran,
        p.Jumlah,
        p.Status,
        p.Tanggal,
        d.Nama as nama_dokter,
        d.Spesialis
    FROM Pembayaran p
    JOIN Pendaftaran pend ON p.ID_Pendaftaran = pend.ID_Pendaftaran
    JOIN Jadwal_Dokter jd ON pend.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    WHERE pend.ID_Pasien = ?
    ORDER BY p.Tanggal DESC
");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$paymentHistory = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$pendingPayments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get payment methods
$paymentMethods = $conn->query("SELECT * FROM Metode_Pembayaran WHERE Status = 'Aktif'")->fetch_all(MYSQLI_ASSOC);

// Process payment submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $paymentId = $_POST['payment_id'];
    $methodId = $_POST['payment_method'];
    $referenceNumber = $_POST['reference_number'];
    
    // Handle file upload
    $uploadDir = '../uploads/bukti_pembayaran/';
    $fileName = '';
    
    if (isset($_FILES['payment_proof']) && $_FILES['payment_proof']['error'] === 0) {
        $fileName = time() . '_' . $_FILES['payment_proof']['name'];
        move_uploaded_file($_FILES['payment_proof']['tmp_name'], $uploadDir . $fileName);
    }
    
    // Begin transaction
    $conn->begin_transaction();
    
    try {
        // Insert payment detail
        $stmt = $conn->prepare("
            INSERT INTO Detail_Pembayaran (
                ID_Pembayaran, 
                ID_Metode, 
                Nomor_Referensi, 
                Bukti_Pembayaran
            ) VALUES (?, ?, ?, ?)
        ");
        $stmt->bind_param("iiss", $paymentId, $methodId, $referenceNumber, $fileName);
        $stmt->execute();
        
        // Update payment status
        $stmt = $conn->prepare("
            UPDATE Pembayaran 
            SET Status = 'Menunggu Verifikasi'
            WHERE ID_Pembayaran = ?
        ");
        $stmt->bind_param("i", $paymentId);
        $stmt->execute();
        
        $conn->commit();
        $_SESSION['success_message'] = "Pembayaran berhasil disubmit dan menunggu verifikasi";
        header("Location: payment_history.php");
        exit();
    } catch (Exception $e) {
        $conn->rollback();
        $_SESSION['error_message'] = "Terjadi kesalahan dalam proses pembayaran";
    }
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pembayaran - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
<div class="container mx-auto px-4 py-8">
    <h1 class="text-2xl font-bold mb-6">Riwayat Pembayaran</h1>

    <?php if (empty($paymentHistory)): ?>
        <div class="bg-white rounded-lg shadow p-6">
            <p class="text-gray-500">Tidak ada riwayat pembayaran</p>
        </div>
    <?php else: ?>
        <table class="min-w-full bg-white rounded-lg shadow">
            <thead>
                <tr class="bg-gray-100 text-gray-600 uppercase text-sm leading-normal">
                    <th class="py-3 px-6 text-left">ID Pembayaran</th>
                    <th class="py-3 px-6 text-left">Dokter</th>
                    <th class="py-3 px-6 text-left">Spesialis</th>
                    <th class="py-3 px-6 text-left">Tanggal</th>
                    <th class="py-3 px-6 text-left">Jumlah</th>
                    <th class="py-3 px-6 text-left">Status</th>
                </tr>
            </thead>
            <tbody class="text-gray-600 text-sm">
                <?php foreach ($paymentHistory as $payment): ?>
                    <tr class="border-b border-gray-200 hover:bg-gray-100">
                        <td class="py-4 px-6">#<?php echo $payment['ID_Pembayaran']; ?></td>
                        <td class="py-4 px-6"><?php echo htmlspecialchars($payment['nama_dokter']); ?></td>
                        <td class="py-4 px-6"><?php echo htmlspecialchars($payment['Spesialis']); ?></td>
                        <td class="py-4 px-6"><?php echo date('d F Y', strtotime($payment['Tanggal'])); ?></td>
                        <td class="py-4 px-6">Rp <?php echo number_format($payment['Jumlah'], 0, ',', '.'); ?></td>
                        <td class="py-4 px-6">
                            <span class="px-2 py-1 rounded-full text-sm 
                                <?php echo $payment['Status'] === 'Lunas' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800'; ?>">
                                <?php echo $payment['Status']; ?>
                            </span>
                        </td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    <?php endif; ?>
</div>

    <script>
        // Add client-side validation if needed
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const fileInput = this.querySelector('input[type="file"]');
                if (fileInput.files[0] && fileInput.files[0].size > 5 * 1024 * 1024) {
                    e.preventDefault();
                    alert('Ukuran file tidak boleh lebih dari 5MB');
                }
            });
        });
    </script>
</body>
</html>