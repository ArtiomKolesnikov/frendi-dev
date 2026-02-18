<?php

namespace App\Http\Controllers\Api;

use App\Actions\Reactions\ToggleReactionAction;
use App\DTO\ReactionData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ReactionStoreRequest;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;

class ReactionController extends Controller
{
    /** @var ToggleReactionAction */
    private $toggleReaction;

    public function __construct(ToggleReactionAction $toggleReaction)
    {
        $this->toggleReaction = $toggleReaction;
    }

    public function store(ReactionStoreRequest $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $deviceFingerprint = ClientContext::fingerprint($request);
        $data = ReactionData::fromRequest($request);

        $payload = ($this->toggleReaction)($post, $data, $authorToken, $deviceFingerprint);

        return response()->json($payload);
    }
}
