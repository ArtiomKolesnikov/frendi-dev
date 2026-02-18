<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ReactionController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'type' => ['required', Rule::in(['like', 'dislike'])],
            'author_display_name' => ['nullable', 'string', 'max:120'],
        ]);

        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);
        $userId = auth()->id();
        // Use global session() helper because some API requests may not have Request::session bound
        $adminId = session('admin_id');

        $reactionQuery = $post->reactions();
        if ($adminId) {
            $reactionQuery->where('admin_id', $adminId);
        } elseif ($userId) {
            // For logged in users, check both user_id and device_fingerprint
            $reactionQuery->where(function ($q) use ($userId, $deviceFingerprint) {
                $q->where('user_id', $userId);
                if ($deviceFingerprint) {
                    $q->orWhere('device_fingerprint', $deviceFingerprint);
                }
            });
        } elseif ($deviceFingerprint) {
            // For guests, use device fingerprint
            $reactionQuery->where('device_fingerprint', $deviceFingerprint);
        } else {
            // Fallback to author_token
            $reactionQuery->where('author_token', $authorToken);
        }
        $reaction = $reactionQuery->first();

        if ($reaction && $reaction->type === $validated['type']) {
            $reaction->delete();
            $userReaction = null;
        } else {
            // Determine the identifier to use for creation
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
                    'type' => $validated['type'],
                    'author_display_name' => $validated['author_display_name'] ?? null,
                ]
            );
            $userReaction = $validated['type'];
        }

        $post->loadCount([
            'reactions as likes_count' => fn ($q) => $q->where('type', 'like'),
            'reactions as dislikes_count' => fn ($q) => $q->where('type', 'dislike'),
        ]);

        return response()->json([
            'likes_count' => (int) $post->likes_count,
            'dislikes_count' => (int) $post->dislikes_count,
            'user_reaction' => $userReaction,
        ]);
    }
}
