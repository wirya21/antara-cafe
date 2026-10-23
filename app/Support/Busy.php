<?php

namespace App\Support;

use App\Models\Setting;

/** Mode ramai: pelanggan diberi tahu pesanan mungkin sedikit terlambat. */
class Busy
{
    public const DEFAULT_MESSAGE = 'Pesanan sedang ramai, mohon maaf pesanan Anda mungkin sedikit terlambat.';

    public static function on(): bool
    {
        return Setting::get('busy_mode') === '1';
    }

    public static function message(): ?string
    {
        return self::on() ? (Setting::get('busy_message') ?: self::DEFAULT_MESSAGE) : null;
    }

    public static function extraMinutes(): int
    {
        return self::on() ? max(0, (int) Setting::get('busy_extra_minutes', '10')) : 0;
    }
}
