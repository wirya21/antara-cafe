<?php

namespace App\Models\Concerns;

use App\Models\Cafe;
use App\Support\Tenant;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/** Data milik satu kafe: otomatis dibatasi ke kafe aktif dan diberi cafe_id saat dibuat. */
trait BelongsToCafe
{
    protected static function bootBelongsToCafe(): void
    {
        static::addGlobalScope('cafe', function (Builder $query) {
            if ($id = Tenant::id()) {
                $query->where($query->qualifyColumn('cafe_id'), $id);
            }
        });

        static::creating(function ($model) {
            $model->cafe_id ??= Tenant::id();
        });
    }

    public function cafe(): BelongsTo
    {
        return $this->belongsTo(Cafe::class);
    }
}
