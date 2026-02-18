<?php

namespace App\Actions\Posts;

use App\DTO\PostUpdateData;
use App\Models\Post;
use App\Services\PostAccessService;
use App\Services\PostMediaService;
use Illuminate\Support\Facades\DB;

class UpdatePostAction
{
    /** @var PostAccessService */
    private $accessService;
    /** @var PostMediaService */
    private $mediaService;

    public function __construct(
        PostAccessService $accessService,
        PostMediaService $mediaService
    ) {
        $this->accessService = $accessService;
        $this->mediaService = $mediaService;
    }

    public function __invoke(Post $post, PostUpdateData $data, string $authorToken): Post
    {
        $this->accessService->ensureOwner($post, $authorToken);

        DB::transaction(function () use ($post, $data) {
            $post->fill([
                'title' => $data->title ?? $post->title,
                'body' => $data->body ?? $post->body,
                'meta' => $data->meta ?? $post->meta,
                'author_display_name' => $data->authorDisplayName ?? $post->author_display_name,
                'author_contact' => $data->authorContact ?? $post->author_contact,
                'status' => Post::STATUS_PENDING,
                'published_at' => null,
            ])->save();

            $this->mediaService->deleteByIds($post, $data->removeMediaIds);
            $this->mediaService->store($post, $data->media);
        });

        return $post->refresh()->load(['media']);
    }
}

