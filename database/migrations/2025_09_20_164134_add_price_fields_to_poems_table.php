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
        Schema::table('poems', function (Blueprint $table) {
            $table->decimal('price', 8, 2)->default(0)->after('language');
            $table->string('lemon_squeezy_product_id')->nullable()->after('price');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('poems', function (Blueprint $table) {
            $table->dropColumn(['price', 'lemon_squeezy_product_id']);
        });
    }
};
