📚 Pembelajaran Database MySQL & MariaDB

Repository ini berisi materi dan praktik pembelajaran database menggunakan **MySQL/MariaDB**, mulai dari pengelolaan tabel hingga fitur lanjutan database.

## 🎯 Tujuan Pembelajaran

- Memahami konsep dan penggunaan Database View.
- Memahami penggunaan JOIN untuk menggabungkan tabel.
- Mempelajari Subquery dalam SQL.
- Membuat dan menggunakan Stored Procedure.
- Membuat dan menggunakan Stored Function.
- Memahami serta menerapkan Database Trigger.

## 📖 Daftar Materi

| Bab | Materi | Keterangan |
|---|---|---|
| 1 | Database View | Membuat tabel virtual |
| 2 | JOIN | Menggabungkan data antar tabel |
| 3 | Subquery | Query di dalam query |
| 4 | Stored Procedure | Membuat prosedur SQL |
| 5 | Stored Function | Membuat fungsi SQL |
| 6 | Database Trigger | Menjalankan perintah otomatis |

## 🛠️ Teknologi yang Digunakan

- MySQL
- MariaDB
- phpMyAdmin
- SQL
- XAMPP

## 💻 Contoh Query SQL

### 1. Membuat View

```sql
CREATE VIEW view_produk AS
SELECT * FROM produk;
```

### 2. INNER JOIN

```sql
SELECT *
FROM produk
INNER JOIN kategori
ON produk.id_kategori = kategori.id_kategori;
```

### 3. LEFT JOIN

```sql
SELECT *
FROM produk
LEFT JOIN kategori
ON produk.id_kategori = kategori.id_kategori;
```

### 4. Subquery

```sql
SELECT * FROM produk
WHERE harga > (
    SELECT AVG(harga) FROM produk
);
```

### 5. Stored Procedure

```sql
DELIMITER //

CREATE PROCEDURE tampil_produk()
BEGIN
    SELECT * FROM produk;
END //

DELIMITER ;

CALL tampil_produk();
```

### 6. Database Trigger

```sql
DELIMITER //

CREATE TRIGGER setelah_tambah_produk
AFTER INSERT ON produk
FOR EACH ROW
BEGIN
    INSERT INTO log_produk (nama_produk)
    VALUES (NEW.nama_produk);
END //

DELIMITER ;
```

## 📂 Struktur Repository

```text
Pembelajaran-Database/
│
├── BAB-1-Database-View/
├── BAB-2-JOIN/
├── BAB-3-Subquery/
├── BAB-4-Stored-Procedure/
├── BAB-5-Stored-Function/
├── BAB-6-Database-Trigger/
│
└── README.md
```

## 🚀 Cara Menjalankan

1. Install XAMPP.
2. Aktifkan Apache dan MySQL.
3. Buka phpMyAdmin melalui browser.
4. Buat database baru.
5. Masukkan query SQL sesuai materi.
6. Jalankan dan periksa hasil query.

## 👨‍💻 Penulis

**Nama:** Rasya  
**Kelas:** XII RPL 2  
**Jurusan:** Rekayasa Perangkat Lunak (RPL)

## 📝 Kesimpulan

Pembelajaran ini bertujuan untuk meningkatkan pemahaman tentang pengelolaan database menggunakan SQL serta mempraktikkan fitur-fitur lanjutan MySQL/MariaDB untuk membangun sistem aplikasi yang lebih efektif, terstruktur, dan efisien.

---

⭐ Terima kasih telah mengunjungi repository ini!