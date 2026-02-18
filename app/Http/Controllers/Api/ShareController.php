<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Comment;
use App\Models\Post;
use App\Models\ShareEvent;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);

        if (!hash_equals($post->author_token ?? '', $authorToken) && $post->status !== Post::STATUS_APPROVED) {
            abort(403, 'Ссылка будет доступна после модерации.');
        }

        // save share event (channel optional)
        ShareEvent::create([
            'post_id' => $post->id,
            'channel' => $request->string('channel')->toString() ?: null,
            'author_token' => $authorToken,
        ]);

        return response()->json([
            'share_url' => url('/share/'.$post->share_slug),
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $authorToken = ClientContext::token($request);

        $post = Post::query()
            ->where('share_slug', $slug)
            ->firstOrFail();

        if ($post->status !== Post::STATUS_APPROVED && !hash_equals($post->author_token ?? '', $authorToken)) {
            abort(404);
        }

        $post->load(['media', 'comments' => function ($q) use ($authorToken) {
            $q->visibleFor($authorToken)->latest()->limit(20);
        }])->loadCount([
            'reactions as likes_count' => fn ($q) => $q->where('type', 'like'),
            'reactions as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
            'comments as comments_count' => function ($q) use ($authorToken) {
                $q->where(function ($inner) use ($authorToken) {
                    $inner->where('status', Comment::STATUS_APPROVED)
                        ->orWhere('author_token', $authorToken);
                });
            },
        ]);

        $post->setAttribute('user_reaction', $post->reactions()
            ->where('author_token', $authorToken)
            ->value('type'));

        return PostResource::make($post)->response();
    }
}
