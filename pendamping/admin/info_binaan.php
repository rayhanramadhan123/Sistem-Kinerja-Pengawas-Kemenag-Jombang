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

// Ambil data binaan
$sql_binaan = "SELECT lembaga_binaan.nsm, pendamping.Nama_pendamping AS NamaPendamping,
                      lembaga.nama_madrasah AS NamaMadrasah
               FROM lembaga_binaan
               JOIN pendamping ON lembaga_binaan.PEGID = pendamping.PEGID
               JOIN lembaga ON lembaga_binaan.nsm = lembaga.nsm";

$stmt_binaan = $conn->prepare($sql_binaan);
$stmt_binaan->execute();
$result_binaan = $stmt_binaan->get_result();

// Ambil data pendamping untuk dropdown
$sql_pendamping = "SELECT PEGID, Nama_pendamping FROM pendamping";
$stmt_pendamping = $conn->prepare($sql_pendamping);
$stmt_pendamping->execute();
$result_pendamping = $stmt_pendamping->get_result();

// Ambil data lembaga untuk checkbox
$sql_lembaga = "SELECT nsm, nama_madrasah FROM lembaga";
$stmt_lembaga = $conn->prepare($sql_lembaga);
$stmt_lembaga->execute();
$result_lembaga = $stmt_lembaga->get_result();

// Inisialisasi variabel untuk lembaga yang sedang diampu
$checked_lembaga = [];
$selected_lembaga = [];

// Proses untuk mengambil binaan berdasarkan pendamping yang dipilih
$selected_pendamping = isset($_POST['pendamping']) ? $_POST['pendamping'] : null;

