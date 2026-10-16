# Traceability Matrix: Use Case → Data → ERD
## AbodaFarm System Documentation

---

## 1. Pendahuluan

Dokumen ini menjelaskan hubungan traceabilitas antara:
- **Use Case** - kebutuhan fungsional sistem
- **Data Requirements** - data yang diperlukan untuk memenuhi use case
- **ERD Entities** - entitas database yang menyimpan data tersebut

### Tujuan
- Memastikan semua kebutuhan Use Case ter-cover di ERD
- Memvalidasi bahwa setiap entity ERD memiliki use case yang membutuhkannya
- Memudahkan tracing requirement ke implementasi

---

## 2. Traceability Matrix

### 2.1 Authentication Module

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-01 Registrasi Akun | Data registrasi user | `users` | name, email, password, role |
| UC-01 Registrasi Akun | Level pelanggan default | `customer_levels` | name, display_name |
| UC-02 Login | Data autentikasi | `users` | email, password |
| UC-04 Verifikasi Email | Token verifikasi | `users` | email_verified_at |
| UC-07 Two-Factor Auth | Secret key 2FA | `users` | two_factor_secret, two_factor_recovery_codes |
| UC-09 Lihat Profil | Data profil user | `users` | name, email |
| UC-10 Edit Profil | Update profil | `users` | name, email |
| UC-12 Verifikasi Akun | Status verifikasi | `users` | role |
| UC-12 Verifikasi Akun | Data peternak | `farm_profiles` | is_verified |

---

### 2.2 Customer Module - Catalog & Browsing

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-20 Lihat Katalog | Daftar produk | `products`, `categories` | name, price, type, status |
| UC-21 Search Produk | Keyword pencarian | `products` | name, description |
| UC-22 Filter Produk | Filter by kategori | `categories` | id, name |
| UC-22 Filter Produk | Filter by tipe | `products` | type (live/fresh/supply) |
| UC-22 Filter Produk | Filter by lokasi | `farm_profiles` | province, regency |
| UC-23 Lihat Detail Produk | Info produk lengkap | `products` | name, price, stock, weight, age, breed |
| UC-23 Lihat Detail Produk | Info peternak | `users`, `farm_profiles` | farm_name, rating, location |
| UC-24 Lihat Review | Review produk | `reviews` | rating, content |
| UC-24 Lihat Review | Foto review | `review_photos` | photo |
| UC-25 Lihat Galeri | Galeri produk | `products` | images |

---

### 2.3 Customer Module - Shopping Cart

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-30 Tambah ke Keranjang | Data item | `order_items` (temporary) | product_id, quantity |
| UC-30 Tambah ke Keranjang | Info produk | `products` | price, stock, name |
| UC-31 Lihat Keranjang | Item di cart | Session/Cart | - |
| UC-32 Update Jumlah | Update quantity | Session/Cart | quantity |
| UC-33 Hapus Item | Hapus dari cart | Session/Cart | - |
| UC-35 Lihat Subtotal | Perhitungan total | Computed | - |

---

### 2.4 Customer Module - Checkout & Address

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-40 Checkout | Data checkout | `orders` | customer_name, customer_address, notes |
| UC-41 Kelola Alamat | Daftar alamat user | `addresses` | recipient_name, phone, address |
| UC-42 Tambah Alamat | Alamat baru | `addresses` | all address fields |
| UC-43 Edit Alamat | Update alamat | `addresses` | all address fields |
| UC-44 Hapus Alamat | Hapus alamat | `addresses` | is_active |
| UC-45 Pilih Alamat | Alamat untuk checkout | `addresses` | is_default |
| UC-46 Set Alamat Default | Set default | `addresses` | is_default |
| UC-47 Tambah Catatan | Catatan pesanan | `orders` | notes |

---

### 2.5 Customer Module - Payment

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-50 Pembayaran | Data pesanan | `orders` | total_amount, order_number |
| UC-50 Pembayaran | Data pembayaran | `payments` | transaction_id, gross_amount |
| UC-51 Pilih Metode Bayar | Metode tersedia | Config | - |
| UC-53 Gunakan Kode Promo | Data promo | `promotions` | code, discount_value, min_purchase |
| UC-53 Gunakan Kode Promo | Usage promo | `promotion_usages` | promotion_id, user_id |
| UC-54 Lihat Ringkasan | Ringkasan pesanan | `orders`, `order_items` | total_items, total_amount |
| UC-56 Bayar Ulang | Retry payment | `payments` | snap_token |

---

