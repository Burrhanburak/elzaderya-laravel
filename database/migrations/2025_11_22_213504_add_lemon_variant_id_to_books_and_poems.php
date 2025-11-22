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
        $table->unsignedBigInteger('lemon_variant_id')->nullable()->after('price');
    });

    Schema::table('poems', function (Blueprint $table) {
        $table->unsignedBigInteger('lemon_variant_id')->nullable()->after('price');
    });
}

public function down(): void
{
    Schema::table('books', function (Blueprint $table) {
        $table->dropColumn('lemon_variant_id');
    });

    Schema::table('poems', function (Blueprint $table) {
        $table->dropColumn('lemon_variant_id');
    });
}

};
