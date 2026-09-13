# SMM Panel - Master System Architecture & Technical Documentation

---

## 1. System Overview & Architecture

This project is an enterprise-grade **Social Media Marketing (SMM) Panel** platform. It features:
- **Laravel 11 (PHP 8.2+)** core backend handling Web User Portal, Admin Dashboard, Order Processing Engine, Automated Payments, and REST APIs.
- **Native Flutter Mobile App** (Dart 3.x) for Android & iOS providing a 60 FPS mobile user experience.
- **Reseller SMM API (v1 & v2)** compatible with standard SMM panel formats.
- **Automated UPI Payment Engine** with real-time UTR ingestion and verification.
- **Automation Integration APIs** (e.g. n8n workflow automated blog post creation).
- **Multi-Provider Failover & Auto Refund System** for seamless order execution.

```
+-----------------------------------------------------------------------------------+
|                                 CLIENT LAYER                                      |
|  +---------------------------+        +----------------------------------------+  |
|  | Native Flutter App (v1)   |        | Web Browser UI (Blade + JS)            |  |
|  | (Android & iOS Release APK)|        | (User Portal & Admin Dashboard)        |  |
|  +-------------+-------------+        +-------------------+--------------------+  |
+----------------|------------------------------------------|-----------------------+
                 | JSON REST API (/api/v1/*)                | Session HTTP / CSRF
                 v                                          v
+-----------------------------------------------------------------------------------+
|                             LARAVEL 11 CORE BACKEND                               |
|  +-----------------------------+ +---------------------+ +----------------------+  |
|  | AppApiController (Mobile)   | | OrderController     | | AdminController      |  |
|  +--------------+--------------+ +----------+----------+ +----------+-----------+  |
|                 |                           |                       |             |
|  +--------------v---------------------------v-----------------------v----------+  |
|  |                           SERVICE / BUSINESS LAYER                          |  |
|  | OrderProcessor | RefundService | ScratchCardService | ReferralService etc. |  |
|  +------------------------------------------+----------------------------------+  |
+---------------------------------------------|-------------------------------------+
                                              | Eloquent ORM
                                              v
+-----------------------------------------------------------------------------------+
|                        DATABASE & EXTERNAL INTEGRATIONS                           |
|  +---------------------+   +---------------------+   +-------------------------+  |
|  | MySQL 8.0 / MariaDB |   | SMM Provider APIs   |   | UPI Bank Payment Engine |  |
|  +---------------------+   +---------------------+   +-------------------------+  |
+-----------------------------------------------------------------------------------+
```

---

## 2. Technology Stack

- **Backend Framework**: Laravel 11.x (PHP 8.2+)
- **Database**: MySQL 8.0 / MariaDB (XAMPP / Production)
- **Frontend Engine**: Laravel Blade, Vanilla CSS (Glassmorphism & Neon Dark Theme), Alpine.js / Vanilla JS
- **Mobile Framework**: Native Flutter (Dart 3.x) with Provider State Management & Material 3 UI
- **Authentication**: Laravel Web Sessions + Dual Token/API-Key Authentication (`api_key`) + Google OAuth 2.0
- **Security Protocols**: CSRF protection, reCAPTCHA (v2, v3, Enterprise), CSP Headers, Row-Level Lock (`lockForUpdate()`), Idempotency Token Validation
- **High-Precision Currency**: `format_currency()` helper (formats amounts `< ₹0.01` up to 4 decimals e.g., `₹0.008`, `₹0.0008`)

---

## 3. Directory Structure

