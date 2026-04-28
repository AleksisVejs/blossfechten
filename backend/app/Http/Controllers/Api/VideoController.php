<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Http;
use Throwable;

class VideoController extends Controller
{
    private const CHANNEL_ID = 'UCZ5S_Xv7pzN6XiWupyhg8-A';
    private const CACHE_TTL  = 3600; // 1 hour

    public function index()
    {
        $videos = Cache::remember('youtube_videos', self::CACHE_TTL, function () {
            return $this->fetchFromRss();
        });

        return response()->json(['data' => $videos]);
    }

    private function fetchFromRss(): array
    {
        $url = 'https://www.youtube.com/feeds/videos.xml?channel_id=' . self::CHANNEL_ID;

        try {
            $request = Http::timeout(3);

            // Windows local environments can miss CA bundles; keep production verification intact.
            if (app()->environment(['local', 'testing'])) {
                $request = $request->withoutVerifying();
            }

            $response = $request->get($url);
        } catch (Throwable) {
            return [];
        }

        if ($response->failed()) {
            return [];
        }

        $xml = simplexml_load_string($response->body());
        if (! $xml) {
            return [];
        }

        $videos = [];
        foreach ($xml->entry as $entry) {
            $ns   = $entry->children('yt', true);
            $media = $entry->children('media', true);
            $videoId = (string) $ns->videoId;

            $videos[] = [
                'id'        => $videoId,
                'title'     => (string) $entry->title,
                'published' => (string) $entry->published,
                'thumbnail' => "https://i.ytimg.com/vi/{$videoId}/hqdefault.jpg",
                'url'       => "https://www.youtube.com/watch?v={$videoId}",
            ];

            if (count($videos) >= 6) break;
        }

        return $videos;
    }
}
