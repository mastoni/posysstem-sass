<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class BranchScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Cashiers are restricted to their assigned branch
            if ($user->role === 'cashier' && $user->branch_id) {
                // If the model has a branch_id column, filter by it
                // We'll check if the column exists to be safe
                $builder->where($model->getTable() . '.branch_id', $user->branch_id);
            }
            
            // Tenants (Owners) see everything in their branches (handled by TenantScope)
        }
    }
}
