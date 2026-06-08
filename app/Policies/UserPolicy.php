<?php

namespace App\Policies;

use App\Models\User;

class UserPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasPermissionTo('manage-users');
    }

    public function view(User $user, User $model): bool
    {
        if ($user->id === $model->id) return true;
        if ($user->hasRole('admin')) return true;
        if ($user->hasRole('supervisor') && $model->school_id === $user->school_id) return true;
        if ($user->hasRole('parent')) {
            return $user->linkedStudents()->where('student_id', $model->id)->exists();
        }
        return false;
    }

    public function create(User $user): bool
    {
        return $user->hasPermissionTo('manage-users');
    }

    public function update(User $user, User $model): bool
    {
        if ($user->id === $model->id) return true;
        return $user->hasRole('admin');
    }

    public function delete(User $user, User $model): bool
    {
        return $user->hasRole('admin') && $user->id !== $model->id;
    }
}
