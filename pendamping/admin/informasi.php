<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$role = $_SESSION['role'] ?? null;
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
    <style>
        .image-modal {
            position: fixed;
            inset: 0;
            background-color: rgba(0, 0, 0, 0.9);
            display: flex;
            align-items: center;
            justify-content: center;
            z-index: 50;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease-in-out;
            padding: 1rem;
        }

        .image-modal.active {
            opacity: 1;
            visibility: visible;
        }

        .modal-content {
            position: relative;
            max-width: 90vw;
            max-height: 90vh;
            transform: scale(0.95);
            transition: transform 0.3s ease-in-out;
        }

        .image-modal.active .modal-content {
            transform: scale(1);
        }

        .modal-image-container {
            position: relative;
            width: 100%;
            height: 100%;
            display: flex;
            justify-content: center;
            align-items: center;
        }

        .modal-image {
            max-width: 100%;
            max-height: 85vh;
            object-fit: contain;
            border-radius: 8px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
        }

        .modal-close {
            position: absolute;
            top: -2rem;
            right: -2rem;
            background-color: white;
            color: #374151;
            width: 2.5rem;
            height: 2.5rem;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.2s ease-in-out;
            box-shadow: 0 2px 4px rgba(0, 0, 0, 0.1);
        }

        .modal-close:hover {
            background-color: #f3f4f6;
            transform: scale(1.1);
        }

        .modal-title {
            position: absolute;
            bottom: -2.5rem;
            left: 0;
            right: 0;
            text-align: center;
            color: white;
            font-size: 0.875rem;
            padding: 0.5rem;
        }

        @media (max-width: 640px) {
            .modal-close {
                top: -1.5rem;
                right: -1rem;
                width: 2rem;
                height: 2rem;
            }

            .modal-title {
                bottom: -2rem;
                font-size: 0.75rem;
            }
        }

        /* Loading spinner */
        .loading-spinner {
            width: 40px;
            height: 40px;
            border: 3px solid #f3f3f3;
            border-top: 3px solid #3498db;
            border-radius: 50%;
            animation: spin 1s linear infinite;
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
        }

        @keyframes spin {
            0% { transform: translate(-50%, -50%) rotate(0deg); }
            100% { transform: translate(-50%, -50%) rotate(360deg); }
        }
    </style>
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

            <!-- Pencarian -->
            <div class="mb-6 flex items-center space-x-4">
                <input 
                    type="text" 
                    id="searchInput" 
                    placeholder="Cari informasi..." 
                    class="flex-1 px-4 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500"
                    onkeyup="searchInformasi()"
                >
            </div>

            <?php if ($role == 'admin' && $result->num_rows > 0): ?>
                <div class="mb-4 flex justify-between items-center">
                    <div>
                        <button 
                            onclick="toggleAllCheckboxes()" 
                            class="bg-gray-500 hover:bg-gray-600 text-white px-4 py-2 rounded transition mr-2">
                            <i class="fas fa-check-square mr-2"></i>Pilih Semua
                        </button>
                        <button 
                            id="deleteSelectedBtn"
                            onclick="confirmDeleteSelected()"
                            class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded transition hidden">
                            <i class="fas fa-trash-alt mr-2"></i>Hapus yang Dipilih
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <div id="informasiList">
                <?php if ($result->num_rows > 0): ?>
                    <form id="deleteForm">
                        <?php while($row = $result->fetch_assoc()): ?>
                            <div class="informasi-item border-b py-4 hover:bg-gray-50 transition duration-200">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center space-x-4">
                                        <?php if ($role == 'admin'): ?>
                                            <div class="flex items-center">
                                                <input 
                                                    type="checkbox" 
                                                    name="selected_items[]" 
                                                    value="<?php echo $row['id']; ?>"
                                                    class="delete-checkbox w-5 h-5 text-blue-600 rounded focus:ring-blue-500"
                                                    onchange="updateDeleteButton()"
                                                >
                                            </div>
                                        <?php endif; ?>

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
                                            <button 
                                                type="button" 
                                                onclick="showImageModal('../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>')"
                                                class="bg-green-600 hover:bg-green-700 text-white py-2 px-4 rounded transition mr-2"
                                                title="Lihat Gambar">
                                                <i class="fas fa-eye mr-2"></i>Lihat
                                            </button>
                                            
                                            <a href="../uploads/informasi/<?php echo htmlspecialchars($row['file_path']); ?>" 
                                            download
                                            class="bg-blue-600 hover:bg-blue-800 text-white py-2 px-4 rounded transition mr-2"
                                            title="Download Gambar">
                                                <i class="fas fa-download mr-2"></i>Download
                                            </a>
                                            
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
                function toggleAllCheckboxes() {
                    const checkboxes = document.querySelectorAll('.delete-checkbox');
                    const areAllChecked = Array.from(checkboxes).every(cb => cb.checked);
                    checkboxes.forEach(checkbox => {
                        checkbox.checked = !areAllChecked;
                    });
                    updateDeleteButton();
                }

                function updateDeleteButton() {
                    const selectedCount = document.querySelectorAll('.delete-checkbox:checked').length;
                    const deleteBtn = document.getElementById('deleteSelectedBtn');
                    if (selectedCount > 0) {
                        deleteBtn.classList.remove('hidden');
                        deleteBtn.textContent = `Hapus yang Dipilih (${selectedCount})`;
                    } else {
                        deleteBtn.classList.add('hidden');
                    }
                }

                function confirmDeleteSelected() {
                    const selectedCount = document.querySelectorAll('.delete-checkbox:checked').length;
                    if (selectedCount > 0) {
                        const modal = document.getElementById('deleteModal');
                        const message = document.getElementById('deleteMessage');
                        message.textContent = `Apakah Anda yakin ingin menghapus ${selectedCount} item yang dipilih?`;
                        modal.classList.remove('hidden');
                    }
                }

                function confirmDeleteSingle(id) {
                    const modal = document.getElementById('deleteModal');
                    const message = document.getElementById('deleteMessage');
                    message.textContent = `Apakah Anda yakin ingin menghapus item ini?`;
                    modal.classList.remove('hidden');

                    // Set up the submitDelete function to delete the specific item
                    window.submitDelete = () => {
                        const formData = new FormData();
                        formData.append('ids', JSON.stringify([id]));

                        fetch('delete_informasi_handler.php', {
                            method: 'POST',
                            body: formData
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                const item = document.querySelector(`input[value='${id}']`).closest('.informasi-item');
                                item.remove();
                                message.textContent = 'Item berhasil dihapus!';
                                modal.classList.add('hidden');
                            } else {
                                message.textContent = 'Gagal menghapus item!';
                            }
                        })
                        .catch(error => {
                            console.error(error);
                        });
                    };
                }

                function submitDelete() {
                    const selectedItems = document.querySelectorAll('.delete-checkbox:checked');
                    const ids = Array.from(selectedItems).map(item => item.value);
                    const formData = new FormData();
                    formData.append('ids', JSON.stringify(ids));

                    fetch('delete_informasi_handler.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            selectedItems.forEach(item => {
                                const parent = item.closest('.informasi-item');
                                parent.remove();
                            });
                            const message = document.getElementById('deleteMessage');
                            message.textContent = 'Item berhasil dihapus!';
                            const modal = document.getElementById('deleteModal');
                            modal.classList.add('hidden');
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
                    const spinner = modal.querySelector('.loading-spinner');

                    // Show spinner
                    spinner.style.display = 'block';
                    
                    // Set image source
                    modalImage.src = imageSrc;

                    // Show modal with animation
                    modal.classList.add('active');
                    
                    // Add keyboard and click event listeners
                    document.addEventListener('keydown', handleKeyPress);
                    modal.addEventListener('click', handleModalClick);
                }

                function closeImageModal() {
                    const modal = document.getElementById('imageModal');
                    modal.classList.remove('active');
                    
                    // Remove event listeners
                    document.removeEventListener('keydown', handleKeyPress);
                    modal.removeEventListener('click', handleModalClick);
                }

                function hideSpinner() {
                    const spinner = document.querySelector('.loading-spinner');
                    spinner.style.display = 'none';
                }

                function handleKeyPress(e) {
                    if (e.key === 'Escape') {
                        closeImageModal();
                    }
                }

                function handleModalClick(e) {
                    if (e.target.classList.contains('image-modal')) {
                        closeImageModal();
                    }
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

            <div id="imageModal" class="image-modal">
                <div class="modal-content">
                    <div class="modal-image-container">
                        <div class="loading-spinner"></div>
                        <img id="modalImage" src="" alt="Preview" class="modal-image" onload="hideSpinner()">
                        <button class="modal-close" onclick="closeImageModal()">
                            <i class="fas fa-times"></i>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>