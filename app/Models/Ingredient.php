<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCafe;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'unit', 'stock', 'min_stock', 'cost_per_unit'])]
class Ingredient extends Model
{
    use BelongsToCafe;

    public const UNITS = ['gram', 'kg', 'ml', 'liter', 'pcs', 'pack', 'botol', 'shot'];

    protected function casts(): array
    {
        return [
            'stock' => 'float',
            'min_stock' => 'float',
            'cost_per_unit' => 'float',
        ];
    }

    public function products(): BelongsToMany
    {
        return $this->belongsToMany(Product::class)->withPivot('quantity');
    }

    public function movements(): HasMany
    {
        return $this->hasMany(StockMovement::class);
    }

    public function scopeLow(Builder $query): void
    {
        $query->whereColumn('stock', '<=', 'min_stock');
    }

    public function status(): string
    {
        return match (true) {
            $this->stock <= 0 => 'habis',
            $this->stock <= $this->min_stock => 'menipis',
            default => 'aman',
        };
    }
}
