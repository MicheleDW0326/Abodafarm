# Database Table Specification
## AbodaFarm - Transaction Support Tables

---

## Overview

Dokumen ini mendefinisikan **tabel database** yang mendukung transaksi utama checkout dan order di AbodaFarm. Fokus pada transaksi yang traceable dari Use Case ke Transaction Flow ke Database.

---

## 1. Core Transaction Tables

### 1.1 Orders Table

**Purpose:** Menyimpan data pesanan utama (checkout transaction)

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| order_number | VARCHAR(50) | - | UNIQUE, NOT NULL | Business order number (e.g., ORD-20261013-001) |
| user_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to customers (users table) |
| assigned_staff_id | BIGINT UNSIGNED | FK | NULLABLE, INDEX | Staff assigned to fulfill order |
| assigned_by | BIGINT UNSIGNED | FK | NULLABLE | Admin who assigned staff |
| order_type | ENUM('cart','inquiry') | - | NOT NULL, DEFAULT 'cart' | Type of order |
| customer_name | VARCHAR(255) | - | NOT NULL | Customer name for this order |
| customer_phone | VARCHAR(20) | - | NOT NULL | Customer phone |
| customer_address | TEXT | - | NOT NULL | Full delivery address |
| notes | TEXT | - | NULLABLE | Order notes/special requests |
| total_items | INT UNSIGNED | - | NOT NULL, DEFAULT 0 | Total quantity |
| total_amount | DECIMAL(14,2) | - | NOT NULL | Total order amount |
| discount_amount | DECIMAL(14,2) | - | DEFAULT 0.00 | Discount applied |
| final_amount | DECIMAL(14,2) | - | NOT NULL | Final amount after discount |
| promo_code_used | VARCHAR(50) | FK | NULLABLE | Promo code if used |
| status | VARCHAR(30) | - | NOT NULL | Current order status |
| placed_at | TIMESTAMP | - | NULLABLE | When order was placed |
| preferred_delivery_date | DATE | - | NULLABLE | Desired delivery date |
| pickup_location | VARCHAR(255) | - | NULLABLE | For inquiry type orders |
| admin_notes | TEXT | - | NULLABLE | Admin notes |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- UNIQUE (order_number)
- INDEX (user_id)
- INDEX (assigned_staff_id)
- INDEX (status)
- INDEX (order_type)
- INDEX (placed_at)

**Relationships:**
```
orders.user_id → users.id (Many-to-One)
orders.assigned_staff_id → users.id (Many-to-One)
orders.promo_code_used → promotions.code (Many-to-One)
```

---

### 1.2 Order Items Table

**Purpose:** Menyimpan detail item dalam pesanan

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to orders |
| product_id | BIGINT UNSIGNED | **FK** | NULLABLE, INDEX | Reference to products |
| product_name | VARCHAR(255) | - | NOT NULL | Product name snapshot |
| product_image | VARCHAR(255) | - | NULLABLE | Product image snapshot |
| quantity | INT UNSIGNED | - | NOT NULL | Ordered quantity |
| unit_price | DECIMAL(12,2) | - | NOT NULL | Price per unit at order time |
| subtotal | DECIMAL(14,2) | - | NOT NULL | quantity × unit_price |
| farmer_id | BIGINT UNSIGNED | FK | NULLABLE | Farmer who owns this product |
| notes | TEXT | - | NULLABLE | Item-specific notes |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- INDEX (order_id)
- INDEX (product_id)
- INDEX (farmer_id)

**Relationships:**
```
order_items.order_id → orders.id (Many-to-One)
order_items.product_id → products.id (Many-to-One)
order_items.farmer_id → users.id (Many-to-One)
```

---

### 1.3 Payments Table

