<?php

use App\Models\KafehubAdmin;
use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Str;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Membuat akun pengelola kafehub.id, atau mengganti password-nya bila sudah ada.
Artisan::command('kafehub:akun {username=kafehub}', function (string $username) {
    $password = Str::password(14, symbols: false);
    KafehubAdmin::updateOrCreate(['username' => $username], ['name' => 'Pengelola kafehub', 'password' => $password]);
    $this->info("Akun panel kafehub.id: {$username} / {$password} (ganti setelah masuk)");
})->purpose('Buat akun pengelola kafehub.id atau atur ulang password-nya');
