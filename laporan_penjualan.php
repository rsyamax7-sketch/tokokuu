<?php
// laporan_penjualan.php (Panel Admin)
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Pastikan hanya admin yang bisa mengakses laporan penuh
$role_login = $_SESSION['role'] ?? 'admin';
if ($role_login !== 'admin') {
    header("Location: dashboard_kasir.php");
    exit();
}

$nama_user = $_SESSION['nama_lengkap'] ?? 'Administrator';

// Ambil data rekapitulasi transaksi dari database
$query_laporan = "SELECT t.id_transaksi, t.tanggal, t.total_belanja, p.nama_pelanggan, p.kota 
                  FROM tbl_transaksi t 
                  JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan 
                  ORDER BY t.id_transaksi DESC";
$result_laporan = mysqli_query($koneksi, $query_laporan);

// Hitung total keseluruhan pendapatan
$query_total = "SELECT SUM(total_belanja) as total_pendapatan, COUNT(id_transaksi) as total_trx FROM tbl_transaksi";
$result_total = mysqli_query($koneksi, $query_total);
$data_sum = mysqli_fetch_assoc($result_total);
$total_pendapatan = $data_sum['total_pendapatan'] ?? 0;
$total_trx = $data_sum['total_trx'] ?? 0;
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Laporan Penjualan - Panel Admin Toko Kita</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @media print {
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { position: absolute; left: 0; top: 0; width: 100%; }
            .no-print { display: none; }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 flex h-screen overflow-hidden">

    <!-- SIDEBAR ADMIN (INDIGO) -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-6 shrink-0 border-r border-slate-800 no-print">
        <div>
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-indigo-600 text-white font-bold rounded-xl flex items-center justify-center w-10 h-10 shadow-lg shadow-indigo-500/20">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base tracking-tight leading-tight">Toko Kita</h2>
                    <span class="text-[11px] text-indigo-400 font-medium tracking-wide uppercase">Panel Admin</span>
                </div>
            </div>

            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">Menu Utama</div>
            <nav class="space-y-1 text-sm font-medium">
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
                    <span>Dashboard</span>
                </a>
                <a href="laporan_penjualan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                    <i class="fa-solid fa-receipt text-xs"></i>
                    <span>Laporan Penjualan</span>
                </a>
                <a href="data_produk.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-box-archive text-xs text-slate-400"></i>
                    <span>Data Produk &amp; Kasir</span>
                </a>
                <a href="data_pelanggan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-users text-xs text-slate-400"></i>
                    <span>Data Pelanggan</span>
                </a>
            </nav>
        </div>

        <div class="border-t border-slate-800/80 pt-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-indigo-400 font-bold flex items-center justify-center text-xs border border-slate-700">AD</div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-slate-200"><?= htmlspecialchars($nama_user); ?></p>
                    <p class="text-indigo-400 text-[11px] mt-0.5 capitalize">Role: admin</p>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')" class="w-full bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0 no-print">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Laporan Penjualan Keseluruhan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Rekapitulasi transaksi keluar masuk toko.</p>
            </div>
            <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                <i class="fa-solid fa-print text-[10px]"></i> Cetak Laporan
            </button>
        </header>

        <main class="flex-1 overflow-y-auto p-8 space-y-6" id="printArea">
            
            <!-- KARTU STATISTIK RINGKASAN -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Pendapatan</p>
                        <h3 class="text-2xl font-bold text-indigo-600 mt-1">Rp <?= number_format($total_pendapatan, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-xs font-bold text-slate-400 uppercase tracking-wider">Total Transaksi Berhasil</p>
                        <h3 class="text-2xl font-bold text-slate-800 mt-1"><?= number_format($total_trx, 0, ',', '.'); ?> Transaksi</h3>
                    </div>
                    <div class="w-12 h-12 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-lg font-bold">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>
            </div>

            <!-- TABEL LAPORAN TRANSAKSI -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100">
                    <h3 class="font-bold text-slate-800 text-sm">Riwayat Transaksi Pelanggan</h3>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">ID Transaksi</th>
                                <th class="py-3.5 px-6">Tanggal</th>
                                <th class="py-3.5 px-6">Nama Pelanggan</th>
                                <th class="py-3.5 px-6">Kota</th>
                                <th class="py-3.5 px-6 text-right">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($result_laporan) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_laporan)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#TRX-<?= str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6"><?= htmlspecialchars($row['tanggal']); ?></td>
                                        <td class="py-4 px-6 font-semibold text-slate-800"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td class="py-4 px-6">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px] font-semibold">
                                                <?= htmlspecialchars($row['kota']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right font-bold text-indigo-600">Rp <?= number_format($row['total_belanja'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data transaksi penjualan.</td>
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