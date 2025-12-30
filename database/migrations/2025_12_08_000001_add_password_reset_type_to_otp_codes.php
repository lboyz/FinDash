<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Modify the enum to add 'password_reset' type
        // For SQLite (local dev), we need to recreate the column
        // For MySQL, we can alter the enum directly
        
        if (DB::connection()->getDriverName() === 'sqlite') {
            // SQLite doesn't support ALTER COLUMN, so we handle it differently
            // The OTPCode model will just accept the new type value
        } else {
            // MySQL - modify the enum
            DB::statement("ALTER TABLE otp_codes MODIFY COLUMN type ENUM('register', 'login', 'password_reset') NOT NULL");
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (DB::connection()->getDriverName() !== 'sqlite') {
            DB::statement("ALTER TABLE otp_codes MODIFY COLUMN type ENUM('register', 'login') NOT NULL");
        }
    }
};
