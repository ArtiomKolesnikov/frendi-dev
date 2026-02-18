<?php

namespace App\DTO;

use App\Http\Requests\Api\FeedIndexRequest;

final class FeedFilterData
{
    public $perPage;

    /**
     * @var string[]|null
     */
    public $types;

    /**
     * @param string[]|null $types
     */
    public function __construct(int $perPage, ?array $types)
    {
        $this->perPage = $perPage;
        $this->types = $types;
    }

    public static function fromRequest(FeedIndexRequest $request): self
    {
        $validated = $request->validated();
        $types = $validated['types'] ?? null;

        return new self(
            (int) ($validated['per_page'] ?? 10),
            $types ? array_values((array) $types) : null
        );
    }
}

