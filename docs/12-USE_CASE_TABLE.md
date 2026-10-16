# Use Case Specification Table
## AbodaFarm System

---

## Tabel Use Case

### 1. Authentication Module

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-01 | Registrasi Akun | Guest | Guest mengklik "Register" | Guest belum memiliki akun | Guest memiliki akun aktif | User mendaftarkan akun baru dengan email, password, dan role selection |
| UC-02 | Login | All Users | User memasukkan kredensial | User memiliki akun aktif | User ter-authenticated | User login dengan email dan password |
| UC-03 | Logout | All Users | User mengklik "Logout" | User sudah login | Session berakhir | User keluar dari sistem |
| UC-04 | Verifikasi Email | Guest | Link verifikasi diklik | User mendaftar | Email terverifikasi | Konfirmasi email untuk aktivasi akun |
| UC-05 | Lupa Password | Guest | Guest mengklik "Forgot Password" | Guest memiliki email terdaftar | Email reset dikirim | Reset password via email |
| UC-06 | Reset Password | Guest | Link reset diklik | Guest menerima email reset | Password berhasil diubah | Mengatur password baru |
| UC-07 | Two-Factor Authentication | All Users | User enable 2FA | User login | 2FA aktif | Menambah lapisan keamanan dengan 2FA |
| UC-08 | Ganti Password | All Users | User ubah password | User tahu password lama | Password berhasil diubah | Mengubah password akun |
| UC-09 | Lihat Profil | All Users | User akses menu profil | User login | Profil ditampilkan | Melihat data profil sendiri |
| UC-10 | Edit Profil | All Users | User ubah data profil | User login | Profil ter-update | Mengubah data profil |
| UC-11 | Upload Avatar | All Users | User upload foto | User login, file valid | Avatar ter-upload | Mengupload foto profil |
| UC-12 | Verifikasi Akun | Pelanggan, Peternak | Admin memverifikasi | Akun belum diverifikasi | Status verifikasi berubah | Admin memverifikasi akun user |

---

### 2. Customer Module

#### 2.1 Catalog & Browsing

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-20 | Lihat Katalog Produk | Guest, Pelanggan | User mengakses halaman shop | - | Daftar produk ditampilkan | Melihat daftar produk dengan pagination |
| UC-21 | Search Produk | Guest, Pelanggan | User memasukkan keyword | - | Hasil pencarian ditampilkan | Mencari produk berdasarkan keyword |
| UC-22 | Filter Produk | Guest, Pelanggan | User memilih filter | - | Produk ter-filter | Filter berdasarkan kategori, tipe, harga, lokasi |
| UC-23 | Lihat Detail Produk | Guest, Pelanggan | User klik produk | Produk ada | Detail produk ditampilkan | Melihat informasi lengkap produk |
| UC-24 | Lihat Review Produk | Guest, Pelanggan | User lihat review | Ada review | Review ditampilkan | Melihat review dan rating produk |
| UC-25 | Lihat Galeri Produk | Guest, Pelanggan | User lihat foto produk | Produk punya galeri | Foto galeri ditampilkan | Melihat foto-foto produk |

#### 2.2 Shopping Cart

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-30 | Tambah ke Keranjang | Pelanggan | User klik "Add to Cart" | Stok tersedia | Item di keranjang | Menambahkan produk ke shopping cart |
| UC-31 | Lihat Keranjang | Pelanggan | User akses keranjang | Ada item | Keranjang ditampilkan | Melihat isi keranjang belanja |
| UC-32 | Update Jumlah | Pelanggan | User ubah kuantitas | Item di keranjang | Jumlah ter-update | Mengubah jumlah item di keranjang |
| UC-33 | Hapus Item | Pelanggan | User klik hapus | Item di keranjang | Item dihapus | Menghapus item dari keranjang |
| UC-34 | Kosongkan Keranjang | Pelanggan | User klik "Clear Cart" | Ada item di keranjang | Keranjang kosong | Menghapus semua item |
| UC-35 | Lihat Subtotal | Pelanggan | User lihat ringkasan | Item di keranjang | Subtotal ditampilkan | Melihat total harga sebelum checkout |

#### 2.3 Checkout & Address

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-40 | Checkout | Pelanggan | User klik "Checkout" | Ada item di keranjang | Halaman checkout | Memulai proses checkout |
| UC-41 | Kelola Alamat | Pelanggan | User akses pengaturan alamat | User login | Daftar alamat ditampilkan | CRUD alamat pengiriman |
| UC-42 | Tambah Alamat | Pelanggan | User klik "Add Address" | User login | Alamat baru ditambahkan | Menambah alamat baru |
| UC-43 | Edit Alamat | Pelanggan | User edit alamat | Ada alamat tersimpan | Alamat ter-update | Mengubah alamat yang ada |
| UC-44 | Hapus Alamat | Pelanggan | User hapus alamat | Ada alamat tersimpan | Alamat dihapus | Menghapus alamat |
| UC-45 | Pilih Alamat | Pelanggan | User pilih alamat checkout | Ada alamat tersimpan | Alamat dipilih | Memilih alamat untuk pengiriman |
| UC-46 | Set Alamat Default | Pelanggan | User set default | Ada >1 alamat | Alamat default ter-set | Mengatur alamat utama |
| UC-47 | Tambah Catatan | Pelanggan | User tambahkan catatan | Checkout aktif | Catatan tersimpan | Menambahkan catatan pesanan |

