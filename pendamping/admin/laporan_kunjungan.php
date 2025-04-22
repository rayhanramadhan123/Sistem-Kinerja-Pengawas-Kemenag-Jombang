<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$admin_name = $_SESSION['admin']['username_admin'] ?? 'ADMIN';

$sql_pendamping = "SELECT DISTINCT p.PEGID, p.Nama_pendamping FROM pendamping p";
$stmt_pendamping = $conn->prepare($sql_pendamping);
$stmt_pendamping->execute();
$result_pendamping = $stmt_pendamping->get_result();

$pendampingOptions = '';
$selected_pendamping = $_POST['pendamping'] ?? '';
while ($row = $result_pendamping->fetch_assoc()) {
    $selected = ($selected_pendamping == $row['PEGID']) ? 'selected' : '';
    $pendampingOptions .= '<option value="' . $row['PEGID'] . '" ' . $selected . '>' . htmlspecialchars($row['Nama_pendamping']) . '</option>';
}
$stmt_pendamping->close();

$dataKunjungan = [];
if ($selected_pendamping) {
    $sql = "SELECT l.nama_madrasah AS Binaan, 
            k.tanggal_kunjungan, k.uraian, k.foto
            FROM kunjungan k
            INNER JOIN lembaga_binaan lb ON k.id_binaan = lb.id_binaan
            INNER JOIN lembaga l ON lb.nsm = l.nsm
            INNER JOIN pendamping p ON lb.PEGID = p.PEGID
            WHERE p.PEGID = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("i", $selected_pendamping);
    $stmt->execute();
    $result = $stmt->get_result();

    while ($row = $result->fetch_assoc()) {
        $dataKunjungan[] = $row;
    }
    $stmt->close();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_all_kunjungan'])) {
    $sql = "DELETE FROM kunjungan";
    if ($conn->query($sql) === TRUE) {
        echo "<script>alert('Semua data kunjungan berhasil dihapus.');</script>";
        echo "<script>window.location.href = 'laporan_kunjungan.php';</script>";
    } else {
        echo "<script>alert('Gagal menghapus data kunjungan: " . $conn->error . "');</script>";
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <title>Laporan Kunjungan</title>
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

                for (let j = 0; j < cells.length - 1; j++) {
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

        function showImage(src, event) {
            event.stopPropagation();
            const modalImage = document.getElementById("modalImage");
            modalImage.src = src;
            toggleModal("imageModal");
        }

        function confirmDelete() {
            if (confirm("Apakah Anda yakin ingin menghapus semua data kunjungan?")) {
                document.getElementById("deleteForm").submit();
            }
        }
    </script>
</head>

<body class="bg-gray-100 font-sans antialiased">
    <div class="flex flex-col md:flex-row">

        <div class="md:hidden flex justify-between items-center bg-gradient-to-r from-green-600 to-green-800 text-white p-4 shadow-lg">
            <h1 class="text-lg font-semibold"><?php echo htmlspecialchars($admin_name); ?></h1>
            <button onclick="toggleMenu()" class="focus:outline-none">
                <i class="fas fa-bars"></i>
            </button>
        </div>

        <div id="sidebar" class="sidebar w-full md:w-64 bg-gradient-to-b from-green-700 to-green-500 text-white min-h-screen hidden md:block shadow-lg">
            <div class="text-center mt-10">
                <h1 class="text-xl font-bold tracking-wide"><?php echo $admin_name; ?></h1>
            </div>
            <nav class="mt-10">
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="beranda_admin.php">
                    <i class="fas fa-home mr-3"></i> Beranda
                </a>
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="info_binaan.php">
                    <i class="fas fa-database mr-3"></i> Informasi Binaan
                </a>
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="laporan_kunjungan.php">
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

        <div class="flex-1 p-6 md:p-10">
            <div class="sticky top-0 bg-gray-100 z-10">
                <div class="flex justify-between items-center mb-6">
                    <h1 class="text-4xl font-extrabold text-gray-700">LAPORAN KUNJUNGAN</h1>
                </div>

                <div class="mb-6 flex items-center">
                <form method="POST" action="laporan_kunjungan.php" class="flex-1 mr-4">
                    <label for="pendamping" class="block text-gray-700 mb-2 font-semibold">Pilih Pendamping:</label>
                    <select id="pendamping" name="pendamping" class="w-full py-3 pl-4 pr-4 rounded border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" onchange="this.form.submit()">
                        <option value="" disabled <?= empty($selected_pendamping) ? 'selected' : '' ?>> Pendamping</option>
                        <?php echo $pendampingOptions; ?>
                    </select>
                </form>

                <button onclick="confirmDelete()" class="bg-red-600 hover:bg-red-700 text-white py-3 px-6 rounded-lg shadow-md flex items-center transition duration-300 whitespace-nowrap mt-6">
                    <i class="fas fa-trash-alt mr-2"></i> Hapus Semua Data Kunjungan 
                </button>
            </div>

                <form id="deleteForm" method="POST" action="laporan_kunjungan.php" class="hidden">
                    <input type="hidden" name="delete_all_kunjungan" value="1">
                </form>

                <div class="relative mb-6">
                    <input id="searchInput" onkeyup="searchTable()" class="w-full py-3 pl-12 pr-4 rounded-lg border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Data Kunjungan" type="text" />
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                </div>

                <div class="table-scroll">
                    <?php
                    if (!empty($dataKunjungan)) {
                        echo '<div class="table-container">
                                <table class="data-table w-full bg-white shadow-lg rounded-lg overflow-hidden">
                                    <thead class="bg-green-500 text-white">
                                        <tr>
                                            <th class="py-3 px-4">No</th>
                                            <th class="py-3 px-4">Madrasah</th>
                                            <th class="py-3 px-4">Tanggal Kunjungan</th>
                                            <th class="py-3 px-4">Uraian</th>
                                            <th class="py-3 px-4">Bukti Dokumentasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>';

                        $no = 1;
                        foreach ($dataKunjungan as $row) {
                            echo '<tr class="hover:bg-gray-100">
                                    <td class="py-3 px-4">' . $no++ . '</td>
                                    <td class="py-3 px-4">' . htmlspecialchars($row["Binaan"]) . '</td>
                                    <td class="py-3 px-4">' . htmlspecialchars($row["tanggal_kunjungan"]) . '</td>
                                    <td class="py-3 px-4">' . htmlspecialchars($row["uraian"]) . '</td>
                                    <td class="py-3 px-4">
                                        <img src="../uploads/' . htmlspecialchars($row["foto"]) . '" alt="Foto Kunjungan" class="table-img cursor-pointer" onclick="showImage(\'../uploads/' . htmlspecialchars($row["foto"]) . '\', event)">
                                    </td>
                                </tr>';
                        }

                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="no-data text-center text-gray-700">Tidak ada data.</div>';
                    }
                    ?>
                </div>

                <div id="imageModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-70 hidden z-50">
                    <span class="absolute top-4 right-4 cursor-pointer" onclick="toggleModal('imageModal')">
                        <i class="fas fa-times text-white text-2xl"></i>
                    </span>
                    <img id="modalImage" src="" alt="Gambar Besar" class="max-w-full max-h-full">
                </div>

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
    </div>
</body>
</html>