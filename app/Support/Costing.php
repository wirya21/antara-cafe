<?php

namespace App\Support;

use App\Models\Setting;

/**
 * Biaya operasional bulanan (gaji, listrik, dll.) yang dibebankan ke setiap porsi
 * supaya harga pokok tidak hanya menghitung bahan.
 */
class Costing
{
    public const OVERHEADS = [
        'op_salary' => 'Gaji karyawan',
        'op_utilities' => 'Listrik, air, gas, internet',
        'op_rent' => 'Sewa tempat',
        'op_transport' => 'Bensin & transport',
        'op_other' => 'Tak terduga & lainnya',
    ];

    /** @return array<string, int> */
    public static function overheads(): array
    {
        return collect(self::OVERHEADS)->map(fn ($label, $key) => (int) Setting::get($key, '0'))->all();
    }

    public static function monthlyOverhead(): int
    {
        return array_sum(self::overheads());
    }

    public static function monthlyServings(): int
    {
        return max(0, (int) Setting::get('op_monthly_servings', '0'));
    }

    /** Beban operasional per porsi, 0 bila perkiraan porsi per bulan belum diisi. */
    public static function overheadPerServing(): int
    {
        $servings = self::monthlyServings();

        return $servings > 0 ? (int) round(self::monthlyOverhead() / $servings) : 0;
    }

    /** Beban operasional untuk rentang hari tertentu (dipakai laporan laba bersih). */
    public static function overheadForDays(float $days): int
    {
        return (int) round(self::monthlyOverhead() * $days / 30);
    }
}