#### 2.4 Payment

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-50 | Pembayaran | Pelanggan | User pilih metode bayar | Checkout aktif | Halaman pembayaran | Bayar pesanan via Midtrans |
| UC-51 | Pilih Metode Bayar | Pelanggan | User pilih metode | Halaman pembayaran | Metode dipilih | Pilih Bank Transfer, E-Wallet, Credit Card, COD |
| UC-52 | Konfirmasi Bayar | Pelanggan | User konfirmasi | Metode dipilih | Payment initiated | Konfirmasi sebelum bayar |
| UC-53 | Gunakan Kode Promo | Pelanggan | User masukkan kode | Kode valid | Diskon diterapkan | Menggunakan kode promo saat checkout |
| UC-54 | Lihat Ringkasan | Pelanggan | User lihat ringkasan | Checkout aktif | Ringkasan ditampilkan | Melihat ringkasan pesanan |
| UC-55 | Hitung Ulang | Pelanggan | Item/stok berubah | Checkout aktif | Total dihitung ulang | Update perhitungan harga |
| UC-56 | Bayar Ulang | Pelanggan | Payment gagal | Ada payment gagal | Halaman pembayaran | Retry pembayaran |
| UC-57 | Batalkan Pesanan | Pelanggan | User klik cancel | Pesanan belum dibayar | Pesanan dibatalkan | Membatalkan pesanan |

#### 2.5 Booking/Inquiry (Live Animals)

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-60 | Buat Booking | Pelanggan | User klik "Booking" | Produk Live dipilih | Booking dibuat | Membuat inquiry untuk ternak |
| UC-61 | Pilih Jadwal | Pelanggan | User pilih tanggal | Booking aktif | Jadwal dipilih | Pilih jadwal kunjungan |
| UC-62 | Upload Dokumen | Pelanggan | User upload file | Booking aktif | Dokumen tersimpan | Upload KTP, surat izin |
| UC-63 | Negosiasi Harga | Pelanggan | User ajukan harga | Booking aktif | Negosiasi dimulai | Ajukan harga tawaran |
| UC-64 | Approve Booking | Pelanggan | User approve | Farmer mengirim penawaran | Booking disetujui | Menyetujui penawaran peternak |
| UC-65 | Reject Booking | Pelanggan | User reject | Farmer mengirim penawaran | Booking ditolak | Menolak penawaran |
| UC-66 | Lihat Detail Booking | Pelanggan | User lihat booking | Booking ada | Detail ditampilkan | Melihat info lengkap booking |
| UC-67 | Upload Tanda Tangan | Pelanggan | User tanda tangan | Kontrak ditampilkan | Tanda tangan tersimpan | Digital signature untuk kontrak |

#### 2.6 Order Tracking

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-70 | Lacak Pesanan | Pelanggan | User lacak pesanan | Pesanan ada | Status ditampilkan | Melacak status pesanan real-time |
| UC-71 | Lihat Notifikasi | Pelanggan | User buka notifikasi | Ada notifikasi | Notifikasi ditampilkan | Melihat daftar notifikasi |
| UC-72 | Konfirmasi Penerimaan | Pelanggan | User terima barang | Pesanan dikirim | Pesanan selesai | Konfirmasi barang sudah diterima |
| UC-73 | Riwayat Pesanan | Pelanggan | User buka riwayat | User login | Riwayat ditampilkan | Melihat semua pesanan |
| UC-74 | Filter Pesanan | Pelanggan | User filter | Riwayat ditampilkan | Hasil filter | Filter berdasarkan status |
| UC-75 | Search Pesanan | Pelanggan | User search | Riwayat ditampilkan | Hasil search | Cari pesanan tertentu |
| UC-76 | Print Invoice | Pelanggan | User print | Pesanan ada | Invoice dicetak | Cetak invoice pesanan |
| UC-77 | Download Invoice | Pelanggan | User download | Pesanan ada | Invoice di-download | Download PDF invoice |

#### 2.7 Reviews & Ratings

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-80 | Review Produk | Pelanggan | User submit review | Pesanan selesai | Review tayang | Memberikan review produk |
| UC-81 | Review Peternak | Pelanggan | User submit review | Pesanan selesai | Review tayang | Memberikan review peternak |
| UC-82 | Upload Foto Review | Pelanggan | User upload foto | Review aktif | Foto tersimpan | Upload foto saat review |
| UC-83 | Edit Review | Pelanggan | User edit review | Review ada | Review ter-update | Mengubah review yang sudah dibuat |
| UC-84 | Hapus Review | Pelanggan | User hapus review | Review ada | Review dihapus | Menghapus review |
| UC-85 | Balas Review | Peternak | Peternak balas | Review ada | Balasan tayang | Memberi balasan review |

