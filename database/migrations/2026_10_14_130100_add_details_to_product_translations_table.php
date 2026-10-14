<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            $table->longText('functions')->nullable()->after('description');
            $table->longText('applications')->nullable()->after('functions');
            $table->string('seo_title')->nullable()->after('applications');
            $table->string('seo_description', 500)->nullable()->after('seo_title');
            $table->foreignId('og_image_id')->nullable()->after('seo_description')
                ->constrained('media_assets')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('product_translations', function (Blueprint $table) {
            $table->dropConstrainedForeignId('og_image_id');
            $table->dropColumn(['functions', 'applications', 'seo_title', 'seo_description']);
        });
    }
};
