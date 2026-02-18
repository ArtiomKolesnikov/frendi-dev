<?php

namespace App\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use App\Models\ContestWinner;
use App\Models\Post;
use App\Models\PostReaction;
use App\Support\ClientContext;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cookie;

class FeedController extends Controller
{
    public function __invoke(Request $request)
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);

        if (!$request->hasCookie('frendi_token')) {
            Cookie::queue('frendi_token', $authorToken, 60 * 24 * 365);
        }
        if (!$request->hasCookie('frendi_fingerprint')) {
            Cookie::queue('frendi_fingerprint', $deviceFingerprint, 60 * 24 * 365);
        }

        $userId = auth()->id();
        $isAdmin = session()->has('admin_id');
        $filterType = $request->query('type');

        if ($filterType === Post::TYPE_ROUTE) {
            $winners = collect();
            $winnerPostIds = collect();

            $posts = Post::query()
                ->when(!$isAdmin, function ($q) use ($authorToken) {
                    return $q->visibleFor($authorToken);
                })
                ->where('type', Post::TYPE_ROUTE)
                ->withUserReaction($authorToken, $deviceFingerprint)
                ->with('media')
                // Сортировка по времени создания, самые свежие сверху
                ->orderByDesc('created_at')
                ->paginate(10);
            $posts->appends($request->query());

            $likedIds = $this->likedIdsFor($userId, $authorToken, $deviceFingerprint, $posts->pluck('id')->all());

            if ($request->ajax()) {
                $html = view('feed._posts', [
                    'posts' => $posts,
                    'winnerPostIds' => [],
                    'authorToken' => $authorToken,
                    'likedPostIds' => $likedIds,
                ])->render();
                return response()->json([
                    'html' => $html,
                    'next_page_url' => $posts->nextPageUrl(),
                ]);
            }

            return response()->view('feed.index', [
                'title' => 'Frendi feed',
                'posts' => $posts,
                'winners' => $winners,
                'winnerPostIds' => $winnerPostIds,
                'authorToken' => $authorToken,
                'deviceFingerprint' => $deviceFingerprint,
                'likedPostIds' => $likedIds,
            ]);
        }

        // Winners (latest first)
        $winners = ContestWinner::with(['post' => function($q) use ($authorToken, $deviceFingerprint) { $q->with('media')->withUserReaction($authorToken, $deviceFingerprint); }])
            ->orderByDesc('created_at')
            ->get();
        $winnerPostIds = $winners->pluck('post_id')->filter()->values();

        // Regular feed (exclude winners)
        $posts = Post::query()
            ->when(!$isAdmin, function ($q) use ($authorToken) {
                return $q->visibleFor($authorToken);
            })
            ->whereNotIn('id', $winnerPostIds)
            ->withUserReaction($authorToken, $deviceFingerprint)
            ->with('media')
            // Сортировка по времени создания, самые свежие сверху
            ->orderByDesc('created_at')
            ->paginate(10);
        $posts->appends($request->query());

        $likedIds = $this->likedIdsFor($userId, $authorToken, $deviceFingerprint, array_merge(
            $posts->pluck('id')->all(),
            $winners->pluck('post_id')->all()
        ));

        if ($request->ajax()) {
            $html = view('feed._posts', [
                'posts' => $posts,
                'winnerPostIds' => $winnerPostIds,
                'authorToken' => $authorToken,
                'likedPostIds' => $likedIds,
            ])->render();
            return response()->json([
                'html' => $html,
                'next_page_url' => $posts->nextPageUrl(),
            ]);
        }

        // Build OG with cache-busting only outside production
        $ogImage = asset('images/og-default.jpg') . '?v=' . env('CACHE_VERSION', 1);
        $ogUrl = url()->current();
        if (!app()->environment('production')) {
            $suffix = '?v=' . time();
            $ogImage .= $suffix;
            $ogUrl .= $suffix;
        }
        $og = [
            'title' => 'Frendi',
            'description' => 'Mascotas, rutas y concursos. ¡Únete!',
            'image' => $ogImage,
            'url' => $ogUrl,
            'type' => 'website',
        ];

        return response()->view('feed.index', [
            'title' => 'Frendi',
            'posts' => $posts,
            'winners' => $winners,
            'winnerPostIds' => $winnerPostIds,
            'authorToken' => $authorToken,
            'deviceFingerprint' => $deviceFingerprint,
            'likedPostIds' => $likedIds,
            'og' => $og,
        ]);
    }

    private function likedIdsFor(?int $userId, ?string $authorToken, ?string $deviceFingerprint, array $postIds): array
    {
        if (empty($postIds)) return [];
        
        $q = PostReaction::query()->whereIn('post_id', $postIds)->where('type', 'like');
        
        if ($userId) {
            // For logged in users, check both user_id and device_fingerprint
            $q->where(function ($query) use ($userId, $deviceFingerprint) {
                $query->where('user_id', $userId);
                if ($deviceFingerprint) {
                    $query->orWhere('device_fingerprint', $deviceFingerprint);
                }
            });
        } elseif ($deviceFingerprint) {
            // For guests, use device fingerprint
            $q->where('device_fingerprint', $deviceFingerprint);
        } else {
            // Fallback to author_token
            $q->where('author_token', $authorToken);
        }
        
        return $q->pluck('post_id')->unique()->values()->all();
    }
}
