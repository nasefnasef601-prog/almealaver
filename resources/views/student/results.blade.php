@extends('layouts.student', ['activeTab' => 'results'])

@section('title', 'النتائج')

@php
    use App\Models\QuizResult;
    use App\Models\SkillProgress;
    use App\Models\Lesson;

    $user = Auth::user();
    $allResults = QuizResult::where('user_id', $user->id)
        ->with('quiz')
        ->latest()
        ->get();
    $totalQuizzes = $allResults->count();
    $avgScore = $allResults->avg('score_percentage');
    $passedCount = $allResults->where('passed', true)->count();
    $totalCorrect = $allResults->sum('correct_count');
    $totalQuestions = $allResults->sum('total_questions');
    $accuracy = $totalQuestions > 0 ? round(($totalCorrect / $totalQuestions) * 100, 1) : 0;

    // Aggregate skill progress
    $skillProgress = SkillProgress::where('user_id', $user->id)
        ->with('skill.section.subject')
        ->orderBy('mastery')
        ->get();
@endphp

@section('content')
<div class="max-w-6xl mx-auto" x-data="{ selectedResult: null, showSkillDetails: null }">
    <h1 class="text-2xl font-black text-gray-900 mb-2">النتائج</h1>
    <p class="text-gray-500 text-sm mb-6">جميع نتائج اختباراتك وتحليل المهارات</p>

    {{-- Summary --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">إجمالي الاختبارات</p>
            <p class="text-3xl font-black text-gray-900 mt-1">{{ $totalQuizzes }}</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">متوسط النتيجة</p>
            <p class="text-3xl font-black {{ $avgScore >= 70 ? 'text-emerald-600' : ($avgScore >= 50 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $avgScore ? number_format($avgScore, 1) : '—' }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">نسبة النجاح</p>
            <p class="text-3xl font-black {{ $passedCount/$totalQuizzes >= 0.7 ? 'text-emerald-600' : ($passedCount/$totalQuizzes >= 0.5 ? 'text-amber-600' : 'text-red-600') }} mt-1">{{ $totalQuizzes > 0 ? round(($passedCount/$totalQuizzes)*100) : 0 }}%</p>
        </div>
        <div class="bg-white rounded-2xl border border-gray-100 p-5 shadow-sm">
            <p class="text-gray-500 text-sm">الدقة</p>
            <p class="text-3xl font-black text-blue-600 mt-1">{{ $accuracy ?: '—' }}%</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Left: Results List --}}
        <div class="lg:col-span-2 space-y-6">
            <h2 class="text-lg font-bold text-gray-900">آخر النتائج</h2>
            @forelse($allResults as $result)
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <div class="flex items-center justify-between mb-4">
                        <div>
                            <p class="font-bold text-gray-900">{{ $result->quiz->title_ar ?? $result->quiz->title ?? 'اختبار' }}</p>
                            <p class="text-xs text-gray-500">{{ $result->created_at->format('Y/m/d h:i A') }}</p>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="text-2xl font-black {{ $result->passed ? 'text-emerald-600' : 'text-red-500' }}">{{ number_format($result->score_percentage, 0) }}%</span>
                            <span class="text-xs px-2 py-1 rounded-full font-bold {{ $result->passed ? 'bg-emerald-100 text-emerald-700' : 'bg-red-100 text-red-700' }}">{{ $result->passed ? 'ناجح' : 'راسب' }}</span>
                        </div>
                    </div>
                    <div class="h-2.5 bg-gray-100 rounded-full overflow-hidden mb-4">
                        <div class="h-full rounded-full {{ $result->score_percentage >= 70 ? 'bg-emerald-500' : ($result->score_percentage >= 50 ? 'bg-amber-500' : 'bg-red-500') }}" style="width: {{ $result->score_percentage }}%"></div>
                    </div>
                    <div class="flex items-center justify-between text-sm">
                        <div class="flex gap-4 text-gray-500">
                            <span>✓ {{ $result->correct_count }} صح</span>
                            <span>✗ {{ $result->incorrect_count }} خطأ</span>
                            <span>— {{ $result->unanswered_count }} لم تجب</span>
                        </div>
                        <div class="flex gap-2">
                            <a href="{{ route('student.quiz.result', ['quiz' => $result->quiz_id, 'attempt' => $result->attempt_id]) }}" class="text-blue-600 hover:text-blue-800 font-bold text-sm">
                                مراجعة
                            </a>
                        </div>
                    </div>

                    {{-- Skill breakdown for this result --}}
                    @if(!empty($result->skill_breakdown))
                        <div class="mt-4 pt-4 border-t border-gray-100">
                            <p class="text-sm font-bold text-gray-700 mb-3">تحليل المهارات</p>
                            <div class="grid grid-cols-1 sm:grid-cols-2 gap-2">
                                @foreach($result->skill_breakdown as $sb)
                                    @php
                                        $m = $sb['mastery'] ?? 0;
                                        $bar = $m >= 80 ? 'bg-emerald-500' : ($m >= 60 ? 'bg-amber-500' : 'bg-red-500');
                                    @endphp
                                    <div class="flex items-center gap-2 text-xs">
                                        <span class="w-16 truncate shrink-0 text-gray-600">{{ $sb['skill_name'] }}</span>
                                        <div class="flex-1 h-1.5 bg-gray-100 rounded-full overflow-hidden">
                                            <div class="h-full rounded-full {{ $bar }}" style="width: {{ $m }}%"></div>
                                        </div>
                                        <span class="font-bold w-8 text-left {{ $m >= 80 ? 'text-emerald-600' : ($m >= 60 ? 'text-amber-600' : 'text-red-600') }}">{{ number_format($m, 0) }}%</span>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                </div>
            @empty
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-12 text-center">
                    <p class="text-gray-400 text-lg">لا توجد نتائج بعد. قم بحل بعض الاختبارات!</p>
                    <a href="{{ route('student.quiz.list') }}" class="inline-block mt-4 px-6 py-3 bg-blue-600 text-white rounded-xl font-bold hover:bg-blue-700">اذهب للاختبارات</a>
                </div>
            @endforelse
        </div>

        {{-- Right: Skill Analysis Summary --}}
        <div class="space-y-6">
            @if($skillProgress->isNotEmpty())
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                    <h2 class="text-lg font-bold text-gray-900 mb-4">تقدم المهارات</h2>
                    @php
                        $strong = $skillProgress->filter(fn($s) => $s->status === 'mastered')->count();
                        $good = $skillProgress->filter(fn($s) => $s->status === 'good')->count();
                        $avg = $skillProgress->filter(fn($s) => $s->status === 'average')->count();
                        $weak = $skillProgress->filter(fn($s) => $s->status === 'weak')->count();
                    @endphp
                    <div class="flex flex-wrap gap-2 mb-6">
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-emerald-100 text-emerald-700">{{ $strong }} متقن</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-blue-100 text-blue-700">{{ $good }} جيد</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-amber-100 text-amber-700">{{ $avg }} متوسط</span>
                        <span class="px-3 py-1 rounded-full text-xs font-bold bg-red-100 text-red-700">{{ $weak }} ضعيف</span>
                    </div>
                    <div class="space-y-4">
                        @foreach($skillProgress as $sp)
                            @php
                                $barColor = match($sp->status) {
                                    'mastered' => 'bg-emerald-500',
                                    'good' => 'bg-blue-500',
                                    'average' => 'bg-amber-500',
                                    default => 'bg-red-500',
                                };
                                $textColor = match($sp->status) {
                                    'mastered' => 'text-emerald-700',
                                    'good' => 'text-blue-700',
                                    'average' => 'text-amber-700',
                                    default => 'text-red-700',
                                };
                            @endphp
                            <div>
                                <div class="flex justify-between text-sm mb-1">
                                    <span class="font-medium text-gray-900">{{ $sp->skill->name_ar ?? 'مهارة' }}</span>
                                    <span class="font-bold {{ $textColor }}">{{ number_format($sp->mastery, 0) }}%</span>
                                </div>
                                <div class="w-full h-2 bg-gray-100 rounded-full overflow-hidden">
                                    <div class="h-full rounded-full {{ $barColor }}" style="width: {{ $sp->mastery }}%"></div>
                                </div>
                                <div class="flex justify-between text-xs text-gray-400 mt-0.5">
                                    <span>{{ $sp->skill?->section?->subject?->name_ar ?? '' }}</span>
                                    <span>{{ $sp->total_attempts }} محاولة</span>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>

                {{-- Weak Skills Recommendations --}}
                @php
                    $weakSkills = $skillProgress->filter(fn($s) => $s->status === 'weak' || $s->status === 'average')->take(5);
                @endphp
                @if($weakSkills->isNotEmpty())
                    <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6">
                        <h2 class="text-lg font-bold text-gray-900 mb-4">المهارات الأقل إتقاناً</h2>
                        <div class="space-y-4">
                            @foreach($weakSkills as $sp)
                                @php
                                    $lessons = Lesson::whereHas('course', fn($q) => $q->where('skill_id', $sp->skill_id))
                                        ->where('is_published', true)
                                        ->take(2)
                                        ->get();
                                @endphp
                                <div class="p-4 bg-red-50 rounded-2xl">
                                    <p class="font-bold text-gray-900 text-sm">{{ $sp->skill->name_ar ?? 'مهارة' }}</p>
                                    <p class="text-xs text-gray-500 mb-2">الإتقان: {{ number_format($sp->mastery, 0) }}% — {{ $sp->total_attempts }} محاولة</p>
                                    @if($lessons->isNotEmpty())
                                        <p class="text-xs font-bold text-gray-700 mb-1">دروس مقترحة:</p>
                                        @foreach($lessons as $lesson)
                                            <a href="{{ route('student.lesson.show', ['course' => $lesson->course_id, 'lesson' => $lesson->id]) }}" class="block text-xs text-blue-600 hover:text-blue-800 mb-1">
                                                ← {{ $lesson->title_ar ?? $lesson->title }}
                                            </a>
                                        @endforeach
                                    @endif
                                    <a href="{{ route('student.quiz.list') }}" class="inline-block mt-2 text-xs font-bold text-red-600 hover:text-red-800">
                                        حل اختبارات لهذه المهارة ←
                                    </a>
                                </div>
                            @endforeach
                        </div>
                    </div>
                @endif
            @else
                <div class="bg-white rounded-3xl border border-gray-100 shadow-sm p-6 text-center">
                    <p class="text-gray-400">لم يتم تحليل المهارات بعد.<br>قم بحل اختبار لبدء التحليل.</p>
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