#### 2.8 Wishlist & Favorites

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-90 | Tambah Favorit | Pelanggan | User klik hati | Produk ada | Ditambahkan ke favorit | Menambah produk ke wishlist |
| UC-91 | Hapus Favorit | Pelanggan | User klik hapus | Produk favorit | Dihapus dari favorit | Menghapus dari wishlist |
| UC-92 | Lihat Favorit | Pelanggan | User buka favorit | Ada favorit | Daftar ditampilkan | Melihat semua produk favorit |
| UC-93 | Share Produk | Pelanggan | User share | Produk ada | Link dishare | Bagikan produk ke media sosial |

#### 2.9 Vouchers & Wallet

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-100 | Klaim Voucher | Pelanggan | User klaim | Voucher available | Voucher diklaim | Klaim voucher dari kampanye |
| UC-101 | Lihat Voucher | Pelanggan | User buka voucher | Ada voucher | Daftar ditampilkan | Melihat voucher yang dimiliki |
| UC-102 | Lihat Wallet | Pelanggan | User buka wallet | User login | Wallet ditampilkan | Melihat saldo dan poin loyalitas |
| UC-103 | Topup Wallet | Pelanggan | User topup | User login | Saldo bertambah | Menambah saldo wallet |
| UC-104 | Transfer Saldo | Pelanggan | User transfer | Saldo cukup | Saldo dipindahkan | Transfer ke user lain |
| UC-105 | Withdraw Saldo | Pelanggan | User withdraw | Saldo cukup | Saldo berkurang | Tarik ke rekening bank |

---

### 3. Farmer Module

#### 3.1 Product Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-110 | Dashboard Produk | Peternak | Peternak buka dashboard | Login sebagai peternak | Dashboard ditampilkan | Overview manajemen produk |
| UC-111 | Tambah Produk | Peternak | Peternak klik "Add" | Login sebagai peternak | Produk dibuat | Menambahkan produk baru |
| UC-112 | Edit Produk | Peternak | Peternak edit produk | Produk ada | Produk ter-update | Mengubah data produk |
| UC-113 | Hapus Produk | Peternak | Peternak hapus produk | Produk ada | Produk dihapus | Menghapus produk |
| UC-114 | Update Foto Produk | Peternak | Peternak upload foto | Produk ada | Foto ter-upload | Mengubah foto produk |
| UC-115 | Kelola Stok | Peternak | Peternak buka stok | Login sebagai peternak | Stok ditampilkan | Update stok inventory |
| UC-116 | Update Stok | Peternak | Peternak update | Stok ada | Stok ter-update | Mengubah jumlah stok |
| UC-117 | Riwayat Stok | Peternak | Peternak lihat history | Stok ada | History ditampilkan | Melihat perubahan stok |
| UC-118 | Bulk Update Stok | Peternak | Peternak bulk update | File Excel siap | Stok ter-update | Update banyak produk |

#### 3.2 Livestock Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-120 | Kelola Care Records | Peternak | Peternak buka records | Ternak ada | Records ditampilkan | Manajemen perawatan ternak |
| UC-121 | Catat Feeding | Peternak | Peternak catat | Ternak ada | Feeding dicatat | Record jadwal makan |
| UC-122 | Catat Vaccination | Peternak | Peternak catat | Ternak ada | Vaccination dicatat | Record vaksinasi |
| UC-123 | Catat Health Check | Peternak | Peternak catat | Ternak ada | Health check dicatat | Record pemeriksaan kesehatan |
| UC-124 | Catat Cleaning | Peternak | Peternak catat | Ternak ada | Cleaning dicatat | Record pembersihan kandang |
| UC-125 | Lihat Riwayat Care | Peternak | Peternak lihat history | Records ada | History ditampilkan | Melihat semua records |

#### 3.3 Booking Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-130 | Booking Masuk | Peternak | Peternak buka booking | Ada booking | Booking ditampilkan | Melihat semua booking masuk |
| UC-131 | Lihat Detail Booking | Peternak | Peternak klik booking | Booking ada | Detail ditampilkan | Melihat info lengkap |
| UC-132 | Approve Booking | Peternak | Peternak approve | Booking pending | Booking disetujui | Menyetujui booking |
| UC-133 | Reject Booking | Peternak | Peternak reject | Booking pending | Booking ditolak | Menolak booking |
| UC-134 | Jadwalkan Survei | Peternak | Peternak jadwalkan | Booking approved | Survei terjadwal | Atur jadwal survei |
| UC-135 | Negosiasi Harga | Peternak | Peternak ajukan | Booking aktif | Negosiasi dimulai | Ajukan harga penawaran |
| UC-136 | Kirim Penawaran | Peternak | Peternak kirim | Negosiasi selesai | Penawaran dikirim | Kirim harga final |

