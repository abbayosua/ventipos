# VentiPOS — Knowledge Base

**Last updated:** 2026-05-01 05:00 UTC

---

## Overview

VentiPOS is a multi-tenant SaaS Point of Sale system built with PHP (custom MVC) and MySQL. It supports multiple companies, outlets, and users with role-based access.

---

## Architecture

### Directory Structure

```
ventipos/
├── public/                    # Web root
│   ├── index.php             # Front controller
│   ├── .htaccess             # URL rewriting
│   ├── install.php           # Web-based installer wizard
│   ├── assets/css/           # style.css, pos.css
│   └── assets/js/            # app.js, pos.js
├── app/
│   ├── Core/                 # Framework: Router, Database, Session, Request, Controller, Model, View, App
│   ├── Controllers/          # Auth, Dashboard, POS, Products, Categories, Customers, Suppliers, Sales, Stock, Expenses, Reports, Settings
│   ├── Models/               # Base model (extendable per-table)
│   ├── Views/                # 15 directories, 28+ view files
│   ├── Lang/                 # Lang class + id.php (ID), en.php (EN)
│   └── Helpers/helpers.php   # __(), formatMoney(), baseUrl(), csrfField(), etc.
├── config.php                # App + DB configuration (gitignored)
├── config.example.php        # Example config (committed)
├── database/schema.sql       # Full schema (16 tables)
├── seed.php                  # Demo data seeder (38 products, 10 customers, 60 days sales)
├── install.lock              # Created by installer after completion (gitignored)
└── .htaccess                 # Rewrites everything to public/
```

### Multi-Tenant Design

| Concept | Implementation |
|---|---|
| **Tenant** | `companies` table — each registration creates a new company |
| **Data isolation** | Every table has `company_id` column |
| **User membership** | `company_user` pivot table with role (owner/admin/cashier) |
| **Outlets** | Each company has multiple outlets/stores, each with their own display currency |
| **Registration** | Creates company + admin user + main outlet + base currency |

---

## Core Framework

| Class | File | Purpose |
|---|---|---|
| `App` | `app/Core/App.php` | Bootstrap: init Session, DB, Lang, Request, register routes |
| `Router` | `app/Core/Router.php` | GET/POST route registration with `{param}` extraction |
| `Database` | `app/Core/Database.php` | PDO singleton with query/fetch/insert/update/delete helpers |
| `Session` | `app/Core/Session.php` | Session start/get/set/flash/destroy |
| `Request` | `app/Core/Request.php` | `$_GET`/`$_POST` wrapper with URI parsing |
| `Controller` | `app/Core/Controller.php` | Base: render/redirect/json + auth checks + currency helpers |
| `Model` | `app/Core/Model.php` | Base CRUD with company/outlet scoping |
| `View` | `app/Core/View.php` | PHP template renderer with layout system |

### Routes

All routes are defined in `App.php::registerRoutes()`. Pattern:
```php
$this->router->get('/products', 'ProductController@index');
$this->router->post('/products/store', 'ProductController@store');
$this->router->get('/products/edit/{id}', 'ProductController@edit');
```

---

## Database — 16 Tables

| Table | Scope | Key Columns |
|---|---|---|
| `companies` | Global | `base_currency`, `display_currency`, `currency_code`, `currency_symbol` |
| `users` | Global | `email` (unique), `password` |
| `company_user` | Pivot | `company_id`, `user_id`, `role` |
| `outlets` | Per-company | `display_currency` (per-outlet) |
| `categories` | Per-company | `name`, `description` |
| `products` | Per-company | `cost_price`, `selling_price` (in base currency), `tax_rate`, `barcode`, `sku` |
| `stock` | Per-outlet | `product_id`, `outlet_id`, `quantity` |
| `stock_movements` | Per-outlet | `type` (in/out/adjustment), `reference_type`, `reference_id` |
| `customers` | Per-company | Shared across outlets |
| `suppliers` | Per-company | `contact_person` |
| `sales` | Per-outlet | `total` (in base currency), `payment_status`, `payment_method` |
| `sale_items` | Per-sale | `price`, `subtotal` (in base currency), `discount_amount`, `tax_amount` |
| `payments` | Per-sale | `method`, `amount` |
| `expenses` | Per-outlet | `category`, `amount` (in base currency) |
| `currency_rates` | Per-company | `code`, `symbol`, `rate` (relative to base), `is_base` |
| `settings` | Per-company | Key-value store |

---

## Currency System

### Design

- **Base currency** — set once per company during registration (default: IDR). Never changed.
- **Display currency** — per-outlet setting, freely switchable. Default: IDR.
- All monetary values stored in **base currency** in the database.
- All displays convert from base → display using `currency_rates`.

### Key methods

| Method | Location | Purpose |
|---|---|---|
| `formatMoney()` | `helpers.php` | Converts base amount → display using rate |
| `toBaseCurrency()` | `Controller.php` | Converts user input (display) → base for storage |
| `toDisplayCurrency()` | `Controller.php` | Converts stored base → display for output |

### Rate format

`currency_rates.rate` = `1 base_currency = rate target_currency`
- If base=IDR, USD rate = 0.000062 (1 IDR = 0.000062 USD)
- Fetch from Frankfurter API: `GET https://api.frankfurter.app/latest?from={base}`

