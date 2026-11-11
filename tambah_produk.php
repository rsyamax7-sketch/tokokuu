<?php
// tambah_produk.php
require_once 'koneksi.php';

// Ambil data kategori untuk pilihan dropdown
$query_kategori = "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);

// Proses jika form disubmit
$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_kategori  = intval($_POST['id_kategori']);
    $nama_produk  = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga        = floatval($_POST['harga']);
    $stok         = intval($_POST['stok']);

    if (!empty($nama_produk) && $harga > 0) {
        $query_insert = "INSERT INTO tbl_produk (id_kategori, nama_produk, harga, stok) 
                         VALUES ('$id_kategori', '$nama_produk', '$harga', '$stok')";
        
        if (mysqli_query($koneksi, $query_insert)) {
            header("Location: data_produk.php?status=success");
            exit();
        } else {
            $error = "Gagal menyimpan produk: " . mysqli_error($koneksi);
        }
    } else {
        $error = "Mohon isi nama produk dan harga dengan benar.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk - Toko Kita</title>
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
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Tambah Produk Baru</h1>
                <p class="text-xs text-slate-400 mt-0.5">Form Input Data ke tabel <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">tbl_produk</code></p>
            </div>
            <a href="data_produk.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali
            </a>
        </header>

        <!-- AREA KONTEN -->
        <main class="flex-1 overflow-y-auto p-8 flex justify-center">
            <div class="w-full max-w-xl">
                
                <?php if (!empty($error)): ?>
                    <div class="mb-4 bg-red-50 border border-red-200 text-red-700 px-4 py-3 rounded-xl text-xs flex items-center gap-2">
                        <i class="fa-solid fa-circle-exclamation text-sm"></i>
                        <span><?= $error; ?></span>
                    </div>
                <?php endif; ?>

                <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                    <div class="px-6 py-4 border-b border-slate-100 bg-slate-50/50">
                        <h3 class="font-bold text-slate-800 text-sm">Formulir Katalog Produk</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Lengkapi informasi detail produk di bawah ini.</p>
                    </div>

                    <form action="" method="POST" class="p-6 space-y-4 text-xs">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Nama Produk</label>
                            <input type="text" name="nama_produk" required placeholder="Contoh: Mechanical Keyboard" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                        </div>

                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Kategori Produk</label>
                            <select name="id_kategori" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800 bg-white">
                                <option value="" disabled selected>Pilih Kategori Produk</option>
                                <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                    <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                                <?php endwhile; ?>
                            </select>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                                <input type="number" name="harga" min="0" step="500" required placeholder="Contoh: 250000" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Stok Awal (Pcs)</label>
                                <input type="number" name="stok" min="0" required placeholder="Contoh: 15" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                            </div>
                        </div>

                        <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                            <a href="data_produk.php" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</a>
                            <button type="submit" class="px-5 py-2 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Simpan Produk</button>
                        </div>
                    </form>
                </div>

            </div>
        </main>
    </div>

</body>
</html>