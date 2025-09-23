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
            $table->string('paddle_product_id')->nullable();
            $table->string('paddle_price_id')->nullable();
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
