<?php

namespace App\Http\Resources;

use Illuminate\Http\Resources\Json\JsonResource;

class PostMediaResource extends JsonResource
{
    public function toArray($request): array
    {
        return [
            'id' => $this->id,
            'url' => asset('storage/'.$this->path),
            'caption' => $this->caption,
            'position' => $this->position,
        ];
    }
}
