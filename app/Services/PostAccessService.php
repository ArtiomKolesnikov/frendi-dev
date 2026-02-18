<?php

namespace App\Services;

use App\Models\Post;

class PostAccessService
{
    public function isOwner(Post $post, ?string $authorToken): bool
    {
        return $authorToken && $post->author_token && hash_equals($post->author_token, $authorToken);
    }

    public function ensureOwner(Post $post, ?string $authorToken): void
    {
        abort_unless($this->isOwner($post, $authorToken), 403, 'Недостаточно прав для изменения поста.');
    }
}

