<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Builder;

class Comment extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'post_id',
        'status',
        'body',
        'author_display_name',
        'author_token',
    ];

    public function scopeVisibleFor(Builder $query, ?string $authorToken): Builder
    {
        return $query->where(function ($inner) use ($authorToken) {
            $inner->where('status', self::STATUS_APPROVED);

            if ($authorToken) {
                $inner->orWhere('author_token', $authorToken);
            }
        });
    }

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
