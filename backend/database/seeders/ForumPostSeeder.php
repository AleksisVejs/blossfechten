<?php

namespace Database\Seeders;

use App\Models\ForumPost;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ForumPostSeeder extends Seeder
{
    public function run(): void
    {
        for ($i = 1; $i <= 50; $i++) {
            ForumPost::create([
                'title' => [
                    'en' => "Seed Post {$i}",
                ],
                'excerpt' => [
                    'en' => "Seed excerpt {$i}",
                ],
                'body' => [
                    'en' => "Seed body content for post {$i} used for pagination testing.",
                ],
                'slug' => 'seed-post-' . Str::lower(Str::random(10)) . '-' . $i,
                'tags' => ['seed', 'pagination'],
                'is_pinned' => false,
                'published' => true,
                'published_at' => now()->subMinutes($i),
                'created_by' => 1,
            ]);
        }
    }
}
