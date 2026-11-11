<?php
// tambah_produk_kasir.php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$_SESSION['role'] = 'kasir';
$role_login = 'kasir';
$nama_user  = $_SESSION['nama_lengkap'] ?? 'Kasir Toko';

// Ambil data kategori untuk dropdown
$query_kategori = "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);

// PROSES TAMBAH PRODUK DENGAN UPLOAD GAMBAR
if (isset($_POST['tambah_produk'])) {
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $id_kategori = intval($_POST['id_kategori']);
    $harga       = floatval($_POST['harga']);
    $stok        = intval($_POST['stok']);
    
    $nama_file_gambar = null;

    // Tangani proses upload file gambar
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
        $file_tmp  = $_FILES['gambar']['tmp_name'];
        $file_name = $_FILES['gambar']['name'];
        $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
        
        $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
        if (in_array($file_ext, $allowed_ext)) {
            $nama_file_gambar = 'prod_' . time() . '.' . $file_ext;
            // Pastikan folder 'uploads/' ada di dalam folder tokoku
            move_uploaded_file($file_tmp, 'uploads/' . $nama_file_gambar);
        }
    }

    if (!empty($nama_produk) && $id_kategori > 0 && $harga >= 0 && $stok >= 0) {
        $query = "INSERT INTO tbl_produk (nama_produk, id_kategori, harga, stok, gambar) VALUES ('$nama_produk', '$id_kategori', '$harga', '$stok', " . ($nama_file_gambar ? "'$nama_file_gambar'" : "NULL") . ")";
        if (mysqli_query($koneksi, $query)) {
            header("Location: data_produk_kasir.php?status=sukses_tambah");
            exit();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tambah Produk &amp; Gambar - Toko Kita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 flex h-screen overflow-hidden">

    <!-- SIDEBAR KASIR -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-6 shrink-0 border-r border-slate-800">
        <div>
            <div class="flex items-center gap-3 mb-8">
                <div class="bg-emerald-600 text-white font-bold rounded-xl flex items-center justify-center w-10 h-10 shadow-lg shadow-emerald-500/20">
                    <i class="fa-solid fa-cash-register text-base"></i>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base tracking-tight leading-tight">Toko Kita</h2>
                    <span class="text-[11px] text-emerald-400 font-medium tracking-wide uppercase">Panel Kasir</span>
                </div>
            </div>

            <nav class="space-y-1 text-sm font-medium">
                <a href="dashboard_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
                    <span>Dashboard Kasir</span>
                </a>
                <a href="data_produk_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                    <i class="fa-solid fa-cart-shopping text-xs"></i>
                    <span>Kasir &amp; Keranjang</span>
                </a>
                <a href="data_pelanggan_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                    <i class="fa-solid fa-users text-xs text-slate-400"></i>
                    <span>Data Pelanggan</span>
                </a>
            </nav>
        </div>

        <div class="border-t border-slate-800/80 pt-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 text-emerald-400 font-bold flex items-center justify-center text-xs border border-slate-700">KS</div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-slate-200"><?= htmlspecialchars($nama_user); ?></p>
                    <p class="text-emerald-400 text-[11px] mt-0.5 capitalize">Role: kasir</p>
                </div>
            </div>
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar?')" class="w-full bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Tambah Produk Baru (Kasir)</h1>
                <p class="text-xs text-slate-400 mt-0.5">Input data produk beserta gambar produk.</p>
            </div>
            <a href="data_produk_kasir.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-arrow-left text-[10px]"></i> Kembali ke Kasir
            </a>
        </header>

        <main class="flex-1 overflow-y-auto p-8">
            <div class="max-w-xl mx-auto bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 bg-slate-50">
                    <h3 class="font-bold text-slate-800 text-sm">Formulir Tambah Barang &amp; Gambar</h3>
                </div>
                
                <!-- ATRIBUT enctype="multipart/form-data" DITAMBAHKAN AGAR BISA UPLOAD GAMBAR -->
                <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Nama Produk</label>
                        <input type="text" name="nama_produk" required placeholder="Contoh: Sabun Mandi" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Kategori Produk</label>
                        <select name="id_kategori" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800 bg-white">
                            <option value="" disabled selected>-- Pilih Kategori --</option>
                            <?php while ($kat = mysqli_fetch_assoc($result_kategori)): ?>
                                <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                            <?php endwhile; ?>
                        </select>
                    </div>
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                            <input type="number" name="harga" min="0" step="500" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                        </div>
                        <div>
                            <label class="block font-semibold text-slate-600 mb-1">Stok Awal (Pcs)</label>
                            <input type="number" name="stok" min="0" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                        </div>
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Foto / Gambar Produk (Opsional)</label>
                        <input type="file" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl outline-none text-slate-800 bg-slate-50">
                        <p class="text-[10px] text-slate-400 mt-1">Format yang diizinkan: JPG, JPEG, PNG, WEBP.</p>
                    </div>

                    <div class="pt-4 flex items-center justify-end gap-3 border-t border-slate-100">
                        <a href="data_produk_kasir.php" class="px-4 py-2.5 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</a>
                        <button type="submit" name="tambah_produk" class="px-6 py-2.5 font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">Simpan Produk</button>
                    </div>
                </form>
            </div>
        </main>
    </div>
</body>
</html>