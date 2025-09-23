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
        Schema::create('books', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('description');
            $table->string('cover_image')->nullable();
            $table->string('preview_pdf')->nullable();
            $table->string('full_pdf')->nullable();
            $table->decimal('price', 8, 2);
            $table->string('slug')->nullable();
            $table->string('paddle_product_id')->nullable();  // pro_xxx
            $table->string('paddle_price_id')->nullable();    // pri_xxx
            $table->string('cover_image_filename')->nullable();
            $table->string('preview_pdf_filename')->nullable();
            $table->string('full_pdf_filename')->nullable();
            $table->enum('language', ['tr', 'en', 'ru', 'az'])->default('tr');

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('books');
    }
};
