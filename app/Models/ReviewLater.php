<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLater extends Model
{
    protected $table = 'review_laters';

    protected $fillable = [
        'user_id', 'question_id',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function question(): BelongsTo
    {
        return $this->belongsTo(Question::class);
    }

    public static function isMarked(int $userId, int $questionId): bool
    {
        return static::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->exists();
    }

    public static function toggle(int $userId, int $questionId): bool
    {
        $existing = static::where('user_id', $userId)
            ->where('question_id', $questionId)
            ->first();

        if ($existing) {
            $existing->delete();
            return false;
        }

        static::create([
            'user_id' => $userId,
            'question_id' => $questionId,
        ]);
        return true;
    }
}
