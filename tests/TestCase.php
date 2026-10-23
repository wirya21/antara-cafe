<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI tidak menjalankan `npm run build`, jadi aset Vite tidak perlu ada saat tes.
        $this->withoutVite();
    }
}
