<?php
session_start();

// Arahkan pengguna kembali ke halaman login
session_unset();  // Menghapus semua variabel sesi
session_destroy();  // Menghancurkan sesi
header("Location: ../auth/login.php");  // Halaman login
exit();

// Jika pengguna ingin logout
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Hapus session
    session_destroy();
    header("Location: ../auth/login.php"); // Arahkan ke halaman login
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>  
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Logout</title>
    <link rel="icon" href="../image/Kemenag_logo-rb.png" type="image/jpg" />
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css" rel="stylesheet" />
</head>
<body class="bg-gray-900 flex items-center justify-center min-h-screen">
    <div class="bg-white rounded-3xl shadow-lg p-6 w-80 relative">
        <div class="flex justify-center mb-4">
            <img src="Kemenag_logo.jpg" 
                 alt="Person with a cat at the door illustration" 
                 class="w-32 h-32" 
                 width="150" 
                 height="150" />
        </div>
        <h2 class="text-center text-xl font-semibold mb-2">
        LOGOUT ?
        </h2>
        <p class="text-center text-gray-600 mb-4">
        Are you sure you want to log out? Click 'Logout' to continue or 'Cancel' to return.
        </p>
        <div class="flex justify-center space-x-4">
            <a href="detail_kunjungan.php" class="bg-white border border-gray-300 text-gray-700 rounded-full px-4 py-2">
                Cancel
            </a>
            <form method="POST">
                <button type="submit" class="bg-red-600 text-white rounded-full px-4 py-2">
                    Log out
                </button>
            </form>
        </div>
    </div>
</body>
</html>
