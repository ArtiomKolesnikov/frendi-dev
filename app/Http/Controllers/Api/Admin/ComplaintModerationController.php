<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ComplaintModerationController extends AdminController
{
    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $status = $request->input('status', Complaint::STATUS_PENDING);

        $complaints = Complaint::query()
            ->when($status, fn ($q) => $q->where('status', $status))
            ->with('post.media')
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return ComplaintResource::collection($complaints)->response();
    }

    public function updateStatus(Request $request, Complaint $complaint): JsonResponse
    {
        $this->ensureAdmin($request);

        $validated = $request->validate([
            'status' => ['required', Rule::in([
                Complaint::STATUS_PENDING,
                Complaint::STATUS_REVIEWED,
                Complaint::STATUS_REJECTED,
            ])],
        ]);

        $complaint->update(['status' => $validated['status']]);

        $complaint->load('post.media');

        return ComplaintResource::make($complaint)->response();
    }
}
