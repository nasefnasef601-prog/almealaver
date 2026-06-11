<?php

namespace App\Http\Controllers;

use App\Models\AccessGrant;
use App\Models\Course;
use App\Models\DiscussionReply;
use App\Models\DiscussionThread;
use App\Models\Lesson;
use App\Models\Quiz;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DiscussionController extends Controller
{
    public function index(Course $course)
    {
        $this->authorizeCourseDiscussion($course);

        $threads = $this->threadsFor('course', $course->id);
        $entityType = 'course';
        $entityTitle = $course->title_ar ?? $course->title;

        return view('student.course-discussions', compact('course', 'threads', 'entityType', 'entityTitle'));
    }

    public function store(Request $request, Course $course)
    {
        $this->authorizeCourseDiscussion($course);

        return $this->storeThread($request, $course, 'course', $course->id);
    }

    public function lessonIndex(Course $course, Lesson $lesson)
    {
        $this->authorizeLessonDiscussion($course, $lesson);

        $threads = $this->threadsFor('lesson', $lesson->id);
        $entityType = 'lesson';
        $entityTitle = $lesson->title_ar ?? $lesson->title;

        return view('student.course-discussions', compact('course', 'threads', 'entityType', 'entityTitle', 'lesson'));
    }

    public function lessonStore(Request $request, Course $course, Lesson $lesson)
    {
        $this->authorizeLessonDiscussion($course, $lesson);

        return $this->storeThread($request, $course, 'lesson', $lesson->id);
    }

    public function quizIndex(Quiz $quiz)
    {
        $course = $quiz->course;
        $this->authorizeQuizDiscussion($quiz);

        $threads = $this->threadsFor('quiz', $quiz->id);
        $entityType = 'quiz';
        $entityTitle = $quiz->title_ar ?? $quiz->title;

        return view('student.course-discussions', compact('course', 'threads', 'entityType', 'entityTitle', 'quiz'));
    }

    public function quizStore(Request $request, Quiz $quiz)
    {
        $this->authorizeQuizDiscussion($quiz);

        return $this->storeThread($request, $quiz->course, 'quiz', $quiz->id);
    }

    public function reply(Request $request, Course $course, DiscussionThread $thread)
    {
        $this->authorizeThreadDiscussion($thread);
        abort_unless((int) $thread->course_id === (int) $course->id, 404);

        return $this->storeReply($request, $thread);
    }

    public function threadReply(Request $request, DiscussionThread $thread)
    {
        $this->authorizeThreadDiscussion($thread);

        return $this->storeReply($request, $thread);
    }

    public function resolve(Request $request, Course $course, DiscussionThread $thread)
    {
        $this->authorizeThreadDiscussion($thread);
        abort_unless((int) $thread->course_id === (int) $course->id, 404);

        return $this->resolveThread($request, $thread);
    }

    public function threadResolve(Request $request, DiscussionThread $thread)
    {
        $this->authorizeThreadDiscussion($thread);

        return $this->resolveThread($request, $thread);
    }

    public function toggleThreadUpvote(DiscussionThread $thread)
    {
        $this->authorizeThreadDiscussion($thread);
        $upvoted = $thread->toggleUpvote(auth()->id());

        return back()->with('success', $upvoted ? 'تم التصويت للنقاش.' : 'تم إلغاء التصويت.');
    }

    public function toggleReplyUpvote(DiscussionReply $reply)
    {
        $thread = $reply->thread()->firstOrFail();
        $this->authorizeThreadDiscussion($thread);
        $upvoted = $reply->toggleUpvote(auth()->id());

        return back()->with('success', $upvoted ? 'تم التصويت للرد.' : 'تم إلغاء التصويت.');
    }

    public function acceptReply(Request $request, DiscussionReply $reply)
    {
        $thread = $reply->thread()->with('course')->firstOrFail();
        $this->authorizeThreadDiscussion($thread);

        $user = $request->user();
        $canAccept = (int) $thread->author_id === (int) $user->id
            || $user->role === 'admin'
            || $user->hasRole('admin')
            || (int) $thread->course?->assigned_teacher_id === (int) $user->id
            || (int) $thread->course?->created_by === (int) $user->id;

        abort_unless($canAccept, 403);

        DB::transaction(function () use ($thread, $reply) {
            $thread->replies()->update(['is_accepted_answer' => false]);
            $reply->update(['is_accepted_answer' => true]);
            $thread->update(['is_resolved' => true]);
        });

        return back()->with('success', 'تم قبول الرد كإجابة.');
    }

    private function resolveThread(Request $request, DiscussionThread $thread)
    {
        $user = $request->user();
        $canResolve = $user->role === 'admin'
            || $user->hasRole('admin')
            || (int) $thread->author_id === (int) $user->id
            || (int) $thread->course?->assigned_teacher_id === (int) $user->id
            || (int) $thread->course?->created_by === (int) $user->id;

        abort_unless($canResolve, 403);

        $thread->update(['is_resolved' => true]);

        return back()->with('success', 'تم إغلاق النقاش كمنتهي.');
    }

    private function storeThread(Request $request, ?Course $course, string $entityType, int $entityId)
    {
        $validated = $request->validate([
            'title' => 'required|string|min:2|max:180',
            'body' => 'required|string|min:2|max:4000',
        ]);

        DiscussionThread::create([
            'entity_type' => $entityType,
            'entity_id' => $entityId,
            'course_id' => $course?->id,
            'author_id' => $request->user()->id,
            'title' => trim($validated['title']),
            'body' => trim($validated['body']),
        ]);

        return back()->with('success', 'تم نشر النقاش.');
    }

    private function storeReply(Request $request, DiscussionThread $thread)
    {
        $validated = $request->validate([
            'body' => 'required|string|min:2|max:4000',
        ]);

        DB::transaction(function () use ($request, $thread, $validated) {
            $thread->replies()->create([
                'author_id' => $request->user()->id,
                'body' => trim($validated['body']),
                'is_instructor_reply' => in_array($request->user()->role, ['admin', 'teacher', 'supervisor'], true),
            ]);

            $thread->increment('replies_count');
            $thread->touch();
        });

        return back()->with('success', 'تم إضافة الرد.');
    }

    private function threadsFor(string $entityType, int $entityId)
    {
        return DiscussionThread::query()
            ->with(['author', 'replies.author'])
            ->where('entity_type', $entityType)
            ->where('entity_id', $entityId)
            ->orderByDesc('is_pinned')
            ->orderByDesc('created_at')
            ->paginate(20);
    }

    private function authorizeCourseDiscussion(Course $course): void
    {
        $user = auth()->user();
        abort_unless($user, 403);

        $hasAccess = (bool) $course->is_free
            || (float) $course->price <= 0
            || AccessGrant::userHasCourseAccess($user->id, $course->id);

        abort_unless($course->is_published && $hasAccess, 403);
    }

    private function authorizeLessonDiscussion(Course $course, Lesson $lesson): void
    {
        abort_unless((int) $lesson->course_id === (int) $course->id && $lesson->is_published, 404);

        $user = auth()->user();
        abort_unless($user, 403);

        $hasAccess = (bool) $course->is_free
            || (bool) $lesson->is_free
            || (float) $course->price <= 0
            || AccessGrant::userHasCourseAccess($user->id, $course->id);

        abort_unless($course->is_published && $hasAccess, 403);
    }

    private function authorizeQuizDiscussion(Quiz $quiz): void
    {
        abort_unless($quiz->is_published, 404);

        if (! $quiz->course_id) {
            return;
        }

        $course = $quiz->course;
        abort_unless($course, 404);
        $this->authorizeCourseDiscussion($course);
    }

    private function authorizeThreadDiscussion(DiscussionThread $thread): void
    {
        $thread->loadMissing('course');

        if ($thread->entity_type === 'course' && $thread->course) {
            $this->authorizeCourseDiscussion($thread->course);
            return;
        }

        if ($thread->entity_type === 'lesson') {
            $lesson = Lesson::findOrFail($thread->entity_id);
            $course = $thread->course ?: $lesson->course;
            abort_unless($course, 404);
            $this->authorizeLessonDiscussion($course, $lesson);
            return;
        }

        if ($thread->entity_type === 'quiz') {
            $quiz = Quiz::findOrFail($thread->entity_id);
            $this->authorizeQuizDiscussion($quiz);
            return;
        }

        abort(404);
    }
}
