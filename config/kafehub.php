<?php

return [
    // Domain yang menampilkan halaman depan kafehub (bukan menu kafe).
    'hosts' => array_filter(explode(',', env('KAFEHUB_HOSTS', 'kafehub.id,www.kafehub.id'))),

    // Nomor WhatsApp bawaan untuk calon pengguna; bisa diganti dari panel admin Halaman kafehub.
    'whatsapp' => env('KAFEHUB_WHATSAPP'),
];
