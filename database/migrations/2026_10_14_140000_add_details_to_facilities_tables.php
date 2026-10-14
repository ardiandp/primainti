<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('facilities', function (Blueprint $table) {
            $table->unsignedInteger('sort_order')->default(0)->after('status');
            $table->index(['status', 'sort_order']);
        });

        Schema::table('facility_translations', function (Blueprint $table) {
            $table->text('summary')->nullable()->after('slug');
        });
    }

    public function down(): void
    {
        Schema::table('facility_translations', function (Blueprint $table) {
            $table->dropColumn('summary');
        });

        Schema::table('facilities', function (Blueprint $table) {
            $table->dropIndex(['status', 'sort_order']);
            $table->dropColumn('sort_order');
        });
    }
};
