<?php

namespace App\Actions\Admin;

use App\Models\Post;

class UpdatePostStatusAction
{
    public function __invoke(Post $post, string $status): Post
    {
        $post->status = $status;
        $post->published_at = $post->status === Post::STATUS_APPROVED ? now() : null;
        $post->save();

        return $post->load(['media', 'comments']);
    }
}

