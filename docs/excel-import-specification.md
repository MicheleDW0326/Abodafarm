# Excel Import Specification - Products

## Database Audit Summary

### Products Table Complete Column List

| # | Column | Type | Nullable | Default | Description |
|---|--------|------|----------|---------|-------------|
| 1 | id | BIGINT UNSIGNED | No | Auto | Primary Key |
| 2 | owner_id | BIGINT UNSIGNED | Yes | NULL | FK to users (peternak/owner) |
| 3 | category_id | BIGINT UNSIGNED | No | - | FK to categories |
| 4 | name | VARCHAR(255) | No | - | Product name |
| 5 | breed | VARCHAR(255) | Yes | NULL | Ras/bangsa ternak (livestock only) |
| 6 | slug | VARCHAR(255) | No | Auto | URL-friendly slug |
| 7 | description | TEXT | Yes | NULL | Product description |
| 8 | price | DECIMAL(12,2) | No | - | Base price |
| 9 | discount_price | DECIMAL(12,2) | Yes | NULL | Discounted price (supply only) |
| 10 | discount_percent | DECIMAL(5,2) | Yes | NULL | Discount percentage (supply only) |
| 11 | image | VARCHAR(255) | Yes | NULL | Main image path |
| 12 | images | JSON | Yes | NULL | Multiple images array |
| 13 | type | ENUM | No | 'fresh' | 'live', 'fresh', 'supply' |
| 14 | status | ENUM | No | 'available' | 'available', 'pre_order', 'in_care', 'sold' |
| 15 | stock | INT | No | 0 | Stock quantity |
| 16 | unit | VARCHAR(50) | Yes | NULL | Unit label (supply only) |
| 17 | weight | DECIMAL(8,2) | Yes | NULL | Weight in kg (livestock) |
| 18 | age | VARCHAR(255) | Yes | NULL | Age string (livestock) |
| 19 | sex | ENUM | Yes | NULL | 'male', 'female', 'mixed' (livestock) |
| 20 | origin | VARCHAR(255) | Yes | NULL | Origin location (livestock) |
| 21 | condition | VARCHAR(255) | Yes | NULL | Health condition (livestock) |
| 22 | barn_location | VARCHAR(255) | Yes | NULL | Barn location (livestock) |
| 23 | weight_per_unit | DECIMAL(8,2) | Yes | NULL | Weight per unit (fresh only) |
| 24 | specifications | TEXT | Yes | NULL | Technical specs (supply only) |
| 25 | how_to_use | TEXT | Yes | NULL | Usage instructions (supply only) |
| 26 | shipping_info | TEXT | Yes | NULL | Shipping details (supply only) |
| 27 | is_featured | BOOLEAN | No | false | Featured flag |
| 28 | rating | DECIMAL(3,2) | No | 0 | Average rating |
| 29 | total_reviews | INT UNSIGNED | No | 0 | Total review count |
| 30 | rating_1_count | INT UNSIGNED | No | 0 | 1-star reviews |
| 31 | rating_2_count | INT UNSIGNED | No | 0 | 2-star reviews |
| 32 | rating_3_count | INT UNSIGNED | No | 0 | 3-star reviews |
| 33 | rating_4_count | INT UNSIGNED | No | 0 | 4-star reviews |
| 34 | rating_5_count | INT UNSIGNED | No | 0 | 5-star reviews |
| 35 | created_at | TIMESTAMP | No | Auto | Creation timestamp |
| 36 | updated_at | TIMESTAMP | No | Auto | Update timestamp |

---

## Field Classification

### Required Fields
| Column | Data Type | Validation |
|--------|-----------|------------|
| name | String | Required, max 255 chars |
| category_id | Integer | Required, must exist in categories table |
| type | Enum | Required: 'live', 'fresh', or 'supply' |
| price | Decimal | Required, min 0 |
| stock | Integer | Required, min 0 |

### Optional Fields
| Column | Data Type | Validation |
|--------|-----------|------------|
| description | Text | Max 10000 chars |
| breed | String | Max 255 chars (type='live') |
| weight | Decimal | Min 0, max 9999.99 (type='live') |
| age | String | Max 255 chars (type='live') |
| sex | Enum | 'male', 'female', 'mixed' (type='live') |
| origin | String | Max 255 chars (type='live') |
| condition | String | Max 255 chars (type='live') |
| barn_location | String | Max 255 chars (type='live') |
| weight_per_unit | Decimal | Min 0 (type='fresh') |
| unit | String | Max 50 chars (type='supply') |
| discount_price | Decimal | Min 0, must be < price |
| discount_percent | Decimal | 0-100 |
| specifications | Text | Max 10000 chars (type='supply') |
| how_to_use | Text | Max 10000 chars (type='supply') |
| shipping_info | Text | Max 10000 chars (type='supply') |
| is_featured | Boolean | 0 or 1 |

