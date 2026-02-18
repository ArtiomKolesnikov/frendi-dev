<?php

namespace App\DTO;

use App\Http\Requests\Api\ShareStoreRequest;

final class ShareData
{
    /** @var string|null */
    public $channel;

    public function __construct(
        ?string $channel
    ) {
        $this->channel = $channel;
    }

    public static function fromRequest(ShareStoreRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['channel'] ?? null
        );
    }
}

