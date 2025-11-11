<?php

namespace App\Http\Controllers;

use App\Models\Subdivision;
use App\Models\User;
use Illuminate\Http\Request;

class SubdivisionUserAssignmentController extends Controller
{
    public function store(Request $request, Subdivision $subdivision)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $subdivision->users()->syncWithoutDetaching([$user->id]);
        $this->syncUserSubdivisionIds($user);

        return redirect()
            ->back()
            ->with('success', "{$user->name} assigned to {$subdivision->name}.");
    }

    public function destroy(Subdivision $subdivision, User $user)
    {
        $subdivision->users()->detach($user->id);
        $this->syncUserSubdivisionIds($user);

        return redirect()
            ->back()
            ->with('success', "{$user->name} removed from {$subdivision->name}.");
    }

    private function syncUserSubdivisionIds(User $user): void
    {
        $ids = $user->subdivisions()->pluck('subdivision_id')->all();
        $user->subdivision_ids = $ids;
        $user->primary_subdivision_id = $ids[0] ?? null;
        $user->save();
    }
}
