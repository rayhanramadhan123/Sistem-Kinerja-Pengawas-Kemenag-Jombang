<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil data dari form
$pegid = $_POST['pegid'] ?? null;
$lembaga = $_POST['pilih_lembaga'] ?? [];

// Validasi input
if ($pegid && !empty($lembaga)) {
    // Siapkan query untuk memasukkan data
    $sql_insert = "INSERT INTO lembaga_binaan (PEGID, nsm) VALUES (?, ?)";
    $stmt_insert = $conn->prepare($sql_insert);

    foreach ($lembaga as $nsm) {
        $stmt_insert->bind_param("is", $pegid, $nsm);
        $stmt_insert->execute();
    }

    $stmt_insert->close();
    header("Location: info_binaan.php?success=1"); // Redirect dengan pesan sukses
} else {
    header("Location: info_binaan.php?error=1"); // Redirect dengan pesan error
}
?>  