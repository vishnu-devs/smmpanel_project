# Social Media Marketing (SMM) Panel Platform

[![Laravel](https://img.shields.io/badge/Laravel-v12.x-red.svg)](https://laravel.com)
[![PHP](https://img.shields.io/badge/PHP-%3E%3D8.2-blue.svg)](https://php.net)

A full-stack Social Media Marketing (SMM) reseller panel built on the Laravel framework. It enables panel owners to manage services, automate customer order dispatches to upstream API providers, process instant payment transactions, handle user wallets, generate reseller APIs, send automated balance notifications, and manage customer support tickets.

---

## ✨ Key Features

### 🛍️ Customer Portal
- **Service Catalog**: Categorized services with real-time price calculation, minimum/maximum quantity limits, average completion times, and service filters.
- **Dynamic Service Types**: Supports Default services, Custom Comments, Comment Likes, Package services, and Subscriptions.
- **Wallet & Funds**: Payment integration with automated verification and balance history tracking.
- **Order Management**: Real-time status tracking (Pending, Processing, Completed, Partial, Canceled, Refilling) with partial refunds for incomplete quantities.
- **Support Ticket System**: Threaded support ticketing portal for customer assistance.
- **Automated Reminders**: Email notifications for account balance updates.
- **Progressive Web App (PWA)**: Installable web interface for mobile access.

### 🛡️ Security Architecture
- **API Key Security**: Encryption for provider API keys stored in database.
- **Bot Mitigation**: Bot prevention integration on authentication endpoints.
- **Wallet Transaction Locking**: Database transactions (`DB::transaction`) and locking (`lockForUpdate`) to prevent race conditions during balance operations.
- **OAuth Authentication**: Secure third-party authentication support.
- **Security Headers**: HSTS, X-Frame-Options, X-Content-Type-Options, Content Security Policy (CSP), and Referrer-Policy headers.
- **Rate Limiting**: Custom throttle middleware for authentication, ordering, and API endpoints.

### 👨‍💼 Admin Panel (`/admin`)
- **Dashboard & Metrics**: Analytics for users, orders, revenue, and provider health.
- **User Management**: Manage user profiles, update roles, adjust wallet balances, and view activity logs.
- **Category & Service Catalog**: Service grouping, sorting, custom markup controls, and branding sanitization rules.
- **Provider API Management**: Multi-provider configuration, balance verification, service definition sync, and health status tracking.
- **Audit & Order Control**: Detailed logs for transactions, orders, refills, and manual adjustments.

---

## 🛠️ Technology Stack

- **Backend Framework**: Laravel 12 (PHP 8.2+)
- **Database**: MySQL 8.0+ / MariaDB 10.4+
- **Frontend**: Blade Templating, Vanilla CSS, JavaScript (ES6+), FontAwesome
- **PWA & Mobile**: Service Worker, Web Manifest Integration

---

## 📋 System Requirements

- **PHP**: `>= 8.2`
- **Extensions**: `BCMath`, `Ctype`, `cURL`, `DOM`, `Fileinfo`, `Filter`, `Hash`, `Mbstring`, `OpenSSL`, `PCRE`, `PDO`, `PDO_MySQL`, `Session`, `Tokenizer`, `XML`
- **Database**: MySQL 8.0+ or MariaDB 10.4+
- **Composer**: `2.x`
- **Node.js**: Node 18+

---

## 🚀 Installation & Setup

### 1. Clone Repository
```bash
git clone <your-repository-url>
cd <repository-folder>
```

### 2. Install PHP Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

### 3. Configure Environment File
```bash
cp .env.example .env
php artisan key:generate
```

### 4. Database Setup
Run migrations:
```bash
php artisan migrate
```

### 5. Storage Link
Link public storage for uploads:
```bash
php artisan storage:link
```

### 6. Run Local Development Server
```bash
php artisan serve
```

---

## ⏱️ Scheduler & Cron Setup

Background automated tasks include:
1. Provider health & balance sync
2. Order status synchronization
3. Automated balance reminders
4. Expired token pruning

Add the following Cron entry to your server:

```bash
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

---

## 🔄 Reseller API (`/api/v2`)

Standard API v2 endpoint compatible with reseller management platforms.

- **Endpoint**: `/api/v2`
- **Authentication**: Pass `key` parameter (user's API Key) in POST request payload.
- **Supported Actions**:
  - `add`: Place a new order
  - `status`: Check single or multiple order status
  - `services`: Fetch active services list
  - `balance`: Query user wallet balance
  - `refill`: Request order refill

---

## 📄 License & Terms

- **Software**: Proprietary Software — All Rights Reserved.
