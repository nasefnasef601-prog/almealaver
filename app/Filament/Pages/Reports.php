<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\CourseEnrollmentsChart;
use App\Filament\Widgets\QuizCompletionChart;
use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\LessonCompletion;
use App\Models\PaymentRequest;
use App\Models\QuizResult;
use App\Models\SkillProgress;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;

class Reports extends Page
{
    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-chart-bar-square';

    protected static ?string $navigationLabel = 'التقارير';

    protected static ?string $title = 'التقارير والإحصائيات';

    protected string $view = 'filament.pages.reports';

    public int $totalStudents = 0;
    public int $totalCourses = 0;
    public int $totalCompletions = 0;
    public int $totalQuizAttempts = 0;
    public int $totalRevenue = 0;
    public int $studentsActiveToday = 0;
    public int $avgQuizScore = 0;
    public int $totalSkillsMastered = 0;

    public function mount(): void
    {
        $studentRole = User::where('role', 'student');
        $this->totalStudents = (clone $studentRole)->count();
        $this->totalCourses = Course::count();
        $this->totalCompletions = LessonCompletion::count();
        $this->totalQuizAttempts = QuizResult::count();
        $this->totalRevenue = (int) PaymentRequest::where('status', 'approved')->sum('amount');
        $this->studentsActiveToday = (clone $studentRole)->whereDate('updated_at', today())->count();

        $avg = QuizResult::avg('score_percentage');
        $this->avgQuizScore = (int) round($avg ?? 0);

        $this->totalSkillsMastered = SkillProgress::where('mastery', '>=', 80)->count();
    }

    public function getWidgets(): array
    {
        return [
            CourseEnrollmentsChart::make(),
            QuizCompletionChart::make(),
        ];
    }

    protected function getHeaderWidgets(): array
    {
        return $this->getWidgets();
    }
}
