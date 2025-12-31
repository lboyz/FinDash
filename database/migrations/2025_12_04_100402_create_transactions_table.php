<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->date('date');
            $table->string('category');
            $table->foreignId('platform_id')->constrained()->onDelete('restrict');
            $table->string('type'); // QRIS, Transfer, Tunai, etc.
            $table->text('description')->nullable();
            $table->decimal('amount', 15, 2);
            $table->string('attachment')->nullable(); // For expense receipts
            $table->timestamps();

            $table->index(['user_id', 'date']);
            $table->index(['user_id', 'category']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transactions');
    }
};
