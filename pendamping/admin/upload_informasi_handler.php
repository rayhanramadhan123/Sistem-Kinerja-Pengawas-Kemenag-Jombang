<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan hanya admin yang bisa upload
if (!isset($_SESSION['admin'])) {
    header("Location: ../auth/login.php");
    exit();
}

function generateUniqueFileName($originalName) {
    // Bersihkan nama file dari karakter yang tidak diinginkan
    $cleanFileName = preg_replace("/[^a-zA-Z0-9._-]/", "", $originalName);
    
    // Potong nama file jika terlalu panjang (misalnya maks 50 karakter)
    $cleanFileName = substr($cleanFileName, 0, 50);
    
    // Dapatkan ekstensi file
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $judul = trim($_POST['judul']);
    $deskripsi = trim($_POST['deskripsi'] ?? '');

    // Validasi input
    if (empty($judul)) {
        $_SESSION['upload_error'] = "Judul tidak boleh kosong";
        header("Location: ../admin/upload_informasi.php");
        exit();
    }

    // Direktori untuk menyimpan file
    $upload_dir = "../uploads/informasi/";
    
    // Buat direktori jika belum ada
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }

    // Proses upload file
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $original_name = basename($_FILES['file']['name']);
        $file_name = ($original_name);
        $file_path = $upload_dir . $file_name;
        $file_type = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));

        // Validasi tipe file
        $allowed_types = [
            'image' => ['jpg', 'jpeg', 'png', 'gif', 'webp'],
            'pdf' => ['pdf'],
            'docx' => ['docx', 'doc'],
            'xlsx' => ['xlsx', 'xls']
        ];

        $file_category = null;
        foreach ($allowed_types as $category => $types) {
            if (in_array($file_type, $types)) {
                $file_category = $category;
                break;
            }
        }

        // Jika tipe file tidak valid
        if ($file_category === null) {
            $_SESSION['upload_error'] = "Tipe file tidak diizinkan";
            header("Location: ../admin/upload_informasi.php");
            exit();
        }

        // Batasi ukuran file (misalnya 10MB)
        if ($_FILES['file']['size'] > 10 * 1024 * 1024) {
            $_SESSION['upload_error'] = "Ukuran file terlalu besar (maks 10MB)";
            header("Location: ../admin/upload_informasi.php");
            exit();
        }

        // Pindahkan file yang diupload
        if (move_uploaded_file($_FILES['file']['tmp_name'], $file_path)) {
            // Simpan informasi ke database
            $sql = "INSERT INTO informasi (judul, deskripsi, file_path, file_type) VALUES (?, ?, ?, ?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("ssss", $judul, $deskripsi, $file_name, $file_category);
            
            if ($stmt->execute()) {
                $_SESSION['upload_success'] = "File berhasil diupload";
            } else {
                // Hapus file yang sudah diupload jika gagal menyimpan di database
                unlink($file_path);
                $_SESSION['upload_error'] = "Gagal menyimpan informasi";
            }
        } else {
            $_SESSION['upload_error'] = "Gagal mengupload file";
        }
    } else {
        $_SESSION['upload_error'] = "Kesalahan dalam upload file";
    }

    header("Location: ../admin/upload_informasi.php");
    exit();
}
?>