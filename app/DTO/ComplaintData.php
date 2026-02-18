<?php

namespace App\DTO;

use App\Http\Requests\Api\ComplaintStoreRequest;

final class ComplaintData
{
    /** @var string|null */
    public $reasonCode;
    /** @var string|null */
    public $reasonText;

    public function __construct(
        ?string $reasonCode,
        ?string $reasonText
    ) {
        $this->reasonCode = $reasonCode;
        $this->reasonText = $reasonText;
    }

    public static function fromRequest(ComplaintStoreRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['reason_code'] ?? null,
            $validated['reason_text'] ?? null
        );
    }
}

