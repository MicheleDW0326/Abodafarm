# Dokumentasi Frontend AbodaFarm

Dokumen ini ditujukan untuk tim backend dan frontend agar mudah memahami bagaimana tampilan, layout, dan interaksi UI dibangun di proyek ini. Fokusnya adalah struktur frontend saat ini, bukan sekadar daftar file.

## 1. Gambaran Umum

Frontend proyek ini dibangun dengan pendekatan server-rendered + interaktif ringan:

- Blade untuk template utama
- Livewire untuk halaman yang butuh state dan interaksi
- Alpine.js untuk state UI sederhana di browser
- Tailwind CSS v4 untuk styling
- Vite untuk bundling asset

Artinya, aplikasi ini bukan SPA penuh. Mayoritas UI dirender dari Laravel, lalu diperkuat dengan komponen Livewire dan sedikit state lokal dari Alpine.

## 2. Stack Frontend

Dependensi frontend utama ada di `package.json`:

- `tailwindcss`
- `@tailwindcss/vite`
- `alpinejs`
- `vite`
- `laravel-vite-plugin`

File entry utama:

- `resources/css/app.css`
- `resources/js/app.js`

`resources/js/app.js` sangat tipis karena Alpine disediakan dan diinisialisasi oleh Livewire.

## 3. Struktur Frontend yang Penting

Folder dan file yang paling relevan:

- `resources/views/layouts` untuk layout utama
- `resources/views/pages/auth` untuk halaman login, register, reset password, dan sejenisnya
- `resources/views/livewire` untuk view komponen Livewire
- `resources/views/components` untuk komponen Blade reusable
- `resources/css/app.css` untuk theme dan utility tambahan

## 4. Layout Utama

Ada tiga layout frontend yang paling penting:

### Layout Admin

File: `resources/views/layouts/main.blade.php`

Layout ini dipakai untuk halaman admin. Ciri utamanya:

- sidebar tetap di kiri
- header sticky di atas
- topbar berisi judul halaman, pencarian, notifikasi, dan tanggal
- memakai Alpine untuk state sidebar collapse dan account menu
- memakai localStorage untuk menyimpan status sidebar collapse

### Layout Guest / Publik

File: `resources/views/layouts/guest-home.blade.php`

Dipakai untuk halaman publik seperti home dan checkout. Layout ini lebih ringan dibanding admin dan cocok untuk tampilan landing page atau flow pembelian.

### Layout Auth

File: `resources/views/layouts/auth.blade.php`

Dipakai oleh halaman login, register, dan halaman auth lain dari Fortify.

## 5. Navigasi dan Sidebar

Sidebar utama ada di `resources/views/layouts/app/sidebar.blade.php`.

Sidebar ini:

- menyesuaikan menu berdasarkan role user
- menampilkan menu admin, staf, atau pelanggan sesuai hak akses
- bisa collapse di desktop
- bisa dibuka sebagai overlay di mobile
- menyimpan state collapse di localStorage dengan key `freshgo.sidebarCollapsed`

Menu admin saat ini mencakup:

- Dashboard
- Manajemen Produk
- Manajemen Pesanan
- Manajemen Pelanggan
- Laporan Keuangan
- Manajemen Staf
- Pengaturan Toko

Sidebar memakai ikon dari partial Blade di `resources/views/livewire/icons`.

## 6. Komponen Interaktif

Frontend memakai dua lapis interaksi:

### Livewire

Dipakai untuk state yang perlu sinkron ke server, misalnya:

- halaman home dengan keranjang
- halaman checkout
- halaman admin dashboard
- product management

### Alpine.js

Dipakai untuk state lokal seperti:

- buka tutup sidebar
- buka tutup menu akun
- show/hide password di auth form

## 7. Halaman Publik

### Home

File komponen: `app/Livewire/Home.php`

View terkait: `resources/views/livewire/home.blade.php`

Halaman ini memuat:

- hero slides
- kategori
- daftar produk
- highlight brand
- alur delivery
- testimoni
- FAQ
- cart mini state

Keranjang disimpan di session dengan key `freshgo.cart`.

### Checkout

File komponen: `app/Livewire/Checkout.php`

View terkait: `resources/views/livewire/checkout.blade.php`

Halaman ini menampilkan form checkout dan ringkasan cart. Setelah submit, order dibuat lewat transaksi database.

## 8. Halaman Auth

Halaman auth ada di `resources/views/pages/auth`.

Yang paling penting:

- `login.blade.php`
- `register.blade.php`
- `forgot-password.blade.php`
- `reset-password.blade.php`
- `verify-email.blade.php`
- `confirm-password.blade.php`
- `two-factor-challenge.blade.php`

Karakter UI auth saat ini:

- card modern
- form lebar penuh
- icon di dalam input
- tombol besar dan jelas
- password visibility toggle dengan Alpine