#### 3.4 Order Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-140 | Kelola Pesanan | Peternak | Peternak buka pesanan | Ada pesanan | Pesanan ditampilkan | Mengelola pesanan masuk |
| UC-141 | Update Status Kirim | Peternak | Peternak update | Pesanan ada | Status ter-update | Update status pengiriman |
| UC-142 | Input Info Pengiriman | Peternak | Peternak input | Pesanan aktif | Info tersimpan | Input detail pengiriman |
| UC-143 | Lacak Pengiriman | Peternak | Peternak lacak | Pengiriman aktif | Lokasi ditampilkan | Track pengiriman |
| UC-144 | Generate Dokumen | Peternak | Peternak generate | Pesanan ada | Dokumen dibuat | Generate surat jalan/sertifikat |
| UC-145 | Konfirmasi Handover | Peternak | Peternak konfirmasi | Pesanan ready | Handover confirmed | Konfirmasi serah terima |

#### 3.5 Farm Profile

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-150 | Profil Peternakan | Peternak | Peternak buka profil | Login sebagai peternak | Profil ditampilkan | Kelola profil peternakan |
| UC-151 | Edit Info Farm | Peternak | Peternak edit | Profil ada | Info ter-update | Ubah info farm |
| UC-152 | Kelola Galeri | Peternak | Peternak buka galeri | Profil ada | Galeri ditampilkan | Upload dan kelola foto |
| UC-153 | Upload Foto | Peternak | Peternak upload | Galeri aktif | Foto tersimpan | Upload foto galeri |
| UC-154 | Hapus Foto | Peternak | Peternak hapus | Foto ada | Foto dihapus | Hapus foto galeri |
| UC-155 | Atur Lokasi GPS | Peternak | Peternak atur | Profil ada | Lokasi tersimpan | Set koordinat farm |
| UC-156 | Kelola Badge | Peternak | Peternak manage | Profil ada | Badge ter-update | Kelola badge verifikasi |
| UC-157 | Set Jam Operasional | Peternak | Peternak atur | Profil ada | Jam tersimpan | Set jam buka/tutup |

#### 3.6 Reviews & Analytics

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-160 | Lihat Ulasan | Peternak | Peternak buka reviews | Ada review | Reviews ditampilkan | Melihat semua review |
| UC-161 | Balas Ulasan | Peternak | Peternak balas | Review ada | Balasan terkirim | Beri balasan review |
| UC-162 | Lihat Rating | Peternak | Peternak lihat | Ada rating | Rating ditampilkan | Lihat rating farm |
| UC-163 | Statistik Ulasan | Peternak | Peternak buka stats | Ada reviews | Stats ditampilkan | Statistik review |
| UC-164 | Dashboard Analitik | Peternak | Peternak buka analytics | Login sebagai peternak | Analytics ditampilkan | Dashboard penjualan |
| UC-165 | Lihat Revenue | Peternak | Peternak lihat | Ada transaksi | Revenue ditampilkan | Lihat laporan pendapatan |
| UC-166 | Lihat Statistik | Peternak | Peternak lihat | Ada data | Stats ditampilkan | Statistik penjualan |
| UC-167 | Trend Penjualan | Peternak | Peternak buka | Ada data | Trend ditampilkan | Tren penjualan |
| UC-168 | Export Laporan | Peternak | Peternak export | Data ada | File di-export | Export CSV/Excel |

#### 3.7 Notifications

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-170 | Notifikasi | Peternak | Sistem kirim | Ada notifikasi | Notifikasi dikirim | Terima notifikasi |
| UC-171 | Booking Baru Alert | Peternak | Booking baru | - | Alert diterima | Notifikasi booking baru |
| UC-172 | Ulasan Baru Alert | Peternak | Review baru | - | Alert diterima | Notifikasi review baru |
| UC-173 | Pembayaran Alert | Peternak | Payment masuk | - | Alert diterima | Notifikasi pembayaran |

---

### 4. Staff Module

#### 4.1 Order Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-180 | Daftar Pesanan | Staf | Staf buka pesanan | Login sebagai staf | Daftar pesanan ditampilkan | Melihat semua pesanan |
| UC-181 | Lihat Detail Pesanan | Staf | Staf klik pesanan | Pesanan ada | Detail ditampilkan | Info lengkap pesanan |
| UC-182 | Filter Pesanan | Staf | Staf filter | Daftar ada | Hasil filter | Filter berdasarkan status/tanggal |
| UC-183 | Search Pesanan | Staf | Staf search | Daftar ada | Hasil search | Cari pesanan tertentu |
| UC-184 | Print Invoice | Staf | Staf print | Pesanan ada | Invoice dicetak | Cetak invoice |
| UC-185 | Print Label | Staf | Staf print | Pesanan ada | Label dicetak | Cetak label pengiriman |

