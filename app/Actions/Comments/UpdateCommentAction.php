<?php

namespace App\Actions\Comments;

use App\Models\Comment;
use App\Models\Post;

class UpdateCommentAction
{
    public function __invoke(Post $post, Comment $comment, string $body): Comment
    {
        if ($comment->post_id !== $post->id) {
            abort(404);
        }

        $comment->update(['body' => $body]);

        return $comment;
    }
}

