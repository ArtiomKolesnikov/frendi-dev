<?php

namespace App\Actions\Reactions;

use App\DTO\ReactionData;
use App\Models\Post;

class ToggleReactionAction
{
    public function __invoke(Post $post, ReactionData $data, string $authorToken, ?string $deviceFingerprint): array
    {
        $userId = auth()->id();
        // Use global session() helper because some API requests may not have Request::session bound
        $adminId = session('admin_id');

        $reactionQuery = $post->reactions();
        if ($adminId) {
            $reactionQuery->where('admin_id', $adminId);
        } elseif ($userId) {
            $reactionQuery->where(function ($q) use ($userId, $deviceFingerprint) {
                $q->where('user_id', $userId);
                if ($deviceFingerprint) {
                    $q->orWhere('device_fingerprint', $deviceFingerprint);
                }
            });
        } elseif ($deviceFingerprint) {
            $reactionQuery->where('device_fingerprint', $deviceFingerprint);
        } else {
            $reactionQuery->where('author_token', $authorToken);
        }
        $reaction = $reactionQuery->first();

        if ($reaction && $reaction->type === $data->type) {
            $reaction->delete();
            $userReaction = null;
        } else {
            $identifier = [];
            if ($adminId) {
                $identifier = ['admin_id' => $adminId];
            } elseif ($userId) {
                $identifier = ['user_id' => $userId];
            } elseif ($deviceFingerprint) {
                $identifier = ['device_fingerprint' => $deviceFingerprint];
            } else {
                $identifier = ['author_token' => $authorToken];
            }

            $post->reactions()->updateOrCreate(
                $identifier,
                [
                    'type' => $data->type,
                    'author_display_name' => $data->authorDisplayName,
                ]
            );
            $userReaction = $data->type;
        }

        $post->loadCount([
            'reactions as likes_count' => function ($q) {
                return $q->where('type', 'like');
            },
            'reactions as dislikes_count' => function ($q) {
                return $q->where('type', 'dislike');
            },
        ]);

        return [
            'likes_count' => (int) $post->likes_count,
            'dislikes_count' => (int) $post->dislikes_count,
            'user_reaction' => $userReaction,
        ];
    }
}

