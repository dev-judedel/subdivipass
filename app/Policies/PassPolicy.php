<?php

namespace App\Policies;

use App\Models\Pass;
use App\Models\User;

class PassPolicy
{
    /**
     * Super-admin bypass.
     */
    public function before(User $user, string $ability): ?bool
    {
        if ($user->hasRole('super-admin')) {
            return true;
        }

        return null;
    }

    public function view(?User $user, Pass $pass): bool
    {
        return $this->canAccessSubdivision($user, $pass);
    }

    public function update(User $user, Pass $pass): bool
    {
        if ($user->can('edit passes')) {
            return $this->canAccessSubdivision($user, $pass);
        }

        return false;
    }

    public function delete(User $user, Pass $pass): bool
    {
        return $user->can('delete passes') && $this->canAccessSubdivision($user, $pass);
    }

    public function approve(User $user, Pass $pass): bool
    {
        return $user->can('approve passes') && $this->canAccessSubdivision($user, $pass);
    }

    public function revoke(User $user, Pass $pass): bool
    {
        return $user->can('revoke passes') && $this->canAccessSubdivision($user, $pass);
    }

    protected function canAccessSubdivision(?User $user, Pass $pass): bool
    {
        if (!$user) {
            return false;
        }

        // Admin/employee can only view passes for their subdivisions
        if ($user->hasRole('admin') || $user->hasRole('employee')) {
            $subdivisionIds = is_array($user->subdivision_ids) ? $user->subdivision_ids : json_decode($user->subdivision_ids, true) ?? [];
            return in_array($pass->subdivision_id, $subdivisionIds);
        }

        // Guards can view passes in their assigned gates' subdivisions
        if ($user->hasRole('guard')) {
            $subdivisionId = $user->primary_subdivision_id;
            if ($subdivisionId) {
                return $pass->subdivision_id === $subdivisionId;
            }

            $gateIds = is_array($user->gate_ids) ? $user->gate_ids : json_decode($user->gate_ids, true) ?? [];
            return !empty($gateIds);
        }

        // Requesters can see their own passes
        if ($user->hasRole('requester')) {
            return $pass->requester_id === $user->id;
        }

        return false;
    }
}
