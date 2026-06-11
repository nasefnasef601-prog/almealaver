<?php

namespace Tests\Feature;

use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DiscussionThread;
use App\Models\Lesson;
use App\Models\Quiz;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CourseDiscussionTest extends TestCase
{
    use RefreshDatabase;

    public function test_student_can_create_reply_and_resolve_discussion_for_free_course(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, [
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.course-discussions.index', $course))
            ->assertOk();

        $this->actingAs($student)
            ->post(route('student.course-discussions.store', $course), [
                'title' => 'سؤال عن الدرس',
                'body' => 'أحتاج توضيح فكرة في هذه الدورة.',
            ])
            ->assertRedirect();

        $thread = DiscussionThread::firstOrFail();

        $this->actingAs($student)
            ->post(route('student.course-discussions.reply', [$course, $thread]), [
                'body' => 'هذه متابعة على نفس السؤال.',
            ])
            ->assertRedirect();

        $this->actingAs($student)
            ->post(route('student.course-discussions.resolve', [$course, $thread]))
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_threads', [
            'id' => $thread->id,
            'course_id' => $course->id,
            'author_id' => $student->id,
            'replies_count' => 1,
            'is_resolved' => true,
        ]);
        $this->assertDatabaseHas('discussion_replies', [
            'discussion_thread_id' => $thread->id,
            'author_id' => $student->id,
        ]);
    }

    public function test_paid_course_discussions_require_access(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, [
            'price' => 250,
            'is_free' => false,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->get(route('student.course-discussions.index', $course))
            ->assertForbidden();
    }

    public function test_json_scoped_grant_allows_paid_course_discussions(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, [
            'price' => 250,
            'is_free' => false,
            'is_published' => true,
        ]);

        AccessGrant::create([
            'user_id' => $student->id,
            'course_ids' => [(string) $course->id],
            'content_types' => ['courses'],
            'source_type' => 'access_code',
            'source_id' => 'test-discussion',
            'idempotency_key' => 'test-discussion:' . $student->id . ':' . $course->id,
            'grant_type' => 'code',
            'status' => 'active',
            'granted_by' => $teacher->id,
            'granted_at' => now(),
            'starts_at' => now(),
            'expires_at' => now()->addDay(),
        ]);

        $this->actingAs($student)
            ->get(route('student.course-discussions.index', $course))
            ->assertOk();
    }

    public function test_student_can_create_lesson_discussion(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, [
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
        ]);
        $module = CourseModule::create([
            'course_id' => $course->id,
            'title' => 'Module',
            'title_ar' => 'وحدة',
        ]);
        $lesson = Lesson::create([
            'module_id' => $module->id,
            'course_id' => $course->id,
            'title' => 'Lesson',
            'title_ar' => 'درس',
            'content_type' => 'text',
            'content_text' => 'Lesson body',
            'is_free' => false,
            'is_published' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.lesson-discussions.store', [$course, $lesson]), [
                'title' => 'نقاش الدرس',
                'body' => 'سؤال مرتبط بالدرس.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_threads', [
            'entity_type' => 'lesson',
            'entity_id' => $lesson->id,
            'course_id' => $course->id,
            'author_id' => $student->id,
        ]);
    }

    public function test_student_can_create_quiz_discussion(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, [
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
        ]);
        $quiz = Quiz::create([
            'title' => 'Quiz',
            'title_ar' => 'اختبار',
            'course_id' => $course->id,
            'created_by' => $teacher->id,
            'quiz_type' => 'standard',
            'difficulty' => 'all',
            'passing_score' => 50,
            'randomize_questions' => false,
            'show_answers' => true,
            'show_explanations' => true,
            'is_published' => true,
            'status' => 'active',
        ]);

        $this->actingAs($student)
            ->post(route('student.quiz-discussions.store', $quiz), [
                'title' => 'نقاش الاختبار',
                'body' => 'سؤال مرتبط بالاختبار.',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_threads', [
            'entity_type' => 'quiz',
            'entity_id' => $quiz->id,
            'course_id' => $course->id,
            'author_id' => $student->id,
        ]);
    }

    public function test_student_can_toggle_thread_and_reply_upvotes(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, ['is_free' => true, 'is_published' => true]);
        $thread = DiscussionThread::create([
            'entity_type' => 'course',
            'entity_id' => $course->id,
            'course_id' => $course->id,
            'author_id' => $student->id,
            'title' => 'Vote thread',
            'body' => 'Vote body',
        ]);
        $reply = $thread->replies()->create([
            'author_id' => $teacher->id,
            'body' => 'Reply body',
            'is_instructor_reply' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.discussions.upvote', $thread))
            ->assertRedirect();
        $this->actingAs($student)
            ->post(route('student.discussion-replies.upvote', $reply))
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_threads', [
            'id' => $thread->id,
            'upvotes_count' => 1,
        ]);
        $this->assertDatabaseHas('discussion_replies', [
            'id' => $reply->id,
            'upvotes_count' => 1,
        ]);

        $this->actingAs($student)
            ->post(route('student.discussions.upvote', $thread))
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_threads', [
            'id' => $thread->id,
            'upvotes_count' => 0,
        ]);
    }

    public function test_thread_author_can_accept_reply_as_answer(): void
    {
        $student = User::factory()->create(['role' => 'student']);
        $teacher = User::factory()->create(['role' => 'teacher']);
        $course = $this->createCourse($teacher, ['is_free' => true, 'is_published' => true]);
        $thread = DiscussionThread::create([
            'entity_type' => 'course',
            'entity_id' => $course->id,
            'course_id' => $course->id,
            'author_id' => $student->id,
            'title' => 'Accept thread',
            'body' => 'Accept body',
        ]);
        $reply = $thread->replies()->create([
            'author_id' => $teacher->id,
            'body' => 'Accepted reply',
            'is_instructor_reply' => true,
        ]);

        $this->actingAs($student)
            ->post(route('student.discussion-replies.accept', $reply))
            ->assertRedirect();

        $this->assertDatabaseHas('discussion_replies', [
            'id' => $reply->id,
            'is_accepted_answer' => true,
        ]);
        $this->assertDatabaseHas('discussion_threads', [
            'id' => $thread->id,
            'is_resolved' => true,
        ]);
    }

    private function createCourse(User $teacher, array $overrides = []): Course
    {
        return Course::create(array_merge([
            'title' => 'Discussion Course',
            'title_ar' => 'دورة النقاش',
            'slug' => 'discussion-course-' . uniqid(),
            'created_by' => $teacher->id,
            'assigned_teacher_id' => $teacher->id,
            'price' => 0,
            'is_free' => true,
            'is_published' => true,
            'status' => 'active',
        ], $overrides));
    }
}
