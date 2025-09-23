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
        Schema::table('books', function (Blueprint $table) {
            if (!Schema::hasColumn('books', 'paddle_price_id')) {
                $table->string('paddle_price_id')->nullable()->after('price');
            }
        });
        
        Schema::table('poems', function (Blueprint $table) {
            if (!Schema::hasColumn('poems', 'paddle_price_id')) {
                $table->string('paddle_price_id')->nullable()->after('price');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('books', function (Blueprint $table) {
            $table->dropColumn('paddle_price_id');
        });
        
        Schema::table('poems', function (Blueprint $table) {
            $table->dropColumn('paddle_price_id');
        });
    }
};
