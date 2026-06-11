<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class DiscussionThread extends Model
{
    protected $fillable = [
        'entity_type',
        'entity_id',
        'course_id',
        'author_id',
        'title',
        'body',
        'upvoter_ids',
        'upvotes_count',
        'is_pinned',
        'is_resolved',
        'replies_count',
    ];

    protected function casts(): array
    {
        return [
            'entity_id' => 'integer',
            'upvoter_ids' => 'array',
            'upvotes_count' => 'integer',
            'is_pinned' => 'boolean',
            'is_resolved' => 'boolean',
            'replies_count' => 'integer',
        ];
    }

    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class);
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class);
    }

    public function toggleUpvote(int|string $userId): bool
    {
        $userId = (string) $userId;
        $upvoters = array_values(array_unique(array_map('strval', $this->upvoter_ids ?? [])));

        if (in_array($userId, $upvoters, true)) {
            $upvoters = array_values(array_diff($upvoters, [$userId]));
            $upvoted = false;
        } else {
            $upvoters[] = $userId;
            $upvoted = true;
        }

        $this->forceFill([
            'upvoter_ids' => $upvoters,
            'upvotes_count' => count($upvoters),
        ])->save();

        return $upvoted;
    }

    public function isUpvotedBy(int|string|null $userId): bool
    {
        if (! $userId) {
            return false;
        }

        return in_array((string) $userId, array_map('strval', $this->upvoter_ids ?? []), true);
    }
}
