<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscussionReply extends Model
{
    protected $fillable = [
        'discussion_thread_id',
        'author_id',
        'body',
        'upvoter_ids',
        'upvotes_count',
        'is_instructor_reply',
        'is_accepted_answer',
    ];

    protected function casts(): array
    {
        return [
            'upvoter_ids' => 'array',
            'upvotes_count' => 'integer',
            'is_instructor_reply' => 'boolean',
            'is_accepted_answer' => 'boolean',
        ];
    }

    public function thread(): BelongsTo
    {
        return $this->belongsTo(DiscussionThread::class, 'discussion_thread_id');
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
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
