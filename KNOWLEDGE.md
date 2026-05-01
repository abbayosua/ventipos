# VentiPOS — Knowledge Base

**Last updated:** 2026-05-01 05:15 UTC

---

## 1. Project Overview

Multi-tenant SaaS POS. PHP 8+ custom MVC, MySQL, Bootstrap 5. Indonesian market (IDR base).

### Access
- **URL:** configurable in `config.php` (`app.url`)
- **Dev:** `http://ventipos.test`
- **Demo login:** `admin@ventipos.com` / `admin123`

---

## 2. Directory Map

```
ventipos/
├── public/                          # Document root
│   ├── index.php                    # Entry point. Checks config.php + install.lock → redirects to install.php
│   ├── .htaccess                    # Apache: rewrites everything to public/
│   ├── install.php                  # Web installer (standalone, no framework deps)
│   ├── assets/
│   │   ├── css/style.css            # Global styles + print styles + scanner styles
│   │   ├── css/pos.css              # POS layout + receipt overlay + scanner modal + print
│   │   ├── js/app.js                # Alert dismiss, confirm deletes, barcode scanner, Open Food Facts lookup
│   │   └── js/pos.js                # Cart logic, checkout, receipt overlay, barcode scanner
├── app/
│   ├── Core/
│   │   ├── App.php                  # Bootstrap: init Session → Lang → DB → Request → registerRoutes()
│   │   ├── Router.php              # get(path, handler) / post(path, handler). Params: {id}
│   │   ├── Database.php            # PDO singleton. Methods: query, fetch, fetchAll, insert, update, delete
│   │   ├── Session.php             # get/set/has/remove/flash/destroy
│   │   ├── Request.php             # method(), uri(), get(key), post(key), all()
│   │   ├── Controller.php          # render(view, data, layout), redirect(url), json(data), requireAuth(), etc.
│   │   ├── Model.php               # Base model: all, find, where, create, updateRecord, deleteRecord, companyScope, outletScope
│   │   └── View.php                # render(view, data, layout), renderPartial, renderRaw
│   ├── Controllers/                # 12 controllers (listed in section 6)
│   ├── Models/                     # Extend base Model. Define static $table, $primaryKey
│   ├── Views/
│   │   ├── layouts/
│   │   │   ├── auth.php            # Login/register. CDN: bootstrap css. Has lang switcher + flash alerts
│   │   │   ├── main.php            # Sidebar + topbar. CDN: bootstrap, bootstrap-icons, html5-qrcode, chart.js. Lang switcher
│   │   │   └── pos.php             # Full-width (no sidebar). CDN: bootstrap, bootstrap-icons, html5-qrcode. Lang switcher
│   │   ├── auth/ (login.php, register.php)
│   │   ├── dashboard/ (index.php)
│   │   ├── pos/ (index.php)
│   │   ├── products/ (index.php, form.php)
│   │   ├── categories/ (index.php, form.php)
│   │   ├── customers/ (index.php, form.php)
│   │   ├── suppliers/ (index.php, form.php)
│   │   ├── sales/ (index.php, show.php)
│   │   ├── stock/ (index.php, adjustment.php, movements.php)
│   │   ├── expenses/ (index.php, form.php)
│   │   ├── reports/ (daily.php, top-products.php, profit-loss.php)
│   │   └── settings/ (index.php, outlets.php, users.php)
│   ├── Lang/
│   │   ├── Lang.php                # init(locale), get(key, replace), locale(), setLocale(), available()
│   │   ├── id.php                  # Indonesian (default). 200+ keys organized by module
│   │   └── en.php                  # English. Same key structure
│   └── Helpers/helpers.php         # Global helper functions (see section 7)
├── config.php                      # DB creds, app url, debug mode (gitignored)
├── config.example.php              # Template for config.php
├── database/schema.sql             # Full schema with CREATE TABLE IF NOT EXISTS + indexes
├── seed.php                        # Demo data seeder. Run: php seed.php
├── install.lock                    # Created by install.php on completion (gitignored)
├── KNOWLEDGE.md                    # ← You are here
└── .htaccess                       # RewriteRule ^(.*)$ public/$1 [L]
```

---

## 3. config.php Structure

```php
return [
    'app' => [
        'name' => 'VentiPOS',
        'version' => '1.0.0',
        'debug' => true,           // Shows PHP errors. Set false in production.
        'url' => 'http://ventipos.test',  // Used by baseUrl() helper
    ],
    'database' => [
        'driver' => 'mysql',  'host' => 'localhost',  'port' => 3306,
        'dbname' => 'ventipos',  'username' => 'root',  'password' => '',
        'charset' => 'utf8mb4',
        'options' => [ PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION, ... ],
    ],
    'session' => [ 'lifetime' => 86400 ],
];
```

