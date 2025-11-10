<?php

namespace App\Policies;

use App\Models\User;
use App\Models\PassType;

class PassTypePolicy
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
        return $user->can('view pass-types');
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, PassType $passType): bool
    {
        return $user->can('view pass-types');
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->can('create pass-types');
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, PassType $passType): bool
    {
        // Must have edit permission
        if (!$user->can('edit pass-types')) {
            return false;
        }

        // Super admin can edit any pass type
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin/Employee can only edit pass types from their subdivisions
        if ($user->hasRole('admin') || $user->hasRole('employee')) {
            $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];
            return in_array($passType->subdivision_id, $subdivisionIds);
        }

        return false;
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, PassType $passType): bool
    {
        // Must have delete permission
        if (!$user->can('delete pass-types')) {
            return false;
        }

        // Super admin can delete any pass type
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can only delete pass types from their subdivisions
        if ($user->hasRole('admin')) {
            $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];
            return in_array($passType->subdivision_id, $subdivisionIds);
        }

        return false;
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, PassType $passType): bool
    {
        return $user->can('delete pass-types');
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, PassType $passType): bool
    {
        // Only super-admin can force delete
        return $user->hasRole('super-admin');
    }

    /**
     * Determine whether the user can activate/deactivate the model.
     */
    public function changeStatus(User $user, PassType $passType): bool
    {
        // Must have edit permission
        if (!$user->can('edit pass-types')) {
            return false;
        }

        // Super admin can change any pass type status
        if ($user->hasRole('super-admin')) {
            return true;
        }

        // Admin can only change status for their subdivision's pass types
        if ($user->hasRole('admin')) {
            $subdivisionIds = json_decode($user->subdivision_ids, true) ?? [];
            return in_array($passType->subdivision_id, $subdivisionIds);
        }

        return false;
    }
}
