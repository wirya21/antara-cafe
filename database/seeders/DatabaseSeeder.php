<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use App\Support\Inventory;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $username = env('ADMIN_USERNAME', 'admin');
        $password = env('ADMIN_PASSWORD') ?: Str::password(14, symbols: false);

        User::updateOrCreate(['username' => $username], [
            'name' => 'Admin Antara',
            'email' => env('ADMIN_EMAIL'),
            'password' => $password,
            'role' => 'admin',
        ]);

        if (! env('ADMIN_PASSWORD')) {
            $this->command?->warn("Akun admin: {$username} / {$password} (simpan, lalu ganti setelah login)");
        }

        $menu = [
            ['Espresso Based', 'kopi', [
                ['Espresso', 18000, 'Shot espresso dari biji arabika pilihan.'],
                ['Americano', 22000, 'Espresso dengan air panas, ringan dan bersih.'],
                ['Caffe Latte', 28000, 'Espresso dengan susu segar dan sedikit foam.'],
                ['Cappuccino', 28000, 'Espresso, susu, dan foam tebal.'],
            ]],
            ['Manual Brew', 'kopi', [
                ['V60 Kintamani', 30000, 'Seduh manual, rasa buah dan asam segar.'],
            ]],
            ['Non Kopi', 'minuman', [
                ['Matcha Latte', 30000, 'Matcha Jepang dengan susu segar.'],
                ['Chocolate', 26000, 'Cokelat pekat hangat atau dingin.'],
                ['Lemon Tea', 18000, 'Teh dengan perasan lemon segar.'],
            ]],
            ['Makanan', 'makanan', [
                ['Nasi Goreng Antara', 35000, 'Nasi goreng spesial dengan telur dan kerupuk.'],
                ['Croissant Butter', 22000, 'Croissant renyah dengan butter.'],
                ['French Fries', 20000, 'Kentang goreng dengan saus.'],
            ]],
        ];

        foreach ($menu as $order => [$categoryName, $group, $products]) {
            $category = Category::updateOrCreate(
                ['slug' => Str::slug($categoryName)],
                ['name' => $categoryName, 'group' => $group, 'sort_order' => $order],
            );

            foreach ($products as $i => [$name, $price, $description]) {
                Product::updateOrCreate(['slug' => Str::slug($name)], [
                    'category_id' => $category->id,
                    'name' => $name,
                    'price' => $price,
                    'description' => $description,
                    'sort_order' => $i,
                    'is_featured' => $i === 0,
                ]);
            }
        }

        $this->seedIngredients();
    }

    /** Contoh bahan dan resep. Ubah sesuai takaran dan harga di kafe. */
    private function seedIngredients(): void
    {
        if (Ingredient::exists()) {
            return;
        }

        $inventory = app(Inventory::class);
        $ingredients = [];

        foreach ([
            // nama, satuan, stok awal, total harga, minimum
            ['Biji kopi arabika', 'gram', 2000, 500000, 500],
            ['Susu segar', 'ml', 10000, 200000, 2000],
            ['Gula aren cair', 'ml', 2000, 60000, 500],
            ['Bubuk matcha', 'gram', 500, 250000, 100],
            ['Bubuk cokelat', 'gram', 1000, 150000, 200],
            ['Teh hitam', 'gram', 500, 50000, 100],
            ['Lemon', 'pcs', 30, 60000, 10],
            ['Beras', 'gram', 5000, 75000, 1000],
            ['Telur', 'pcs', 30, 60000, 10],
            ['Croissant beku', 'pcs', 20, 160000, 6],
            ['Kentang beku', 'gram', 5000, 175000, 1000],
            ['Cup + tutup', 'pcs', 200, 200000, 50],
        ] as [$name, $unit, $stock, $cost, $min]) {
            $ingredient = Ingredient::create(['name' => $name, 'unit' => $unit, 'min_stock' => $min]);
            $inventory->purchase($ingredient, $stock, $cost, note: 'Stok awal');
            $ingredients[$name] = $ingredient->id;
        }

        $recipes = [
            'espresso' => ['Biji kopi arabika' => 18],
            'americano' => ['Biji kopi arabika' => 18, 'Cup + tutup' => 1],
            'caffe-latte' => ['Biji kopi arabika' => 18, 'Susu segar' => 150, 'Cup + tutup' => 1],
            'cappuccino' => ['Biji kopi arabika' => 18, 'Susu segar' => 120, 'Cup + tutup' => 1],
            'v60-kintamani' => ['Biji kopi arabika' => 15],
            'matcha-latte' => ['Bubuk matcha' => 5, 'Susu segar' => 180, 'Gula aren cair' => 15, 'Cup + tutup' => 1],
            'chocolate' => ['Bubuk cokelat' => 25, 'Susu segar' => 180, 'Cup + tutup' => 1],
            'lemon-tea' => ['Teh hitam' => 3, 'Lemon' => 0.5, 'Gula aren cair' => 20, 'Cup + tutup' => 1],
            'nasi-goreng-antara' => ['Beras' => 150, 'Telur' => 1],
            'croissant-butter' => ['Croissant beku' => 1],
            'french-fries' => ['Kentang beku' => 150],
        ];

        foreach ($recipes as $slug => $recipe) {
            Product::where('slug', $slug)->first()?->ingredients()->sync(
                collect($recipe)->mapWithKeys(fn ($qty, $name) => [$ingredients[$name] => ['quantity' => $qty]])
            );
        }
    }
}
