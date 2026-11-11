<?php
// data_produk_kasir.php
require_once 'koneksi.php';

// Inisialisasi Session untuk Keranjang Belanja & Role Kasir
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
if (!isset($_SESSION['keranjang'])) {
    $_SESSION['keranjang'] = [];
}

// Kunci session role khusus kasir
$_SESSION['role'] = 'kasir';
$role_login = 'kasir';
$nama_user  = $_SESSION['nama_lengkap'] ?? 'Kasir Toko';

// PROSES TAMBAH KE KERANJANG (SESSION)
if (isset($_POST['tambah_keranjang'])) {
    $id_produk = intval($_POST['id_produk']);
    $qty_beli  = intval($_POST['qty']);

    $q_prod = mysqli_query($koneksi, "SELECT * FROM tbl_produk WHERE id_produk = $id_produk");
    $prod   = mysqli_fetch_assoc($q_prod);

    if ($prod && $qty_beli > 0) {
        if ($qty_beli <= $prod['stok']) {
            $found = false;
            foreach ($_SESSION['keranjang'] as &$item) {
                if ($item['id_produk'] == $id_produk) {
                    $item['qty'] += $qty_beli;
                    $found = true;
                    break;
                }
            }
            unset($item);

            if (!$found) {
                $_SESSION['keranjang'][] = [
                    'id_produk'   => $prod['id_produk'],
                    'nama_produk' => $prod['nama_produk'],
                    'harga'       => $prod['harga'],
                    'qty'         => $qty_beli
                ];
            }
            header("Location: data_produk_kasir.php?status=sukses_add_cart");
            exit();
        } else {
            header("Location: data_produk_kasir.php?status=stok_kurang");
            exit();
        }
    }
}

// PROSES HAPUS ITEM DARI KERANJANG
if (isset($_GET['hapus_cart'])) {
    $index = intval($_GET['hapus_cart']);
    if (isset($_SESSION['keranjang'][$index])) {
        unset($_SESSION['keranjang'][$index]);
        $_SESSION['keranjang'] = array_values($_SESSION['keranjang']);
    }
    header("Location: data_produk_kasir.php");
    exit();
}

// PROSES EDIT PRODUK KATALOG (DENGAN OPSIONAL UPLOAD GAMBAR)
if (isset($_POST['edit_produk'])) {
    $id_produk   = intval($_POST['id_produk']);
    $id_kategori = intval($_POST['id_kategori']);
    $nama_produk = mysqli_real_escape_string($koneksi, $_POST['nama_produk']);
    $harga       = floatval($_POST['harga']);
    $stok        = intval($_POST['stok']);

    if ($id_produk > 0 && !empty($nama_produk)) {
        if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] === UPLOAD_ERR_OK) {
            $file_tmp  = $_FILES['gambar']['tmp_name'];
            $file_name = $_FILES['gambar']['name'];
            $file_ext  = strtolower(pathinfo($file_name, PATHINFO_EXTENSION));
            $allowed_ext = ['jpg', 'jpeg', 'png', 'webp'];
            
            if (in_array($file_ext, $allowed_ext)) {
                $nama_file_gambar = 'prod_' . time() . '.' . $file_ext;
                if (move_uploaded_file($file_tmp, 'uploads/' . $nama_file_gambar)) {
                    mysqli_query($koneksi, "UPDATE tbl_produk SET id_kategori = '$id_kategori', nama_produk = '$nama_produk', harga = '$harga', stok = '$stok', gambar = '$nama_file_gambar' WHERE id_produk = $id_produk");
                }
            }
        } else {
            mysqli_query($koneksi, "UPDATE tbl_produk SET id_kategori = '$id_kategori', nama_produk = '$nama_produk', harga = '$harga', stok = '$stok' WHERE id_produk = $id_produk");
        }
        header("Location: data_produk_kasir.php?status=sukses_edit");
        exit();
    }
}

// PROSES HAPUS PRODUK KATALOG
if (isset($_GET['hapus'])) {
    $id_produk = intval($_GET['hapus']);
    if ($id_produk > 0) {
        mysqli_query($koneksi, "DELETE FROM tbl_produk WHERE id_produk = $id_produk");
        header("Location: data_produk_kasir.php?status=sukses_hapus");
        exit();
    }
}