```
insta_star/
├── app/
│   ├── Console/
│   │   └── Commands/              # Artisan CLI commands (cron status sync, balance reminders)
│   ├── Helpers/
│   │   └── helpers.php            # Global helper functions (format_currency)
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── AdminController.php# Admin management & system control
│   │   │   ├── Api/
│   │   │   │   └── AppApiController.php # Mobile App REST API controller
│   │   │   ├── ApiBlogController.php   # Automated n8n blog posting API
│   │   │   ├── AuthController.php      # Auth, Google OAuth & Profile management
│   │   │   ├── BlogController.php      # Web Blog frontend
│   │   │   ├── FeedbackController.php  # User feedback & admin notifications
│   │   │   ├── LandingController.php   # Public pages, services & referrals
│   │   │   ├── LicenseController.php   # System license activation
│   │   │   ├── OrderController.php     # Web order placement & status
│   │   │   ├── PaymentController.php   # Add funds, UTR submission & webhooks
│   │   │   ├── ResellerApiController.php # Reseller SMM API dispatcher
│   │   │   ├── ScratchCardController.php # Daily reward scratch cards
│   │   │   └── TicketController.php    # Customer support ticket portal
│   │   └── Middleware/
│   │       ├── AdminMiddleware.php           # Role-based access control
│   │       ├── SecurityHeadersMiddleware.php # CSP & Security headers
│   │       └── VerifyProjectLicense.php    # System license protection
│   ├── Models/                    # Eloquent Database Models (22 models)
│   └── Services/                  # Business Logic Services (12 services)
├── bootstrap/
│   └── app.php                    # Middleware configuration & CSRF exceptions
├── config/                        # Application configuration files
├── database/
│   ├── migrations/                # 34 Database migration files
│   └── seeders/                   # Initial database seeders
├── flutter_app/                   # Complete Native Flutter Mobile Application
├── public/                        # Public web assets, index.php & PWA files
├── resources/
│   ├── views/
│   │   ├── admin/                 # Admin Blade templates
│   │   ├── auth/                  # Authentication Blade templates
│   │   ├── emails/                # Email notification templates
│   │   ├── errors/                # Custom error pages (404, 500, 419)
│   │   ├── user/                  # User Dashboard Blade templates
│   │   └── index.blade.php        # Public Landing Page
├── routes/
│   ├── api.php                    # Mobile App REST API routes
│   ├── console.php                # Scheduled artisan commands
│   └── web.php                    # Web Portal, Admin & Reseller API routes
├── scratch/                       # Test & diagnostic suite scripts
├── documentation.md               # Master system technical documentation
└── completed-tasks.md             # Completed features & task log
```

---

## 4. Database Schema & Eloquent Models

The database contains 34 migration tables powering 22 core Eloquent models:

### 4.1 `User` (`users`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Unique user identifier |
| `name` | String | Full name |
| `email` | String (Unique) | Account email address |
| `password` | String | Hashed password |
| `balance` | Decimal(12, 4) | Current wallet balance in INR (₹) |
| `role` | Enum('user', 'admin') | Access role |
| `status` | Enum('active', 'suspended') | Account status |
| `api_key` | String(64) | Unique API key for mobile app & reseller API |
| `google_id` | String (Nullable) | Google OAuth unique ID |
| `whatsapp` | String (Nullable) | Contact WhatsApp number |
| `otp_code` | String (Nullable) | 2FA verification code |
| `otp_expires_at` | Timestamp (Nullable) | OTP expiration timestamp |

### 4.2 `Category` (`categories`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Category ID |
| `name` | String | Category title |
| `custom_name` | String (Nullable) | Custom display override title |
| `icon` | String (Nullable) | Icon identifier / CSS class |
| `sort_order` | Integer | Sorting position |
| `is_pinned` | Boolean | Whether pinned to top |
| `status` | Enum('active', 'inactive') | Visibility status |

### 4.3 `Service` (`services`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Service ID |
| `category_id` | Foreign Key | Link to `categories.id` |
| `provider_id` | Foreign Key (Nullable) | External SMM provider link (`providers.id`) |
| `provider_service_id` | String (Nullable) | Service ID on provider panel |
| `name` | String | Original service name |
| `custom_name` | String (Nullable) | Custom override name |
| `price_per_k` | Decimal(10, 4) | Price per 1,000 items |
| `min_quantity` | BigInt | Minimum allowed order quantity |
| `max_quantity` | BigInt | Maximum allowed order quantity |
| `service_type` | Enum('default', 'custom_comments') | Service type |
| `description` | Text (Nullable) | Service guidelines & description |
| `status` | Enum('active', 'inactive') | Display state |
| `sort_order` | Integer | Sort position within category |

