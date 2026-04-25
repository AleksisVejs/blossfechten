<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
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
        return response()->json(['data' => $training], 201);
    }

    public function update(Request $request, TrainingSession $training)
    {
        $training->update($this->validated($request));
        return response()->json(['data' => $training]);
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
        return $request->validate([
            'starts_at' => ['required', 'date'],
            'ends_at' => ['required', 'date', 'after:starts_at'],
            'location' => ['nullable', 'string', 'max:120'],
            'focus' => ['nullable', 'string', 'max:120'],
            'title' => ['nullable', 'array'],
            'description' => ['nullable', 'array'],
            'capacity' => ['required', 'integer', 'min:1', 'max:200'],
            'members_only' => ['boolean'],
            'cancelled' => ['boolean'],
        ]);
    }
}