// PROSES FINAL CHECKOUT KASIR (SEKALIGUS BANYAK PRODUK)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['proses_checkout_all'])) {
    $id_pelanggan = intval($_POST['id_pelanggan']);
    $tanggal      = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    
    if ($id_pelanggan > 0 && !empty($_SESSION['keranjang'])) {
        $grand_total = 0;
        foreach ($_SESSION['keranjang'] as $item) {
            $grand_total += ($item['harga'] * $item['qty']);
        }

        $query_transaksi = "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja) VALUES ('$tanggal', '$id_pelanggan', '$grand_total')";
        
        if (mysqli_query($koneksi, $query_transaksi)) {
            $id_transaksi_baru = mysqli_insert_id($koneksi);

            foreach ($_SESSION['keranjang'] as $item) {
                $pid   = $item['id_produk'];
                $pname = mysqli_real_escape_string($koneksi, $item['nama_produk']);
                $pqty  = $item['qty'];
                $prc   = $item['harga'];

                mysqli_query($koneksi, "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, nama_produk, jumlah, harga) VALUES ('$id_transaksi_baru', '$pid', '$pname', '$pqty', '$prc')");
                mysqli_query($koneksi, "UPDATE tbl_produk SET stok = stok - $pqty WHERE id_produk = $pid");
            }

            unset($_SESSION['keranjang']);

            header("Location: data_produk_kasir.php?status=sukses_checkout&trx_id=" . $id_transaksi_baru);
            exit();
        }
    }
}

$keyword = isset($_GET['cari']) ? mysqli_real_escape_string($koneksi, $_GET['cari']) : '';

$query_kategori = "SELECT * FROM tbl_kategori ORDER BY nama_kategori ASC";
$result_kategori = mysqli_query($koneksi, $query_kategori);
$data_kategori_list = [];
while ($kat = mysqli_fetch_assoc($result_kategori)) {
    $data_kategori_list[] = $kat;
}

$data_pelanggan = mysqli_query($koneksi, "SELECT * FROM tbl_pelanggan ORDER BY nama_pelanggan ASC");

$query_produk = "SELECT p.*, k.nama_kategori 
                 FROM tbl_produk p 
                 LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori";
if (!empty($keyword)) {
    $query_produk .= " WHERE p.nama_produk LIKE '%$keyword%' OR k.nama_kategori LIKE '%$keyword%'";
}
$query_produk .= " ORDER BY p.id_produk DESC";
$result_produk = mysqli_query($koneksi, $query_produk);

