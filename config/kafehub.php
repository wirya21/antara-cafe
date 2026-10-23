<?php

return [
    // Domain yang menampilkan halaman depan kafehub (bukan menu kafe).
    'hosts' => array_filter(explode(',', env('KAFEHUB_HOSTS', 'kafehub.id,www.kafehub.id'))),

    // Nomor WhatsApp untuk calon pengguna, format 62xxxx. Kosong = tombol diarahkan ke demo.
    'whatsapp' => env('KAFEHUB_WHATSAPP'),

    // Kafe yang sudah memakai sistem ini, ditampilkan di halaman depan.
    'showcase' => [
        ['name' => 'Antara Cafe', 'url' => 'https://antara.kafehub.id'],
    ],
];
