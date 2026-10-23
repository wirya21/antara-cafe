<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Product;
use App\Models\Promotion;
use Illuminate\Support\Facades\Storage;
use Illuminate\View\View;

class DisplayController extends Controller
{
    public function __invoke(): View
    {
        $categories = Category::where('is_active', true)
            ->with(['products' => fn ($q) => $q->with('ingredients')->orderBy('sort_order')->orderBy('name')])
            ->whereHas('products')
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();

        $slides = Promotion::running()->whereNotNull('image_path')->latest()->get()
            ->map(fn (Promotion $promo) => [
                'type' => 'promo',
                'title' => $promo->title,
                'text' => $promo->description,
                'image' => Storage::disk('public')->url($promo->image_path),
            ])
            ->concat(
                Product::with('category', 'ingredients')->where('is_featured', true)->where('is_available', true)
                    ->whereHas('category', fn ($q) => $q->where('is_active', true))
                    ->orderBy('sort_order')->get()
                    ->filter(fn (Product $product) => $product->isOrderable())
                    ->map(fn (Product $product) => [
                        'type' => 'product',
                        'title' => $product->name,
                        'text' => $product->description,
                        'price' => $product->price,
                        'image' => $product->image_url,
                        'group' => $product->category->group,
                    ])
            )
            ->values();

        return view('display.index', [
            'categories' => $categories,
            'slides' => $slides,
            'menuUrl' => route('menu'),
        ]);
    }
}
