<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class ComplaintResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'post_id' => $this->post_id,
            'reason_code' => $this->reason_code,
            'reason_text' => $this->reason_text,
            'status' => $this->status,
            'created_at' => $this->created_at,
            'post' => new PostResource($this->whenLoaded('post')),
        ];
    }
}
