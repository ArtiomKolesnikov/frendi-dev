<?php

namespace App\Http\Controllers\Api\Admin;

use App\Actions\Admin\ListComplaintsForModerationAction;
use App\Actions\Admin\UpdateComplaintStatusAction;
use App\Http\Requests\Api\Admin\ComplaintStatusUpdateRequest;
use App\Http\Resources\ComplaintResource;
use App\Models\Complaint;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ComplaintModerationController extends AdminController
{
    /** @var ListComplaintsForModerationAction */
    private $listComplaints;
    /** @var UpdateComplaintStatusAction */
    private $updateComplaintStatus;

    public function __construct(
        ListComplaintsForModerationAction $listComplaints,
        UpdateComplaintStatusAction $updateComplaintStatus
    ) {
        $this->listComplaints = $listComplaints;
        $this->updateComplaintStatus = $updateComplaintStatus;
    }

    public function index(Request $request): JsonResponse
    {
        $this->ensureAdmin($request);

        $status = $request->input('status', Complaint::STATUS_PENDING);
        $complaints = ($this->listComplaints)($status);

        return ComplaintResource::collection($complaints)->response();
    }

    public function updateStatus(ComplaintStatusUpdateRequest $request, Complaint $complaint): JsonResponse
    {
        $this->ensureAdmin($request);

        $complaint = ($this->updateComplaintStatus)($complaint, $request->string('status')->toString());

        return ComplaintResource::make($complaint)->response();
    }
}
