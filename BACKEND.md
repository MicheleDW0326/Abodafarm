# Dokumentasi Backend AbodaFarm

Dokumen ini ditulis untuk membantu tim backend memahami struktur aplikasi saat ini, alur data utama, dan area yang paling mungkin berubah saat proyek dirombak dari jualan daging fresh menjadi jualan ternak organik hidup.

## 1. Gambaran Umum

Backend ini dibangun dengan:

- Laravel 13
- Livewire 4
- Fortify untuk autentikasi
- Spatie Laravel Permission untuk role dan permission
- SweetAlert untuk notifikasi UI

Fokus bisnis saat ini masih berada pada katalog produk, keranjang belanja, checkout, manajemen stok, dan dashboard admin. Untuk rencana baru, domain bisnis perlu bergeser dari produk potong/daging ke ternak hidup, jadi beberapa model dan istilah akan perlu diganti secara bertahap.

## 2. Alur Besar Aplikasi

Secara sederhana, alurnya seperti ini:

1. Pengunjung membuka halaman utama.
2. Pengunjung memilih produk dan menambahkannya ke keranjang.
3. Keranjang disimpan di session.
4. Pengunjung melakukan checkout.
5. Order disimpan ke database beserta item-itemnya.
6. User login diarahkan ke dashboard sesuai role.
7. Admin mengelola produk, stok, pesanan, pelanggan, staf, dan laporan.

## 3. Struktur Folder yang Penting

- `app/Livewire` berisi komponen halaman dan dashboard.
- `app/Models` berisi model Eloquent utama.
- `app/Actions/Fortify` berisi logika register dan reset password.
- `app/Http/Middleware` berisi pembatas akses berdasarkan role.
- `app/Http/Responses` berisi redirect setelah login.
- `routes/web.php` berisi route utama aplikasi.
- `database/migrations` berisi struktur tabel.
- `database/seeders` berisi data awal untuk development.

## 4. Autentikasi dan Role

Autentikasi dikelola oleh Fortify dan sudah mendukung:

- registrasi
- login
- reset password
- verifikasi email
- two-factor authentication

Konfigurasi utamanya ada di:

- `config/fortify.php`
- `app/Providers/FortifyServiceProvider.php`
- `app/Http/Responses/LoginResponse.php`

Saat user baru mendaftar, sistem otomatis memberi role `pelanggan` lewat `app/Actions/Fortify/CreateNewUser.php`.

Role yang dipakai saat ini:

- `admin`
- `staf`
- `pelanggan`

Middleware pembatas akses:

- `check.admin`
- `check.staff`
- `check.pelanggan`

## 5. Route Penting

File route utama ada di `routes/web.php`.

Route publik:

- `/` -> halaman home
- `/checkout` -> halaman checkout

Route setelah login:

- `/dashboard` -> redirect ke dashboard sesuai role

Route admin:

- `/admin`
- `/admin/produk`
- `/admin/pemesanan`
- `/admin/pelanggan`
- `/admin/staf`
- `/admin/laporan-keuangan`
- `/admin/pengaturan-toko`

Route staf dan pelanggan juga sudah dipisahkan, tetapi isi halamannya masih sederhana.

## 6. Model Data Utama

### User

Model user berada di `app/Models/User.php`.

Fitur penting:

- mendukung role dari Spatie
- mendukung two-factor auth
- password otomatis di-hash
- memiliki helper `initials()`

### Category

Model kategori berada di `app/Models/Category.php`.

Kategori saat ini dipakai untuk mengelompokkan produk.

### Product

Model produk berada di `app/Models/Product.php`.

Relasi:

- `Product belongsTo Category`
- `Product hasMany ProductStock`

Field penting:

- nama
- deskripsi
- harga
- gambar
- stok

### ProductStock

Model stok berada di `app/Models/ProductStock.php`.

Fungsi utamanya menyimpan histori penambahan stok dari supplier.

### Order dan OrderItem

Model order ada di `app/Models/Order.php` dan item order di `app/Models/OrderItem.php`.

Relasi:

- `Order hasMany OrderItem`
- `OrderItem belongsTo Order`

Field order yang penting:

- nomor order
- nama pelanggan
- telepon
- alamat
- catatan
- total item
- total amount
- status
- placed_at

## 7. Seed Data

Seeder utama ada di `database/seeders/DatabaseSeeder.php`.

Data awal yang disiapkan:

