<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Check if 'pendamping' exists in the session and set default values
$user_name = isset($_SESSION['pendamping']['Nama_pendamping']) ? $_SESSION['pendamping']['Nama_pendamping'] : 'User';
$pegid = isset($_SESSION['pendamping']['PEGID']) ? $_SESSION['pendamping']['PEGID'] : null; // Use null or a default value

$sql_total_binaan = "SELECT COUNT(DISTINCT nsm) as total_binaan FROM lembaga_binaan WHERE PEGID = ?";
$stmt_total_binaan = $conn->prepare($sql_total_binaan);
$stmt_total_binaan->bind_param("i", $pegid);
$stmt_total_binaan->execute();
$total_binaan = $stmt_total_binaan->get_result()->fetch_assoc()['total_binaan'];

$sql_total_kunjungan = "SELECT COUNT(*) as total_kunjungan FROM kunjungan k 
                        JOIN lembaga_binaan lb ON k.id_binaan = lb.id_binaan 
                        WHERE lb.PEGID = ?";
$stmt_total_kunjungan = $conn->prepare($sql_total_kunjungan);
$stmt_total_kunjungan->bind_param("i", $pegid);
$stmt_total_kunjungan->execute();
$total_kunjungan = $stmt_total_kunjungan->get_result()->fetch_assoc()['total_kunjungan'];
$sql_informasi = "SELECT * FROM informasi ORDER BY tanggal_upload DESC LIMIT 3";
$result_informasi = $conn->query($sql_informasi);
?>