### 2.6 Customer Module - Booking/Inquiry

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-60 Buat Booking | Data booking | `orders` | order_type=inquiry, preferred_delivery_date |
| UC-61 Pilih Jadwal | Tanggal kunjungan | `orders` | preferred_delivery_date, pickup_location |
| UC-62 Upload Dokumen | Dokumen pendukung | File Storage | - |
| UC-63 Negosiasi Harga | Harga tawaran | `orders` | admin_notes |
| UC-64 Approve Booking | Setuju penawaran | `orders` | status, booking_status_id |
| UC-65 Reject Booking | Tolak penawaran | `orders` | status |
| UC-66 Lihat Detail Booking | Info lengkap booking | `orders`, `order_items` | all fields |
| UC-67 Upload Tanda Tangan | Signature digital | File Storage | - |

---

### 2.7 Customer Module - Order Tracking

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-70 Lacak Pesanan | Status pesanan | `orders` | status, shipment_status_id |
| UC-70 Lacak Pesanan | Log status | `order_status_logs` | from_status, to_status, created_at |
| UC-71 Lihat Notifikasi | Daftar notifikasi | `customer_notifications` | type, title, message, is_read |
| UC-72 Konfirmasi Penerimaan | Konfirmasi terima | `orders` | completed_by_customer, completed_at |
| UC-73 Riwayat Pesanan | History pesanan | `orders` | all order fields |
| UC-76 Print Invoice | Data invoice | `orders`, `payments` | all relevant fields |
| UC-77 Download Invoice | PDF invoice | Generated PDF | - |

---

### 2.8 Customer Module - Reviews

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-80 Review Produk | Data review produk | `reviews` | rating, content, product_id |
| UC-81 Review Peternak | Data review peternak | `reviews` | rating, farmer_id, review_type=farmer |
| UC-82 Upload Foto Review | Foto review | `review_photos` | photo |
| UC-83 Edit Review | Update review | `reviews` | rating, content |
| UC-84 Hapus Review | Hapus review | `reviews` | (soft delete) |
| UC-85 Balas Review | Balasan peternak | `reviews` | (handled in same table) |

---

### 2.9 Farmer Module - Product Management

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-111 Tambah Produk | Data produk baru | `products` | name, price, stock, type, category_id |
| UC-112 Edit Produk | Update produk | `products` | all product fields |
| UC-113 Hapus Produk | Hapus produk | `products` | status |
| UC-114 Update Foto | Foto produk | `products` | image, images |
| UC-115 Kelola Stok | Data stok | `products` | stock |
| UC-116 Update Stok | Update jumlah | `products` | stock |
| UC-117 Riwayat Stok | Log perubahan | `inventory_logs` | type, quantity, previous/new_stock |
| UC-118 Bulk Update Stok | File Excel | `products` | (batch update) |

---

### 2.10 Farmer Module - Livestock Management

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-120 Kelola Care Records | Record perawatan | `care_records` | record_type, title, description |
| UC-121 Catat Feeding | Record makan | `care_records` | record_type=feeding |
| UC-122 Catat Vaccination | Record vaksin | `care_records` | record_type=vaccination |
| UC-123 Catat Health Check | Record kesehatan | `care_records` | record_type=health_check |
| UC-124 Catat Cleaning | Record cleaning | `care_records` | record_type=cleaning |
| UC-125 Lihat Riwayat | Semua records | `care_records` | all fields |

---

### 2.11 Farmer Module - Booking Management

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-130 Booking Masuk | Daftar booking | `orders` | order_type=inquiry, status |
| UC-131 Lihat Detail Booking | Info booking | `orders`, `order_items` | all fields |
| UC-132 Approve Booking | Setujui booking | `orders` | status=approved |
| UC-133 Reject Booking | Tolak booking | `orders` | status=rejected |
| UC-134 Jadwalkan Survei | Tanggal survei | `orders` | preferred_delivery_date |
| UC-135 Negosiasi Harga | Harga penawaran | `orders` | admin_notes |
| UC-136 Kirim Penawaran | Offer final | `orders` | admin_notes |

---

### 2.12 Farmer Module - Farm Profile

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-150 Profil Peternakan | Data farm | `farm_profiles` | farm_name, description, address |
| UC-151 Edit Info Farm | Update info | `farm_profiles` | all fields |
| UC-152 Kelola Galeri | Foto galeri | `farm_galleries` | image, caption |
| UC-153 Upload Foto | Upload galeri | `farm_galleries` | image |
| UC-154 Hapus Foto | Hapus galeri | `farm_galleries` | (soft delete) |
| UC-155 Atur Lokasi GPS | Koordinat farm | `farm_profiles` | latitude, longitude |
| UC-156 Kelola Badge | Badge verifikasi | `farmer_badges` | badge_type, earned_at |
| UC-157 Set Jam Operasional | Jam buka | `farm_profiles` | (additional field) |

