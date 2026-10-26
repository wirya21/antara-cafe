<?php

namespace App\Models;

use App\Support\Tenant;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/**
 * Pengaturan tiap kafe yang diubah dari panel admin (QRIS, rekening, WhatsApp).
 * Baris tanpa cafe_id adalah pengaturan kafehub.id sendiri.
 */
#[Fillable(['cafe_id', 'key', 'value'])]
class Setting extends Model
{
    /** Kunci yang disimpan terenkripsi. */
    private const SECRET = ['fonnte_token'];

    /** Pengaturan kafe yang sedang dibuka. */
    public static function get(string $key, ?string $default = null): ?string
    {
        return static::read(Tenant::id(), $key, $default);
    }

    public static function put(string $key, ?string $value): void
    {
        static::write(Tenant::id(), $key, $value);
    }

    /** Pengaturan kafehub.id, sama untuk semua kafe. */
    public static function platform(string $key, ?string $default = null): ?string
    {
        return static::read(null, $key, $default);
    }

    public static function putPlatform(string $key, ?string $value): void
    {
        static::write(null, $key, $value);
    }

    private static function cacheKey(?int $cafeId): string
    {
        return 'settings.'.($cafeId ?? 'platform');
    }

    private static function read(?int $cafeId, string $key, ?string $default): ?string
    {
        $all = Cache::rememberForever(static::cacheKey($cafeId), fn () => static::where('cafe_id', $cafeId)->pluck('value', 'key')->all());
        $value = $all[$key] ?? null;

        if ($value !== null && in_array($key, self::SECRET, true)) {
            $value = rescue(fn () => Crypt::decryptString($value), null, false);
        }

        return $value ?? $default;
    }

    private static function write(?int $cafeId, string $key, ?string $value): void
    {
        if ($value !== null && in_array($key, self::SECRET, true)) {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(['cafe_id' => $cafeId, 'key' => $key], ['value' => $value]);
        Cache::forget(static::cacheKey($cafeId));
    }
}
