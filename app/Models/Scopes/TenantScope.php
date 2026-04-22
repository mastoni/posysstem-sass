<?php

namespace App\Models\Scopes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Scope;
use Illuminate\Support\Facades\Auth;

class TenantScope implements Scope
{
    /**
     * Apply the scope to a given Eloquent query builder.
     */
    public function apply(Builder $builder, Model $model): void
    {
        if (Auth::check()) {
            $user = Auth::user();
            
            // Superadmin can see everything
            if ($user->role === 'superadmin') {
                return;
            }

            // For Tenants (Owners), filter by their own ID
            if ($user->role === 'tenant') {
                $builder->where($model->getTable() . '.user_id', $user->id);
            }
            
            // For Cashiers, filter by their Owner's ID
            if ($user->role === 'cashier') {
                $builder->where($model->getTable() . '.user_id', $user->owner_id);
            }
        }
    }
}