#### 4.2 Delivery Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-190 | Kelola Pengiriman | Staf | Staf buka delivery | Login sebagai staf | Daftar pengiriman ditampilkan | Manajemen delivery |
| UC-191 | Assign Driver | Staf | Staf assign | Pengiriman ada | Driver assigned | Menugaskan driver |
| UC-192 | Pilih Driver | Staf | Staf pilih | Driver ada | Driver dipilih | Pilih driver yang tersedia |
| UC-193 | Atur Rute | Staf | Staf atur | Driver assigned | Rute tersimpan | Atur rute pengiriman |
| UC-194 | Update Status Kirim | Staf | Staf update | Pengiriman ada | Status ter-update | Update status |
| UC-195 | Status Persiapan | Staf | Staf set | Pengiriman ada | Status: preparing | Tandai sedang disiapkan |
| UC-196 | Status Siap Kirim | Staf | Staf set | Items siap | Status: ready | Tandai siap kirim |
| UC-197 | Status Dalam Pengiriman | Driver | Driver update | Pickup done | Status: shipping | Update posisi driver |
| UC-198 | Status Diterima | Driver | Driver set | Sampai | Status: delivered | Tandai diterima |
| UC-199 | Batalkan Pengiriman | Staf | Staf cancel | Pengiriman ada | Pengiriman dibatalkan | Batalkan pengiriman |

#### 4.3 Delivery Proof

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-200 | Input Bukti Kirim | Driver | Driver input | Pesanan sampai | Bukti tersimpan | Upload bukti pengiriman |
| UC-201 | Upload Foto Barang | Driver | Driver upload | Bukti aktif | Foto tersimpan | Foto paket yang dikirim |
| UC-202 | Input Tanda Tangan | Driver | Driver input | Bukti aktif | Signature tersimpan | Tanda tangan penerima |
| UC-203 | Input GPS Location | Driver | Driver input | Bukti aktif | Lokasi tersimpan | Koordinat lokasi |
| UC-204 | Catat Penerima | Driver | Driver catat | Bukti aktif | Data tersimpan | Nama kontak penerima |
| UC-205 | Catat Kondisi Barang | Driver | Driver catat | Bukti aktif | Kondisi tersimpan | Kondisi fisik paket |

#### 4.4 Delivery Tracking

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-210 | Lacak Pengiriman | Driver, Staf | Akses tracking | Pengiriman aktif | Lokasi ditampilkan | Track posisi real-time |
| UC-211 | Lihat Timeline | Driver | Akses timeline | Pengiriman ada | Timeline ditampilkan | Lihat riwayat lokasi |
| UC-212 | Update Lokasi | Driver | Sistem auto | Tracking aktif | Lokasi di-update | Update posisi GPS |
| UC-213 | Laporkan Masalah | Driver | Masalah terjadi | Pengiriman aktif | Laporan dibuat | Laporkan issue |
| UC-214 | Selesaikan Masalah | Staf | Masalah ada | Laporan ada | Masalah solved | Resolve delivery issue |

#### 4.5 History & Reports

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-220 | Riwayat Pengiriman | Staf | Staf buka riwayat | Ada pengiriman | Riwayat ditampilkan | Lihat semua pengiriman |
| UC-221 | Filter Riwayat | Staf | Staf filter | Riwayat ada | Hasil filter | Filter berdasarkan tanggal/status |
| UC-222 | Export Laporan | Staf | Staf export | Data ada | File di-export | Export laporan pengiriman |
| UC-223 | Statistik Harian | Staf | Staf buka stats | Ada data | Stats ditampilkan | Statistik harian |

#### 4.6 Operations

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-230 | Kelola Operasional | Staf | Staf buka ops | Login sebagai staf | Ops ditampilkan | Manajemen operasional |
| UC-231 | Kelola Armada | Staf | Staf buka fleet | Login sebagai staf | Fleet ditampilkan | Manajemen kendaraan |
| UC-232 | Tambah Armada | Staf | Staf tambah | - | Armada ditambahkan | Tambah kendaraan baru |
| UC-233 | Edit Armada | Staf | Staf edit | Armada ada | Armada ter-update | Ubah data kendaraan |
| UC-234 | Hapus Armada | Staf | Staf hapus | Armada ada | Armada dihapus | Hapus kendaraan |
| UC-235 | Kelola Jadwal | Staf | Staf buka jadwal | Login sebagai staf | Jadwal ditampilkan | Manajemen jadwal driver |
| UC-236 | Kelola Area | Staf | Staf buka area | Login sebagai staf | Area ditampilkan | Manajemen area pengiriman |
| UC-237 | Kelola Driver | Staf | Staf buka driver | Login sebagai staf | Driver ditampilkan | Manajemen profil driver |
| UC-238 | Set Availability | Driver | Driver set | Login sebagai driver | Status tersimpan | Set status available/offline |

