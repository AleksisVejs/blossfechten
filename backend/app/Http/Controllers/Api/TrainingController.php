<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Registration;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class TrainingController extends Controller
{
    public function index(Request $request)
    {
        $sessions = TrainingSession::query()
            ->where('starts_at', '>=', now()->subHours(3))
            ->orderBy('starts_at')
            ->withCount(['registrations as confirmed_count' => function ($q) {
                $q->where('status', 'confirmed');
            }])
            ->get()
            ->map(function ($s) use ($request) {
                $arr = $s->toArray();
                $arr['is_registered'] = $request->user()
                    ? $s->registrations()->where('user_id', $request->user()->id)->where('status', '!=', 'cancelled')->exists()
                    : false;
                return $arr;
            });

        return response()->json(['data' => $sessions]);
    }

    public function show(TrainingSession $training)
    {
        $training->load(['registrations.user:id,name,rank']);
        return response()->json(['data' => $training]);
    }

    public function register(Request $request, TrainingSession $training)
    {
        $user = $request->user();

        if ($training->cancelled) {
            return response()->json(['message' => __('messages.training.cancelled')], 422);
        }
        if ($training->starts_at->isPast()) {
            return response()->json(['message' => __('messages.training.already_started')], 422);
        }

        $status = $training->confirmedCount() >= $training->capacity ? 'waitlist' : 'confirmed';

        $registration = Registration::updateOrCreate(
            ['user_id' => $user->id, 'training_session_id' => $training->id],
            ['status' => $status, 'note' => $request->input('note')]
        );

        return response()->json(['data' => $registration], 201);
    }

    public function unregister(Request $request, TrainingSession $training)
    {
        Registration::where('user_id', $request->user()->id)
            ->where('training_session_id', $training->id)
            ->delete();
        return response()->noContent();
    }

    public function myRegistrations(Request $request)
    {
        $items = Registration::with('trainingSession')
            ->where('user_id', $request->user()->id)
            ->whereHas('trainingSession', fn($q) => $q->where('starts_at', '>=', now()->subWeek()))
            ->get();
        return response()->json(['data' => $items]);
    }
}
