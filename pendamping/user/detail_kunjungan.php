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

// bagian submit 
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $id_binaan = $_POST['tempat_binaan'];
    $uraian = $_POST['uraian'];
    $tanggal = $_POST['tanggal'];

    // Handle file upload
    if (isset($_FILES["foto"]) && $_FILES["foto"]["error"] === UPLOAD_ERR_OK) {
        $target_dir = "../uploads/"; 
        $file_name = basename($_FILES["foto"]["name"]);
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        $allowed_types = ["jpg", "jpeg", "png", "gif"];
    
        if (!in_array($file_type, $allowed_types)) {
            echo "Sorry, only JPG, JPEG, PNG, and GIF files are allowed.";
            exit();
        }
    
        $target_file = $target_dir . uniqid() . "_" . $file_name;
    
        if (!is_dir($target_dir)) {
            mkdir($target_dir, 0777, true); // Buat direktori jika belum ada
        }
    
        if (move_uploaded_file($_FILES["foto"]["tmp_name"], $target_file)) {
            // Jika upload berhasil, masukkan data ke database
            $sql = "INSERT INTO kunjungan (id_binaan, uraian, tanggal_kunjungan, foto) 
                    VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("isss", $id_binaan, $uraian, $tanggal, $target_file);
    
            if ($stmt->execute()) {
                header("Location: detail_kunjungan.php");
                exit();
            } else {
                echo "Error: " . $stmt->error;
            }
            $stmt->close();
        } else {
            echo "Sorry, there was an error uploading your file.";
        }
    } else {
        echo "File upload error.";
    } 
}

