<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->string('alt_override')->nullable()->after('media_asset_id');
            $table->date('taken_at')->nullable()->after('alt_override');
            $table->unsignedInteger('position')->default(0)->after('taken_at');
            $table->index(['status', 'position']);
        });
    }

    public function down(): void
    {
        Schema::table('gallery_items', function (Blueprint $table) {
            $table->dropIndex(['status', 'position']);
            $table->dropColumn(['alt_override', 'taken_at', 'position']);
        });
    }
};
