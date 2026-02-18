<?php

namespace App\Http\Controllers\Api;

use App\Actions\Posts\CreatePostAction;
use App\Actions\Posts\DeletePostAction;
use App\Actions\Posts\ShowPostAction;
use App\Actions\Posts\UpdatePostAction;
use App\DTO\PostData;
use App\DTO\PostUpdateData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\PostStoreRequest;
use App\Http\Requests\Api\PostUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostController extends Controller
{
    /** @var CreatePostAction */
    private $createPost;
    /** @var ShowPostAction */
    private $showPost;
    /** @var UpdatePostAction */
    private $updatePost;
    /** @var DeletePostAction */
    private $deletePost;

    public function __construct(
        CreatePostAction $createPost,
        ShowPostAction $showPost,
        UpdatePostAction $updatePost,
        DeletePostAction $deletePost
    ) {
        $this->createPost = $createPost;
        $this->showPost = $showPost;
        $this->updatePost = $updatePost;
        $this->deletePost = $deletePost;
    }

    public function store(PostStoreRequest $request): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $post = ($this->createPost)(PostData::fromRequest($request), $authorToken);

        return PostResource::make($post)
            ->response()
            ->setStatusCode(201);
    }

    public function show(Request $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);

        $post = ($this->showPost)($post, $authorToken, $deviceFingerprint);

        return PostResource::make($post)->response();
    }

    public function update(PostUpdateRequest $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $post = ($this->updatePost)($post, PostUpdateData::fromRequest($request), $authorToken);

        return PostResource::make($post)->response();
    }

    public function destroy(Request $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        ($this->deletePost)($post, $authorToken);

        return response()->json(['status' => 'deleted']);
    }
}
