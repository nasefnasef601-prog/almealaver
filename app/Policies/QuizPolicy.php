<?php

namespace App\Policies;

use App\Models\Quiz;
use App\Models\User;

class QuizPolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Quiz $quiz): bool
    {
        return true;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-quizzes');
    }

    public function update(User $user, Quiz $quiz): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('teacher')) return $quiz->created_by === $user->id;
        return false;
    }

    public function delete(User $user, Quiz $quiz): bool
    {
        return $user->hasRole('admin');
    }
}
