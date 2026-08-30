<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class PushSubscriptionController extends Controller
{
    public function store(Request $request)
    {
        $user = auth()->user();
        $user->updatePushSubscription(
            $request->endpoint,
            $request->keys['p256dh'] ?? null,
            $request->keys['auth'] ?? null,
            $request->contentEncoding ?? 'aesgcm'
        );

        return response()->json(['success' => true]);
    }

    public function destroy(Request $request)
    {
        $user = auth()->user();
        $user->deletePushSubscription($request->endpoint);
        return response()->json(['success' => true]);
    }
}
