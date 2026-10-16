# AbodaFarm - Use Case & Business Process Documentation

## Overview

Dokumen ini menjelaskan Use Case Diagram dan Business Process Flows untuk platform e-commerce AbodaFarm - marketplace peternakan yang menyediakan tiga tipe produk: Ternak Hidup (Live), Daging Segar (Fresh), dan Pakan & Perlengkapan (Supply).

---

## Table of Contents

1. [Actors Overview](#actors-overview)
2. [Use Cases by Module](#use-cases-by-module)
   - [Authentication Module](#-authentication-module)
   - [Customer Module](#-customer-module)
   - [Farmer Module](#-farmer-module)
   - [Staff Module](#-staff-module)
   - [Admin Module](#-admin-module)
3. [Use Case Relationships](#use-case-relationships)
4. [Business Process Flows](#business-process-flows)
   - [Order Lifecycle State Machine](#order-lifecycle-state-machine)
   - [Checkout Flow](#checkout-flow)
   - [Booking/Inquiry Flow](#bookinginquiry-flow)
   - [Delivery Flow](#delivery-flow)
   - [Marketing Campaign Flow](#marketing-campaign-flow)
5. [System Architecture](#system-architecture)
6. [Notification Types](#notification-types)
7. [Product Types](#product-types)
8. [Diagrams Index](#diagrams-index)

---

## Actors Overview

| Actor | Deskripsi | Role |
|-------|-----------|------|
| **Guest** | Pengguna yang belum login | Browse, Search, Register |
| **Pelanggan** | Customer yang telah login | Shop, Order, Review |
| **Peternak** | Partner/peternak | Manage Products, Bookings |
| **Staf** | Staff operasional | Manage Delivery |
| **Admin** | Administrator | Full System Access |
| **Driver** | Pengemudi | Deliver Orders |
| **Sistem Midtrans** | Payment gateway | Process Payments |
| **Sistem FCM** | Push notifications | Send Notifications |

---

## Use Cases by Module

### 🔐 Authentication Module

| ID | Use Case | Actor | Deskripsi |
|----|----------|-------|-----------|
| UC-01 | Registrasi Akun | Guest | Mendaftar akun baru dengan verifikasi email |
| UC-02 | Login | All Users | Login ke sistem dengan email dan password |
| - | Lupa Password | Guest | Reset password via email |
| - | Logout | All Users | Logout dari sistem |
| - | Two-Factor Auth | All Users | Aktifkan 2FA untuk keamanan |
| - | Ganti Password | All Users | Mengubah password akun |
| - | Kelola Profil | All Users | Update data profil |

**Diagram:** [01-use-case-authentication.puml](01-use-case-authentication.puml)

---

### 🛒 Customer Module (Pelanggan)

| ID | Use Case | Actor | Deskripsi |
|----|----------|-------|-----------|
| UC-03 | Lihat Katalog Produk | Guest, Pelanggan | Melihat daftar produk dengan pagination |
| UC-04 | Search Produk | Guest, Pelanggan | Mencari produk dengan keyword |
| UC-05 | Filter Produk | Guest, Pelanggan | Filter berdasarkan kategori, tipe, harga |
| UC-06 | Lihat Detail Produk | Guest, Pelanggan | Melihat detail lengkap produk |
| UC-07 | Tambah ke Keranjang | Pelanggan | Menambahkan produk ke shopping cart |
| UC-08 | Checkout | Pelanggan | Proses checkout dengan validasi |
| UC-09 | Pembayaran | Pelanggan | Bayar via Midtrans Snap |
| UC-10 | Buat Booking | Pelanggan | Membuat inquiry untuk ternak |
| UC-11 | Approve/Reject Booking | Pelanggan | Menyetujui atau menolak booking |
| UC-12 | Lacak Pesanan | Pelanggan | Melacak status pesanan |
| UC-13 | Konfirmasi Penerimaan | Pelanggan | Konfirmasi barang diterima |
| UC-14 | Review Produk | Pelanggan | Memberikan review produk |
| UC-15 | Review Peternak | Pelanggan | Memberikan review peternak |
| UC-16 | Kelola Alamat | Pelanggan | CRUD alamat pengiriman |
| UC-17 | Wishlist/Favorit | Pelanggan | Menambah produk favorit |
| UC-18 | Gunakan Kode Promo | Pelanggan | Menggunakan kode promo |
| UC-19 | Klaim Voucher | Pelanggan | Klaim voucher kampanye |
| UC-20 | Lihat Notifikasi | Pelanggan | Melihat notifikasi |
| UC-21 | Kelola Profil | Pelanggan | Update data profil |
| UC-22 | Riwayat Pesanan | Pelanggan | Melihat history pesanan |
| UC-23 | Wallet & Poin | Pelanggan | Melihat poin loyalitas |

**Diagram:** [02-use-case-customer.puml](02-use-case-customer.puml)

---

### 🐄 Farmer Module (Peternak)

| ID | Use Case | Actor | Deskripsi |
|----|----------|-------|-----------|
| UC-31 | Dashboard Produk | Peternak | Overview manajemen produk |
| UC-32 | Tambah Produk | Peternak | Menambahkan produk baru |
| UC-33 | Edit Produk | Peternak | Mengubah data produk |
| UC-34 | Hapus Produk | Peternak | Menghapus produk |
| UC-35 | Kelola Stok | Peternak | Update stok inventory |
| UC-36 | Booking Masuk | Peternak | Melihat booking masuk |
| UC-37 | Approve/Reject Booking | Peternak | Setuju/tolak inquiry |
| UC-38 | Kelola Pesanan | Peternak | Mengelola pesanan |
| UC-39 | Update Status Kirim | Peternak | Update status pengiriman |
| UC-40 | Profil Peternakan | Peternak | Update farm profile |
| UC-41 | Dashboard Analitik | Peternak | Lihat statistik penjualan |
| UC-42 | Export Laporan | Peternak | Export laporan CSV/Excel |
| UC-43 | Lihat Ulasan | Peternak | Melihat review produk |
| UC-44 | Balas Ulasan | Peternak | Memberi balasan review |
| UC-45 | Kelola Galeri | Peternak | Upload foto galeri |
| UC-46 | Notifikasi | Peternak | Lihat notifikasi |
| UC-47 | Care Records | Peternak | Record feeding, vaccination, health |

**Diagram:** [03-use-case-farmer.puml](03-use-case-farmer.puml)

---

### 🚚 Staff Module (Staf)

| ID | Use Case | Actor | Deskripsi |
|----|----------|-------|-----------|
| UC-51 | Daftar Pesanan | Staf | Melihat semua pesanan |
| UC-52 | Kelola Pengiriman | Staf | Manajemen delivery |
| UC-53 | Assign Driver | Staf | Menugaskan driver |
| UC-54 | Update Status Kirim | Staf | Update status pengiriman |
| UC-55 | Input Bukti Kirim | Staf | Upload foto & tanda tangan |
| UC-56 | Riwayat Pengiriman | Staf | Lihat history pengiriman |
| UC-57 | Kelola Operasional | Staf | Manajemen armada & jadwal |
| UC-58 | Notifikasi | Staf | Lihat notifikasi |

**Diagram:** [04-use-case-staff.puml](04-use-case-staff.puml)

---

### ⚙️ Admin Module

| ID | Use Case | Actor | Deskripsi |
|----|----------|-------|-----------|
| UC-61 | Kelola Produk | Admin | CRUD semua produk |
| UC-62 | Import Excel | Admin | Bulk import produk |
| UC-63 | Kelola Kategori | Admin | CRUD kategori |
| UC-64 | Kelola Pesanan | Admin | Monitoring pesanan |
| UC-65 | Kelola Pelanggan | Admin | CRUD pelanggan |
| UC-66 | Kelola Staf | Admin | CRUD staff & role |
| UC-67 | Kelola Promo | Admin | CRUD kode promo |
| UC-68 | Kelola Kampanye | Admin | Marketing campaign |
| UC-69 | Moderasi Review | Admin | Approve/reject review |
| UC-70 | Verifikasi Peternak | Admin | Verifikasi akun farm |
| UC-71 | Laporan Keuangan | Admin | Laporan revenue |
| UC-72 | Dashboard Analitik | Admin | Analitik lengkap |
| UC-73 | Export Laporan | Admin | Export laporan |
| UC-74 | Pengaturan Toko | Admin | Konfigurasi toko |
| UC-75 | Area Pengiriman | Admin | CRUD area & ongkir |
| UC-76 | Kelola Konten | Admin | FAQ, Testimonial, Hero |

**Diagram:** [05-use-case-admin.puml](05-use-case-admin.puml)

---

## Use Case Relationships

### Include Relationships

```
Checkout (UC-08)
  ├── include ── Kelola Alamat (UC-16)
  ├── include ── Tambah Catatan
  ├── include ── Gunakan Kode Promo (UC-18)
  └── include ── Hitung Ulang

Pembayaran (UC-09)
  ├── include ── Pilih Metode Bayar
  ├── include ── Gunakan Kode Promo (UC-18)
  └── include ── Lihat Ringkasan

Checkout ── Lacak Pesanan (UC-12) ── include ── Lihat Notifikasi (UC-20)
Riwayat Pesanan (UC-22) ── include ── Lacak Pesanan (UC-12)

Kelola Pengiriman (UC-52)
  ├── include ── Assign Driver (UC-53)
  ├── include ── Update Status (UC-54)
  ├── include ── Input Bukti Kirim (UC-55)
  └── extend ── Lacak Pengiriman
```

### Extend Relationships

```
Buat Booking (UC-10) ── extend ── Negosiasi Harga
Review Produk (UC-14) ── extend ── Review Peternak (UC-15)
Cancel Order ── extend ── Riwayat Pesanan (UC-22)
```

---

## Business Process Flows

### Order Lifecycle State Machine

```
[*] ──> OrderCreated ──> WaitPay ──> PaymentProcessing
                                              │
                    ┌─────────────────────────┼─────────────────────────┐
                    │                         │                         │
                    ▼                         ▼                         ▼
             PaymentSuccess           PaymentFailed              PaymentExpired
                    │                         │                         │
                    ▼                         ▼                         ▼
             OrderConfirmed         WaitPay (Retry)           OrderCancelled
                    │                                                 │
                    ▼                                                 │
              Preparing ──> ReadyToShip ──> OutForDelivery ──> Delivered
                                                                   │
                                              ┌────────────────────┤
                                              │                    │
                                              ▼                    ▼
                                    DeliveryConfirmed      DeliveryIssue
                                              │                    │
                                              ▼                    ▼
                                      OrderCompleted        Reschedule
                                                                   │
                                                                   ▼
                                                             RefundProcessing
                                                                   │
                                                                   ▼
                                                              RefundCompleted
```

**Diagram:** [06-order-lifecycle-state.puml](06-order-lifecycle-state.puml)

---

### Checkout Flow

```
┌──────────────┐
│  View Cart   │
└──────┬───────┘
       │
       ▼
┌──────────────────────┐     No      ┌─────────────────┐
│   Cart Empty?        ├─────────────>│ Empty Cart Page │
└──────────┬───────────┘             └─────────────────┘
           │ Yes
           ▼
┌──────────────────────┐
│  Proceed Checkout   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐     Invalid
│ Select Address       ├──────────┐
└──────────┬───────────┘         │
           │ Valid                ▼
           ▼              ┌─────────────────┐
┌──────────────────────┐  │ Enter New Address│
│  Calculate Shipping  │  └─────────────────┘
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│   Apply Promo Code   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐     No
│   Confirm Order?     ├──────────┐
└──────────┬───────────┘         │
           │ Yes                  ▼
           ▼              ┌─────────────────┐
┌──────────────────────┐  │   Cancel        │
│  Select Payment      │  └─────────────────┘
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Generate Snap Token  │ ──> Midtrans
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│   Payment UI         │ ──> Midtrans
└──────────┬───────────┘
           │
           ▼
      ┌────┴────┐
      │ Success │
      └────┬────┘
           │
           ▼
┌──────────────────────┐
│ Update Order Status  │
│ Reserve Stock        │
│ Send Notifications   │
└──────────────────────┘
```

**Diagram:** [07-checkout-use-case.puml](07-checkout-use-case.puml)

---

### Booking/Inquiry Flow

```
┌──────────────────────┐
│ View Live Product    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Click "Booking"      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Select Farm & Date  │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Upload Documents     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Submit Booking       │ ──> Notify Farmer
└──────────┬───────────┘          │
           │                      ▼
           ▼              ┌─────────────────┐
┌──────────────────────┐  │ Farmer Reviews │
│ Booking Received     │  │ Booking        │
└──────────┬───────────┘  └────────┬────────┘
           │                       │
           │         ┌─────────────┴─────────────┐
           │         │                           │
           ▼         ▼                           ▼
      ┌────────┐  ┌────────┐                ┌─────────┐
      │Accept  │  │Reject  │                │Propose  │
      │Booking │  │Booking │                │Schedule │
      └───┬────┘  └────────┘                └────┬────┘
          │                                     │
          │                                     ▼
          │                           ┌─────────────────┐
          │                           │ Customer Accept │
          │                           └────────┬────────┘
          │                                    │
          ▼                                    │
    ┌─────────────┐                             │
    │Send Offer  │                             │
    └──────┬──────┘                             │
           │                                    │
    ┌──────┴──────┐                             │
    │            │                             │
    ▼            ▼                             │
┌────────┐   ┌────────┐                         │
│Customer│   │Counter │                         │
│Accept  │   │Offer   │──┐                      │
└───┬────┘   └────────┘  │                      │
    │                    │                      │
    ▼                    ▼                      │
┌─────────────────────────────┐                  │
│ Generate Contract           │                  │
└─────────────┬───────────────┘                  │
              │                                  │
              ▼                                  │
┌─────────────────────────────┐                  │
│ Both Parties Sign           │                  │
└─────────────┬───────────────┘                  │
              │                                  │
              ▼                                  │
┌─────────────────────────────┐                  │
│ Payment Link Generated      │                  │
└─────────────┬───────────────┘                  │
              │                                  │
              ▼                                  │
┌─────────────────────────────┐                  │
│ Customer Completes Payment  │                  │
└─────────────┬───────────────┘                  │
              │                                  │
              ▼                                  │
┌─────────────────────────────┐                  │
│ Visit Farm & Handover       │                  │
└─────────────┬───────────────┘                  │
              │                                  │
              ▼                                  │
       ┌────────────┐                            │
       │  COMPLETED │                            │
       └────────────┘                            │
```

**Diagram:** [08-booking-use-case.puml](08-booking-use-case.puml)

---

### Delivery Flow

```
┌──────────────────────┐
│ Order Confirmed      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Staff Receives Order│
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Prepare Items        │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Assign Driver        │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Driver Picks Up      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Status: IN_TRANSIT   │
│ Share Live Location   │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐     Customer     ┌─────────────────┐
│ Arrive at Destination│<──── Not Home ──>│ Reschedule/Cancel│
└──────────┬───────────┘     Issue         └─────────────────┘
           │ Customer Available
           ▼
┌──────────────────────┐
│ Customer Signs        │
│ Photo Taken          │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Upload Delivery Proof│
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Customer Confirms    │
└──────────┬───────────┘
           │
           ▼
       ┌────────────┐
       │ COMPLETED  │
       └────────────┘
```

**Diagram:** [09-delivery-flow.puml](09-delivery-flow.puml)

---

### Marketing Campaign Flow

```
┌──────────────────────┐
│ Create Campaign      │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Select Campaign Type│
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Define Rules         │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Configure Budget     │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐
│ Generate Vouchers    │
└──────────┬───────────┘
           │
           ▼
┌──────────────────────┐     Not Eligible
│ Customer Action      │<─────────────────┐
└──────────┬───────────┘                  │
           │ Eligible                     │
           ▼                              │
┌──────────────────────┐     Not Active   │
│ Check Campaign      │<─────────────────┤
└──────────┬───────────┘                  │
           │ Active                       │
           ▼                              │
┌──────────────────────┐     Limit Reached
│ Check Usage Limits   │<─────────────────┤
└──────────┬───────────┘                  │
           │ Available                    │
           ▼                              │
┌──────────────────────┐                  │
│ Apply Discount       │                  │
└──────────┬───────────┘                  │
           │                              │
           ▼                              │
    ┌─────────────┐                       │
    │ Order Complete│                      │
    └──────┬──────┘                       │
           │                              │
           ▼                              │
┌──────────────────────┐                  │
│ Update Analytics     │                  │
└──────────────────────┘                  │
```

**Diagram:** [11-marketing-campaign-flow.puml](11-marketing-campaign-flow.puml)

---

## System Architecture

```
┌─────────────────────────────────────────────────────────────────┐
│                        CLIENT LAYER                              │
│  ┌──────────┐  ┌──────────┐  ┌──────────────────┐             │
│  │Web Browser│  │Mobile App│  │ Admin Dashboard │             │
│  │(Livewire) │  │   (PWA)  │  │                 │             │
│  └────┬─────┘  └────┬─────┘  └────────┬─────────┘             │
└───────┼─────────────┼──────────────────┼───────────────────────┘
        │             │                  │
        └─────────────┼──────────────────┘
                      │
                      ▼
        ┌─────────────────────────┐
        │    Load Balancer        │
        │      (Nginx)            │
        └───────────┬─────────────┘
                    │
    ┌───────────────┼───────────────┐
    │               │               │
    ▼               ▼               ▼
┌─────────┐   ┌─────────┐    ┌─────────────┐
│API Routes│   │Livewire │    │Controllers  │
└────┬────┘   │Components│    │(REST API)   │
     │        └─────────┘    └──────┬──────┘
     │                              │
     └──────────┬───────────────────┘
                │
     ┌──────────┼──────────┐
     │          │          │
     ▼          ▼          ▼
┌─────────┐┌─────────┐┌─────────┐
│Product  ││Order    ││Payment  │
│Service  ││Service  ││Service │
└────┬────┘└────┬────┘└────┬────┘
     │          │          │
     └──────────┼──────────┘
                │
     ┌──────────┼──────────┐
     │          │          │
     ▼          ▼          ▼
┌─────────┐┌─────────┐┌─────────┐
│MySQL    ││Redis    ││File     │
│Database ││Cache    ││Storage  │
└─────────┘└─────────┘└─────────┘
     │          │          │
     └──────────┼──────────┘
                │
     ┌──────────┼──────────┐
     │          │          │
     ▼          ▼          ▼
┌─────────┐┌─────────┐┌─────────┐
│Midtrans ││Firebase ││Email    │
│Payment  ││  FCM    ││Service  │
└─────────┘└─────────┘└─────────┘
```

**Diagram:** [10-system-architecture.puml](10-system-architecture.puml)

---

## Notification Types

### Customer Notifications
| Type | Trigger | Channel |
|------|---------|---------|
| Payment Pending | Checkout initiated | Email, FCM |
| Payment Success | Payment completed | Email, FCM |
| Booking Approved | Farmer approves | Email, FCM |
| Booking Rejected | Farmer rejects | Email, FCM |
| Order Shipped | Driver picks up | FCM |
| Order Delivered | Driver arrives | FCM |
| Review Reminder | Order completed | Email |

### Farmer Notifications
| Type | Trigger | Channel |
|------|---------|---------|
| New Booking | Customer submits inquiry | Email, FCM |
| Booking Update | Customer responds | Email, FCM |
| New Review | Customer reviews | Email, FCM |
| Payment Received | Payment confirmed | Email, FCM |

### Staff Notifications
| Type | Trigger | Channel |
|------|---------|---------|
| Order Assigned | Admin assigns | FCM |
| Delivery Update | Status changed | FCM |
| Order Completed | Customer confirms | FCM |

---

## Product Types

| Type | Categories | Attributes | Features |
|------|------------|------------|----------|
| **Live** (Ternak) | Sapi, Kambing, Ayam, Bebek | Breed, Weight, Age, Sex, Origin | Booking system, Farm visit |
| **Fresh** (Daging) | Daging Sapi, Kambing, Ayam | Weight per unit, Expiry date | Cart checkout, Cold chain delivery |
| **Supply** (Pakan) | Pakan, Vitamin, Obat, Alat | Specifications, How to use | Standard checkout |

---

## Diagrams Index

| # | Diagram | File | Description |
|---|---------|------|-------------|
| 1 | Authentication Use Case | [01-use-case-authentication.puml](01-use-case-authentication.puml) | Registration, Login, 2FA |
| 2 | Customer Use Case | [02-use-case-customer.puml](02-use-case-customer.puml) | Shop, Cart, Checkout, Booking |
| 3 | Farmer Use Case | [03-use-case-farmer.puml](03-use-case-farmer.puml) | Products, Bookings, Analytics |
| 4 | Staff Use Case | [04-use-case-staff.puml](04-use-case-staff.puml) | Delivery, Operations |
| 5 | Admin Use Case | [05-use-case-admin.puml](05-use-case-admin.puml) | Full system management |
| 6 | Order Lifecycle State | [06-order-lifecycle-state.puml](06-order-lifecycle-state.puml) | Order state machine |
| 7 | Checkout Use Case | [07-checkout-use-case.puml](07-checkout-use-case.puml) | Checkout process |
| 8 | Booking Use Case | [08-booking-use-case.puml](08-booking-use-case.puml) | Live animal booking |
| 9 | Delivery Flow | [09-delivery-flow.puml](09-delivery-flow.puml) | Delivery operations |
| 10 | System Architecture | [10-system-architecture.puml](10-system-architecture.puml) | Component overview |
| 11 | Marketing Campaign | [11-marketing-campaign-flow.puml](11-marketing-campaign-flow.puml) | Campaign engine |

---

## Related Documentation

- **ERD**: [ERD.md](ERD.md) - Entity Relationship Diagram
- **API Documentation**: [API.md](API.md) - REST API endpoints
- **System Overview**: [README.md](../README.md) - Project overview

---

*Generated for AbodaFarm System Documentation*
*Last Updated: 2026-10-12*
