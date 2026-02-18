<?php

namespace App\DTO;

use App\Http\Requests\Api\PostUpdateRequest;
use Illuminate\Http\UploadedFile;

final class PostUpdateData
{
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
     * @var int[]
     */
    public $removeMediaIds = [];

    /**
     * @param UploadedFile[] $media
     * @param int[] $removeMediaIds
     */
    public function __construct(
        ?string $title,
        ?string $body,
        ?array $meta,
        ?string $authorDisplayName,
        ?string $authorContact,
        array $media = [],
        array $removeMediaIds = [],
    ) {
        $this->title = $title;
        $this->body = $body;
        $this->meta = $meta;
        $this->authorDisplayName = $authorDisplayName;
        $this->authorContact = $authorContact;
        $this->media = $media;
        $this->removeMediaIds = $removeMediaIds;
    }

    public static function fromRequest(PostUpdateRequest $request): self
    {
        $validated = $request->validated();

        return new self(
            $validated['title'] ?? null,
            $validated['body'] ?? null,
            $validated['meta'] ?? null,
            $validated['author_display_name'] ?? null,
            $validated['author_contact'] ?? null,
            $request->file('media', []),
            $validated['remove_media_ids'] ?? []
        );
    }
}

