<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Ambil data dari session
$user_name = isset($_SESSION['pendamping']['Nama_pendamping']) ? $_SESSION['pendamping']['Nama_pendamping'] : 'User';
$pegid = $_SESSION['pendamping']['PEGID'];

// Query untuk mendapatkan data pendamping dan lingkup binaan
$sql = "SELECT p.*
        FROM pendamping p 
        WHERE p.PEGID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pegid);
$stmt->execute();
$result = $stmt->get_result();
$pendamping = $result->fetch_assoc();

// Query untuk mendapatkan total lembaga binaan tanpa batasan jenjang
$sql_count = "SELECT COUNT(DISTINCT lb.nsm) as total_binaan 
              FROM lembaga_binaan lb
              WHERE lb.PEGID = ?";

$stmt_count = $conn->prepare($sql_count);
$stmt_count->bind_param("i", $pegid);
$stmt_count->execute();
$count_data = $stmt_count->get_result()->fetch_assoc();

// Query untuk mendapatkan daftar lembaga binaan
$sql_lembaga = "SELECT l.nama_madrasah, l.Jenjang, l.Alamat, l.Kabupaten, l.Kecamatan 
                FROM lembaga_binaan lb 
                JOIN lembaga l ON lb.nsm = l.nsm 
                WHERE lb.PEGID = ?
                ORDER BY l.nama_madrasah";

$stmt_lembaga = $conn->prepare($sql_lembaga);
$stmt_lembaga->bind_param("i", $pegid);
$stmt_lembaga->execute();
$result_lembaga = $stmt_lembaga->get_result();

// Debug: Tambahkan informasi untuk membantu diagnosis
$debug_info = [
    'PEGID' => $pegid,
    'Total Binaan' => $count_data['total_binaan'],
    'Lingkup Binaan' => $pendamping['Lingkup_binaan']
];

