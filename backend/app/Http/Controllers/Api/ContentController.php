<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Member;
use App\Models\Page;

class ContentController extends Controller
{
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
