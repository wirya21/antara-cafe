<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Category;
use App\Models\Ingredient;
use App\Models\Product;
use App\Support\Costing;
use App\Support\Photo;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $products = Product::with('category', 'ingredients')
            ->when($request->filled('category'), fn ($q) => $q->where('category_id', $request->integer('category')))
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->orderBy(Category::select('sort_order')->whereColumn('categories.id', 'products.category_id'))
            ->orderBy('sort_order')
            ->orderBy('name')
            ->paginate(20)
            ->withQueryString();

        return view('admin.products.index', [
            'products' => $products,
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
        ]);
    }

    public function create(): View
    {
        return view('admin.products.form', [
            'product' => new Product(['is_available' => true]),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
            'overheadPerServing' => Costing::overheadPerServing(),
        ]);
    }

    public function store(Request $request): RedirectResponse
    {
        $data = $this->validated($request);

        if ($request->hasFile('image')) {
            $data['image_path'] = Photo::store($request->file('image'), 'products', 900);
        }

        $product = Product::create($data);
        $this->syncRecipe($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Produk ditambahkan.');
    }

    public function edit(Product $product): View
    {
        return view('admin.products.form', [
            'product' => $product->load('ingredients'),
            'categories' => Category::orderBy('sort_order')->orderBy('name')->get(),
            'ingredients' => Ingredient::orderBy('name')->get(),
            'overheadPerServing' => Costing::overheadPerServing(),
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $data = $this->validated($request, $product);

        if ($request->hasFile('image') || $request->boolean('remove_image')) {
            if ($product->image_path) {
                Storage::disk('public')->delete($product->image_path);
            }
            $data['image_path'] = $request->hasFile('image')
                ? Photo::store($request->file('image'), 'products', 900)
                : null;
        }

        $product->update($data);
        $this->syncRecipe($product, $request);

        return redirect()->route('admin.products.index')->with('status', 'Produk diperbarui.');
    }

    public function destroy(Product $product): RedirectResponse
    {
        if ($product->image_path) {
            Storage::disk('public')->delete($product->image_path);
        }

        $product->delete();

        return redirect()->route('admin.products.index')->with('status', 'Produk dihapus.');
    }

    private function validated(Request $request, ?Product $product = null): array
    {
        $data = $request->validate([
            'category_id' => ['required', 'exists:categories,id'],
            'name' => ['required', 'string', 'max:120'],
            'description' => ['nullable', 'string', 'max:1000'],
            'price' => ['required', 'integer', 'min:0'],
            'prep_minutes' => ['nullable', 'integer', 'min:1', 'max:180'],
            'manual_cost' => ['nullable', 'integer', 'min:0'],
            'sort_order' => ['nullable', 'integer', 'min:0'],
            'image' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'max:3072'],
            'recipe' => ['nullable', 'array'],
            'recipe.*.ingredient_id' => ['nullable', 'exists:ingredients,id'],
            'recipe.*.quantity' => ['nullable', 'numeric', 'min:0'],
        ]);

        unset($data['image'], $data['recipe']);
        $data['slug'] = $this->uniqueSlug($data['name'], $product);
        $data['sort_order'] = $data['sort_order'] ?? 0;
        $data['is_available'] = $request->boolean('is_available');
        $data['is_featured'] = $request->boolean('is_featured');

        return $data;
    }

    private function syncRecipe(Product $product, Request $request): void
    {
        $recipe = collect($request->input('recipe', []))
            ->filter(fn ($row) => ! empty($row['ingredient_id']) && (float) ($row['quantity'] ?? 0) > 0)
            ->mapWithKeys(fn ($row) => [(int) $row['ingredient_id'] => ['quantity' => (float) $row['quantity']]]);

        $product->ingredients()->sync($recipe);
    }

    private function uniqueSlug(string $name, ?Product $product): string
    {
        $base = Str::slug($name);
        $slug = $base;

        for ($i = 2; Product::where('slug', $slug)->when($product, fn ($q) => $q->whereKeyNot($product->id))->exists(); $i++) {
            $slug = "{$base}-{$i}";
        }

        return $slug;
    }
}
