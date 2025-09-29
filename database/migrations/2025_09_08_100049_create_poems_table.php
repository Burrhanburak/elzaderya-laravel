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
        Schema::create('poems', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->string('slug')->nullable();
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->string('cover_image')->nullable();
            $table->string('preview_pdf')->nullable();
            $table->string('full_pdf')->nullable();
            $table->string('cover_image_filename')->nullable();
            $table->string('preview_pdf_filename')->nullable();
            $table->string('full_pdf_filename')->nullable();
            $table->integer('preview_lines')->default(3);
        
            // Paddle entegrasyonu
         
            // fiyat alanları
            $table->decimal('price', 8, 2)->default(0);
            $table->string('currency', 3)->default('USD'); // Polar API'den price_currency geliyor

            // Polar entegrasyonu
            $table->string('polar_product_id')->nullable(); // pol_prod_xxx
        
            $table->string('language', 5)->default('tr');
        
            $table->timestamps();
        });
        
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('poems');
    }
};
