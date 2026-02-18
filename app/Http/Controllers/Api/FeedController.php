<?php

namespace App\Http\Controllers\Api;

use App\Actions\Feed\GetFeedAction;
use App\DTO\FeedFilterData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\FeedIndexRequest;
use App\Http\Resources\PostResource;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;

class FeedController extends Controller
{
    /** @var GetFeedAction */
    private $getFeedAction;

    public function __construct(GetFeedAction $getFeedAction)
    {
        $this->getFeedAction = $getFeedAction;
    }

    public function index(FeedIndexRequest $request): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);
        $filter = FeedFilterData::fromRequest($request);

        $paginator = ($this->getFeedAction)($filter, $authorToken, $deviceFingerprint);

        return PostResource::collection($paginator)->response();
    }
}
