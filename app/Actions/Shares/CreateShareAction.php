<?php

namespace App\Actions\Shares;

use App\DTO\ShareData;
use App\Models\Post;
use App\Models\ShareEvent;
use App\Services\PostAccessService;

class CreateShareAction
{
    /** @var PostAccessService */
    private $accessService;

    public function __construct(PostAccessService $accessService)
    {
        $this->accessService = $accessService;
    }

    public function __invoke(Post $post, ShareData $data, string $authorToken): string
    {
        if (!$this->accessService->isOwner($post, $authorToken) && $post->status !== Post::STATUS_APPROVED) {
            abort(403, 'Ссылка будет доступна после модерации.');
        }

        ShareEvent::create([
            'post_id' => $post->id,
            'channel' => $data->channel,
            'author_token' => $authorToken,
        ]);

        return url('/share/'.$post->share_slug);
    }
}