if ($selected_pendamping) {
    $sql_binaan = "SELECT lembaga_binaan.nsm, pendamping.Nama_pendamping AS NamaPendamping,
                          lembaga.nama_madrasah AS NamaMadrasah
                   FROM lembaga_binaan
                   JOIN pendamping ON lembaga_binaan.PEGID = pendamping.PEGID
                   JOIN lembaga ON lembaga_binaan.nsm = lembaga.nsm
                   WHERE lembaga_binaan.PEGID = '$selected_pendamping'";

    $stmt_binaan = $conn->prepare($sql_binaan);
    $stmt_binaan->execute();
    $result_binaan = $stmt_binaan->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <title>Informasi Binaan</title>
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

        function openEditModal(nsm, namaMadrasah, pegid) {
            document.getElementById('editNSM').value = nsm;
            const editLembaga = document.getElementById('editLembaga');
            
            for (let i = 0; i < editLembaga.options.length; i++) {
                if (editLembaga.options[i].text === namaMadrasah) {
                    editLembaga.selectedIndex = i;
                    break;
                }
            }
            document.getElementById('pendamping').value = pegid
            toggleModal('editModal');
        }

        function openDeleteModal(nsm, pegid) {
            document.getElementById('deleteNSM').value = nsm;
            document.getElementById('pendamping').value = pegid; 
            toggleModal('deleteModal');
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

        function validateForm() {
        const pendampingSelect = document.getElementById('pegid');
        if (pendampingSelect.value === "") {
            alert("Silakan pilih pendamping sebelum menyimpan.");
            return false; // Prevent form submission
        }
        return true; // Allow form submission
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
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="info_binaan.php">
                    <i class="fas fa-database mr-3"></i> Informasi Binaan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="laporan_kunjungan.php">
                    <i class="fas fa-users mr-3"></i> Laporan Kunjungan
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="laporan_kinerja.php">
                    <i class="fas fa-file-alt mr-3"></i> Laporan Kinerja
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="laporan_total_kinerja.php">
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
                    <h1 class="text-4xl font-extrabold text-gray-700">INFORMASI BINAAN</h1>
                    <button onclick="toggleModal('modal')" class="bg-gradient-to-r from-green-500 to-green-700 text-white px-6 py-3 rounded-full shadow-lg hover:shadow-2xl hover:bg-green-600 focus:outline-none transition-all duration-300">
                        TAMBAH BINAAN
                    </button>
                </div>
                
                <!-- Dropdown Pilihan Pendamping -->
                <form method="POST" class="mb-6">
                    <label for="pendamping" class="block text-gray-700 mb-2 font-semibold">Pilih Pendamping:</label>
                    <select name="pendamping" id="pendamping" onchange="this.form.submit()" class="w-full py-3 pl-4 pr-10 border border-gray-300 rounded-lg bg-white text-gray-800 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-blue-500 shadow-sm transition duration-200 ease-in-out hover:bg-gray-100">
                        <option value="">Semua Pendamping</option>
                        <?php while ($row_pendamping = $result_pendamping->fetch_assoc()): ?>
                            <option value="<?= htmlspecialchars($row_pendamping['PEGID']) ?>" <?= ($selected_pendamping == $row_pendamping['PEGID']) ? 'selected' : '' ?>><?= htmlspecialchars($row_pendamping['Nama_pendamping']) ?></option>
                        <?php endwhile; ?>
                    </select>
                </form>

                <div class="relative mb-6">
                    <input id="searchInput" onkeyup="searchTable()" class="w-full py-3 pl-12 pr-4 rounded-lg border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Data Binaan" type="text" />
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Tampilan Tabel Data Binaan -->
            <div class="table-scroll">
                <div class="table-container">
                    <table class="data-table w-full">
                        <thead>
                            <tr>
                                <th>No</th>
                                <th>Nama Pendamping</th>
                                <th>Nama Madrasah</th>
                                <th>Fitur</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            if ($result_binaan->num_rows > 0) {
                                $no = 1;
                                while ($row = $result_binaan->fetch_assoc()) {
                                    echo '<tr>
                                            <td>' . $no++ . '</td> 
                                            <td>' . htmlspecialchars($row["NamaPendamping"]) . '</td>
                                            <td>' . htmlspecialchars($row["NamaMadrasah"]) . '</td>
                                            <td>
                                                <button title="Edit" onclick="openEditModal(\'' . htmlspecialchars($row["nsm"]) . '\', \'' . htmlspecialchars($row["NamaMadrasah"]) . '\')" class="text-blue-600">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button title="Hapus" onclick="openDeleteModal(\'' . htmlspecialchars($row["nsm"]) . '\')" class="text-red-600">
                                                    <i class="fas fa-trash-alt"></i>
                                                </button>
                                            </td>
                                        </tr>';
                                }
                            } else {
                                echo '<tr><td colspan="4" class="text-center">Tidak ada data.</td></tr>';
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal untuk Input Data -->
            <div id="modal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden">
                <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg mx-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-1xl font-bold text-green-600">TAMBAH BINAAN PENDAMPING</h2>
                        <button onclick="toggleModal('modal')" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="addBinaanForm" enctype="multipart/form-data" method="POST" action="insert_binaan.php" onsubmit="return validateForm()">
                        <div class="mb-6">
                            <label for="pegid" class="block text-gray-700 font-semibold mb-2">Pilih Pendamping</label>
                            <select id="pegid" name="pegid" class="shadow appearance-none border border-gray-300 rounded-lg w-full py-3 px-4 text-gray-700 leading-tight focus:outline-none focus:ring-2 focus:ring-blue-500">
                                <option value="" disabled selected>Pendamping</option>
                                <?php
                                $result_pendamping->data_seek(0); // Reset pointer hasil pendamping
                                while ($row_pendamping = $result_pendamping->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row_pendamping['PEGID']) . '">' . htmlspecialchars($row_pendamping['Nama_pendamping']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>

                        <div class="mb-6">
                        <label for="pegid" class="block text-gray-700 font-semibold mb-2">Pilih Madrasah</label>
                        
                            <div class="checkbox-container border border-gray-300 rounded-lg overflow-y-auto">
                                <div class="max-h-64">
                                    <?php
                                    $result_lembaga->data_seek(0); // Reset pointer hasil lembaga
                                    while ($row_lembaga = $result_lembaga->fetch_assoc()) {
                                        echo '<div class="mb-2">
                                                <input type="checkbox" id="lembaga' . htmlspecialchars($row_lembaga['nsm']) . '" name="pilih_lembaga[]" value="' . htmlspecialchars($row_lembaga['nsm']) . '" class="mr-2 leading-tight">
                                                <label for="lembaga' . htmlspecialchars($row_lembaga['nsm']) . '" class="text-gray-700">' . htmlspecialchars($row_lembaga['nama_madrasah']) . '</label>
                                            </div>';
                                    }
                                    ?>
                                </div>
                            </div>
                            
                        </div>
                        <div class="flex items-center justify-center">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal untuk Edit Data -->
            <div id="editModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50">
                <div class="bg-white p-8 rounded-lg shadow-lg max-w-lg mx-auto">
                    <div class="flex justify-between items-center mb-4">
                        <h2 class="text-3xl font-bold text-green-600">Edit Binaan</h2>
                        <button onclick="toggleModal('editModal')" class="text-gray-500 hover:text-gray-700">
                            <i class="fas fa-times text-xl"></i>
                        </button>
                    </div>
                    <form id="editForm" method="POST" action="edit_binaan.php">
                        <input type="hidden" name="nsm" id="editNSM">
                        <input type="hidden" name="selected_pendamping" value="<?= htmlspecialchars($selected_pendamping) ?>">
                        <div class="mb-6">
                            <label for="editLembaga" class="block text-gray-700 font-semibold mb-2">Nama Madrasah</label>
                            <select id="editLembaga" name="nama_madrasah" class="shadow border border-gray-300 rounded-lg w-full py-3 px-4" required>
                                <?php
                                $result_lembaga->data_seek(0); // Reset pointer hasil lembaga
                                while ($row_lembaga = $result_lembaga->fetch_assoc()) {
                                    echo '<option value="' . htmlspecialchars($row_lembaga['nsm']) . '">' . htmlspecialchars($row_lembaga['nama_madrasah']) . '</option>';
                                }
                                ?>
                            </select>
                        </div>
                        <div class="flex items-center justify-center">
                            <button type="submit" class="bg-green-600 hover:bg-green-700 text-white font-bold py-3 px-6 rounded-lg">
                                Simpan
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Modal untuk Konfirmasi Hapus -->
            <div id="deleteModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden z-50">
                <div class="bg-white rounded-lg shadow-lg p-6 w-full max-w-sm text-center">
                    <h2 class="text-xl font-semibold mb-2">Hapus Binaan?</h2>
                    <p class="text-gray-600 mb-4">Apakah Anda yakin ingin menghapus data ini?</p>
                    <div class="flex justify-center space-x-4">
                        <button onclick="toggleModal('deleteModal')" class="bg-gray-200 hover:bg-gray-300 text-gray-700 rounded-full px-5 py-2 font-medium">
                            Batal
                        </button>
                        <form method="POST" id="deleteForm" action="delete_binaan.php" class="inline">
                            <input type="hidden" name="nsm" id="deleteNSM">
                            <input type="hidden" name="selected_pendamping" value="<?= htmlspecialchars($selected_pendamping) ?>">
                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white rounded-full px-5 py-2 font-medium">
                                Hapus
                            </button>
                        </form>
                    </div>
                </div>
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