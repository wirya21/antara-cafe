<?php

namespace App\Support;

use App\Models\Cafe;

/**
 * Kafe yang sedang dilayani permintaan ini, dikenali dari subdomain.
 * Kosong di kafehub.id (panel pengelola) sehingga data semua kafe terlihat.
 */
class Tenant
{
    public static function set(?Cafe $cafe): void
    {
        app()->instance('tenant.cafe', $cafe);
    }

    public static function cafe(): ?Cafe
    {
        return app()->bound('tenant.cafe') ? app('tenant.cafe') : null;
    }

    public static function id(): ?int
    {
        return self::cafe()?->id;
    }

    /** Jalankan sesuatu atas nama kafe tertentu, lalu kembalikan kafe sebelumnya. */
    public static function run(?Cafe $cafe, callable $callback): mixed
    {
        $previous = self::cafe();
        self::set($cafe);

        try {
            return $callback();
        } finally {
            self::set($previous);
        }
    }
}
