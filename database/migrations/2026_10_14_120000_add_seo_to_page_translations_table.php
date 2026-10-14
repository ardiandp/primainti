<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->string('seo_title')->nullable()->after('content');
            $table->string('seo_description', 500)->nullable()->after('seo_title');
            // OG image mereferensikan Media Library; media yang dipakai
            // tidak boleh dihapus permanen (nullOnDelete, bukan cascade).
            $table->foreignId('og_image_id')->nullable()->after('seo_description')
                ->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('page_translations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('og_image_id');
            $table->dropColumn(['seo_title', 'seo_description']);
        });
    }
};
