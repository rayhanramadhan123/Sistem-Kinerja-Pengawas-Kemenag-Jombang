<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nsm = $_POST['nsm'];
    $selected_pendamping = $_POST['selected_pendamping'];

    $sql = "DELETE FROM lembaga_binaan WHERE nsm = ?";
    $stmt = $conn->prepare($sql);
    $stmt->bind_param("s", $nsm);
    $stmt->execute();

    header("Location: info_binaan.php?pendamping=" . urlencode($selected_pendamping));
    exit();
}
?>