<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReviewLater extends Model
{
    protected $table = 'review_laters';

    protected $fillable = [
        'user_id',
        'question_id',
        'ease_factor',
        'interval_days',
        'repetitions',
        'next_review_at',
        'last_quality',
    ];

    protected function casts(): array
    {
        return [
            'ease_factor' => 'float',
            'interval_days' => 'integer',
            'repetitions' => 'integer',
            'next_review_at' => 'datetime',
            'last_quality' => 'integer',
        ];
    }

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
            'next_review_at' => now(),
        ]);
        return true;
    }

    public function applyReviewQuality(int $quality): void
    {
        $quality = max(0, min(5, $quality));
        $easeFactor = $this->ease_factor ?: 2.5;
        $interval = max(1, (int) ($this->interval_days ?: 1));
        $repetitions = max(0, (int) ($this->repetitions ?: 0));

        if ($quality >= 3) {
            if ($repetitions === 0) {
                $interval = 1;
            } elseif ($repetitions === 1) {
                $interval = 6;
            } else {
                $interval = max(1, (int) round($interval * $easeFactor));
            }

            $easeFactor = $easeFactor + 0.1 - (5 - $quality) * (0.08 + (5 - $quality) * 0.02);
            $easeFactor = max(1.3, $easeFactor);
            $repetitions++;
        } else {
            $repetitions = 0;
            $interval = 1;
        }

        $this->update([
            'ease_factor' => round($easeFactor, 3),
            'interval_days' => $interval,
            'repetitions' => $repetitions,
            'next_review_at' => now()->addDays($interval),
            'last_quality' => $quality,
        ]);
    }
}
