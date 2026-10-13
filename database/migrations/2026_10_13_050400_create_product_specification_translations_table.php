<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_specification_translations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('product_specification_id');
            $table->string('locale', 10);
            $table->string('label');
            $table->text('value')->nullable();
            $table->timestamps();

            // Nama FK eksplisit: nama otomatis melebihi batas MySQL (64 karakter).
            $table->foreign('product_specification_id', 'pst_spec_fk')
                ->references('id')->on('product_specifications')->cascadeOnDelete();

            $table->unique(['product_specification_id', 'locale'], 'pst_locale_unique');
            $table->index(['locale', 'label']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_specification_translations');
    }
};
