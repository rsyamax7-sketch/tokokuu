<?php
// dashboard_kasir.php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Proteksi: Pastikan hanya kasir yang bisa mengakses
if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'kasir') {
    header("Location: login.php");
    exit();
}

$nama_kasir = $_SESSION['nama_lengkap'] ?? 'Kasir Toko';

// Ambil statistik ringkasan untuk kasir hari ini
$today = date('Y-m-d');
$q_trx_hari_ini = mysqli_query($koneksi, "SELECT COUNT(*) AS total, SUM(total_belanja) AS omset FROM tbl_transaksi WHERE tanggal = '$today'");
$data_hari_ini = mysqli_fetch_assoc($q_trx_hari_ini);

$total_trx_hari_ini = $data_hari_ini['total'] ?? 0;
$omset_hari_ini     = $data_hari_ini['omset'] ?? 0;

$q_total_produk = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_produk WHERE stok > 0");
$total_produk_tersedia = mysqli_fetch_assoc($q_total_produk)['total'] ?? 0;

// Ambil 5 riwayat transaksi terakhir yang diinput kasir
$query_riwayat = "SELECT t.*, p.nama_pelanggan, p.kota FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan ORDER BY t.id_transaksi DESC LIMIT 5";
$result_riwayat = mysqli_query($koneksi, $query_riwayat);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard Kasir - Toko Kita</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 flex h-screen overflow-hidden">

    <!-- SIDEBAR NAVIGASI KASIR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-6 shrink-0 border-r border-slate-800">
        <div>
            <!-- LOGO BRAND -->
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-emerald-600 text-white font-bold rounded-xl flex items-center justify-center w-10 h-10 shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-cash-register text-base"></i>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base tracking-tight leading-tight">Toko Kita</h2>
                    <span class="text-[11px] text-emerald-400 font-medium tracking-wide uppercase">Panel Kasir</span>
                </div>
            </div>

            <!-- MENU UTAMA KASIR -->
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">Menu Kasir</div>
            <nav class="space-y-1 text-sm font-medium">
                <a href="dashboard_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Dashboard Kasir</span>
                </a>
                <a href="data_produk_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-cart-shopping text-xs text-slate-400"></i>
                    <span>Kasir &amp; Keranjang</span>
                </a>
                <a href="data_pelanggan_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-users text-xs text-slate-400"></i>
                    <span>Data Pelanggan</span>
                </a>
            </nav>
        </div>

        <!-- USER PROFILE & LOGOUT FOOTER -->
        <div class="border-t border-slate-800/80 pt-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-emerald-400 font-bold flex items-center justify-center text-xs border border-slate-700">
                    KS
                </div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-slate-200"><?= htmlspecialchars($nama_kasir); ?></p>
                    <p class="text-emerald-400 text-[11px] mt-0.5 font-medium">Role: Kasir</p>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?')" class="w-full bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Logout / Keluar
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- HEADER TOPBAR -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Dashboard Kasir &amp; Poin Penjualan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Sistem Kasir Toko Kita — Tanggal: <?= date('d M Y'); ?></p>
            </div>
        </header>

        <!-- AREA KONTEN -->
        <main class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- KARTU STATISTIK HARI INI -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Omset Hari Ini</p>
                        <h3 class="text-xl font-bold text-emerald-600 mt-1">Rp <?= number_format($omset_hari_ini, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Transaksi Hari Ini</p>
                        <h3 class="text-xl font-bold text-slate-800 mt-1"><?= $total_trx_hari_ini; ?> <span class="text-xs font-normal text-slate-400">struk</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Produk Siap Jual</p>
                        <h3 class="text-xl font-bold text-slate-800 mt-1"><?= $total_produk_tersedia; ?> <span class="text-xs font-normal text-slate-400">item</span></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-lg shrink-0">
                        <i class="fa-solid fa-box"></i>
                    </div>
                </div>
            </div>

            <!-- BANNER AKSI CEPAT -->
            <div class="bg-gradient-to-r from-emerald-600 to-teal-700 text-white p-6 rounded-2xl shadow-md flex flex-col md:flex-row items-center justify-between gap-4">
                <div>
                    <h3 class="font-bold text-base">Siap Melayani Pelanggan?</h3>
                    <p class="text-xs text-emerald-100 mt-1">Gunakan fitur keranjang kasir untuk memproses transaksi multi-item dan cetak struk otomatis.</p>
                </div>
                <a href="data_produk.php" class="bg-white text-emerald-800 hover:bg-emerald-50 px-5 py-2.5 rounded-xl font-bold text-xs shadow transition shrink-0 flex items-center gap-2">
                    <i class="fa-solid fa-cart-shopping"></i> Mulai Transaksi Baru
                </a>
            </div>

            <!-- TABEL 5 TRANSAKSI TERAKHIR -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Riwayat Transaksi Terakhir</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Daftar penjualan terbaru yang tercatat di sistem kasir.</p>
                    </div>
                    <a href="data_produk.php" class="text-xs font-bold text-emerald-600 hover:underline">Kelola Kasir &rarr;</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">ID TRX</th>
                                <th class="py-3.5 px-6">Tanggal</th>
                                <th class="py-3.5 px-6">Pelanggan</th>
                                <th class="py-3.5 px-6">Kota</th>
                                <th class="py-3.5 px-6 text-right">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($result_riwayat) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_riwayat)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#TRX-<?= str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6 text-slate-400"><?= $row['tanggal']; ?></td>
                                        <td class="py-4 px-6 text-slate-800 font-semibold"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td class="py-4 px-6"><span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border text-[11px]"><?= htmlspecialchars($row['kota']); ?></span></td>
                                        <td class="py-4 px-6 text-right font-bold text-emerald-600">Rp <?= number_format($row['total_belanja'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada riwayat transaksi hari ini.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

</body>
</html>