# POS System — Deployment Guide

## Environment Setup

### Requirements
| Component | Minimum |
|-----------|---------|
| PHP       | 8.0+    |
| MySQL     | 8.0+ / MariaDB 10.5+ |
| Perfex CRM | 2.9+  |
| HTTPS     | Required for M-Pesa, Telebirr callbacks |

### 1. Copy Module
```bash
# From your deployment package
cp -r pos_system /var/www/html/perfex_crm/modules/
```

### 2. Activate Module
1. Log into Perfex CRM Admin → **Setup → Modules**
2. Find **POS System** → click **Activate**
3. All 15+ database tables are created automatically via `install.php`

### 3. Environment Variables
Add these to `application/config/app-config.php`:
```php
define('POS_ENV',           'production');          // or 'sandbox'
define('POS_JWT_TTL',       '12');                  // Token TTL hours
define('POS_RATE_LIMIT',    '120');                 // Requests per minute
define('POS_CACHE_DIR',     APPPATH . 'cache/');    // Writable by web server
```

### 4. Mobile Money Configuration
Navigate to **POS → Settings → Payment Methods** and configure each provider:

#### M-Pesa (Kenya)
```json
{
  "consumer_key":        "your_consumer_key",
  "consumer_secret":     "your_consumer_secret",
  "business_short_code": "174379",
  "passkey":             "your_lipa_na_mpesa_passkey",
  "callback_url":        "https://yourdomain.com/pos_system/api/payments/callback/mpesa",
  "environment":         "production"
}
```

#### Airtel Money
```json
{
  "client_id":     "your_client_id",
  "client_secret": "your_client_secret",
  "country":       "KE",
  "currency":      "KES"
}
```

#### MTN Mobile Money (Uganda/Rwanda)
```json
{
  "api_key":          "your_api_key",
  "subscription_key": "your_subscription_key",
  "environment":      "production",
  "currency":         "UGX"
}
```

#### Telebirr (Ethiopia)
```json
{
  "app_id":              "your_app_id",
  "app_key":             "your_app_key",
  "short_code":          "your_short_code",
  "merchant_private_key": "-----BEGIN PRIVATE KEY-----\n...",
  "telebirr_public_key": "-----BEGIN PUBLIC KEY-----\n...",
  "notify_url":          "https://yourdomain.com/pos_system/api/payments/callback/telebirr"
}
```

### 5. Assign Staff to Branches
1. Go to **POS → Branches**
2. Click a branch → **Assign Staff**
3. Set role: `cashier` / `supervisor` / `manager` / `admin`
4. Check **Default Branch** for each staff member

### 6. Web Server Configuration

#### Nginx
```nginx
location /pos_system/api {
    try_files $uri $uri/ /index.php?$query_string;
    
    # CORS handled by PHP — do not duplicate here
    
    # Rate limiting (additional layer)
    limit_req zone=pos_api burst=50 nodelay;
}

# Protect test files
location ~* /modules/pos_system/tests {
    deny all;
}
```

#### Apache (.htaccess additions)
```apache
# Protect tests directory
<DirectoryMatch "modules/pos_system/tests">
    Require all denied
</DirectoryMatch>
```

### 7. File Permissions
```bash
# Cache directory must be writable
chmod 775 application/cache/
chown www-data:www-data application/cache/

# Uploads
chmod 775 modules/pos_system/uploads/ 2>/dev/null || true
```

### 8. MySQL Optimisation
Run after installation:
```sql
-- Analyse table statistics for the query planner
ANALYZE TABLE tblpos_sales, tblpos_sale_items, tblpos_inventory, tblpos_payments;

-- Optional: partition large sales tables by year (for >1M rows)
-- ALTER TABLE tblpos_sales PARTITION BY RANGE (YEAR(date_created)) ( ... );
```

