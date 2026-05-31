@extends('layouts.student', ['activeTab' => 'skills'])

@section('title', $skill->name_ar ?? $skill->name)

@php
    use App\Models\SkillProgress;
    use App\Models\Course;
    use App\Models\Quiz;

    $user = Auth::user();
    $progress = SkillProgress::where('user_id', $user->id)
        ->where('skill_id', $skill->id)
        ->first();

    $courses = Course::where('skill_id', $skill->id)
        ->where('is_published', true)
        ->with(['modules.lessons' => fn($q) => $q->where('is_published', true)])
        ->get();

    $quizzes = Quiz::whereHas('course', fn($q) => $q->where('skill_id', $skill->id))
        ->where('is_published', true)
        ->withCount('questions')
        ->get();

    $allLessons = $courses->flatMap->modules->flatMap->lessons;

    $enrolledCourseIds = \App\Models\AccessGrant::where('user_id', $user->id)
        ->where('status', 'active')
        ->pluck('course_id')
        ->toArray();

    $libraryFiles = $allLessons->filter(fn($l) => in_array($l->content_type, ['pdf', 'file', 'document', 'image']) && $l->content_url);
@endphp

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ tab: 'lessons' }">
    {{-- Header --}}
    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-8 mb-6">
        <div class="flex items-start justify-between">
            <div>
                <p class="text-sm text-gray-500 mb-1">{{ $skill->section?->subject?->name_ar ?? '' }} / {{ $skill->section?->name_ar ?? '' }}</p>
                <h1 class="text-2xl font-black text-gray-900">{{ $skill->name_ar ?? $skill->name }}</h1>
                @if($skill->description_ar)
                    <p class="text-gray-600 mt-2">{{ $skill->description_ar }}</p>
                @endif
            </div>
            @if($progress)
                @php
                    $badgeColor = match($progress->status) {
                        'mastered' => 'bg-emerald-100 text-emerald-700',
                        'good' => 'bg-blue-100 text-blue-700',
                        'average' => 'bg-amber-100 text-amber-700',
                        default => 'bg-red-100 text-red-700',
                    };
                    $barColor = match($progress->status) {
                        'mastered' => 'bg-emerald-500',
                        'good' => 'bg-blue-500',
                        'average' => 'bg-amber-500',
                        default => 'bg-red-500',
                    };
                @endphp
                <div class="text-center">
                    <div class="w-24 h-24 rounded-full bg-gray-50 flex items-center justify-center mx-auto mb-2 border-4 {{ $progress->mastery >= 80 ? 'border-emerald-200' : ($progress->mastery >= 60 ? 'border-blue-200' : ($progress->mastery >= 40 ? 'border-amber-200' : 'border-red-200')) }}">
                        <span class="text-2xl font-black {{ $progress->mastery >= 80 ? 'text-emerald-600' : ($progress->mastery >= 60 ? 'text-blue-600' : ($progress->mastery >= 40 ? 'text-amber-600' : 'text-red-600')) }}">{{ number_format($progress->mastery, 0) }}%</span>
                    </div>
                    <span class="text-xs px-2.5 py-1 rounded-full font-bold {{ $badgeColor }}">{{ $progress->status === 'mastered' ? 'متقن' : ($progress->status === 'good' ? 'جيد' : ($progress->status === 'average' ? 'متوسط' : 'ضعيف')) }}</span>
                    <p class="text-xs text-gray-400 mt-1">{{ $progress->total_attempts }} محاولة</p>
                </div>
            @endif
        </div>
    </div>

    {{-- Tabs --}}
    <div class="flex gap-2 mb-6">
        <button @click="tab = 'lessons'" :class="tab === 'lessons' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">الشروحات ({{ $allLessons->count() }})</button>
        <button @click="tab = 'quizzes'" :class="tab === 'quizzes' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">التدريبات ({{ $quizzes->count() }})</button>
        @if($libraryFiles->isNotEmpty())
            <button @click="tab = 'library'" :class="tab === 'library' ? 'bg-blue-600 text-white shadow-md' : 'bg-white text-gray-600 border border-gray-200 hover:border-blue-300'" class="px-5 py-2.5 rounded-xl font-bold text-sm transition-all">المكتبة ({{ $libraryFiles->count() }})</button>
        @endif
    </div>

    {{-- Lessons Tab --}}
    <div x-show="tab === 'lessons'" x-transition class="space-y-4">
        @if($allLessons->isNotEmpty())
            @foreach($courses as $course)
                @php
                    $hasAccess = in_array($course->id, $enrolledCourseIds) || $course->is_free;
                @endphp
                @foreach($course->modules as $module)
                    @if($module->lessons->isNotEmpty())
                        <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                            <h3 class="font-bold text-gray-900 mb-3">{{ $module->title_ar ?? $module->title }}</h3>
                            <div class="space-y-2">
                                @foreach($module->lessons as $lesson)
                                    <a href="{{ $hasAccess ? route('student.lesson.show', ['course' => $course->id, 'lesson' => $lesson->id]) : '#' }}"
                                       class="flex items-center gap-3 p-3 rounded-xl {{ $hasAccess ? 'hover:bg-gray-50' : 'opacity-60' }} transition-colors {{ !$hasAccess ? 'cursor-not-allowed' : '' }}">
                                        <div class="w-10 h-10 rounded-xl bg-gray-100 flex items-center justify-center shrink-0">
                                            @if($lesson->content_type === 'youtube' || $lesson->content_type === 'video')
                                                <svg class="w-5 h-5 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                            @elseif($lesson->content_type === 'pdf')
                                                <svg class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                                            @else
                                                <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                            @endif
                                        </div>
                                        <div class="flex-1">
                                            <p class="font-bold text-sm text-gray-900">{{ $lesson->title_ar ?? $lesson->title }}</p>
                                            @if($lesson->duration_minutes)
                                                <p class="text-xs text-gray-500">{{ $lesson->duration_minutes }} دقيقة</p>
                                            @endif
                                        </div>
                                        @if(!$hasAccess)
                                            <span class="text-xs px-2 py-1 bg-gray-100 rounded-full text-gray-500">مقفول</span>
                                        @endif
                                    </a>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endforeach
            @endforeach
        @else
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد دروس لهذه المهارة بعد.</p>
            </div>
        @endif
    </div>

    {{-- Quizzes Tab --}}
    <div x-show="tab === 'quizzes'" x-transition class="space-y-4">
        @if($quizzes->isNotEmpty())
            <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($quizzes as $quiz)
                    @php
                        $best = \App\Models\QuizResult::where('user_id', $user->id)
                            ->where('quiz_id', $quiz->id)
                            ->max('score_percentage');
                        $attemptsCount = \App\Models\QuizAttempt::where('user_id', $user->id)
                            ->where('quiz_id', $quiz->id)
                            ->count();
                        $canTake = is_null($quiz->max_attempts) || $attemptsCount < $quiz->max_attempts;
                    @endphp
                    <div class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-shadow">
                        <div class="flex items-center justify-between mb-3">
                            <span class="text-xs px-2 py-0.5 rounded-full font-bold
                                {{ $quiz->difficulty === 'hard' ? 'bg-red-100 text-red-700' : ($quiz->difficulty === 'medium' ? 'bg-amber-100 text-amber-700' : 'bg-emerald-100 text-emerald-700') }}">
                                {{ $quiz->difficulty === 'hard' ? 'صعب' : ($quiz->difficulty === 'medium' ? 'متوسط' : 'سهل') }}
                            </span>
                            <span class="text-xs text-gray-400">{{ $quiz->questions_count ?? 0 }} سؤال</span>
                        </div>
                        <p class="font-bold text-gray-900 text-sm mb-3">{{ $quiz->title_ar ?? $quiz->title }}</p>
                        @if($best !== null)
                            <div class="mb-3">
                                <p class="text-xs text-gray-500 mb-1">أفضل نتيجة: <span class="font-bold {{ $best >= 70 ? 'text-emerald-600' : 'text-amber-600' }}">{{ number_format($best, 0) }}%</span></p>
                                <div class="h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $best >= 70 ? 'bg-emerald-500' : 'bg-amber-500' }}" style="width: {{ $best }}%"></div>
                                </div>
                            </div>
                        @endif
                        @if($canTake)
                            <a href="{{ route('student.quiz.show', $quiz->id) }}" class="block w-full text-center py-2 bg-blue-600 text-white rounded-xl text-sm font-bold hover:bg-blue-700 transition-colors">ابدأ الاختبار</a>
                        @else
                            <a href="{{ route('student.quiz.result', $quiz->id) }}" class="block w-full text-center py-2 bg-gray-100 text-gray-500 rounded-xl text-sm font-bold cursor-not-allowed">انتهت المحاولات</a>
                        @endif
                    </div>
                @endforeach
            </div>
        @else
            <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                <p class="text-gray-400">لا توجد تدريبات لهذه المهارة بعد.</p>
            </div>
        @endif
    </div>

    {{-- Library Tab --}}
    @if($libraryFiles->isNotEmpty())
    <div x-show="tab === 'library'" x-transition class="space-y-4">
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($libraryFiles as $file)
                @php
                    $isPdf = $file->content_type === 'pdf';
                    $ext = $isPdf ? 'pdf' : pathinfo($file->content_url, PATHINFO_EXTENSION);
                @endphp
                <a href="{{ asset('storage/' . $file->content_url) }}" target="_blank"
                   class="bg-white rounded-2xl border border-gray-100 shadow-sm p-5 hover:shadow-md transition-all group">
                    <div class="w-12 h-12 rounded-xl {{ $isPdf ? 'bg-red-50' : 'bg-blue-50' }} flex items-center justify-center mb-3 group-hover:scale-110 transition-transform">
                        @if($isPdf)
                            <svg class="w-6 h-6 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                        @else
                            <svg class="w-6 h-6 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                        @endif
                    </div>
                    <p class="font-bold text-sm text-gray-900 group-hover:text-blue-600 transition-colors">{{ $file->title_ar ?? $file->title }}</p>
                    @if($file->duration_minutes)
                        <p class="text-xs text-gray-500 mt-1">{{ $file->duration_minutes }} دقيقة</p>
                    @endif
                    <span class="inline-block mt-2 text-xs px-2 py-0.5 rounded-full {{ $isPdf ? 'bg-red-100 text-red-700' : 'bg-blue-100 text-blue-700' }} font-bold uppercase">{{ $ext }}</span>
                </a>
            @endforeach
        </div>
    </div>
    @endif
</div>
@endsection
