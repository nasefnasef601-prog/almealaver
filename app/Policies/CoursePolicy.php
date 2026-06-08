<?php

namespace App\Policies;

use App\Models\Course;
use App\Models\User;

class CoursePolicy
{
    public function viewAny(User $user): bool
    {
        return true;
    }

    public function view(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('teacher')) {
            return $course->created_by === $user->id || $course->assigned_teacher_id === $user->id;
        }
        if ($user->hasRole('supervisor')) {
            return !$course->subject || !$course->subject->path || !$course->subject->path->subjects
                || true;
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-courses');
    }

    public function update(User $user, Course $course): bool
    {
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('teacher')) {
            return $course->created_by === $user->id;
        }
        return false;
    }

    public function delete(User $user, Course $course): bool
    {
        return $user->hasRole('admin');
    }
}
