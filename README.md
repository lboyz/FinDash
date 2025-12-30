<h1 align="center">Personal Finance Dashboard</h1>
<p align="center">Secure Financial Tracking Application with OTP & Trusted Device Authentication</p>

<p align="center">
  <img src="https://img.shields.io/badge/Laravel-10.x-FF2D20?style=flat&logo=laravel&logoColor=white" alt="Laravel"/>
  <img src="https://img.shields.io/badge/Vue.js-3.x-4FC08D?style=flat&logo=vue.js&logoColor=white" alt="Vue.js"/>
  <img src="https://img.shields.io/badge/TailwindCSS-3.x-38B2AC?style=flat&logo=tailwind-css&logoColor=white" alt="TailwindCSS"/>
  <img src="https://img.shields.io/badge/Inertia.js-1.x-9553E9?style=flat&logo=inertia&logoColor=white" alt="Inertia.js"/>
  <img src="https://img.shields.io/badge/License-MIT-yellow.svg" alt="License MIT"/>
</p>

> 💡 **NOTE**: Designed for personal financial management with enterprise-grade security features.

## Overview

**Personal Finance Dashboard** is a comprehensive solution for tracking income, expenses, and transaction history. Built on the robust **Laravel** framework and a reactive **Vue.js** frontend (via Inertia), it offers a seamless single-page application (SPA) experience.

**What it does:**
-   **Security First**: Implements Email OTP (One-Time Password) for login.
-   **Smart Authentication**: Features a "Trusted Device" mechanism (30-day memory) to balance security and convenience.
-   **Visual Insight**: Summary cards (Income, Expense, Balance) and color-coded transaction lists.
-   **Evidence Tracking**: Supports file attachments for individual transactions.
-   **Export Ready**: Generate PDF and CSV reports instantly.

## 🚀 Deployment & Installation

### Prerequisites
-   PHP 8.2+
-   Composer
-   Node.js 18+
-   MySQL Database

### 1. Clone & Setup
```bash
git clone https://github.com/yourusername/dashboard-keuangan.git
cd dashboard-keuangan
```

### 2. Install Dependencies
```bash
composer install
npm install
```

### 3. Environment Configuration
Duplicate the example environment file:
```bash
cp .env.example .env
```
Configure your database and SMTP settings in `.env`. **SMTP is required for OTP.**
```env
DB_DATABASE=dashboard_keuangan
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
# ... other mail settings
```

### 4. Application Key & Database
```bash
php artisan key:generate
php artisan storage:link
php artisan migrate --seed
```

### 5. Build for Production
For deployment, compile the assets for production:
```bash
npm run build
```

## ⚙️ Configuration

-   **Disable OTP**: Add `OTP_ENABLED=false` to your `.env` to bypass OTP.
-   **Modify Session Lifetime**: Adjust `SESSION_LIFETIME` in `.env` (default is 120 minutes).

---

<p align="center">
  Developed by Your Name
</p>
