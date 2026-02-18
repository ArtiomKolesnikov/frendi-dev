<?php

namespace App\Actions\Posts;

use App\Models\Post;

class ShowPostAction
{
    public function __invoke(Post $post, string $authorToken, ?string $deviceFingerprint): Post
    {
        return Post::query()
            ->whereKey($post->id)
            ->visibleFor($authorToken)
            ->withUserReactionFor($authorToken, $deviceFingerprint)
            ->withCountsFor($authorToken)
            ->with([
                'media',
                'comments' => function ($q) use ($authorToken) {
                    return $q->visibleFor($authorToken)->latest()->limit(20);
                },
            ])
            ->firstOrFail();
    }
}

