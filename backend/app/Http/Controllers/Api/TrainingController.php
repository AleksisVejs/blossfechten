<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Jobs\NotifyPromotedFromWaitlist;
use App\Models\Registration;
use App\Models\TrainingSession;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

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
            ->get();

        // One query for the caller's own registrations rather than two per
        // session — the list is short, but it grows with the calendar.
        $mine = $request->user()
            ? Registration::query()
                ->where('user_id', $request->user()->id)
                ->whereIn('training_session_id', $sessions->pluck('id'))
                ->where('status', '!=', 'cancelled')
                ->pluck('status', 'training_session_id')
            : collect();

        $data = $sessions->map(function (TrainingSession $session) use ($mine) {
            $status = $mine->get($session->id);

            return $session->toArray() + [
                'is_registered' => $status !== null,
                // 'confirmed' or 'waitlist' — a waitlisted member needs to know
                // they do not yet hold a seat.
                'registration_status' => $status,
            ];
        });

        return response()->json(['data' => $data]);
    }

    public function show(TrainingSession $training)
    {
        // Deliberately no registrations: this endpoint is public, and the
        // registration rows carry member names, ranks and their free-text
        // notes. Admins read the attendee list through
        // /api/admin/trainings/{id}/registrations instead.
        return response()->json(['data' => $training->loadCount([
            'registrations as confirmed_count' => fn($q) => $q->where('status', 'confirmed'),
        ])]);
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

        $registration = DB::transaction(function () use ($training, $user, $request) {
            // Lock the session row for the duration: without it two people
            // clicking Register at the same moment both read the same seat
            // count and both get confirmed for the last seat.
            $locked = TrainingSession::whereKey($training->id)->lockForUpdate()->firstOrFail();

            // Exclude any seat this user already holds, so re-posting a
            // registration to change the note cannot demote them to the
            // waitlist of a full session.
            $taken = $locked->registrations()
                ->where('status', 'confirmed')
                ->where('user_id', '!=', $user->id)
                ->count();

            return Registration::updateOrCreate(
                ['user_id' => $user->id, 'training_session_id' => $locked->id],
                [
                    'status' => $taken >= $locked->capacity ? 'waitlist' : 'confirmed',
                    'note' => $request->input('note'),
                ]
            );
        });

        return response()->json([
            'data' => $registration,
            'message' => $registration->status === 'waitlist'
                ? __('messages.training.waitlisted')
                : __('messages.training.registered'),
        ], 201);
    }

    public function unregister(Request $request, TrainingSession $training)
    {
        $promoted = DB::transaction(function () use ($request, $training) {
            $locked = TrainingSession::whereKey($training->id)->lockForUpdate()->firstOrFail();

            $released = Registration::where('user_id', $request->user()->id)
                ->where('training_session_id', $locked->id)
                ->delete();

            // Giving up a seat hands it to whoever has waited longest. Without
            // this the waitlist only ever grows and nobody is ever let in.
            return $released > 0 ? $locked->promoteFromWaitlist() : collect();
        });

        // Dispatched after the transaction commits, never inside it: a rolled
        // back promotion that had already queued mail would tell a member they
        // hold a seat that does not exist.
        $this->announcePromotions($training, $promoted);

        return response()->noContent();
    }

    /**
     * @param \Illuminate\Support\Collection<int, Registration> $promoted
     */
    private function announcePromotions(TrainingSession $training, Collection $promoted): void
    {
        if ($promoted->isEmpty()) {
            return;
        }

        NotifyPromotedFromWaitlist::dispatch($training->id, $promoted->pluck('id')->all());
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
