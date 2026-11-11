<?php
// index.php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah sudah login sebagai admin (opsional proteksi)
// if (!isset($_SESSION['role']) || $_SESSION['role'] !== 'admin') {
//     header("Location: login.php");
//     exit();
// }

// PROSES EDIT TRANSAKSI
if (isset($_POST['edit_transaksi'])) {
    $id_trx  = intval($_POST['id_transaksi']);
    $tgl     = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $total   = floatval($_POST['total_belanja']);

    if ($id_trx > 0 && $total > 0) {
        mysqli_query($koneksi, "UPDATE tbl_transaksi SET tanggal = '$tgl', total_belanja = '$total' WHERE id_transaksi = $id_trx");
        header("Location: index.php?status=sukses_edit");
        exit();
    }
}

// PROSES HAPUS TRANSAKSI
if (isset($_GET['hapus'])) {
    $id_trx = intval($_GET['hapus']);
    if ($id_trx > 0) {
        mysqli_query($koneksi, "DELETE FROM tbl_detail_transaksi WHERE id_transaksi = $id_trx");
        mysqli_query($koneksi, "DELETE FROM tbl_transaksi WHERE id_transaksi = $id_trx");
        header("Location: index.php?status=sukses_hapus");
        exit();
    }
}

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
                 <a href="laporan_penjualan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-receipt text-xs text-slate-400"></i>
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

        <!-- USER PROFILE & LOGOUT FOOTER -->
        <div class="border-t border-slate-800/80 pt-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-indigo-400 font-bold flex items-center justify-center text-xs border border-slate-700">
                    AD
                </div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-slate-200"><?= $_SESSION['nama_lengkap'] ?? 'Administrator'; ?></p>
                    <p class="text-slate-400 text-[11px] mt-0.5">admin@tokokita.com</p>
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
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Dashboard &amp; Laporan Penjualan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Basis Data: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">db_tokoku</code></p>
            </div>
        </header>

        <!-- AREA KONTEN (SCROLLABLE) -->
        <main class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- NOTIFIKASI -->
            <?php if (isset($_GET['status'])): ?>
                <div class="bg-emerald-50 border border-emerald-200/70 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span><strong>Sukses!</strong> Aksi transaksi berhasil diproses.</span>
                    </div>
                    <a href="index.php" class="text-emerald-600 hover:text-emerald-900"><i class="fa-solid fa-xmark text-sm"></i></a>
                </div>
            <?php endif; ?>

            <!-- KARTU STATISTIK -->
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

            <!-- GRAFIK -->
            <div class="bg-white p-6 rounded-2xl border border-slate-200/70 shadow-2xs">
                <div class="flex items-center justify-between mb-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Grafik Analisis Tren Penjualan</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Visualisasi omset bulanan sistem toko</p>
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
                                <th class="py-3.5 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($data_transaksi) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($data_transaksi)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#TRX-<?= str_pad($row['id_transaksi'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6 text-slate-400"><?= $row['tanggal']; ?></td>
                                        <td class="py-4 px-6 text-slate-800 font-semibold"><?= htmlspecialchars($row['nama_pelanggan']); ?></td>
                                        <td class="py-4 px-6"><span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px]"><?= htmlspecialchars($row['kota']); ?></span></td>
                                        <td class="py-4 px-6 text-right font-bold text-indigo-600">Rp <?= number_format($row['total_belanja'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-center space-x-2">
                                            <button onclick="bukaModalEdit(<?= $row['id_transaksi']; ?>, '<?= $row['tanggal']; ?>', <?= $row['total_belanja']; ?>)" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-2.5 py-1.5 rounded-lg border border-amber-200 transition font-semibold" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <a href="index.php?hapus=<?= $row['id_transaksi']; ?>" onclick="return confirm('Yakin ingin menghapus riwayat transaksi ini?')" class="bg-red-50 hover:bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg border border-red-200 transition font-semibold inline-block" title="Hapus">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Belum ada data transaksi.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL EDIT TRANSAKSI -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Edit Data Transaksi</h3>
                <button onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" id="editIdTrx" name="id_transaksi">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Tanggal Transaksi</label>
                    <input type="date" id="editTanggal" name="tanggal" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Total Belanja (Rp)</label>
                    <input type="number" id="editTotal" name="total_belanja" min="0" step="500" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800 font-bold">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="tutupModalEdit()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" name="edit_transaksi" class="px-5 py-2 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- SCRIPT GRAFIK & MODAL -->
    <script>
        function bukaModalEdit(id, tanggal, total) {
            document.getElementById('editIdTrx').value = id;
            document.getElementById('editTanggal').value = tanggal;
            document.getElementById('editTotal').value = total;
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function tutupModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

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
                plugins: { legend: { display: false } },
                scales: {
                    x: { grid: { display: false }, ticks: { font: { size: 10 } } },
                    y: {
                        grid: { color: '#f1f5f9' },
                        ticks: {
                            font: { size: 10 },
                            callback: function(value) { return 'Rp ' + (value / 1000000).toFixed(1) + 'jt'; }
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>