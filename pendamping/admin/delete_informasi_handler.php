<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

$ids = json_decode($_POST['ids'], true);

if (empty($ids)) {
    echo json_encode(['success' => false, 'message' => 'Tidak ada item yang dipilih!']);
    exit();
}

foreach ($ids as $id) {
    $sql = "DELETE FROM informasi WHERE id = '$id'";
    $result = $conn->query($sql);
    if (!$result) {
        echo json_encode(['success' => false, 'message' => 'Gagal menghapus item!']);
        exit();
    }
}

echo json_encode(['success' => true, 'message' => 'Item berhasil dihapus!']);
?>