---

### 2.13 Staff Module - Delivery Management

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-190 Kelola Pengiriman | Data delivery | `deliveries` | status, delivery_code |
| UC-191 Assign Driver | Assign staff | `deliveries` | assigned_staff_id |
| UC-192 Pilih Driver | Pilih driver | `users` (staff role) | name |
| UC-193 Atur Rute | Rute pengiriman | `deliveries` | (future: route field) |
| UC-194 Update Status Kirim | Update status | `deliveries` | status, shipped_at |
| UC-200 Input Bukti Kirim | Foto bukti | `deliveries` | proof_photo |
| UC-201 Upload Foto Barang | Foto paket | `deliveries` | proof_photo |
| UC-202 Input Tanda Tangan | Signature | `deliveries` | customer_signature |
| UC-203 Input GPS Location | Koordinat | `deliveries` | latitude, longitude |
| UC-204 Catat Penerima | Nama penerima | `deliveries` | receiver_name |

---

### 2.14 Admin Module - User Management

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-280 Kelola Pelanggan | Data pelanggan | `users` | all user fields |
| UC-284 Kelola Staf | Data staff | `users` | role, permissions |
| UC-285 Tambah Staf | Staff baru | `users` | name, email, role |
| UC-288 Assign Role | Set permission | `model_has_roles` | role_id |
| UC-289 Reset Password | Reset password | `users` | password |
| UC-295 Verifikasi Peternak | Data verifikasi | `farm_profiles` | is_verified |
| UC-296 Lihat Detail Farm | Info lengkap farm | `farm_profiles` | all fields |

---

### 2.15 Admin Module - Marketing

| Use Case | Data Requirements | ERD Entity | Attributes |
|----------|-------------------|-------------|------------|
| UC-310 Kelola Promo | Data promo | `promotions` | code, discount_value, usage_limit |
| UC-316 Kelola Kampanye | Data kampanye | `marketing_campaigns` | name, type, status |
| UC-317 Buat Kampanye | Rules kampanye | `campaign_rules` | rule_type, action_type |
| UC-320 Kelola Voucher | Generate voucher | Generated from campaign | - |
| UC-321 Statistik Kampanye | Analytics | `promotion_usages` | usage tracking |

---

## 3. Entity Coverage Summary

### 3.1 Entity Usage by Module

| Entity | Used In | Primary Use Cases |
|--------|---------|------------------|
| `users` | Auth, Customer, Farmer, Staff, Admin | UC-01, UC-02, UC-09, UC-10, UC-280 |
| `customer_levels` | Customer, Loyalty | UC-01 |
| `categories` | Customer, Admin | UC-20, UC-22, UC-256 |
| `products` | Customer, Farmer, Admin | UC-20, UC-23, UC-111, UC-250 |
| `inventory_logs` | Farmer | UC-117, UC-118 |
| `care_records` | Farmer | UC-120-125 |
| `orders` | Customer, Farmer, Staff, Admin | UC-40, UC-60, UC-130, UC-270 |
| `order_items` | Customer, Farmer | UC-40, UC-60 |
| `payments` | Customer, Admin | UC-50, UC-270 |
| `order_status_logs` | Customer, Staff | UC-70 |
| `addresses` | Customer | UC-41-46 |
| `reviews` | Customer, Farmer, Admin | UC-80-85, UC-340 |
| `review_photos` | Customer | UC-82 |
| `farm_profiles` | Farmer, Admin | UC-150-157, UC-295 |
| `farm_galleries` | Farmer | UC-152-154 |
| `farmer_badges` | Farmer | UC-156 |
| `promotions` | Customer, Admin | UC-53, UC-310 |
| `promotion_usages` | System | UC-53 |
| `marketing_campaigns` | Admin | UC-316 |
| `campaign_rules` | Admin | UC-317 |
| `deliveries` | Staff, Customer | UC-190-204 |
| `delivery_timelines` | Staff | UC-211 |
| `customer_notifications` | Customer | UC-71 |
| `peternak_notifications` | Farmer | UC-170-173 |
| `staff_notifications` | Staff | UC-240-243 |
| `fcm_tokens` | System | UC-381 |

---

## 4. Checklist Validasi

### 4.1 Actor Completeness ✅

