<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    public function index(Request $request, Post $post): JsonResponse
    {
        $perPage = max(1, min(50, (int) $request->input('per_page', 20)));
        $authorToken = ClientContext::token($request);

        $comments = $post->comments()
            ->visibleFor($authorToken)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return CommentResource::collection($comments)->response();
    }

    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
            'author_display_name' => ['nullable', 'string', 'max:120'],
        ]);

        $authorToken = ClientContext::token($request);

        $comment = $post->comments()->create([
            'body' => $validated['body'],
            'author_display_name' => $validated['author_display_name'] ?? null,
            'author_token' => $authorToken,
            'status' => Comment::STATUS_APPROVED,
        ]);

        return CommentResource::make($comment)->response()->setStatusCode(201);
    }

    public function update(Request $request, Post $post, Comment $comment): JsonResponse
    {
        // Admin only
        if (!$request->session()->has('admin_id')) {
            abort(403);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }
        $validated = $request->validate([
            'body' => ['required', 'string', 'max:1000'],
        ]);
        $comment->update(['body' => $validated['body']]);
        return CommentResource::make($comment)->response();
    }

    public function destroy(Request $request, Post $post, Comment $comment): JsonResponse
    {
        // Admin only
        if (!$request->session()->has('admin_id')) {
            abort(403);
        }
        if ($comment->post_id !== $post->id) {
            abort(404);
        }
        $comment->delete();
        return response()->json(['status' => 'ok']);
    }
}
