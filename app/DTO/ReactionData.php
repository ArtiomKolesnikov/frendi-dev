<?php

namespace App\DTO;

use App\Http\Requests\Api\ReactionStoreRequest;

final class ReactionData
{
    /** @var string */
    public $type;
    /** @var string|null */
    public $authorDisplayName;

    public function __construct(
        string $type,
        ?string $authorDisplayName
    ) {
        $this->type = $type;
        $this->authorDisplayName = $authorDisplayName;
    }

    public static function fromRequest(ReactionStoreRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['type'],
            $validated['author_display_name'] ?? null
        );
    }
}

