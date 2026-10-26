<?php

namespace App\Models;

use App\Models\Concerns\BelongsToCafe;
use App\Support\Busy;
use App\Support\Inventory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\DB;

#[Fillable(['code', 'source', 'status', 'customer_name', 'table_number', 'member_id', 'promotion_id', 'cashier_id', 'subtotal', 'discount', 'total', 'payment_method', 'payment_proof_path', 'paid_at', 'completed_at', 'void_reason', 'voided_by', 'voided_at'])]
class Order extends Model
{
    use BelongsToCafe;

    public const STATUSES = [
        'pending' => 'Menunggu pembayaran',
        'paid' => 'Dibayar',
        'cancelled' => 'Dibatalkan',
    ];

    public static function generateCode(): string
    {
        // Tanpa 0/O/1/I agar mudah dibacakan ke kasir.
        $alphabet = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';

        do {
            $code = 'A-'.collect(range(1, 5))->map(fn () => $alphabet[random_int(0, strlen($alphabet) - 1)])->implode('');
        } while (static::withoutGlobalScope('cafe')->where('code', $code)->exists());

        return $code;
    }

    public const PAYMENT_METHODS = [
        'tunai' => 'Tunai',
        'qris' => 'QRIS',
        'transfer' => 'Transfer',
        'kartu' => 'Kartu debit/kredit',
    ];

    /** Pilihan bayar untuk pelanggan yang memesan sendiri. Tunai hanya diterima di kasir. */
    public const SELF_PAYMENT_METHODS = [
        'tunai' => 'Tunai di kasir',
        'qris' => 'QRIS',
        'transfer' => 'Transfer bank',
    ];

    /** Pilihan bayar yang benar-benar bisa dipakai: QRIS/transfer hanya bila sudah diisi di Pengaturan. */
    public static function availableSelfPaymentMethods(): array
    {
        return array_filter(self::SELF_PAYMENT_METHODS, fn ($label, $method) => match ($method) {
            'qris' => filled(Setting::get('qris_image_path')),
            'transfer' => filled(Setting::get('bank_accounts')),
            default => true,
        }, ARRAY_FILTER_USE_BOTH);
    }

    /** Tahap pesanan untuk tampilan: menunggu bayar, sedang dibuat, selesai, batal. */
    public function stage(): string
    {
        return match (true) {
            $this->status === 'pending' => 'pending',
            $this->status === 'paid' && $this->completed_at === null => 'processing',
            $this->status === 'paid' => 'completed',
            default => 'cancelled',
        };
    }

    /** Label yang dilihat pelanggan. */
    public const CUSTOMER_STAGES = [
        'pending' => 'Menunggu pembayaran',
        'processing' => 'Sedang dibuat',
        'completed' => 'Siap · selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public const STAGES = [
        'pending' => 'Menunggu pembayaran',
        'processing' => 'Dibayar · sedang dibuat',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];

    public function needsProof(): bool
    {
        return $this->status === 'pending' && in_array($this->payment_method, ['qris', 'transfer'], true);
    }

    public function markCompleted(): void
    {
        abort_unless($this->status === 'paid', 409, 'Pesanan belum dibayar.');

        if ($this->completed_at === null) {
            $this->update(['completed_at' => now()]);
        }
    }

    public function markPaid(string $paymentMethod, User $cashier): void
    {
        DB::transaction(function () use ($paymentMethod, $cashier) {
            // Cegah dua kasir membayar pesanan yang sama bersamaan, dan pesanan batal ikut terbayar.
            $updated = static::whereKey($this->id)->where('status', 'pending')->update([
                'status' => 'paid',
                'payment_method' => $paymentMethod,
                'cashier_id' => $cashier->id,
                'paid_at' => now(),
            ]);

            abort_if($updated === 0, 409, 'Pesanan ini sudah dibayar atau dibatalkan.');

            $this->refresh();
            app(Inventory::class)->recordSale($this);
            $this->member?->increment('points', intdiv($this->total, Member::RUPIAH_PER_POINT));
        });
    }

    /** Batalkan pesanan yang sudah dibayar (void) dan kembalikan stok bahannya. */
    public function void(User $by, string $reason): void
    {
        DB::transaction(function () use ($by, $reason) {
            $this->update(['status' => 'cancelled', 'void_reason' => $reason, 'voided_by' => $by->id, 'voided_at' => now()]);
            app(Inventory::class)->reverseSale($this, $by);

            if ($this->member) {
                $this->member->points = max(0, $this->member->points - intdiv($this->total, Member::RUPIAH_PER_POINT));
                $this->member->save();
            }
        });
    }

    /**
     * Perkiraan menit sampai pesanan siap sejak dibayar: menu terlama di pesanan,
     * ditambah tambahan waktu saat mode ramai aktif. Null bila belum ada perkiraan.
     */
    public function estimatedMinutes(): ?int
    {
        $this->loadMissing('items.product');
        $longest = $this->items->max(fn ($item) => $item->product?->prep_minutes);
        $extra = Busy::extraMinutes();

        return $longest || $extra ? (int) $longest + $extra : null;
    }

    public function getRouteKeyName(): string
    {
        return 'code';
    }

    protected function casts(): array
    {
        return [
            'paid_at' => 'datetime',
            'completed_at' => 'datetime',
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
