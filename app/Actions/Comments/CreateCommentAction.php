<?php

namespace App\Actions\Comments;

use App\DTO\CommentData;
use App\Models\Comment;
use App\Models\Post;

class CreateCommentAction
{
    public function __invoke(Post $post, CommentData $data, string $authorToken): Comment
    {
        return $post->comments()->create([
            'body' => $data->body,
            'author_display_name' => $data->authorDisplayName,
            'author_token' => $authorToken,
            'status' => Comment::STATUS_APPROVED,
        ]);
    }
}

