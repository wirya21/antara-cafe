<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCafe;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

#[Fillable(['title', 'description', 'image_path', 'discount_type', 'discount_value', 'voucher_code', 'min_purchase', 'starts_at', 'ends_at', 'is_active', 'show_popup'])]
class Promotion extends Model
{
    use BelongsToCafe;

    protected function casts(): array
    {
        return [
            'starts_at' => 'datetime',
            'ends_at' => 'datetime',
            'is_active' => 'boolean',
            'show_popup' => 'boolean',
        ];
    }

    public const DISCOUNT_TYPES = [
        'percent' => 'Persen (%)',
        'fixed' => 'Potongan rupiah',
    ];

    public function discountFor(int $subtotal): int
    {
        if (! $this->discount_type || $subtotal < $this->min_purchase) {
            return 0;
        }

        return min($subtotal, $this->discount_type === 'percent'
            ? intdiv($subtotal * min($this->discount_value, 100), 100)
            : $this->discount_value);
    }

    /** Promo berjalan tanpa kode voucher yang otomatis berlaku untuk semua pesanan. */
    public static function bestAutomaticFor(int $subtotal): ?self
    {
        return static::running()->whereNotNull('discount_type')->whereNull('voucher_code')->get()
            ->filter(fn (self $promo) => $promo->discountFor($subtotal) > 0)
            ->sortByDesc(fn (self $promo) => $promo->discountFor($subtotal))
            ->first();
    }

    public static function findVoucher(string $code): ?self
    {
        return static::running()->whereNotNull('discount_type')->where('voucher_code', strtoupper(trim($code)))->first();
    }

    public function summary(): string
    {
        $value = $this->discount_type === 'percent'
            ? "{$this->discount_value}%"
            : 'Rp'.number_format($this->discount_value, 0, ',', '.');

        return "Diskon {$value}".($this->min_purchase ? ' min. belanja Rp'.number_format($this->min_purchase, 0, ',', '.') : '');
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function imageUrl(): ?string
    {
        return $this->image_path ? Storage::disk('public')->url($this->image_path) : null;
    }

    public function isRunning(): bool
    {
        return $this->is_active
            && (! $this->starts_at || $this->starts_at->lte(now()))
            && (! $this->ends_at || $this->ends_at->gte(now()));
    }

    public function scopeRunning(Builder $query): void
    {
        $query->where('is_active', true)
            ->where(fn ($q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', now()))
            ->where(fn ($q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', now()));
    }
}
