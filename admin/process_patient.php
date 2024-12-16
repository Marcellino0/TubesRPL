<?php
session_start(); // Memulai sesi pengguna
header('Content-Type: application/json'); // Menetapkan header sebagai JSON untuk respons

// Fungsi untuk menghubungkan ke database
function connectDB() {
    $host = 'localhost'; // Host database
    $dbname = 'PoliklinikX'; // Nama database
    $username = 'root'; // Username database
    $password = ''; // Password database

    try {
        // Menghubungkan ke database menggunakan PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Menangani error dengan melemparkan exception
        return $conn; // Mengembalikan koneksi database
    } catch(PDOException $e) {
        // Jika gagal terhubung, kirimkan pesan error dalam format JSON
        die(json_encode(['success' => false, 'message' => "Connection failed: " . $e->getMessage()]));
    }
}

// Fungsi untuk menghasilkan nomor rekam medis yang unik
function generateMedicalRecordNumber($conn,  $registration_type = 'Offline') {
    // Format: RM[Offline/Online]-YYYY- + nomor urut 4 digit
    $prefix = "RM{$registration_type}-" . date('Y') . '-';
    
    // Memeriksa nomor rekam medis terakhir
    $stmt = $conn->prepare("SELECT Nomor_Rekam_Medis FROM pasien 
                            WHERE Nomor_Rekam_Medis LIKE ? 
                            ORDER BY Nomor_Rekam_Medis DESC 
                            LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last_record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($last_record) {
        // Mengambil nomor urut terakhir dan menambahkannya
        $last_sequence = intval(substr($last_record['Nomor_Rekam_Medis'], -5));
        $new_sequence = str_pad($last_sequence + 1, 5, '0', STR_PAD_LEFT);
    } else {
        // Jika belum ada rekam medis, mulai dengan urut 00001
        $new_sequence = '00001';
    }
    
    return $prefix . $new_sequence; // Mengembalikan nomor rekam medis baru
}

try {
    $conn = connectDB(); // Membuka koneksi ke database
    $conn->beginTransaction(); // Memulai transaksi database untuk memastikan semua query berhasil

    // Memvalidasi dan menyaring input dari form
    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING); // Nama pasien
    $nik = filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_STRING); // NIK pasien
    $tanggal_lahir = filter_input(INPUT_POST, 'tanggal_lahir', FILTER_SANITIZE_STRING); // Tanggal lahir pasien
    $jenis_kelamin = filter_input(INPUT_POST, 'jenis_kelamin', FILTER_SANITIZE_STRING); // Jenis kelamin pasien
    $jadwal_id = filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_NUMBER_INT); // ID jadwal dokter
    $registration_date = filter_input(INPUT_POST, 'registration_date', FILTER_SANITIZE_STRING); // Tanggal pendaftaran

    // Validasi format NIK
    if (strlen($nik) !== 16 || !ctype_digit($nik)) {
        throw new Exception("NIK harus 16 digit angka"); // Jika NIK tidak valid
    }

    // Memeriksa apakah pasien sudah ada dalam database
    $stmt = $conn->prepare("SELECT ID_Pasien FROM pasien WHERE NIK = ?");
    $stmt->execute([$nik]);
    $existing_patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $patient_id = null; // ID pasien (null jika pasien baru)
    
    if ($existing_patient) {
        $patient_id = $existing_patient['ID_Pasien']; // Jika pasien sudah ada, ambil ID pasien
    } else {
         // Jika pasien baru, buat nomor rekam medis unik
         $nomor_rekam_medis = generateMedicalRecordNumber($conn);

         // Menyimpan data pasien baru
         $stmt = $conn->prepare("INSERT INTO pasien (NIK, Nama, Tanggal_Lahir, Jenis_Kelamin, Nomor_Rekam_Medis) VALUES (?, ?, ?, ?, ?)");
         $stmt->execute([$nik, $nama, $tanggal_lahir, $jenis_kelamin, $nomor_rekam_medis]);
         $patient_id = $conn->lastInsertId(); // Ambil ID pasien yang baru saja dimasukkan
    }

    // Mendapatkan nomor antrian pasien berdasarkan jadwal yang dipilih
    $stmt = $conn->prepare("SELECT COUNT(*) as current_queue 
                           FROM pendaftaran 
                           WHERE ID_Jadwal = ? AND DATE(Waktu_Daftar) = ?");
    $stmt->execute([$jadwal_id, $registration_date]);
    $queue_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $queue_number = $queue_result['current_queue'] + 1; // Nomor antrian adalah jumlah pasien yang sudah mendaftar + 1

    // Memeriksa kuota offline dan mengurangi kuota
    $stmt = $conn->prepare("UPDATE jadwal_dokter 
                            SET Kuota_Offline = Kuota_Offline - 1 
                            WHERE ID_Jadwal = ? AND Kuota_Offline > 0");
    $update_quota = $stmt->execute([$jadwal_id]);
    
    if (!$update_quota || $stmt->rowCount() == 0) {
        throw new Exception("Kuota offline sudah habis"); // Jika kuota offline sudah habis
    }

    // Memeriksa total kuota (offline + online)
    $stmt = $conn->prepare("SELECT Kuota_Online + Kuota_Offline as total_quota 
                           FROM jadwal_dokter 
                           WHERE ID_Jadwal = ?");
    $stmt->execute([$jadwal_id]);
    $quota_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($queue_number > $quota_result['total_quota']) {
        throw new Exception("Kuota jadwal sudah penuh"); // Jika antrian lebih besar dari total kuota
    }

    // Membuat kode registrasi unik
    $registration_code = 'REG' . date('Ymd') . str_pad($queue_number, 3, '0', STR_PAD_LEFT);

    // Menyimpan data pendaftaran pasien
    $stmt = $conn->prepare("INSERT INTO pendaftaran (ID_Pasien, ID_Jadwal, Waktu_Daftar, No_Antrian, Status, Bukti_Reservasi) 
                           VALUES (?, ?, ?, ?, 'Menunggu', ?)");
    $stmt->execute([
        $patient_id, // ID pasien
        $jadwal_id, // ID jadwal dokter
        $registration_date . ' ' . date('H:i:s'), // Waktu pendaftaran (tanggal dan jam saat ini)
        $queue_number, // Nomor antrian
        $registration_code // Kode registrasi
    ]);

    $conn->commit(); // Menyimpan perubahan dalam transaksi
    echo json_encode([ // Mengirimkan respons JSON sukses
        'success' => true,
        'message' => "Pendaftaran berhasil dengan nomor registrasi: " . $registration_code
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack(); // Membatalkan perubahan jika ada error
    }
    echo json_encode([ // Mengirimkan respons JSON jika terjadi error
        'success' => false,
        'message' => $e->getMessage() // Pesan error yang diterima
    ]);
}
?>