**Purpose:** Menyimpan data pembayaran Midtrans

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | **FK** | UNIQUE, NOT NULL | Reference to orders |
| transaction_id | VARCHAR(100) | - | UNIQUE, NULLABLE | Midtrans transaction ID |
| payment_type | VARCHAR(50) | - | NULLABLE | Payment method (credit_card, bank_transfer, etc) |
| gross_amount | DECIMAL(14,2) | - | NOT NULL | Total payment amount |
| net_amount | DECIMAL(14,2) | - | NULLABLE | Net amount after fees |
| transaction_status | VARCHAR(30) | - | NOT NULL | Midtrans status |
| fraud_status | VARCHAR(20) | - | NULLABLE | Fraud detection status |
| snap_token | TEXT | - | NULLABLE | Snap API token |
| snap_redirect_url | VARCHAR(500) | - | NULLABLE | Redirect URL for payment |
| payment_method | VARCHAR(50) | - | NULLABLE | Actual payment method used |
| paid_at | TIMESTAMP | - | NULLABLE | When payment was completed |
| expiry_time | TIMESTAMP | - | NULLABLE | Payment expiry time |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- UNIQUE (order_id)
- UNIQUE (transaction_id)
- INDEX (transaction_status)

**Relationships:**
```
payments.order_id → orders.id (One-to-One)
```

---

### 1.4 Promotions Table

**Purpose:** Menyimpan data kode promo dan kampanye

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| code | VARCHAR(50) | - | UNIQUE, NOT NULL | Promo code |
| title | VARCHAR(255) | - | NOT NULL | Promo title |
| description | TEXT | - | NULLABLE | Promo description |
| type | ENUM('percentage','fixed','buy_x_get_y') | - | NOT NULL | Discount type |
| discount_value | DECIMAL(10,2) | - | NOT NULL | Discount amount/percentage |
| max_discount_amount | DECIMAL(12,2) | - | NULLABLE | Maximum discount cap |
| min_purchase_amount | DECIMAL(12,2) | - | DEFAULT 0.00 | Minimum purchase to use |
| usage_limit | INT UNSIGNED | - | NULLABLE | Total usage limit |
| usage_count | INT UNSIGNED | - | DEFAULT 0 | Current usage count |
| per_user_limit | INT UNSIGNED | - | DEFAULT 1 | Usage per user |
| is_active | BOOLEAN | - | DEFAULT TRUE | Promo active status |
| starts_at | TIMESTAMP | - | NULLABLE | Promo start date |
| expires_at | TIMESTAMP | - | NULLABLE | Promo expiry date |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- UNIQUE (code)
- INDEX (is_active)
- INDEX (starts_at, expires_at)

---

### 1.5 Promotion Usages Table

**Purpose:** Track penggunaan promo oleh user

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| promotion_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to promotions |
| user_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | User who used promo |
| order_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Order where promo used |
| discount_amount | DECIMAL(12,2) | - | NOT NULL | Actual discount given |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Usage timestamp |

**Indexes:**
- PRIMARY (id)
- INDEX (promotion_id)
- INDEX (user_id)
- INDEX (order_id)
- UNIQUE (promotion_id, user_id) -- per_user_limit enforcement

**Relationships:**
```
promotion_usages.promotion_id → promotions.id (Many-to-One)
promotion_usages.user_id → users.id (Many-to-One)
promotion_usages.order_id → orders.id (Many-to-One)
```

---

### 1.6 Products Table (Transaction Relevant Fields)

**Purpose:** Menyimpan data produk (fokus field untuk transaksi)

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| category_id | BIGINT UNSIGNED | FK | NOT NULL, INDEX | Reference to categories |
| owner_id | BIGINT UNSIGNED | FK | NOT NULL, INDEX | Farmer who owns product |
| name | VARCHAR(255) | - | NOT NULL | Product name |
| slug | VARCHAR(255) | - | UNIQUE | URL-friendly name |
| price | DECIMAL(12,2) | - | NOT NULL | Product price |
| discount_price | DECIMAL(12,2) | - | NULLABLE | Discounted price |
| discount_percent | DECIMAL(5,2) | - | NULLABLE | Discount percentage |
| type | ENUM('live','fresh','supply') | - | NOT NULL | Product type |
| status | ENUM('available','pre_order','in_care','sold') | - | NOT NULL | Availability status |
| stock | INT UNSIGNED | - | NOT NULL, DEFAULT 0 | Current stock quantity |
| unit | VARCHAR(50) | - | NULLABLE | Unit of measurement |
| weight | DECIMAL(10,2) | - | NULLABLE | Weight (for livestock) |
| is_featured | BOOLEAN | - | DEFAULT FALSE | Featured product flag |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- UNIQUE (slug)
- INDEX (category_id)
- INDEX (owner_id)
- INDEX (type)
- INDEX (status)
- INDEX (stock)
- INDEX (price)

