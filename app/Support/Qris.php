<?php

namespace App\Support;

/**
 * Mengubah kode QRIS statis kafe menjadi QRIS dengan nominal terisi,
 * sehingga pelanggan cukup scan lalu bayar tanpa mengetik angka.
 */
class Qris
{
    /** Kode QRIS statis yang sah: format EMV, berakhir dengan CRC yang cocok. */
    public static function isValid(?string $payload): bool
    {
        $payload = trim((string) $payload);

        if (! str_starts_with($payload, '000201') || strlen($payload) < 30 || substr($payload, -8, 4) !== '6304') {
            return false;
        }

        return self::parse($payload) !== null && self::crc(substr($payload, 0, -4)) === strtoupper(substr($payload, -4));
    }

    /** Kode QRIS dengan nominal (Rupiah) tertanam; null bila kode statisnya tidak sah. */
    public static function withAmount(?string $payload, int $amount): ?string
    {
        if (! self::isValid($payload) || $amount <= 0) {
            return null;
        }

        $tags = self::parse(trim($payload));
        unset($tags['63']);
        $tags['01'] = '12'; // 11 = statis, 12 = dinamis (sekali bayar)
        $tags['54'] = (string) $amount;
        ksort($tags, SORT_STRING);

        $body = '';
        foreach ($tags as $id => $value) {
            $body .= $id.str_pad((string) strlen($value), 2, '0', STR_PAD_LEFT).$value;
        }
        $body .= '6304';

        return $body.self::crc($body);
    }

    /** @return array<string, string>|null tag tingkat atas => nilainya */
    private static function parse(string $payload): ?array
    {
        $tags = [];
        $i = 0;

        while ($i < strlen($payload)) {
            $id = substr($payload, $i, 2);
            $length = substr($payload, $i + 2, 2);
            if (! ctype_digit($id) || ! ctype_digit($length) || $i + 4 + (int) $length > strlen($payload)) {
                return null;
            }
            $tags[$id] = substr($payload, $i + 4, (int) $length);
            $i += 4 + (int) $length;
        }

        return $tags;
    }

    /** CRC16-CCITT (poly 0x1021, awal 0xFFFF) sesuai standar QRIS. */
    private static function crc(string $data): string
    {
        $crc = 0xFFFF;
        foreach (str_split($data) as $char) {
            $crc ^= ord($char) << 8;
            for ($bit = 0; $bit < 8; $bit++) {
                $crc = ($crc & 0x8000) ? (($crc << 1) ^ 0x1021) : ($crc << 1);
                $crc &= 0xFFFF;
            }
        }

        return sprintf('%04X', $crc);
    }
}
