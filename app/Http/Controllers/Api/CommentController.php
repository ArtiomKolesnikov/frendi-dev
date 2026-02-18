<?php

namespace App\Http\Controllers\Api;

use App\Actions\Comments\CreateCommentAction;
use App\Actions\Comments\DeleteCommentAction;
use App\Actions\Comments\UpdateCommentAction;
use App\DTO\CommentData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CommentIndexRequest;
use App\Http\Requests\Api\CommentStoreRequest;
use App\Http\Requests\Api\CommentUpdateRequest;
use App\Http\Resources\CommentResource;
use App\Models\Comment;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class CommentController extends Controller
{
    /** @var CreateCommentAction */
    private $createComment;
    /** @var UpdateCommentAction */
    private $updateComment;
    /** @var DeleteCommentAction */
    private $deleteComment;

    public function __construct(
        CreateCommentAction $createComment,
        UpdateCommentAction $updateComment,
        DeleteCommentAction $deleteComment
    ) {
        $this->createComment = $createComment;
        $this->updateComment = $updateComment;
        $this->deleteComment = $deleteComment;
    }

    public function index(CommentIndexRequest $request, Post $post): JsonResponse
    {
        $perPage = (int) ($request->validated()['per_page'] ?? 20);
        $authorToken = ClientContext::token($request);

        $comments = $post->comments()
            ->visibleFor($authorToken)
            ->latest()
            ->paginate($perPage)
            ->withQueryString();

        return CommentResource::collection($comments)->response();
    }

    public function store(CommentStoreRequest $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $comment = ($this->createComment)($post, CommentData::fromRequest($request), $authorToken);

        return CommentResource::make($comment)->response()->setStatusCode(201);
    }

    public function update(CommentUpdateRequest $request, Post $post, Comment $comment): JsonResponse
    {
        // Admin only
        if (!$request->session()->has('admin_id')) {
            abort(403);
        }

        $comment = ($this->updateComment)($post, $comment, $request->string('body')->toString());

        return CommentResource::make($comment)->response();
    }

    public function destroy(Request $request, Post $post, Comment $comment): JsonResponse
    {
        // Admin only
        if (!$request->session()->has('admin_id')) {
            abort(403);
        }

        ($this->deleteComment)($post, $comment);

        return response()->json(['status' => 'ok']);
    }
}
