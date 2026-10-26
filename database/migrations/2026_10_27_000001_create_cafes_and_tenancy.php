<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * kafehub melayani banyak kafe dari satu aplikasi. Setiap data kafe diberi cafe_id;
 * data yang sudah ada menjadi milik kafe pertama (Antara Cafe).
 */
return new class extends Migration
{
    /** Tabel milik kafe dan kolom unik yang sekarang berlaku per kafe. */
    private const TABLES = [
        'users' => ['username', 'email'],
        'categories' => ['slug'],
        'products' => ['slug'],
        'ingredients' => [],
        'stock_movements' => [],
        'members' => ['phone'],
        'promotions' => ['voucher_code'],
        'orders' => [],
    ];

    public function up(): void
    {
        Schema::create('cafes', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug')->unique(); // subdomain: {slug}.kafehub.id
            $table->string('owner_name')->nullable();
            $table->string('owner_phone')->nullable();
            $table->string('city')->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('is_trial')->default(false);
            $table->date('active_until')->nullable(); // kosong = tanpa batas
            $table->unsignedInteger('monthly_fee')->default(0);
            $table->text('notes')->nullable();
            $table->timestamps();
        });

        $hasData = DB::table('users')->exists() || DB::table('products')->exists();
        $firstId = $hasData
            ? DB::table('cafes')->insertGetId(['name' => 'Antara Cafe', 'slug' => 'antara', 'is_active' => true, 'created_at' => now(), 'updated_at' => now()])
            : null;

        foreach (self::TABLES as $name => $uniques) {
            Schema::table($name, function (Blueprint $table) use ($uniques) {
                $table->foreignId('cafe_id')->nullable()->after('id')->constrained()->cascadeOnDelete();
                foreach ($uniques as $column) {
                    $table->dropUnique([$column]);
                }
            });
            if ($firstId) {
                DB::table($name)->update(['cafe_id' => $firstId]);
            }
            Schema::table($name, function (Blueprint $table) use ($uniques) {
                foreach ($uniques as $column) {
                    $table->unique(['cafe_id', $column]);
                }
            });
        }

        // Pengaturan: cafe_id kosong = pengaturan kafehub.id sendiri (isi halaman depan).
        Schema::create('cafe_settings', function (Blueprint $table) {
            $table->id();
            $table->foreignId('cafe_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('key');
            $table->text('value')->nullable();
            $table->timestamps();
            $table->unique(['cafe_id', 'key']);
        });
        foreach (DB::table('settings')->get() as $row) {
            DB::table('cafe_settings')->insert([
                'cafe_id' => $row->key === 'kafehub_content' ? null : $firstId,
                'key' => $row->key,
                'value' => $row->value,
                'created_at' => $row->created_at,
                'updated_at' => $row->updated_at,
            ]);
        }
        Schema::drop('settings');
        Schema::rename('cafe_settings', 'settings');
    }

    public function down(): void
    {
        Schema::create('old_settings', function (Blueprint $table) {
            $table->string('key')->primary();
            $table->text('value')->nullable();
            $table->timestamps();
        });
        $first = DB::table('cafes')->min('id');
        foreach (DB::table('settings')->whereNull('cafe_id')->orWhere('cafe_id', $first)->get() as $row) {
            DB::table('old_settings')->insertOrIgnore(['key' => $row->key, 'value' => $row->value, 'created_at' => $row->created_at, 'updated_at' => $row->updated_at]);
        }
        Schema::drop('settings');
        Schema::rename('old_settings', 'settings');

        foreach (self::TABLES as $name => $uniques) {
            Schema::table($name, function (Blueprint $table) use ($uniques) {
                foreach ($uniques as $column) {
                    $table->dropUnique(['cafe_id', $column]);
                }
                $table->dropConstrainedForeignId('cafe_id');
                foreach ($uniques as $column) {
                    $table->unique($column);
                }
            });
        }
        Schema::dropIfExists('cafes');
    }
};
