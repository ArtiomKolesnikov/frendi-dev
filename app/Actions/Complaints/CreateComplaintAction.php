<?php

namespace App\Actions\Complaints;

use App\DTO\ComplaintData;
use App\Models\Complaint;
use App\Models\Post;

class CreateComplaintAction
{
    public function __invoke(Post $post, ComplaintData $data, string $authorToken): Complaint
    {
        return $post->complaints()->create([
            'reason_code' => $data->reasonCode,
            'reason_text' => $data->reasonText,
            'author_token' => $authorToken,
            'status' => Complaint::STATUS_PENDING,
        ]);
    }
}