#### 4.7 Notifications

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-240 | Notifikasi | Staf, Driver | Sistem kirim | Ada notifikasi | Notifikasi diterima | Terima notifikasi |
| UC-241 | Pesanan Baru Alert | Driver | Pesanan baru | - | Alert diterima | Notifikasi pesanan masuk |
| UC-242 | Update Status Alert | Staf | Status berubah | - | Alert diterima | Notifikasi perubahan status |
| UC-243 | Pengiriman Selesai Alert | Driver, Staf | Pengiriman selesai | - | Alert diterima | Notifikasi selesai |

---

### 5. Admin Module

#### 5.1 Product Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-250 | Kelola Produk | Admin | Admin buka produk | Login sebagai admin | Produk ditampilkan | CRUD semua produk |
| UC-251 | Tambah Produk | Admin | Admin tambah | Login sebagai admin | Produk dibuat | Tambah produk baru |
| UC-252 | Edit Produk | Admin | Admin edit | Produk ada | Produk ter-update | Edit produk apapun |
| UC-253 | Hapus Produk | Admin | Admin hapus | Produk ada | Produk dihapus | Hapus produk |
| UC-254 | Import Excel | Admin | Admin import | File Excel siap | Produk ter-import | Bulk import produk |
| UC-255 | Export Produk | Admin | Admin export | Ada produk | File di-export | Export produk ke Excel |
| UC-256 | Kelola Kategori | Admin | Admin buka kategori | Login sebagai admin | Kategori ditampilkan | CRUD kategori |
| UC-257 | Tambah Kategori | Admin | Admin tambah | - | Kategori ditambahkan | Tambah kategori baru |
| UC-258 | Edit Kategori | Admin | Admin edit | Kategori ada | Kategori ter-update | Edit kategori |
| UC-259 | Hapus Kategori | Admin | Admin hapus | Kategori ada | Kategori dihapus | Hapus kategori |
| UC-260 | Atur Urutan Kategori | Admin | Admin atur | Kategori ada | Urutan tersimpan | Atur display order |

#### 5.2 Order Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-270 | Kelola Pesanan | Admin | Admin buka pesanan | Login sebagai admin | Pesanan ditampilkan | Monitoring semua pesanan |
| UC-271 | Lihat Semua Pesanan | Admin | Admin lihat | Ada pesanan | Daftar ditampilkan | Lihat semua pesanan |
| UC-272 | Filter Pesanan | Admin | Admin filter | Daftar ada | Hasil filter | Filter berdasarkan status |
| UC-273 | Detail Pesanan | Admin | Admin klik | Pesanan ada | Detail ditampilkan | Info lengkap pesanan |
| UC-274 | Batalkan Pesanan | Admin | Admin cancel | Pesanan ada | Pesanan dibatalkan | Batalkan pesanan |
| UC-275 | Edit Pesanan | Admin | Admin edit | Pesanan ada | Pesanan ter-edit | Edit detail pesanan |
| UC-276 | Assign Staff | Admin | Admin assign | Pesanan ada | Staff assigned | Tugaskan staff pesanan |
| UC-277 | Edit Alamat Pesanan | Admin | Admin edit | Pesanan ada | Alamat ter-edit | Ubah alamat pengiriman |

#### 5.3 User Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-280 | Kelola Pelanggan | Admin | Admin buka pelanggan | Login sebagai admin | Pelanggan ditampilkan | CRUD pelanggan |
| UC-281 | Lihat Pelanggan | Admin | Admin lihat | Ada pelanggan | Data ditampilkan | Lihat data pelanggan |
| UC-282 | Edit Pelanggan | Admin | Admin edit | Pelanggan ada | Data ter-update | Edit data pelanggan |
| UC-283 | Hapus Pelanggan | Admin | Admin hapus | Pelanggan ada | Pelanggan dihapus | Hapus akun pelanggan |
| UC-284 | Kelola Staf | Admin | Admin buka staf | Login sebagai admin | Staf ditampilkan | CRUD staff |
| UC-285 | Tambah Staf | Admin | Admin tambah | - | Staf ditambahkan | Tambah staff baru |
| UC-286 | Edit Staf | Admin | Admin edit | Staf ada | Data ter-update | Edit data staff |
| UC-287 | Hapus Staf | Admin | Admin hapus | Staf ada | Staf dihapus | Hapus staff |
| UC-288 | Assign Role | Admin | Admin assign | Staf ada | Role tersimpan | Set permission staff |
| UC-289 | Reset Password User | Admin | Admin reset | User ada | Password di-reset | Reset password |
| UC-290 | Lihat Aktivitas User | Admin | Admin lihat | User ada | Log ditampilkan | Activity log user |

