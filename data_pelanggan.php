<?php
// data_pelanggan.php
require_once 'koneksi.php';

if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
$role_login = $_SESSION['role'] ?? 'admin';
$nama_user  = $_SESSION['nama_lengkap'] ?? 'Administrator';

// PROSES TAMBAH PELANGGAN
if (isset($_POST['tambah_pelanggan'])) {
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $kota = mysqli_real_escape_string($koneksi, $_POST['kota']);
    if (!empty($nama) && !empty($kota)) {
        mysqli_query($koneksi, "INSERT INTO tbl_pelanggan (nama_pelanggan, kota) VALUES ('$nama', '$kota')");
        header("Location: data_pelanggan.php?status=sukses_tambah");
        exit();
    }
}

// PROSES EDIT PELANGGAN
if (isset($_POST['edit_pelanggan'])) {
    $id   = intval($_POST['id_pelanggan']);
    $nama = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $kota = mysqli_real_escape_string($koneksi, $_POST['kota']);
    if ($id > 0 && !empty($nama) && !empty($kota)) {
        mysqli_query($koneksi, "UPDATE tbl_pelanggan SET nama_pelanggan = '$nama', kota = '$kota' WHERE id_pelanggan = $id");
        header("Location: data_pelanggan.php?status=sukses_edit");
        exit();
    }
}

// PROSES HAPUS PELANGGAN
if (isset($_GET['hapus'])) {
    $id = intval($_GET['hapus']);
    if ($id > 0) {
        mysqli_query($koneksi, "DELETE FROM tbl_transaksi WHERE id_pelanggan = $id");
        mysqli_query($koneksi, "DELETE FROM tbl_pelanggan WHERE id_pelanggan = $id");
        header("Location: data_pelanggan.php?status=sukses_hapus");
        exit();
    }
}

