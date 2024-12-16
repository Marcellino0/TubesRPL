<?php
session_start(); // Memulai sesi PHP agar variabel sesi dapat digunakan di seluruh aplikasi

// Fungsi untuk konfigurasi dan koneksi ke database menggunakan PDO
function connectDB() {
    $host = 'localhost'; // Host database
    $dbname = 'PoliklinikX'; // Nama database
    $username = 'root'; // Username untuk koneksi database (ganti sesuai dengan pengaturan MySQL Anda)
    $password = ''; // Password untuk koneksi database (ganti sesuai dengan pengaturan MySQL Anda)

    try {
        // Membuat koneksi database dengan PDO
        $conn = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION); // Mengatur mode error ke exception
        return $conn; // Mengembalikan objek koneksi
    } catch (PDOException $e) {
        // Jika koneksi gagal, tampilkan pesan error
        die("Connection failed: " . $e->getMessage());
    }
}

// Memeriksa apakah pengguna telah login sebagai admin
if (!isset($_SESSION['user_type']) || $_SESSION['user_type'] !== 'admin') {
    // Jika bukan admin, arahkan ke halaman login
    header("Location: login.php");
    exit();
}

// Mendapatkan daftar spesialisasi dokter untuk dropdown
try {
    $conn = connectDB(); // Membuat koneksi ke database
    $spec_query = $conn->query("SELECT DISTINCT Spesialis FROM dokter"); // Query untuk mengambil data spesialisasi unik
    $specializations = $spec_query->fetchAll(PDO::FETCH_COLUMN); // Mengambil hasil query sebagai array
} catch (PDOException $e) {
    // Jika terjadi error saat mengambil data spesialisasi, simpan pesan error
    $error_message = "Error fetching specializations: " . $e->getMessage();
}
?>


