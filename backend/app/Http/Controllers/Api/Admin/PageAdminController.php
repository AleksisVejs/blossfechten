<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\Page;
use Illuminate\Http\Request;

class PageAdminController extends Controller
{
    public function index()
    {
        return response()->json([
            'data' => Page::orderBy('slug')->get(),
        ]);
    }

    public function show(string $slug)
    {
        $page = Page::where('slug', $slug)->firstOrNew(['slug' => $slug]);
        return response()->json(['data' => $page]);
    }

    public function upsert(Request $request, string $slug)
    {
        $data = $request->validate([
            'title' => ['nullable', 'array'],
            'body' => ['nullable', 'array'],
            'published' => ['boolean'],
        ]);

        $data['title'] = $data['title'] ?? [];
        $data['body'] = $data['body'] ?? [];
        $data['published'] = $data['published'] ?? true;

        $page = Page::updateOrCreate(['slug' => $slug], $data);

        return response()->json(['data' => $page]);
    }
}