// Hanya ambil data kunjungan untuk pengguna yang sedang login
$sql = "SELECT p.PEGID, l.nama_madrasah AS Binaan, 
        k.tanggal_kunjungan, k.uraian, k.foto
        FROM kunjungan k
        INNER JOIN lembaga_binaan lb ON k.id_binaan = lb.id_binaan
        INNER JOIN lembaga l ON lb.nsm = l.nsm
        INNER JOIN pendamping p ON lb.PEGID = p.PEGID
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
    <title>Detail Kunjungan</title>
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
            event.stopPropagation(); // Mencegah event klik menyebar ke elemen induk
            const modalImage = document.getElementById("modalImage");
            modalImage.src = src; // Set src image modal
            toggleModal("imageModal"); // Tampilkan modal
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
                <a class="flex items-center py-3 px-8 text-gray-200 hover:bg-green-600 rounded-lg hover:text-white transition-all duration-300" href="data_binaan.php">
                    <i class="fas fa-database mr-3"></i> Data Binaan
                </a>
                <a class="flex items-center py-3 px-8 bg-green-500 text-white rounded-lg hover:bg-green-400 transition-all duration-300" href="detail_kunjungan.php">
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
                    <h1 class="text-4xl font-extrabold text-gray-700">DETAIL KUNJUNGAN</h1>
                    <button id="tambahDataButton" onclick="toggleModal('modal')" class="bg-gradient-to-r from-green-500 to-green-700 text-white px-6 py-3 rounded-full shadow-lg hover:shadow-2xl hover:bg-blue-600 focus:outline-none transition-all duration-300">
                        TAMBAH DATA
                    </button>
                </div>
                <div class="relative mb-6">
                    <input id="searchInput" onkeyup="searchTable()" class="w-full py-3 pl-12 pr-4 rounded-lg border border-gray-300 shadow focus:outline-none focus:ring-2 focus:ring-blue-500" placeholder="Cari Data Detail Kunjungan" type="text" />
                    <i class="fas fa-search absolute left-4 top-3 text-gray-400"></i>
                </div>
            </div>
            
            <!-- Tampilan Tabel Detail Kunjungan -->
            <div class="table-scroll">
                <?php
                    if ($result->num_rows > 0) {
                        echo '<div class="table-container">
                                <table class="data-table">
                                    <thead>
                                        <tr>
                                            <th>No</th> <!-- Kolom nomor urut -->
                                            <th>Madrasah</th>
                                            <th>Tanggal Kunjungan</th>
                                            <th>Uraian</th>
                                            <th>Bukti Dokumentasi</th>
                                        </tr>
                                    </thead>
                                    <tbody>';
                        
                        $no = 1; // Inisialisasi nomor urut
                        while ($row = $result->fetch_assoc()) {
                            echo '<tr>
                                    <td>' . $no++ . '</td> 
                                    <td>' . htmlspecialchars($row["Binaan"]) . '</td>
                                    <td>' . htmlspecialchars($row["tanggal_kunjungan"]) . '</td>
                                    <td>' . htmlspecialchars($row["uraian"]) . '</td>
                                    <td>
                                        <img src="../uploads/' . htmlspecialchars($row["foto"]) . '" alt="Foto Kunjungan" class="table-img" onclick="showImage(\'../uploads/' . htmlspecialchars($row["foto"]) . '\', event)" style="cursor: pointer;">
                                    </td>
                                </tr>';
                        }
                        
                        echo '</tbody></table></div>';
                    } else {
                        echo '<div class="no-data">Tidak ada data.</div>';
                    }
                ?>
            </div>

            <!-- Bagian Button Input Data -->
            <div id="modal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden">
                <div class="bg-white rounded-2xl shadow-2xl w-full max-w-md transform">
                    <div class="p-6 border-b border-gray-200">
                        <div class="flex justify-between items-center">
                            <h2 class="text-2xl font-bold text-gray-800 flex items-center">
                                <i class="fas fa-plus-circle text-green-600 mr-2"></i>
                                Tambah Data
                            </h2>
                            <button onclick="toggleModal('modal')" class="text-gray-500 hover:text-gray-700 transition-colors">
                                <i class="fas fa-times text-xl"></i>
                            </button>
                        </div>
                    </div>
                    
                    <div class="p-6">
                        <!-- form -->
                        <form class="space-y-6" enctype="multipart/form-data" method="POST" action="detail_kunjungan.php">
                            <div class="space-y-2">
                                <label class="form-label">
                                    <i class="fas fa-building mr-2 text-green-600"></i>
                                    Pilih Tempat Binaan
                                </label>
                                <select class="form-input" name="tempat_binaan" required>
                                    <option value="" disabled selected>Lokasi</option>
                                    <?php echo $binaanOptions; ?>
                                </select>
                            </div>
                            <div class="space-y-2">
                                <label class="form-label">
                                    <i class="fas fa-file-alt mr-2 text-green-600"></i>
                                    Uraian
                                </label>
                                <textarea class="form-textarea" name="uraian" placeholder="Masukkan uraian kegiatan..." rows="4" required></textarea>
                            </div>
                            <div class="space-y-2">
                                <label class="form-label">
                                    <i class="fas fa-calendar mr-2 text-green-600"></i>
                                    Tanggal
                                </label>
                                <input type="date" name="tanggal" class="form-input" required />
                            </div>
                            <div class="space-y-2">
                                <label class="form-label">
                                    <i class="fas fa-image mr-2 text-green-600"></i>
                                    Unggah Foto
                                </label>
                                <input type="file" name="foto" accept="image/*" class="form-input" required capture="user" />
                            </div>
                            <div class="flex justify-end space-x-3 pt-4">
                                <button type="button" onclick="toggleModal('modal')" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all">
                                    <i class="fas fa-times mr-2"></i>
                                    Tutup
                                </button>
                                <button type="submit" class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition-all">
                                    <i class="fas fa-paper-plane mr-2"></i>
                                    Kirim
                                </button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- untuk Menampilkan Gambar Besar -->
            <div id="imageModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-70 hidden z-50">
                <span class="absolute top-4 right-4 cursor-pointer" onclick="toggleModal('imageModal')">
                    <i class="fas fa-times text-white text-2xl"></i>
                </span>
                <img id="modalImage" src="" alt="Gambar Besar" class="max-w-full max-h-full">
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