### 4.4 `Order` (`orders`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Order ID (starts at 100000) |
| `user_id` | Foreign Key | Owner link to `users.id` |
| `service_id` | Foreign Key | Link to `services.id` |
| `provider_id` | Foreign Key (Nullable) | Link to `providers.id` |
| `provider_order_id` | String (Nullable) | ID returned by provider |
| `link` | String | Target social media link / username |
| `quantity` | BigInt | Order quantity |
| `charge` | Decimal(10, 4) | Total charged amount (₹) |
| `start_count` | BigInt | Initial counter value |
| `remains` | BigInt | Remaining count to deliver |
| `status` | Enum | `pending`, `processing`, `completed`, `canceled`, `partial`, `refunded` |
| `comments` | LongText (Nullable) | Custom line-separated comments |
| `idempotency_token` | String (Nullable) | Unique token preventing duplicate placement |

### 4.5 `Transaction` (`transactions`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Transaction ID |
| `user_id` | Foreign Key | Link to `users.id` |
| `payment_method` | String | Payment gateway / UPI |
| `amount` | Decimal(10, 2) | Deposited amount in INR |
| `payment_id` | String (Unique) | UTR number / Reference ID |
| `screenshot_path` | String (Nullable) | Stored payment screenshot path |
| `status` | Enum('pending', 'completed', 'approved', 'rejected') | Transaction status |

### 4.6 `ReceivedPayment` (`received_payments`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Auto-ingested payment entry |
| `utr` | String (Unique) | 12-digit UPI UTR number |
| `amount` | Decimal(10, 2) | Received payment amount |
| `status` | Enum('unused', 'used') | Usage status |
| `user_id` | Foreign Key (Nullable) | User who claimed the UTR |

### 4.7 `ScratchCard` (`scratch_cards`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Scratch card record ID |
| `user_id` | Foreign Key | User link |
| `reward_amount` | Decimal(10, 4) | Reward amount credited to balance |
| `card_type` | Enum('jackpot', 'micro') | Category of reward |
| `scratched_at` | Timestamp | Date and time when scratched |

### 4.8 `Feedback` (`feedbacks`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Feedback ID |
| `user_id` | Foreign Key | Link to `users.id` |
| `type` | Enum('issue', 'feature', 'other') | Feedback category |
| `subject` | String | Subject / title |
| `message` | Text | Detailed explanation |
| `screenshot` | String (Nullable) | Path to uploaded screenshot |
| `status` | Enum('pending', 'reviewed', 'resolved') | Admin resolution state |

### 4.9 `SocialPlatform` (`social_platforms`)
| Column | Type | Description |
|---|---|---|
| `id` | BigInt (PK) | Platform ID |
| `name` | String | Social platform name (e.g. Instagram, YouTube) |
| `slug` | String (Unique) | Platform URL slug |
| `icon` | String | FontAwesome icon or image path |
| `status` | Enum('active', 'inactive') | Visibility state |
| `is_default` | Boolean | System default tab state |

---

## 5. Web Application Routing Map

### 5.1 Public Routes
- `GET /`: Public landing homepage.
- `GET /services`: Interactive services catalog & live search.
- `GET /api-docs`: Developer Reseller API documentation page.
- `GET /faq`, `/rules`, `/resellers`, `/privacy-policy`: Informational & policy pages.
- `GET /blog`, `GET /blog/{slug}`: Article & blog portal.
- `GET /download-app`: Android APK download endpoint.
- `GET /license`, `POST /license/verify`: System license activation portal.
- `MATCH /cron/run`: Web cron runner for external cron triggers.

### 5.2 User Routes (`auth` middleware)
- `GET /dashboard`: Main user dashboard & order form.
- `POST /order/place`: Place order with row locking & idempotency validation.
- `GET /orders`: User order history with search and status filters.
- `POST /orders/{order}/cancel`, `POST /orders/{order}/refill`: Order cancellation & refill requests.
- `GET /add-funds`, `POST /add-funds/pay`, `POST /add-funds/manual`: Wallet deposit & UTR submission.
- `GET /tickets`, `POST /tickets`, `GET /tickets/{ticket}`, `POST /tickets/{ticket}/reply`: Support portal.
- `GET /referrals`: Affiliate program dashboard & referral link.
- `GET /payment-history`: Comprehensive wallet transaction history.
- `GET /profile`, `POST /profile/update`, `POST /profile/api-key`: User profile & API key generation.
- `GET /feedback`, `POST /feedback`: Customer feedback & screenshot upload.
- `GET /api/scratch-card/today`, `POST /api/scratch-card/scratch`: Daily reward scratch card endpoints.

