<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php"); // Arahkan ke halaman login jika belum login
    exit();
}

// Ambil PEGID dari session
$pegid = $_SESSION['pendamping']['PEGID'];

// Query untuk update data pendamping
$sql = "UPDATE pendamping SET password = ? WHERE PEGID = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("si", $_POST['password'], $pegid);
$stmt->execute();

// Tutup koneksi
$stmt->close();
$conn->close();

// Arahkan ke halaman profile
header("Location: profile.php");
exit();
?>