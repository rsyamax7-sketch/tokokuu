<?php
// login.php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = mysqli_real_escape_string($koneksi, $_POST['username']);
    $password = mysqli_real_escape_string($koneksi, $_POST['password']);

    // Cek data berdasarkan username dan password di tbl_pengguna
    $query = "SELECT * FROM tbl_pengguna WHERE username = '$username' AND password = '$password'";
    $result = mysqli_query($koneksi, $query);

    if ($result && mysqli_num_rows($result) > 0) {
        $user = mysqli_fetch_assoc($result);
        
        // Simpan sesi login
        $_SESSION['id_pengguna']   = $user['id_pengguna'];
        $_SESSION['nama_lengkap']  = $user['nama_lengkap'];
        $_SESSION['role']          = $user['role']; // admin, kasir, atau pelanggan

        // Redirect otomatis berdasarkan role masing-masing
        if ($user['role'] === 'admin') {
            header("Location: index.php"); // Dashboard Admin
            exit();
        } elseif ($user['role'] === 'kasir') {
            header("Location: dashboard_kasir.php"); // Halaman Kasir / Produk
            exit();
        } elseif ($user['role'] === 'pelanggan') {
            header("Location: katalog_belanja.php"); // Portal Belanja Pelanggan
            exit();
        }
    } else {
        $error = "Username atau password yang kamu masukkan salah!";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login Sistem - Toko Kita</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] flex items-center justify-center h-screen">

    <div class="bg-white p-8 rounded-2xl border border-slate-200 shadow-xl w-full max-w-md">
        <div class="text-center mb-6">
            <div class="bg-indigo-600 text-white font-bold rounded-2xl inline-flex items-center justify-center w-12 h-12 shadow-lg shadow-indigo-500/20 mb-3">
                <i class="fa-solid fa-store text-lg"></i>
            </div>
            <h2 class="font-bold text-slate-800 text-lg">Login Sistem - Toko Kita</h2>
            <p class="text-xs text-slate-400 mt-0.5">Masukkan akun (Admin, Kasir, atau Pelanggan).</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs mb-4 flex items-center gap-2">
                <i class="fa-solid fa-circle-exclamation text-sm"></i>
                <span><?= $error; ?></span>
            </div>
        <?php endif; ?>

        <form action="" method="POST" class="space-y-4 text-xs">
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Username</label>
                <input type="text" name="username" required placeholder="Masukkan username..." class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-slate-800">
            </div>
            <div>
                <label class="block font-semibold text-slate-600 mb-1">Password</label>
                <input type="password" name="password" required placeholder="Masukkan password..." class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 outline-none text-slate-800">
            </div>

            <button type="submit" class="w-full py-3 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">
                Masuk ke Sistem
            </button>
        </form>

        <div class="mt-6 pt-4 border-t border-slate-100 text-[11px] text-slate-400 space-y-1">
            <p class="font-bold text-slate-600">Panduan Akun Demo:</p>
            <div class="grid grid-cols-3 gap-1 pt-1">
                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                    <span class="block font-semibold text-slate-700">Admin</span>
                    <code>admin / admin123</code>
                </div>
                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                    <span class="block font-semibold text-slate-700">Kasir</span>
                    <code>kasir / kasir123</code>
                </div>
                <div class="bg-slate-50 p-2 rounded border border-slate-100">
                    <span class="block font-semibold text-slate-700">Pelanggan</span>
                    <code>rina / rina123</code>
                </div>
            </div>
        </div>
    </div>

</body>
</html>