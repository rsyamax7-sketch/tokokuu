<?php
// tambah_transaksi.php
require_once 'koneksi.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama_pelanggan = mysqli_real_escape_string($koneksi, $_POST['nama_pelanggan']);
    $kota           = mysqli_real_escape_string($koneksi, $_POST['kota']);
    $tanggal        = mysqli_real_escape_string($koneksi, $_POST['tanggal']);
    $total_belanja  = floatval($_POST['total_belanja']);

    // 1. Tambah pelanggan baru
    $query_pelanggan = "INSERT INTO tbl_pelanggan (nama_pelanggan, kota) VALUES ('$nama_pelanggan', '$kota')";
    
    if (mysqli_query($koneksi, $query_pelanggan)) {
        $id_pelanggan = mysqli_insert_id($koneksi);

        // 2. Tambah transaksi menggunakan ID pelanggan baru
        $query_transaksi = "INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja) VALUES ('$tanggal', '$id_pelanggan', '$total_belanja')";
        
        if (mysqli_query($koneksi, $query_transaksi)) {
            header("Location: index.php?status=success");
            exit();
        } else {
            echo "Gagal menambahkan transaksi: " . mysqli_error($koneksi);
        }
    } else {
        echo "Gagal menambahkan pelanggan: " . mysqli_error($koneksi);
    }
}
?>