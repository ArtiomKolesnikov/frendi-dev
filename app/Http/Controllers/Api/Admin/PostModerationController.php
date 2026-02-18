<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostModerationController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $status = $request->input('status', Post::STATUS_PENDING);

        $posts = Post::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with(['media', 'comments'])
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return PostResource::collection($posts)->response();
    }

    public function updateStatus(Request $request, Post $post): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Post::STATUS_PENDING,
                Post::STATUS_APPROVED,
                Post::STATUS_REJECTED,
            ])],
        ]);

        $post->status = $validated['status'];
        $post->published_at = $post->status === Post::STATUS_APPROVED ? now() : null;
        $post->save();

        $post->load(['media', 'comments']);

        return PostResource::make($post)->response();
    }
}
