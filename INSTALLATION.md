# FinDash - Financial Dashboard Installation Guide

## 📋 Prerequisites

Before you begin, ensure you have the following installed:
- PHP >= 8.2
- Composer
- Node.js >= 18.x
- NPM or Yarn
- MySQL >= 8.0
- Laragon (or similar local development environment)

## 🚀 Installation Steps

### 1. Clone or Navigate to Project

```bash
cd d:\laragon\www\Dashboard-Keuangan
```

### 2. Install PHP Dependencies

```bash
composer install
```

### 3. Install JavaScript Dependencies

```bash
npm install
```

### 4. Environment Configuration

The `.env` file should already exist. Update the following settings:

#### Database Configuration
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dashboard_keuangan
DB_USERNAME=root
DB_PASSWORD=
```

#### Mail Configuration (Required for OTP)

**Option 1: Using Mailtrap (Recommended for Development)**
```env
MAIL_MAILER=smtp
MAIL_HOST=sandbox.smtp.mailtrap.io
MAIL_PORT=2525
MAIL_USERNAME=your_mailtrap_username
MAIL_PASSWORD=your_mailtrap_password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="noreply@findash.com"
MAIL_FROM_NAME="FinDash"
```

**Option 2: Using Gmail**
```env
MAIL_MAILER=smtp
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your-email@gmail.com
MAIL_PASSWORD=your-app-password
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS="your-email@gmail.com"
MAIL_FROM_NAME="FinDash"
```

> **Note**: For Gmail, you need to create an [App Password](https://support.google.com/accounts/answer/185833)

#### Application Settings
```env
APP_NAME="FinDash"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://localhost
```

### 5. Generate Application Key

```bash
php artisan key:generate
```

### 6. Create Database

Create a new MySQL database named `dashboard_keuangan`:

```sql
CREATE DATABASE dashboard_keuangan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Or use phpMyAdmin in Laragon to create the database.

### 7. Run Migrations

```bash
php artisan migrate
```

This will create all necessary tables:
- `users` (with username and profile_image fields)
- `otp_codes`
- `platforms`
- `transactions`
- And other Laravel default tables

### 8. Create Storage Link

```bash
php artisan storage:link
```

This creates a symbolic link for file uploads (profile images and transaction attachments).

### 9. Seed Database

```bash
php artisan db:seed
```

This will:
- Create platform entries (Gopay, OVO, Dana, BCA, Mandiri, etc.)
- Create a test user (if none exists)
- Generate 100 sample transactions

**Test User Credentials:**
- Email: `test@example.com`
- Password: `password`

### 10. Build Frontend Assets

**For Development:**
```bash
npm run dev
```

**For Production:**
```bash
npm run build
```

### 11. Start Development Server

**Option 1: Using Laravel's Built-in Server**
```bash
php artisan serve
```

**Option 2: Using Composer Script (Recommended)**
```bash
composer run dev
```

This will start:
- Laravel server on `http://localhost:8000`
- Queue listener
- Vite dev server

**Option 3: Using Laragon**
Just start Laragon and access `http://dashboard-keuangan.net`

## 🧪 Testing the Application

### 1. Access the Application

Open your browser and navigate to:
- `http://localhost:8000` (if using artisan serve)
- `http://dashboard-keuangan.net` (if using Laragon)

### 2. Test Registration Flow

1. Click "Register"
2. Fill in the form:
   - Name: Your Name
   - Username: yourusername
   - Email: your-email@example.com
   - Password: password
   - Confirm Password: password
3. Click "Create account"
4. Check your email (or Mailtrap inbox) for the OTP code
5. Enter the 6-digit OTP code
6. You should be redirected to the dashboard

### 3. Test Login Flow

1. Logout from the current session
2. Click "Login"
3. Enter your email and password
4. Check your email for the OTP code
5. Enter the OTP code
6. You should be logged in to the dashboard

### 4. Test Dashboard Features

- **View Summary**: Check total income, expense, and balance
- **Add Transaction**: Click the "+" button to add income or expense
- **Filter Transactions**: Use the filter bar to filter by date, category, platform, etc.
- **Edit Transaction**: Click the edit icon on any transaction
- **Delete Transaction**: Click the delete icon on any transaction
- **Export Data**: Click CSV or PDF export buttons
- **Profile**: Navigate to Profile page and update your information
- **Settings**: Navigate to Settings and change your password

