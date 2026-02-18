<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Complaint extends Model
{
    use HasFactory;

    public const STATUS_PENDING = 'pending';
    public const STATUS_REVIEWED = 'reviewed';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'post_id',
        'reason_code',
        'reason_text',
        'status',
        'author_token',
    ];

    public function post(): BelongsTo
    {
        return $this->belongsTo(Post::class);
    }
}
