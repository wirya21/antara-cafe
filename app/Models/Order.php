<?php

namespace App\Models;

use App\Support\Inventory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['code', 'source', 'status', 'customer_name', 'table_number', 'member_id', 'promotion_id', 'cashier_id', 'subtotal', 'discount', 'total', 'payment_method', 'paid_at', 'void_reason', 'voided_by', 'voided_at'])]
class Order extends Model
{
    public const STATUSES = [
        'pending' => 'Menunggu kasir',
        'paid' => 'Dibayar',
        'cancelled' => 'Dibatalkan',
    ];

    public static function generateCode(): string
    {
        // Tanpa 0/O/1/I agar mudah dibacakan ke kasir.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = 'A-'.collect(range(1, 5))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (static::where('code', $code)->exists());

        return $code;
    }

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'kartu' => 'Kartu debit/kredit',
    ];

    public function markPaid(string $paymentMethod, User $cashier): void
    {
        DB::transaction(function () use ($paymentMethod, $cashier) {
            // Cegah dua kasir membayar pesanan yang sama bersamaan.
            $updated = static::whereKey($this->id)->where('status', '!=', 'paid')->update([
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'cashier_id' => $cashier->id,
                'paid_at' => now(),
            ]);

            abort_if($updated === 0, 409, 'Pesanan ini sudah dibayar.');

            $this->refresh();
            app(Inventory::class)->recordSale($this);
        });
    }

    /** Batalkan pesanan yang sudah dibayar (void) dan kembalikan stok bahannya. */
    public function void(User $by, string $reason): void
    {
        DB::transaction(function () use ($by, $reason) {
            $this->update(['status' => 'cancelled', 'void_reason' => $reason, 'voided_by' => $by->id, 'voided_at' => now()]);
            app(Inventory::class)->reverseSale($this, $by);
        });
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'voided_at' => 'datetime',
        ];
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function member(): BelongsTo
    {
        return $this->belongsTo(Member::class);
    }

    public function promotion(): BelongsTo
    {
        return $this->belongsTo(Promotion::class);
    }

    public function cashier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'cashier_id');
    }
}
