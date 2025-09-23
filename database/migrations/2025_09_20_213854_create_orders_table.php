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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('email');
            $table->morphs('purchasable'); // purchasable_type / purchasable_id
            $table->string('transaction_id')->unique();
            $table->enum('status', ['pending', 'paid', 'refunded'])->default('pending');
            $table->decimal('amount', 10, 2);
            
            // opsiyonel müşteri bilgileri
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('country')->nullable();
            $table->string('postal_code')->nullable();
            
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
