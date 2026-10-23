<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Http\Request;
use Illuminate\View\View;

/** Satu halaman untuk melihat bahan tiap menu, stoknya, dan sisa porsi yang bisa dibuat. */
class RecipeController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category', 'ingredients')
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->orderBy(Category::select('sort_order')->whereColumn('categories.id', 'products.category_id'))
            ->orderBy('sort_order')->orderBy('name')
            ->get();

        return view('admin.recipes.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
            'unused' => Ingredient::doesntHave('products')->orderBy('name')->get(),
        ]);
    }
}