### 9. Running Tests
```bash
# Install dev dependencies
composer require --dev phpunit/phpunit guzzlehttp/guzzle

# Unit tests (no server needed)
vendor/bin/phpunit modules/pos_system/tests/unit/

# API integration tests (server must be running)
POS_API_URL=http://localhost/perfex_crm/pos_system/api \
POS_EMAIL=admin@example.com \
POS_PASSWORD=yourpassword \
POS_BRANCH_ID=1 \
vendor/bin/phpunit modules/pos_system/tests/api/
```

### 10. Vue SPA Production Build
The terminal currently loads Vue 3 via CDN. For production:
```bash
# Install build tools
npm install -D vite @vitejs/plugin-vue

# Build
npx vite build --outDir modules/pos_system/assets/dist

# Reference the built bundle in terminal.php instead of CDN scripts
```

---

## Permission Matrix

| Feature                    | Cashier | Supervisor | Manager | Admin |
|----------------------------|:-------:|:----------:|:-------:|:-----:|
| Open/close session         | ✓       | ✓          | ✓       | ✓     |
| Create sale                | ✓       | ✓          | ✓       | ✓     |
| Apply discount             | ✓       | ✓          | ✓       | ✓     |
| Refund / void              | –       | ✓          | ✓       | ✓     |
| View reports               | –       | ✓          | ✓       | ✓     |
| Stock adjustment           | –       | ✓          | ✓       | ✓     |
| View P&L / Tax report      | –       | –          | ✓       | ✓     |
| Create/edit products       | –       | –          | ✓       | ✓     |
| Stock transfer             | –       | –          | ✓       | ✓     |
| Branch management          | –       | –          | –       | ✓     |
| Payment method config      | –       | –          | –       | ✓     |
| View all branches          | –       | –          | –       | ✓     |
| General ledger             | –       | –          | –       | ✓     |

---

## API Quick Reference

**Base URL:** `POST /pos_system/api/auth/login` → get Bearer token

| Method | Endpoint                              | Role    | Description            |
|--------|---------------------------------------|---------|------------------------|
| POST   | `/auth/login`                         | –       | Get token              |
| GET    | `/auth/me`                            | any     | Current user           |
| GET    | `/products/pos`                       | any     | POS catalog + stock    |
| GET    | `/products/barcode/:code`             | any     | Barcode lookup         |
| POST   | `/sessions/open`                      | any     | Open session           |
| POST   | `/sessions/:id/close`                 | any     | Close session          |
| POST   | `/sessions/:id/cash-in`               | any     | Float in               |
| POST   | `/sales/create`                       | any     | New sale               |
| POST   | `/sales/sync`                         | any     | Offline batch sync     |
| POST   | `/sales/:id/refund`                   | super   | Refund                 |
| GET    | `/customers/search?q=...`             | any     | Customer autocomplete  |
| POST   | `/payments/mobile-money`              | any     | Initiate MM push       |
| POST   | `/inventory/adjust`                   | super   | Stock adjustment       |
| POST   | `/inventory/transfer`                 | manager | Branch transfer        |
| GET    | `/reports/daily-sales`                | super   | Daily totals           |
| GET    | `/reports/profit-loss`                | manager | P&L                    |
| GET    | `/reports/tax`                        | manager | VAT report             |
| GET    | `/reports/branches`                   | admin   | All branches           |

---

## Offline Mode

The SPA uses **IndexedDB** (`pos_offline_db`) to:
1. Cache the product catalog (30-min TTL)
2. Queue sales when offline (keyed by `sale_uid`)
3. Auto-sync every 2 minutes on reconnect
4. Deduplicate via `sale_uid` — safe to sync same sale twice

The offline queue persists across browser refresh and device restart.

---

## Monitoring & Alerts

Low stock alerts: `GET /inventory/low-stock?threshold=5`  
Expiring stock: `GET /inventory/expiring?days=14`  
Activity logs: stored in `tblpos_activity_logs` — query for suspicious patterns

---

*Generated by Claude Code — POS System v1.0.0*
