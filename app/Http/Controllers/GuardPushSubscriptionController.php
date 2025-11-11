<?php

namespace App\Http\Controllers;

use App\Models\GuardPushSubscription;
use Illuminate\Http\Request;

class GuardPushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
            'keys.auth' => ['required', 'string'],
            'keys.p256dh' => ['required', 'string'],
        ]);

        GuardPushSubscription::updateOrCreate(
            ['endpoint' => $validated['endpoint']],
            [
                'user_id' => $request->user()->id,
                'auth_token' => $validated['keys']['auth'],
                'p256dh_key' => $validated['keys']['p256dh'],
            ]
        );

        return response()->json([
            'status' => 'subscribed',
        ]);
    }

    public function destroy(Request $request)
    {
        $validated = $request->validate([
            'endpoint' => ['required', 'string'],
        ]);

        GuardPushSubscription::where('endpoint', $validated['endpoint'])
            ->where('user_id', $request->user()->id)
            ->delete();

        return response()->json([
            'status' => 'unsubscribed',
        ]);
    }
}
