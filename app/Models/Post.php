<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Models\PostReaction;

class Post extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_APPROVED = 'approved';
    public const STATUS_REJECTED = 'rejected';

    public const TYPE_ROUTE = 'route';
    public const TYPE_PET = 'pet';
    public const TYPE_MY_DOG = 'my_dog';
    public const TYPE_CONTEST = 'contest';

    public const TYPES = [
        self::TYPE_ROUTE,
        self::TYPE_PET,
        self::TYPE_MY_DOG,
        self::TYPE_CONTEST,
    ];

    protected $fillable = [
        'uuid',
        'type',
        'status',
        'title',
        'body',
        'meta',
        'author_display_name',
        'author_contact',
        'author_token',
        'is_admin',
        'share_slug',
        'published_at',
    ];

    protected $casts = [
        'meta' => 'array',
        'is_admin' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function booted(): void
    {
        static::creating(function (Post $post) {
            if (empty($post->uuid)) {
                $post->uuid = (string) Str::uuid();
            }

            if (empty($post->share_slug)) {
                $post->share_slug = Str::slug(Str::limit($post->title ?? Str::random(6), 40, '')) . '-' . Str::lower(Str::random(6));
            }
        });

        static::saving(function (Post $post) {
            if ($post->status === self::STATUS_APPROVED) {
                $post->published_at = $post->published_at ?? now();
            } else {
                $post->published_at = null;
            }
        });

        static::deleting(function (Post $post) {
            $post->media()->each(function ($media) {
                Storage::disk($media->disk ?? 'public')->delete($media->path);
            });
        });
    }

    public function scopeVisibleFor(Builder $query, ?string $authorToken): Builder
    {
        return $query->where(function (Builder $inner) use ($authorToken) {
            $inner->where('status', self::STATUS_APPROVED);

            if ($authorToken) {
                $inner->orWhere('author_token', $authorToken);
            }
        });
    }

    public function scopeWithUserReaction(Builder $query, ?string $authorToken, ?string $deviceFingerprint = null): Builder
    {
        $adminId = session()->get('admin_id');
        if ($adminId) {
            return $query->addSelect([
                'user_reaction' => PostReaction::select('type')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('admin_id', $adminId)
                    ->limit(1),
            ]);
        }

        $userId = auth()->id();
        if ($userId) {
            return $query->addSelect([
                'user_reaction' => PostReaction::select('type')
                    ->whereColumn('post_id', 'posts.id')
                    ->where(function ($q) use ($userId, $deviceFingerprint) {
                        $q->where('user_id', $userId);
                        if ($deviceFingerprint) {
                            $q->orWhere('device_fingerprint', $deviceFingerprint);
                        }
                    })
                    ->limit(1),
            ]);
        }

        // For guests, use device fingerprint if available
        if ($deviceFingerprint) {
            return $query->addSelect([
                'user_reaction' => PostReaction::select('type')
                    ->whereColumn('post_id', 'posts.id')
                    ->where('device_fingerprint', $deviceFingerprint)
                    ->limit(1),
            ]);
        }

        return $query;
    }

    public function media(): HasMany
    {
        return $this->hasMany(PostMedia::class)->orderBy('position');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(PostReaction::class);
    }

    public function complaints(): HasMany
    {
        return $this->hasMany(Complaint::class);
    }
}
