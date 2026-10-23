<?php

namespace App\Actions;

use App\Models\Member;
use App\Models\Order;
use App\Models\Product;
use App\Models\Promotion;
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
    public function handle(array $items, array $attributes = [], ?string $voucherCode = null, ?string $memberPhone = null): Order
    {
        $voucher = null;
        if (filled($voucherCode)) {
            $voucher = Promotion::findVoucher($voucherCode);
            if (! $voucher) {
                throw ValidationException::withMessages(['voucher_code' => 'Kode voucher tidak berlaku atau sudah berakhir.']);
            }
        }

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

        return DB::transaction(function () use ($items, $products, $attributes, $voucher, $memberPhone) {
            if (filled($memberPhone)) {
                $attributes['member_id'] = Member::findOrRegister($memberPhone, $attributes['customer_name'] ?? null)->id;
            }

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

            if ($voucher && $voucher->discountFor($subtotal) === 0) {
                throw ValidationException::withMessages([
                    'voucher_code' => 'Voucher ini berlaku untuk belanja minimal Rp'.number_format($voucher->min_purchase, 0, ',', '.').'.',
                ]);
            }

            $promotion = $voucher ?? Promotion::bestAutomaticFor($subtotal);
            $discount = $promotion?->discountFor($subtotal) ?? 0;

            $order->update([
                'subtotal' => $subtotal,
                'discount' => $discount,
                'promotion_id' => $promotion?->id,
                'total' => $subtotal - $discount,
            ]);

            return $order;
        });
    }
}
