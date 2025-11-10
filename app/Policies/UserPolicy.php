<?php

namespace App\Policies;

use App\Models\User;
use Illuminate\Auth\Access\Response;

class UserPolicy
{
    /**
     * Perform pre-authorization checks.
     * Super-admin can do anything.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null; // Continue with normal authorization
    }

    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->can('view users');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, User $model): bool
    {
        // Users can view their own profile or if they have permission
        return $user->id === $model->id || $user->can('view users');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create users');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, User $model): bool
    {
        // Users can update their own profile
        if ($user->id === $model->id) {
            return true;
        }

        // Admin can update users
        if (!$user->can('edit users')) {
            return false;
        }

        // Super admin can update anyone
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin cannot edit super-admin accounts
        if ($model->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, User $model): bool
    {
        // Cannot delete yourself
        if ($user->id === $model->id) {
            return false;
        }

        // Must have permission
        if (!$user->can('delete users')) {
            return false;
        }

        // Super admin can delete anyone except other super admins
        if ($user->hasRole('super-admin')) {
            return !$model->hasRole('super-admin') || $user->id !== $model->id;
        }

        // Admin cannot delete super-admin accounts
        if ($model->hasRole('super-admin')) {
            return false;
        }

        return true;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, User $model): bool
    {
        return $user->can('delete users');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, User $model): bool
    {
        // Only super-admin can force delete
        return $user->hasRole('super-admin') && $user->id !== $model->id;
    }

    /**
     * Determine whether the user can activate/deactivate the model.
     */
    public function changeStatus(User $user, User $model): bool
    {
        // Cannot change own status
        if ($user->id === $model->id) {
            return false;
        }

        // Must have edit permission
        if (!$user->can('edit users')) {
            return false;
        }

        // Admin cannot change super-admin status
        if ($model->hasRole('super-admin') && !$user->hasRole('super-admin')) {
            return false;
        }

        return true;
    }
}
