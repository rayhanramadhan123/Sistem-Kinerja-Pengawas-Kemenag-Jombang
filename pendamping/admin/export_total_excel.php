<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Ambil data total kunjungan untuk semua pendamping
$sql = "SELECT 
            p.Nama_pendamping,
            COUNT(k.id_kunjungan) AS total_kunjungan
        FROM pendamping p
        LEFT JOIN lembaga_binaan lb ON p.PEGID = lb.PEGID
        LEFT JOIN kunjungan k ON lb.id_binaan = k.id_binaan
        GROUP BY p.PEGID, p.Nama_pendamping
        ORDER BY total_kunjungan DESC";

$stmt = $conn->prepare($sql);
$stmt->execute();
$result = $stmt->get_result();

// Set headers to force download as Excel
header('Content-Type: application/vnd.ms-excel');
header('Content-Disposition: attachment; filename="raport_kinerja.xls"');

// Tampilkan data dalam format tabel
echo "<table border='1'>
        <tr>
            <th>No</th>
            <th>Nama Pendamping</th>
            <th>Total Kunjungan</th>
        </tr>";

$no = 1;
if ($result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        echo "<tr>
                <td>{$no}</td>
                <td>" . htmlspecialchars($row["Nama_pendamping"]) . "</td>
                <td>" . htmlspecialchars($row["total_kunjungan"]) . "</td>
              </tr>";
        $no++;
    }
} else {
    echo "<tr><td colspan='3'>Tidak ada data.</td></tr>";
}

echo "</table>";
exit();
?>