### Usage in controllers

- **ProductController** — `cost_price` and `selling_price` converted to base on create/edit
- **PosController** — `paid_amount` and `discount_value` converted to base at checkout. Product prices converted to display for POS grid.
- **ExpenseController** — `amount` converted to base on create

---

## Authentication

### Flow

1. User registers → creates `users` + `companies` + `company_user` + `outlets` + `currency_rates`
2. User logs in → session stores: `user_id`, `company_id`, `outlet_id`, `role`, `base_currency`, `display_currency`
3. All controllers check `$this->requireAuth()` and `$this->requireCompany()`

### Roles

| Role | Settings | Users | Outlets | POS | Reports |
|---|---|---|---|---|---|
| Owner | ✅ | ✅ | ✅ | ✅ | ✅ |
| Admin | ❌ | ✅ | ❌ | ✅ | ✅ |
| Cashier | ❌ | ❌ | ❌ | ✅ | ✅ |

---

## POS Screen

### Location: `/pos`
### Layout: `views/layouts/pos.php` (full-width, no sidebar)

### Features

- Product grid with category filter + search
- Barcode scanning (physical scanner + camera via `html5-qrcode`)
- Cart with item-level discounts (% or fixed)
- Order-level discount (% or fixed)
- Quick amount buttons (500, 1k, 2k, 5k, 10k, 20k, 50k, 100k, Exact)
- Live change calculation
- Receipt overlay after successful transaction
- Print receipt (80mm thermal format)
- Customer selection per transaction
- Payment methods: Cash, Card, Bank Transfer, Other

### Checkout Logic (`PosController@checkout`)

1. Validates stock availability
2. Calculates item discounts, tax, totals (all in base currency)
3. Generates invoice number: `INV-YYYYMMDD-NNNN`
4. Inserts `sales` + `sale_items` records
5. Deducts stock atomically: `UPDATE stock SET quantity = quantity - ?`
6. Records `stock_movements` audit trail
7. Records payment in `payments` table
8. All wrapped in a DB transaction

---

## Multilingual System

### Location: `app/Lang/`

| File | Language | Status |
|---|---|---|
| `id.php` | Indonesian | **Default** (200+ keys) |
| `en.php` | English | Complete (200+ keys) |

### Usage

```php
__('common.save')                  // "Simpan" or "Save"
__('auth.login_title', ['app' => 'VentiPOS'])  // "Masuk ke VentiPOS"
__('stock.movements_for', ['name' => $product->name])
```

### Adding a new language

1. Create `app/Lang/fr.php` with the same key structure
2. Add to `Lang::available()` — it auto-appears in the switcher

### Language switcher

- Login page: top-right buttons
- Main layout: top bar buttons
- POS layout: top bar text links
- Route: `/lang/{locale}` sets session, redirects back

---

## Barcode Scanner

### Library: `html5-qrcode` (via jsdelivr CDN)

### Locations

| Page | Button | Behavior |
|---|---|---|
| **POS** | 📷 next to barcode input | Scans → adds to cart if product found, else Open Food Facts lookup |
| **Product form** | 📷 next to barcode field | Scans → fills barcode field → auto-lookup name/category from Open Food Facts |

### Open Food Facts API

- Free, no API key
- Endpoint: `GET https://world.openfoodfacts.org/api/v0/product/{barcode}.json`
- Falls back gracefully if product not found

---

## Reports

| Report | Route | Features |
|---|---|---|
| **Daily** | `/reports/daily` | Date picker, sales count, total, tax, expenses, transaction list |
| **Top Products** | `/reports/top-products` | Date range, qty sold, revenue, profit, ranked |
| **Profit & Loss** | `/reports/profit-loss` | Date range, gross/net sales, COGS, expenses, net profit, margin |

---

## Installer

### Location: `public/install.php`

### Flow

1. **Requirements check** — PHP 8.0+, PDO, MySQL, JSON, MBString, writable permissions
2. **Database config** — host, port, dbname, user, password
3. **Migration** — creates 16 tables, writes `config.php`
4. **Admin account** — creates company + owner user + main outlet + USD currency
5. **Demo data** — optional seed (18 products, 5 customers, 8 categories, 2 currencies)
6. **Complete** — writes `install.lock`, redirects to login

### Auto-redirect

`public/index.php` checks for `config.php` and `install.lock`. If missing, redirects to `/install.php`.

---

## Installation

```bash
1. Upload files to server
2. Copy config.example.php → config.php
3. Visit /install.php in browser
4. Follow the wizard
5. Login with created admin account
```

### For demo data (manual):
```bash
php seed.php
```

---

## Key Design Decisions

| Decision | Rationale |
|---|---|
| **Custom MVC (no framework)** | Lightweight, no vendor bloat, full control |
| **Single DB + tenant_id** | Simple, cost-effective, easy cross-tenant reports |
| **MPA (Multi-Page Application)** | Server-rendered PHP pages with JS enhancements |
| **Bootstrap 5** | Responsive, well-known, ready components |
| **Base currency (fixed)** | All values stored in one currency, converted on display |
| **Per-outlet display currency** | Different stores in different countries |
| **Open Food Facts fallback** | Free barcode lookup without paid API keys |
| **Web installer** | No command-line access needed for deployment |
