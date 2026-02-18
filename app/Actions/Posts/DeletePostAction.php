<?php

namespace App\Actions\Posts;

use App\Models\Post;
use App\Services\PostAccessService;

class DeletePostAction
{
    /** @var PostAccessService */
    private $accessService;

    public function __construct(PostAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function __invoke(Post $post, string $authorToken): void
    {
        $this->accessService->ensureOwner($post, $authorToken);
        $post->delete();
    }
}

