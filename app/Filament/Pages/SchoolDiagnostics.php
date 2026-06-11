<?php

namespace App\Filament\Pages;

use App\Models\Group;
use App\Models\QuizResult;
use App\Models\School;
use App\Models\SkillProgress;
use App\Models\StudyPlan;
use App\Models\User;
use BackedEnum;
use Filament\Notifications\Notification;
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
        $this->schools = $this->schoolQuery()->orderBy('name_ar')->get();
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
        $this->normalizeScope();

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

    public function exportSkillHotspotsCsv()
    {
        $this->refreshReport();

        $filename = 'school-weak-skill-hotspots-' . now()->format('Y-m-d-His') . '.csv';

        return response()->streamDownload(function (): void {
            $handle = fopen('php://output', 'w');
            fwrite($handle, "\xEF\xBB\xBF");
            fputcsv($handle, ['Skill', 'Subject', 'Weak students', 'Average mastery', 'Measured questions']);

            foreach ($this->skillHotspots as $hotspot) {
                fputcsv($handle, [
                    $hotspot['skill_name'],
                    $hotspot['subject_name'],
                    $hotspot['students_count'],
                    number_format((float) $hotspot['average_mastery'], 1) . '%',
                    $hotspot['total_questions'],
                ]);
            }

            fclose($handle);
        }, $filename, ['Content-Type' => 'text/csv; charset=UTF-8']);
    }

    public function createTreatmentPlanFor(int $studentId): void
    {
        abort_unless($this->studentQuery()->where('users.id', $studentId)->exists(), 403);

        $student = User::query()
            ->where('role', 'student')
            ->findOrFail($studentId);
        $weakSkill = $this->weakSkills->firstWhere('user_id', $student->id)
            ?: SkillProgress::query()
                ->where('user_id', $student->id)
                ->where('mastery', '<', $this->weakThreshold)
                ->where('total_questions', '>', 0)
                ->orderBy('mastery')
                ->first();
        $skill = $weakSkill?->skill;
        $startsAt = now()->toDateString();
        $endsAt = now()->addDays(13)->toDateString();

        StudyPlan::updateOrCreate(
            [
                'user_id' => $student->id,
                'source' => 'school_intervention',
                'status' => 'active',
                'skill_id' => $skill?->id,
            ],
            [
                'created_by' => auth()->id(),
                'school_id' => $this->schoolId ?: $student->school_id,
                'group_id' => $this->groupId,
                'path_id' => $skill?->section?->subject?->path_id,
                'subject_id' => $skill?->section?->subject_id,
                'name' => 'خطة علاج ' . ($skill?->name_ar ?: $skill?->name ?: 'المهارة الأضعف') . ' - ' . $student->name,
                'starts_at' => $startsAt,
                'ends_at' => $endsAt,
                'daily_minutes' => $weakSkill && (float) $weakSkill->mastery < 40 ? 60 : 45,
                'preferred_start_time' => '17:00',
                'tasks' => $this->tasksFor($skill?->name_ar ?: $skill?->name ?: 'المهارة الأضعف'),
                'notes' => 'تم إنشاء الخطة من تقرير تشخيص المدرسة بناء على نتائج الطالب والمهارات الضعيفة.',
            ],
        );

        Notification::make()
            ->title('تم إنشاء خطة علاج للطالب')
            ->body('ستظهر الخطة في تبويب خطتي داخل حساب الطالب.')
            ->success()
            ->send();

        $this->refreshReport();
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

    private function tasksFor(string $skillName): array
    {
        return [
            ['day' => 0, 'text' => "تشخيص سريع لأخطاء {$skillName}", 'done' => false],
            ['day' => 1, 'text' => "شرح مركز للقاعدة الأساسية في {$skillName}", 'done' => false],
            ['day' => 2, 'text' => "حل 10 أسئلة متدرجة على {$skillName}", 'done' => false],
            ['day' => 4, 'text' => 'مراجعة الأخطاء المتكررة مع المشرف', 'done' => false],
            ['day' => 6, 'text' => 'اختبار متابعة قصير وقياس التحسن', 'done' => false],
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

    private function schoolQuery()
    {
        $query = School::query()->where('is_active', true);
        $schoolIds = $this->managedSchoolIds();

        if ($schoolIds !== null) {
            $query->whereIn('id', $schoolIds);
        }

        return $query;
    }

    private function managedSchoolIds(): ?Collection
    {
        $user = auth()->user();

        if (! $user) {
            return null;
        }

        if (($user->role === 'admin') || $user->hasRole('admin')) {
            return null;
        }

        if (($user->role !== 'supervisor') && ! $user->hasRole('supervisor')) {
            return collect();
        }

        $schoolIds = collect([$user->school_id])->filter();

        $groupSchoolIds = Group::query()
            ->whereNotNull('school_id')
            ->whereHas('users', function ($query) use ($user): void {
                $query
                    ->where('users.id', $user->id)
                    ->whereIn('group_user.role', ['supervisor', 'class_supervisor', 'school_manager']);
            })
            ->pluck('school_id');

        return $schoolIds
            ->merge($groupSchoolIds)
            ->filter()
            ->unique()
            ->values();
    }

    private function normalizeScope(): void
    {
        $schoolIds = $this->managedSchoolIds();

        if ($schoolIds !== null && $this->schoolId && ! $schoolIds->contains((int) $this->schoolId)) {
            $this->schoolId = $schoolIds->first();
            $this->groupId = null;
        }

        if (! $this->schoolId) {
            $this->schoolId = $this->schools->first()?->id;
        }

        if ($this->groupId) {
            $groupAllowed = Group::query()
                ->whereKey($this->groupId)
                ->where('school_id', $this->schoolId)
                ->exists();

            if (! $groupAllowed) {
                $this->groupId = null;
            }
        }
    }
}
