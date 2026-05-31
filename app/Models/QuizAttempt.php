<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class QuizAttempt extends Model
{
    protected $fillable = [
        'user_id', 'quiz_id', 'status', 'started_at', 'completed_at', 'score',
        'total_points', 'passed', 'answers', 'time_taken_seconds', 'attempt_number',
        'submission_key', 'ip_address', 'user_agent',
    ];

    protected function casts(): array
    {
        return [
            'answers' => 'array',
            'passed' => 'boolean',
            'score' => 'decimal:2',
            'started_at' => 'datetime',
            'completed_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function quiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class);
    }

    public function result(): HasOne
    {
        return $this->hasOne(QuizResult::class, 'attempt_id');
    }
}
