<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Topping berbayar milik satu menu (mis. Nasi Goreng: bakso, extra telur).
        Schema::create('product_addons', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->string('name', 60);
            $table->unsignedInteger('price')->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        // Topping yang dipilih disalin ke item pesanan, supaya riwayat tidak berubah bila topping diedit.
        Schema::table('order_items', function (Blueprint $table) {
            $table->json('addons')->nullable()->after('note');
        });
    }

    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('addons'));
        Schema::dropIfExists('product_addons');
    }
};
