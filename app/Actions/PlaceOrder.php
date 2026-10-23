<?php

namespace App\Actions;

use App\Models\Order;
use App\Models\Product;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class PlaceOrder
{
    /**
     * Buat pesanan baru. Harga selalu diambil dari database, bukan dari browser.
     *
     * @param  array<int, array{product_id: int, quantity: int, note?: string|null}>  $items
     * @param  array<string, mixed>  $attributes  kolom order lain (source, customer_name, table_number, ...)
     */
    public function handle(array $items, array $attributes = []): Order
    {
        $products = Product::with('category')
            ->whereIn('id', array_column($items, 'product_id'))
            ->get()
            ->keyBy('id');

        foreach ($items as $item) {
            $product = $products->get($item['product_id']);

            if (! $product || ! $product->is_available || ! $product->category->is_active) {
                throw ValidationException::withMessages([
                    'items' => ($product->name ?? 'Salah satu menu').' sedang tidak tersedia.',
                ]);
            }
        }

        return DB::transaction(function () use ($items, $products, $attributes) {
            $order = Order::create([
                ...$attributes,
                'code' => Order::generateCode(),
                'status' => 'pending',
            ]);

            $subtotal = 0;

            foreach ($items as $item) {
                $product = $products->get($item['product_id']);
                $lineTotal = $product->price * $item['quantity'];
                $subtotal += $lineTotal;

                $order->items()->create([
                    'product_id' => $product->id,
                    'product_name' => $product->name,
                    'price' => $product->price,
                    'quantity' => $item['quantity'],
                    'subtotal' => $lineTotal,
                    'note' => $item['note'] ?? null,
                ]);
            }

            $order->update(['subtotal' => $subtotal, 'total' => $subtotal - ($order->discount ?? 0)]);

            return $order;
        });
    }
}
