<?php

namespace App\Http\Resources;

use App\Support\ClientContext;
use Illuminate\Http\Resources\Json\JsonResource;

class PostResource extends JsonResource
{
    public function toArray($request): array
    {
        $requestToken = ClientContext::token($request);

        return [
            'id' => $this->id,
            'uuid' => $this->uuid,
            'type' => $this->type,
            'status' => $this->status,
            'title' => $this->title,
            'body' => $this->body,
            'meta' => $this->meta,
            'author_display_name' => $this->author_display_name,
            'author_contact' => $this->author_contact,
            'created_at' => $this->created_at,
            'published_at' => $this->published_at,
            'likes_count' => isset($this->likes_count)
                ? (int) $this->likes_count
                : (int) $this->reactions()->where('type', 'like')->count(),
            'dislikes_count' => isset($this->dislikes_count)
                ? (int) $this->dislikes_count
                : (int) $this->reactions()->where('type', 'dislike')->count(),
            'comments_count' => isset($this->comments_count)
                ? (int) $this->comments_count
                : (int) $this->comments()->count(),
            'share_url' => url('/share/'.$this->share_slug),
            'user_reaction' => $this->user_reaction ?? null,
            'is_owner' => $this->author_token
                ? hash_equals($this->author_token, $requestToken)
                : false,
            'media' => PostMediaResource::collection($this->whenLoaded('media')),
            'comments' => CommentResource::collection($this->whenLoaded('comments')),
        ];
    }
}
