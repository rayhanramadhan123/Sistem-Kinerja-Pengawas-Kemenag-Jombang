<?php
session_start();
require __DIR__ . '/../config/koneksi.php'; // File untuk koneksi ke database

$error = "";


// Cek jika form disubmit
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['username'];
    $password = $_POST['password'];

    // Validasi input
    if (empty($username) || empty($password)) {
        $error = "Username dan Password harus diisi.";
    } else {
        // Query untuk login admin
        $stmt_admin = $conn->prepare("SELECT * FROM admin WHERE username_admin = ? AND password_admin = ?");
        $stmt_admin->bind_param("ss", $username, $password); // Gunakan "ss" karena kedua kolom bertipe string
        $stmt_admin->execute();
        $result_admin = $stmt_admin->get_result();

        // Jika ditemukan admin, proses login sebagai admin
        if ($result_admin->num_rows > 0) {
            $admin = $result_admin->fetch_assoc();
            $_SESSION['loggedin'] = true;
            $_SESSION['role'] = 'admin';
            $_SESSION['admin'] = $admin;

            // Arahkan ke halaman admin dashboard
            header("Location: ../admin/beranda_admin.php");
            exit();
        }

        // Query untuk login pendamping
        $stmt_pendamping = $conn->prepare("SELECT * FROM pendamping WHERE Nama_pendamping = ? AND password = ?");
        $stmt_pendamping->bind_param("ss", $username, $password); // Gunakan "ss" karena kedua kolom bertipe string
        $stmt_pendamping->execute();
        $result_pendamping = $stmt_pendamping->get_result();

        // Jika ditemukan pendamping, proses login sebagai pendamping
        if ($result_pendamping->num_rows > 0) {
            $pendamping = $result_pendamping->fetch_assoc();
            $_SESSION['loggedin'] = true;
            $_SESSION['role'] = 'pendamping';
            $_SESSION['pendamping'] = $pendamping;

            // Arahkan ke halaman beranda user
            header("Location: ../user/beranda.php");
            exit();
        } else {
            // Jika tidak ditemukan baik admin maupun pendamping
            $error = "Username atau Password salah.";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="utf-8"/>
    <meta content="width=device-width, initial-scale=1.0" name="viewport"/>
    <title>Login</title>
    <link href="../image/Kemenag_logo-rb.png" rel="icon" type="Kemenag_logo/jpg"/>
    <script src="https://cdn.tailwindcss.com"></script>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet"/>
    <style>
        @keyframes fadeIn {
            from {
                opacity: 0;
            }
            to {
                opacity: 1;
            }
        }

        @keyframes slideIn {
            from {
                transform: translateX(40px);
                opacity: 0;
            }
            to {
                transform: translateX(0);
                opacity: 1;
            }
        }

        .gradient-mask {
            background: linear-gradient(
                to right,
                rgba(255, 255, 255, 0) 0%,
                rgba(255, 255, 255, 0) 20%,
                rgba(255, 255, 255, 0.5) 70%,
                rgba(255, 255, 255, 0.8) 85%,
                rgba(255, 255, 255, 1) 100%
            );
        }

        .animate-fade {
            animation: fadeIn 0.8s ease-out;
        }

        .animate-slide {
            animation: slideIn 0.8s ease-out;
        }

        .form-input {
            transition: all 0.3s ease;
        }

        .form-input:focus {
            border-color: #4f46e5;
            box-shadow: 0 0 0 3px rgba(79, 70, 229, 0.2);
        }

        .login-button {
            background: linear-gradient(45deg, #4f46e5, #6366f1);
            transition: all 0.3s ease;
        }

        .login-button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(79, 70, 229, 0.4);
        }

        .avatar-container {
            width: 96px;
            height: 96px;
            position: relative;
            overflow: hidden;
            border-radius: 50%;
            border: 4px solid white;
            box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.1);
        }

        .avatar-image {
            width: 100%;
            height: 100%;
            object-fit: cover;
            object-position: center;
        }

        @media (max-width: 768px) {
            .background-section {
                display: none;
            }

            .form-section {
                background: url('../image/kantor.jpg') no-repeat center center;
                background-size: cover;
            }

            .form-container {
                background: rgba(255, 255, 255, 0.9);
                padding: 2rem;
                border-radius: 0.5rem;
            }
        }
    </style>
    <script>
        // Fungsi untuk menampilkan pop-up
        function showAlert(message) {
            alert(message);
        }
    </script>
</head>
<body class="min-h-screen bg-gray-50">
<div class="flex min-h-screen">
    <div class="background-section hidden md:flex md:w-1/2 relative overflow-hidden">
        <div class="absolute inset-0 bg-indigo-600">
            <img alt="Background image of an office building with a modern design" class="w-full h-full object-cover opacity-80" height="600" src="../image/kantor.jpg" width="800"/>
        </div>
        <div class="absolute inset-0 gradient-mask"></div>
        <div class="relative z-10 p-12 text-white">
            <h1 class="text-4xl font-bold mb-4 animate-fade">Selamat Datang!</h1>
            <p class="text-lg opacity-90 animate-fade" style="animation-delay: 0.2s">
                Ini adalah website data dan bukti kerja bagi pendamping.
                <br>Login untuk Melanjutkan.</br>
            </p>
        </div>
    </div>
    <div class="form-section w-full md:w-1/2 flex items-center justify-center p-8">
        <div class="form-container w-full max-w-md space-y-8">
            <div class="text-center">
                <div class="avatar-container mx-auto mb-6">
                    <img alt="Logo of Kemenag" class="avatar-image" height="96" src="../image/Kemenag_logo.jpg" width="96"/>
                </div>
                <h2 class="text-3xl font-bold text-gray-900 mb-2">Masukkan Akun</h2>
                <p class="text-gray-600">Tolong Login untuk Melanjutkan</p>
            </div>
            <form action="login.php" class="mt-8 space-y-6" method="POST">
                <div class="space-y-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="username">Username</label>
                        <input class="form-input block w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" 
                         id="username" name="username" placeholder="Masukkan Username" required="" type="text"/>
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700 mb-2" for="password">Password</label>
                        <div class="relative">
                            <input class="form-input block w-full py-2 px-4 border border-gray-300 rounded-lg focus:outline-none focus:ring-2 focus:ring-blue-500" id="password" name="password" placeholder="Masukkan Password" required="" type="password"/>
                            <span class="absolute inset-y-0 right-0 flex items-center pr-3">
                                <input class="form-checkbox" id="show-password" onclick="togglePasswordVisibility()" type="checkbox"/>
                                <label class="ml-2 text-sm text-gray-600" for="show-password">Tampilkan</label>
                            </span>
                        </div>
                    </div>
                </div>
                <div>
                    <button class="login-button w-full py-3 px-4 rounded-lg text-white font-medium focus:outline-none" type="submit">Log in</button>
                </div>
                <?php if (!empty($error)): ?>
                    <script>
                        showAlert("<?php echo $error; ?>");
                    </script>
                <?php endif; ?>
            </form>
        </div>
    </div>
</div>
<script>
    function togglePasswordVisibility() {
        const passwordInput = document.getElementById('password');
        const showPasswordCheckbox = document.getElementById('show-password');
        passwordInput.type = showPasswordCheckbox.checked ? 'text' : 'password';
    }
</script>
</body>
</html>