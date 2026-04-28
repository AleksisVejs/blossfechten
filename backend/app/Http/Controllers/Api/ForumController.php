<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'tag' => ['nullable', 'string', 'max:60'],
        ]);

        $query = ForumPost::query()
            ->where('published', true)
            ->where(function ($inner) {
                $inner->where('is_pinned', true)
                    ->orWhereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if (!empty($validated['q'])) {
            $term = mb_strtolower($validated['q']);
            $query->where(function ($inner) use ($term) {
                $inner->whereRaw('LOWER(slug) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, "$.en"))) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, "$.lv"))) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(excerpt, "$.en"))) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(excerpt, "$.lv"))) LIKE ?', ["%{$term}%"]);
            });
        }

        if (!empty($validated['tag'])) {
            $tag = mb_strtolower($validated['tag']);
            $query->whereJsonContains('tags', $tag);
        }

        return response()->json([
            'data' => $query->paginate(12),
        ]);
    }

    public function show(string $slug)
    {
        $post = ForumPost::query()
            ->where('published', true)
            ->where(function ($inner) {
                $inner->where('is_pinned', true)
                    ->orWhereNull('published_at')
                    ->orWhere('published_at', '<=', now());
            })
            ->where('slug', $slug)
            ->with('author:id,name')
            ->firstOrFail();

        return response()->json(['data' => $post]);
    }
}
