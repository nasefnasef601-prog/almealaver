<?php

namespace Tests\Feature;

use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Lesson;
use App\Models\LessonCompletion;
use App\Models\Quiz;
use App\Models\QuizAttempt;
use App\Models\Question;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class QuizFlowTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->artisan('db:seed', ['--class' => 'Database\Seeders\RoleAndPermissionSeeder']);
    }

    public function test_student_can_start_quiz()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $quiz = Quiz::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $this->actingAs($student);
        $response = $this->get(route('student.quiz.start', $quiz->id));
        $response->assertRedirect();

        $this->assertDatabaseHas('quiz_attempts', [
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
        ]);
    }

    public function test_student_can_submit_quiz()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create(['module_id' => $module->id, 'course_id' => $course->id]);
        $quiz = Quiz::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'passing_score' => 50,
        ]);

        $question = Question::create([
            'quiz_id' => $quiz->id,
            'question_text' => 'Test question',
            'question_type' => 'multiple_choice',
            'options' => [
                ['text_ar' => 'A', 'text' => 'A', 'is_correct' => true],
                ['text_ar' => 'B', 'text' => 'B', 'is_correct' => false],
            ],
            'correct_answer' => '0',
            'points' => 1,
        ]);

        $attempt = QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'status' => 'in_progress',
            'started_at' => now(),
            'attempt_number' => 1,
        ]);

        $this->actingAs($student);
        $response = $this->post(route('student.quiz.submit', $attempt->id), [
            'answers' => [$question->id => '0'],
            'time_taken_seconds' => 60,
        ]);

        $response->assertRedirect();

        $this->assertDatabaseHas('quiz_results', [
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'passed' => true,
        ]);

        $this->assertDatabaseHas('quiz_attempts', [
            'id' => $attempt->id,
            'status' => 'completed',
        ]);
    }

    public function test_lesson_completion_triggers_course_check()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $module = CourseModule::factory()->create(['course_id' => $course->id]);
        $lesson = Lesson::factory()->create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'is_published' => true,
        ]);

        $this->actingAs($student);
        $response = $this->post(route('student.lesson.complete', [$course->id, $lesson->id]));

        $response->assertRedirect();
        $this->assertDatabaseHas('lesson_completions', [
            'user_id' => $student->id,
            'lesson_id' => $lesson->id,
            'course_id' => $course->id,
        ]);
    }

    public function test_quiz_attempt_limit_enforced()
    {
        $student = User::factory()->create(['role' => 'student']);
        $course = Course::factory()->create(['is_published' => true]);
        $quiz = Quiz::factory()->create([
            'course_id' => $course->id,
            'is_published' => true,
            'max_attempts' => 2,
        ]);

        QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'started_at' => now()->subDay(),
            'completed_at' => now()->subDay(),
            'attempt_number' => 1,
        ]);

        QuizAttempt::create([
            'user_id' => $student->id,
            'quiz_id' => $quiz->id,
            'status' => 'completed',
            'started_at' => now(),
            'completed_at' => now(),
            'attempt_number' => 2,
        ]);

        $this->actingAs($student);
        $response = $this->get(route('student.quiz.start', $quiz->id));
        $response->assertRedirect();
        $response->assertSessionHas('error');
    }
}