---

### 1.7 Inventory Logs Table

**Purpose:** Track perubahan stok/inventory

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| product_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to products |
| recorded_by | BIGINT UNSIGNED | FK | NOT NULL, INDEX | User who recorded |
| type | ENUM('in','out','adjustment','reserve','release') | - | NOT NULL | Log type |
| quantity | INT | - | NOT NULL | Quantity changed (positive/negative) |
| previous_stock | INT UNSIGNED | - | NOT NULL | Stock before change |
| new_stock | INT UNSIGNED | - | NOT NULL | Stock after change |
| reason | VARCHAR(255) | - | NULLABLE | Reason for change |
| reference_type | VARCHAR(50) | - | NULLABLE | Related entity type (order, manual) |
| reference_id | BIGINT UNSIGNED | - | NULLABLE | Related entity ID |
| notes | TEXT | - | NULLABLE | Additional notes |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Log timestamp |

**Indexes:**
- PRIMARY (id)
- INDEX (product_id)
- INDEX (recorded_by)
- INDEX (type)
- INDEX (reference_type, reference_id)

---

### 1.8 Users Table (Transaction Relevant Fields)

**Purpose:** User data (fokus field untuk transaksi)

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| name | VARCHAR(255) | - | NOT NULL | User full name |
| email | VARCHAR(255) | - | UNIQUE, NOT NULL | Email address |
| password | VARCHAR(255) | - | NOT NULL | Hashed password |
| email_verified_at | TIMESTAMP | - | NULLABLE | Email verification |
| role | ENUM('admin','staf','peternak','pelanggan') | - | NOT NULL | User role |
| loyalty_points | INT UNSIGNED | - | DEFAULT 0 | Loyalty points balance |
| total_orders | INT UNSIGNED | - | DEFAULT 0 | Total completed orders |
| total_spent | DECIMAL(14,2) | - | DEFAULT 0.00 | Total spending |
| customer_level_id | BIGINT UNSIGNED | FK | NULLABLE | Customer level |
| last_order_at | TIMESTAMP | - | NULLABLE | Last order timestamp |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- UNIQUE (email)
- INDEX (role)
- INDEX (customer_level_id)

---

### 1.9 Addresses Table

**Purpose:** Menyimpan alamat pengiriman user

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| user_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to users |
| label | VARCHAR(50) | - | NULLABLE | Address label (Rumah, Kantor) |
| recipient_name | VARCHAR(255) | - | NOT NULL | Recipient name |
| phone | VARCHAR(20) | - | NOT NULL | Contact phone |
| address | TEXT | - | NOT NULL | Full address |
| province | VARCHAR(100) | - | NOT NULL | Province |
| city | VARCHAR(100) | - | NOT NULL | City/Kota |
| district | VARCHAR(100) | - | NOT NULL | District/Kecamatan |
| postal_code | VARCHAR(10) | - | NULLABLE | Postal code |
| is_default | BOOLEAN | - | DEFAULT FALSE | Default address flag |
| is_active | BOOLEAN | - | DEFAULT TRUE | Active status |
| latitude | DECIMAL(10,8) | - | NULLABLE | GPS latitude |
| longitude | DECIMAL(11,8) | - | NULLABLE | GPS longitude |
| courier_notes | TEXT | - | NULLABLE | Notes for courier |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Creation timestamp |
| updated_at | TIMESTAMP | - | ON UPDATE CURRENT_TIMESTAMP | Last update |

