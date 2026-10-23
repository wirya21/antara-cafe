<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Crypt;

/** Pengaturan toko yang diubah dari panel admin (QRIS, rekening, WhatsApp). */
#[Fillable(['key', 'value'])]
class Setting extends Model
{
    protected $primaryKey = 'key';

    public $incrementing = false;

    protected $keyType = 'string';

    /** Kunci yang disimpan terenkripsi. */
    private const SECRET = ['fonnte_token'];

    public static function get(string $key, ?string $default = null): ?string
    {
        $all = Cache::rememberForever('settings', fn () => static::pluck('value', 'key')->all());
        $value = $all[$key] ?? null;

        if ($value !== null && in_array($key, self::SECRET, true)) {
            $value = rescue(fn () => Crypt::decryptString($value), null, false);
        }

        return $value ?? $default;
    }

    public static function put(string $key, ?string $value): void
    {
        if ($value !== null && in_array($key, self::SECRET, true)) {
            $value = Crypt::encryptString($value);
        }

        static::updateOrCreate(['key' => $key], ['value' => $value]);
        Cache::forget('settings');
    }
}
