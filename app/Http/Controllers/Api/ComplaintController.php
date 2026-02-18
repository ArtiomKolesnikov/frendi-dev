<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Complaint;
use App\Models\Post;
use App\Support\ClientContext;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintController extends Controller
{
    public function store(Request $request, Post $post): JsonResponse
    {
        $validated = $request->validate([
            'reason_code' => ['nullable', 'string', 'max:50'],
            'reason_text' => ['nullable', 'string', 'max:1000'],
        ]);

        $authorToken = ClientContext::token($request);

        $complaint = $post->complaints()->create([
            'reason_code' => $validated['reason_code'] ?? null,
            'reason_text' => $validated['reason_text'] ?? null,
            'author_token' => $authorToken,
            'status' => Complaint::STATUS_PENDING,
        ]);

        return response()->json([
            'status' => 'submitted',
            'complaint_id' => $complaint->id,
        ], 201);
    }
}
