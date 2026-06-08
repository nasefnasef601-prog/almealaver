<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\QuizResult;
use App\Models\User;
use App\Services\CourseCompletionService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseCompletionTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RoleAndPermissionSeeder']);
    }

    public function test_course_completion_when_all_lessons_done()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        $lessons = Lesson::factory(3)->create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        foreach ($lessons as $lesson) {
            LessonCompletion::create([
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
            ]);
        }

        $service = app(CourseCompletionService::class);
        $completion = $service->checkAndComplete($student->id, $course->id);

        $this->assertNotNull($completion);
        $this->assertEquals($student->id, $completion->user_id);
        $this->assertEquals($course->id, $completion->course_id);
        $this->assertNotNull($completion->certificate_code);
    }

    public function test_course_not_completed_without_all_lessons()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        Lesson::factory(3)->create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        LessonCompletion::create([
            'user_id' => $student->id,
            'lesson_id' => 1,
            'course_id' => $course->id,
        ]);

        $service = app(CourseCompletionService::class);
        $completion = $service->checkAndComplete($student->id, $course->id);

        $this->assertNull($completion);
    }

    public function test_course_requires_passing_all_quizzes()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        $lessons = Lesson::factory(2)->create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'is_published' => true,
        ]);
        $quiz = Quiz::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'passing_score' => 50,
        ]);

        foreach ($lessons as $lesson) {
            LessonCompletion::create([
                'user_id' => $student->id,
                'lesson_id' => $lesson->id,
                'course_id' => $course->id,
            ]);
        }

        $quizResult = QuizResult::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'passed' => false,
            'score_percentage' => 30,
            'total_questions' => 1,
            'correct_count' => 0,
            'incorrect_count' => 1,
            'unanswered_count' => 0,
            'skill_breakdown' => [],
        ]);

        $service = app(CourseCompletionService::class);
        $completion = $service->checkAndComplete($student->id, $course->id);

        $this->assertNull($completion, 'Should not complete if quiz not passed');
    }

    public function test_certificate_code_is_unique()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course1 = Course::factory()->create(['is_published' => true]);
        $course2 = Course::factory()->create(['is_published' => true]);

        $mod1 = CourseModule::factory()->create(['course_id' => $course1->id]);
        $mod2 = CourseModule::factory()->create(['course_id' => $course2->id]);

        $l1 = Lesson::factory()->create(['module_id' => $mod1->id, 'course_id' => $course1->id, 'is_published' => true]);
        $l2 = Lesson::factory()->create(['module_id' => $mod2->id, 'course_id' => $course2->id, 'is_published' => true]);

        LessonCompletion::create(['user_id' => $student->id, 'lesson_id' => $l1->id, 'course_id' => $course1->id]);
        LessonCompletion::create(['user_id' => $student->id, 'lesson_id' => $l2->id, 'course_id' => $course2->id]);

        $service = app(CourseCompletionService::class);
        $c1 = $service->checkAndComplete($student->id, $course1->id);
        $c2 = $service->checkAndComplete($student->id, $course2->id);

        $this->assertNotNull($c1);
        $this->assertNotNull($c2);
        $this->assertNotEquals($c1->certificate_code, $c2->certificate_code);
    }

    public function test_course_completion_is_idempotent()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id, 'course_id' => $course->id, 'is_published' => true]);

        LessonCompletion::create(['user_id' => $student->id, 'lesson_id' => $lesson->id, 'course_id' => $course->id]);

        $service = app(CourseCompletionService::class);
        $first = $service->checkAndComplete($student->id, $course->id);
        $second = $service->checkAndComplete($student->id, $course->id);

        $this->assertNotNull($first);
        $this->assertNotNull($second);
        $this->assertEquals($first->id, $second->id);
    }
}
