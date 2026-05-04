<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Models\ForumPost;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ForumPostAdminController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:120'],
            'status' => ['nullable', Rule::in(['all', 'published', 'draft'])],
        ]);

        $query = ForumPost::query()
            ->with('author:id,name')
            ->orderByDesc('is_pinned')
            ->orderByDesc('published_at')
            ->orderByDesc('created_at');

        if (($validated['status'] ?? 'all') === 'published') {
            $query->where('published', true);
        } elseif (($validated['status'] ?? 'all') === 'draft') {
            $query->where('published', false);
        }

        if (!empty($validated['q'])) {
            $term = mb_strtolower($validated['q']);
            $query->where(function ($inner) use ($term) {
                $inner->whereRaw('LOWER(slug) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, "$.en"))) LIKE ?', ["%{$term}%"])
                    ->orWhereRaw('LOWER(JSON_UNQUOTE(JSON_EXTRACT(title, "$.lv"))) LIKE ?', ["%{$term}%"]);
            });
        }

        return response()->json(['data' => $query->paginate(50)]);
    }

    public function store(Request $request)
    {
        $data = $this->validated($request);
        $data['is_pinned'] = (bool) ($data['is_pinned'] ?? false);
        $data['published'] = (bool) ($data['published'] ?? false);
        $data['slug'] = $this->makeSlug($data['slug'] ?? null, $data['title'] ?? [], null);
        $data['created_by'] = $request->user()->id;
        $data['tags'] = $this->normalizeTags($data['tags'] ?? []);
        $data['published_at'] = $data['published'] ? ($data['published_at'] ?? now()) : null;

        $post = ForumPost::create($data)->load('author:id,name');

        return response()->json(['data' => $post], 201);
    }

    public function update(Request $request, ForumPost $forumPost)
    {
        $data = $this->validated($request, $forumPost);
        $data['is_pinned'] = (bool) ($data['is_pinned'] ?? false);
        $data['published'] = (bool) ($data['published'] ?? false);
        $data['slug'] = $this->makeSlug($data['slug'] ?? null, $data['title'] ?? [], $forumPost->id);
        $data['tags'] = $this->normalizeTags($data['tags'] ?? []);
        $data['published_at'] = $data['published'] ? ($data['published_at'] ?? ($forumPost->published_at ?? now())) : null;

        $forumPost->update($data);

        return response()->json(['data' => $forumPost->load('author:id,name')]);
    }

    public function destroy(ForumPost $forumPost)
    {
        $this->cleanupPostImages($forumPost);
        $forumPost->delete();
        return response()->noContent();
    }

    public function uploadCover(Request $request)
    {
        $data = $request->validate([
            'cover' => ['required', 'file', 'image', 'max:5120'], // 5MB
        ]);

        $file = $data['cover'];
        $targetDir = public_path('img/forum');

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($targetDir, $filename);

        return response()->json([
            'data' => [
                'cover_image_url' => url('img/forum/' . $filename),
            ],
        ]);
    }

    public function uploadInlineImage(Request $request)
    {
        $file = $request->file('image') ?: $request->file('file');

        if (! $file) {
            return response()->json([
                'message' => 'No image file was uploaded. Please drop or choose an image file.',
            ], 422);
        }

        $validator = validator(
            ['image' => $file],
            ['image' => ['file', 'image', 'max:10240']], // 10MB
            ['image.max' => 'Image is too large. Maximum allowed size is 10MB.']
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('image'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $targetDir = public_path('img/forum/inline');

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.' . $file->getClientOriginalExtension();
        $file->move($targetDir, $filename);

        return response()->json([
            'data' => [
                'image_url' => url('img/forum/inline/' . $filename),
            ],
        ]);
    }

    public function uploadInlinePdf(Request $request)
    {
        $file = $request->file('pdf');

        if (! $file) {
            return response()->json([
                'message' => 'No PDF file was uploaded.',
            ], 422);
        }

        $validator = validator(
            ['pdf' => $file],
            ['pdf' => ['file', 'mimes:pdf', 'max:20480']],
            ['pdf.max' => 'PDF is too large. Maximum allowed size is 20MB.']
        );

        if ($validator->fails()) {
            return response()->json([
                'message' => $validator->errors()->first('pdf'),
                'errors' => $validator->errors(),
            ], 422);
        }

        $targetDir = public_path('img/forum/files');

        if (! File::exists($targetDir)) {
            File::makeDirectory($targetDir, 0755, true);
        }

        $filename = Str::uuid()->toString() . '.pdf';
        $file->move($targetDir, $filename);

        return response()->json([
            'data' => [
                'file_url' => url('img/forum/files/' . $filename),
            ],
        ]);
    }

    public function deleteUploadedImage(Request $request)
    {
        $data = $request->validate([
            'url' => ['required', 'string', 'max:2048'],
        ]);

        if (! $this->isAllowedForumImageUrl($data['url'])) {
            return response()->json(['message' => 'File path is not allowed.'], 422);
        }

        $this->deleteForumImageByUrl($data['url']);

        return response()->noContent();
    }

    private function validated(Request $request, ?ForumPost $post = null): array
    {
        return $request->validate([
            'title' => ['required', 'array'],
            'excerpt' => ['nullable', 'array'],
            'body' => ['required', 'array'],
            'slug' => ['nullable', 'string', 'max:160', Rule::unique('forum_posts', 'slug')->ignore($post?->id)],
            'cover_image_url' => [
                'nullable',
                'string',
                'max:2048',
                function (string $attribute, mixed $value, \Closure $fail): void {
                    if (! is_string($value) || trim($value) === '') {
                        return;
                    }

                    if (filter_var($value, FILTER_VALIDATE_URL)) {
                        return;
                    }

                    if (preg_match('#^/img/forum/[A-Za-z0-9._-]+$#', $value) === 1) {
                        return;
                    }

                    $fail("The {$attribute} field must be a valid URL or forum image path.");
                },
            ],
            'tags' => ['nullable', 'array'],
            'tags.*' => ['string', 'max:40'],
            'is_pinned' => ['boolean'],
            'published' => ['boolean'],
            'published_at' => ['nullable', 'date'],
        ]);
    }

    private function makeSlug(?string $value, array $title, ?int $ignoreId): string
    {
        $base = Str::slug($value ?: ($title['en'] ?? $title['lv'] ?? 'forum-post')) ?: 'forum-post';
        $candidate = $base;
        $count = 1;

        while (ForumPost::query()
            ->where('slug', $candidate)
            ->when($ignoreId, fn($query) => $query->where('id', '!=', $ignoreId))
            ->exists()) {
            $count++;
            $candidate = "{$base}-{$count}";
        }

        return $candidate;
    }

    private function normalizeTags(array $tags): array
    {
        return collect($tags)
            ->filter(fn($tag) => is_string($tag) && trim($tag) !== '')
            ->map(fn($tag) => mb_strtolower(trim($tag)))
            ->unique()
            ->values()
            ->all();
    }

    private function cleanupPostImages(ForumPost $forumPost): void
    {
        $urls = collect([]);

        if (is_string($forumPost->cover_image_url) && $forumPost->cover_image_url !== '') {
            $urls->push($forumPost->cover_image_url);
        }

        $bodyByLocale = is_array($forumPost->body) ? $forumPost->body : [];
        foreach ($bodyByLocale as $bodyValue) {
            if (! is_string($bodyValue) || $bodyValue === '') {
                continue;
            }

            preg_match_all('/!\[[^\]]*\]\((https?:\/\/[^\s)]+|\/[^\s)]+)\)/', $bodyValue, $matches);
            foreach (($matches[1] ?? []) as $url) {
                if (is_string($url) && $url !== '') {
                    $urls->push($url);
                }
            }
        }

        $urls->unique()->each(function (string $url): void {
            if ($this->isAllowedForumImageUrl($url)) {
                $this->deleteForumImageByUrl($url);
            }
        });
    }

    private function isAllowedForumImageUrl(string $url): bool
    {
        $normalizedPath = $this->extractNormalizedForumImagePath($url);
        if (! is_string($normalizedPath) || $normalizedPath === '') {
            return false;
        }

        if (str_contains($normalizedPath, '..')) {
            return false;
        }

        return preg_match('#^/img/forum(?:/inline|/files)?/[A-Za-z0-9._-]+$#', $normalizedPath) === 1;
    }

    private function deleteForumImageByUrl(string $url): void
    {
        $normalizedPath = $this->extractNormalizedForumImagePath($url);
        if (! is_string($normalizedPath) || ! $this->isAllowedForumImageUrl($normalizedPath)) {
            return;
        }

        $absolutePath = public_path(ltrim($normalizedPath, '/'));
        $realPublicPath = realpath(public_path()) ?: public_path();
        $realAbsolutePath = realpath($absolutePath);

        if (! $realAbsolutePath) {
            return;
        }

        if (! str_starts_with($realAbsolutePath, $realPublicPath . DIRECTORY_SEPARATOR . 'img' . DIRECTORY_SEPARATOR . 'forum')) {
            return;
        }

        if (File::exists($absolutePath)) {
            File::delete($absolutePath);
        }
    }

    private function extractNormalizedForumImagePath(string $url): ?string
    {
        $parsedPath = parse_url($url, PHP_URL_PATH);
        if (! is_string($parsedPath) || trim($parsedPath) === '') {
            return null;
        }

        $normalizedPath = '/' . ltrim(str_replace('\\', '/', $parsedPath), '/');
        $appBasePath = parse_url(config('app.url'), PHP_URL_PATH);
        $appBasePath = is_string($appBasePath) ? trim($appBasePath) : '';

        if ($appBasePath !== '' && $appBasePath !== '/') {
            $normalizedBasePath = '/' . trim(str_replace('\\', '/', $appBasePath), '/');
            if (str_starts_with($normalizedPath, $normalizedBasePath . '/')) {
                $normalizedPath = substr($normalizedPath, strlen($normalizedBasePath));
                $normalizedPath = '/' . ltrim($normalizedPath, '/');
            } elseif ($normalizedPath === $normalizedBasePath) {
                $normalizedPath = '/';
            }
        }

        return $normalizedPath;
    }
}
