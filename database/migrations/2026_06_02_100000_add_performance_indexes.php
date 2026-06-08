<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private function tryIndex(string $table, array $columns): void
    {
        try {
            Schema::table($table, function (Blueprint $t) use ($columns) {
                $t->index($columns);
            });
        } catch (\Throwable $e) {
            // Already exists or not supported, ignore
        }
    }

    public function up(): void
    {
        // Users
        $this->tryIndex('users', ['role']);
        $this->tryIndex('users', ['school_id']);
        $this->tryIndex('users', ['created_at']);

        // Courses
        $this->tryIndex('courses', ['is_published']);
        $this->tryIndex('courses', ['price']);
        $this->tryIndex('courses', ['created_at']);

        // Lessons
        $this->tryIndex('lessons', ['course_id']);
        $this->tryIndex('lessons', ['is_published']);
        $this->tryIndex('lessons', ['sort_order']);

        // Quizzes
        $this->tryIndex('quizzes', ['course_id']);
        $this->tryIndex('quizzes', ['is_published']);
        $this->tryIndex('quizzes', ['created_at']);

        // Questions
        $this->tryIndex('questions', ['quiz_id']);
        $this->tryIndex('questions', ['skill_id']);
        $this->tryIndex('questions', ['question_type']);

        // Quiz Results
        $this->tryIndex('quiz_results', ['user_id']);
        $this->tryIndex('quiz_results', ['quiz_id']);
        $this->tryIndex('quiz_results', ['created_at']);

        // Quiz Attempts
        $this->tryIndex('quiz_attempts', ['user_id']);
        $this->tryIndex('quiz_attempts', ['quiz_id']);
        $this->tryIndex('quiz_attempts', ['status']);

        // Skill Progress
        $this->tryIndex('skill_progress', ['user_id']);
        $this->tryIndex('skill_progress', ['skill_id']);
        $this->tryIndex('skill_progress', ['mastery']);

        // Access Grants
        $this->tryIndex('access_grants', ['user_id']);
        $this->tryIndex('access_grants', ['course_id']);
        $this->tryIndex('access_grants', ['status']);

        // Lesson Completions
        $this->tryIndex('lesson_completions', ['user_id']);
        $this->tryIndex('lesson_completions', ['lesson_id']);
        $this->tryIndex('lesson_completions', ['course_id']);
        $this->tryIndex('lesson_completions', ['created_at']);

        // Payment Requests
        $this->tryIndex('payment_requests', ['user_id']);
        $this->tryIndex('payment_requests', ['status']);
        $this->tryIndex('payment_requests', ['created_at']);

        // Notifications
        $this->tryIndex('notifications', ['user_id']);
        $this->tryIndex('notifications', ['type']);
        $this->tryIndex('notifications', ['created_at']);

        // Favorites
        $this->tryIndex('favorites', ['user_id']);
        $this->tryIndex('favorites', ['favoritable_type', 'favoritable_id']);

        // Schools
        $this->tryIndex('schools', ['created_at']);
    }

    public function down(): void
    {
        // Don't crash on dropping indexes that might not exist in SQLite
        $tables = [
            'users' => ['role'],
            'courses' => ['is_published'],
            'lessons' => ['course_id'],
            'quizzes' => ['course_id'],
            'questions' => ['quiz_id'],
            'quiz_results' => ['user_id'],
            'quiz_attempts' => ['user_id'],
            'skill_progress' => ['user_id'],
            'access_grants' => ['user_id'],
            'lesson_completions' => ['user_id'],
            'payment_requests' => ['user_id'],
            'notifications' => ['user_id'],
            'favorites' => ['user_id'],
            'schools' => ['created_at'],
        ];

        foreach ($tables as $table => $columns) {
            try {
                Schema::table($table, function (Blueprint $t) use ($columns) {
                    $t->dropIndex($columns);
                });
            } catch (\Throwable $e) {
                // Ignore
            }
        }
    }
};
