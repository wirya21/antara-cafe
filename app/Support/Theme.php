<?php

namespace App\Support;

use App\Models\Setting;

/** Warna tema kafe. Mengganti variabel warna "espresso" yang dipakai semua halaman. */
class Theme
{
    public const DEFAULT = 'espresso';

    /** Warna utama tiap pilihan; skala terang-gelap dibuat otomatis dari warna ini. */
    public const PRESETS = [
        'espresso' => ['label' => 'Espresso (bawaan)', 'color' => '#a8743f'],
        'matcha' => ['label' => 'Hijau matcha', 'color' => '#5f8a3b'],
        'laut' => ['label' => 'Biru laut', 'color' => '#2f6f9f'],
        'bata' => ['label' => 'Merah bata', 'color' => '#b4533a'],
        'ungu' => ['label' => 'Ungu taro', 'color' => '#7a5aa6'],
        'arang' => ['label' => 'Arang elegan', 'color' => '#6b6f76'],
    ];

    public static function key(): string
    {
        $key = Setting::get('theme', self::DEFAULT);

        return $key === 'custom' || isset(self::PRESETS[$key]) ? $key : self::DEFAULT;
    }

    public static function color(): string
    {
        $custom = Setting::get('theme_color');

        return self::key() === 'custom' && self::isHex($custom)
            ? strtolower($custom)
            : self::PRESETS[self::key() === 'custom' ? self::DEFAULT : self::key()]['color'];
    }

    public static function isHex(?string $value): bool
    {
        return is_string($value) && preg_match('/^#[0-9a-fA-F]{6}$/', $value) === 1;
    }

    /** CSS yang menimpa skala warna bawaan; kosong bila memakai tema bawaan. */
    public static function css(): string
    {
        if (self::key() === self::DEFAULT) {
            return '';
        }

        $c = self::color();
        $scale = [
            50 => "color-mix(in oklab, {$c} 6%, white)",
            100 => "color-mix(in oklab, {$c} 15%, white)",
            200 => "color-mix(in oklab, {$c} 32%, white)",
            300 => "color-mix(in oklab, {$c} 55%, white)",
            400 => "color-mix(in oklab, {$c} 78%, white)",
            500 => $c,
            600 => "color-mix(in oklab, {$c} 82%, black)",
            700 => "color-mix(in oklab, {$c} 64%, black)",
            800 => "color-mix(in oklab, {$c} 44%, black)",
            900 => "color-mix(in oklab, {$c} 26%, black)",
        ];

        return ':root{'.collect($scale)->map(fn ($v, $k) => "--color-espresso-{$k}:{$v};")->join('').'}';
    }
}
