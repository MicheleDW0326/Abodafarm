# Search Console & Analytics Verification Setup

## Files Added

### Components
- `resources/views/components/webmaster-verification.blade.php` - Search engine verification meta tags
- `resources/views/components/analytics.blade.php` - Analytics & tracking scripts
- `resources/views/components/analytics-body-start.blade.php` - GTM noscript iframe

### Configuration Updated
- `config/services.php` - Added Google, Bing, Yandex, Clarity, Facebook configurations
- `.env.example` - Added environment variable templates

### Layouts Updated
- `resources/views/layouts/main.blade.php` - Added verification & analytics
- `resources/views/layouts/guest-home.blade.php` - Added verification & analytics
- `resources/views/partials/head.blade.php` - Added verification meta

## Environment Variables

Add these to your `.env` file:

```env
# Search Console Verification
GOOGLE_SITE_VERIFICATION=your-google-verification-code
BING_SITE_VERIFICATION=your-bing-verification-code
YANDEX_SITE_VERIFICATION=your-yandex-verification-code

# Analytics & Tracking
GOOGLE_ANALYTICS_ID=G-XXXXXXXXXX
GOOGLE_TAG_MANAGER_ID=GTM-XXXXXXX
CLARITY_PROJECT_ID=your-clarity-project-id
FACEBOOK_PIXEL_ID=your-facebook-pixel-id

# Facebook Pixel Events
FACEBOOK_PIXEL_EVENTS_ENABLED=false
```

## Supported Tools

| Tool | Meta Tag | Environment Variable |
|------|----------|---------------------|
| Google Search Console | `google-site-verification` | `GOOGLE_SITE_VERIFICATION` |
| Bing Webmaster | `msvalidate.01` | `BING_SITE_VERIFICATION` |
| Yandex Webmaster | `yandex-verification` | `YANDEX_SITE_VERIFICATION` |
| Google Analytics 4 | gtag.js | `GOOGLE_ANALYTICS_ID` |
| Google Tag Manager | GTM container | `GOOGLE_TAG_MANAGER_ID` |
| Microsoft Clarity | clarity.js | `CLARITY_PROJECT_ID` |
| Facebook Pixel | fbevents.js | `FACEBOOK_PIXEL_ID` |

## Usage

All tags are automatically included in layouts. Just set the environment variables.

### Example Setup

```env
# Google Search Console (from verification page)
GOOGLE_SITE_VERIFICATION=abc123xyz...

# Google Analytics 4
GOOGLE_ANALYTICS_ID=G-XY12345678

# Google Tag Manager
GOOGLE_TAG_MANAGER_ID=GTM-ABC1234

# Microsoft Clarity
CLARITY_PROJECT_ID=abc123def456

# Facebook Pixel
FACEBOOK_PIXEL_ID=1234567890123456
FACEBOOK_PIXEL_EVENTS_ENABLED=true
```

---

# Breadcrumb Navigation - Automatic Breadcrumbs

## Files Added

### Services
- `app/Services/BreadcrumbService.php` - Automatic breadcrumb generation service

### Components
- `resources/views/components/breadcrumb.blade.php` - Reusable breadcrumb component

## Features

### Auto-Detection
- Automatically detects current route
- Generates breadcrumbs based on route pattern
- Supports nested routes (e.g., `/peternak/{province}/{regency}`)

### Route Support
- Homepage: `Beranda`
- Shop: `Beranda > Toko`
- Product: `Beranda > Toko > {Product Name}`
- Farmer: `Beranda > Peternak > {Farm Name}`
- Location: `Beranda > Peternak > {Province} > {Regency}`
- Dashboard pages with full path

### Schema.org Support
- Generates BreadcrumbList JSON-LD schema
- Includes position, name, and URL for each item
- SEO-friendly structured data

---

# XML Sitemap - Search Engine Crawlability

## Files Added

### Services
- `app/Services/SitemapService.php` - XML sitemap generator

### Controllers
- `app/Http/Controllers/SitemapController.php` - Sitemap endpoint

## URL

| Endpoint | Description |
|----------|-------------|
| `/sitemap.xml` | Main XML sitemap |

## Included URLs