<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Pasien Baru - Poliklinik X</title>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@2.2.19/dist/tailwind.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-2xl mx-auto bg-white shadow-md rounded-lg overflow-hidden">
            <div class="p-6">
                <h3 class="text-xl font-semibold text-gray-900 mb-6">Tambah Pasien Baru</h3>

                <form id="patientForm" class="space-y-4">
                    <!-- Patient Information Section -->
                    <div>
                        <h4 class="text-md font-semibold mb-2">Informasi Pasien</h4>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Nama Lengkap</label>
                                <input type="text" name="nama" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">NIK</label>
                                <input type="text" name="nik" required maxlength="16"
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                            </div>
                        </div>
                        <div class="grid grid-cols-2 gap-4 mt-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Lahir</label>
                                <input type="date" name="tanggal_lahir" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Jenis Kelamin</label>
                                <select name="jenis_kelamin" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                    <option value="Laki-laki">Laki-laki</option>
                                    <option value="Perempuan">Perempuan</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Appointment Registration Section -->
                    <div>
                        <h4 class="text-md font-semibold mb-2">Pendaftaran Pemeriksaan</h4>
                        <div class="mb-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Spesialis</label>
                            <select id="specialization" class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                <option value="">Pilih Spesialis</option>
                                <?php foreach ($specializations as $spec): ?>
                                    <option value="<?php echo htmlspecialchars($spec); ?>">
                                        <?php echo htmlspecialchars($spec); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Dokter</label>
                                <select name="doctor" id="doctor" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                    <option value="">Pilih Spesialis Terlebih Dahulu</option>
                                </select>
                            </div>
                            <div>
                                <label class="block text-gray-700 text-sm font-bold mb-2">Tanggal Pendaftaran</label>
                                <input type="date" name="registration_date" id="registration_date" required
                                    class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                            </div>
                        </div>

                        <div class="mt-4">
                            <label class="block text-gray-700 text-sm font-bold mb-2">Pilih Jadwal</label>
                            <select name="schedule" id="schedule" required
                                class="w-full px-3 py-2 border rounded-md focus:outline-none focus:ring-blue-500">
                                <option value="">Pilih Jadwal</option>
                            </select>
                        </div>
                    </div>

                    <div class="mt-6 flex justify-end space-x-3">
                        <a href="manage_patients.php" 
                            class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                            Batal
                        </a>
                        <button type="submit"
                            class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                            Simpan
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    // Function to handle specialization change
    document.getElementById('specialization').addEventListener('change', function() {
        const spesialis = this.value;
        const doctorSelect = document.getElementById('doctor');
        
        // Clear existing options
        doctorSelect.innerHTML = '<option value="">Pilih Dokter</option>';
        document.getElementById('schedule').innerHTML = '<option value="">Pilih Jadwal</option>';
        
        if (!spesialis) return;
        
        // Fetch doctors for selected specialization
        fetch(`get_doctors.php?spesialis=${encodeURIComponent(spesialis)}`)
            .then(response => response.json())
            .then(doctors => {
                doctors.forEach(doctor => {
                    const option = document.createElement('option');
                    option.value = doctor.ID_Dokter;
                    option.textContent = doctor.Nama;
                    doctorSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching doctors:', error));
    });

    // Function to handle doctor and date selection
    function updateSchedules() {
        const doctorId = document.getElementById('doctor').value;
        const registrationDate = document.getElementById('registration_date').value;
        const scheduleSelect = document.getElementById('schedule');
        
        if (!doctorId || !registrationDate) return;
        
        // Clear existing options
        scheduleSelect.innerHTML = '<option value="">Memuat jadwal...</option>';
        
        // Fetch schedules for selected doctor
        fetch(`get_schedule.php?doctor_id=${encodeURIComponent(doctorId)}&registration_day=${encodeURIComponent(registrationDate)}`)
            .then(response => response.json())
            .then(schedules => {
                scheduleSelect.innerHTML = '<option value="">Pilih Jadwal</option>';
                
                schedules.forEach(schedule => {
                    const option = document.createElement('option');
                    option.value = schedule.ID_Jadwal;
                    
                    // Calculate available quotas
                    const isToday = registrationDate === new Date().toISOString().split('T')[0];
                    const usedQuota = isToday ? schedule.used_quota_today : schedule.used_quota_tomorrow;
                    
                    // Calculate available slots for both online and offline
                    const availableOnline = schedule.Kuota_Online - usedQuota;
                    const availableOffline = schedule.Kuota_Offline - usedQuota;
                    const totalAvailable = availableOnline + availableOffline;
                    
                    // Format the schedule display
                    const scheduleText = `${schedule.Hari}: ${schedule.Jam_Mulai} - ${schedule.Jam_Selesai} 
                        ( Offline: ${schedule.Kuota_Offline})`;
                    option.textContent = scheduleText;
                    
                    // Disable option if no quota available
                    if (totalAvailable <= 0) {
                        option.disabled = true;
                        option.textContent += ' - PENUH';
                    }
                    
                    scheduleSelect.appendChild(option);
                });
            })
            .catch(error => console.error('Error fetching schedules:', error));
    }

    // Add event listeners for doctor and date selection
    document.getElementById('doctor').addEventListener('change', updateSchedules);
    document.getElementById('registration_date').addEventListener('change', updateSchedules);

    // Set minimum date for registration to today
    const registrationDateInput = document.getElementById('registration_date');
    const today = new Date().toISOString().split('T')[0];
    registrationDateInput.min = today;
    registrationDateInput.value = today;

    document.getElementById('patientForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        // Validate NIK
        const nik = formData.get('nik');
        if (nik.length !== 16 || !/^\d+$/.test(nik)) {
            alert('NIK harus 16 digit angka');
            return;
        }
        
        // Validate required fields
        const requiredFields = ['nama', 'nik', 'tanggal_lahir', 'jenis_kelamin', 'doctor', 'schedule'];
        for (const field of requiredFields) {
            if (!formData.get(field)) {
                alert('Semua field harus diisi');
                return;
            }
        }
        
        // Submit form
        fetch('process_patient.php', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                alert(data.message);
                window.location.href = 'manage_patients.php';
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(error => {
            console.error('Error:', error);
            alert('Terjadi kesalahan saat menyimpan data.');
        });
    });
    </script>
</body>
</html>

<?php
$conn = null; // Close connection
?>