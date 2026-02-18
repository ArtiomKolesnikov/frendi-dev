<?php

namespace App\Http\Controllers\Api;

use App\Actions\Shares\CreateShareAction;
use App\Actions\Shares\ShowSharePostAction;
use App\DTO\ShareData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShareStoreRequest;
use App\Http\Resources\PostResource;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ShareController extends Controller
{
    /** @var CreateShareAction */
    private $createShare;
    /** @var ShowSharePostAction */
    private $showSharePost;

    public function __construct(
        CreateShareAction $createShare,
        ShowSharePostAction $showSharePost
    ) {
        $this->createShare = $createShare;
        $this->showSharePost = $showSharePost;
    }

    public function store(ShareStoreRequest $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $shareUrl = ($this->createShare)($post, ShareData::fromRequest($request), $authorToken);

        return response()->json([
            'share_url' => $shareUrl,
        ]);
    }

    public function show(Request $request, string $slug): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);

        $post = ($this->showSharePost)($slug, $authorToken, $deviceFingerprint);

        return PostResource::make($post)->response();
    }
}
