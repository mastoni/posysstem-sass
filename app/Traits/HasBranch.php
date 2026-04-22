<?php

namespace App\Traits;

use App\Models\Scopes\BranchScope;

trait HasBranch
{
    protected static function bootHasBranch()
    {
        static::addGlobalScope(new BranchScope);

        static::creating(function ($model) {
            if (auth()->check() && !isset($model->branch_id)) {
                $user = auth()->user();
                if ($user->role === 'cashier' && $user->branch_id) {
                    $model->branch_id = $user->branch_id;
                }
            }
        });
    }
}
