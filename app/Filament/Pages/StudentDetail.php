<?php

namespace App\Filament\Pages;

use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;

class StudentDetail extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user';

    protected static ?string $navigationLabel = 'تفاصيل الطالب';

    protected static ?string $title = 'ملف الطالب';

    protected string $view = 'filament.pages.student-detail';

    public User $student;

    public function mount(int $id): void
    {
        $this->student = User::with('school')
            ->withCount(['accessGrants as enrolled_courses_count', 'lessonCompletions as completed_lessons_count', 'quizResults as quiz_attempts_count'])
            ->withAvg('quizResults', 'score_percentage')
            ->findOrFail($id);
    }

    public function getBreadcrumbs(): array
    {
        return [
            '/admin/users' => 'المستخدمين',
            '#' => $this->student->name,
        ];
    }

    protected static bool $shouldRegisterNavigation = false;

    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }
}