| Type | Priority | Change Frequency |
|------|----------|-------------------|
| Homepage (`/`) | 1.0 | daily |
| Shop (`/shop`) | 0.9 | daily |
| Products (`/shop/{slug}`) | 0.8 | weekly |
| Categories (`/shop?category=`) | 0.8 | weekly |
| Farm Profiles (`/peternak/{id}`) | 0.7 | weekly |
| Location Pages (`/peternak/{province}`) | 0.6 | weekly |
| Static Pages (`/tentang-kami`, etc.) | 0.7 | monthly |

## Excluded URLs

| Pattern | Reason |
|---------|--------|
| `/admin/*` | Admin area |
| `/staff/*` | Staff area |
| `/peternak/*` (dashboard) | Authenticated |
| `/pelanggan/*` | Customer area |
| `/login`, `/register` | Auth pages |
| `/checkout` | Checkout flow |
| `/api/*` | API routes |
| `/settings/*` | User settings |

## robots.txt

```txt
User-agent: *
Allow: /
Sitemap: https://abodafarm.test/sitemap.xml

Disallow: /admin/
Disallow: /staff/
Disallow: /peternak/
Disallow: /pelanggan/
Disallow: /login
Disallow: /register
Disallow: /checkout
Disallow: /api/
```

## SitemapService Methods

```php
$service = app(SitemapService::class);

// Generate full sitemap XML
$xml = $service->generate();

// Get items count
$count = $service->getItemsCount();

// Get all items as array
$items = $service->getItems();

// Generate sitemap index (for multiple sitemaps)
$index = $service->generateIndex([
    ['loc' => 'https://example.com/sitemap-products.xml', 'lastmod' => '2024-01-01'],
]);
```

## Validation

- XML is well-formed
- No duplicate URLs
- All URLs use absolute paths
- Proper UTF-8 encoding

---

# Error Handling - SEO-Friendly Error Pages

## Files Added

### Services
- `app/Services/ErrorService.php` - Error page data provider

### Views
- `resources/views/errors/404.blade.php` - Custom 404 page
- `resources/views/errors/500.blade.php` - Custom 500 page
- `resources/views/errors/403.blade.php` - Custom 403 page

## Features

### SEO-Friendly
- Proper HTTP 404 status code
- Noindex, nofollow meta tags
- Canonical URL pointing to error page
- Descriptive title and meta description

### Content
- Friendly error messages in Indonesian
- Back to home button
- Search products form
- Popular categories links
- Latest products showcase
- Quick navigation links

### Error Codes Supported
| Code | Page | Theme |
|------|------|-------|
| 404 | Halaman Tidak Ditemukan | Emerald/green |
| 403 | Akses Ditolak | Amber/yellow |
| 500 | Kesalahan Server | Red/orange |
| 419 | Sesi Berakhir | Default |
| 429 | Terlalu Banyak Permintaan | Default |

## ErrorService Methods

```php
$errorService = app(ErrorService::class);

// Get categories for error pages
$categories = $errorService->getCategories($limit = 8);

// Get latest products
$products = $errorService->getLatestProducts($limit = 4);

// Get featured products
$products = $errorService->getFeaturedProducts($limit = 4);

// Get all error page data
$data = $errorService->getErrorPageData(404);
```

## API Error Response

```json
{
    "success": false,
    "message": "Resource not found",
    "error": {
        "code": 404,
        "message": "Halaman yang Anda cari tidak ditemukan."
    }
}
```

### Mobile Friendly
- Responsive design with Tailwind CSS
- Truncates long labels on mobile
- Home icon visible on all screen sizes
- Smooth transition effects

## Usage

### Basic Usage (Auto-detect)
```blade
<x-breadcrumb />
```

### Custom Items
```blade
<x-breadcrumb :items="[
    ['name' => 'Home', 'url' => '/'],
    ['name' => 'Products', 'url' => '/products'],
    ['name' => 'Current Page']
]" />
```

### Disable Schema
```blade
<x-breadcrumb :show-schema="false" />
```

### Disable Icons
```blade
<x-breadcrumb :show-icons="false" />
```

### Helper Functions
```php
// Get breadcrumbs
$items = get_breadcrumbs();

// Get JSON-LD schema
$jsonLd = breadcrumb_jsonld($items);

// Set custom parameter labels
set_breadcrumb_label('province', 'Sulawesi Utara');

// Get service instance
$bc = breadcrumbs();
$bc->addItem('Custom', '/custom');
$bc->setParameterLabel('slug', 'My Product');
```

