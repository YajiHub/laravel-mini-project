# QueenBuilders IMS — Inventory Management System

A comprehensive web-based inventory management and point-of-sale system for **QueenBuilders Hardware & Construction Supplies**, developed as part of the ITSD 82 Mini Project (BSIT 3C).

## Features

- **Role-Based Access Control** — 4 user roles (Admin, Inventory Manager, Store Manager, Cashier) with granular permissions
- **Point of Sale (POS)** — AJAX-driven cart, variant support, multiple payment methods, receipt generation
- **Inventory Management** — Product CRUD, categories, suppliers, product variants, stock transactions
- **Real-Time Notifications** — Low stock alerts, in-app notification bell with live polling
- **Dashboard Analytics** — Chart.js visualizations, role-specific statistics, recent activity feeds
- **Reporting & Exports** — Inventory and sales reports in PDF, CSV, and Excel formats
- **Security** — bcrypt password hashing, CSRF protection, CSP headers, rate limiting, XSS prevention
- **Two-Factor Authentication** — TOTP-based MFA with QR code setup
- **Audit Logging** — Complete audit trail of all system activity (logins, CRUD, backups)
- **Automated Backup** — Weekly database + file backups with 30-day retention
- **Data Import** — Bulk product import via CSV/Excel with validation

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 13.7 (PHP 8.3+) |
| Frontend | Blade + Tailwind CSS 3 + Alpine.js 3 |
| Database | PostgreSQL 18 |
| PDF | barryvdh/laravel-dompdf |
| Excel | maatwebsite/excel |
| Backup | spatie/laravel-backup |
| Charts | Chart.js |

## Test Accounts

| Role | Email | Password |
|------|-------|----------|
| Administrator | admin@queenbuilders.com | password123 |
| Cashier | cashier@queenbuilders.com | password123 |
| Inventory Manager | inventory@queenbuilders.com | password123 |
| Store Manager | manager@queenbuilders.com | password123 |

## Quick Start (Local)

```bash
git clone https://github.com/your-username/QueenBuilders-IMS.git
cd QueenBuilders-IMS
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate --seed
php artisan storage:link
npm install && npm run build
php artisan serve
```

## Documentation

- 📖 **User Manual:** `docs/USER-MANUAL.html`
- 🔧 **Technical Docs:** `docs/TECHNICAL-DOCS.html`
- 🗄️ **SQL Schema:** `database/schema/schema-dump.sql`

## Project Team

- Developer 1 — Montecillo
- Developer 2 — Salapang
- BSIT 3C | ITSD 82 | Web Software Tools

## License

This project is submitted as an academic requirement for ITSD 82.
