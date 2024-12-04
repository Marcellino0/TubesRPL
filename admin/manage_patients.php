<?php
session_start();

// Database connection configuration
function connectDB() {
    $host = 'localhost';
    $dbname = 'PoliklinikX';
    $username = 'root';  // Replace with your MySQL username
    $password = '';      // Replace with your MySQL password

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

// Fetch specializations
try {
    $spec_query = $conn->query("SELECT DISTINCT Spesialis FROM dokter");
    $specializations = $spec_query->fetchAll(PDO::FETCH_COLUMN);
} catch (PDOException $e) {
    $error_message = "Error fetching specializations: " . $e->getMessage();
}

// Display success/error messages if they exist
if (isset($_SESSION['success_message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['success_message']) . "');</script>";
    unset($_SESSION['success_message']);
}
if (isset($_SESSION['error_message'])) {
    echo "<script>alert('" . htmlspecialchars($_SESSION['error_message']) . "');</script>";
    unset($_SESSION['error_message']);
}
?>

<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Pasien - Poliklinik X</title>
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
                    <a href="admin_dashboard.php" class="flex items-center space-x-3 p-3 rounded hover:bg-blue-700">
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
                    <a href="manage_patients.php" class="flex items-center space-x-3 p-3 rounded bg-blue-900">
                        <i class="fas fa-users"></i>
                        <span>Kelola Pasien</span>
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
            <!-- Header -->
            <div class="flex justify-between items-center mb-6">
                <h1 class="text-2xl font-bold">Kelola Pasien</h1>
                <button onclick="openModal()" class="bg-blue-500 hover:bg-blue-600 text-white px-4 py-2 rounded-lg flex items-center space-x-2">
                    <i class="fas fa-plus"></i>
                    <span>Tambah Pasien</span>
                </button>
            </div>

            <!-- Patient List -->
            <div class="bg-white rounded-lg shadow">
                <div class="p-6">
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                        <thead>
    <tr>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">NIK</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nama</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">No. Rekam Medis</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal Lahir</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Jenis Kelamin</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
        <th class="px-6 py-3 bg-gray-50 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Aksi</th>
    </tr>
</thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                <?php
                                try {
                                    $stmt = $conn->query("SELECT * FROM Pasien ORDER BY Nama");
                                    while($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                        echo "<tr>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-900'>" . htmlspecialchars($row['NIK']) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900'>" . htmlspecialchars($row['Nama']) . "</td>";
        // Add the new column for medical record number
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Nomor_Rekam_Medis']) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Tanggal_Lahir']) . "</td>";
        echo "<td class='px-6 py-4 whitespace-nowrap text-sm text-gray-500'>" . htmlspecialchars($row['Jenis_Kelamin']) . "</td>";
                                        
                                        echo "<td class='px-6 py-4 whitespace-nowrap'>
                                                <span class='px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800'>
                                                    Aktif
                                                </span>
                                              </td>";
                                        echo "<td class='px-6 py-4 whitespace-nowrap text-sm font-medium'>
                                                <button onclick='editPatient(\"" . $row['NIK'] . "\")' class='text-blue-600 hover:text-blue-900 mr-3'>
                                                    <i class='fas fa-edit'></i>
                                                </button>
                                                <button onclick='deletePatient(\"" . $row['NIK'] . "\")' class='text-red-600 hover:text-red-900'>
                                                    <i class='fas fa-trash'></i>
                                                </button>
                                              </td>";
                                        echo "</tr>";
                                    }
                                } catch(PDOException $e) {
                                    echo "<tr><td colspan='6' class='text-red-500'>Error: " . $e->getMessage() . "</td></tr>";
                                }
                                ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>

    <!-- Patient Modal -->
    <div id="patientModal" class="fixed inset-0 z-50 overflow-y-auto hidden">
        <div class="flex items-center justify-center min-h-screen pt-4 px-4 pb-20 text-center sm:block sm:p-0">
            <div class="fixed inset-0 transition-opacity" onclick="closeModal()">
                <div class="absolute inset-0 bg-gray-500 opacity-75"></div>
            </div>

            <div class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-lg sm:w-full">
                <div class="p-6">
                    <h3 class="text-lg font-medium text-gray-900 mb-4">Tambah Pasien Baru</h3>

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
                            <button type="button" onclick="closeModal()"
                                class="px-4 py-2 border rounded-md text-gray-600 hover:bg-gray-100">
                                Batal
                            </button>
                            <button type="submit"
                                class="px-4 py-2 bg-blue-500 text-white rounded-md hover:bg-blue-600">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    
    <script>
    function openModal() {
        document.getElementById('patientModal').classList.remove('hidden');
    }

    function closeModal() {
        document.getElementById('patientModal').classList.add('hidden');
    }

    function editPatient(nik) {
        // Implement edit functionality
        alert('Edit patient with NIK: ' + nik);
    }

    function deletePatient(nik) {
        if(confirm('Are you sure you want to delete this patient?')) {
            // Implement delete functionality
            alert('Delete patient with NIK: ' + nik);
        }
    }

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
            location.reload();
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