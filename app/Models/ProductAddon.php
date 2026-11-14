<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Topping berbayar untuk satu menu. */
#[Fillable(['product_id', 'name', 'price', 'sort_order'])]
class ProductAddon extends Model
{
    protected function casts(): array
    {
        return ['price' => 'integer', 'sort_order' => 'integer'];
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
