<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('menu_items', function (Blueprint $table) {
            // url (tautan bebas) | page (halaman CMS) | product_category (katalog filter) | route (rute bernama)
            $table->string('link_type', 30)->default('url')->after('page_id');
            $table->foreignId('product_category_id')->nullable()->after('page_id')
                ->constrained()->nullOnDelete();
            $table->string('route_name', 100)->nullable()->after('product_category_id');
        });

        Schema::create('menu_item_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('menu_item_id')->constrained()->cascadeOnDelete();
            $table->string('locale', 10);
            $table->string('title');
            $table->timestamps();

            $table->unique(['menu_item_id', 'locale']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('menu_item_translations');

        Schema::table('menu_items', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_category_id');
            $table->dropColumn(['link_type', 'route_name']);
        });
    }
};