**Indexes:**
- PRIMARY (id)
- INDEX (user_id)
- INDEX (is_default)
- INDEX (is_active)

---

### 1.10 Order Status Logs Table

**Purpose:** Track perubahan status pesanan

| Field | Type | PK/FK | Constraint | Description |
|-------|------|--------|------------|-------------|
| id | BIGINT UNSIGNED | **PK** | AUTO_INCREMENT | Primary key |
| order_id | BIGINT UNSIGNED | **FK** | NOT NULL, INDEX | Reference to orders |
| from_status | VARCHAR(30) | - | NULLABLE | Previous status |
| to_status | VARCHAR(30) | - | NOT NULL | New status |
| triggered_by_type | VARCHAR(30) | - | NOT NULL | Who triggered (customer/admin/system/webhook) |
| triggered_by_id | BIGINT UNSIGNED | - | NULLABLE | User ID who triggered |
| notes | TEXT | - | NULLABLE | Additional notes |
| created_at | TIMESTAMP | - | DEFAULT CURRENT_TIMESTAMP | Change timestamp |

**Indexes:**
- PRIMARY (id)
- INDEX (order_id)
- INDEX (created_at)

---

## 2. Cardinality & Relationships

### 2.1 Entity Relationship Diagram (Transaction Focus)

```
┌─────────────────────────────────────────────────────────────────────────────┐
│                         TRANSACTION CORE FLOW                                 │
└─────────────────────────────────────────────────────────────────────────────┘

    ┌──────────┐         ┌──────────────┐         ┌──────────────┐
    │  users   │         │   orders     │         │ order_items  │
    │──────────│         │──────────────│         │──────────────│
    │ PK id    │──────<  │ PK id        │    1──<│ PK id        │
    │          │    1    │ FK user_id   │    N    │ FK order_id  │
    │          │         │ FK staff_id  │         │ FK product_id│
    └──────────┘         └──────┬───────┘         │ FK farmer_id │
                               │                  └──────────────┘
                               │
              ┌────────────────┼────────────────┐
              │                │                │
              ▼                ▼                ▼
    ┌──────────────┐  ┌──────────────┐  ┌──────────────┐
    │  payments    │  │ promotions   │  │   orders     │
    │──────────────│  │──────────────│  │   (self)     │
    │ PK id        │  │ PK id        │  │ FK prev_status
    │ FK order_id  │  │              │  │
    └──────────────┘  └──────┬───────┘  └──────────────┘
                              │
                              ▼
                    ┌──────────────────┐
                    │promotion_usages │
                    │─────────────────│
                    │ PK id           │
                    │ FK promotion_id │
                    │ FK user_id      │
                    │ FK order_id     │
                    └─────────────────┘
```

### 2.2 Transaction Flow Relationships

```
users ──────────────┬──────────────────────────> orders
                    │  1:N (user places many orders)
                    │
                    ├──────────────────────────> addresses
                    │  1:N (user has many addresses)
                    │
                    └──────────────────────────> promotion_usages
                        1:N (user uses many promos)

orders ──────────────┼──────────────────────────> order_items
                    │  1:N (order has many items)
                    │
                    ├──────────────────────────> payments
                    │  1:1 (order has one payment)
                    │
                    ├──────────────────────────> order_status_logs
                    │  1:N (order has status history)
                    │
                    └──────────────────────────> promotion_usages
                        1:N (order can use promo)

order_items ────────┼──────────────────────────> products
                    │  N:1 (item references product)
                    │
                    └──────────────────────────> users (farmer)
                        N:1 (product owned by farmer)

products ───────────┼──────────────────────────> inventory_logs
                    │  1:N (product has stock changes)
                    │
                    ├──────────────────────────> categories
                    │  N:1 (product belongs to category)
                    │
                    └──────────────────────────> users (owner)
                        N:1 (product owned by farmer)
```

---

## 3. Transaction Flow → Database Mapping

### 3.1 Checkout Transaction Mapping

