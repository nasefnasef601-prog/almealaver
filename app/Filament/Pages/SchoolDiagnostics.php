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
    public int $untestedStudentsCount = 0;
    public int $averageScore = 0;

    public Collection $schools;
    public Collection $groups;
    public Collection $weakStudents;
    public Collection $weakSkills;
    public Collection $skillHotspots;
    public Collection $treatmentPlans;
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
        $this->untestedStudentsCount = User::query()
            ->whereIn('id', $studentIds)
            ->withCount('quizResults')
            ->get()
            ->where('quiz_results_count', 0)
            ->count();

        $this->weakSkills = SkillProgress::query()
            ->with(['user.school', 'skill.section.subject'])
            ->whereIn('user_id', $studentIds)
            ->where('mastery', '<', $this->weakThreshold)
            ->where('total_questions', '>', 0)
            ->orderBy('mastery')
            ->limit(30)
            ->get();

        $this->weakSkillsCount = $this->weakSkills->count();
        $this->skillHotspots = $this->weakSkills
            ->groupBy('skill_id')
            ->map(function (Collection $items) {
                $first = $items->first();

                return [
                    'skill_id' => $first?->skill_id,
                    'skill_name' => $first?->skill?->name_ar ?: $first?->skill?->name ?: 'مهارة',
                    'subject_name' => $first?->skill?->section?->subject?->name_ar ?: $first?->skill?->section?->subject?->name,
                    'students_count' => $items->pluck('user_id')->unique()->count(),
                    'average_mastery' => (int) round((float) $items->avg('mastery')),
                    'total_questions' => (int) $items->sum('total_questions'),
                ];
            })
            ->sortBy([
                ['students_count', 'desc'],
                ['average_mastery', 'asc'],
            ])
            ->take(10)
            ->values();

        $this->treatmentPlans = $this->weakStudents
            ->map(fn (User $student) => [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'email' => $student->email,
                'average_score' => (float) ($student->quiz_results_avg_score_percentage ?? 0),
                'weak_skills' => $this->weakSkills
                    ->where('user_id', $student->id)
                    ->take(3)
                    ->map(fn (SkillProgress $progress) => [
                        'name' => $progress->skill?->name_ar ?: $progress->skill?->name ?: 'مهارة',
                        'mastery' => (float) $progress->mastery,
                    ])
                    ->values(),
                'plan' => $this->treatmentPlanFor((float) ($student->quiz_results_avg_score_percentage ?? 0)),
            ])
            ->values();

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

    public function exportWeakStudentsCsv()
    {
        $this->refreshReport();

        $filename = 'school-weak-students-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Student', 'Email', 'Average score', 'Attempts', 'Weak skills', 'Suggested treatment plan']);

            foreach ($this->treatmentPlans as $plan) {
                fputcsv($handle, [
                    $plan['student_name'],
                    $plan['email'],
                    number_format((float) $plan['average_score'], 1) . '%',
                    $this->weakStudents->firstWhere('id', $plan['student_id'])?->quiz_results_count ?? 0,
                    $plan['weak_skills']->map(fn (array $skill) => $skill['name'] . ' (' . number_format((float) $skill['mastery'], 1) . '%)')->implode(' | '),
                    implode(' | ', $plan['plan']),
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    private function treatmentPlanFor(float $averageScore): array
    {
        if ($averageScore < 40) {
            return [
                'جلسة تأسيس قصيرة للمهارة الأضعف',
                'واجب علاجي من 10 أسئلة متدرجة',
                'إعادة اختبار بعد 7 أيام',
            ];
        }

        if ($averageScore < $this->weakThreshold) {
            return [
                'تدريب مركز على الأخطاء المتكررة',
                'اختبار قصير بعد كل درس',
                'متابعة المشرف في نهاية الأسبوع',
            ];
        }

        return [
            'مراجعة خفيفة للحفاظ على المستوى',
            'اختبار قياس بعد أسبوعين',
        ];
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
            $query->where(function ($schoolQuery): void {
                $schoolQuery
                    ->where('school_id', $this->schoolId)
                    ->orWhereIn('users.id', function ($subquery): void {
                        $subquery->select('group_user.user_id')
                            ->from('group_user')
                            ->join('groups', 'groups.id', '=', 'group_user.group_id')
                            ->where('groups.school_id', $this->schoolId)
                            ->where('group_user.role', 'student');
                    });
            });
        }

        return $query;
    }
}
