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
        // BOOKS TABLE
        Schema::table('books', function (Blueprint $table) {
            $table->string('currency', 3)->default('TRY')->change();
        });

        // POEMS TABLE
        Schema::table('poems', function (Blueprint $table) {
            $table->string('currency', 3)->default('TRY')->change();
        });

        // Eğer mevcut kayıtlar USD ise bunları TRY yapmak istersen:
        DB::table('books')->where('currency', '!=', 'TRY')->update(['currency' => 'TRY']);
        DB::table('poems')->where('currency', '!=', 'TRY')->update(['currency' => 'TRY']);
    }

    public function down(): void
    {
        // Geri alma işleminde eski değeri USD olarak varsayıyoruz
        Schema::table('books', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->change();
        });

        Schema::table('poems', function (Blueprint $table) {
            $table->string('currency', 3)->default('USD')->change();
        });
    }
};