| Transaction Step | Database Operation | Tables Affected |
|------------------|-------------------|------------------|
| 1. Add to Cart | Session only (no DB) | - |
| 2. View Cart | Session + Product lookup | products |
| 3. Checkout | Validate items | order_items (temp) |
| 4. Select Address | Fetch user addresses | addresses |
| 5. Apply Promo | Validate & check usage | promotions, promotion_usages |
| 6. Select Payment | Store payment preference | orders |
| 7. Generate Order | **INSERT order** | orders |
| 8. Store Items | **INSERT order_items** | order_items |
| 9. Reserve Stock | **UPDATE products.stock** | products |
| 10. Log Inventory | **INSERT inventory_logs** | inventory_logs |
| 11. Initiate Payment | Create Midtrans token | payments |
| 12. Payment Callback | **UPDATE orders.status** | orders |
| 13. Notification | **INSERT notifications** | customer_notifications |
| 14. Complete Order | Update loyalty points | users |

---

## 4.完整性验证 (Completeness Check)

### 4.1 Data Requirements for Transaction

| Requirement | Covered | Table(s) |
|-------------|---------|----------|
| Customer identification | ✅ | users.id |
| Address for delivery | ✅ | addresses, orders.customer_address |
| Cart items | ✅ | order_items |
| Product pricing | ✅ | products.price, order_items.unit_price |
| Discount calculation | ✅ | promotions, promotion_usages |
| Payment processing | ✅ | payments |
| Stock management | ✅ | products.stock, inventory_logs |
| Order status tracking | ✅ | orders.status, order_status_logs |
| Loyalty points | ✅ | users.loyalty_points |
| Multi-farmer support | ✅ | order_items.farmer_id |

### 4.2 Business Rules Enforced

| Rule | Implementation |
|------|---------------|
| Stock check before order | Application logic + inventory_logs |
| Promo usage limit | promotion_usages table + per_user_limit |
| Order number uniqueness | orders.order_number UNIQUE index |
| Payment order linkage | payments.order_id UNIQUE index |
| Status change audit | order_status_logs table |
| Customer address ownership | addresses.user_id FK |

---

## 5. Sample Transaction Queries

### 5.1 Create Order Transaction

```sql
-- 1. Start transaction
BEGIN;

-- 2. Create order
INSERT INTO orders (order_number, user_id, customer_name, customer_phone,
    customer_address, total_items, total_amount, final_amount, status)
VALUES ('ORD-20261013-001', 123, 'John Doe', '08123456789',
    'Jl. Raya No. 1, Jakarta', 3, 1500000, 1350000, 'pending');

SET @order_id = LAST_INSERT_ID();

-- 3. Insert order items
INSERT INTO order_items (order_id, product_id, product_name, quantity, unit_price, subtotal)
VALUES
    (@order_id, 1, 'Sapi Limousin', 1, 1000000, 1000000),
    (@order_id, 2, 'Pakan Ternak Premium', 2, 250000, 500000);

-- 4. Update product stock
UPDATE products SET stock = stock - 1 WHERE id = 1;
UPDATE products SET stock = stock - 2 WHERE id = 2;

-- 5. Log inventory changes
INSERT INTO inventory_logs (product_id, recorded_by, type, quantity, previous_stock, new_stock, reason, reference_type, reference_id)
VALUES
    (1, 123, 'out', -1, 10, 9, 'Order placed', 'order', @order_id),
    (2, 123, 'out', -2, 50, 48, 'Order placed', 'order', @order_id);

-- 6. Apply promo if used
INSERT INTO promotion_usages (promotion_id, user_id, order_id, discount_amount)
VALUES (1, 123, @order_id, 150000);

-- 7. Update promo usage count
UPDATE promotions SET usage_count = usage_count + 1 WHERE id = 1;

-- 8. Commit
COMMIT;
```

---

## 6. Database Diagram

### 6.1 Physical Model (Simplified)