### 5.3 Admin Routes (`auth`, `admin` middleware)
- `GET /admin`: Main admin analytics & overview.
- `GET /admin/users`, `POST /admin/users/{user}/edit`, `POST /admin/users/{user}/delete`: User management.
- `GET /admin/services`, `POST /admin/services/store`, `POST /admin/services/import`: Services management.
- `GET /admin/categories`, `POST /admin/categories/store`: Category management.
- `GET /admin/providers`, `POST /admin/providers/store`, `POST /admin/providers/{provider}/sync-services`: Provider API integration.
- `GET /admin/orders`, `POST /admin/orders/{order}/update`, `POST /admin/orders/{order}/retry`: Orders management.
- `GET /admin/transactions`, `POST /admin/transactions/{id}/approve`, `POST /admin/transactions/{id}/reject`: Payments management.
- `GET /admin/tickets`, `GET /admin/tickets/{ticket}`, `POST /admin/tickets/{ticket}/reply`: Ticket support management.
- `GET /admin/settings`, `POST /admin/settings/update`: Platform settings & SMTP configuration.
- `GET /admin/feedbacks`, `POST /admin/feedbacks/{id}/status`: Customer feedback management.
- `GET /admin/platforms`, `POST /admin/platforms/store`: Social media platform tabs manager.
- `GET /admin/health`, `POST /admin/cache/clear`, `POST /admin/cron/run-manual`: System diagnostics & maintenance.

---

## 6. Mobile App REST API (`/api/v1/*`)

Base Endpoint: `https://your-domain.com/api/v1`

### 6.1 Endpoints Summary
| Endpoint | Method | Auth Required | Description |
|---|---|---|---|
| `/auth/login` | `POST` | No | Email & password authentication |
| `/auth/register` | `POST` | No | User account registration |
| `/auth/google` | `POST` | No | Google OAuth token verification |
| `/catalog` | `GET` | No | Categorized services catalog |
| `/user/profile` | `GET` | Yes (`Bearer <api_key>`) | Profile info, balance & stats |
| `/orders/create` | `POST` | Yes (`Bearer <api_key>`) | Place new SMM order |
| `/orders` | `GET` | Yes (`Bearer <api_key>`) | User orders history |
| `/orders/{id}/cancel` | `POST` | Yes (`Bearer <api_key>`) | Cancel pending order & get refund |
| `/orders/{id}/refill` | `POST` | Yes (`Bearer <api_key>`) | Request order refill |
| `/payments/qr-details` | `GET` | No | Active UPI ID & QR Code details |
| `/payments/submit-utr` | `POST` | Yes (`Bearer <api_key>`) | Submit 12-digit UPI UTR for verification |
| `/payments/history` | `GET` | Yes (`Bearer <api_key>`) | Wallet payment transactions |
| `/scratch-card/today` | `GET` | Yes (`Bearer <api_key>`) | Get daily scratch card state |
| `/scratch-card/scratch`| `POST` | Yes (`Bearer <api_key>`) | Perform scratch action & credit reward |
| `/tickets` | `GET` | Yes (`Bearer <api_key>`) | List user tickets |
| `/tickets/create` | `POST` | Yes (`Bearer <api_key>`) | Create support ticket |
| `/feedback/create` | `POST` | Yes (`Bearer <api_key>`) | Submit customer feedback |

---

## 7. Reseller SMM API (`POST /api/v1` & `/api/v2`)

Standard API endpoint supporting external SMM panels & resellers.

| Action | Parameters | Description |
|---|---|---|
| `services` | `key` | List all active services and pricing |
| `add` | `key`, `service`, `link`, `quantity`, `comments` | Place order programmatically |
| `status` | `key`, `order` (or `orders`) | Fetch order status or bulk statuses |
| `refill` | `key`, `order` | Trigger order refill |
| `balance` | `key` | Fetch reseller wallet balance |

---

## 8. Automation & External APIs

