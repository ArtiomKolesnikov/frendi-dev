<?php

namespace App\Actions\Admin;

use App\Models\Post;
use Illuminate\Pagination\LengthAwarePaginator;

class ListPostsForModerationAction
{
    public function __invoke(?string $status): LengthAwarePaginator
    {
        return Post::query()
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->with(['media', 'comments'])
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }
}

