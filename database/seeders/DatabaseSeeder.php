<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\User;
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
    }
}
