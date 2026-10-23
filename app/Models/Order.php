<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['code', 'source', 'status', 'customer_name', 'table_number', 'member_id', 'promotion_id', 'cashier_id', 'subtotal', 'discount', 'total', 'payment_method', 'paid_at'])]
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

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
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
