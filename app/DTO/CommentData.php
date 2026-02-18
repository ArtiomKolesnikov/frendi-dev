<?php

namespace App\DTO;

use App\Http\Requests\Api\CommentStoreRequest;

final class CommentData
{
    /** @var string */
    public $body;
    /** @var string|null */
    public $authorDisplayName;

    public function __construct(
        string $body,
        ?string $authorDisplayName
    ) {
        $this->body = $body;
        $this->authorDisplayName = $authorDisplayName;
    }

    public static function fromRequest(CommentStoreRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['body'],
            $validated['author_display_name'] ?? null
        );
    }
}

