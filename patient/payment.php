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
        d.Spesialis,
        mp.ID_Metode,
        mp.Nama_Metode
    FROM Pembayaran p
    JOIN Pendaftaran pend ON p.ID_Pendaftaran = pend.ID_Pendaftaran
    JOIN Jadwal_Dokter jd ON pend.ID_Jadwal = jd.ID_Jadwal
    JOIN Dokter d ON jd.ID_Dokter = d.ID_Dokter
    LEFT JOIN Detail_Pembayaran dp ON p.ID_Pembayaran = dp.ID_Pembayaran
    LEFT JOIN Metode_Pembayaran mp ON dp.ID_Metode = mp.ID_Metode
    WHERE pend.ID_Pasien = ? AND p.Status = 'Pending'
    ORDER BY p.Tanggal DESC
");
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
        <h1 class="text-2xl font-bold mb-6">Pembayaran Pending</h1>
        
        <?php if (empty($pendingPayments)): ?>
            <div class="bg-white rounded-lg shadow p-6">
                <p class="text-gray-500">Tidak ada pembayaran pending</p>
            </div>
        <?php else: ?>
            <?php foreach ($pendingPayments as $payment): ?>
                <div class="bg-white rounded-lg shadow p-6 mb-6">
                    <div class="flex justify-between items-start mb-4">
                        <div>
                            <h2 class="text-xl font-semibold">
                                Pembayaran #<?php echo $payment['ID_Pembayaran']; ?>
                            </h2>
                            <p class="text-gray-600">
                                Dr. <?php echo htmlspecialchars($payment['nama_dokter']); ?> 
                                (<?php echo htmlspecialchars($payment['Spesialis']); ?>)
                            </p>
                            <p class="text-gray-500 text-sm">
                                <?php echo date('d F Y', strtotime($payment['Tanggal'])); ?>
                            </p>
                        </div>
                        <div class="text-right">
                            <p class="text-lg font-bold">
                                Rp <?php echo number_format($payment['Jumlah'], 0, ',', '.'); ?>
                            </p>
                            <span class="px-2 py-1 rounded-full text-sm 
                                <?php echo $payment['Status'] === 'Pending' ? 'bg-yellow-100 text-yellow-800' : 'bg-blue-100 text-blue-800'; ?>">
                                <?php echo $payment['Status']; ?>
                            </span>
                        </div>
                    </div>

                    <form action="" method="POST" enctype="multipart/form-data" class="mt-6">
                        <input type="hidden" name="payment_id" value="<?php echo $payment['ID_Pembayaran']; ?>">
                        
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Metode Pembayaran
                                </label>
                                <select name="payment_method" required 
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500">
                                    <option value="">Pilih metode pembayaran</option>
                                    <?php foreach ($paymentMethods as $method): ?>
                                        <option value="<?php echo $method['ID_Metode']; ?>">
                                            <?php echo htmlspecialchars($method['Nama_Metode']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            
                            <div>
                                <label class="block text-sm font-medium text-gray-700 mb-2">
                                    Nomor Referensi
                                </label>
                                <input type="text" name="reference_number" required
                                    class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                    placeholder="Masukkan nomor referensi pembayaran">
                            </div>
                        </div>

                        <div class="mt-6">
                            <label class="block text-sm font-medium text-gray-700 mb-2">
                                Bukti Pembayaran
                            </label>
                            <input type="file" name="payment_proof" required
                                class="w-full px-3 py-2 border border-gray-300 rounded-md focus:outline-none focus:ring-blue-500 focus:border-blue-500"
                                accept="image/*,.pdf">
                            <p class="mt-1 text-sm text-gray-500">
                                Format yang diterima: JPG, PNG, PDF. Maksimal 5MB
                            </p>
                        </div>

                        <div class="mt-6">
                            <button type="submit" class="w-full bg-blue-600 text-white px-4 py-2 rounded-md hover:bg-blue-700 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:ring-offset-2">
                                Submit Pembayaran
                            </button>
                        </div>
                    </form>
                </div>
            <?php endforeach; ?>
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