<?php

namespace App\Traits;

use App\Models\Scopes\TenantScope;

trait HasTenant
{
    protected static function bootHasTenant()
    {
        static::addGlobalScope(new TenantScope);

        static::creating(function ($model) {
            if (auth()->check() && !isset($model->user_id)) {
                $user = auth()->user();
                if ($user->role === 'tenant') {
                    $model->user_id = $user->id;
                }
            }
        });
    }
}
