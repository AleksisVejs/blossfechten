<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnnounceTrainingSession;
use App\Models\TrainingSession;
use Illuminate\Http\Request;

class TrainingAdminController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => TrainingSession::orderByDesc('starts_at')
                ->withCount([
                    'registrations' => fn($query) => $query->where('status', '!=', 'cancelled'),
                ])
                ->paginate(50),
        ]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $training = TrainingSession::create($data);

        $this->announceIfRequested($request, $training);

        return response()->json(['data' => $training->fresh()], 201);
    }

    public function update(Request $request, TrainingSession $training)
    {
        $training->update($this->validated($request));

        $this->announceIfRequested($request, $training);

        return response()->json(['data' => $training->fresh()]);
    }

    /**
     * Announcement mail only goes out when the admin explicitly asks for it,
     * and only once per session — editing an announced event never re-mails
     * the club.
     */
    private function announceIfRequested(Request $request, TrainingSession $training): void
    {
        if (! $request->boolean('notify_subscribers')) {
            return;
        }

        if (! $training->isAnnounceable()) {
            return;
        }

        AnnounceTrainingSession::dispatch($training->id);
    }

    public function destroy(TrainingSession $training)
    {
        $training->delete();
        return response()->noContent();
    }

    public function registrations(TrainingSession $training)
    {
        return response()->json([
            'data' => $training->registrations()
                ->where('status', '!=', 'cancelled')
                ->with('user:id,name,email,rank,phone')
                ->orderBy('status')
                ->orderByDesc('created_at')
                ->get(),
        ]);
    }

    private function validated(Request $request): array
    {
        $data = $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:120'],
            'focus' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'array'],
            'description' => ['nullable', 'array'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'members_only' => ['boolean'],
            'cancelled' => ['boolean'],
            'notify_subscribers' => ['sometimes', 'boolean'],
        ]);

        // Not a column — it is a per-request instruction, handled separately.
        unset($data['notify_subscribers']);

        return $data;
    }
}
