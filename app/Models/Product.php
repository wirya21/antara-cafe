<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCafe;
use App\Support\Costing;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['category_id', 'name', 'slug', 'description', 'price', 'prep_minutes', 'manual_cost', 'image_path', 'is_available', 'is_featured', 'sort_order'])]
class Product extends Model
{
    use BelongsToCafe;

    protected function casts(): array
    {
        return [
            'price' => 'integer',
            'prep_minutes' => 'integer',
            'manual_cost' => 'integer',
            'is_available' => 'boolean',
            'is_featured' => 'boolean',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
    }

    public function ingredients(): BelongsToMany
    {
        return $this->belongsToMany(Ingredient::class)->withPivot('quantity');
    }

    /** HPP bahan per porsi: dari resep, atau harga pokok manual bila menu tidak punya resep. */
    public function estimatedCost(): int
    {
        if ($this->ingredients->isEmpty()) {
            return (int) $this->manual_cost;
        }

        return (int) round($this->ingredients->sum(fn ($i) => $i->pivot->quantity * $i->cost_per_unit));
    }

    /** Harga pokok lengkap: bahan + beban operasional per porsi. */
    public function fullCost(): int
    {
        return $this->estimatedCost() + Costing::overheadPerServing();
    }

    /** Batas "hampir habis" untuk label "Sisa N" di menu. */
    public const LOW_SERVINGS = 5;

    /**
     * Berapa porsi lagi yang bisa dibuat dari stok bahan saat ini.
     * Null bila produk belum punya resep (stok tidak bisa dihitung).
     */
    public function servingsLeft(): ?int
    {
        $limits = $this->ingredients
            ->filter(fn ($i) => $i->pivot->quantity > 0)
            ->map(fn ($i) => (int) floor(max($i->stock, 0) / $i->pivot->quantity + 1e-9));

        return $limits->isEmpty() ? null : $limits->min();
    }

    /** Bahan yang paling membatasi jumlah porsi. */
    public function limitingIngredient(): ?Ingredient
    {
        return $this->ingredients
            ->filter(fn ($i) => $i->pivot->quantity > 0)
            ->sortBy(fn ($i) => max($i->stock, 0) / $i->pivot->quantity)
            ->first();
    }

    /** Bisa dipesan: ditandai tersedia dan stok bahannya cukup untuk minimal satu porsi. */
    public function isOrderable(): bool
    {
        return $this->is_available && $this->servingsLeft() !== 0;
    }

    public function soldOutByStock(): bool
    {
        return $this->is_available && $this->servingsLeft() === 0;
    }

    protected function imageUrl(): Attribute
    {
        return Attribute::get(fn () => $this->image_path ? Storage::disk('public')->url($this->image_path) : null);
    }
}