### Service Methods
```php
$bc = app(BreadcrumbService::class);

// Generate from current route
$items = $bc->generate();

// Generate with custom items
$bc->addItem('Home', '/');
$bc->addItem('Products', '/products');
$bc->addItem('Current');
$items = $bc->getItems();

// Get JSON-LD
$schema = $bc->toJsonLd();
```

## Output Example

**HTML:**
```html
<nav aria-label="Breadcrumb">
    <ol class="breadcrumb-list">
        <li><a href="/">Beranda</a></li>
        <li><i class="fa-chevron-right"></i></li>
        <li><a href="/shop">Toko</a></li>
        <li><i class="fa-chevron-right"></i></li>
        <li><span aria-current="page">Sapi Limousin</span></li>
    </ol>
</nav>
```

**JSON-LD:**
```json
{
    "@context": "https://schema.org",
    "@type": "BreadcrumbList",
    "itemListElement": [
        {"@type": "ListItem", "position": 1, "name": "Beranda", "item": "https://abodafarm.test/"},
        {"@type": "ListItem", "position": 2, "name": "Toko", "item": "https://abodafarm.test/shop"},
        {"@type": "ListItem", "position": 3, "name": "Sapi Limousin"}
    ]
}
```

---

# Dynamic SEO - Unified SEO Service

## Files Updated

### Services
- `app/Services/SeoService.php` - Enhanced with dynamic SEO generation methods

### Helpers
- `app/Helpers/seo.php` - Updated with dynamic SEO helper functions

### Livewire Components
- `app/Livewire/Home.php` - Uses SeoService for homepage SEO
- `app/Livewire/Shop.php` - Uses SeoService for shop/category pages
- `app/Livewire/Shop/ProductDetail.php` - Uses SeoService for product pages

### Layouts
- `resources/views/layouts/guest-home.blade.php` - Updated to support $seo array from SeoService

## Dynamic SEO Methods

### SeoService Methods

| Method | Description |
|--------|-------------|
| `forHomepage()` | Homepage SEO: "AbodaFarm - Organic Livestock Marketplace" |
| `forCategory(Category, count)` | Category page: "{Category} - AbodaFarm" |
| `forProduct(Product)` | Product page: "{Name} \| Rp {Price} \| AbodaFarm" |
| `forFarmer(FarmProfile, count)` | Farmer: "{Farm Name} - Organic Farm \| AbodaFarm" |
| `forShop(search, category)` | Shop/search page with dynamic title |
| `forLocation(location, type, data)` | Location page: "Peternak & Ternak di {Location} \| AbodaFarm" |

## SEO Generation

### Homepage
```
Title: "AbodaFarm - Organic Livestock Marketplace"
Description: Site description from config
Keywords: peternakan organik, pasarternak, sapi, kambing, ayam, Indonesia, AbodaFarm
Image: Default OG image
Schemas: organization, website
```

### Category Page
```
Title: "{Category Name} - AbodaFarm"
Description: "Jual {Category} berkualitas tinggi. Tersedia {count} produk..."
Keywords: {category}, jual {category}, harga {category}, peternakan
Schemas: organization, website
```

### Product Page
```
Title: "{Product Name} | Rp {Price} | AbodaFarm"
Description: ~150 chars from product description, category, weight, origin
Keywords: product name, category, farm name, location
Image: Product image
Type: product
Schemas: organization, website, product
```

### Farmer Profile
```
Title: "{Farm Name} - Organic Farm | AbodaFarm"
Description: Farm description with product count
Keywords: farm name, peternakan organik, province, regency, peternak terpercaya
Image: Farm cover image
Schemas: organization, website, localbusiness
```

## Helper Functions

```php
// Get SEO data for homepage
$seo = seo_homepage();

// Get SEO data for category
$seo = seo_category($category, $productCount);

// Get SEO data for product
$seo = seo_product($product);

// Get SEO data for farmer
$seo = seo_farmer($farmProfile, $productCount);

// Get SEO data for shop
$seo = seo_shop($search, $category);

// Get SEO data for location
$seo = seo_location($location, 'province', ['farmers' => 10, 'products' => 50]);
```

## Usage in Livewire Components

```php
// In render() method
$seoService = app(SeoService::class);
$seo = $seoService->forProduct($this->product);

return view('livewire.shop.product-detail', [
    'seo' => $seo,
]);
```

## Layout Support

The guest-home layout supports multiple SEO data sources:
1. `$seo['title']` - From SeoService (new)
2. `$seoData['title']` - From LocalSeoService (legacy)
3. `$seoTitle` - Individual variables (legacy)

