<?php

namespace App\DTO;

use App\Http\Requests\Api\PostStoreRequest;
use Illuminate\Http\UploadedFile;

final class PostData
{
    /** @var string */
    public $type;
    /** @var string|null */
    public $title;
    /** @var string|null */
    public $body;
    /** @var array|null */
    public $meta;
    /** @var string|null */
    public $authorDisplayName;
    /** @var string|null */
    public $authorContact;
    /**
     * @var UploadedFile[]
     */
    public $media = [];

    /**
     * @param UploadedFile[] $media
     */
    public function __construct(
        string $type,
        ?string $title,
        ?string $body,
        ?array $meta,
        ?string $authorDisplayName,
        ?string $authorContact,
        array $media = [],
    ) {
        $this->type = $type;
        $this->title = $title;
        $this->body = $body;
        $this->meta = $meta;
        $this->authorDisplayName = $authorDisplayName;
        $this->authorContact = $authorContact;
        $this->media = $media;
    }

    public static function fromRequest(PostStoreRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['type'],
            $validated['title'] ?? null,
            $validated['body'] ?? null,
            $validated['meta'] ?? null,
            $validated['author_display_name'] ?? null,
            $validated['author_contact'] ?? null,
            $request->file('media', [])
        );
    }
}

