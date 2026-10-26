<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Foundation\Auth\User as Authenticatable;

/** Pengelola kafehub.id; masuk lewat kafehub.id/panel, bukan lewat login kafe. */
#[Fillable(['name', 'username', 'password'])]
#[Hidden(['password', 'remember_token'])]
class KafehubAdmin extends Authenticatable
{
    protected function casts(): array
    {
        return ['password' => 'hashed'];
    }
}
