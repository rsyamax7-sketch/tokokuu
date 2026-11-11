# 📚 Pembelajaran Database MySQL/MariaDB & Sistem POS Toko Kita

Repository ini berisi materi dan praktik pembelajaran database menggunakan **MySQL/MariaDB**, mulai dari penguasaan konsep SQL tingkat dasar hingga lanjutan, serta implementasinya dalam membangun aplikasi web kasir full-stack (**Toko Kita**) berbasis Role-Based Access Control (RBAC).

---

## 🎯 Tujuan Pembelajaran & Proyek

- Memahami dan mengimplementasikan konsep Database View, JOIN, Subquery, Stored Procedure, Stored Function, serta Database Trigger.
- Menerapkan **Role-Based Access Control (RBAC)** untuk memisahkan hak akses antara panel **Admin** (biru/indigo) dan **Kasir** (hijau/emerald) guna mencegah *session bleeding*.
- Mengelola data inventaris produk lengkap dengan fitur **unggah foto/gambar produk (*file upload*)**.
- Membangun alur transaksi *multi-item* berbasis modal keranjang belanja, kalkulasi uang tunai/kembalian otomatis, hingga pencetakan struk cetak thermal ukuran 58mm.

---

## 📖 Daftar Materi Database

| Bab | Materi | Keterangan |
|---|---|---|
| 1 | Database View | Membuat tabel virtual untuk menyederhanakan query |
| 2 | JOIN | Menggabungkan data relasional antar tabel |
| 3 | Subquery | Query bersarang di dalam query utama |
| 4 | Stored Procedure | Membuat instruksi prosedur SQL yang dapat dipanggil kembali |
| 5 | Stored Function | Membuat fungsi khusus SQL dengan pengembalian nilai |
| 6 | Database Trigger | Menjalankan aksi query otomatis saat ada manipulasi data (INSERT/UPDATE/DELETE) |

---

## 🛠️ Teknologi & Environment

- **Database Management:** MySQL, MariaDB, phpMyAdmin, XAMPP
- **Backend & Scripting:** PHP Native (Session handling, File Upload, MySQLi)
- **Frontend & UI/UX:** HTML5, JavaScript (DOM & Modal Handler), Tailwind CSS, Font Awesome 6
- **Hardware Integration:** Thermal Printer (Epson TM-T82X / Standard 58mm POS Receipt Printer)

---

## 💻 Contoh Query SQL & Fitur Sistem

### 1. DDL & Alter Tabel Produk (Penambahan Kolom Gambar)
```sql
ALTER TABLE tbl_produk ADD COLUMN gambar VARCHAR(255) DEFAULT NULL;
```

### 2. Relasi Tabel Produk & Kategori (LEFT JOIN)
```sql
SELECT p.*, k.nama_kategori 
FROM tbl_produk p 
LEFT JOIN tbl_kategori k ON p.id_kategori = k.id_kategori
ORDER BY p.id_produk DESC;
```

### 3. Transaksi Multi-Item & Detail Pembelian
```sql
-- 1. Menyimpan data transaksi utama
INSERT INTO tbl_transaksi (tanggal, id_pelanggan, total_belanja) 
VALUES ('2026-11-11', 1, 150000);

-- 2. Menyimpan detail item dan memotong stok produk secara otomatis
INSERT INTO tbl_detail_transaksi (id_transaksi, id_produk, nama_produk, jumlah, harga) 
VALUES (1, 5, 'Kabel LAN', 2, 15000);

UPDATE tbl_produk SET stok = stok - 2 WHERE id_produk = 5;
```

### 4. Contoh Stored Procedure & Trigger
```sql
-- Stored Procedure: Menampilkan seluruh produk
DELIMITER //
CREATE PROCEDURE tampil_produk()
BEGIN
    SELECT * FROM tbl_produk;
END //
DELIMITER ;

-- Trigger: Pencatatan Log Produk Baru
DELIMITER //
CREATE TRIGGER setelah_tambah_produk
AFTER INSERT ON tbl_produk
FOR EACH ROW
BEGIN
    INSERT INTO log_produk (nama_produk, waktu)
    VALUES (NEW.nama_produk, NOW());
END //
DELIMITER ;
```

---

## 📂 Struktur Repository

```text
tokoku/
│
├── uploads/                      # Direktori fisik tempat menyimpan file gambar produk
├── dashboard_kasir.php           # Dashboard ringkasan khusus role Kasir (Emerald Theme)
├── data_produk_kasir.php         # Katalog produk, keranjang belanja, & cetak struk kasir
├── tambah_barang_kasir.php       # Formulir input barang baru & upload gambar
├── data_pelanggan_kasir.php      # Pengelolaan data pelanggan untuk Kasir
├── laporan_penjualan.php         # Rekapitulasi laporan keuangan & statistik Admin
├── index.php                     # Dashboard & panel kontrol Administrator (Indigo Theme)
├── koneksi.php                   # File konfigurasi koneksi database PHP & MySQL
└── README.md                     # Dokumentasi utama proyek & pembelajaran
```

---

## 🚀 Cara Menjalankan Project

1. Pastikan aplikasi **XAMPP** sudah terinstall di komputer Anda.
2. Buka Control Panel XAMPP, lalu jalankan modul **Apache** dan **MySQL**.
3. Buat database baru bernama `tokoku` melalui phpMyAdmin (`http://localhost/phpmyadmin`).
4. Import struktur tabel yang diperlukan (`tbl_produk`, `tbl_kategori`, `tbl_pelanggan`, `tbl_transaksi`, `tbl_detail_transaksi`).
5. Unduh atau *clone* repository ini ke direktori folder XAMPP:
   `C:\xampp\htdocs\tokoku`
6. Buat folder bernama `uploads` di dalam direktori `tokoku` untuk menampung file foto produk.
7. Akses aplikasi melalui browser:
   - Panel Kasir: `http://localhost/tokoku/data_produk_kasir.php`
   - Panel Admin: `http://localhost/tokoku/index.php`

---

## 👨‍💻 Penulis / Profil Developer

- **Nama:** Rasya Anandityo Keyhsa
- **Kelas:** XII RPL 2
- **Jurusan:** Rekayasa Perangkat Lunak (RPL)

---

## 📝 Kesimpulan

Proyek dan materi pembelajaran ini berhasil menggabungkan dasar-dasar pengelolaan database relasional SQL dengan pembangunan aplikasi POS (Point of Sales) dunia nyata. Aplikasi dirancang secara efisien, responsif, memiliki keamanan pemisahan peran (*role clearance*), serta terintegrasi dengan perangkat keras pencetak nota thermal.

---
⭐ *Terima kasih telah mengunjungi repository ini!*
