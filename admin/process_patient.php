<?php
session_start();
header('Content-Type: application/json');


function connectDB() {
    $host = 'localhost';
    $dbname = 'PoliklinikX';
    $username = 'root';
    $password = '';

    try {
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        return $conn;
    } catch(PDOException $e) {
        die(json_encode(['success' => false, 'message' => "Connection failed: " . $e->getMessage()]));
    }
}


function generateMedicalRecordNumber($conn,  $registration_type = 'Offline') {
  
    $prefix = "RM{$registration_type}-" . date('Y') . '-';
    

    $stmt = $conn->prepare("SELECT Nomor_Rekam_Medis FROM pasien 
                            WHERE Nomor_Rekam_Medis LIKE ? 
                            ORDER BY Nomor_Rekam_Medis DESC 
                            LIMIT 1");
    $stmt->execute([$prefix . '%']);
    $last_record = $stmt->fetch(PDO::FETCH_ASSOC);
    
    if ($last_record) {
     
        $last_sequence = intval(substr($last_record['Nomor_Rekam_Medis'], -5));
        $new_sequence = str_pad($last_sequence + 1, 5, '0', STR_PAD_LEFT);
    } else {
   
        $new_sequence = '00001';
    }
    
    return $prefix . $new_sequence;

    
}

try {
    $conn = connectDB();
    $conn->beginTransaction();


    $nama = filter_input(INPUT_POST, 'nama', FILTER_SANITIZE_STRING);
    $nik = filter_input(INPUT_POST, 'nik', FILTER_SANITIZE_STRING);
    $tanggal_lahir = filter_input(INPUT_POST, 'tanggal_lahir', FILTER_SANITIZE_STRING);
    $jenis_kelamin = filter_input(INPUT_POST, 'jenis_kelamin', FILTER_SANITIZE_STRING);
    $jadwal_id = filter_input(INPUT_POST, 'schedule', FILTER_SANITIZE_NUMBER_INT);
    $registration_date = filter_input(INPUT_POST, 'registration_date', FILTER_SANITIZE_STRING);


    if (strlen($nik) !== 16 || !ctype_digit($nik)) {
        throw new Exception("NIK harus 16 digit angka");
    }

  
    $stmt = $conn->prepare("SELECT ID_Pasien FROM pasien WHERE NIK = ?");
    $stmt->execute([$nik]);
    $existing_patient = $stmt->fetch(PDO::FETCH_ASSOC);
    
    $patient_id = null;
    
    if ($existing_patient) {
        $patient_id = $existing_patient['ID_Pasien'];
    } else {
        
         $nomor_rekam_medis = generateMedicalRecordNumber($conn);

         
         $stmt = $conn->prepare("INSERT INTO pasien (NIK, Nama, Tanggal_Lahir, Jenis_Kelamin, Nomor_Rekam_Medis) VALUES (?, ?, ?, ?, ?)");
         $stmt->execute([$nik, $nama, $tanggal_lahir, $jenis_kelamin, $nomor_rekam_medis]);
         $patient_id = $conn->lastInsertId();
    }


    $stmt = $conn->prepare("SELECT COUNT(*) as current_queue 
                           FROM pendaftaran 
                           WHERE ID_Jadwal = ? AND DATE(Waktu_Daftar) = ?");
    $stmt->execute([$jadwal_id, $registration_date]);
    $queue_result = $stmt->fetch(PDO::FETCH_ASSOC);
    $queue_number = $queue_result['current_queue'] + 1;

  
    $stmt = $conn->prepare("UPDATE jadwal_dokter 
                            SET Kuota_Offline = Kuota_Offline - 1 
                            WHERE ID_Jadwal = ? AND Kuota_Offline > 0");
    $update_quota = $stmt->execute([$jadwal_id]);
    
    if (!$update_quota || $stmt->rowCount() == 0) {
        throw new Exception("Kuota offline sudah habis");
    }

 
    $stmt = $conn->prepare("SELECT Kuota_Online + Kuota_Offline as total_quota 
                           FROM jadwal_dokter 
                           WHERE ID_Jadwal = ?");
    $stmt->execute([$jadwal_id]);
    $quota_result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($queue_number > $quota_result['total_quota']) {
        throw new Exception("Kuota jadwal sudah penuh");
    }

   
    $registration_code = 'REG' . date('Ymd') . str_pad($queue_number, 3, '0', STR_PAD_LEFT);

 
    $stmt = $conn->prepare("INSERT INTO pendaftaran (ID_Pasien, ID_Jadwal, Waktu_Daftar, No_Antrian, Status, Bukti_Reservasi) 
                           VALUES (?, ?, ?, ?, 'Menunggu', ?)");
    $stmt->execute([
        $patient_id,
        $jadwal_id,
        $registration_date . ' ' . date('H:i:s'),
        $queue_number,
        $registration_code
    ]);

    $conn->commit();
    echo json_encode([
        'success' => true,
        'message' => "Pendaftaran berhasil dengan nomor registrasi: " . $registration_code
    ]);

} catch (Exception $e) {
    if (isset($conn)) {
        $conn->rollBack();
    }
    echo json_encode([
        'success' => false,
        'message' => $e->getMessage()
    ]);
}
?>