---

## 4. Session Keys (set on login, used app-wide)

| Key | Set by | Type | Purpose |
|---|---|---|---|
| `user_id` | AuthController | int | Current user |
| `user_name` | AuthController | string | Display name in top bar |
| `user_email` | AuthController | string | For profile |
| `company_id` | AuthController | int | Current tenant |
| `company_name` | AuthController/String | string | Display in top bar + receipt |
| `base_currency` | AuthController | string | e.g. 'IDR'. Fixed after registration |
| `display_currency` | AuthController/switchOutlet | string | e.g. 'IDR', 'USD'. Per-outlet |
| `currency_symbol` | AuthController | string | Deprecated, kept for legacy. Use display_currency rates |
| `role` | AuthController | string | 'owner', 'admin', or 'cashier' |
| `outlet_id` | AuthController/switchOutlet | int | Active outlet |
| `outlet_name` | AuthController/switchOutlet | string | Active outlet name |
| `lang` | AuthController@switchLang | string | 'id' or 'en'. Default 'id' |
| `csrf_token` | csrfField() | string | CSRF protection |
| `_flash` | Session::flash() | array | One-time flash messages |

---

## 5. Database — 16 Tables

### Companies
```sql
id, name, email, phone, address, currency_code, currency_symbol,
base_currency VARCHAR(3) DEFAULT 'IDR',
display_currency VARCHAR(3) DEFAULT 'IDR',
timezone, is_active, created_at, updated_at
```

### Users
```sql
id, name, email UNIQUE, password, is_active, created_at, updated_at
```

### Company User
```sql
id, company_id FK, user_id FK, role ENUM('owner','admin','cashier'), is_active
```

### Outlets
```sql
id, company_id FK, name, code,
display_currency VARCHAR(3) DEFAULT 'IDR',
address, phone, email, is_active, created_at
```

### Categories
```sql
id, company_id FK, name, description, is_active, created_at
```

### Products
```sql
id, company_id FK, category_id FK, name, sku, barcode, description,
cost_price DECIMAL(15,2), selling_price DECIMAL(15,2) (both in base_currency),
tax_rate DECIMAL(5,2), unit, image, is_active, created_at, updated_at
```

### Stock
```sql
id, product_id FK, outlet_id FK, quantity DECIMAL(15,2), updated_at
```

### Stock Movements
```sql
id, product_id FK, outlet_id FK, type ENUM('in','out','adjustment'),
quantity, reference_type, reference_id, notes, created_by, created_at
```

### Customers
```sql
id, company_id FK, name, email, phone, address, tax_number, credit_limit, is_active
```

### Suppliers
```sql
id, company_id FK, name, contact_person, email, phone, address, tax_number, is_active
```

### Sales
```sql
id, outlet_id FK, customer_id FK, invoice_no,
subtotal, discount_type, discount_value, discount_amount,
tax_amount, total DECIMAL(15,2) (all in base currency),
paid_amount, change_amount, payment_status ENUM('paid','partial','unpaid'),
payment_method, notes, created_by, created_at
```

### Sale Items
```sql
id, sale_id FK CASCADE, product_id FK, quantity, price, discount_amount, tax_amount,
subtotal DECIMAL(15,2) (all in base currency)
```

### Payments
```sql
id, sale_id FK CASCADE, method, amount, reference, created_at
```

### Expenses
```sql
id, outlet_id FK, category, amount DECIMAL(15,2) (base currency),
description, expense_date, created_by, created_at
```

### Currency Rates
```sql
id, company_id FK, code VARCHAR(3), symbol VARCHAR(10),
rate DECIMAL(15,6) (1 base_currency = rate target_currency),
is_base TINYINT(1)
```
Example: base=IDR → `USD: rate=0.000062, symbol=$`

### Settings
```sql
id, company_id FK, key VARCHAR(100), value TEXT
```

---

## 6. All Controllers & Methods

### AuthController
```
loginForm()  login()  registerForm()  register()  logout()  switchLang(locale)
```

### DashboardController
```
index()  — today's sales, count, products, customers, low stock, hourly chart
```

### PosController
```
index()              — POS page (products converted to display_currency)
searchProducts()     — AJAX GET /pos/products?q=&category_id=
searchCustomers()    — AJAX GET /pos/customers?q=
checkout()           — POST /pos/checkout. JSON body. Transactional.
```

### ProductController
```
index()  create()  store()  edit(id)  update(id)  destroy(id)
— store/update convert cost_price + selling_price to base_currency
```

