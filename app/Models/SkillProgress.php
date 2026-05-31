<?php

declare(strict_types=1);

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SkillProgress extends Model
{
    protected $table = 'skill_progress';

    protected $fillable = [
        'user_id', 'skill_id', 'mastery', 'status',
        'total_attempts', 'correct_answers', 'total_questions',
        'last_quiz_id', 'last_quiz_title', 'last_attempt_at',
    ];

    protected function casts(): array
    {
        return [
            'mastery' => 'decimal:1',
            'last_attempt_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function skill(): BelongsTo
    {
        return $this->belongsTo(Skill::class);
    }

    public function lastQuiz(): BelongsTo
    {
        return $this->belongsTo(Quiz::class, 'last_quiz_id');
    }
}