// Ambil semua data pelanggan dari database
$query_pelanggan = "SELECT * FROM tbl_pelanggan ORDER BY id_pelanggan DESC";
$result_pelanggan = mysqli_query($koneksi, $query_pelanggan);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Pelanggan - Toko Kita</title>
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

    <!-- SIDEBAR NAVIGASI DINAMIS -->
    <aside class="w-64 bg-slate-900 text-slate-300 flex flex-col justify-between p-6 shrink-0 border-r border-slate-800">
        <div>
            <!-- LOGO BRAND -->
            <div class="flex items-center gap-3 mb-8">
                <div class="<?= $role_login === 'kasir' ? 'bg-emerald-600 shadow-emerald-500/20' : 'bg-indigo-600 shadow-indigo-500/20'; ?> text-white font-bold rounded-xl flex items-center justify-center w-10 h-10 shadow-lg">
                    <i class="fa-solid fa-store text-base"></i>
                </div>
                <div>
                    <h2 class="font-bold text-white text-base tracking-tight leading-tight">Toko Kita</h2>
                    <span class="text-[11px] <?= $role_login === 'kasir' ? 'text-emerald-400' : 'text-indigo-400'; ?> font-medium tracking-wide uppercase">
                        <?= $role_login === 'kasir' ? 'Panel Kasir' : 'Sistem Penjualan'; ?>
                    </span>
                </div>
            </div>

            <!-- MENU UTAMA -->
            <div class="text-[10px] font-bold text-slate-500 uppercase tracking-wider mb-3 px-1">Menu Utama</div>
            <nav class="space-y-1 text-sm font-medium">
                <?php if ($role_login === 'kasir'): ?>
                    <a href="dashboard_kasir.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
                        <span>Dashboard Kasir</span>
                    </a>
                    <a href="data_produk.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                        <i class="fa-solid fa-cart-shopping text-xs text-slate-400"></i>
                        <span>Kasir &amp; Keranjang</span>
                    </a>
                    <a href="data_pelanggan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-emerald-600 text-white shadow-md shadow-emerald-600/20">
                        <i class="fa-solid fa-users text-xs"></i>
                        <span>Data Pelanggan</span>
                    </a>
                <?php else: ?>
                    <a href="index.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl hover:bg-slate-800/60 hover:text-white transition">
                        <i class="fa-solid fa-chart-pie text-xs text-slate-400"></i>
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
                    <a href="data_pelanggan.php" class="flex items-center gap-3 px-3.5 py-2.5 rounded-xl bg-indigo-600 text-white shadow-md shadow-indigo-600/20">
                        <i class="fa-solid fa-users text-xs"></i>
                        <span>Data Pelanggan</span>
                    </a>
                <?php endif; ?>
            </nav>
        </div>

        <!-- USER PROFILE & LOGOUT FOOTER -->
        <div class="border-t border-slate-800/80 pt-4 space-y-3">
            <div class="flex items-center gap-3">
                <div class="w-9 h-9 rounded-full bg-slate-800 <?= $role_login === 'kasir' ? 'text-emerald-400' : 'text-indigo-400'; ?> font-bold flex items-center justify-center text-xs border border-slate-700">
                    <?= $role_login === 'kasir' ? 'KS' : 'AD'; ?>
                </div>
                <div class="text-xs leading-tight">
                    <p class="font-semibold text-slate-200"><?= htmlspecialchars($nama_user); ?></p>
                    <p class="text-slate-400 text-[11px] mt-0.5 capitalize">Role: <?= $role_login; ?></p>
                </div>
            </div>
        </div>
    </aside>

    <!-- KONTEN UTAMA -->
    <div class="flex-1 flex flex-col overflow-hidden">

        <!-- HEADER TOPBAR -->
        <header class="bg-white border-b border-slate-200/80 px-8 py-4 flex items-center justify-between shrink-0">
            <div>
                <h1 class="text-lg font-bold text-slate-800 tracking-tight">Manajemen Data Pelanggan</h1>
                <p class="text-xs text-slate-400 mt-0.5">Basis Data: <code class="bg-slate-100 px-1.5 py-0.5 rounded text-slate-600 font-mono text-[11px]">tbl_pelanggan</code></p>
            </div>
            <div class="flex items-center gap-3">
                <button onclick="bukaModalTambah()" class="<?= $role_login === 'kasir' ? 'bg-emerald-600 hover:bg-emerald-700' : 'bg-indigo-600 hover:bg-indigo-700'; ?> text-white px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition shadow-sm">
                    <i class="fa-solid fa-plus text-[10px]"></i> Tambah Pelanggan
                </button>
                <a href="<?= $role_login === 'kasir' ? 'dashboard_kasir.php' : 'index.php'; ?>" class="bg-slate-100 hover:bg-slate-200 text-slate-700 px-4 py-2 rounded-xl text-xs font-semibold flex items-center gap-2 transition">
                    <i class="fa-solid fa-arrow-left text-[10px]"></i> Dashboard
                </a>
            </div>
        </header>

        <!-- AREA KONTEN -->
        <main class="flex-1 overflow-y-auto p-8 space-y-6">

            <!-- NOTIFIKASI SUKSES -->
            <?php if (isset($_GET['status'])): ?>
                <div class="bg-emerald-50 border border-emerald-200/70 text-emerald-800 px-4 py-3 rounded-2xl text-xs flex items-center justify-between shadow-xs">
                    <div class="flex items-center gap-2">
                        <i class="fa-solid fa-circle-check text-emerald-600 text-sm"></i>
                        <span><strong>Berhasil!</strong> Aksi data pelanggan berhasil diproses.</span>
                    </div>
                    <a href="data_pelanggan.php" class="text-emerald-600 hover:text-emerald-900"><i class="fa-solid fa-xmark text-sm"></i></a>
                </div>
            <?php endif; ?>

            <!-- KARTU TABEL PELANGGAN -->
            <div class="bg-white rounded-2xl border border-slate-200/70 shadow-2xs overflow-hidden">
                <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between">
                    <div>
                        <h3 class="font-bold text-slate-800 text-sm">Daftar Pelanggan Terdaftar</h3>
                        <p class="text-xs text-slate-400 mt-0.5">Kelola data pelanggan, edit profil, atau hapus data dari sistem.</p>
                    </div>
                    <span class="bg-emerald-50 text-emerald-700 text-[11px] font-semibold px-2.5 py-1 rounded-lg border border-emerald-100">Live Database</span>
                </div>
                <div class="overflow-x-auto">
                    <table class="w-full text-left border-collapse text-xs">
                        <thead>
                            <tr class="bg-slate-50/70 border-b border-slate-100 text-slate-400 font-bold uppercase tracking-wider">
                                <th class="py-3.5 px-6">ID Pelanggan</th>
                                <th class="py-3.5 px-6">Nama Pelanggan</th>
                                <th class="py-3.5 px-6">Kota Asal</th>
                                <th class="py-3.5 px-6 text-center">Status</th>
                                <th class="py-3.5 px-6 text-center">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 text-slate-600 font-medium">
                            <?php if (mysqli_num_rows($result_pelanggan) > 0): ?>
                                <?php while ($row = mysqli_fetch_assoc($result_pelanggan)): ?>
                                    <tr class="hover:bg-slate-50/60 transition">
                                        <td class="py-4 px-6 font-bold text-slate-800">#CUST-<?= str_pad($row['id_pelanggan'], 3, '0', STR_PAD_LEFT); ?></td>
                                        <td class="py-4 px-6 text-slate-800 font-semibold flex items-center gap-2.5">
                                            <div class="w-8 h-8 rounded-full bg-indigo-50 text-indigo-600 flex items-center justify-center text-xs shrink-0 font-bold">
                                                <?= strtoupper(substr($row['nama_pelanggan'], 0, 1)); ?>
                                            </div>
                                            <?= htmlspecialchars($row['nama_pelanggan']); ?>
                                        </td>
                                        <td class="py-4 px-6">
                                            <span class="bg-slate-100 text-slate-600 px-2.5 py-1 rounded-md border border-slate-200/60 text-[11px] font-semibold">
                                                <?= htmlspecialchars($row['kota']); ?>
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center">
                                            <span class="bg-indigo-50 text-indigo-600 px-3 py-1 rounded-full text-[11px] font-semibold inline-block">
                                                Aktif
                                            </span>
                                        </td>
                                        <td class="py-4 px-6 text-center space-x-2">
                                            <button onclick="bukaModalEdit(<?= $row['id_pelanggan']; ?>, '<?= htmlspecialchars($row['nama_pelanggan'], ENT_QUOTES); ?>', '<?= htmlspecialchars($row['kota'], ENT_QUOTES); ?>')" class="bg-amber-50 hover:bg-amber-100 text-amber-700 px-2.5 py-1.5 rounded-lg border border-amber-200 transition font-semibold" title="Edit">
                                                <i class="fa-solid fa-pen-to-square"></i> Edit
                                            </button>
                                            <a href="data_pelanggan.php?hapus=<?= $row['id_pelanggan']; ?>" onclick="return confirm('Yakin ingin menghapus pelanggan ini?')" class="bg-red-50 hover:bg-red-100 text-red-600 px-2.5 py-1.5 rounded-lg border border-red-200 transition font-semibold inline-block" title="Hapus">
                                                <i class="fa-solid fa-trash"></i> Hapus
                                            </a>
                                        </td>
                                    </tr>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" class="py-8 text-center text-slate-400">Belum ada data pelanggan.</td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </main>
    </div>

    <!-- MODAL TAMBAH PELANGGAN -->
    <div id="modalTambah" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Tambah Pelanggan Baru</h3>
                <button onclick="tutupModalTambah()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="" method="POST" class="p-6 space-y-4 text-xs">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Pelanggan</label>
                    <input type="text" name="nama_pelanggan" required placeholder="Masukkan nama lengkap" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Kota Asal</label>
                    <input type="text" name="kota" required placeholder="Contoh: Ponorogo, Madiun" class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="tutupModalTambah()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" name="tambah_pelanggan" class="px-5 py-2 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Simpan Pelanggan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- MODAL EDIT PELANGGAN -->
    <div id="modalEdit" class="fixed inset-0 bg-slate-900/40 backdrop-blur-xs flex items-center justify-center hidden z-50 p-4">
        <div class="bg-white rounded-2xl shadow-xl w-full max-w-md overflow-hidden border border-slate-100">
            <div class="px-6 py-4 border-b border-slate-100 flex items-center justify-between bg-slate-50">
                <h3 class="font-bold text-slate-800 text-sm">Edit Data Pelanggan</h3>
                <button onclick="tutupModalEdit()" class="text-slate-400 hover:text-slate-600"><i class="fa-solid fa-xmark text-base"></i></button>
            </div>
            
            <form action="" method="POST" class="p-6 space-y-4 text-xs">
                <input type="hidden" id="editId" name="id_pelanggan">
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Nama Pelanggan</label>
                    <input type="text" id="editNama" name="nama_pelanggan" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>
                <div>
                    <label class="block font-semibold text-slate-600 mb-1">Kota Asal</label>
                    <input type="text" id="editKota" name="kota" required class="w-full px-3.5 py-2.5 border border-slate-300 rounded-xl focus:ring-2 focus:ring-indigo-600 focus:border-indigo-600 outline-none text-slate-800">
                </div>

                <div class="pt-2 flex items-center justify-end gap-3 border-t border-slate-100">
                    <button type="button" onclick="tutupModalEdit()" class="px-4 py-2 font-semibold text-slate-500 hover:bg-slate-100 rounded-xl transition">Batal</button>
                    <button type="submit" name="edit_pelanggan" class="px-5 py-2 font-semibold bg-indigo-600 hover:bg-indigo-700 text-white rounded-xl transition shadow-sm">Simpan Perubahan</button>
                </div>
            </form>
        </div>
    </div>

    <!-- JAVASCRIPT MODAL -->
    <script>
        function bukaModalTambah() {
            document.getElementById('modalTambah').classList.remove('hidden');
        }
        function tutupModalTambah() {
            document.getElementById('modalTambah').classList.add('hidden');
        }

        function bukaModalEdit(id, nama, kota) {
            document.getElementById('editId').value = id;
            document.getElementById('editNama').value = nama;
            document.getElementById('editKota').value = kota;
            document.getElementById('modalEdit').classList.remove('hidden');
        }
        function tutupModalEdit() {
            document.getElementById('modalEdit').classList.add('hidden');
        }
    </script>
</body>
</html>