<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil nama pengguna dari session
$user_name = isset($_SESSION['pendamping']['Nama_pendamping']) ? $_SESSION['pendamping']['Nama_pendamping'] : 'User';

// Ambil PEGID dari session
$pegid = $_SESSION['pendamping']['PEGID'];

// Query untuk mendapatkan binaan berdasarkan PEGID
$sql_binaan = "SELECT lb.id_binaan, l.nama_madrasah 
               FROM lembaga_binaan lb 
               INNER JOIN lembaga l ON lb.nsm = l.nsm 
               WHERE lb.PEGID = ?";
$stmt_binaan = $conn->prepare($sql_binaan);
$stmt_binaan->bind_param("i", $pegid);
$stmt_binaan->execute();
$result_binaan = $stmt_binaan->get_result();

$binaanOptions = '';
while ($row = $result_binaan->fetch_assoc()) {
    $binaanOptions .= '<option value="' . $row['id_binaan'] . '">' . $row['nama_madrasah'] . '</option>';
}
$stmt_binaan->close();

// Hanya ambil data kunjungan untuk pengguna yang sedang login
$sql = "SELECT 
        l.nama_madrasah,
        l.Alamat
        FROM lembaga_binaan lb
        JOIN lembaga l ON lb.nsm = l.nsm
        WHERE lb.PEGID = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $pegid);
$stmt->execute();
$result = $stmt->get_result();

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <title>Data Binaan</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="../tampilan/tabel-pagination-sidebar.css">
    
    <script>
        function toggleMenu() {
            document.getElementById("sidebar").classList.toggle("hidden");
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle("hidden");
        }

        function showLogoutModal() {
            toggleModal("logoutModal");
        }

        function searchTable() {
            const input = document.getElementById("searchInput");
            const filter = input.value.toLowerCase();
            const table = document.querySelector(".data-table");
            const rows = table.getElementsByTagName("tr");

            for (let i = 1; i < rows.length; i++) {
                const cells = rows[i].getElementsByTagName("td");
                let rowVisible = false;

                for (let j = 0; j < cells.length; j++) {
                    const cell = cells[j];
                    if (cell) {
                        const text = cell.textContent || cell.innerText;
                        if (text.toLowerCase().indexOf(filter) > -1) {
                            rowVisible = true;
                            break;
                        }
                    }
                }
                rows[i].style.display = rowVisible ? "" : "none";
            }
        }
    </script>

</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="flex flex-col md:flex-row">

        <!-- Hamburger Menu -->
        <div class="md:hidden flex justify-between items-center bg-gradient-to-r from-green-600 to-green-800 text-white p-4 shadow-lg">
            <h1 class="text-lg font-semibold"><?php echo $user_name; ?></h1>
            <button onclick="toggleMenu()" class="focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-full md:w-64 bg-gradient-to-b from-green-800 to-green-600 text-white min-h-screen hidden md:block shadow-lg">
            <div class="text-center mt-10">
                <h1 class="text-xl font-bold tracking-wide"><?php echo $user_name; ?></h1>
            </div>
            <nav class="mt-10">
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="beranda.php">
                    <i class="fas fa-home mr-3"></i> Beranda
                </a>
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="halaman_binaan.php">
                    <i class="fas fa-database mr-3"></i> Data Binaan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="detail_kunjungan.php">
                    <i class="fas fa-users mr-3"></i> Detail Kunjungan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="rekap_kinerja.php">
                    <i class="fas fa-file-alt mr-3"></i> Rekap Kinerja
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="profile.php">
                    <i class="fas fa-user mr-3"></i> Profile
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="#" onclick="showLogoutModal()">
                    <i class="fas fa-sign-out-alt mr-3"></i> Keluar
                </a>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex-1 p-6 md:p-10">
            <div class="sticky top-0 bg-gray-100 z-10">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-4xl font-extrabold text-gray-700">DATA BINAAN</h1>
                </div>
                <div class="relative mb-6">
                    <input id="searchInput" onkeyup="searchTable()" class="w-full py-3 pl-12 pr-4 rounded-lg border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Data Binaan" type="text" />
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Tampilan Tabel Rekap Kinerja -->
            <div class="table-scroll">
                <?php
                    if ($result->num_rows > 0) {
                        echo '<div class="table-container">
                                <table class="data-table w-full">
                                    <thead>
                                        <tr>
                                            <th>No</th>
                                            <th>Madrasah</th>
                                            <th>Alamat</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        $no = 1;
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>
                                    <td>' . $no++ . '</td> 
                                    <td>' . htmlspecialchars($row["nama_madrasah"]) . '</td>
                                    <td>' . htmlspecialchars($row["Alamat"]) . '</td>
                                </tr>';
                        }                       
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="no-data">Tidak ada data.</div>';
                    }
                ?>
            </div>

            <!-- Modal untuk Konfirmasi Logout -->
            <div id="logoutModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out">
                <div class="bg-white rounded-3xl shadow-lg p-6 w-full max-w-sm relative transform scale-95 transition-transform duration-300 ease-in-out">
                    <div class="flex justify-center mb-4">
                        <img src="../image/Kemenag_logo.jpg" alt="Logo" class="w-24 h-24 rounded-full shadow-md" />
                    </div>
                    <h2 class="text-center text-xl font-semibold mb-2 text-gray-800">Keluar?</h2>
                    <p class="text-center text-gray-600 mb-4">
                        Apakah Anda yakin ingin keluar? <br>Tekan <span class="font-semibold text-red-600">"Keluar"</span> untuk melanjutkan <br> atau <span class="font-semibold text-gray-700">"Batal"</span> untuk kembali.
                    </p>
                    <div class="flex justify-center space-x-4">
                        <button onclick="toggleModal('logoutModal')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full px-5 py-2 font-medium transition">
                            Batal
                        </button>
                        <form method="POST" action="../auth/logout.php" class="inline">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-full px-5 py-2 font-medium transition">
                                Keluar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>