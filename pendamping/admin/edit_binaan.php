<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nsm = $_POST['nsm'];
    $nama_madrasah = $_POST['nama_madrasah'];
    $selected_pendamping = $_POST['selected_pendamping'];

    $sql = "UPDATE lembaga_binaan SET nsm = ? WHERE nsm = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("ss", $nama_madrasah, $nsm);
    $stmt->execute();

    header("Location: info_binaan.php?pendamping=" . urlencode($selected_pendamping));
    exit();
}
?>