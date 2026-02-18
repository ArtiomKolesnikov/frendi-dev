<?php

namespace App\Actions\Feed;

use App\DTO\FeedFilterData;
use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class GetFeedAction
{
    public function __invoke(FeedFilterData $filter, string $authorToken, ?string $deviceFingerprint): LengthAwarePaginator
    {
        return Post::query()
            ->visibleFor($authorToken)
            ->withUserReactionFor($authorToken, $deviceFingerprint)
            ->withCountsFor($authorToken)
            ->when($filter->types, function ($q) use ($filter) {
                return $q->whereIn('type', $filter->types);
            })
            ->with([
                'media',
                'comments' => function ($q) use ($authorToken) {
                    return $q->visibleFor($authorToken)
                        ->latest()
                        ->limit(5);
                },
            ])
            ->latest('published_at')
            ->latest()
            ->paginate($filter->perPage)
            ->withQueryString();
    }
}

