<?php

namespace App\Actions\Shares;

use App\Models\Post;

class ShowSharePostAction
{
    public function __invoke(string $slug, string $authorToken, ?string $deviceFingerprint): Post
    {
        return Post::query()
            ->where('share_slug', $slug)
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