### 8.1 n8n Automated Blog Creation API
- **Endpoint**: `POST /api/v1/blog/create`
- **Authentication**: `key` parameter or `X-Api-Key` header matching user admin API key or `.env` `BLOG_API_KEY`.
- **Request Body**:
```json
{
  "key": "your_admin_api_key_or_env_key",
  "title": "How to Grow Your Instagram in 2026",
  "content": "<p>Article content HTML...</p>",
  "excerpt": "Short excerpt summary...",
  "slug": "how-to-grow-instagram-2026",
  "featured_image": "https://example.com/image.jpg",
  "meta_title": "Grow Instagram 2026",
  "meta_description": "SEO Description",
  "is_published": true
}
```

---

## 9. Business Services Layer

- **`OrderProcessor`**: Handles order creation, rate calculations, row-level locking (`DB::transaction` with `lockForUpdate`), idempotency validation, and dispatching to external provider APIs.
- **`RefundService`**: Executes balance refunds for canceled or partially completed orders with audit logging.
- **`ScratchCardService`**: Manages daily scratch cards, random jackpot generation logic, anti-cheat limits, and automatic transaction creation marked as `'completed'`.
- **`ReferralService`**: Calculates referral commissions and executes affiliate payouts.
- **`DiscountService`**: Applies tier-based and customer-specific discounts to service prices.
- **`OrderStatusSyncService`**: Cron background engine that queries external provider APIs, syncs order statuses (`completed`, `in_progress`, `partial`, `canceled`), and triggers partial refunds.
- **`LicenseService`**: Manages product license key verification and security checks.
- **`RecaptchaService`**: Verifies reCAPTCHA v2, v3, and Enterprise tokens.
- **`SecureUploadService`**: Validates file MIME types, extensions, size limits, and securely stores uploaded screenshots.
- **`BackupManager`**: Generates and manages system database and code backups.
- **`PlatformHelper`**: Categorizes services dynamically by social platform.
- **`BrandingSanitizer`**: Replaces provider branding terms in service names with custom platform branding.

---

## 10. Security & High-Precision System Rules

1. **Double-Spending & Race Condition Prevention**:
   All financial operations (order placement, deposit credit, scratch rewards, refunds) utilize strict MySQL database row-level locking (`User::where('id', $id)->lockForUpdate()->first()`).

2. **Micro-Currency Precision (`format_currency()`)**:
   Global helper in `app/helpers.php`. Standard amounts (`>= ₹0.01`) format to 2 decimal places (`₹10.50`). Micro-amounts (`< ₹0.01`) format up to 4 decimal places (`₹0.008`, `₹0.0008`) to ensure accurate price display.

3. **CSRF Exemption Whitelist**:
   Configured in `bootstrap/app.php`: `api/v1/*`, `api/v2/*`, `payment/webhook/*`.

4. **Security Headers & CSP**:
   Implemented via `SecurityHeadersMiddleware.php` enforcing `X-Frame-Options: SAMEORIGIN`, `X-Content-Type-Options: nosniff`, `X-XSS-Protection`, and Content-Security-Policy rules.

---

## 11. Native Flutter Mobile App Guide (`flutter_app/`)

### 11.1 App Architecture
- State Management: Provider (`AuthNotifier`, `SmmProvider`).
- HTTP Client: Native `http` package talking directly to `/api/v1/*`.
- Design System: Material 3 Glassmorphism with Dark/Neon theme.
- Features: Login, Register, Google Sign-In, Catalog Browsing, Order Placement, Live Orders Filter, UPI Payment with QR & UTR, Daily Scratch Card, Feedback Portal, Support Tickets.

### 11.2 Building Release APK
1. Update API domain in `flutter_app/lib/config/api_config.dart`:
   ```dart
   class ApiConfig {
     static const String baseUrl = "https://your-domain.com/api/v1";
   }
   ```
2. Run build command:
   ```bash
   cd flutter_app
   flutter pub get
   flutter build apk --release
   ```
3. Compiled APK file output:
   `flutter_app/build/app/outputs/flutter-apk/app-release.apk`

---

## 12. System Maintenance & Cron Jobs

Add the following cron entry to your server crontab (running every minute):

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

Alternatively, invoke the Web Cron endpoint via HTTP GET/POST:
`https://your-domain.com/cron/run`
