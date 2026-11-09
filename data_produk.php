<?php
// data_produk.php
require_once 'koneksi.php';

// Ambil data produk digabung dengan kategori menggunakan JOIN
$query_produk = "SELECT p.*, k.nama_kategori 
                 FROM tbl_produk p 
                 LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori 
                 ORDER BY p.id_produk DESC";
$result_produk = mysqli_query($koneksi, $query_produk);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Produk - Toko Kita</title>
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

    <!-- SIDEBAR NAVIGASI -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-6 shrink-0 border-r border-slate-800">
        <div>
            <!-- LOGO BRAND -->
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-indigo-600 text-white font-bold rounded-xl flex items-center justify-center w-10 h-10 shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base tracking-tight leading-tight">Toko Kita</h2>
                    <span class="text-[11px] text-indigo-400 font-medium tracking-wide uppercase">Sistem Penjualan</span>
                </div>
            </div>

            <!-- MENU UTAMA -->
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">Menu Utama</div>
            <nav class="space-y-1 text-sm font-medium">
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="index.php#tabel-transaksi" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-receipt text-xs text-slate-400"></i>
                    <span>Laporan Penjualan</span>
                </a>
                <a href="data_produk.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                    <i class="fa-solid fa-box-archive text-xs"></i>
                    <span>Data Produk</span>
                </a>
                <a href="data_pelanggan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-users text-xs text-slate-400"></i>
                    <span>Data Pelanggan</span>
                </a>
            </nav>
        </div>

        <!-- USER PROFILE FOOTER -->
        <div class="border-t border-slate-800/80 pt-4 flex items-center gap-3">
            <div class="w-9 h-9 rounded-full bg-slate-800 text-indigo-400 font-bold flex items-center justify-center text-xs border border-slate-700">
                AD
            </div>
            <div class="text-xs leading-tight">
                <p class="font-semibold text-slate-200">Administrator</p>
                <p class="text-slate-400 text-[11px] mt-0.5">admin@tokokita.com</p>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- HEADER TOPBAR -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Katalog Produk Toko</h1>
                <p class="text-xs text-slate-400 mt-0.5">Basis Data: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">tbl_produk</code> &amp; <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">tbl_kategori</code></p>
            </div>
            <a href="index.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Dashboard
            </a>
        </header>

        <!-- AREA KONTEN -->
        <main class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- KARTU TABEL PRODUK -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Daftar Seluruh Produk</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Menampilkan informasi lengkap produk beserta kategori dan stok real-time.</p>
                    </div>
                    <span class="bg-indigo-50 text-indigo-700 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-indigo-100">Live Database</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">ID Produk</th>
                                <th class="py-3.5 px-6">Nama Produk</th>
                                <th class="py-3.5 px-6">Kategori</th>
                                <th class="py-3.5 px-6 text-right">Harga Satuan</th>
                                <th class="py-3.5 px-6 text-center">Stok Gudang</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($result_produk) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_produk)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#PROD-<?= str_pad($row['id_produk'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6 text-slate-800 font-semibold flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-lg bg-slate-100 text-slate-500 flex items-center justify-center text-xs shrink-0 font-bold">
                                                <i class="fa-solid fa-box"></i>
                                            </div>
                                            <?= htmlspecialchars($row['nama_produk']); ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px] font-semibold">
                                                <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right font-bold text-indigo-600">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="<?= $row['stok'] <= 5 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'; ?> px-3 py-1 rounded-full border text-[11px] font-bold inline-block">
                                                <?= $row['stok']; ?> Pcs
                                            </span>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data produk.</td>
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