---

# Local SEO - Location Pages

## Files Added

### Services
- `app/Services/LocalSeoService.php` - Local SEO service for location metadata and data

### Livewire Components
- `app/Livewire/Location/ProvincePage.php` - Province location page
- `app/Livewire/Location/RegencyPage.php` - Regency/city location page
- `app/Livewire/Location/CategoryLocationPage.php` - Category + location page

### Views
- `resources/views/livewire/location/province-page.blade.php`
- `resources/views/livewire/location/regency-page.blade.php`
- `resources/views/livewire/location/category-location-page.blade.php`

## Routes

| URL | Description |
|-----|-------------|
| `/peternak/{province}` | Province page (e.g., `/peternak/sulawesi-utara`) |
| `/peternak/{province}/{regency}` | Regency page (e.g., `/peternak/sulawesi-utara/manado`) |
| `/ternak/{location}` | Livestock products by location |
| `/daging/{location}` | Fresh meat by location |
| `/pakan/{location}` | Supplies by location |

## SEO Features

### Each Page Includes
- ✅ Unique title
- ✅ Unique meta description
- ✅ Canonical URL
- ✅ Open Graph tags
- ✅ Breadcrumb navigation
- ✅ LocalBusiness schema
- ✅ Related location links

### LocalBusiness Schema
- Generates schema based on farmer data in the location
- Includes aggregate ratings and review counts
- Supports province, regency, district, and village levels

## Display Sections

### Province Page
- Province statistics (farmers, products, regencies)
- List of regencies/kabupaten
- Nearby farmers
- Featured products
- Latest products
- Related locations (internal linking)

### Regency Page
- Regency statistics
- List of districts/kecamatan
- Nearby farmers
- Featured products
- Related locations

### Category Location Page
- Products filtered by category and location
- Featured products
- Pagination support

## Usage

```php
// Using LocalSeoService
$service = app(LocalSeoService::class);

// Get SEO data for a province
$seo = $service->getProvinceSeo('Sulawesi Utara');

// Get nearby farmers
$farmers = $service->getNearbyFarmers('Sulawesi Utara', 'province', 10);

// Get products by location
$products = $service->getProductsByLocation('Manado', 'regency', 12);

// Generate LocalBusiness schema
$schema = $service->generateLocalBusinessSchema('Sulawesi Utara', 'province');
```

---

# Core Web Vitals Optimization

## Files Added

### Configuration
- `config/performance.php` - Performance optimization settings
- `config/livewire_optimize.php` - Livewire-specific optimizations
- `app/Http/Middleware/PerformanceHeaders.php` - Cache headers middleware
- `app/Console/Commands/OptimizePerformance.php` - CLI optimization command

## Metrics Optimized

### LCP (Largest Contentful Paint)
- ✅ Preload hero images
- ✅ Preload logo
- ✅ Preconnect to external domains
- ✅ DNS prefetch for third-party resources
- ✅ Async font loading with display=swap

### CLS (Cumulative Layout Shift)
- ✅ Reserved dimensions for all images
- ✅ Skeleton placeholders for above-fold content
- ✅ Inline critical CSS
- ✅ Fixed aspect ratios on images

### FCP (First Contentful Paint)
- ✅ Inline critical CSS
- ✅ Preconnected fonts
- ✅ Optimized Vite build with chunking
- ✅ Deferred non-critical scripts

### INP (Interaction to Next Paint)
- ✅ Livewire update debouncing
- ✅ Lazy loaded components
- ✅ Deferred JavaScript initialization

### TTFB (Time to First Byte)
- ✅ Browser caching headers
- ✅ Route caching (production)
- ✅ Config caching (production)
- ✅ ETag support

## Configuration

### Performance Config (`config/performance.php`)
```php
// Preload critical resources
'preload' => [
    'logo' => '/icons/MainLogo.png',
    'font_main' => 'https://fonts.bunny.net/css?family=outfit:400,500,600,700&display=swap',
],

// DNS prefetch domains
'dns_prefetch' => [
    'https://fonts.bunny.net',
    'https://cdnjs.cloudflare.com',
    'https://maps.googleapis.com',
],

// Cache durations (seconds)
'cache' => [
    'immutable' => 31536000, // 1 year
    'images' => 604800,       // 1 week
    'fonts' => 2592000,        // 30 days
],
```

