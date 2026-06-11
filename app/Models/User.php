<?php

declare(strict_types=1);

namespace App\Models;

use Database\Factories\UserFactory;
use Filament\Models\Contracts\FilamentUser;
use Filament\Panel;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable implements FilamentUser
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, HasRoles, Notifiable;

    protected $fillable = [
        'name', 'email', 'password', 'phone', 'role', 'school_id', 'is_active', 'profile_photo_path',
    ];

    protected $hidden = [
        'password', 'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_active' => 'boolean',
        ];
    }

    public function canAccessPanel(Panel $panel): bool
    {
        return in_array($this->role, ['admin', 'supervisor'], true)
            || $this->hasAnyRole(['admin', 'supervisor']);
    }

    public function school(): BelongsTo
    {
        return $this->belongsTo(School::class);
    }

    public function linkedStudents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'parent_id', 'student_id');
    }

    public function linkedParents(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'parent_student', 'student_id', 'parent_id');
    }

    public function courses(): HasMany
    {
        return $this->hasMany(Course::class, 'created_by');
    }

    public function assignedCourses(): HasMany
    {
        return $this->hasMany(Course::class, 'assigned_teacher_id');
    }

    public function quizAttempts(): HasMany
    {
        return $this->hasMany(QuizAttempt::class);
    }

    public function quizResults(): HasMany
    {
        return $this->hasMany(QuizResult::class);
    }

    public function paymentRequests(): HasMany
    {
        return $this->hasMany(PaymentRequest::class);
    }

    public function accessGrants(): HasMany
    {
        return $this->hasMany(AccessGrant::class);
    }

    public function lessonCompletions(): HasMany
    {
        return $this->hasMany(LessonCompletion::class);
    }

    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    public function reports(): HasMany
    {
        return $this->hasMany(Report::class);
    }

    public function studyPlans(): HasMany
    {
        return $this->hasMany(StudyPlan::class);
    }

    public function createdQuizzes(): HasMany
    {
        return $this->hasMany(Quiz::class, 'created_by');
    }

    public function createdQuestions(): HasMany
    {
        return $this->hasMany(Question::class, 'created_by');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function reviewLaters(): HasMany
    {
        return $this->hasMany(ReviewLater::class);
    }

    public function discussionThreads(): HasMany
    {
        return $this->hasMany(DiscussionThread::class, 'author_id');
    }

    public function discussionReplies(): HasMany
    {
        return $this->hasMany(DiscussionReply::class, 'author_id');
    }
}
