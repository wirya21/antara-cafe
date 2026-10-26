<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/** Kafe yang memakai kafehub, masing-masing di {slug}.kafehub.id. */
#[Fillable(['name', 'slug', 'owner_name', 'owner_phone', 'city', 'is_active', 'is_trial', 'active_until', 'monthly_fee', 'notes'])]
class Cafe extends Model
{
    /** Subdomain yang tidak boleh dipakai kafe. */
    public const RESERVED = ['www', 'mail', 'admin', 'panel', 'api', 'app', 'kafehub', 'ftp', 'cpanel', 'webmail', 'ns1', 'ns2'];

    public const STATUSES = [
        'aktif' => ['Aktif', 'bg-green-50 text-green-700 ring-green-600/20'],
        'trial' => ['Masa coba', 'bg-blue-50 text-blue-700 ring-blue-600/20'],
        'segera' => ['Segera berakhir', 'bg-amber-50 text-amber-700 ring-amber-600/20'],
        'berakhir' => ['Berakhir', 'bg-red-50 text-red-700 ring-red-600/20'],
        'nonaktif' => ['Nonaktif', 'bg-gray-100 text-gray-600 ring-gray-500/20'],
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
            'is_trial' => 'boolean',
            'active_until' => 'date',
            'monthly_fee' => 'integer',
        ];
    }

    public static function findBySlug(string $slug): ?self
    {
        return static::where('slug', strtolower($slug))->first();
    }

    /** Kafe bisa dibuka pelanggan dan stafnya: aktif dan masa langganan belum lewat. */
    public function isOpen(): bool
    {
        return $this->is_active && ($this->active_until === null || ! $this->active_until->endOfDay()->isPast());
    }

    public function status(): string
    {
        return match (true) {
            ! $this->is_active => 'nonaktif',
            ! $this->isOpen() => 'berakhir',
            $this->is_trial => 'trial',
            $this->active_until !== null && $this->daysLeft() <= 7 => 'segera',
            default => 'aktif',
        };
    }

    public function daysLeft(): ?int
    {
        return $this->active_until ? (int) now()->startOfDay()->diffInDays($this->active_until, false) : null;
    }

    public function url(string $path = '/'): string
    {
        $scheme = parse_url((string) config('app.url'), PHP_URL_SCHEME) ?: 'https';

        return $scheme.'://'.$this->slug.'.'.config('kafehub.domain').($path === '/' ? '' : '/'.ltrim($path, '/'));
    }

    /** Nama untuk logo: kata terakhir diberi warna aksen bila namanya lebih dari satu kata. */
    public function brandParts(): array
    {
        $words = preg_split('/\s+/', trim($this->name));

        return count($words) > 1 ? [implode(' ', array_slice($words, 0, -1)), end($words)] : [$this->name, ''];
    }

    public function users(): HasMany
    {
        return $this->hasMany(User::class);
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }

    public function products(): HasMany
    {
        return $this->hasMany(Product::class);
    }
}