### CategoryController
```
index()  create()  store()  edit(id)  update(id)  destroy(id)
```

### CustomerController
```
index()  create()  store()  edit(id)  update(id)  destroy(id)
```

### SupplierController
```
index()  create()  store()  edit(id)  update(id)  destroy(id)
```

### SaleController
```
index()  — sales list with payment status badges
show(id) — sale detail with items, payments
```

### StockController
```
index()            — stock overview with status badges
adjustmentForm()   — stock in/out/set form
adjustmentStore()  — atomic stock update + movement record
movements(id)      — movement history for a product
```

### ExpenseController
```
index()   — date range filter, total
create()  — category dropdown
store()   — amount converted to base_currency
destroy(id)
```

### ReportController
```
daily()               — date picker, summary cards, transaction table
topProducts()         — date range, ranked by qty, revenue, profit
profitLoss()          — net sales, COGS, gross profit, expenses, net profit
```

### SettingController
```
index()        — company profile form + currency rates table (auto-fetches live rates)
update()       — saves company name/email/phone/address only
outlets()      — list + create form
outletStore()  — create outlet with display_currency
switchOutlet() — switch active outlet, updates session display_currency
users()        — list + invite form
userInvite()   — creates or links user with role
fetchRates()   — manual fetch from Frankfurter API
```

---

## 7. Helper Functions

### Global helpers (available in all views)
```php
baseUrl('/path')             → 'http://ventipos.test/path'
assetUrl('/css/file.css')    → 'http://ventipos.test/assets/css/file.css'
__('key', ['param'=>'val'])  → 'Translated string'
formatMoney(15000)           → 'Rp15,000.00'  (base→display)
e('<script>')                → '&lt;script&gt;'  (HTML escape)
old('field_name', 'default') → $_POST value or default
csrfField()                  → '<input type="hidden" name="_csrf_token" value="...">'
flash('key')                 → returns and clears flash message
flash('key', 'value')        → sets flash message
config('app.url')            → reads from config.php
```

### JavaScript globals (set in POS view)
```js
products       // Array of {id, name, sku, barcode, selling_price, tax_rate, unit, stock_qty}
customers      // Array of {id, name, phone, email}
currencySymbol // e.g. 'Rp'
baseUrl        // e.g. 'http://ventipos.test'
langShort      // 'short' translated (for change display)
langThankYou   // 'Thank you!' translated
storeName      // Company name for receipt
storeAddress   // Company address for receipt
```

---

## 8. Route Table (All Registered Routes)

```
GET   /                          → AuthController@loginForm
GET   /login                     → AuthController@loginForm
POST  /login                     → AuthController@login
GET   /logout                    → AuthController@logout
GET   /register                  → AuthController@registerForm
POST  /register                  → AuthController@register
GET   /lang/{locale}             → AuthController@switchLang

GET   /dashboard                 → DashboardController@index

GET   /pos                       → PosController@index
POST  /pos/checkout              → PosController@checkout
GET   /pos/products              → PosController@searchProducts
GET   /pos/customers             → PosController@searchCustomers

GET   /products                  → ProductController@index
GET   /products/create           → ProductController@create
POST  /products/store            → ProductController@store
GET   /products/edit/{id}        → ProductController@edit
POST  /products/update/{id}      → ProductController@update
POST  /products/delete/{id}      → ProductController@destroy

GET   /categories                → CategoryController@index
GET   /categories/create         → CategoryController@create
POST  /categories/store          → CategoryController@store
GET   /categories/edit/{id}      → CategoryController@edit
POST  /categories/update/{id}    → CategoryController@update
POST  /categories/delete/{id}    → CategoryController@destroy

GET   /customers                 → CustomerController@index
GET   /customers/create          → CustomerController@create
POST  /customers/store           → CustomerController@store
GET   /customers/edit/{id}       → CustomerController@edit
POST  /customers/update/{id}     → CustomerController@update
POST  /customers/delete/{id}     → CustomerController@destroy

GET   /suppliers                 → SupplierController@index
GET   /suppliers/create          → SupplierController@create
POST  /suppliers/store           → SupplierController@store
GET   /suppliers/edit/{id}       → SupplierController@edit
POST  /suppliers/update/{id}     → SupplierController@update
POST  /suppliers/delete/{id}     → SupplierController@destroy

GET   /stock                     → StockController@index
GET   /stock/adjustment          → StockController@adjustmentForm
POST  /stock/adjustment          → StockController@adjustmentStore
GET   /stock/movements/{id}      → StockController@movements

GET   /expenses                  → ExpenseController@index
GET   /expenses/create           → ExpenseController@create
POST  /expenses/store            → ExpenseController@store
POST  /expenses/delete/{id}      → ExpenseController@destroy

GET   /sales                     → SaleController@index
GET   /sales/{id}                → SaleController@show

GET   /reports/daily             → ReportController@daily
GET   /reports/top-products      → ReportController@topProducts
GET   /reports/profit-loss       → ReportController@profitLoss

GET   /settings                  → SettingController@index
POST  /settings/update           → SettingController@update
GET   /settings/outlets          → SettingController@outlets
POST  /settings/outlets/store    → SettingController@outletStore
POST  /settings/switch-outlet    → SettingController@switchOutlet
GET   /settings/users            → SettingController@users
POST  /settings/users/invite     → SettingController@userInvite
POST  /settings/fetch-rates      → SettingController@fetchRates
```

