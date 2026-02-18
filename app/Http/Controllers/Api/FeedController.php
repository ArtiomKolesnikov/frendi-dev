<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class FeedController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $perPage = max(1, min(20, (int) $request->input('per_page', 10)));
        $authorToken = ClientContext::token($request);
        $typesInput = $request->input('types');
        $types = is_string($typesInput)
            ? array_filter(array_map('trim', explode(',', $typesInput)))
            : ($typesInput ?: null);

        $paginator = Post::query()
            ->visibleFor($authorToken)
            ->withUserReaction($authorToken)
            ->when($types, fn ($q) => $q->whereIn('type', (array) $types))
            ->with([
                'media',
                'comments' => fn ($q) => $q->visibleFor($authorToken)
                    ->latest()
                    ->limit(5),
            ])
            ->withCount([
                'reactions as likes_count' => fn ($q) => $q->where('type', 'like'),
                'reactions as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
                'comments as comments_count' => function ($q) use ($authorToken) {
                    $q->where(function ($inner) use ($authorToken) {
                        $inner->where('status', 'approved')
                            ->orWhere('author_token', $authorToken);
                    });
                },
            ])
            ->latest('published_at')
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return PostResource::collection($paginator)->response();
    }
}