$show_struk = false;
$data_struk = [];
$detail_struk_items = [];
if (isset($_GET['status']) && $_GET['status'] === 'sukses_checkout' && isset($_GET['trx_id'])) {
    $id_trx_baru = intval($_GET['trx_id']);
    $q_struk = mysqli_query($koneksi, "SELECT t.*, p.nama_pelanggan, p.kota FROM tbl_transaksi t JOIN tbl_pelanggan p ON t.id_pelanggan = p.id_pelanggan WHERE t.id_transaksi = $id_trx_baru");
    if ($q_struk && mysqli_num_rows($q_struk) > 0) {
        $data_struk = mysqli_fetch_assoc($q_struk);
        $q_det = mysqli_query($koneksi, "SELECT * FROM tbl_detail_transaksi WHERE id_transaksi = $id_trx_baru");
        while ($d_item = mysqli_fetch_assoc($q_det)) {
            $detail_struk_items[] = $d_item;
        }
        $show_struk = true;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kasir &amp; Keranjang - Toko Kita</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&display=swap');
        body { font-family: 'Plus Jakarta Sans', sans-serif; }
        
        @media print {
            @page {
                margin: 0;
                size: 58mm auto;
            }
            body, html {
                margin: 0 !important;
                padding: 0 !important;
                width: 58mm !important;
            }
            body * { visibility: hidden; }
            #printArea, #printArea * { visibility: visible; }
            #printArea { 
                position: absolute; 
                left: 0; 
                top: 0; 
                width: 54mm;
                margin: 0 auto;
                padding: 2mm;
                font-family: 'Courier New', Courier, monospace;
                font-size: 9px;
                color: #000;
                background: white;
            }
        }
    </style>
</head>
<body class="bg-[#f8fafc] text-slate-700 flex h-screen overflow-hidden">

    <!-- SIDEBAR KASIR (HIJAU EMERALD) -->
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

            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">Menu Kasir</div>
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
            <a href="logout.php" onclick="return confirm('Yakin ingin keluar dari sistem?')" class="w-full bg-red-500/10 hover:bg-red-500 text-red-400 hover:text-white px-3 py-2 rounded-xl text-xs font-semibold flex items-center justify-center gap-2 transition border border-red-500/20">
                <i class="fa-solid fa-right-from-bracket text-[10px]"></i> Logout
            </a>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Katalog Produk &amp; Keranjang Kasir</h1>
                <p class="text-xs text-slate-400 mt-0.5">Kelola transaksi dan keranjang belanja.</p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="bukaModalKeranjang()" class="relative bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 shadow-sm transition">
                    <i class="fa-solid fa-cart-shopping text-[10px]"></i> Keranjang Belanja
                    <?php if (count($_SESSION['keranjang']) > 0): ?>
                        <span class="absolute -top-1.5 -right-1.5 bg-red-500 text-white font-bold rounded-full w-5 h-5 flex items-center justify-center text-[10px]">
                            <?= count($_SESSION['keranjang']); ?>
                        </span>
                    <?php endif; ?>
                </button>
                <a href="tambah_produk_kasir.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah Produk
                </a>
                <a href="dashboard_kasir.php" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Dashboard Kasir
                </a>
            </div>
        </header>

        <main class="flex-1 overflow-y-auto p-8 space-y-6">
            <?php if (isset($_GET['status'])): ?>
                <div class="bg-emerald-50 border border-emerald-200/70 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span>
                            <?php if ($_GET['status'] === 'sukses_add_cart') echo 'Produk berhasil dimasukkan ke keranjang!'; ?>
                            <?php if ($_GET['status'] === 'sukses_checkout') echo 'Checkout berhasil diproses dan struk dicetak!'; ?>
                            <?php if ($_GET['status'] === 'stok_kurang') echo 'Gagal: Jumlah pembelian melebihi stok gudang!'; ?>
                            <?php if ($_GET['status'] === 'sukses_edit') echo 'Data produk berhasil diperbarui.'; ?>
                            <?php if ($_GET['status'] === 'sukses_tambah') echo 'Produk baru berhasil ditambahkan.'; ?>
                            <?php if ($_GET['status'] === 'sukses_hapus') echo 'Produk berhasil dihapus.'; ?>
                        </span>
                    </div>
                    <a href="data_produk_kasir.php" class="text-emerald-600 hover:text-emerald-900"><i class="fa-solid fa-xmark text-sm"></i></a>
                </div>
            <?php endif; ?>

            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex flex-col md:flex-row items-center justify-between gap-4">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Daftar Produk &amp; Aksi Kasir</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Pilih produk dan masukkan ke keranjang untuk transaksi multi-item.</p>
                    </div>

                    <form action="" method="GET" class="flex items-center gap-2 w-full md:w-auto">
                        <div class="relative w-full md:w-64">
                            <span class="absolute inset-y-0 left-0 flex items-center pl-3 text-slate-400">
                                <i class="fa-solid fa-magnifying-glass text-xs"></i>
                            </span>
                            <input type="text" name="cari" value="<?= htmlspecialchars($keyword); ?>" placeholder="Cari nama produk..." class="w-full pl-9 pr-4 py-2 bg-slate-50 border border-slate-200 rounded-xl text-xs outline-none text-slate-800">
                        </div>
                        <?php if (!empty($keyword)): ?>
                            <a href="data_produk_kasir.php" class="bg-slate-100 hover:bg-slate-200 text-slate-600 px-3 py-2 rounded-xl text-xs font-semibold transition" title="Reset">
                                <i class="fa-solid fa-rotate-left"></i>
                            </a>
                        <?php endif; ?>
                    </form>
                </div>

                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">ID Produk</th>
                                <th class="py-3.5 px-6">Nama Produk</th>
                                <th class="py-3.5 px-6">Kategori</th>
                                <th class="py-3.5 px-6 text-right">Harga Satuan</th>
                                <th class="py-3.5 px-6 text-center">Stok</th>
                                <th class="py-3.5 px-6 text-center">Kasir / Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($result_produk) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_produk)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#PROD-<?= str_pad($row['id_produk'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6 text-slate-800 font-semibold flex items-center gap-2.5">
                                            <?php if (!empty($row['gambar']) && file_exists('uploads/' . $row['gambar'])): ?>
                                                <img src="uploads/<?= htmlspecialchars($row['gambar']); ?>" alt="Foto" class="w-10 h-10 rounded-lg object-cover border border-slate-200 shrink-0 shadow-xs">
                                            <?php else: ?>
                                                <div class="w-10 h-10 rounded-lg bg-slate-100 text-slate-400 flex items-center justify-center text-xs shrink-0 font-bold border border-slate-200">
                                                    <i class="fa-solid fa-image"></i>
                                                </div>
                                            <?php endif; ?>
                                            <span><?= htmlspecialchars($row['nama_produk']); ?></span>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px] font-semibold">
                                                <?= htmlspecialchars($row['nama_kategori'] ?? 'Tanpa Kategori'); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-right font-bold text-emerald-600">Rp <?= number_format($row['harga'], 0, ',', '.'); ?></td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="<?= $row['stok'] <= 5 ? 'bg-red-50 text-red-600 border-red-200' : 'bg-emerald-50 text-emerald-600 border-emerald-200'; ?> px-3 py-1 rounded-full border text-[11px] font-bold inline-block">
                                                <?= $row['stok']; ?> Pcs
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center space-x-1">
                                            <?php if ($row['stok'] > 0): ?>
                                                <button onclick="bukaModalAddToCart(<?= $row['id_produk']; ?>, '<?= htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>', <?= $row['stok']; ?>)" class="bg-emerald-600 hover:bg-emerald-700 text-white px-2.5 py-1.5 rounded-lg transition font-semibold" title="Tambah ke Keranjang">
                                                    <i class="fa-solid fa-cart-plus"></i> + Keranjang
                                                </button>
                                            <?php else: ?>
                                                <span class="text-red-500 font-bold text-[10px]">Stok Habis</span>
                                            <?php endif; ?>
                                            <button onclick="bukaModalEdit(<?= $row['id_produk']; ?>, '<?= $row['id_kategori']; ?>', '<?= htmlspecialchars($row['nama_produk'], ENT_QUOTES); ?>', <?= $row['harga']; ?>, <?= $row['stok']; ?>)" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-2.5 py-1.5 rounded-lg border border-amber-200 transition font-semibold" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i>
                                            </button>
                                            <a href="data_produk_kasir.php?hapus=<?= $row['id_produk']; ?>" onclick="return confirm('Yakin ingin menghapus produk ini?')" class="bg-red-50 hover:bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg border border-red-200 transition font-semibold inline-block" title="Hapus">
                                                <i class="fa-solid fa-trash"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" class="py-8 text-center text-slate-400">Produk tidak ditemukan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- MODAL TAMBAH KE KERANJANG -->
    <div id="modalAddToCart" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-sm overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Masukkan ke Keranjang</h3>
                <button onclick="tutupModalAddToCart()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" id="cartIdProd" name="id_produk">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Produk</label>
                    <input type="text" id="cartNamaProd" readonly class="w-full px-3.5 py-2.5 bg-slate-100 border border-slate-300 rounded-xl text-slate-800 font-semibold outline-none">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Jumlah Beli (Qty)</label>
                    <input type="number" id="cartQty" name="qty" min="1" value="1" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                    <p class="text-[10px] text-slate-400 mt-1">Stok tersedia: <span id="cartMaxStok" class="font-bold text-slate-600"></span></p>
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="tutupModalAddToCart()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" name="tambah_keranjang" class="px-5 py-2 font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">+ Masukkan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL KERANJANG & CHECKOUT -->
    <div id="modalKeranjang" class="fixed inset-0 bg-slate-900/50 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-2xl overflow-hidden border border-slate-100 flex flex-col max-h-[90vh]">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50 shrink-0">
                <div class="flex items-center gap-2">
                    <div class="w-8 h-8 rounded-lg bg-emerald-50 text-emerald-600 flex items-center justify-center font-bold">
                        <i class="fa-solid fa-cart-shopping text-xs"></i>
                    </div>
                    <h3 class="font-bold text-slate-800 text-sm">Keranjang Belanja Kasir</h3>
                </div>
                <button onclick="tutupModalKeranjang()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <div class="p-6 overflow-y-auto space-y-4 text-xs flex-1">
                <?php if (!empty($_SESSION['keranjang'])): ?>
                    <div class="border border-slate-200 rounded-xl overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-slate-50 border-b border-slate-200 text-slate-400 font-bold uppercase text-[10px]">
                                    <th class="py-2.5 px-4">No</th>
                                    <th class="py-2.5 px-4">Nama Produk</th>
                                    <th class="py-2.5 px-4 text-right">Harga</th>
                                    <th class="py-2.5 px-4 text-center">Qty</th>
                                    <th class="py-2.5 px-4 text-right">Subtotal</th>
                                    <th class="py-2.5 px-4 text-center">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-slate-100 text-slate-600">
                                <?php 
                                $no = 1;
                                $grand_total = 0;
                                foreach ($_SESSION['keranjang'] as $idx => $item): 
                                    $subtotal = $item['harga'] * $item['qty'];
                                    $grand_total += $subtotal;
                                ?>
                                    <tr class="hover:bg-slate-50">
                                        <td class="py-3 px-4 font-bold"><?= $no++; ?></td>
                                        <td class="py-3 px-4 font-semibold text-slate-800"><?= htmlspecialchars($item['nama_produk']); ?></td>
                                        <td class="py-3 px-4 text-right">Rp <?= number_format($item['harga'], 0, ',', '.'); ?></td>
                                        <td class="py-3 px-4 text-center font-bold text-emerald-600"><?= $item['qty']; ?></td>
                                        <td class="py-3 px-4 text-right font-bold text-slate-800">Rp <?= number_format($subtotal, 0, ',', '.'); ?></td>
                                        <td class="py-3 px-4 text-center">
                                            <a href="data_produk_kasir.php?hapus_cart=<?= $idx; ?>" class="text-red-500 hover:text-red-700 bg-red-50 p-1.5 rounded-lg border border-red-200 inline-block" title="Hapus Item">
                                                <i class="fa-solid fa-trash text-[11px]"></i>
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>

                    <form action="" method="POST" onsubmit="return validasiCheckoutKeranjang()" class="space-y-4 pt-2 border-t border-slate-100">
                        <input type="hidden" name="proses_checkout_all" value="1">
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Pilih Pelanggan</label>
                                <select name="id_pelanggan" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl bg-white outline-none text-slate-800">
                                    <option value="" disabled selected>-- Pilih Pelanggan --</option>
                                    <?php while ($cust = mysqli_fetch_assoc($data_pelanggan)): ?>
                                        <option value="<?= $cust['id_pelanggan']; ?>"><?= htmlspecialchars($cust['nama_pelanggan']); ?> (<?= htmlspecialchars($cust['kota']); ?>)</option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                            <div>
                                <label class="block font-semibold text-slate-600 mb-1">Tanggal Transaksi</label>
                                <input type="date" name="tanggal" required value="<?= date('Y-m-d'); ?>" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                            </div>
                        </div>

                        <div class="grid grid-cols-1 md:grid-cols-3 gap-4 bg-slate-50 p-4 rounded-xl border border-slate-200">
                            <div>
                                <label class="block font-bold text-slate-500 mb-1">Total Belanja</label>
                                <input type="text" id="displayGrandTotal" readonly value="Rp <?= number_format($grand_total, 0, ',', '.'); ?>" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold text-emerald-700 outline-none text-sm">
                                <input type="hidden" id="valGrandTotal" value="<?= $grand_total; ?>">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Uang Tunai (Rp)</label>
                                <input type="number" id="uangTunaiCart" min="0" oninput="hitungKembalianCart()" required placeholder="Nominal uang" class="w-full px-3 py-2 bg-white border border-slate-300 rounded-lg font-bold text-emerald-700 outline-none text-sm">
                            </div>
                            <div>
                                <label class="block font-bold text-slate-700 mb-1">Uang Kembalian</label>
                                <input type="text" id="uangKembalianCart" readonly value="Rp 0" class="w-full px-3 py-2 bg-emerald-50 border border-emerald-200 rounded-lg font-bold text-emerald-800 outline-none text-sm">
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-3 pt-2">
                            <button type="button" onclick="tutupModalKeranjang()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Tutup</button>
                            <button type="submit" class="px-6 py-2.5 font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">Proses Checkout Sekaligus</button>
                        </div>
                    </form>
                <?php else: ?>
                    <div class="text-center py-12 text-slate-400 space-y-2">
                        <i class="fa-solid fa-cart-shopping text-3xl text-slate-300"></i>
                        <p class="font-semibold text-slate-600">Keranjang belanja masih kosong.</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- MODAL EDIT PRODUK (DENGAN UPLOAD GAMBAR) -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Edit Data Produk</h3>
                <button onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="" method="POST" enctype="multipart/form-data" class="p-6 space-y-4 text-xs">
                <input type="hidden" id="editId" name="id_produk">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Produk</label>
                    <input type="text" id="editNama" name="nama_produk" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Kategori Produk</label>
                    <select id="editKategori" name="id_kategori" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800 bg-white">
                        <option value="" disabled>Pilih Kategori</option>
                        <?php foreach ($data_kategori_list as $kat): ?>
                            <option value="<?= $kat['id_kategori']; ?>"><?= htmlspecialchars($kat['nama_kategori']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Harga Satuan (Rp)</label>
                        <input type="number" id="editHarga" name="harga" min="0" step="500" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                    </div>
                    <div>
                        <label class="block font-semibold text-slate-600 mb-1">Stok (Pcs)</label>
                        <input type="number" id="editStok" name="stok" min="0" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl outline-none text-slate-800">
                    </div>
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Ganti Foto / Gambar (Opsional)</label>
                    <input type="file" name="gambar" accept="image/png, image/jpeg, image/jpg, image/webp" class="w-full px-3.5 py-2 border border-slate-300 rounded-xl outline-none text-slate-800 bg-slate-50">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="tutupModalEdit()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 transition">Batal</button>
                    <button type="submit" name="edit_produk" class="px-5 py-2 font-semibold bg-emerald-600 hover:bg-emerald-700 text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL STRUK PEMBAYARAN NOTA -->
    <?php if ($show_struk): ?>
    <div id="modalStruk" class="fixed inset-0 bg-slate-900/60 backdrop-blur-xs flex items-center justify-center z-50 p-4">
        <div class="bg-white rounded-2xl shadow-2xl w-full max-w-sm overflow-hidden border border-slate-200">
            <div id="printArea" class="p-4 space-y-3 text-xs font-mono">
                <div class="text-center border-b border-dashed border-slate-400 pb-2">
                    <h2 class="font-bold text-sm text-slate-900 font-sans">TOKO KITA</h2>
                    <p class="text-[10px] text-slate-600">Nota Belanja Kasir</p>
                    <p class="text-[9px] text-slate-500 mt-0.5"><?= $data_struk['tanggal']; ?></p>
                </div>

                <div class="space-y-0.5 text-slate-700 border-b border-dashed border-slate-400 pb-2 text-[10px]">
                    <div class="flex justify-between">
                        <span>No:</span>
                        <span class="font-bold">#TRX-<?= str_pad($data_struk['id_transaksi'], 3, '0', STR_PAD_LEFT); ?></span>
                    </div>
                    <div class="flex justify-between">
                        <span>Plg:</span>
                        <span class="font-bold"><?= htmlspecialchars($data_struk['nama_pelanggan']); ?></span>
                    </div>
                </div>

                <div class="border-b border-dashed border-slate-400 pb-2 space-y-1 text-[10px]">
                    <p class="font-bold text-slate-800 font-sans">Item:</p>
                    <?php foreach ($detail_struk_items as $d_st): ?>
                        <div class="flex justify-between">
                            <span><?= htmlspecialchars($d_st['nama_produk']); ?> (x<?= $d_st['jumlah']; ?>)</span>
                            <span>Rp <?= number_format($d_st['harga'] * $d_st['jumlah'], 0, ',', '.'); ?></span>
                        </div>
                    <?php endforeach; ?>
                </div>

                <div class="border-b border-dashed border-slate-400 pb-2 space-y-0.5 text-[10px]">
                    <div class="flex justify-between font-bold text-slate-900 text-xs">
                        <span>TOTAL:</span>
                        <span>Rp <?= number_format($data_struk['total_belanja'], 0, ',', '.'); ?></span>
                    </div>
                </div>

                <div class="text-center pt-1 text-[9px] text-slate-600">
                    <p class="font-sans font-semibold">Terima Kasih!</p>
                </div>
            </div>

            <div class="px-6 py-3 bg-slate-50 border-t border-slate-100 flex items-center justify-between font-sans text-xs">
                <button onclick="window.print()" class="bg-slate-800 hover:bg-slate-900 text-white px-3 py-1.5 rounded-lg font-semibold flex items-center gap-1.5 transition">
                    <i class="fa-solid fa-print text-[10px]"></i> Cetak Struk
                </button>
                <a href="data_produk_kasir.php" class="bg-emerald-600 hover:bg-emerald-700 text-white px-4 py-1.5 rounded-lg font-semibold transition">
                    Selesai
                </a>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <script>
        function bukaModalAddToCart(id, nama, stok) {
            document.getElementById('cartIdProd').value = id;
            document.getElementById('cartNamaProd').value = nama;
            document.getElementById('cartMaxStok').innerText = stok + ' Pcs';
            document.getElementById('cartQty').max = stok;
            document.getElementById('modalAddToCart').classList.remove('hidden');
        }
        function tutupModalAddToCart() {
            document.getElementById('modalAddToCart').classList.add('hidden');
        }

        function bukaModalKeranjang() {
            document.getElementById('modalKeranjang').classList.remove('hidden');
        }
        function tutupModalKeranjang() {
            document.getElementById('modalKeranjang').classList.add('hidden');
        }

        function bukaModalEdit(id, id_kat, nama, harga, stok) {
            document.getElementById('editId').value = id;
            document.getElementById('editKategori').value = id_kat;
            document.getElementById('editNama').value = nama;
            document.getElementById('editHarga').value = harga;
            document.getElementById('editStok').value = stok;
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function tutupModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }

        function hitungKembalianCart() {
            const grandTotal = parseFloat(document.getElementById('valGrandTotal').value) || 0;
            const uangTunai = parseFloat(document.getElementById('uangTunaiCart').value) || 0;
            const kembalian = uangTunai - grandTotal;

            if (kembalian >= 0) {
                document.getElementById('uangKembalianCart').value = 'Rp ' + kembalian.toLocaleString('id-ID');
                document.getElementById('uangKembalianCart').classList.remove('text-red-600', 'bg-red-50');
                document.getElementById('uangKembalianCart').classList.add('text-emerald-800', 'bg-emerald-50');
            } else {
                document.getElementById('uangKembalianCart').value = 'Uang Kurang!';
                document.getElementById('uangKembalianCart').classList.remove('text-emerald-800', 'bg-emerald-50');
                document.getElementById('uangKembalianCart').classList.add('text-red-600', 'bg-red-50');
            }
        }

        function validasiCheckoutKeranjang() {
            const grandTotal = parseFloat(document.getElementById('valGrandTotal').value) || 0;
            const uangTunai = parseFloat(document.getElementById('uangTunaiCart').value) || 0;
            if (uangTunai < grandTotal) {
                alert('Jumlah uang tunai kurang dari total belanja!');
                return false;
            }
            return true;
        }
    </script>
</body>
</html>