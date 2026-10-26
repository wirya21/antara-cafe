<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCafe;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['ingredient_id', 'type', 'quantity', 'stock_after', 'total_cost', 'order_id', 'user_id', 'note'])]
class StockMovement extends Model
{
    use BelongsToCafe;

    public const TYPES = [
        'purchase' => 'Stok masuk',
        'sale' => 'Terjual',
        'adjustment' => 'Opname',
        'waste' => 'Terbuang',
        'void' => 'Batal transaksi',
    ];

    protected function casts(): array
    {
        return [
            'quantity' => 'float',
            'stock_after' => 'float',
        ];
    }

    public function ingredient(): BelongsTo
    {
        return $this->belongsTo(Ingredient::class);
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
