<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Ensure the user is logged in
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Retrieve user role
$role = $_SESSION['role'] ?? null;

// Check if admin details are set correctly
if ($role === 'user' && !isset($_SESSION['pendamping'])) {
    die("Admin session not initialized.");
}

// Fetch information from the database
$sql = "SELECT * FROM informasi ORDER BY tanggal_upload DESC";
$result = $conn->query($sql);

if (!$result) {
    die("Query gagal: " . $conn->error);
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Informasi & Pengumuman</title>
    <link rel="icon" href="../image/Kemenag_logoo.png" type="image/jpg" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="../tampilan/tabel-pagination-sidebar.css">
</head>
<body class="bg-gray-100 font-sans">
    <div class="container mx-auto px-4 py-8">
        <div class="bg-white shadow-md rounded-lg p-6">
            <h1 class="text-3xl font-bold mb-6 text-center text-gray-800">
                <i class="fas fa-bullhorn text-yellow-600 mr-3"></i>
                Informasi & Pengumuman
            </h1>

            <!-- Alert Messages -->
            <?php if (isset($_SESSION['delete_success'])): ?>
                <div class="bg-green-100 border-l-4 border-green-500 text-green-700 p-4 mb-4" role="alert">
                    <?php 
                    echo $_SESSION['delete_success'];
                    unset($_SESSION['delete_success']);
                    ?>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['delete_error'])): ?>
                <div class="bg-red-100 border-l-4 border-red-500 text-red-700 p-4 mb-4" role="alert">
                    <?php 
                    echo $_SESSION['delete_error'];
                    unset($_SESSION['delete_error']);
                    ?>
                </div>
            <?php endif; ?>

            <!-- Search Bar -->
            <div class="mb-6 flex items-center space-x-4">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Cari informasi..." 
                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeyup="searchInformasi()"
                >
            </div>

            <div id="informasiList">
                <?php if ($result->num_rows > 0): ?>
                    <form id="deleteForm">
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="informasi-item border-b py-4 hover:bg-gray-50 transition duration-200" data-id="<?php echo $row['id']; ?>">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <i class="<?php echo $icon . ' ' . $iconColor . ' text-2xl'; ?>"></i>
                                        <div>
                                            <h3 class="text-lg font-semibold text-gray-800"><?php echo htmlspecialchars($row['judul']); ?></h3>
                                            <p class="text-sm text-gray-500">
                                                <?php echo date('d M Y', strtotime($row['tanggal_upload'])); ?> 
                                                • <?php echo strtoupper($row['file_type']); ?>
                                            </p>
                                        </div>
                                    </div>

                                    <div class="flex space-x-2">
                                        <?php if($row['file_type'] == 'image'): ?>
                                            <a href="../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                               download
                                               class="bg-blue-600 hover:bg-blue-800 text-white py-2 px-4 rounded transition mr-2"
                                               title="Download Gambar">
                                                Download
                                            </a>
                                            <button 
                                                onclick="showImageModal('../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>')" 
                                                class="bg-green-600 hover:bg-green-800 text-white py-2 px-4 rounded transition"
                                                title="Lihat Gambar">
                                                Tampilkan
                                            </button>
                                        <?php else: ?>
                                            <a href="../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                               target="_blank" 
                                               class="text-blue-600 hover:text-blue-800 transition"
                                               title="Buka File">
                                                <i class="fas fa-external-link-alt"></i>
                                            </a>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <?php if(!empty($row['deskripsi'])): ?>
                                    <p class="mt-2 text-gray-600 text-sm"><?php echo htmlspecialchars($row['deskripsi']); ?></p>
                                <?php endif; ?>
                            </div>
                        <?php endwhile; ?>
                    </form>
                <?php else: ?>
                    <div class="text-center py-8 text-gray-500">
                        <i class="fas fa-info-circle text-4xl mb-4"></i>
                        <p>Belum ada informasi yang tersedia.</p>
                    </div>
                <?php endif; ?>
            </div>

            <!-- Modal Gambar -->
            <div id="imageModal" class="fixed inset-0 bg-black bg-opacity-75 flex items-center justify-center z-50 hidden">
                <button onclick="closeImageModal()" class="absolute top-20 right-20 text-white text-8xl hover:text-gray-300 transition-colors">
                    &times;
                </button>
                <div class="max-w-4xl max-h-[90vh] overflow-auto relative">
                    <img id="modalImage" src="" alt="Gambar Informasi" class="max-w-full max-h-full object-contain">
                </div>
            </div>

            <!-- Modal Konfirmasi Hapus -->
            <div id="deleteModal" class="fixed inset-0 bg-black bg-opacity-50 flex items-center justify-center z-50 hidden">
                <div class="bg-white rounded-lg p-6 max-w-md w-full mx-4">
                    <h3 class="text-lg font-bold mb-4">Konfirmasi Hapus</h3>
                    <p id="deleteMessage" class="mb-6 text-gray-600"></p>
                    <div class="flex justify-end space-x-4">
                        <button onclick="hideDeleteModal()" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300 transition">
                            Batal
                        </button>
                        <button onclick="submitDelete()" class="px-4 py-2 bg-red-600 text-white rounded hover:bg-red-700 transition">
                            Hapus
                        </button>
                    </div>
                </div>
            </div>

            <script>
                function confirmDelete(itemId) {
                    const modal = document.getElementById('deleteModal');
                    const message = document.getElementById('deleteMessage');
                    message.textContent = "Apakah Anda yakin ingin menghapus item ini?";
                    modal.classList.remove('hidden');
                    
                    // Store the ID of the item to be deleted
                    modal.dataset.itemId = itemId;
                }

                function submitDelete() {
                    const itemId = document.getElementById('deleteModal').dataset.itemId;
                    
                    fetch('delete_informasi_handler.php', {
                        method: 'POST',
                        body: JSON.stringify({ id: itemId }),
                        headers: {
                            'Content-Type': 'application/json'
                        }
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            document.querySelector(`.informasi-item[data-id='${itemId}']`).remove();
                            const message = document.getElementById('deleteMessage');
                            message.textContent = 'Item berhasil dihapus!';
                        } else {
                            const message = document.getElementById('deleteMessage');
                            message.textContent = 'Gagal menghapus item!';
                        }
                    })
                    .catch(error => {
                        console.error(error);
                    });
                }

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

                function hideDeleteModal() {
                    const modal = document.getElementById('deleteModal');
                    modal.classList.add('hidden');
                }

                function searchInformasi() {
                    const input = document.getElementById('searchInput');
                    const filter = input.value.toLowerCase();
                    const items = document.getElementById('informasiList').getElementsByClassName('informasi-item');

                    for (let i = 0; i < items.length; i++) {
                        const title = items[i].querySelector('h3');
                        const description = items[i].querySelector('p');
                        
                        if (
                            title.textContent.toLowerCase().includes(filter) || 
                            description.textContent.toLowerCase().includes(filter)
                        ) {
                            items[i].style.display = '';
                        } else {
                            items[i].style.display = 'none';
                        }
                    }
                }
            </script>
        </body>
    </html>