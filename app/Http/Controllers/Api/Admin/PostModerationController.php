<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\ListPostsForModerationAction;
use App\Actions\Admin\UpdatePostStatusAction;
use App\Http\Requests\Api\Admin\PostStatusUpdateRequest;
use App\Http\Resources\PostResource;
use App\Models\Post;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class PostModerationController extends AdminController
{
    /** @var ListPostsForModerationAction */
    private $listPosts;
    /** @var UpdatePostStatusAction */
    private $updatePostStatus;

    public function __construct(
        ListPostsForModerationAction $listPosts,
        UpdatePostStatusAction $updatePostStatus
    ) {
        $this->listPosts = $listPosts;
        $this->updatePostStatus = $updatePostStatus;
    }

    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $status = $request->input('status', Post::STATUS_PENDING);
        $posts = ($this->listPosts)($status);

        return PostResource::collection($posts)->response();
    }

    public function updateStatus(PostStatusUpdateRequest $request, Post $post): JsonResponse
    {
        $this->ensureAdmin($request);

        $post = ($this->updatePostStatus)($post, $request->string('status')->toString());

        return PostResource::make($post)->response();
    }
}
