<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\JsonResponse;

class NotificationPreferenceController extends Controller
{
    /**
     * Token-based opt-out. Deliberately public: an unsubscribe link that
     * demands a login is not a working unsubscribe, and mailbox providers
     * fetch this endpoint directly for one-click List-Unsubscribe.
     */
    public function unsubscribe(string $token): JsonResponse
    {
        $user = strlen($token) >= 32
            ? User::query()->where('unsubscribe_token', $token)->first()
            : null;

        if ($user === null) {
            return response()->json([
                'message' => __('messages.notifications.invalid_unsubscribe_token'),
            ], 404);
        }

        if ($user->notify_new_events) {
            $user->forceFill(['notify_new_events' => false])->save();
        }

        return response()->json([
            'message' => __('messages.notifications.unsubscribed'),
            'email' => $user->email,
        ]);
    }
}