#### 5.4 Farmer Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-295 | Verifikasi Peternak | Admin | Admin buka verifikasi | Ada peternak baru | Verifikasi ditampilkan | Verifikasi akun farm |
| UC-296 | Lihat Detail Farm | Admin | Admin lihat | Farm ada | Detail ditampilkan | Info lengkap farm |
| UC-297 | Approve Farm | Admin | Admin approve | Farm pending | Farm approved | Setujui pendaftaran farm |
| UC-298 | Reject Farm | Admin | Admin reject | Farm pending | Farm rejected | Tolak pendaftaran farm |
| UC-299 | Suspend Farm | Admin | Admin suspend | Farm active | Farm suspended | Suspend farm sementara |
| UC-300 | Lihat Riwayat Farm | Admin | Admin lihat | Farm ada | History ditampilkan | Riwayat aktivitas farm |

#### 5.5 Marketing

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-310 | Kelola Promo | Admin | Admin buka promo | Login sebagai admin | Promo ditampilkan | CRUD kode promo |
| UC-311 | Buat Kode Promo | Admin | Admin buat | - | Promo dibuat | Buat promo baru |
| UC-312 | Edit Promo | Admin | Admin edit | Promo ada | Promo ter-update | Edit promo |
| UC-313 | Hapus Promo | Admin | Admin hapus | Promo ada | Promo dihapus | Hapus promo |
| UC-314 | Activate Promo | Admin | Admin activate | Promo inactive | Promo active | Aktifkan promo |
| UC-315 | Deactivate Promo | Admin | Admin deactivate | Promo active | Promo inactive | Nonaktifkan promo |
| UC-316 | Kelola Kampanye | Admin | Admin buka campaign | Login sebagai admin | Kampanye ditampilkan | Marketing campaign |
| UC-317 | Buat Kampanye | Admin | Admin buat | - | Kampanye dibuat | Buat campaign baru |
| UC-318 | Pause Kampanye | Admin | Admin pause | Campaign active | Campaign paused | Jeda campaign |
| UC-319 | Resume Kampanye | Admin | Admin resume | Campaign paused | Campaign active | Lanjutkan campaign |
| UC-320 | Kelola Voucher | Admin | Admin kelola | Campaign ada | Voucher ditampilkan | Generate & manage vouchers |
| UC-321 | Statistik Kampanye | Admin | Admin lihat stats | Campaign ada | Stats ditampilkan | Lihat performance campaign |

#### 5.6 Content Management

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-330 | Kelola Konten | Admin | Admin buka konten | Login sebagai admin | Konten ditampilkan | CRUD konten website |
| UC-331 | Kelola FAQ | Admin | Admin buka FAQ | - | FAQ ditampilkan | CRUD FAQ |
| UC-332 | Kelola Testimonial | Admin | Admin buka testimonial | - | Testimonial ditampilkan | CRUD testimonial |
| UC-333 | Kelola Hero Slide | Admin | Admin buka slide | - | Slide ditampilkan | CRUD hero banner |
| UC-334 | Kelola Statistik | Admin | Admin buka stats | - | Stats ditampilkan | Update statistik website |
| UC-335 | Kelola Artikel | Admin | Admin buka artikel | - | Artikel ditampilkan | CRUD artikel/berita |
| UC-336 | Preview Konten | Admin | Admin preview | Konten ada | Preview ditampilkan | Lihat preview sebelum publish |

#### 5.7 Reviews & Moderation

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-340 | Moderasi Review | Admin | Admin buka moderasi | Ada review | Review ditampilkan | Approve/reject review |
| UC-341 | Approve Review | Admin | Admin approve | Review pending | Review approved | Setujui review |
| UC-342 | Reject Review | Admin | Admin reject | Review pending | Review rejected | Tolak review |
| UC-343 | Hapus Review | Admin | Admin hapus | Review ada | Review dihapus | Hapus review |
| UC-344 | Lihat Laporan | Admin | Admin lihat | Ada laporan | Laporan ditampilkan | Lihat laporan abuse |
| UC-345 | Blokir User | Admin | Admin blokir | User ada | User diblokir | Blokir user sementara |
| UC-346 | Unblokir User | Admin | Admin unblokir | User diblokir | User di-unblokir | Aktifkan kembali user |
| UC-347 | Bulk Approve Review | Admin | Admin bulk approve | Ada review pending | Review approved | Approve banyak review |

#### 5.8 Reports & Analytics

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-350 | Laporan Keuangan | Admin | Admin buka laporan | Login sebagai admin | Laporan ditampilkan | Laporan keuangan |
| UC-351 | Revenue Report | Admin | Admin lihat | Ada transaksi | Report ditampilkan | Laporan pendapatan |
| UC-352 | Profit Report | Admin | Admin lihat | Ada data | Report ditampilkan | Laporan profit margin |
| UC-353 | Dashboard Analitik | Admin | Admin buka dashboard | Login sebagai admin | Dashboard ditampilkan | Overview analytics |
| UC-354 | Sales Analytics | Admin | Admin lihat | Ada data | Analytics ditampilkan | Analisis penjualan |
| UC-355 | User Analytics | Admin | Admin lihat | Ada user | Analytics ditampilkan | Analisis user |
| UC-356 | Product Analytics | Admin | Admin lihat | Ada produk | Analytics ditampilkan | Analisis produk |
| UC-357 | Export Laporan | Admin | Admin export | Data ada | File di-export | Export berbagai format |
| UC-358 | Schedule Report | Admin | Admin schedule | - | Schedule tersimpan | Auto-generate laporan |

