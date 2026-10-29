<?php

namespace Tests\Unit;

use App\Support\Qris;
use PHPUnit\Framework\TestCase;

class QrisTest extends TestCase
{
    public const STATIC = '00020101021126570011ID.DANA.WWW011893600915300000000102090000000010303UMI51440014ID.CO.QRIS.WWW0215ID10200000000000303UMI5204581253033605802ID5911ANTARA CAFE6008DENPASAR61058011162070703A01630430E6';

    public function test_static_code_is_validated_by_its_crc(): void
    {
        $this->assertTrue(Qris::isValid(self::STATIC));
        $this->assertFalse(Qris::isValid(substr(self::STATIC, 0, -1).'7'));
        $this->assertFalse(Qris::isValid('https://contoh.com'));
        $this->assertFalse(Qris::isValid(null));
    }

    public function test_amount_is_embedded_as_a_dynamic_code(): void
    {
        $code = Qris::withAmount(self::STATIC, 28000);

        $this->assertSame('00020101021226570011ID.DANA.WWW011893600915300000000102090000000010303UMI51440014ID.CO.QRIS.WWW0215ID10200000000000303UMI5204581253033605405280005802ID5911ANTARA CAFE6008DENPASAR61058011162070703A016304840C', $code);
        $this->assertTrue(Qris::isValid($code));
        $this->assertNull(Qris::withAmount('rusak', 28000));
    }
}
