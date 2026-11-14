<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['order_id', 'product_id', 'product_name', 'price', 'cost', 'quantity', 'subtotal', 'note', 'addons'])]
class OrderItem extends Model
{
    protected function casts(): array
    {
        return ['addons' => 'array'];
    }

    /** Nama topping yang dipilih, mis. "+ Bakso, + Extra telur"; kosong bila tidak ada. */
    public function addonText(): string
    {
        return collect($this->addons ?? [])->map(fn ($a) => '+ '.$a['name'])->implode(', ');
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function product(): BelongsTo
    {
        return $this->belongsTo(Product::class);
    }
}
