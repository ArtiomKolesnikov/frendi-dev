<?php

namespace App\Actions\Admin;

use App\Models\Complaint;

class UpdateComplaintStatusAction
{
    public function __invoke(Complaint $complaint, string $status): Complaint
    {
        $complaint->update(['status' => $status]);

        return $complaint->load('post.media');
    }
}