```
╔══════════════════════════════════════════════════════════════════════╗
║                        TRANSACTION TABLES                             ║
╠══════════════════════════════════════════════════════════════════════╣
║                                                                       ║
║  ┌──────────┐     ┌─────────────────────────────────────────────┐    ║
║  │  users   │     │                   orders                     │    ║
║  ├──────────┤     ├─────────────────────────────────────────────┤    ║
║  │ PK id ●──┼────<│ PK id                                       │    ║
║  │          │ 1:N  │ FK user_id ●                                │    ║
║  │ email    │     │ FK assigned_staff_id ○                      │    ║
║  │ loyalty  │     │ order_number                                 │    ║
║  │ total    │     │ order_type                                   │    ║
║  └──────────┘     │ customer_name                                │    ║
║       │           │ customer_address                              │    ║
║       │           │ total_amount                                  │    ║
║       │           │ discount_amount                               │    ║
║       │           │ final_amount                                  │    ║
║       │           │ status                                        │    ║
║       │           └───────────────────┬─────────────────────────┘    ║
║       │                               │                              ║
║       │        ┌───────────────────────┼───────────────────────┐     ║
║       │        │                       │                       │     ║
║       │        ▼                       ▼                       ▼     ║
║       │  ┌───────────┐     ┌─────────────────┐     ┌──────────────┐  ║
║       │  │ addresses │     │   order_items   │     │   payments   │  ║
║       │  ├───────────┤     ├─────────────────┤     ├──────────────┤  ║
║       │  │ PK id ●──┼──<│ PK id            │     │ PK id        │  ║
║       │  │ FK user  │1:N │ FK order_id ●   │1:N   │ FK order_id ●│  ║
║       │  │ name     │     │ FK product_id ○ │      │ transaction_ │  ║
║       │  │ address  │     │ FK farmer_id ○  │      │   id         │  ║
║       │  │ phone    │     │ product_name    │      │ gross_amount │  ║
║       │  └───────────┘     │ quantity        │      │ status       │  ║
║       │                    │ unit_price     │      └──────────────┘  ║
║       │                    │ subtotal       │                        ║
║       │                    └────────┬───────┘                        ║
║       │                             │                                 ║
║       │                             ▼                                 ║
║       │                      ┌───────────┐                          ║
║       │                      │ products  │                          ║
║       │                      ├───────────┤                          ║
║       │                      │ PK id ●   │                          ║
║       │                      │ FK owner  │○                          ║
║       │                      │ name      │                          ║
║       │                      │ price     │                          ║
║       │                      │ stock     │                          ║
║       │                      │ type      │                          ║
║       │                      └─────┬─────┘                          ║
║       │                            │                                ║
║       │                            ▼                                ║
║       │                      ┌───────────┐     ┌──────────────────┐ ║
║       │                      │inventory_ │     │   promotions     │ ║
║       │                      │   logs    │     ├──────────────────┤ ║
║       │                      ├───────────┤     │ PK id            │ ║
║       │                      │ PK id     │     │ code             │ ║
║       │                      │ FK product│     │ discount_value   │ ║
║       │                      │ type      │     │ usage_limit      │ ║
║       │                      │ quantity  │     │ usage_count      │ ║
║       │                      │ prev_stock│     │ min_purchase     │ ║
║       │                      │ new_stock │     │ is_active        │ ║
║       │                      └───────────┘     └────────┬─────────┘ ║
║       │                                                  │           ║
║       │                                                  ▼           ║
║       │                                          ┌─────────────────┐ ║
║       │                                          │promotion_usages│ ║
║       │                                          ├─────────────────┤ ║
║       │                                          │ PK id           │ ║
║       │                                          │ FK promotion_id●│ ║
║       └───────────────────────────────────────>│ FK user_id  ●   │ ║
║                          1:N                    │ FK order_id ●   │ ║
║                                                  │ discount_amount │ ║
║                                                  └─────────────────┘ ║
║                                                                       ║
╚══════════════════════════════════════════════════════════════════════╝

Legend:
  ● = NOT NULL
  ○ = NULLABLE
  > = Foreign Key reference
  < = Foreign Key source (Many side)
```

---

*Document Version: 1.0*
*Last Updated: 2026-10-13*
*Purpose: Transaction Support Database Design*