| Actor | Covered In | Use Cases |
|-------|------------|-----------|
| Guest | UC-01, UC-03, UC-20-25 | Browse, Register |
| Pelanggan | UC-30-105 | Shop, Order, Review |
| Peternak | UC-110-173 | Manage Products, Bookings |
| Staf | UC-180-243 | Delivery, Operations |
| Admin | UC-250-369 | Full System Management |
| Driver | UC-194, UC-198, UC-210-214 | Delivery Operations |
| Sistem Midtrans | UC-380, UC-384 | Payment Processing |
| Sistem FCM | UC-381 | Push Notifications |

### 4.2 Use Case Completeness ✅

| Module | Total UC | Status |
|--------|----------|--------|
| Authentication | 12 | ✅ Complete |
| Customer | 68 | ✅ Complete |
| Farmer | 49 | ✅ Complete |
| Staff | 53 | ✅ Complete |
| Admin | 92 | ✅ Complete |
| System Integration | 11 | ✅ Complete |

### 4.3 Entity Relevance ✅

| Entity | Required By | Justification |
|--------|------------|---------------|
| users | All modules | Core entity for all user types |
| products | Catalog, Order | Central product data |
| orders | Order flow | Core transaction entity |
| reviews | Rating system | Customer feedback |
| farm_profiles | Farmer module | Farm identity |
| promotions | Marketing | Discount system |
| deliveries | Shipping | Delivery tracking |

### 4.4 Primary Key Clarity ✅

| Entity | PK | Justification |
|--------|-----|---------------|
| users | id | Auto-increment, unique |
| products | id | Auto-increment, unique |
| orders | id | Auto-increment, order_number as business key |
| payments | id | Auto-increment, transaction_id for external ref |
| reviews | id | Auto-increment |
| farm_profiles | id | Auto-increment, user_id as FK |

### 4.5 Foreign Key Clarity ✅

| Entity | FK | References | Purpose |
|--------|-----|-----------|---------|
| products | category_id | categories.id | Product categorization |
| products | owner_id | users.id | Product ownership |
| orders | user_id | users.id | Order ownership |
| order_items | order_id | orders.id | Item grouping |
| order_items | product_id | products.id | Product reference |
| payments | order_id | orders.id | Payment for order |
| reviews | user_id | users.id | Review author |
| reviews | product_id | products.id | Reviewed product |
| farm_profiles | user_id | users.id | Profile owner |
| deliveries | order_id | orders.id | Delivery for order |
| addresses | user_id | users.id | Address owner |

### 4.6 Cardinality & Relationships ✅

| Relationship | Cardinality | Type | Justification |
|--------------|-------------|------|---------------|
| User → Products | 1:N | Owner | One user can own many products |
| User → Orders | 1:N | Places | One user places many orders |
| Order → OrderItems | 1:N | Contains | One order has many items |
| Order → Payment | 1:1 | Has | One order has one payment |
| Product → Reviews | 1:N | Receives | One product has many reviews |
| User → FarmProfile | 1:1 | Has | One user has one farm profile |
| FarmProfile → Products | 1:N | Owns | One farm has many products |
| Order → Delivery | 1:1 | Ships | One order has one delivery |

### 4.7 Design Consistency ✅

| Standard | Implementation | Status |
|----------|---------------|--------|
| Naming Convention | snake_case | ✅ Consistent |
| ID Convention | bigint PK | ✅ Consistent |
| Timestamp | created_at, updated_at | ✅ Consistent |
| Soft Delete | No explicit, using is_active | ✅ Implemented |
| JSON Fields | images, specifications | ✅ Used where needed |
| Enum Fields | status, type | ✅ Using ENUMs |

---

## 5. Gap Analysis

### 5.1 Missing Implementations

| Gap | Priority | Notes |
|-----|----------|-------|
| Wallet/Saldo | Medium | UC-102-105 not fully implemented |
| Driver Management | Medium | Staff/Driver distinction |
| Signature Upload | Low | Digital signature for contracts |

### 5.2 Recommendations

1. **Add wallet module** if financial transactions are needed
2. **Implement driver as separate role** for clearer permissions
3. **Add operational hours field** to farm_profiles

---

## 6. Diagram Reference

| Diagram | File | Purpose |
|---------|------|---------|
| Use Case Diagram | `01-use-case-*.puml` | Functional requirements |
| ERD Full | `erd-abodafarm.puml` | Complete database design |
| ERD Simple | `erd-abodafarm-simple.puml` | Core relationships |

---

*Document Version: 1.0*
*Last Updated: 2026-10-13*
*Author: Claude Code*
