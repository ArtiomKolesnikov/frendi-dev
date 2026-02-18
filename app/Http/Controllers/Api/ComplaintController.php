<?php

namespace App\Http\Controllers\Api;

use App\Actions\Complaints\CreateComplaintAction;
use App\DTO\ComplaintData;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ComplaintStoreRequest;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;

class ComplaintController extends Controller
{
    /** @var CreateComplaintAction */
    private $createComplaint;

    public function __construct(CreateComplaintAction $createComplaint)
    {
        $this->createComplaint = $createComplaint;
    }

    public function store(ComplaintStoreRequest $request, Post $post): JsonResponse
    {
        $authorToken = ClientContext::token($request);
        $complaint = ($this->createComplaint)($post, ComplaintData::fromRequest($request), $authorToken);

        return response()->json([
            'status' => 'submitted',
            'complaint_id' => $complaint->id,
        ], 201);
    }
}
