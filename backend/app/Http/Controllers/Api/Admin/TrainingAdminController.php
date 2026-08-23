<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Jobs\AnnounceTrainingSession;
use App\Jobs\NotifyTrainingSessionChanged;
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
        $before = $this->snapshot($training);

        $training->update($this->validated($request));
        $training->refresh();

        $changes = $this->diff($before, $training);

        $this->rearmReminderIfMoved($training, $changes);
        $this->announceIfRequested($request, $training);
        $this->notifyChangesIfRequested($request, $training, $changes);

        return response()->json(['data' => $training->fresh()]);
    }

    /**
     * The fields worth mailing a registered member about. Capacity is
     * deliberately absent — nobody needs an email because a seat count moved.
     *
     * @return array<string, mixed>
     */
    private function snapshot(TrainingSession $training): array
    {
        return [
            'starts_at' => $training->starts_at?->format('Y-m-d H:i'),
            'ends_at' => $training->ends_at?->format('Y-m-d H:i'),
            'when' => $this->whenLabel($training),
            'location' => (string) $training->location,
            'title' => $training->title,
            'description' => $training->description,
            'cancelled' => (bool) $training->cancelled,
        ];
    }

    /**
     * @param array<string, mixed> $before
     * @return array<int, array<string, mixed>>
     */
    private function diff(array $before, TrainingSession $training): array
    {
        $after = $this->snapshot($training);
        $changes = [];

        if ($before['starts_at'] !== $after['starts_at'] || $before['ends_at'] !== $after['ends_at']) {
            $changes[] = ['field' => 'when', 'from' => $before['when'], 'to' => $after['when']];
        }

        if ($before['location'] !== $after['location']) {
            $changes[] = ['field' => 'location', 'from' => $before['location'], 'to' => $after['location']];
        }

        if ($before['title'] !== $after['title']) {
            $changes[] = ['field' => 'title', 'from' => $before['title'], 'to' => $after['title']];
        }

        // The body is shown as it now reads; a before/after of prose is noise.
        if ($before['description'] !== $after['description']) {
            $changes[] = ['field' => 'description', 'to' => $after['description']];
        }

        if ($before['cancelled'] !== $after['cancelled']) {
            $changes[] = ['field' => $after['cancelled'] ? 'cancelled' : 'reinstated'];
        }

        return $changes;
    }

    private function whenLabel(TrainingSession $training): string
    {
        $start = $training->starts_at;
        $end = $training->ends_at;

        if ($start === null) {
            return '';
        }

        if ($end === null) {
            return $start->format('d.m.Y H:i');
        }

        return $start->isSameDay($end)
            ? $start->format('d.m.Y H:i') . '-' . $end->format('H:i')
            : $start->format('d.m.Y H:i') . ' - ' . $end->format('d.m.Y H:i');
    }

    /**
     * A session pushed back out beyond the reminder window earns a fresh
     * reminder for its new date — otherwise members who were reminded about
     * the old date hear nothing before the new one.
     *
     * @param array<int, array<string, mixed>> $changes
     */
    private function rearmReminderIfMoved(TrainingSession $training, array $changes): void
    {
        if (! in_array('when', array_column($changes, 'field'), true)) {
            return;
        }

        if (! $training->wasReminded()) {
            return;
        }

        if ($training->starts_at === null || ! $training->starts_at->isAfter(now()->addDay())) {
            return;
        }

        $training->forceFill(['reminded_at' => null])->save();
    }

    /**
     * Change notices go only to members with a stake in the session, and only
     * when something they would care about actually moved.
     *
     * @param array<int, array<string, mixed>> $changes
     */
    private function notifyChangesIfRequested(Request $request, TrainingSession $training, array $changes): void
    {
        if (! $request->boolean('notify_changes') || $changes === []) {
            return;
        }

        NotifyTrainingSessionChanged::dispatch($training->id, $changes);
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
            'send_reminder' => ['sometimes', 'boolean'],
            'notify_subscribers' => ['sometimes', 'boolean'],
            'notify_changes' => ['sometimes', 'boolean'],
        ]);

        // Not columns — these are per-request instructions, handled separately.
        unset($data['notify_subscribers'], $data['notify_changes']);

        return $data;
    }
}