<!DOCTYPE html>
<html>
<head>
<title>Beranda</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet"/>
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet"/>
    <style>
        body {
            font-family: 'Roboto', sans-serif;
            scroll-behavior: smooth;
        }

        .animated-gradient {
            background: linear-gradient(
                45deg,
                rgba(55, 141, 44, 0.72) 5%,
                rgba(177, 179, 67, 0.73) 25%,
                rgba(235, 235, 235, 0.5) 40%,
                rgba(0, 0, 0, 0.2) 25%
            );
            background-size: 400% 400%;
            animation: gradientBG 10s ease infinite;
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            mask-image: linear-gradient(
                to bottom, 
                rgba(0,0,0,1) 0%, 
                rgba(0,0,0,0.8) 25%, 
                rgba(0,0,0,0.5) 50%, 
                rgba(0,0,0,0.2) 75%, 
                rgba(0,0,0,0) 100%
            );
        }

        @keyframes gradientBG {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .scroll-hidden {
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.6s ease-out;
        }

        .scroll-visible {
            opacity: 1;
            transform: translateY(0);
        }
    </style>

    <script>
        function addScrollAnimation() {
            const observer = new IntersectionObserver((entries) => {
                entries.forEach(entry => {
                    if (entry.isIntersecting) {
                        entry.target.classList.add('scroll-visible');
                        entry.target.classList.remove('scroll-hidden');
                    }
                });
            }, {
                threshold: 0.1
            });

            const scrollElements = document.querySelectorAll('.scroll-animate');
            scrollElements.forEach(el => {
                el.classList.add('scroll-hidden');
                observer.observe(el);
            });
        }

        document.addEventListener('DOMContentLoaded', addScrollAnimation);

        function scrollToSection(sectionId) {
            const section = document.getElementById(sectionId);
            if (section) {
                section.scrollIntoView({ 
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }

        function toggleModal(modalId) {
            const modal = document.getElementById(modalId);
            modal.classList.toggle("hidden");
        }

        function showLogoutModal() {
            toggleModal("logoutModal");
        }
    </script>
</head>
<body class="relative min-h-screen flex flex-col">
    <div class="relative min-h-screen w-full bg-cover bg-center bg-no-repeat" style="background-image: url('../image/kantor.jpg')">
        <div class="animated-gradient absolute inset-0 z-0"></div>
            <div class="animated-gradient absolute top-0 left-0 w-full h-64 z-0"></div>
                <div class="container mx-auto px-4 relative z-10">
                    <div class="bg-cover bg-center h-48 rounded-xl" style="background-image: url('../image/asset2.png')">
                        <div class="bg-gray-500 bg-opacity-50 p-6 mb-8 mt-8 flex items-center space-x-6 rounded-lg shadow-md">
                            <div class="avatar-container mr-1">
                                <img alt="Logo" class="avatar-image rounded-full w-24 h-30" height="96" src="../image/Kemenag_logoo.png" width="96"/>
                            </div>
                            <div>
                                <h1 class="text-3xl font-bold text-white mb-2">Selamat Datang, <?php echo $user_name; ?>!</h1>
                                <p class="text-white">Di Website Sistem Informasi Pendampingan Madrasah Kementerian Agama Kabupaten Jombang</p>
                            </div>
                        </div>
                    </div>

                    <section id="menu-section" class="mt-20 bg-white bg-opacity-20 p-4 rounded-lg shadow-lg md:p-20 scroll-animate">
                        <div class="text-center mb-6 md:mb-12">
                            <h2 class="text-3xl font-bold text-white mb-2 md:text-6xl">"KIPAS JOMBANG"</h2>
                            <h1 class="text-3xl font-bold text-white mb-2 md:text-5xl">MENU</h1>
                            <p class="text-white md:text-lg">Kami senang Anda bergabung dengan kami. Jelajahi dan nikmati layanan kami.</p>
                        </div>
                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 md:gap-8">
                            <div class="scroll-reveal text-center">
                            <a href="#" onclick="scrollToSection('info-section'); return false;" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                <i class="fas fa-info-circle text-4xl text-green-600 mb-4 md:text-5xl"></i>
                                <h3 class="text-lg font-semibold md:text-xl">Informasi</h3>
                            </a>
                            </div>

                            <div class="scroll-reveal text-center">
                                <a href="data_binaan.php" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                    <i class="fas fa-database text-4xl text-yellow-600 mb-4 md:text-5xl"></i>
                                    <h3 class="text-lg font-semibold md:text-xl">Data Binaan</h3>
                                </a>
                            </div>
                            
                            <div class="scroll-reveal text-center">
                                <a href="detail_kunjungan.php" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                    <i class="fas fa-calendar-check text-4xl text-blue-600 mb-4 md:text-5xl"></i>
                                    <h3 class="text-lg font-semibold md:text-xl">Detail Kunjungan</h3>
                                </a>
                            </div>
                            
                            <div class="scroll-reveal text-center">
                                <a href="rekap_kinerja.php" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                    <i class="fas fa-file-alt text-4xl text-blue-500 mb-4 md:text-5xl"></i>
                                    <h3 class="text-lg font-semibold md:text-xl">Rekap Kinerja</h3>
                                </a>
                            </div>

                            <div class="scroll-reveal text-center">
                                <a href="profile.php" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                    <i class="fas fa-user text-4xl text-purple-600 mb-4 md:text-5xl"></i>
                                    <h3 class="text-lg font-semibold md:text-xl">Profil</h3>
                                </a>
                            </div>

                            <div class="scroll-reveal text-center">
                                <a href="#" onclick="showLogoutModal()" class="block p-4 bg-white bg-opacity-70 rounded-lg shadow-lg hover:shadow-xl transition-all duration-300 md:p-6">
                                    <i class="fas fa-sign-out-alt text-4xl text-red-600 mb-4 md:text-5xl"></i>
                                    <h3 class="text-lg font-semibold md:text-xl">Keluar</h3>
                                </a>
                            </div>
                        </div>
                    </section>
            
            <!-- Information Section - Updated margin -->
            <section id="info-section" class="mt-[50vh] mb-16 scroll-animate">
                <div class="bg-white bg-opacity-60 rounded-xl shadow-lg p-8">
                    <div class="flex justify-between items-center mb-6">
                        <h2 class="text-2xl font-bold text-gray-800">
                            <i class="fas fa-bullhorn text-yellow-600 mr-2"></i>
                            Informasi dan Pengumuman
                        </h2>
                        <a href="informasi_user.php" class="text-blue-600 hover:text-blue-800 font-medium">
                            Lihat Semua <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>

                    <div class="grid grid-cols-1 gap-6">
                        <?php if ($result_informasi && $result_informasi->num_rows > 0): ?>
                            <?php while($row = $result_informasi->fetch_assoc()): ?>
                                <div class="info-card bg-gray-50 rounded-lg p-6 shadow-md hover:shadow-lg transition-all duration-300 scroll-animate">
                                    <div class="flex justify-between items-start mb-4">
                                        <div class="flex items-center">
                                            <?php 
                                            // Pilih ikon berdasarkan tipe file
                                            switch($row['file_type']) {
                                                case 'pdf':
                                                    echo '<i class="fas fa-file-pdf text-red-500 mr-3 text-2xl"></i>';
                                                    break;
                                                case 'image':
                                                    echo '<i class="fas fa-image text-blue-500 mr-3 text-2xl"></i>';
                                                    break;
                                                case 'docx':
                                                    echo '<i class="fas fa-file-word text-blue-600 mr-3 text-2xl"></i>';
                                                    break;
                                                case 'xlsx':
                                                    echo '<i class="fas fa-file-excel text-green-600 mr-3 text-2xl"></i>';
                                                    break;
                                                default:
                                                    echo '<i class="fas fa-file text-gray-500 mr-3 text-2xl"></i>';
                                            }
                                            ?>
                                            <h3 class="text-xl font-semibold text-gray-800"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                        </div>
                                        <span class="text-sm text-gray-500"><?php echo date('d M Y', strtotime($row['tanggal_upload'])); ?></span>
                                    </div>
                                    
                                    <?php if(!empty($row['deskripsi'])): ?>
                                        <p class="text-gray-600 mb-4"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                    <?php endif; ?>
                                    
                                    <div class="mt-4 flex justify-between items-center">
                                        <a href="../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                        target="_blank" 
                                        class="text-blue-600 hover:text-blue-800 font-medium">
                                            Buka File <i class="fas fa-external-link-alt ml-1"></i>
                                        </a>
                                        
                                        <?php if($row['file_type'] == 'image'): ?>
                                            <button onclick="showImageModal('../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>')" 
                                                    class="text-green-600 hover:text-green-800 font-medium">
                                                Lihat Gambar <i class="fas fa-eye ml-1"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        <?php else: ?>
                            <div class="text-center p-8 scroll-animate">
                                <i class="fas fa-info-circle text-gray-400 text-4xl mb-4"></i>
                                <p class="text-gray-500">Belum ada informasi terbaru.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </section>
            <!-- Tambahkan modal untuk menampilkan gambar -->
                <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
                    <div class="max-w-4xl max-h-[90vh] overflow-auto relative">
                        <button onclick="closeImageModal()" class="absolute top-4 right-4 text-white text-3xl">&times;</button>
                        <img id="modalImage" src="" alt="Gambar Informasi" class="max-w-full max-h-full object-contain">
                    </div>
                </div>
        </div>
    </div>

    <script>
        function showImageModal(imageSrc) {
            const modal = document.getElementById('imageModal');
            const modalImage = document.getElementById('modalImage');
            modalImage.src = imageSrc;
            modal.classList.remove('hidden');
        }

        function closeImageModal() {
            const modal = document.getElementById('imageModal');
            modal.classList.add('hidden');
        }
    </script>

    <!-- Modal untuk Konfirmasi Logout -->
    <div id="logoutModal" class="fixed inset-0 flex items-center justify-center p-4 bg-black bg-opacity-50 backdrop-blur-sm hidden transition-opacity duration-300 ease-in-out z-20">
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
</body>
</html>