### Vite Optimization (`vite.config.js`)
- Terser minification with console removal
- CSS minification
- Chunk splitting for vendor bundles
- Hashed filenames for cache busting

## CLI Command

```bash
# Run all optimizations
php artisan perf:optimize --all

# Clear caches
php artisan perf:optimize --clear

# Build assets
php artisan perf:optimize --build
```

## Usage Notes

1. **Production Build**: Run `npm run build` for optimized assets
2. **Cache Headers**: Automatically applied via middleware
3. **Font Display**: Uses `display=swap` to prevent FOIT
4. **Lazy Loading**: Google Maps only loads when needed

---

# Image SEO Optimization

## Files Added

### Components
- `resources/views/components/image.blade.php` - Reusable optimized image component

### Helpers
- `app/Helpers/images.php` - Image helper functions

## Features

### Image Component (`<x-image>`)
- **Lazy loading**: Default for below-fold images (`loading="lazy"`)
- **Eager loading**: For hero/above-fold images (`loading="eager"`)
- **Async decoding**: All images use `decoding="async"`
- **CLS Prevention**: Width and height attributes
- **Auto alt text**: Meaningful descriptions based on type/context
- **Fallback support**: Default placeholder for missing images
- **WebP support**: Picture element with WebP sources
- **Responsive sizing**: srcset support

### Auto Alt Text Examples

| Image Type | Generated Alt |
|------------|---------------|
| Product (cow) | "Organic Cow - AbodaFarm" |
| Product (goat) | "Healthy Goat - AbodaFarm" |
| Farm | "Farm Profile - AbodaFarm" |
| Avatar | "Photo - AbodaFarm" |
| Gallery | "Gallery - AbodaFarm" |
| Hero | "Hero Banner - AbodaFarm" |

### Usage Examples

```blade
{{-- Basic image --}}
<x-image src="/images/product.jpg" alt="Sapi Limousin" width="400" height="300" />

{{-- Product with auto alt --}}
<x-image src="/images/cow.jpg" type="product" context="sapi" width="400" height="300" />

{{-- Hero image (eager loading) --}}
<x-image src="/images/hero.jpg" type="hero" width="1600" height="600" loading="eager" />

{{-- Avatar --}}
<x-image src="/images/avatar.jpg" type="avatar" width="64" height="64" />
```

### Files Updated with x-image Component
- `resources/views/livewire/home/hero.blade.php`
- `resources/views/livewire/home/categories.blade.php`
- `resources/views/livewire/home/products.blade.php`
- `resources/views/livewire/shop.blade.php`
- `resources/views/livewire/shop/product-detail.blade.php`

### Helper Functions
- `generate_image_alt()` - Generate meaningful alt text
- `generate_webp_src()` - Generate WebP URL
- `generate_responsive_src()` - Generate responsive image URLs
- `get_image_dimensions()` - Get default dimensions by type
- `image_exists()` - Check if image file exists
- `optimized_image_url()` - Get optimized image URL

---

# SEO Phase 2 - Structured Data & Social Metadata

## Files Added

### New Components
- `resources/views/components/seo/meta.blade.php` - Complete SEO solution with JSON-LD schemas

### New Services
- `app/Services/SeoBuilder.php` - Builder class for generating SEO metadata and JSON-LD structured data

## JSON-LD Structured Data

### WebSite Schema
- Included on all public pages
- Contains: name, url, description, SearchAction

### Organization/LocalBusiness Schema
- Included on public pages (configurable)
- Contains: name, description, url, logo, image, telephone, email, address, geo, opening hours, social links

### Product Schema
- Included when type="product" or productData is passed
- Contains: name, description, image, sku, brand, category, offers (price, currency, availability, seller), aggregateRating, weight

### Article Schema
- Future-ready for blog support
- Contains: headline, description, image, author, publisher, datePublished, dateModified

### Breadcrumb Schema
- Auto-generated when breadcrumbs array is passed
- Contains: itemListElement with position, name, url

## Usage Examples

### Basic Public Page
```blade
<x-seo.meta
    title="About Us"
    description="Learn about AbodaFarm"
/>
```

