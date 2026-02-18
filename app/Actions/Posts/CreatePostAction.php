<?php

namespace App\Actions\Posts;

use App\DTO\PostData;
use App\Models\Post;
use App\Services\PostMediaService;
use Illuminate\Support\Facades\DB;

class CreatePostAction
{
    /** @var PostMediaService */
    private $mediaService;

    public function __construct(PostMediaService $mediaService)
    {
        $this->mediaService = $mediaService;
    }

    public function __invoke(PostData $data, string $authorToken): Post
    {
        return DB::transaction(function () use ($data, $authorToken) {
            $post = Post::create([
                'type' => $data->type,
                'title' => $data->title,
                'body' => $data->body,
                'meta' => $data->meta,
                'author_display_name' => $data->authorDisplayName,
                'author_contact' => $data->authorContact,
                'author_token' => $authorToken,
                'status' => Post::STATUS_PENDING,
                'published_at' => null,
            ]);

            $this->mediaService->store($post, $data->media);

            return $post->load(['media']);
        });
    }
}