- role `admin`, `staf`, `pelanggan`
- kategori contoh
- produk contoh
- stok contoh
- akun demo untuk tiap role

Akun demo yang dibuat:

- admin: `royland@freshgo.test`
- staf: `michele@freshgo.test`
- pelanggan: `esau@freshgo.test`

## 8. Alur Checkout Saat Ini

Checkout dikelola di `app/Livewire/Checkout.php`.

Alurnya:

1. User mengisi data nama, telepon, alamat, dan catatan.
2. Sistem validasi form.
3. Sistem mengecek apakah keranjang kosong.
4. Order dibuat di dalam transaksi database.
5. Item order dibuat dari isi keranjang.
6. Session keranjang dibersihkan.
7. Nomor order ditampilkan ke user.

Keranjang saat ini disimpan di session dengan key `freshgo.cart`.

## 9. Dashboard Admin

Dashboard admin ada di `app/Livewire/Admin/Dashboard.php`.

Informasi yang dihitung:

- total pendapatan
- total pesanan
- pelanggan aktif
- rata-rata order
- pendapatan bulanan
- status order
- stok kritis

Catatan: dashboard ini masih sangat berorientasi pada transaksi produk, bukan pada alur ternak hidup.

## 10. Manajemen Produk dan Stok

Komponen `app/Livewire/Admin/ProductManagement.php` menangani:

- tambah produk
- edit produk
- hapus produk
- tambah stok
- pencarian produk

Saat stok ditambahkan, nilai stok produk ikut di-increment.

## 11. Halaman yang Masih Kerangka

Beberapa komponen admin sudah tersedia tetapi isinya masih minimal:

- `CustomerManagement`
- `StaffManagement`
- `FinancialReport`
- `StoreSetting`
- `OrderManagement`

Artinya struktur fiturnya sudah ada, tetapi implementasi detail masih perlu dibuat atau diperluas.

## 12. Hal yang Perlu Dirombak untuk Ternak Hidup

Karena target bisnis berubah, ini bagian yang paling mungkin perlu disesuaikan:

### Terminologi

- `Product` mungkin lebih cocok diganti menjadi `Livestock` atau `Animal`
- `Category` perlu disesuaikan dengan jenis ternak
- `ProductStock` bisa diubah menjadi histori stok hewan hidup, pakan, atau batch ternak
- `Order` perlu dipastikan cocok dengan model transaksi baru

### Field Data

Untuk ternak hidup, field yang mungkin perlu ditambah:

- jenis ternak
- umur
- bobot
- status kesehatan
- lokasi kandang
- batch/gelombang ternak
- supplier atau peternak asal
- tanggal masuk kandang
- kondisi siap jual

### Alur Bisnis

Perlu dipikirkan apakah checkout masih berbasis katalog umum, atau berubah menjadi:

- request ternak hidup
- booking stok ternak
- negosiasi harga per ekor / per kelompok
- pengiriman atau pengambilan langsung

### Dashboard

Dashboard admin sebaiknya nanti tidak hanya menampilkan pendapatan dan order, tetapi juga:

- jumlah ternak per kandang
- status kesehatan ternak
- stok siap jual
- ternak masuk dan keluar
- kematian atau penyusutan

## 13. Urutan Belajar yang Disarankan

Kalau backend ingin mempelajari proyek ini dengan cepat, urutannya bisa seperti ini:

1. `routes/web.php`
2. `app/Providers/FortifyServiceProvider.php`
3. `app/Http/Responses/LoginResponse.php`
4. `app/Models/User.php`
5. `app/Models/Product.php`
6. `app/Models/Order.php`
7. `app/Livewire/Checkout.php`
8. `app/Livewire/Admin/Dashboard.php`
9. `app/Livewire/Admin/ProductManagement.php`
10. `database/seeders/DatabaseSeeder.php`

## 14. Kesimpulan

Backend ini sudah punya fondasi yang cukup rapi untuk sistem penjualan berbasis role dan order. Namun, karena bisnis akan bergeser ke ternak hidup, perubahan terbesar nanti ada di model data, istilah domain, dashboard admin, dan alur transaksi.

Kalau ingin, dokumentasi ini bisa dilanjutkan menjadi versi teknis yang lebih dalam, misalnya berupa:

- ERD sederhana
- flow login dan redirect role
- daftar endpoint dan halaman
- rencana refactor dari produk potong ke ternak hidup