## 📁 Project Structure

```
Dashboard-Keuangan/
├── app/
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── Auth/
│   │   │   │   ├── LoginController.php
│   │   │   │   ├── RegisterController.php
│   │   │   │   └── OTPController.php
│   │   │   ├── DashboardController.php
│   │   │   ├── TransactionController.php
│   │   │   ├── ProfileController.php
│   │   │   ├── SettingsController.php
│   │   │   └── ExportController.php
│   │   └── Middleware/
│   │       └── EnsureOTPVerified.php
│   ├── Mail/
│   │   └── OTPMail.php
│   ├── Models/
│   │   ├── User.php
│   │   ├── OTPCode.php
│   │   ├── Transaction.php
│   │   └── Platform.php
│   └── Services/
│       └── OTPService.php
├── database/
│   ├── migrations/
│   │   ├── 2025_12_04_100400_create_otp_codes_table.php
│   │   ├── 2025_12_04_100401_create_platforms_table.php
│   │   ├── 2025_12_04_100402_create_transactions_table.php
│   │   └── 2025_12_04_100403_add_fields_to_users_table.php
│   └── seeders/
│       ├── PlatformSeeder.php
│       └── TransactionSeeder.php
├── resources/
│   ├── js/
│   │   └── pages/
│   │       ├── Auth/
│   │       │   ├── Register.vue
│   │       │   ├── Login.vue
│   │       │   └── VerifyOTP.vue
│   │       ├── Dashboard.vue
│   │       ├── Profile.vue
│   │       └── Settings.vue
│   └── views/
│       ├── emails/
│       │   └── otp-email.blade.php
│       └── exports/
│           └── transactions-pdf.blade.php
└── routes/
    └── web.php
```

## 🔧 Troubleshooting

### OTP Emails Not Sending

1. Check your `.env` mail configuration
2. Verify SMTP credentials are correct
3. Check `storage/logs/laravel.log` for errors
4. Test email configuration:
   ```bash
   php artisan tinker
   Mail::raw('Test email', function($message) {
       $message->to('test@example.com')->subject('Test');
   });
   ```

### Database Connection Error

1. Ensure MySQL is running in Laragon
2. Verify database name, username, and password in `.env`
3. Check if the database exists

### File Upload Not Working

1. Ensure storage link is created: `php artisan storage:link`
2. Check folder permissions for `storage/app/public`
3. Verify `APP_URL` in `.env` is correct

### Frontend Not Loading

1. Ensure Vite dev server is running: `npm run dev`
2. Clear browser cache
3. Check console for JavaScript errors

## 🔐 Security Notes

1. **OTP Expiration**: OTP codes expire after 10 minutes
2. **One-Time Use**: Each OTP can only be used once
3. **Session Security**: OTP verification is required for each login session
4. **Password Hashing**: All passwords are hashed using Laravel's default bcrypt
5. **CSRF Protection**: All forms include CSRF tokens
6. **File Upload Validation**: Attachments are validated for type and size

## 📊 Database Schema

### Users Table
- id, name, username, email, email_verified_at, password, profile_image, timestamps

### OTP Codes Table
- id, user_id, code, type (register/login), expired_at, used, timestamps

### Platforms Table
- id, name, timestamps

### Transactions Table
- id, user_id, date, category (income/expense), platform_id, type, description, amount, attachment, timestamps

## 🎨 Features

✅ OTP-based authentication (register & login)
✅ Financial transaction management (income & expense)
✅ Advanced filtering (date range, category, platform, type, description)
✅ Transaction CRUD operations
✅ File attachments for expenses
✅ Export to CSV and PDF
✅ User profile management
✅ Password change functionality
✅ Responsive design
✅ Modern UI with Tailwind CSS
✅ Real-time form validation

## 📞 Support

For issues or questions, please check:
1. Laravel logs: `storage/logs/laravel.log`
2. Browser console for frontend errors
3. Network tab for API request/response errors

## 🎉 You're All Set!

Your FinDash application is now ready to use. Happy tracking! 💰
