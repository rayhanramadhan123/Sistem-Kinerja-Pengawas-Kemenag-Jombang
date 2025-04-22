<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan hanya admin yang bisa akses
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit();
}

$admin_name = $_SESSION['admin']['username_admin'] ?? 'Admin';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Upload Informasi</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-100">
    <div class="container mx-auto px-4 py-8">
        <div class="max-w-lg mx-auto bg-white shadow-md rounded-lg p-6">
            <h2 class="text-2xl font-bold mb-6 text-center">
                <i class="fas fa-upload text-green-600 mr-2"></i>Upload Informasi
            </h2>

            <!-- Pesan Sukses/Error -->
            <?php if(isset($_SESSION['upload_success'])): ?>
                <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['upload_success']; ?></span>
                    <?php unset($_SESSION['upload_success']); ?>
                </div>
            <?php endif; ?>

            <?php if(isset($_SESSION['upload_error'])): ?>
                <div class="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mb-4" role="alert">
                    <span class="block sm:inline"><?php echo $_SESSION['upload_error']; ?></span>
                    <?php unset($_SESSION['upload_error']); ?>
                </div>
            <?php endif; ?>

            <!-- Form Upload -->
            <form action="../admin/upload_informasi_handler.php" method="POST" enctype="multipart/form-data" class="space-y-4">
                <div>
                    <label class="block text-gray-700 mb-2">Judul Informasi</label>
                    <input type="text" name="judul" required 
                           class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                           placeholder="Masukkan judul informasi">
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Deskripsi (Opsional)</label>
                    <textarea name="deskripsi" rows="3" 
                              class="w-full px-3 py-2 border rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                              placeholder="Tambahkan deskripsi"></textarea>
                </div>

                <div>
                    <label class="block text-gray-700 mb-2">Pilih File</label>
                    <div class="flex items-center justify-center w-full">
                        <label class="flex flex-col w-full h-32 border-4 border-dashed hover:bg-gray-100 hover:border-green-300 group">
                            <div class="flex flex-col items-center justify-center pt-7 cursor-pointer">
                                <i class ="fas fa-cloud-upload-alt text-green-500 text-3xl group-hover:text-green-400"></i>
                                <span class="mt-2 text-sm text-gray-400 group-hover:text-blue-400">Seret dan lepas file di sini atau klik untuk memilih</span>
                                <input type="file" name="file" class="hidden" required />
                            </div>
                        </label>
                    </div>
                </div>

                <div class="flex justify-end space-x-3 pt-4">
                    <a href="../admin/beranda_admin.php" class="px-4 py-2 bg-gray-100 text-gray-700 rounded-lg hover:bg-gray-200 transition-all">
                        <i class="fas fa-arrow-left mr-2"></i>
                        Kembali
                    </a>
                    <button type="submit" class="bg-green-500 text-white px-4 py-2 rounded-lg hover:bg-green-600">Upload</button>
                </div>
            </form>
        </div>
    </div>
</body>
</html>