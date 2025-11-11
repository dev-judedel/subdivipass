<?php

namespace App\Http\Controllers;

use App\Models\Gate;
use App\Models\User;
use Illuminate\Http\Request;

class GateGuardAssignmentController extends Controller
{
    public function store(Request $request, Gate $gate)
    {
        $validated = $request->validate([
            'user_id' => ['required', 'exists:users,id'],
        ]);

        $user = User::findOrFail($validated['user_id']);

        $gate->guards()->syncWithoutDetaching([$user->id]);
        $this->syncUserGateIds($user);

        return redirect()
            ->back()
            ->with('success', "{$user->name} assigned to {$gate->name}.");
    }

    public function destroy(Gate $gate, User $user)
    {
        $gate->guards()->detach($user->id);
        $this->syncUserGateIds($user);

        return redirect()
            ->back()
            ->with('success', "{$user->name} removed from {$gate->name}.");
    }

    private function syncUserGateIds(User $user): void
    {
        $ids = $user->gates()->pluck('gate_id')->all();
        $user->gate_ids = $ids;
        $user->save();
    }
}
