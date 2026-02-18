<?php

namespace App\Actions\Admin;

use App\Models\Complaint;
use Illuminate\Pagination\LengthAwarePaginator;

class ListComplaintsForModerationAction
{
    public function __invoke(?string $status): LengthAwarePaginator
    {
        return Complaint::query()
            ->when($status, function ($q) use ($status) {
                return $q->where('status', $status);
            })
            ->with('post.media')
            ->latest()
            ->paginate(20)
            ->withQueryString();
    }
}