## 9. Styling dan Theme

File styling utama ada di `resources/css/app.css`.

Poin penting:

- Tailwind diimport langsung dengan `@import 'tailwindcss';`
- source scan diarahkan ke folder views dan pagination Laravel
- font default app memakai `Instrument Sans`
- admin layout memakai font `Outfit`
- ada color token zinc dan accent yang sudah disiapkan
- ada animasi ringan untuk item sidebar dan account menu
- `prefers-reduced-motion` sudah diperhatikan

Hal ini berarti frontend sudah punya fondasi theme, tapi masih bisa dirombak cukup mudah jika perlu identitas visual baru.

## 10. Pola UI yang Sudah Dipakai

Frontend ini cenderung memakai pola berikut:

- layout shell yang konsisten
- cards dengan radius besar
- sidebar gelap untuk admin
- form auth yang bersih dan fokus ke konversi
- penggunaan shadow dan gradient secara selektif
- mobile-first behavior untuk navigasi

## 11. Komponen Reusable

Beberapa komponen Blade yang penting:

- `resources/views/components/app-logo.blade.php`
- `resources/views/components/app-logo-icon.blade.php`
- `resources/views/components/auth-header.blade.php`
- `resources/views/components/auth-session-status.blade.php`
- `resources/views/components/desktop-user-menu.blade.php`
- `resources/views/components/placeholder-pattern.blade.php`
- `resources/views/components/alerts/sweet-alert.blade.php`

Komponen reusable ini membantu menjaga konsistensi visual di seluruh halaman.

## 12. Dashboard Admin dari Sisi Frontend

Frontend admin saat ini lebih berfungsi sebagai shell dashboard daripada visualisasi yang sangat kompleks.

Contoh halaman dashboard sementara:

- `resources/views/dashboard.blade.php`

Halaman ini masih placeholder dan bisa dianggap sebagai template dasar untuk dashboard role lain.

## 13. Ikon dan Visual Aid

Folder `resources/views/livewire/icons` berisi banyak ikon SVG partial.

Keuntungannya:

- tidak perlu library ikon besar
- mudah dikontrol via Blade partial
- fleksibel untuk theme admin

## 14. Hal yang Harus Diperhatikan Saat Pivot ke Ternak Hidup

Karena bisnis akan berubah dari daging fresh ke ternak hidup, frontend juga perlu disesuaikan.

### Copywriting

Semua istilah yang berbau produk potong perlu diganti, misalnya:

- produk
- daging fresh
- seafood
- bahan segar

Menjadi istilah yang lebih cocok seperti:

- ternak hidup
- sapi / kambing / ayam hidup
- bobot ternak
- status siap jual
- booking ternak

### Visual Asset

Gambar hero, katalog, dan kartu produk sekarang masih bernuansa fresh food. Untuk pivot baru, aset visual harus diganti ke:

- kandang
- ternak hidup
- peternakan
- proses cek kesehatan ternak
- pengiriman ternak

### Alur UX

Alur yang semula cocok untuk belanja produk bisa jadi perlu diubah menjadi:

- pilih jenis ternak
- lihat detail bobot dan kondisi
- booking / request stok
- konfirmasi pengiriman atau pengambilan
- checkout dengan parameter baru

### Dashboard

Widget dashboard admin sebaiknya nanti menampilkan data yang relevan dengan ternak hidup, misalnya:

- jumlah ternak per kandang
- status kesehatan
- stok siap jual
- ternak masuk dan keluar
- riwayat perawatan

## 15. Urutan Belajar Frontend yang Disarankan

Kalau ingin memahami frontend proyek ini dengan cepat, urutannya bisa seperti ini:

1. `resources/css/app.css`
2. `resources/views/layouts/main.blade.php`
3. `resources/views/layouts/guest-home.blade.php`
4. `resources/views/layouts/auth.blade.php`
5. `resources/views/layouts/app/sidebar.blade.php`
6. `resources/views/pages/auth/login.blade.php`
7. `resources/views/pages/auth/register.blade.php`
8. `resources/views/livewire/home.blade.php`
9. `resources/views/livewire/checkout.blade.php`
10. `resources/views/components/desktop-user-menu.blade.php`

## 16. Kesimpulan

Frontend proyek ini sudah punya fondasi yang jelas: Blade + Livewire + Alpine + Tailwind, dengan layout yang dipisah antara public, auth, dan admin. Untuk pivot ke ternak hidup, perubahan terbesar bukan pada teknis rendering, tetapi pada identitas visual, copywriting, alur user, dan isi widget/dashboard.

Kalau kamu mau, dokumentasi frontend ini bisa saya lanjutkan menjadi versi yang lebih praktis, misalnya:

- peta halaman frontend
- daftar komponen reusable
- daftar class Tailwind penting yang dipakai
- checklist redesign untuk pivot ke ternak hidup
