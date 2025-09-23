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
        Schema::table('blogs', function (Blueprint $table) {
            $table->string('cover_image_filename')->nullable()->after('cover_image');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->string('file_url_filename')->nullable()->after('file_url');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('blogs', function (Blueprint $table) {
            $table->dropColumn('cover_image_filename');
        });

        Schema::table('certificates', function (Blueprint $table) {
            $table->dropColumn('file_url_filename');
        });
    }
};
