<?php

namespace App\Support;

/** Ikon garis sederhana (24×24, stroke) untuk halaman kafehub. */
class LineIcon
{
    public const ICONS = [
        'qr' => ['QR', 'M4 4h6v6H4zM14 4h6v6h-6zM4 14h6v6H4zM14 14h2v2h-2zM18 14h2M14 18h2M18 18h2v2'],
        'struk' => ['Struk', 'M6 3h12v18l-3-2-3 2-3-2-3 2zM9 8h6M9 12h6M9 16h3'],
        'lonceng' => ['Lonceng', 'M6 8a6 6 0 1 1 12 0c0 7 3 9 3 9H3s3-2 3-9M10.3 21a1.94 1.94 0 0 0 3.4 0'],
        'kotak' => ['Stok / kotak', 'M21 8l-9-5-9 5 9 5 9-5zM3 8v8l9 5 9-5V8M12 13v8'],
        'grafik' => ['Grafik', 'M3 3v18h18M7 15l4-4 3 3 5-6'],
        'tiket' => ['Tiket / voucher', 'M3 7a1 1 0 0 1 1-1h16a1 1 0 0 1 1 1v3a2 2 0 0 0 0 4v3a1 1 0 0 1-1 1H4a1 1 0 0 1-1-1v-3a2 2 0 0 0 0-4zM14 6v2M14 11v2M14 16v2'],
        'tv' => ['TV', 'M3 6h18v11H3zM8 21h8M12 17v4'],
        'chat' => ['Chat', 'M21 12a8 8 0 0 1-11.6 7.1L4 20l1.1-4.4A8 8 0 1 1 21 12z'],
        'hp' => ['HP', 'M7 2h10a1 1 0 0 1 1 1v18a1 1 0 0 1-1 1H7a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1zM11 18h2'],
        'kilat' => ['Kilat / cepat', 'M13 2 4 14h7l-1 8 9-12h-7z'],
        'dompet' => ['Dompet', 'M3 7h15a3 3 0 0 1 3 3v8a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2zM3 7l12-4v4M17 14h.01'],
        'orang' => ['Orang', 'M16 19v-1a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v1M9 10a3 3 0 1 0 0-6 3 3 0 0 0 0 6zM22 19v-1a4 4 0 0 0-3-3.9M16 4.1a3 3 0 0 1 0 5.8'],
        'bola' => ['Bola dunia', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM3 12h18M12 3a14 14 0 0 1 0 18M12 3a14 14 0 0 0 0 18'],
        'kopi' => ['Kopi', 'M17 8h1a3 3 0 0 1 0 6h-1M3 8h14v6a5 5 0 0 1-5 5H8a5 5 0 0 1-5-5zM7 2v3M11 2v3'],
        'jam' => ['Jam', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM12 7v5l3 2'],
        'perisai' => ['Perisai', 'M12 3l8 3v6c0 5-3.5 8-8 9-4.5-1-8-4-8-9V6zM9 12l2 2 4-4'],
        'masuk' => ['Pesanan masuk', 'M22 12h-6l-2 3h-4l-2-3H2M5.5 5h13L22 12v6a2 2 0 0 1-2 2H4a2 2 0 0 1-2-2v-6z'],
        'centang' => ['Centang', 'M12 21a9 9 0 1 0 0-18 9 9 0 0 0 0 18zM8 12l3 3 5-6'],
        'bintang' => ['Bintang', 'M12 3l2.7 5.6 6.1.9-4.4 4.3 1 6.1L12 17l-5.4 2.9 1-6.1-4.4-4.3 6.1-.9z'],
        'halaman' => ['Halaman', 'M4 4h16v16H4zM4 9h16M9 9v11'],
        'kunci' => ['Kunci', 'M15 7a4 4 0 1 1-3.9 4.9L3 20v-3h3v-3h3l2.1-2.1A4 4 0 0 1 15 7zM16 9h.01'],
        'toko' => ['Toko', 'M3 9l1.5-5h15L21 9M3 9h18v2a3 3 0 0 1-6 0 3 3 0 0 1-6 0 3 3 0 0 1-6 0zM5 13v8h14v-8M10 21v-5h4v5'],
    ];

    public static function path(?string $name): string
    {
        return self::ICONS[$name][1] ?? self::ICONS['bintang'][1];
    }

    public static function options(): array
    {
        return array_map(fn ($icon) => $icon[0], self::ICONS);
    }
}
