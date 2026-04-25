<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Member;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;

class MemberAdminController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Member::orderBy('sort_order')->orderBy('id')->get(),
        ]);
    }

    public function store(Request $request)
    {
        $member = Member::create($this->validated($request));
        return response()->json(['data' => $member], 201);
    }

    public function update(Request $request, Member $member)
    {
        $member->update($this->validated($request));
        return response()->json(['data' => $member]);
    }

    public function destroy(Member $member)
    {
        $member->delete();
        return response()->noContent();
    }

    public function reorder(Request $request)
    {
        $data = $request->validate([
            'order' => ['required', 'array'],
            'order.*' => ['integer', 'exists:members,id'],
        ]);

        foreach ($data['order'] as $index => $id) {
            Member::where('id', $id)->update(['sort_order' => $index]);
        }

        return response()->noContent();
    }

    public function uploadPhoto(Request $request)
    {
        $data = $request->validate([
            'photo' => ['required', 'file', 'image', 'max:5120'], // 5MB
        ]);

        $file = $data['photo'];
        $targetDir = public_path('img/members');

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($targetDir, $filename);

        return response()->json([
            'data' => [
                'photo_url' => url('/img/members/' . $filename),
            ],
        ]);
    }

    private function validated(Request $request): array
    {
        return $request->validate([
            'full_name' => ['required', 'string', 'max:120'],
            'role_title' => ['nullable', 'string', 'max:80'],
            'rank' => ['nullable', 'string', 'max:80'],
            'photo_url' => ['nullable', 'string', 'max:2048'],
            'bio' => ['nullable', 'array'],
            'sort_order' => ['nullable', 'integer'],
            'is_instructor' => ['boolean'],
        ]);
    }
}