// Tutup statement
$stmt->close();
$stmt_count->close();
$stmt_lembaga->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profile</title>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>
<body>
    <div class="flex flex-col md:flex-row">

    <!-- Hamburger Menu -->
    <div class="md:hidden flex justify-between items-center bg-gradient-to-r from-green-600 to-green-800 text-white p-4 shadow-lg">
            <h1 class="text-lg font-semibold"><?php echo $user_name; ?></h1>
            <button onclick="toggleMenu()" class="focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-full md:w-64 bg-gradient-to-b from-green-800 to-green-600 text-white min-h-screen md:block shadow-lg">
            <!-- Sidebar content -->
            <div class="text-center mt-10">
                <h1 class="text-xl font-bold tracking-wide"><?php echo $user_name; ?></h1>
            </div>
            <nav class="mt-10">
                <!-- Sidebar menu items -->
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="beranda.php">
                    <i class="fas fa-home mr-3"></i> Beranda
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="data_binaan.php">
                    <i class="fas fa-database mr-3"></i> Data Binaan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="detail_kunjungan.php">
                    <i class="fas fa-users mr-3"></i> Detail Kunjungan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="rekap_kinerja.php">
                    <i class="fas fa-file-alt mr-3"></i> Rekap Kinerja
                </a>
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="Profile.php">
                    <i class="fas fa-user mr-3"></i> Profile
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="#" onclick="toggleLogoutModal()">
                    <i class="fas fa-sign-out-alt mr-3"></i> Keluar
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 md:p-10">
            <div class="bg-white rounded-lg shadow-md p-6 mb-6">
                <h1 class="text-3xl font-bold text-gray-800 text-center mb-2"><?php echo $pendamping['Nama_pendamping']; ?></h1>
                <p class="text-gray-600 text-center mb-6">PEGID: <?php echo $pendamping['PEGID']; ?></p>
                
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-gray-700 mb-2">Tempat Lahir:</label>
                        <input type="text" value="<?php echo htmlspecialchars($pendamping['Tmp_Lahir']); ?>" disabled class="w-full bg-gray-200 p-2 rounded-lg" />
                    </div>
                    <div>
                        <label class="block text-gray-700 mb-2">Tanggal Lahir:</label>
                        <input type="text" value="<?php echo date('d-m-Y', strtotime($pendamping['Tgl_Lahir'])); ?>" disabled class="w-full bg-gray-200 p-2 rounded-lg" />
                    </div>
                    <div class="col-span-2">
                        <label class="block text-gray-700 mb-2">Lingkup Binaan:</label>
                        <div class="bg-gray-200 p-3 rounded-lg">
                            <span class="inline-block px-4 py-2 bg-green-500 text-white rounded-full">
                                <?php echo htmlspecialchars($pendamping['Lingkup_binaan']); ?>
                            </span>
                            <span class="ml-3 text-gray-700">
                                Total Lembaga Binaan: <?php echo $count_data['total_binaan']; ?>
                 </span>
                        </div>
                    </div>
                </div>

                <div class="mt-4">
                    <label class="block text-gray-700 mb-2">Password Saat Ini:</label>
                    <div class="flex items-center">
                        <input type="password" id="password" value="<?php echo $pendamping['password']; ?>" disabled class="w-full bg-gray-200 p-2 rounded-lg" />
                        <button onclick="togglePasswordVisibility()" class="text-gray-500 hover:text-gray-700 transition-colors ml-2">
                            <i class="fas fa-eye text-xl"></i>
                        </button>
                    </div>
                </div>
                <button class="mt-4 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-blue-700" onclick="toggleModal('editModal')">
                    Ubah Password
                </button>
            </div>

            <!-- Modal untuk edit password -->
            <div id="editModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-edit mr-2 text-indigo-600"></i>
                                Ubah Password
                            </h2>
                            <button onclick="toggleModal('editModal')" class="text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-6">
                        <form action="update_profile.php" method="post">
                            <div class="space-y-2">
                                <label class="form-label">
                                    <i class="fas fa-lock mr-2 text-indigo-600"></i>
                                    Password Baru
                                </label>
                                <input type="password" name="password" class="form-input block w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" required>
                            </div>
                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" onclick="toggleModal('editModal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all">
                                    <i class="fas fa-times mr-2"></i>
                                    Tutup
                                </button>
                                <button type="submit" class="px-4 py-2 bg-indigo-600 text-white rounded-lg hover:bg-indigo-700 transition-all">
                                    <i class="fas fa-save mr-2"></i>
                                    Simpan
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Modal untuk Konfirmasi Logout -->
            <div id="logoutModal" class="fixed inset-0 z-[9999] flex items-center justify-center bg-black bg-opacity-70 backdrop-blur-sm hidden">
                <div class="bg-white rounded-3xl shadow-2xl p-8 w-full max-w-md mx-4 transform transition-all duration-300 ease-in-out">
                    <div class="flex justify-center mb-6">
                        <img src="../image/Kemenag_logo.jpg" alt="Logo" class="w-32 h-32 rounded-full shadow-lg" />
                    </div>
                    <h2 class="text-center text-2xl font-bold mb-4 text-gray-800">Keluar Aplikasi?</h2>
                    <p class="text-center text-gray-600 mb-6">
                        Apakah Anda yakin ingin keluar? 
                        <br>Tekan <span class="font-bold text-red-600">"Keluar"</span> untuk melanjutkan 
                        <br>atau <span class="font-bold text-gray-700">"Batal"</span> untuk kembali.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button onclick="toggleLogoutModal()" class="px-6 py-2 bg-gray-200 text-gray-700 rounded-full hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <form method="POST" action="../auth/logout.php" class="inline">
                            <button type="submit" class="px-6 py-2 bg-red-600 text-white rounded-full hover:bg-red-700 transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }
        
        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle("hidden");
        }

        function toggleLogoutModal() {
            const modal = document.getElementById("logoutModal");
            modal.classList.toggle("hidden");
        }

        function togglePasswordVisibility() {
            const passwordInput = document.getElementById('password');
            const showPasswordButton = document.querySelector('.fa-eye');
            passwordInput.type = passwordInput.type === 'password' ? 'text' : 'password';
            showPasswordButton.classList.toggle('fa-eye-slash');
        }
    </script>
</body>
</html>