### Product Page with Full SEO
```blade
<x-seo.meta
    title="{{ $product->name }}"
    description="{{ $product->excerpt }}"
    :image="$product->image_url"
    :url="route('shop.product', $product->slug)"
    type="product"
    :schema="['organization', 'website', 'product']"
    :productData="[
        'name' => $product->name,
        'description' => $product->excerpt,
        'image' => $product->image_url,
        'sku' => $product->sku,
        'price' => $product->price,
        'currency' => 'IDR',
        'in_stock' => $product->stock > 0,
        'seller' => ['name' => 'AbodaFarm']
    ]"
    :breadcrumbs="[
        ['name' => 'Home', 'url' => '/'],
        ['name' => 'Shop', 'url' => '/shop'],
        ['name' => $product->name]
    ]"
/>
```

### Dashboard Page (noindex)
```blade
<x-seo.meta
    title="Admin Dashboard"
    robots="noindex, nofollow"
/>
```

## Configuration Updates

Added to `config/seo.php`:
- `site_phone` - Contact phone for LocalBusiness
- `site_email` - Contact email for LocalBusiness
- `address_locality`, `address_region`, `address_country` - Address components
- `latitude`, `longitude` - Geo coordinates

## Environment Variables (add to .env)
```env
SEO_SITE_PHONE="+62-xxx-xxxx-xxxx"
SEO_SITE_EMAIL="info@abodafarm.test"
SEO_ADDRESS_LOCALITY="Bandung"
SEO_ADDRESS_REGION="Jawa Barat"
SEO_ADDRESS_COUNTRY="ID"
SEO_LATITUDE="-6.9175"
SEO_LONGITUDE="107.6191"
```

---

# SEO Phase 1 Implementation

## Files Added

### Configuration
- `config/seo.php` - SEO configuration with defaults for site name, description, keywords, images, social media, theme color

### Services
- `app/Services/SeoService.php` - SEO service class for generating and managing meta tags
- `app/Providers/SeoServiceProvider.php` - Service provider for registering SEO service and Blade directives

### Helpers
- `app/Helpers/seo.php` - Helper functions for easy SEO tag generation

### Views
- `resources/views/components/seo-meta.blade.php` - Reusable Blade component for SEO meta tags

## Files Modified

### Configuration
- `composer.json` - Added helper file to autoload
- `bootstrap/providers.php` - Registered SeoServiceProvider

### Layouts
- `resources/views/layouts/main.blade.php` - Added SEO meta component with noindex/nofollow for dashboard
- `resources/views/layouts/guest-home.blade.php` - Added SEO meta component with index/follow for public pages
- `resources/views/partials/head.blade.php` - Added SEO meta component for auth pages

## SEO Architecture

### Supported Meta Tags
- Dynamic page title (with site name appended)
- Meta description
- Meta keywords
- Canonical URL (auto-generated from request)
- Open Graph (og:title, og:description, og:image, og:url, og:type, og:locale, og:site_name)
- Twitter Card (twitter:card, twitter:title, twitter:description, twitter:image, twitter:site)
- Robots meta (configurable per page)
- Author meta
- Theme color

### Robots Configuration
- **Public pages** (guest-home layout): `index, follow`
- **Dashboard pages** (main layout): `noindex, nofollow`
- **Auth pages** (auth layouts): `noindex, nofollow`

### Usage Examples

#### Using Blade Component
```blade
<x-seo-meta
    title="Page Title"
    description="Page description"
    image="/images/page.jpg"
    canonical="https://site.com/page"
    type="article"
/>
```

#### Using Helper Functions
```blade
{!! seo_tags(['title' => 'Page', 'description' => 'Desc']) !!}
```

#### Using SEO Service in Livewire
```php
// In Livewire component render method
seo()->title('Product Name')->description($product->excerpt)->image($product->image);
```

## Reusable Components Created

1. **`x-seo.meta`** - Complete SEO component with JSON-LD
2. **`x-seo-meta`** - Legacy SEO meta component
3. **`seo_tags()`** - Helper function for generating SEO tags as HTML string
4. **`seo()`** - SEO service instance
5. **`@seo`, `@seoPublic`, `@seoPrivate`, `@seoTitle`, `@seoDescription`, `@seoImage`** - Blade directives

---

# TODO — Scroll-to-Top Button di Halaman Home

## Steps

- [x]   1. Analisis struktur halaman home & komponen terkait
- [x]   2. Konfirmasi rencana dengan user
- [x]   3. Tambahkan state & scroll listener Alpine.js (showScrollTop) pada root `x-data` di `home.blade.php`
- [x]   4. Tambahkan markup tombol scroll-to-top (fixed, bottom-right) dengan styling emerald & smooth scroll
- [x]   5. Verifikasi hasil rendering
