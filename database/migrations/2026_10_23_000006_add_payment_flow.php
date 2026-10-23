<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->string('payment_proof_path')->nullable()->after('payment_method');
            $table->timestamp('completed_at')->nullable()->after('paid_at');
        });

        // Transaksi lama yang sudah lunas dianggap sudah diserahkan.
        DB::table('orders')->where('status', 'paid')->update(['completed_at' => DB::raw('paid_at')]);

        Schema::create('settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('settings');

        Schema::table('orders', function (Blueprint $table) {
            $table->dropColumn(['payment_proof_path', 'completed_at']);
        });
    }
};
