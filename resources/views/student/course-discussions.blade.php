@extends('layouts.student', ['activeTab' => 'my-courses'])

@section('title', 'نقاشات الدورة')

@section('content')
@php
    $entityType = $entityType ?? 'course';
    $entityTitle = $entityTitle ?? ($course->title_ar ?? $course->title ?? 'المحتوى');
    $storeRoute = match ($entityType) {
        'lesson' => route('student.lesson-discussions.store', [$course->id, $lesson->id]),
        'quiz' => route('student.quiz-discussions.store', $quiz->id),
        default => route('student.course-discussions.store', $course->id),
    };
    $backRoute = match ($entityType) {
        'lesson' => route('student.lesson.show', [$course->id, $lesson->id]),
        'quiz' => route('student.quiz.show', $quiz->id),
        default => route('student.course-detail', $course->id),
    };
    $entityLabel = match ($entityType) {
        'lesson' => 'الدرس',
        'quiz' => 'الاختبار',
        default => 'الدورة',
    };
    $currentUserId = auth()->id();
@endphp

<div class="max-w-5xl space-y-6">
    <div class="flex flex-wrap items-center justify-between gap-3">
        <div>
            <a href="{{ $backRoute }}" class="text-sm font-bold text-gray-500 hover:text-blue-600">العودة إلى {{ $entityLabel }}</a>
            <h1 class="mt-2 text-2xl font-black text-gray-900">نقاشات {{ $entityTitle }}</h1>
            <p class="mt-1 text-sm text-gray-500">اسأل، ناقش، وتابع ردود زملائك داخل نفس {{ $entityLabel }}.</p>
        </div>
        <span class="rounded-full bg-blue-50 px-4 py-2 text-sm font-bold text-blue-700">{{ $threads->total() }} نقاش</span>
    </div>

    <div class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
        <form method="POST" action="{{ $storeRoute }}" class="space-y-3">
            @csrf
            <input name="title" value="{{ old('title') }}" required maxlength="180" placeholder="عنوان النقاش"
                   class="w-full rounded-2xl border border-gray-200 px-4 py-3 text-sm font-bold outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
            <textarea name="body" rows="4" required maxlength="4000" placeholder="اكتب سؤالك أو ملاحظتك..."
                      class="w-full resize-none rounded-2xl border border-gray-200 px-4 py-3 text-sm outline-none transition focus:border-blue-400 focus:ring-2 focus:ring-blue-100">{{ old('body') }}</textarea>
            <button type="submit" class="rounded-xl bg-blue-600 px-6 py-2.5 text-sm font-bold text-white transition hover:bg-blue-700">
                نشر النقاش
            </button>
        </form>
    </div>

    <div class="space-y-4">
        @forelse($threads as $thread)
            <article class="rounded-3xl border border-gray-100 bg-white p-5 shadow-sm">
                <div class="flex flex-wrap items-start justify-between gap-3">
                    <div class="min-w-0">
                        <div class="flex flex-wrap items-center gap-2">
                            @if($thread->is_pinned)
                                <span class="rounded-full bg-amber-50 px-2.5 py-1 text-xs font-bold text-amber-700">مثبت</span>
                            @endif
                            @if($thread->is_resolved)
                                <span class="rounded-full bg-emerald-50 px-2.5 py-1 text-xs font-bold text-emerald-700">تم الحل</span>
                            @endif
                            <h2 class="text-lg font-black text-gray-900">{{ $thread->title }}</h2>
                        </div>
                        <p class="mt-1 text-xs text-gray-400">{{ $thread->author?->name ?? 'طالب' }} · {{ $thread->created_at->diffForHumans() }}</p>
                    </div>
                    @if(!$thread->is_resolved && (auth()->id() === $thread->author_id))
                        <form method="POST" action="{{ route('student.discussions.resolve', $thread->id) }}">
                            @csrf
                            <button class="rounded-xl bg-emerald-50 px-3 py-2 text-xs font-bold text-emerald-700 hover:bg-emerald-100">
                                إغلاق كمنتهي
                            </button>
                        </form>
                    @endif
                </div>

                <p class="mt-4 whitespace-pre-line text-sm leading-7 text-gray-700">{{ $thread->body }}</p>
                <form method="POST" action="{{ route('student.discussions.upvote', $thread->id) }}" class="mt-4">
                    @csrf
                    <button class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $thread->isUpvotedBy($currentUserId) ? 'bg-blue-600 text-white' : 'bg-blue-50 text-blue-700 hover:bg-blue-100' }}">
                        <span>▲</span>
                        <span>{{ $thread->upvotes_count }}</span>
                    </button>
                </form>

                <div class="mt-5 space-y-3 border-t border-gray-100 pt-4">
                    @foreach($thread->replies as $reply)
                        @php
                            $canAcceptReply = ! $reply->is_accepted_answer
                                && (
                                    $currentUserId === $thread->author_id
                                    || auth()->user()->role === 'admin'
                                    || auth()->user()->hasRole('admin')
                                    || $thread->course?->assigned_teacher_id === $currentUserId
                                    || $thread->course?->created_by === $currentUserId
                                );
                        @endphp
                        <div class="rounded-2xl {{ $reply->is_accepted_answer ? 'bg-emerald-50 ring-1 ring-emerald-200' : 'bg-gray-50' }} p-4">
                            <div class="mb-1 flex flex-wrap items-center gap-2 text-xs">
                                <span class="font-bold text-gray-800">{{ $reply->author?->name ?? 'مستخدم' }}</span>
                                @if($reply->is_instructor_reply)
                                    <span class="rounded-full bg-blue-100 px-2 py-0.5 font-bold text-blue-700">رد تعليمي</span>
                                @endif
                                @if($reply->is_accepted_answer)
                                    <span class="rounded-full bg-emerald-100 px-2 py-0.5 font-bold text-emerald-700">إجابة مقبولة</span>
                                @endif
                                <span class="text-gray-400">{{ $reply->created_at->diffForHumans() }}</span>
                            </div>
                            <p class="whitespace-pre-line text-sm leading-7 text-gray-700">{{ $reply->body }}</p>
                            <div class="mt-3 flex flex-wrap items-center gap-2">
                                <form method="POST" action="{{ route('student.discussion-replies.upvote', $reply->id) }}">
                                    @csrf
                                    <button class="inline-flex items-center gap-2 rounded-xl px-3 py-1.5 text-xs font-bold transition {{ $reply->isUpvotedBy($currentUserId) ? 'bg-blue-600 text-white' : 'bg-white text-blue-700 hover:bg-blue-50' }}">
                                        <span>▲</span>
                                        <span>{{ $reply->upvotes_count }}</span>
                                    </button>
                                </form>
                                @if($canAcceptReply)
                                    <form method="POST" action="{{ route('student.discussion-replies.accept', $reply->id) }}">
                                        @csrf
                                        <button class="rounded-xl bg-emerald-600 px-3 py-1.5 text-xs font-bold text-white hover:bg-emerald-700">
                                            قبول الإجابة
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </div>
                    @endforeach

                    <form method="POST" action="{{ route('student.discussions.reply', $thread->id) }}" class="flex flex-col gap-2 sm:flex-row">
                        @csrf
                        <input name="body" required maxlength="4000" placeholder="اكتب ردك..."
                               class="min-w-0 flex-1 rounded-xl border border-gray-200 px-3 py-2 text-sm outline-none focus:border-blue-400 focus:ring-2 focus:ring-blue-100">
                        <button class="rounded-xl bg-gray-900 px-5 py-2 text-sm font-bold text-white hover:bg-gray-800">رد</button>
                    </form>
                </div>
            </article>
        @empty
            <div class="rounded-3xl border border-dashed border-gray-200 bg-white p-10 text-center text-gray-400">
                لا توجد نقاشات بعد.
            </div>
        @endforelse
    </div>

    {{ $threads->links() }}
</div>
@endsection
