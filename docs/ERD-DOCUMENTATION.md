# AbodaFarm ERD Documentation

## Overview

Dokumentasi ini berisi Entity Relationship Diagram (ERD) untuk sistem AbodaFarm - platform e-commerce peternakan berbasis Laravel 13 + Livewire 4.

## File ERD

| File | Deskripsi |
|------|-----------|
| `erd-abodafarm.puml` | ERD Lengkap dengan semua entitas dan field |
| `erd-abodafarm-simple.puml` | ERD Simplified fokus ke relasi utama |

## Cara Melihat ERD di VS Code

1. Install extension **PlantUML** di VS Code
2. Install PlantUML extension untuk preview (atau gunakan extension **PlantUML Viewer**)
3. Buka file `.puml` yang ingin dilihat
4. Tekan `Alt+D` untuk preview PlantUML
5. Atau klik kanan pada file > **Preview Current Diagram**

## Database Packages

### 1. AUTH & USERS
```
users (core user table dengan role: admin/staff/peternak/pelanggan)
├── customer_levels (Bronze, Silver, Gold, Platinum)
├── fcm_tokens (Firebase Cloud Messaging tokens)
├── roles (Spatie permission roles)
└── model_has_roles (role assignments)
```

### 2. PRODUCTS & CATEGORIES
```
categories (kategori produk: Sapi, Kambing, Ayam, dll)
├── products (ternak hidup, daging segar, perlengkapan)
│   ├── inventory_logs (log perubahan stok)
│   ├── care_records (record perawatan ternak)
│   ├── product_views (riwayat view produk)
│   └── favorites (wishlist)
```

### 3. ORDERS & TRANSACTIONS
```
orders
├── order_items (item dalam pesanan)
├── payments (pembayaran Midtrans)
├── order_status_logs (log perubahan status)
├── order_statuses (master status pembayaran)
├── booking_statuses (master status booking)
├── shipment_statuses (master status pengiriman)
└── deliveries (data pengiriman)
    └── delivery_timelines (timeline pengiriman)
```

### 4. DELIVERY & SHIPPING
```
deliveries
├── delivery_timelines
└── delivery_areas (area cakupan pengiriman)
```

### 5. REVIEWS & RATINGS
```
reviews
├── review_photos
├── review_videos
├── review_likes
└── review_reports
```

### 6. FARM PROFILES & SOCIAL
```
farm_profiles
├── farm_galleries
├── farmer_badges
└── farmer_followers (sosial following)
```

### 7. ADDRESSES
```
addresses (alamat user dengan GPS coordinates)
```

### 8. PROMOTIONS & LOYALTY
```
promotions
├── promotion_usages
├── marketing_campaigns
│   └── campaign_rules
```

### 9. NOTIFICATIONS
```
customer_notifications (pelanggan)
peternak_notifications (peternak)
staff_notifications (staf)
```

### 10. CONTENT MANAGEMENT
```
hero_slides
testimonials
faqs
farm_statistics
```

## Entity Relationships Summary

### User Flow
```
User (pelanggan) ────> Orders ────> Payments
     │                              │
     └──> Addresses                 │
     └──> Favorites                 │
     └──> Reviews ────> Products    │
     └──> Notifications             │
                                   │
User (peternak) ────> FarmProfile ──┘
     │
     └──> Products ────> InventoryLogs
     └──> CareRecords
     └──> Notifications
```

### Order Flow
```
Orders (cart/inquiry)
    │
    ├──> OrderItems ──> Products ──> Categories
    │
    ├──> Payments (Midtrans)
    │
    ├──> OrderStatusLogs
    │
    └──> Deliveries ──> DeliveryTimelines
              │
              └──> Assigned Staff
```

### Review Flow
```
Order (completed)
    │
    └──> Reviews ──> Product/Farmer
              │
              ├──> ReviewPhotos
              ├──> ReviewVideos
              ├──> ReviewLikes
              └──> ReviewReports
```

## Key Enums

### Product Types
- `live` - Ternak Hidup
- `fresh` - Daging Segar
- `supply` - Pakan & Perlengkapan

### Order Types
- `cart` - Pembelian (Fresh Meat)
- `inquiry` - Booking (Livestock)

### Order Status Flow
```
CART: pending → paid → processing → shipped → delivered → completed
INQUIRY: pending → approved/rejected → processing → shipped → delivered → completed
```

### Review Types
- `product` - Review untuk produk
- `farmer` - Review untuk peternak

## Customer Levels

| Level | Min Points | Max Points | Discount |
|-------|------------|------------|----------|
| Bronze | 0 | 499 | 0% |
| Silver | 500 | 1999 | 2% |
| Gold | 2000 | 4999 | 5% |
| Platinum | 5000+ | - | 10% |

## Indexes & Performance

Database memiliki performance indexes untuk:
- Product searches (category, type, status)
- Order queries (user_id, status, order_type)
- Review queries (product_id, rating)
- Notification queries (user_id, is_read)

## Revision History

- 2026-10-13: Initial ERD creation based on system audit