### Auto Generated Fields
| Column | How Generated |
|--------|---------------|
| id | Auto-increment |
| slug | Auto-generated from name (str_slug) |
| owner_id | Set from authenticated user or NULL |
| images | JSON array from image uploads |
| status | Default: 'available' |
| rating | Calculated from reviews |
| total_reviews | Count of approved reviews |
| rating_*_count | Count per rating level |
| created_at | Current timestamp |
| updated_at | Current timestamp on save |

### Relationship Fields
| Column | Relationship |
|--------|--------------|
| category_id | BelongsTo Category |
| owner_id | BelongsTo User |

---

## Category Reference

### Live Product Categories
| ID | Name | Slug |
|----|------|------|
| 1 | Sapi | sapi |
| 2 | Kambing | kambing |
| 3 | Ayam | ayam |
| 4 | Bebek | bebek |

### Fresh Product Categories
| ID | Name | Slug |
|----|------|------|
| 5 | Daging Segar | daging-segar |

### Supply Product Categories
| ID | Name | Slug |
|----|------|------|
| 6 | Pakan Ternak | pakan-ternak |
| 7 | Vitamin & Suplemen | vitamin-suplemen |
| 8 | Obat-Obatan | obat-obatan |
| 9 | Alat Minum | alat-minum |
| 10 | Alat Makan | alat-makan |
| 11 | Alat Kebersihan | alat-kebersihan |
| 12 | Perlengkapan Kandang | perlengkapan-kandang |
| 13 | Lainnya | lainnya |

---

## Excel Template Structure

### Sheet 1: Product Import Template

| Column | Header Name | Type | Required | Validation | Example |
|--------|-------------|------|----------|-----------|---------|
| A | Nama Produk | String | Yes | Max 255 chars | "Sapi Limousin Betina" |
| B | Kategori | String | Yes | Must match category name | "Sapi" |
| C | Tipe Produk | String | Yes | live/fresh/supply | "live" |
| D | Harga | Number | Yes | Min 0 | 15000000 |
| E | Stok | Number | Yes | Min 0 | 5 |
| F | Deskripsi | String | No | Max 10000 chars | "Sapi limousin sehat..." |
| G | Ras | String | No | Max 255 chars | "Limousin" |
| H | Berat (kg) | Number | No | Min 0 | 350.5 |
| I | Umur | String | No | Max 255 chars | "2 Tahun" |
| J | Jenis Kelamin | String | No | male/female/mixed | "female" |
| K | Asal | String | No | Max 255 chars | "Jawa Timur" |
| L | Kondisi | String | No | Max 255 chars | "Sehat" |
| M | Lokasi Kandang | String | No | Max 255 chars | "Kandang A" |
| N | Berat Per Unit (kg) | Number | No | Min 0 | 25.5 |
| O | Unit | String | No | Max 50 chars | "kg" |
| P | Harga Diskon | Number | No | Min 0 | 45000 |
| Q | Diskon (%) | Number | No | 0-100 | 10 |
| R | Spesifikasi | String | No | Max 10000 chars | "Komposisi:..." |
| S | Cara Penggunaan | String | No | Max 10000 chars | "Campurkan 100g..." |
| T | Info Pengiriman | String | No | Max 10000 chars | "Gratis ongkir..." |
| U | Unggulan | Number | No | 0 or 1 | 1 |

### Sheet 2: Instructions

See next section for detailed instructions.

---

## Common Mistakes to Avoid

1. **Category Name Mismatch**
   - Wrong: "sapi", "SAPI", "Sapi "
   - Correct: "Sapi" (exact match, case-insensitive)

2. **Type Values**
   - Wrong: "livestock", "meat", "product", "Ternak"
   - Correct: "live", "fresh", "supply"

3. **Sex Values**
   - Wrong: "jantan", "betina", "male ", "JANTAN"
   - Correct: "male", "female", "mixed" (lowercase, no spaces)

4. **Price Format**
   - Wrong: "Rp 15.000.000", "15,000,000", "15jt"
   - Correct: 15000000 (plain number, no formatting)

5. **Decimal Format**
   - Wrong: "350,5", "350.5 kg"
   - Correct: 350.5

6. **Empty Rows**
   - Remove all empty rows before import

7. **Special Characters**
   - Avoid: < > & in text fields (will be escaped)

8. **Image URLs**
   - Not supported in initial import
   - Images must be uploaded separately

9. **Duplicate Products**
   - System will create new entries even if name matches
   - Use "Update" mode to update existing products