---

## 9. Flash Messages & View Variables

### Flash keys used
```php
flash('success')  // Green alert
flash('error')    // Red alert
```

### View variable naming convention
Controllers pass a `$title` for the `<title>` tag + layout. CRUD list views pass `$search` for search input value. CRUD form views pass the model object (null for create). Example:
```php
$this->render('products.index', ['title' => __('products.title'), 'products' => $products, 'categories' => $categories, 'search' => $search]);
$this->render('products.form', ['title' => 'New Product', 'product' => null, 'categories' => $categories]);
$this->render('products.form', ['title' => 'Edit Product', 'product' => $product, 'categories' => $categories]);
```

---

## 10. Currency System — Complete Logic

### Storage
- All monetary values stored in `base_currency` (default: IDR)
- Products: `cost_price`, `selling_price` in IDR
- Sales/sale_items: `total`, `subtotal`, `price`, etc. in IDR
- Expenses: `amount` in IDR
- Customers: `credit_limit` in IDR

### Input conversion (display → base)
Called in ProductController, ExpenseController, PosController:
```php
$this->toBaseCurrency(15000)  // If display=IDR, returns 15000
$this->toBaseCurrency(1)      // If display=USD, returns 1/0.000062 ≈ 16129
```

### Display conversion (base → display)
Called by formatMoney():
```php
formatMoney(15000)  // If display=IDR → 'Rp15,000'
formatMoney(15000)  // If display=USD → '$0.93'  (15000 * 0.000062)
```

### Rate lookup
```sql
SELECT rate, symbol FROM currency_rates WHERE company_id = ? AND code = ?
```
Rates cached per request via static variable in `formatMoney()`.

### API
Frankfurter: `GET https://api.frankfurter.app/latest?from={base_currency}`
Returns rates relative to base. Auto-fetched on settings page load. Manual button also available.

---

## 11. Barcode Scanner System

### Library: `html5-qrcode` (jsdelivr CDN)
- Loaded in `layouts/main.php` and `layouts/pos.php`
- Fallback: dynamic `loadScript()` if CDN fails

### Scanner modal HTML (duplicated in pos/index.php and products/form.php)
```html
<div id="scannerOverlay" class="scanner-overlay d-none">
  <div class="scanner-modal">
    <div class="scanner-modal-header">
      <span>Barcode Scanner</span>
      <button type="button" class="btn-close btn-close-white" onclick="closeBarcodeScanner()"></button>
    </div>
    <div class="scanner-modal-body">
      <div id="scannerContainer"></div>
      <div id="scannerResult" class="scanner-result d-none"></div>
    </div>
    <div class="scanner-modal-footer">
      <button class="btn btn-secondary btn-sm w-100" onclick="closeBarcodeScanner()">Cancel</button>
    </div>
  </div>
</div>
```

### JS Functions
```js
openBarcodeScanner(mode)   // mode='pos' or 'product'. Starts camera, uses rear cam by default
closeBarcodeScanner()      // Stops camera, hides overlay
handleBarcode(code)        // POS: looks up in products → addToCart or Open Food Facts
lookupBarcode()            // Product form: fetches Open Food Facts, fills name + category
lookupBarcodeAPI(code, cb) // Generic fetch to Open Food Facts
```

### Open Food Facts
```
GET https://world.openfoodfacts.org/api/v0/product/{barcode}.json
Response: { status: 1, product: { product_name, categories_tags, ... } }
```

---

## 12. Multilingual System

### Adding new keys
Edit `app/Lang/id.php` and `app/Lang/en.php`. Both files must have the same key structure.

Key naming convention: `module.key_name` (e.g., `products.title`, `common.save`).

### Adding new language
1. Create `app/Lang/fr.php` with same structure as `id.php`
2. Add `'fr' => 'Français'` to `Lang::available()`
3. Language button auto-appears in all layouts

