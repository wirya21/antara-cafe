<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['name', 'phone', 'points'])]
class Member extends Model
{
    /** Poin yang didapat: 1 poin setiap kelipatan Rp10.000. */
    public const RUPIAH_PER_POINT = 10000;

    public static function normalizePhone(string $phone): string
    {
        $digits = preg_replace('/\D+/', '', $phone);

        return str_starts_with($digits, '62') ? '0'.substr($digits, 2) : $digits;
    }

    public static function findOrRegister(string $phone, ?string $name): self
    {
        return static::firstOrCreate(
            ['phone' => static::normalizePhone($phone)],
            ['name' => $name ?: 'Member'],
        );
    }

    public function orders(): HasMany
    {
        return $this->hasMany(Order::class);
    }
}
