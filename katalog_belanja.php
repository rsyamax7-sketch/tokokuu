<?php
// katalog_belanja.php
require_once 'koneksi.php';
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Cek apakah sudah login sebagai pelanggan
if (!isset($_SESSION['id_pelanggan_login']) || $_SESSION['role_login'] !== 'pelanggan') {
    header("Location: login_pelanggan.php");
    exit();
}

$nama_user = $_SESSION['nama_pelanggan_login'];

// Inisialisasi keranjang belanja pelanggan
if (!isset($_SESSION['keranjang_pelanggan'])) {
    $_SESSION['keranjang_pelanggan'] = [];
}

// Tambah ke keranjang
if (isset($_POST['beli_produk'])) {
    $id_prod = intval($_POST['id_produk']);
    $qty     = intval($_POST['qty']);

    $q = mysqli_query($koneksi, "SELECT * FROM tbl_produk WHERE id_produk = $id_prod");
    $p = mysqli_fetch_assoc($q);

    if ($p && $qty > 0 && $qty <= $p['stok']) {
        $found = false;
        foreach ($_SESSION['keranjang_pelanggan'] as &$item) {
            if ($item['id_produk'] == $id_prod) {
                $item['qty'] += $qty;
                $found = true;
                break;
            }
        }
        unset($item);

        if (!$found) {
            $_SESSION['keranjang_pelanggan'][] = [
                'id_produk'   => $p['id_produk'],
                'nama_produk' => $p['nama_produk'],
                'harga'       => $p['harga'],
                'qty'         => $qty
            ];
        }
        header("Location: katalog_belanja.php?status=sukses_cart");
        exit();
    }
}

// Proses Checkout Mandiri oleh Pelanggan
if (isset($_POST['checkout_mandiri'])) {
    $id_user_login = $_SESSION['id_pelanggan_login'];
    $tanggal = date('Y-m-d');

    // Cari id_pelanggan yang sinkron dengan tbl_pelanggan (berdasarkan nama atau buat otomatis)
    // Untuk simpelnya, kita asumsikan ambil pelanggan pertama atau cocokan nama
    $q_cust = mysqli_query($koneksi, "SELECT id_pelanggan FROM tbl_pelanggan LIMIT 1");
    $d_cust = mysqli_fetch_assoc($q_cust);
    $id_pelanggan_relasi = $d_cust['id_pelanggan'] ?? 1;

    if (!empty($_SESSION['keranjang_pelanggan'])) {
        $grand_total = 0;
        foreach ($_SESSION['keranjang_pelanggan'] as $it) {
            $grand_total += ($it['harga'] * $it['qty']);
        }

        // Simpan ke tbl_transaksi
        $ins = mysqli_query($koneksi, "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja) VALUES ('$tanggal', '$id_pelanggan_relasi', '$grand_total')");
        if ($ins) {
            $id_trx = mysqli_insert_id($koneksi);
            foreach ($_SESSION['keranjang_pelanggan'] as $it) {
                $pid = $it['id_produk'];
                $pnm = mysqli_real_escape_string($koneksi, $it['nama_produk']);
                $pqt = $it['qty'];
                $prc = $it['harga'];

                mysqli_query($koneksi, "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, nama_produk, jumlah, harga) VALUES ('$id_trx', '$pid', '$pnm', '$pqt', '$prc')");
                mysqli_query($koneksi, "UPDATE tbl_produk SET stok = stok - $pqt WHERE id_produk = $pid");
            }
            unset($_SESSION['keranjang_pelanggan']);
            header("Location: katalog_belanja.php?status=sukses_checkout&trx=$id_trx");
            exit();
        }
    }
}