---

## 13. CSS/JS Conventions

### CSS classes available app-wide
- `.sidebar` — dark sidebar navigation
- `.product-btn` — POS product card with hover effect
- `.pos-container`, `.pos-products`, `.pos-cart` — POS layout
- `.cart-item` — cart line item
- `.qty-btn` — quick amount button
- `.receipt-overlay`, `.receipt-modal` — success receipt overlay
- `.scanner-overlay`, `.scanner-modal` — barcode scanner camera overlay
- `.check-ok`, `.check-fail` — installer requirement checks

### CDN dependencies
```html
Bootstrap 5 CSS:    https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css
Bootstrap Icons:    https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css
Bootstrap 5 JS:     https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js
Chart.js:           https://cdn.jsdelivr.net/npm/chart.js@4.4.7/dist/chart.umd.min.js
html5-qrcode:       https://cdn.jsdelivr.net/npm/html5-qrcode/dist/html5-qrcode.min.js
```

---

## 14. Print System

### POS receipt print
- 80mm thermal format
- Hidden `#printReceipt` div populated on print
- Store name centered top, ASCII `=` and `-` separators
- Monospace font
- Print CSS hides all UI, shows receipt at 80mm width
- `@page { margin: 0; size: 80mm auto; }`

### Sale detail print
- Hides sidebar, navbar, buttons, forms
- Content takes full width (no margin-left)

---

## 15. Installer Flow

### Session keys (used by installer)
```php
$_SESSION['install']['db_host']
$_SESSION['install']['db_port']
$_SESSION['install']['db_name']
$_SESSION['install']['db_user']
$_SESSION['install']['db_pass']
$_SESSION['install']['company_name']
$_SESSION['install']['admin_name']
$_SESSION['install']['admin_email']
$_SESSION['install']['admin_password']
$_SESSION['install']['admin_done']
$_SESSION['install']['seed_done']
$_SESSION['install']['seed_counts']
$_SESSION['install']['_company_id']
$_SESSION['install']['_outlet_id']
```

### Steps
0. Requirements → 1. DB config → 2. Migration → 3. Admin → 4. Seed data → 5. Done

---

## 16. Naming Conventions

| What | Convention | Example |
|---|---|---|
| Routes | `/resource/action` | `/products/create`, `/stock/adjustment` |
| Controller methods | camelCase | `create()`, `store()`, `edit($id)` |
| View files | kebab-case | `top-products.php`, `profit-loss.php` |
| JS functions | camelCase | `addToCart()`, `clearFilters()`, `completeSale()` |
| CSS classes | kebab-case | `.product-btn`, `.receipt-overlay`, `.scanner-modal` |
| Translation keys | module.key_name | `products.title`, `common.save`, `pos.complete_sale` |
| DB columns | snake_case | `base_currency`, `display_currency`, `selling_price` |
| Session keys | snake_case | `company_id`, `outlet_name`, `display_currency` |
| Form input names | snake_case | `cost_price`, `selling_price`, `payment_method` |

---

## 17. Common CRUD Pattern

Every CRUD module follows this exact pattern:

```
Controller:
  index()   → fetch all (with search), render list view
  create()  → render form view with null model
  store()   → validate, insert, flash+redirect
  edit($id) → fetch by id, render form view with model
  update($id) → validate, update, flash+redirect
  destroy($id) → soft delete (is_active=0), flash+redirect

Views:
  module/index.php   — table with search, actions column
  module/form.php    — create/edit form, uses $model ?? null to switch title/save URL
```

---

## 18. Key Gotchas

1. **CSRF**: Every POST form needs `<?= csrfField() ?>`
2. **Soft delete**: `is_active = 0`, not `DELETE FROM`. All queries filter `WHERE is_active = 1`
3. **Base currency**: Input prices always converted via `toBaseCurrency()`. Display always converted via `formatMoney()`
4. **Outlet switching**: Updates session `outlet_id`, `outlet_name`, `display_currency`. Routes reload with new context
5. **POS JS**: `products` and `customers` arrays are PHP-encoded JSON. Prices in products array are ALREADY converted to display currency by PosController
6. **Translation fallback**: If key not found, returns the key string itself
7. **Print**: Uses `@media print` CSS, not a new window. The `#printReceipt` div is hidden until print is triggered
8. **Session lifetime**: `config('session.lifetime')` in seconds (default 86400 = 24h)
9. **Installer**: Standalone PHP file (no framework). Uses its own PDO connection, not `Database.php`
10. **Demo seed**: `seed.php` creates historical sales for 60 days with random timestamps
