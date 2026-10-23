<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Ingredient;
use App\Models\StockMovement;
use App\Support\Inventory;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\View\View;

class IngredientController extends Controller
{
    public function index(Request $request): View
    {
        $ingredients = Ingredient::with('products:id,name')
            ->when($request->filled('q'), fn ($q) => $q->where('name', 'like', '%'.$request->string('q').'%'))
            ->when($request->input('status') === 'menipis', fn ($q) => $q->low())
            ->orderByRaw('stock <= min_stock desc')
            ->orderBy('name')
            ->get();

        return view('admin.ingredients.index', [
            'ingredients' => $ingredients,
            'lowCount' => Ingredient::low()->count(),
            'stockValue' => (int) Ingredient::where('stock', '>', 0)->get()->sum(fn ($i) => $i->stock * $i->cost_per_unit),
        ]);
    }

    public function create(): View
    {
        return view('admin.ingredients.form', ['ingredient' => new Ingredient(['unit' => 'gram'])]);
    }

    public function store(Request $request, Inventory $inventory): RedirectResponse
    {
        $data = $this->validated($request);
        $initialStock = (float) $request->input('initial_stock', 0);
        $initialCost = (int) $request->input('initial_cost', 0);

        $ingredient = Ingredient::create($data);

        if ($initialStock > 0) {
            $inventory->purchase($ingredient, $initialStock, $initialCost, $request->user(), 'Stok awal');
        }

        return redirect()->route('admin.ingredients.index')->with('status', "Bahan {$ingredient->name} ditambahkan.");
    }

    public function show(Ingredient $ingredient): View
    {
        return view('admin.ingredients.show', [
            'ingredient' => $ingredient->load('products'),
            'movements' => $ingredient->movements()->with('user', 'order')->latest('id')->paginate(30),
        ]);
    }

    public function edit(Ingredient $ingredient): View
    {
        return view('admin.ingredients.form', compact('ingredient'));
    }

    public function update(Request $request, Ingredient $ingredient): RedirectResponse
    {
        $ingredient->update($this->validated($request));

        return redirect()->route('admin.ingredients.index')->with('status', 'Bahan diperbarui.');
    }

    public function destroy(Ingredient $ingredient): RedirectResponse
    {
        if ($ingredient->products()->exists()) {
            return back()->with('error', 'Bahan masih dipakai di resep produk. Hapus dari resep dulu.');
        }

        $ingredient->delete();

        return redirect()->route('admin.ingredients.index')->with('status', 'Bahan dihapus.');
    }

    /** Stok masuk, opname, atau barang terbuang. */
    public function move(Request $request, Ingredient $ingredient, Inventory $inventory): RedirectResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(['purchase', 'adjustment', 'waste'])],
            'quantity' => ['required', 'numeric', 'min:0', 'max:9999999'],
            'total_cost' => ['nullable', 'integer', 'min:0', 'required_if:type,purchase'],
            'note' => ['nullable', 'string', 'max:150'],
        ], [
            'total_cost.required_if' => 'Isi total harga beli supaya HPP bisa dihitung.',
        ]);

        match ($data['type']) {
            'purchase' => $inventory->purchase($ingredient, (float) $data['quantity'], (int) $data['total_cost'], $request->user(), $data['note'] ?? null),
            'adjustment' => $inventory->adjust($ingredient, (float) $data['quantity'], $request->user(), $data['note'] ?? null),
            'waste' => $inventory->waste($ingredient, (float) $data['quantity'], $request->user(), $data['note'] ?? null),
        };

        return back()->with('status', StockMovement::TYPES[$data['type']]." {$ingredient->name} dicatat.");
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'unit' => ['required', 'string', 'max:20'],
            'min_stock' => ['nullable', 'numeric', 'min:0'],
        ]) + ['min_stock' => 0];
    }
}
