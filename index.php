<?php
// index.php
require_once 'koneksi.php';

// Ambil ringkasan data dari database
$q_total_trx  = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_transaksi");
$total_trx    = mysqli_fetch_assoc($q_total_trx)['total'];

$q_total_omset = mysqli_query($koneksi, "SELECT SUM(total_belanja) AS omset FROM tbl_transaksi");
$total_omset  = mysqli_fetch_assoc($q_total_omset)['omset'];

$q_total_cust  = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_pelanggan");
$total_cust   = mysqli_fetch_assoc($q_total_cust)['total'];

$q_total_prod  = mysqli_query($koneksi, "SELECT COUNT(*) AS total FROM tbl_produk");
$total_prod   = mysqli_fetch_assoc($q_total_prod)['total'];

// Ambil daftar transaksi dari VIEW
$query_view = "SELECT * FROM view_transaksi_pelanggan ORDER BY id_transaksi DESC LIMIT 10";
$data_transaksi = mysqli_query($koneksi, $query_view);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Informasi Penjualan Toko Kita</title>
    <!-- Tailwind CSS -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 flex h-screen overflow-hidden">

    <!-- SIDEBAR NAVIGASI MODERN -->
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
                <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                    <i class="fa-solid fa-chart-pie text-xs"></i>
                    <span>Dashboard</span>
                </a>
                <a href="#tabel-transaksi" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-receipt text-xs text-slate-400"></i>
                    <span>Laporan Penjualan</span>
                </a>
                <a href="data_produk.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-box-archive text-xs text-slate-400"></i>
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
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Dashboard Ringkasan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Database Terhubung: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">db_tokoku</code></p>
            </div>
            <button onclick="bukaModalTambah()" class="bg-indigo-600 hover:bg-indigo-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm shadow-indigo-600/20 transition">
                <i class="fa-solid fa-plus text-[10px]"></i> Tambah Transaksi
            </button>
        </header>

        <!-- AREA KONTEN (SCROLLABLE) -->
        <main class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- NOTIFIKASI BERHASIL -->
            <?php if (isset($_GET['status']) && $_GET['status'] === 'success'): ?>
                <div class="bg-emerald-50 border border-emerald-200/70 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span><strong>Sukses!</strong> Data transaksi baru berhasil dicatat ke database.</span>
                    </div>
                    <a href="index.php" class="text-emerald-600 hover:text-emerald-900"><i class="fa-solid fa-xmark text-sm"></i></a>
                </div>
            <?php endif; ?>

            <!-- KARTU STATISTIK (4 GRID) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-5">
                <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Omset</p>
                        <h3 class="text-lg font-bold text-slate-800 mt-1">Rp <?= number_format($total_omset ?? 0, 0, ',', '.'); ?></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-wallet"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Transaksi</p>
                        <h3 class="text-lg font-bold text-slate-800 mt-1"><?= $total_trx; ?> <span class="text-xs font-normal text-slate-400">trx</span></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-blue-50 text-blue-600 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-receipt"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Total Pelanggan</p>
                        <h3 class="text-lg font-bold text-slate-800 mt-1"><?= $total_cust; ?> <span class="text-xs font-normal text-slate-400">orang</span></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-emerald-50 text-emerald-600 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-users"></i>
                    </div>
                </div>

                <div class="bg-white p-5 rounded-2xl border border-slate-200/70 shadow-2xs flex items-center justify-between">
                    <div>
                        <p class="text-[11px] font-bold text-slate-400 uppercase tracking-wider">Katalog Produk</p>
                        <h3 class="text-lg font-bold text-slate-800 mt-1"><?= $total_prod; ?> <span class="text-xs font-normal text-slate-400">item</span></h3>
                    </div>
                    <div class="w-11 h-11 rounded-xl bg-amber-50 text-amber-600 flex items-center justify-center text-base shrink-0">
                        <i class="fa-solid fa-box-archive"></i>
                    </div>
                </div>
            </div>

            <!-- SECTION GRAFIK ANALISIS -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Grafik Analisis Tren Penjualan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Visualisasi omset bulanan sistem toko</p>
                    </div>
                    <div class="flex items-center gap-2 text-[11px] font-semibold text-slate-500 bg-slate-50 px-3 py-1.5 rounded-xl border border-slate-200/60">
                        <span class="w-2.5 h-2.5 rounded-full bg-indigo-600 inline-block"></span> Omset Bulanan
                    </div>
                </div>
                <div class="h-64">
                    <canvas id="grafikPenjualan"></canvas>
                </div>
            </div>

            <!-- TABEL LAPORAN (VIEW) -->
            <div id="tabel-transaksi" class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Riwayat Transaksi Penjualan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Sumber Data: View <code class="text-indigo-600 font-mono">view_transaksi_pelanggan</code></p>
                    </div>
                    <span class="bg-indigo-50 text-indigo-700 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-indigo-100">Live Database</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3 px-6">ID TRX</th>
                                <th class="py-3 px-6">Tanggal</th>
                                <th class="py-3 px-6">Pelanggan</th>
                                <th class="py-3 px-6">Kota</th>
                                <th class="py-3 px-6 text-right">Total Belanja</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($data_transaksi) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($data_transaksi)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-3.5 px-6 font-bold text-slate-800">#TRX-<?= str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-3.5 px-6 text-slate-400"><?= $row['tanggal']; ?></td>
                                        <td class="py-3.5 px-6 text-slate-800 font-semibold"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td class="py-3.5 px-6"><span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px]"><?= htmlspecialchars($row['kota']); ?></span></td>
                                        <td class="py-3.5 px-6 text-right font-bold text-indigo-600">Rp <?= number_format($row['total_belanja'], 0, ',', '.'); ?></td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL FORM TAMBAH TRANSAKSI -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Tambah Transaksi Baru</h3>
                <button onclick="tutupModalTambah()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="tambah_transaksi.php" method="POST" class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" required placeholder="Masukkan nama pelanggan" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Kota</label>
                    <input type="text" name="kota" required placeholder="Contoh: Ponorogo, Madiun" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" name="tanggal" required value="<?= date('Y-m-d'); ?>" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Total Belanja (Rp)</label>
                    <input type="number" name="total_belanja" min="0" step="1000" required placeholder="Contoh: 150000" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3">
                    <button type="button" onclick="tutupModalTambah()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" class="px-4 py-2 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Simpan Transaksi</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT & CHART.JS -->
    <script>
        function bukaModalTambah() {
            document.getElementById('modalTambah').classList.remove('hidden');
        }
        function tutupModalTambah() {
            document.getElementById('modalTambah').classList.add('hidden');
        }

        // Inisialisasi Grafik Chart.js
        const ctx = document.getElementById('grafikPenjualan').getContext('2d');
        new Chart(ctx, {
            type: 'line',
            data: {
                labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov'],
                datasets: [{
                    label: 'Omset Penjualan (Rp)',
                    data: [1500000, 2200000, 1800000, 3100000, 2700000, 3500000, 4100000, 3800000, 4500000, 5200000, 5875000],
                    borderColor: '#4f46e5',
                    backgroundColor: 'rgba(79, 70, 229, 0.05)',
                    borderWidth: 2.5,
                    fill: true,
                    tension: 0.35,
                    pointBackgroundColor: '#4f46e5',
                    pointRadius: 4
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: false }
                },
                scales: {
                    x: {
                        grid: { display: false },
                        ticks: { font: { size: 10 } }
                    },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) {
                                return 'Rp ' + (value / 1000000).toFixed(1) + 'jt';
                            }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>