<?php

namespace App\Policies;

use App\Models\User;
use Spatie\Permission\Models\Role;

class RolePolicy
{
    /**
     * Allow super-admin to bypass checks.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin') && $ability !== 'delete') {
            return true;
        }

        return null;
    }

    public function viewAny(User $user): bool
    {
        return $user->can('manage user roles');
    }

    public function view(User $user, Role $role): bool
    {
        return $user->can('manage user roles');
    }

    public function create(User $user): bool
    {
        return $user->can('manage user roles');
    }

    public function update(User $user, Role $role): bool
    {
        return $user->can('manage user roles');
    }

    public function delete(User $user, Role $role): bool
    {
        // Prevent deleting the default super-admin role unless you are super-admin (handled in before)
        if ($role->name === 'super-admin') {
            return false;
        }

        return $user->can('manage user roles');
    }
}
