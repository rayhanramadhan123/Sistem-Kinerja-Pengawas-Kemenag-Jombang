<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil nama admin dari session
$admin_name = $_SESSION['admin']['username_admin'] ?? 'ADMIN';

// Ambil data total kunjungan untuk semua pendamping
$sql = "SELECT 
            p.Nama_pendamping,
            COUNT(k.id_kunjungan) AS total_kunjungan
        FROM pendamping p
        LEFT JOIN lembaga_binaan lb ON p.PEGID = lb.PEGID
        LEFT JOIN kunjungan k ON lb.id_binaan = k.id_binaan
        GROUP BY p.PEGID, p.Nama_pendamping
        ORDER BY total_kunjungan DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <title>Raport Kinerja</title>
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

        function exportToExcel() {
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = 'export_total_excel.php'; // Pastikan ini sesuai dengan path file Anda
            document.body.appendChild(form);
            form.submit();
        }
    </script>
</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="flex flex-col md:flex-row">

        <!-- Hamburger Menu -->
        <div class="md:hidden flex justify-between items-center bg-gradient-to-r from-green-600 to-green-800 text-white p-4 shadow-lg">
            <h1 class="text-lg font-semibold"><?php echo htmlspecialchars($admin_name); ?></h1>
            <button onclick="toggleMenu()" class="focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <!-- Sidebar -->
        <div id="sidebar" class="sidebar w-full md:w-64 bg-gradient-to-b from-green-700 to-green-500 text-white min-h-screen hidden md:block shadow-lg">
            <div class="text-center mt-10">
                <h1 class="text-xl font-bold tracking-wide"><?php echo htmlspecialchars($admin_name); ?></h1>
            </div>
            <nav class="mt-10">
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="beranda_admin.php">
                    <i class="fas fa-home mr-3"></i> Beranda
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="info_binaan.php">
                    <i class="fas fa-database mr-3"></i> Informasi Binaan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="laporan_kunjungan.php">
                    <i class="fas fa-users mr-3"></i> Laporan Kunjungan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="laporan_kinerja.php">
                    <i class="fas fa-file-alt mr-3"></i> Laporan Kinerja
                </a>
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="laporan_total_kinerja.php">
                    <i class="fas fa-chart-line mr-3"></i> Raport Kinerja
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
                    <h1 class="text-4xl font-extrabold text-gray-700">RAPORT KINERJA PENDAMPING</h1>
                </div>

                <!-- Tampilan Search dan Export -->
                <div class="flex items-center mb-6 space-x-4">
                    <div class="relative w-full flex-grow">
                        <input id="searchInput" onkeyup="searchTable()" class="w-full py-3 pl-10 pr-4 rounded-lg border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Data" type="text" />
                        <i class="fas fa-search absolute left-3 top-3 text-gray-400"></i>
                    </div>
                    <button onclick="exportToExcel()" class="flex-shrink-0 px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-500 transition duration-200">
                        <i class="fas fa-file-excel mr-1"></i> Export to Excel
                    </button>
                </div>

            <!-- Tampilan Tabel Rekap Kinerja -->
            <div class="overflow-auto max-h-96">
                <table class="data-table w-full rounded-lg overflow-hidden">
                    <thead>
                        <tr>
                            <th>No</th>
                            <th>Nama Pendamping</th>
                            <th>Nilai Kunjungan</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                            if ($result->num_rows > 0) {
                                $no = 1;
                                while ($row = $result->fetch_assoc()) {
                                    // Tentukan kelas untuk jumlah kunjungan
                                    $jumlah_kunjungan_class = ($row["total_kunjungan"] < 12) ? 'text-red-600' : '';

                                    echo '<tr>
                                             <td>' . $no++ . '</td>
                                             <td>' . htmlspecialchars($row["Nama_pendamping"]) . '</td>
                                             <td class="' . $jumlah_kunjungan_class . '"><strong>' . htmlspecialchars($row["total_kunjungan"]) . '</strong></td>
                                        </tr>';
                                }                       
                            } else {
                                echo '<tr><td colspan="3" class="text-center">Tidak ada data.</td></tr>';
                            }
                        ?>
                    </tbody>
                </table>
            </div>

            <!-- Informasi Penilaian -->
            <br><label for="pendamping" class="block text-gray-700 mb-2 font-bold">Penilaian :</label>
            <label for="pendamping" class="block text-gray-700 mb-2 font-semibold">- Hasil Dari Keseluruhan Jumlah Kunjungan Pendamping Pada Binaanya Dijumlahkan.</label>
            <label for="pendamping" class="block text-gray-700 mb-2 font-semibold">- Untuk Memenuhi Syarat Nilai Kunjungan Harus Berada Pada Nilai 12 Atau Lebih.</label>

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