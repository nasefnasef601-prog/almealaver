<?php

declare(strict_types=1);

namespace Tests\Feature;

use App\Models\Group;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\School;
use App\Models\Skill;
use App\Models\SkillProgress;
use App\Models\StudyPlan;
use App\Models\User;
use App\Filament\Pages\SchoolDiagnostics;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class SchoolManagementRelationshipsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RoleAndPermissionSeeder']);
    }

    public function test_school_group_roles_and_counts_are_scoped_correctly(): void
    {
        $school = School::create([
            'name' => 'Almeaa Demo School',
            'name_ar' => 'مدرسة المئة التجريبية',
            'code' => 'ALM-DEMO',
            'is_active' => true,
        ]);

        $class = Group::create([
            'school_id' => $school->id,
            'name' => 'Grade 6 - A',
            'description' => 'Core class for school diagnostics.',
            'type' => 'class',
            'is_active' => true,
            'course_ids' => [10, 20],
            'settings' => ['weak_threshold' => 60],
        ]);

        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $teacher = User::factory()->create(['role' => 'teacher', 'school_id' => $school->id]);
        $classSupervisor = User::factory()->create(['role' => 'supervisor', 'school_id' => $school->id]);
        $schoolManager = User::factory()->create(['role' => 'supervisor', 'school_id' => $school->id]);

        $now = now();
        DB::table('group_user')->insert([
            ['group_id' => $class->id, 'user_id' => $student->id, 'role' => 'student', 'created_at' => $now, 'updated_at' => $now],
            ['group_id' => $class->id, 'user_id' => $teacher->id, 'role' => 'teacher', 'created_at' => $now, 'updated_at' => $now],
            ['group_id' => $class->id, 'user_id' => $classSupervisor->id, 'role' => 'class_supervisor', 'created_at' => $now, 'updated_at' => $now],
            ['group_id' => $class->id, 'user_id' => $schoolManager->id, 'role' => 'school_manager', 'created_at' => $now, 'updated_at' => $now],
        ]);

        $class->refresh();
        $school->refresh();

        $this->assertSame(['10', '20'], $class->linkedCourseIds());
        $this->assertTrue($class->is_active);
        $this->assertCount(1, $class->students);
        $this->assertCount(1, $class->teachers);
        $this->assertCount(1, $class->classSupervisors);
        $this->assertCount(1, $class->schoolManagers);
        $this->assertCount(2, $class->supervisors);

        $this->assertSame(1, $school->classes()->count());
        $this->assertSame(1, $school->students()->count());
        $this->assertSame(1, $school->teachers()->count());
        $this->assertSame(2, $school->supervisors()->count());
    }

    public function test_school_diagnostics_identifies_weak_students_and_skills(): void
    {
        $school = School::create([
            'name' => 'Almeaa Demo School',
            'name_ar' => 'مدرسة المئة التجريبية',
            'code' => 'ALM-DIAG',
            'is_active' => true,
        ]);

        $teacher = User::factory()->create(['role' => 'teacher']);
        $student = User::factory()->create(['role' => 'student', 'school_id' => $school->id]);
        $groupOnlyStudent = User::factory()->create(['role' => 'student', 'school_id' => null]);
        $class = Group::create([
            'school_id' => $school->id,
            'name' => 'Grade 8 - Remedial',
            'type' => 'class',
            'is_active' => true,
        ]);
        DB::table('group_user')->insert([
            'group_id' => $class->id,
            'user_id' => $groupOnlyStudent->id,
            'role' => 'student',
            'created_at' => now(),
            'updated_at' => now(),
        ]);
        $skill = Skill::create([
            'name' => 'Fractions',
            'name_ar' => 'الكسور',
            'slug' => 'fractions',
            'is_active' => true,
        ]);
        $quiz = Quiz::create([
            'title' => 'Fractions Quiz',
            'title_ar' => 'اختبار الكسور',
            'created_by' => $teacher->id,
            'is_published' => true,
            'status' => 'published',
        ]);
        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'completed_at' => now(),
            'score' => 35,
            'passed' => false,
        ]);

        QuizResult::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'attempt_id' => $attempt->id,
            'score_percentage' => 35,
            'passed' => false,
            'total_questions' => 10,
            'correct_count' => 3,
            'incorrect_count' => 7,
            'completed_at' => now(),
        ]);
        SkillProgress::create([
            'user_id' => $student->id,
            'skill_id' => $skill->id,
            'mastery' => 35,
            'status' => 'weak',
            'total_attempts' => 1,
            'correct_answers' => 3,
            'total_questions' => 10,
            'last_quiz_id' => $quiz->id,
            'last_quiz_title' => $quiz->title_ar,
            'last_attempt_at' => now(),
        ]);

        $page = new SchoolDiagnostics();
        $page->mount();
        $page->schoolId = $school->id;
        $page->weakThreshold = 60;
        $page->refreshReport();

        $this->assertSame(2, $page->studentsCount);
        $this->assertSame(1, $page->weakStudentsCount);
        $this->assertSame(1, $page->weakSkillsCount);
        $this->assertSame(1, $page->untestedStudentsCount);
        $this->assertCount(1, $page->skillHotspots);
        $this->assertCount(1, $page->treatmentPlans);
        $this->assertTrue($page->weakStudents->pluck('id')->contains($student->id));
        $this->assertTrue($page->weakSkills->pluck('skill_id')->contains($skill->id));

        $export = $page->exportWeakStudentsCsv();
        $this->assertStringContainsString('school-weak-students-', $export->headers->get('content-disposition'));

        $admin = User::factory()->create(['role' => 'admin']);
        $this->actingAs($admin);
        $page->createTreatmentPlanFor($student->id);

        $createdPlan = StudyPlan::query()->where('user_id', $student->id)->first();
        $this->assertNotNull($createdPlan);
        $this->assertSame('school_intervention', $createdPlan->source);
        $this->assertSame($skill->id, $createdPlan->skill_id);
        $this->assertNotEmpty($createdPlan->tasks);
    }
}
