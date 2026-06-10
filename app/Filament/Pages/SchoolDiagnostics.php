<?php

namespace App\Filament\Pages;

use App\Models\Group;
use App\Models\QuizResult;
use App\Models\School;
use App\Models\SkillProgress;
use App\Models\User;
use BackedEnum;
use Filament\Pages\Page;
use Illuminate\Support\Collection;

class SchoolDiagnostics extends Page
{
    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-presentation-chart-line';

    protected static ?string $navigationLabel = 'تشخيص المدارس';

    protected static ?string $title = 'تشخيص المدارس والطلاب الضعاف';

    protected string $view = 'filament.pages.school-diagnostics';

    public ?int $schoolId = null;
    public ?int $groupId = null;
    public int $weakThreshold = 60;

    public int $studentsCount = 0;
    public int $weakStudentsCount = 0;
    public int $weakSkillsCount = 0;
    public int $averageScore = 0;

    public Collection $schools;
    public Collection $groups;
    public Collection $weakStudents;
    public Collection $weakSkills;
    public Collection $recentLowResults;

    public function mount(): void
    {
        $this->schools = School::query()->where('is_active', true)->orderBy('name_ar')->get();
        $this->schoolId = $this->schoolId ?: $this->schools->first()?->id;
        $this->refreshReport();
    }

    public function updatedSchoolId(): void
    {
        $this->groupId = null;
        $this->refreshReport();
    }

    public function updatedGroupId(): void
    {
        $this->refreshReport();
    }

    public function updatedWeakThreshold(): void
    {
        $this->refreshReport();
    }

    public function refreshReport(): void
    {
        $studentIds = $this->studentQuery()->pluck('users.id');

        $this->groups = Group::query()
            ->where('school_id', $this->schoolId)
            ->where('type', 'class')
            ->where('is_active', true)
            ->orderBy('name')
            ->get();

        $this->studentsCount = $studentIds->count();

        $this->weakStudents = User::query()
            ->whereIn('id', $studentIds)
            ->withAvg('quizResults', 'score_percentage')
            ->withCount('quizResults')
            ->with('school')
            ->get()
            ->filter(fn (User $student) => ($student->quiz_results_avg_score_percentage ?? 0) > 0
                && ($student->quiz_results_avg_score_percentage ?? 0) < $this->weakThreshold)
            ->sortBy('quiz_results_avg_score_percentage')
            ->take(20)
            ->values();

        $this->weakStudentsCount = $this->weakStudents->count();

        $this->weakSkills = SkillProgress::query()
            ->with(['user.school', 'skill.section.subject'])
            ->whereIn('user_id', $studentIds)
            ->where('mastery', '<', $this->weakThreshold)
            ->where('total_questions', '>', 0)
            ->orderBy('mastery')
            ->limit(30)
            ->get();

        $this->weakSkillsCount = $this->weakSkills->count();

        $avg = QuizResult::query()
            ->whereIn('user_id', $studentIds)
            ->avg('score_percentage');
        $this->averageScore = (int) round($avg ?? 0);

        $this->recentLowResults = QuizResult::query()
            ->with(['user.school', 'quiz'])
            ->whereIn('user_id', $studentIds)
            ->where('score_percentage', '<', $this->weakThreshold)
            ->latest('completed_at')
            ->limit(20)
            ->get();
    }

    private function studentQuery()
    {
        $query = User::query()->where('role', 'student');

        if ($this->groupId) {
            $query->whereIn('users.id', function ($subquery): void {
                $subquery->select('user_id')
                    ->from('group_user')
                    ->where('group_id', $this->groupId)
                    ->where('role', 'student');
            });
        } elseif ($this->schoolId) {
            $query->where('school_id', $this->schoolId);
        }

        return $query;
    }
}
