<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Page;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function pages(Request $request)
    {
        $validated = $request->validate([
            'slugs' => ['required', 'array', 'min:1'],
            'slugs.*' => ['string', 'distinct'],
        ]);

        $pages = Page::query()
            ->whereIn('slug', $validated['slugs'])
            ->where('published', true)
            ->get()
            ->keyBy('slug');

        return response()->json(['data' => $pages]);
    }

    public function page(string $slug)
    {
        $page = Page::where('slug', $slug)->where('published', true)->firstOrFail();
        return response()->json(['data' => $page]);
    }

    public function members()
    {
        return response()->json([
            'data' => Member::orderBy('sort_order')->get(),
        ]);
    }
}
