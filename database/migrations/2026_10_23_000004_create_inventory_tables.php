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
        Schema::create('ingredients', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('unit'); // gram, ml, pcs, ...
            $table->decimal('stock', 12, 2)->default(0);
            $table->decimal('min_stock', 12, 2)->default(0);
            $table->decimal('cost_per_unit', 12, 2)->default(0); // Rp per satuan, rata-rata tertimbang
            $table->timestamps();
        });

        // Resep: berapa bahan yang dipakai untuk satu porsi produk.
        Schema::create('ingredient_product', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('ingredient_id')->constrained()->restrictOnDelete();
            $table->decimal('quantity', 10, 2);
            $table->unique(['product_id', 'ingredient_id']);
        });

        Schema::create('stock_movements', function (Blueprint $table) {
            $table->id();
            $table->foreignId('ingredient_id')->constrained()->cascadeOnDelete();
            $table->string('type'); // purchase, sale, adjustment, waste, void
            $table->decimal('quantity', 12, 2); // positif = masuk, negatif = keluar
            $table->decimal('stock_after', 12, 2);
            $table->unsignedInteger('total_cost')->nullable(); // untuk pembelian
            $table->foreignId('order_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('note')->nullable();
            $table->timestamps();

            $table->index(['ingredient_id', 'created_at']);
        });

        Schema::table('order_items', function (Blueprint $table) {
            $table->unsignedInteger('cost')->default(0)->after('price'); // HPP per porsi saat terjual
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('order_items', fn (Blueprint $table) => $table->dropColumn('cost'));
        Schema::dropIfExists('stock_movements');
        Schema::dropIfExists('ingredient_product');
        Schema::dropIfExists('ingredients');
    }
};
