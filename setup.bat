@echo off
echo ========================================
echo FinDash - Setup Script
echo ========================================
echo.

echo Step 1: Running migrations...
php artisan migrate --force
if %errorlevel% neq 0 (
    echo ERROR: Migration failed!
    pause
    exit /b 1
)
echo ✓ Migrations completed
echo.

echo Step 2: Seeding database...
php artisan db:seed --force
if %errorlevel% neq 0 (
    echo ERROR: Seeding failed!
    pause
    exit /b 1
)
echo ✓ Database seeded
echo.

echo Step 3: Creating storage link...
php artisan storage:link
if %errorlevel% neq 0 (
    echo WARNING: Storage link may already exist
)
echo ✓ Storage link created
echo.

echo ========================================
echo Setup Complete!
echo ========================================
echo.
echo Test User Credentials:
echo Email: test@example.com
echo Password: password
echo.
echo Next steps:
echo 1. Configure your .env file (especially MAIL settings)
echo 2. Run: npm install
echo 3. Run: npm run dev
echo 4. Run: php artisan serve
echo.
echo Then visit: http://localhost:8000
echo.
pause
