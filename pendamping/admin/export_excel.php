<?php
session_start();
require __DIR__ . '/../config/koneksi.php';

// Pastikan pengguna sudah login sebelum mengakses halaman ini
if (!isset($_SESSION['loggedin'])) {
    header("Location: login.php");
    exit();
}

// Ambil pendamping yang dipilih
$selected_pendamping = isset($_POST['pendamping']) ? $_POST['pendamping'] : null;

// Ambil nama pendamping berdasarkan PEGID
$nama_pendamping_sql = "SELECT Nama_pendamping FROM pendamping WHERE PEGID = ?";
$nama_stmt = $conn->prepare($nama_pendamping_sql);
$nama_stmt->bind_param("i", $selected_pendamping);
$nama_stmt->execute();
$nama_stmt->bind_result($nama_pendamping);
$nama_stmt->fetch();
$nama_stmt->close();

// Ambil data kunjungan untuk pendamping yang dipilih
$sql = "SELECT 
            p.Nama_pendamping,
            l.nama_madrasah,
            COUNT(k.id_kunjungan) AS jumlah_kunjungan
        FROM pendamping p
        JOIN lembaga_binaan lb ON p.PEGID = lb.PEGID
        JOIN lembaga l ON lb.nsm = l.nsm
        LEFT JOIN kunjungan k ON lb.id_binaan = k.id_binaan";

if ($selected_pendamping) {
    $sql .= " WHERE p.PEGID = ?";
}

$sql .= " GROUP BY p.PEGID, p.Nama_pendamping, l.nsm, l.nama_madrasah";

$stmt = $conn->prepare($sql);
if ($selected_pendamping) {
    $stmt->bind_param("i", $selected_pendamping);
}
$stmt->execute();
$result = $stmt->get_result();

// Set header untuk mengunduh file Excel dengan nama pendamping
header("Content-Type: application/vnd.ms-excel");
header("Content-Disposition: attachment; filename=Laporan_Kinerja_{$nama_pendamping}.xls");

// Tampilkan data dalam format tabel
echo "<table border='1'>
        <tr>
            <th>No</th>
            <th>Nama Pendamping</th>
            <th>Madrasah</th>
            <th>Jumlah Kunjungan</th>
        </tr>";

$no = 1;
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td>{$no}</td>
            <td>" . htmlspecialchars($row["Nama_pendamping"]) . "</td>
            <td>" . htmlspecialchars($row["nama_madrasah"]) . "</td>
            <td>" . htmlspecialchars($row["jumlah_kunjungan"]) . "</td>
          </tr>";
    $no++;
}

echo "</table>";
exit();