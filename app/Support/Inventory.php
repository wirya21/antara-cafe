<?php

namespace App\Support;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\StockMovement;
use App\Models\User;
use Illuminate\Support\Facades\DB;

/**
 * Semua perubahan stok bahan lewat kelas ini, supaya setiap perubahan tercatat di kartu stok.
 */
class Inventory
{
    /** Kurangi stok sesuai resep dan simpan HPP per item. Dipanggil saat pesanan dibuat. */
    public function recordSale(Order $order): void
    {
        $order->loadMissing('items.product.ingredients');

        foreach ($order->items as $item) {
            $ingredients = $item->product?->ingredients ?? collect();
            $unitCost = 0;

            foreach ($ingredients as $ingredient) {
                $used = $ingredient->pivot->quantity * $item->quantity;
                $unitCost += $ingredient->pivot->quantity * $ingredient->cost_per_unit;

                $this->move($ingredient, 'sale', -$used, order: $order, note: "{$item->quantity}× {$item->product_name}");
            }

            // Menu tanpa resep memakai harga pokok manual dari admin.
            if ($ingredients->isEmpty()) {
                $unitCost = (int) $item->product?->manual_cost;
            }

            $item->update(['cost' => (int) round($unitCost)]);
        }
    }

    /** Kembalikan stok dari pesanan yang dibatalkan. */
    public function reverseSale(Order $order, ?User $user = null): void
    {
        $sales = StockMovement::where('order_id', $order->id)->where('type', 'sale')->get();

        foreach ($sales as $sale) {
            $this->move($sale->ingredient, 'void', -$sale->quantity, $user, order: $order, note: "Batal {$order->code}");
        }
    }

    /** Stok masuk (pembelian). Harga rata-rata per satuan ikut diperbarui. */
    public function purchase(Ingredient $ingredient, float $quantity, int $totalCost, ?User $user = null, ?string $note = null): StockMovement
    {
        return DB::transaction(function () use ($ingredient, $quantity, $totalCost, $user, $note) {
            $ingredient->refresh();
            $currentStock = max($ingredient->stock, 0);
            $newStock = $currentStock + $quantity;

            if ($newStock > 0) {
                $ingredient->cost_per_unit = round(($currentStock * $ingredient->cost_per_unit + $totalCost) / $newStock, 2);
            }

            return $this->move($ingredient, 'purchase', $quantity, $user, $totalCost, note: $note);
        });
    }

    /** Opname: samakan stok di sistem dengan hasil hitung fisik. */
    public function adjust(Ingredient $ingredient, float $actualStock, ?User $user = null, ?string $note = null): StockMovement
    {
        $ingredient->refresh();

        return $this->move($ingredient, 'adjustment', $actualStock - $ingredient->stock, $user, note: $note);
    }

    public function waste(Ingredient $ingredient, float $quantity, ?User $user = null, ?string $note = null): StockMovement
    {
        return $this->move($ingredient, 'waste', -$quantity, $user, note: $note);
    }

    private function move(Ingredient $ingredient, string $type, float $quantity, ?User $user = null, ?int $totalCost = null, ?Order $order = null, ?string $note = null): StockMovement
    {
        $ingredient->stock = round($ingredient->stock + $quantity, 2);
        $ingredient->save();

        return $ingredient->movements()->create([
            'type' => $type,
            'quantity' => $quantity,
            'stock_after' => $ingredient->stock,
            'total_cost' => $totalCost,
            'order_id' => $order?->id,
            'user_id' => $user?->id,
            'note' => $note,
        ]);
    }
}
