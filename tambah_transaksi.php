<?php
// tambah_transaksi.php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id_pelanggan  = intval($_POST['id_pelanggan']);
    $id_produk     = intval($_POST['id_produk']);
    $qty           = intval($_POST['qty']);
    $tanggal       = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $total_belanja = floatval($_POST['total_belanja']);
    $nama_produk   = mysqli_real_escape_string($koneksi, $_POST['nama_produk_txt']);

    // 1. Simpan data ke tabel tbl_transaksi
    $query_transaksi = "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja) VALUES ('$tanggal', '$id_pelanggan', '$total_belanja')";
    
    if (mysqli_query($koneksi, $query_transaksi)) {
        $id_transaksi_baru = mysqli_insert_id($koneksi);

        // 2. Simpan rincian ke tabel tbl_detail_transaksi (menggunakan harga produk saat ini)
        $q_harga = mysqli_query($koneksi, "SELECT harga FROM tbl_produk WHERE id_produk = $id_produk");
        $d_harga = mysqli_fetch_assoc($q_harga);
        $harga_satuan = $d_harga['harga'] ?? 0;

        mysqli_query($koneksi, "INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, nama_produk, jumlah, harga) VALUES ('$id_transaksi_baru', '$id_produk', '$nama_produk', '$qty', '$harga_satuan')");

        // 3. Kurangi stok produk secara otomatis di tabel tbl_produk
        mysqli_query($koneksi, "UPDATE tbl_produk SET stok = stok - $qty WHERE id_produk = $id_produk");

        // Redirect kembali ke index dengan membawa ID transaksi untuk menampilkan struk
        header("Location: index.php?status=success&trx_id=" . $id_transaksi_baru);
        exit();
    } else {
        echo "Gagal memproses checkout: " . mysqli_error($koneksi);
    }
}
?>