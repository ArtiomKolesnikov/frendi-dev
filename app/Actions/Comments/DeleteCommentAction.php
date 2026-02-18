<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\Post;

class DeleteCommentAction
{
    public function __invoke(Post $post, Comment $comment): void
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $comment->delete();
    }
}

