<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            // Perkiraan lama pembuatan satu pesanan menu ini, dalam menit.
            $table->unsignedSmallInteger('prep_minutes')->nullable()->after('price');
            // Harga pokok manual untuk menu tanpa resep, mis. kue yang dibeli jadi.
            $table->unsignedInteger('manual_cost')->nullable()->after('prep_minutes');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['prep_minutes', 'manual_cost']);
        });
    }
};