$result_produk = mysqli_query($koneksi, "SELECT p.*, k.nama_kategori FROM tbl_produk p LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori ORDER BY p.id_produk DESC");
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Katalog Belanja Pelanggan - Toko Kita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 min-h-screen flex flex-col">

    <!-- NAVBAR ATAS PELANGGAN -->
    <header class="bg-white border-b border-slate-200 px-8 py-4 flex items-center justify-between shadow-xs">
        <div class="flex items-center gap-3">
            <div class="bg-indigo-600 text-white font-bold rounded-xl flex items-center justify-center w-9 h-9">
                <i class="fa-solid fa-store"></i>
            </div>
            <div>
                <h2 class="font-bold text-slate-800 text-sm">Toko Kita - Portal Pelanggan</h2>
                <p class="text-[11px] text-slate-400">Selamat datang, <strong><?= htmlspecialchars($nama_user); ?></strong></p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            <button onclick="bukaKeranjangPelanggan()" class="relative bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                <i class="fa-solid fa-cart-shopping"></i> Keranjang
                <?php if (count($_SESSION['keranjang_pelanggan']) > 0): ?>
                    <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white font-bold rounded-full w-5 h-5 flex items-center justify-center text-[10px]">
                        <?= count($_SESSION['keranjang_pelanggan']); ?>
                    </span>
                <?php endif; ?>
            </button>
            <a href="logout.php" class="bg-red-50 hover:bg-red-100 text-red-600 px-4 py-2 rounded-xl text-xs font-semibold transition">
                <i class="fa-solid fa-right-from-bracket"></i> Logout
            </a>
        </div>
    </header>

    <!-- KONTEN KATALOG PRODUK -->
    <main class="flex-1 max-w-7xl w-full mx-auto p-8 space-y-6">
        <div>
            <h1 class="text-lg font-bold text-slate-800">Katalog Produk Tersedia</h1>
            <p class="text-xs text-slate-400">Pilih produk yang ingin kamu beli secara mandiri.</p>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-4 gap-6">
            <?php while ($prod = mysqli_fetch_assoc($result_produk)): ?>
                <div class="bg-white rounded-2xl border border-slate-200/70 p-5 flex flex-col justify-between shadow-2xs">
                    <div>
                        <div class="w-12 h-12 rounded-xl bg-indigo-50 text-indigo-600 flex items-center justify-center text-lg mb-4 font-bold">
                            <i class="fa-solid fa-box"></i>
                        </div>
                        <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md text-[10px] font-semibold">
                            <?= htmlspecialchars($prod['nama_kategori'] ?? 'Umum'); ?>
                        </span>
                        <h3 class="font-bold text-slate-800 text-sm mt-2"><?= htmlspecialchars($prod['nama_produk']); ?></h3>
                        <p class="text-indigo-600 font-bold text-sm mt-1">Rp <?= number_format($prod['harga'], 0, ',', '.'); ?></p>
                        <p class="text-[11px] text-slate-400 mt-1">Stok: <strong><?= $prod['stok']; ?> Pcs</strong></p>
                    </div>

                    <div class="mt-4 pt-4 border-t border-slate-100">
                        <?php if ($prod['stok'] > 0): ?>
                            <form action="" method="POST" class="flex items-center gap-2">
                                <input type="hidden" name="id_produk" value="<?= $prod['id_produk']; ?>">
                                <input type="number" name="qty" min="1" max="<?= $prod['stok']; ?>" value="1" class="w-16 px-2 py-1.5 border border-slate-300 rounded-lg text-xs text-center outline-none">
                                <button type="submit" name="beli_produk" class="flex-1 bg-indigo-600 hover:bg-indigo-700 text-white py-1.5 rounded-lg text-xs font-semibold transition">
                                    + Beli
                                </button>
                            </form>
                        <?php else: ?>
                            <span class="text-red-500 font-bold text-xs block text-center py-1.5">Stok Habis</span>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endwhile; ?>
        </div>
    </main>

    <!-- MODAL KERANJANG PELANGGAN -->
    <div id="modalKeranjangPelanggan" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-lg overflow-hidden border border-slate-100 flex flex-col">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Keranjang Belanja Kamu</h3>
                <button onclick="tutupKeranjangPelanggan()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            <div class="p-6 space-y-4 text-xs max-h-[60vh] overflow-y-auto">
                <?php if (!empty($_SESSION['keranjang_pelanggan'])): ?>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                                <th class="py-2">Produk</th>
                                <th class="py-2 text-right">Harga</th>
                                <th class="py-2 text-center">Qty</th>
                                <th class="py-2 text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600">
                            <?php 
                            $tot = 0;
                            foreach ($_SESSION['keranjang_pelanggan'] as $cart): 
                                $sub = $cart['harga'] * $cart['qty'];
                                $tot += $sub;
                            ?>
                                <tr>
                                    <td class="py-2.5 font-semibold text-slate-800"><?= htmlspecialchars($cart['nama_produk']); ?></td>
                                    <td class="py-2.5 text-right">Rp <?= number_format($cart['harga'], 0, ',', '.'); ?></td>
                                    <td class="py-2.5 text-center"><?= $cart['qty']; ?></td>
                                    <td class="py-2.5 text-right font-bold">Rp <?= number_format($sub, 0, ',', '.'); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    <div class="border-t border-slate-200 pt-3 flex justify-between font-bold text-sm text-slate-800">
                        <span>Total Pembayaran:</span>
                        <span class="text-indigo-600">Rp <?= number_format($tot, 0, ',', '.'); ?></span>
                    </div>

                    <form action="" method="POST" class="pt-2">
                        <button type="submit" name="checkout_mandiri" class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl font-semibold transition shadow-sm">
                            Konfirmasi Pesanan / Checkout
                        </button>
                    </form>
                <?php else: ?>
                    <p class="text-center text-slate-400 py-6">Keranjang belanja masih kosong.</p>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <script>
        function bukaKeranjangPelanggan() {
            document.getElementById('modalKeranjangPelanggan').classList.remove('hidden');
        }
        function tutupKeranjangPelanggan() {
            document.getElementById('modalKeranjangPelanggan').classList.add('hidden');
        }
    </script>
</body>
</html>