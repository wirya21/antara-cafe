<?php

namespace Tests;

use App\Models\Cafe;
use App\Support\Tenant;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // CI tidak menjalankan `npm run build`, jadi aset Vite tidak perlu ada saat tes.
        $this->withoutVite();

        // Tes berjalan atas nama kafe pertama, seperti localhost saat pengembangan.
        if (in_array(RefreshDatabase::class, class_uses_recursive($this), true)) {
            $this->cafe = Cafe::create(['name' => 'Antara Cafe', 'slug' => 'antara']);
            Tenant::set($this->cafe);
        }
    }

    protected ?Cafe $cafe = null;
}