#### 5.9 Settings

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-360 | Pengaturan Toko | Admin | Admin buka settings | Login sebagai admin | Settings ditampilkan | Konfigurasi toko |
| UC-361 | Pengaturan Umum | Admin | Admin edit | - | Settings tersimpan | General settings |
| UC-362 | Pengaturan Logo | Admin | Admin edit | - | Logo tersimpan | Upload logo toko |
| UC-363 | Pengaturan Contact | Admin | Admin edit | - | Contact tersimpan | Info kontak toko |
| UC-364 | Pengaturan Payment | Admin | Admin edit | - | Payment tersimpan | Konfigurasi payment |
| UC-365 | Pengaturan Notifikasi | Admin | Admin edit | - | Notifikasi tersimpan | Konfigurasi notification |
| UC-366 | Area Pengiriman | Admin | Admin buka area | Login sebagai admin | Area ditampilkan | CRUD area pengiriman |
| UC-367 | Tambah Area | Admin | Admin tambah | - | Area ditambahkan | Tambah area baru |
| UC-368 | Edit Biaya Ongkir | Admin | Admin edit | Area ada | Biaya ter-update | Atur ongkir per area |
| UC-369 | Hapus Area | Admin | Admin hapus | Area ada | Area dihapus | Hapus area |
| UC-370 | Aktifkan/Nonaktifkan Area | Admin | Admin toggle | Area ada | Status berubah | Enable/disable area |

---

### 6. System Integration

| ID | Nama Use Case | Actor | Trigger | Pre-condition | Post-condition | Deskripsi |
|----|---------------|-------|---------|---------------|----------------|-----------|
| UC-380 | Process Payment | Midtrans | Payment initiated | - | Status updated | Process transaksi |
| UC-381 | Send Push Notification | FCM | Event triggered | - | Notifikasi terkirim | Kirim push notification |
| UC-382 | Send Email | Sistem | Event triggered | - | Email terkirim | Kirim email notifikasi |
| UC-383 | Send SMS | Sistem | Event triggered | - | SMS terkirim | Kirim SMS |
| UC-384 | Webhook Payment | Midtrans | Payment status | - | Order updated | Terima notifikasi payment |
| UC-385 | Calculate Shipping | Sistem | Checkout | Alamat ada | Shipping dihitung | Hitung ongkir |
| UC-386 | Generate Invoice | Sistem | Order created | - | Invoice dibuat | Generate invoice PDF |
| UC-387 | Reserve Stock | Sistem | Order confirmed | Stok cukup | Stok di-reserve | Hold stok |
| UC-388 | Release Stock | Sistem | Order cancelled | Stok reserved | Stok released | Kembalikan stok |
| UC-389 | Apply Discount | Sistem | Checkout | Promo valid | Diskon diterapkan | Hitung diskon |
| UC-390 | Calculate Loyalty | Sistem | Order completed | User ada | Poin ditambahkan | Hitung loyalty points |

---

## Summary Statistics

| Module | Total Use Cases |
|--------|---------------|
| Authentication | 12 |
| Customer | 68 |
| Farmer | 49 |
| Staff | 53 |
| Admin | 92 |
| System Integration | 11 |
| **Total** | **285** |

---

## Diagram Reference

| Document | Description |
|----------|-------------|
| [01-use-case-authentication.puml](01-use-case-authentication.puml) | Authentication use case diagram |
| [02-use-case-customer.puml](02-use-case-customer.puml) | Customer use case diagram |
| [03-use-case-farmer.puml](03-use-case-farmer.puml) | Farmer use case diagram |
| [04-use-case-staff.puml](04-use-case-staff.puml) | Staff use case diagram |
| [05-use-case-admin.puml](05-use-case-admin.puml) | Admin use case diagram |
| [06-order-lifecycle-state.puml](06-order-lifecycle-state.puml) | Order state machine |
| [07-checkout-flow.puml](07-checkout-flow.puml) | Checkout process |
| [08-booking-flow.puml](08-booking-flow.puml) | Booking/inquiry process |
| [09-delivery-flow.puml](09-delivery-flow.puml) | Delivery process |
| [10-system-architecture.puml](10-system-architecture.puml) | System architecture |
| [11-marketing-campaign-flow.puml](11-marketing-campaign-flow.puml) | Marketing campaign |
| [USE_CASE_DIAGRAM.md](USE_CASE_DIAGRAM.md) | Main documentation |

---

*Generated for AbodaFarm System Documentation*
*Last Updated: 2026-10-12*
